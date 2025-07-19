<?php
session_start();
include 'db_connect.php';
require 'phpqrcode/qrlib.php';

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get parameters from GET or POST
$user_id = $_REQUEST['user_id'] ?? null;
$event_id = $_REQUEST['event_id'] ?? null;
$portal = $_REQUEST['portal'] ?? null;

// Validate required parameters
if (!$user_id || !$event_id || !$portal) {
    header("Location: ind.php?portal=" . urlencode($portal) . "&error=invalid_data");
    exit();
}

// Verify event details
$eventQuery = "SELECT event_id, event_name, max_participants, current_participants, 
               registration_fee, is_group 
               FROM events WHERE event_id = ?";
$stmt = $conn->prepare($eventQuery);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$eventResult = $stmt->get_result();

if (!$eventResult || $eventResult->num_rows == 0) {
    header("Location: ind.php?portal=" . urlencode($portal) . "&error=invalid_event");
    exit();
}

$eventData = $eventResult->fetch_assoc();
$maxParticipants = $eventData['max_participants'];
$currentParticipants = $eventData['current_participants'];
$registrationFee = $eventData['registration_fee'];
$isGroupEvent = $eventData['is_group'] === 'yes'; // Normalize to boolean-like comparison

// Check if event is full
if ($maxParticipants !== null && $currentParticipants >= $maxParticipants) {
    header("Location: ind.php?portal=" . urlencode($portal) . "&error=event_full_registration_closed");
    exit();
}

// Check if user is already registered
$checkRegQuery = "SELECT registration_id FROM registrations WHERE user_id = ? AND event_id = ?";
$stmt = $conn->prepare($checkRegQuery);
$stmt->bind_param("si", $user_id, $event_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    header("Location: ind.php?portal=" . urlencode($portal) . "&error=already_registered");
    exit();
}
$stmt->close();

// Handle form submission for group events
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isGroupEvent) {
    handleGroupRegistration($conn, $user_id, $event_id, $portal, $registrationFee);
    exit();
}

// Handle paid or free individual registration
if (!$isGroupEvent) {
    registerIndividual($conn, $user_id, $event_id, $portal, $registrationFee);
    exit();
}

// Show group registration form if group event
if ($isGroupEvent) {
    showGroupForm($user_id, $event_id, $portal);
    exit();
}

// Default redirect if something unexpected happens
header("Location: ind.php?portal=" . urlencode($portal) . "&error=registration_failed");
exit();

// ============ FUNCTIONS ============ //

function handleGroupRegistration($conn, $user_id, $event_id, $portal, $registrationFee) {
    // Validate group form data
    if (!isset($_POST['group_name']) || !isset($_POST['group_leader']) || !isset($_POST['group_members'])) {
        header("Location: ind.php?portal=" . urlencode($portal) . "&error=missing_group_data");
        exit();
    }

    $groupName = trim($_POST['group_name']);
    $groupLeader = trim($_POST['group_leader']);
    $groupMembers = array_map('trim', explode(',', $_POST['group_members']));

    // Validate group leader is the current user
    if ($groupLeader != $user_id) {
        header("Location: ind.php?portal=" . urlencode($portal) . "&error=invalid_leader");
        exit();
    }

    // Create the group
    $allMembers = array_unique(array_merge([$groupLeader], $groupMembers));
    $membersString = implode(',', $allMembers);

    $insertGroupQuery = "INSERT INTO groups (event_id, group_name, group_leader, group_members) 
                         VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insertGroupQuery);
    $stmt->bind_param("isss", $event_id, $groupName, $groupLeader, $membersString);

    if (!$stmt->execute()) {
        $stmt->close();
        header("Location: ind.php?portal=" . urlencode($portal) . "&error=group_creation_failed");
        exit();
    }

    $groupId = $stmt->insert_id;
    $stmt->close();

    // Register all group members
    foreach ($allMembers as $memberId) {
        if (empty($memberId)) continue;

        // Verify user exists
        $checkUserQuery = "SELECT user_id FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($checkUserQuery);
        $stmt->bind_param("s", $memberId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            $stmt->close();
            continue; // Skip invalid users
        }
        $stmt->close();

        // Check if already registered
        $checkRegQuery = "SELECT registration_id FROM registrations 
                          WHERE user_id = ? AND event_id = ?";
        $stmt = $conn->prepare($checkRegQuery);
        $stmt->bind_param("si", $memberId, $event_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();
            continue; // Skip already registered members
        }
        $stmt->close();

        // Register member (paid or free)
        registerGroupMember($conn, $memberId, $event_id, $groupId, $registrationFee);
    }

    // Show success pop-up and redirect
    echo "<script>alert('Transaction successful. Successfully registered!'); window.location.href='ind.php?portal=" . urlencode($portal) . "&success=group_registered';</script>";
    exit();
}

