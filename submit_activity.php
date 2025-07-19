<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $organizing_body_id = $_POST['organizing_body_id'];
    $event_name = $_POST['event_name'];
    $event_type = $_POST['event_type'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $certificate_type = $_POST['certificate_type'];

    // Handle file upload
    $target_dir = "certificates/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $stmt = $conn->prepare("SELECT * FROM activity_submissions WHERE student_id=? AND event_name=? AND start_time=?");
    $stmt->bind_param("iss", $student_id, $event_name, $start_time);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<p style='color:red; text-align:center;'>You’ve already submitted this event!</p>";
    } else {
        // Insert into DB
        $stmt = $conn->prepare("INSERT INTO activity_submissions 
        (student_id, organizing_body_id, event_name, event_type, start_time, end_time, certificate_type, certificate_path)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissssss", $student_id, $organizing_body_id, $event_name, $event_type, $start_time, $end_time, $certificate_type, $certificate_path);

        if ($stmt->execute()) {
            echo "<p style='color:green; text-align:center;'>Submission successful!</p>";
        } else {
            echo "<p style='color:red; text-align:center;'>Error: " . $stmt->error . "</p>";
        }
    }
}
?>
