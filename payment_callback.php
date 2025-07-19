<?php
session_start();
include 'db_connect.php';
require 'lib/encdec_paytm.php';
require 'phpqrcode/qrlib.php';

// Paytm Test Mode credentials
define('PAYTM_MERCHANT_ID', 'DIY12386817555501617');
define('PAYTM_MERCHANT_KEY', 'bKMfNxPPf_QdZppa');
define('PAYTM_ENVIRONMENT', 'TEST');

// Get portal from callback
$portal = $_GET['portal'] ?? '';

// Verify checksum
$paytmChecksum = $_POST['CHECKSUMHASH'] ?? '';
$isValidChecksum = verifychecksum_e($_POST, PAYTM_MERCHANT_KEY, $paytmChecksum);

if ($isValidChecksum && isset($_POST['STATUS']) && $_POST['STATUS'] === 'TXN_SUCCESS') {
    $order_id = $_POST['ORDERID'];
    $txn_amount = $_POST['TXNAMOUNT'];
    $txn_id = $_POST['TXNID'];

    // Extract user_id and event_id from order_id
    $order_parts = explode('_', $order_id);
    $event_id = $order_parts[1];
    $user_id = $order_parts[2];
    $group_id = $_GET['group_id'] ?? null;

    // Verify event details
    $eventQuery = "SELECT registration_fee, is_group FROM events WHERE event_id = ?";
    $stmt = $conn->prepare($eventQuery);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $eventResult = $stmt->get_result();
    $eventData = $eventResult->fetch_assoc();
    $expectedFee = $eventData['registration_fee'];
    $isGroupEvent = $eventData['is_group'] === 'yes';

    if ($txn_amount != $expectedFee) {
        header("Location: ind.php?portal=" . urlencode($portal) . "&error=amount_mismatch");
        exit();
    }

    // Register user
    $registrationDate = date("Y-m-d H:i:s");
    $paymentStatus = 'Completed';
    $paymentDate = $registrationDate;

    if ($isGroupEvent && $group_id) {
        $insertRegQuery = "INSERT INTO registrations 
                          (user_id, event_id, registration_date, payment_status, payment_date, group_id) 
                          VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertRegQuery);
        $stmt->bind_param("sssssi", $user_id, $event_id, $registrationDate, $paymentStatus, $paymentDate, $group_id);
    } else {
        $insertRegQuery = "INSERT INTO registrations 
                          (user_id, event_id, registration_date, payment_status, payment_date) 
                          VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertRegQuery);
        $stmt->bind_param("sssss", $user_id, $event_id, $registrationDate, $paymentStatus, $paymentDate);
    }

    if ($stmt->execute()) {
        $registration_id = $stmt->insert_id;
        $stmt->close();

        // Generate ticket
        $ticket_id = uniqid();
        $qr_code_path = "qrcodes/$ticket_id.png";
        QRcode::png("Ticket ID: $ticket_id, Registration ID: $registration_id, Event ID: $event_id, User ID: $user_id", 
                    $qr_code_path, QR_ECLEVEL_L, 4);

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

        header("Location: ind.php?portal=" . urlencode($portal) . "&success=registered");
    } else {
        header("Location: ind.php?portal=" . urlencode($portal) . "&error=registration_failed&message=" . urlencode($stmt->error));
    }
} else {
    $errorMsg = $_POST['RESPMSG'] ?? 'Transaction failed';
    header("Location: ind.php?portal=" . urlencode($portal) . "&error=payment_failed&message=" . urlencode($errorMsg));
}

$conn->close();
exit();
?>