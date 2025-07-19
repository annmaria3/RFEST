<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

$query = "SELECT event_name, event_date, start_time, end_time, venue FROM events";

$events = [];
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $event = [
            'title' => $row['event_name'],
            'start' => $row['event_date'] . ($row['start_time'] ? 'T' . $row['start_time'] : ''),
            'end' => $row['event_date'] . ($row['end_time'] ? 'T' . $row['end_time'] : ''),
            'venue' => $row['venue']
        ];
        $events[] = $event;
    }
    echo json_encode($events);
} else {
    echo json_encode([]);
}

$conn->close();
?>