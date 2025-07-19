<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ticket_id = $_POST['ticket_id'] ?? null;

    if (!$ticket_id) {
        echo json_encode(["status" => "error", "message" => "Invalid Ticket"]);
        exit();
    }

    // Check if the ticket exists and is not already marked
    $query = "SELECT attendance_status FROM tickets WHERE ticket_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $ticket_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo json_encode(["status" => "error", "message" => "Ticket Not Found"]);
        exit();
    }

    $ticket = $result->fetch_assoc();
    if ($ticket['attendance_status'] == 'Present') {
        echo json_encode(["status" => "error", "message" => "Already Marked Present"]);
        exit();
    }

    // Mark attendance
    $updateQuery = "UPDATE tickets SET attendance_status = 'Present', attendance_time = NOW() WHERE ticket_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("s", $ticket_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Attendance Marked"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database Error"]);
    }
}

$conn->close();
?>
