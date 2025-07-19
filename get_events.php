<?php
include 'db_connection.php';

$sql = "SELECT event_name, registration_start, event_date FROM events ORDER BY event_date ASC";
$result = mysqli_query($conn, $sql);

$events = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['status'] = (strtotime($row['registration_start']) > time()) ? "Coming Soon" : "Open";
    $events[] = $row;
}

echo json_encode($events);
?>
