<?php
include 'db_connect.php'; // Ensure this file connects to your database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $club_name = $_POST["club_name"];

    // Insert into event_portal table
    $sql = "INSERT INTO event_portals (portal_name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $club_name);

    if ($stmt->execute()) {
        echo "<script>alert('Event Portal Created Successfully!'); window.location.href='admin.php';</script>";
    } else {
        echo "<script>alert('Error Creating Portal'); window.location.href='admin.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
