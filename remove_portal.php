<?php
session_start();
include 'db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['rolee'] !== 'admin') {
    header("Location: loginn.php");
    exit();
}

// Handle portal removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['portal_id'])) {
    $portal_id = (int)$_POST['portal_id'];

    // Check if portal exists
    $check_query = "SELECT portal_name FROM event_portals WHERE portal_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $portal_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>alert('Portal not found.'); window.location.href='admin_dashboard.php';</script>";
        exit();
    }

    $portal = $result->fetch_assoc();
    $portal_name = $portal['portal_name'];
    $stmt->close();

    // Delete the portal
    $delete_query = "DELETE FROM event_portals WHERE portal_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $portal_id);

    if ($stmt->execute()) {
        echo "<script>alert('Portal \"$portal_name\" removed successfully!'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error removing portal: " . $conn->error . "'); window.location.href='admin_dashboard.php';</script>";
    }
    $stmt->close();
} else {
    header("Location: admin_dashboard.php");
    exit();
}

$conn->close();
?>