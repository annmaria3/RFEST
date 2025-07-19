<?php
include 'db_connect.php'; // Database connection

// Check if user_id is provided in the URL
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Prepare and execute the delete query
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);

    if ($stmt->execute()) {
        // Redirect back to view_users.php with a success message
        header("Location: view_users.php?message=User Deleted Successfully");
    } else {
        // Redirect back with an error message
        header("Location: view_users.php?error=Failed to Delete User");
    }

    $stmt->close();
} else {
    // Redirect if no user_id is provided
    header("Location: view_users.php?error=Invalid Request");
}

$conn->close();
?>
