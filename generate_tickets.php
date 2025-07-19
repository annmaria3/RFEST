<?php
require 'phpqrcode/qrlib.php';
include 'functions.php';

function generateTicket($conn, $registration_id, $portal) {
    $ticket_id = uniqid();
    $qr_code_data = "https://yourwebsite.com/scan_attendance.php?ticket_id=$ticket_id";
    $qr_code_path = "qrcodes/$ticket_id.png";
    QRcode::png($qr_code_data, $qr_code_path, QR_ECLEVEL_L, 4);

    $query = "INSERT INTO tickets (ticket_id, registration_id, ticket_status, qr_code) 
              VALUES (?, ?, 'Issued', ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sis", $ticket_id, $registration_id, $qr_code_path);

    if (!$stmt->execute()) {
        logDebug("Ticket insertion failed: " . $stmt->error);
        redirect("ind.php?portal=$portal&error=ticket_registration_failed&mysql_error=" . urlencode($stmt->error));
    }

    logDebug("Ticket inserted: ticket_id = $ticket_id, registration_id = $registration_id");
    redirect("ind.php?portal=$portal&success=registered");
}
?>