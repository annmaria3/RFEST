<?php
include 'db_connect.php';

$event_id = $_POST['event_id'];
$action = $_POST['action'];

if ($action === 'approve') {
    $query = "UPDATE events SET status = 'approved' WHERE event_id = ?";
} elseif ($action === 'reject') {
    $query = "DELETE FROM events WHERE event_id = ?";
} else {
    die("Invalid action.");
}

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $event_id);
$stmt->execute();

header("Location: faculty_event_dashboard.php");
exit();
?>
