<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_name = $_POST['event_name'];
    $event_type = $_POST['event_type'];
    $event_date = $_POST['event_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $venue = $_POST['venue'];
    $registration_fee = $_POST['registration_fee'];
    $organizer_id = $_POST['organizer_id'];
    $is_virtual = $_POST['is_virtual'];
    $max_participants = $_POST['max_participants'];
    $portal_id = $_POST['portal_id'];

    $sql = "INSERT INTO events (event_name, event_type, event_date, start_time, end_time, venue, registration_fee, organizer_id, is_virtual, max_participants, current_participants, portal_id) 
            VALUES ('$event_name', '$event_type', '$event_date', '$start_time', '$end_time', '$venue', '$registration_fee', '$organizer_id', '$is_virtual', '$max_participants', 0, '$portal_id')";

    if ($conn->query($sql) === TRUE) {
        echo "Event added successfully";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
