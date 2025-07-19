<?php
session_start();
include 'db.php'; // Ensure this file contains your database connection ($conn)

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // This should be a valid user_id from `users` table

// Debugging: Check if user_id exists
if (empty($user_id)) {
    die("Error: User ID is missing from session.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $created_by = (int) $_SESSION['user_id']; // Ensure integer type
    $creation_date = date("Y-m-d H:i:s");

    // Debugging: Check if values are properly assigned
    if (empty($title) || empty($description)) {
        die("Error: Title and Description cannot be empty.");
    }
    
    // Prepare and execute the query
    $stmt = $conn->prepare("INSERT INTO noticeboard (title, description, created_by, creation_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $title, $description, $created_by, $creation_date);

    if ($stmt->execute()) {
        echo "<script>alert('Notice created successfully!'); window.location.href='org1.php';</script>";
    } else {
        die("Error creating notice: " . $stmt->error); // Debugging error output
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Notice</title>
</head>
<body>
    <h2>Create Notice</h2>
    <form method="POST" action="">
        <label>Title:</label>
        <input type="text" name="title" required>
        <br>
        <label>Description:</label>
        <textarea name="description" required></textarea>
        <br>
        <button type="submit">Submit</button>
    </form>
</body>
</html>
