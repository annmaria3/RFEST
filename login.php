<?php
session_start();
include 'db_connect.php'; // Connect to your database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $uid = $_POST['User ID'];
    $password = $_POST['Password'];

    // Fetch user details from database
    $query = "SELECT * FROM users WHERE user_id = ? AND pwd = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $uid, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['rolee'];

        // Redirect based on role
        if ($user['rolee'] == 'student') {
            header("Location: student_dashboard.php");
        } elseif ($user['rolee'] == 'faculty') {
            header("Location: faculty_dashboard.php");
        } elseif ($user['rolee'] == 'organizer') {
            header("Location: organizer_dashboard.php");
        } elseif ($user['rolee'] == 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            echo "Invalid role!";
        }
    } else {
        echo "Invalid UID or Password!";
    }
}
?>
