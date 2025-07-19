<?php
include 'db_connect.php';

if (!isset($_GET['ticket_id'])) {
    die("Invalid QR code.");
}

$ticket_id = $_GET['ticket_id'];

// Check if the ticket exists
$query = "SELECT ticket_id FROM tickets WHERE ticket_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $ticket_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Invalid ticket.");
}

// Update the ticket status to 'Checked-In'
$updateQuery = "UPDATE tickets SET ticket_status = 'Checked-In' WHERE ticket_id = ?";
$stmt = $conn->prepare($updateQuery);
$stmt->bind_param("s", $ticket_id);

if ($stmt->execute()) {
    echo "Attendance Marked Successfully!";
} else {
    echo "Error marking attendance.";
}

$stmt->close();
$conn->close();
?>
