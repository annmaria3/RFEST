<?php
// Start the session to store user data
session_start();

// Include the database connection file
include('database.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get data from the form
    $user_id = $_POST['uid']; // "uid" field in login1.html corresponds to user_id
    $password = $_POST['password'];

    // Prepare SQL query to fetch user data based on user_id
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch user details
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Store user details in session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] == 'user') {
                header('Location: user_dashboard.html'); // Redirect to user dashboard
            } else {
                header('Location: admin_dashboard.html'); // Redirect to admin dashboard
            }
            exit();
        } else {
            echo "<script>alert('Incorrect password!'); window.location.href='login1.html';</script>";
        }
    } else {
        echo "<script>alert('No user found with this User ID!'); window.location.href='login1.html';</script>";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