function registerGroupMember($conn, $user_id, $event_id, $group_id, $registrationFee) {
    $registrationDate = date("Y-m-d H:i:s");
    $paymentStatus = 'Completed'; // Always completed, even for paid events
    $paymentDate = $registrationDate;

    // Insert registration
    $insertRegQuery = "INSERT INTO registrations 
                       (user_id, event_id, registration_date, payment_status, payment_date, group_id) 
                       VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertRegQuery);
    $stmt->bind_param("sssssi", $user_id, $event_id, $registrationDate, $paymentStatus, $paymentDate, $group_id);
    $stmt->execute();
    $registration_id = $stmt->insert_id;
    $stmt->close();

    // Generate ticket
    $ticket_id = uniqid();
    $qr_code_path = "qrcodes/$ticket_id.png";
    if (!file_exists('qrcodes')) {
        mkdir('qrcodes', 0777, true);
    }
    QRcode::png("Ticket ID: $ticket_id, Registration ID: $registration_id, Event ID: $event_id, User ID: $user_id", $qr_code_path, QR_ECLEVEL_L, 4);

    $insertTicketQuery = "INSERT INTO tickets 
                          (ticket_id, registration_id, ticket_status, qr_code, user_id, event_id) 
                          VALUES (?, ?, 'Issued', ?, ?, ?)";
    $stmt = $conn->prepare($insertTicketQuery);
    $stmt->bind_param("sissi", $ticket_id, $registration_id, $qr_code_path, $user_id, $event_id);
    $stmt->execute();
    $stmt->close();

    // Update participant count
    $updateEventQuery = "UPDATE events SET current_participants = current_participants + 1 
                         WHERE event_id = ?";
    $stmt = $conn->prepare($updateEventQuery);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->close();
}

function registerIndividual($conn, $user_id, $event_id, $portal, $registrationFee) {
    $registrationDate = date("Y-m-d H:i:s");
    $paymentStatus = 'Completed'; // Always completed, even for paid events
    $paymentDate = $registrationDate;

    // Insert registration
    $insertRegQuery = "INSERT INTO registrations 
                       (user_id, event_id, registration_date, payment_status, payment_date) 
                       VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertRegQuery);
    $stmt->bind_param("sssss", $user_id, $event_id, $registrationDate, $paymentStatus, $paymentDate);
    $stmt->execute();
    $registration_id = $stmt->insert_id;
    $stmt->close();

    // Generate ticket
    $ticket_id = uniqid();
    $qr_code_path = "qrcodes/$ticket_id.png";
    if (!file_exists('qrcodes')) {
        mkdir('qrcodes', 0777, true);
    }
    QRcode::png("Ticket ID: $ticket_id, Registration ID: $registration_id, Event ID: $event_id, User ID: $user_id", $qr_code_path, QR_ECLEVEL_L, 4);

    $insertTicketQuery = "INSERT INTO tickets 
                          (ticket_id, registration_id, ticket_status, qr_code, user_id, event_id) 
                          VALUES (?, ?, 'Issued', ?, ?, ?)";
    $stmt = $conn->prepare($insertTicketQuery);
    $stmt->bind_param("sissi", $ticket_id, $registration_id, $qr_code_path, $user_id, $event_id);
    $stmt->execute();
    $stmt->close();

    // Update participant count
    $updateEventQuery = "UPDATE events SET current_participants = current_participants + 1 
                         WHERE event_id = ?";
    $stmt = $conn->prepare($updateEventQuery);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->close();

    // Show success pop-up and redirect
    echo "<script>alert('Transaction successful. Successfully registered!'); window.location.href='ind.php?portal=" . urlencode($portal) . "&success=registered';</script>";
    exit();
}

function showGroupForm($user_id, $event_id, $portal) {
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Group Registration</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; }
            .form-group { margin-bottom: 15px; }
            label { display: block; margin-bottom: 5px; font-weight: bold; }
            input[type="text"], textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
            button { background: #28a745; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; }
            button:hover { background: #218838; }
        </style>
    </head>
    <body>
        <h2>Group Registration</h2>
        <form method="POST" action="register.php">
            <input type="hidden" name="user_id" value="' . htmlspecialchars($user_id) . '">
            <input type="hidden" name="event_id" value="' . htmlspecialchars($event_id) . '">
            <input type="hidden" name="portal" value="' . htmlspecialchars($portal) . '">
            
            <div class="form-group">
                <label for="group_name">Group Name:</label>
                <input type="text" id="group_name" name="group_name" required>
            </div>
            
            <div class="form-group">
                <label for="group_leader">Group Leader (Your User ID):</label>
                <input type="text" id="group_leader" name="group_leader" value="' . htmlspecialchars($user_id) . '" readonly>
            </div>
            
            <div class="form-group">
                <label for="group_members">Group Members (comma separated User IDs):</label>
                <textarea id="group_members" name="group_members" rows="3" required></textarea>
                <small>Enter the user IDs of your team members, separated by commas</small>
            </div>
            
            <div class="form-group">
                <button type="submit">Register Group</button>
            </div>
        </form>
    </body>
    </html>';
}
?>