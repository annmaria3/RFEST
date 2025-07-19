<?php
session_start();
include 'db_connect.php';
include 'functions.php';

$user_id = $_POST['user_id'] ?? null;
$event_id = $_POST['event_id'] ?? null;
$portal = $_POST['portal'] ?? '';

if (!$event_id || !$user_id || $_SERVER['REQUEST_METHOD'] != 'POST') {
    redirect("ind.php?portal=$portal&error=invalid_data");
}

$eventData = getEventData($conn, $event_id);
if (!$eventData) {
    redirect("ind.php?portal=$portal&error=invalid_event");
}

if ($eventData['max_participants'] !== null && $eventData['current_participants'] >= $eventData['max_participants']) {
    redirect("ind.php?portal=$portal&error=event_full");
}

$registration_id = isUserRegistered($conn, $user_id, $event_id);
if ($registration_id && hasTicket($conn, $registration_id)) {
    redirect("ind.php?portal=$portal&error=already_registered");
}

$registrationDate = date("Y-m-d H:i:s");
$paymentStatus = 'Completed';
$paymentDate = $registrationDate;
$isGroup = $eventData['is_group'] === 'yes';

if ($isGroup) {
    $groupOption = $_POST['group_option'] ?? '';
    $conn->begin_transaction();
    try {
        if ($groupOption === 'team') {
            $groupName = $_POST['group_name'] ?? '';
            $groupMembers = $_POST['group_members'] ?? '';

            if (empty($groupName) || empty($groupMembers)) {
                throw new Exception("Team name and members are required");
            }

            $members = array_map('intval', explode(',', $groupMembers));
            if (!in_array($user_id, $members)) {
                $members[] = $user_id;
            }

            foreach ($members as $member_id) {
                $query = "SELECT COUNT(*) FROM users WHERE user_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $member_id);
                $stmt->execute();
                if ($stmt->get_result()->fetch_row()[0] == 0) {
                    throw new Exception("Invalid user ID: $member_id");
                }
                $query = "SELECT COUNT(*) FROM registrations WHERE user_id = ? AND event_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $member_id, $event_id);
                $stmt->execute();
                if ($stmt->get_result()->fetch_row()[0] > 0) {
                    throw new Exception("User $member_id is already registered");
                }
            }

            foreach ($members as $member_id) {
                $query = "INSERT INTO registrations (user_id, event_id, registration_date, payment_status, payment_date) 
                          VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sisss", $member_id, $event_id, $registrationDate, $paymentStatus, $paymentDate);
                $stmt->execute();
            }

            $groupMembersStr = implode(',', $members);
            $query = "INSERT INTO groups (event_id, group_name, group_leader, group_members) 
                      VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("isis", $event_id, $groupName, $user_id, $groupMembersStr);
            $stmt->execute();

            $query = "SELECT registration_id FROM registrations WHERE user_id = ? AND event_id = ? LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $user_id, $event_id);
            $stmt->execute();
            $registration_id = $stmt->get_result()->fetch_assoc()['registration_id'];

        } elseif ($groupOption === 'auto') {
            $query = "INSERT INTO registrations (user_id, event_id, registration_date, payment_status, payment_date, auto_group) 
                      VALUES (?, ?, ?, ?, ?, 'yes')";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sisss", $user_id, $event_id, $registrationDate, $paymentStatus, $paymentDate);
            $stmt->execute();
            $registration_id = $stmt->insert_id;
        } else {
            throw new Exception("Invalid or missing group option");
        }

        $query = "UPDATE events SET current_participants = (SELECT COUNT(DISTINCT user_id) FROM registrations WHERE event_id = ?) 
                  WHERE event_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $event_id, $event_id);
        $stmt->execute();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        redirect("ind.php?portal=$portal&error=" . urlencode($e->getMessage()));
    }
} else {
    $query = "INSERT INTO registrations (user_id, event_id, registration_date, payment_status, payment_date) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sisss", $user_id, $event_id, $registrationDate, $paymentStatus, $paymentDate);

    if (!$stmt->execute()) {
        logDebug("Registration failed: " . $stmt->error);
        redirect("ind.php?portal=$portal&error=registration_failed");
    }

    $registration_id = $stmt->insert_id;

    $query = "UPDATE events SET current_participants = current_participants + 1 WHERE event_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $event_id);
    if (!$stmt->execute()) {
        logDebug("Update event failed: " . $stmt->error);
        redirect("ind.php?portal=$portal&error=update_event_failed");
    }
}

// Generate ticket
include 'generate_ticket.php';
generateTicket($conn, $registration_id, $portal);

$conn->close();
?>