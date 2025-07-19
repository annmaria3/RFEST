<?php
session_start();
include 'database.php'; // Ensure database connection is included

$user_id = $_POST['user_id']; // User ID from the login form
$password = $_POST['password']; // Password from the login form

// SQL query to check the users table
$sql = "SELECT user_id, name, pwd, rolee FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists in the database
if ($result->num_rows == 1) {
    $user = $result->fetch_assoc(); // Fetch user data

    // Verify the password using password_verify()
    if (password_verify($password, $user['pwd'])) {
        // Set session variables for the logged-in user
        $_SESSION['user_id'] = $user['user_id']; 
        $_SESSION['rolee'] = $user['rolee']; 
        $_SESSION['name'] = $user['name'];

        // Redirect based on the user's role
        if ($user['rolee'] == 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: user_dashboard.php');
        }
        exit(); // Stop further script execution after redirect
    } else {
        header("Location: loginn.php?error=Invalid User ID or Password");
        exit();
    }
} else {
    header("Location: loginn.php?error=No user found with the provided User ID");
    exit();
}

$stmt->close();
$conn->close();
?>
