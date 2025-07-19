<?php
include 'db_connect.php';

$event_name = $_POST['event_name'];
$competition_type = $_POST['competition_type'];
$entries = $_POST['entries'];

// Get event_id from event_name
$eventQuery = $conn->prepare("SELECT event_id FROM events WHERE event_name = ?");
$eventQuery->bind_param("s", $event_name);
$eventQuery->execute();
$eventResult = $eventQuery->get_result();

if ($eventResult->num_rows === 0) {
    die("Event not found in database.");
}

$eventRow = $eventResult->fetch_assoc();
$event_id = $eventRow['event_id'];

foreach ($entries as $position => $data) {
    $name = $data['name'];
    $points = $data['points'];

    if ($competition_type === 'group') {
        $query = "INSERT INTO student_leaderboard (event_id, competition_type, group_name, position, points)
                  VALUES (?, ?, ?, ?, ?)
                  ON DUPLICATE KEY UPDATE points = VALUES(points)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issii", $event_id, $competition_type, $name, $position, $points);
    } else {
        $query = "INSERT INTO student_leaderboard (event_id, competition_type, student_id, position, points)
                  VALUES (?, ?, ?, ?, ?)
                  ON DUPLICATE KEY UPDATE points = VALUES(points)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issii", $event_id, $competition_type, $name, $position, $points);
    }

    $stmt->execute();
    $stmt->close();
}

$conn->close();
echo "<script>alert('Leaderboard Updated!'); window.location.href='leaderboard_group_form.php';</script>";

?>
