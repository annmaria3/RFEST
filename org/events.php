<?php
include 'db.php';

$sql = "SELECT * FROM events ORDER BY event_date ASC";
$result = $conn->query($sql);

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

header('Content-Type: application/json');
echo json_encode($events);
?>
