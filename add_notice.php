<?php
session_start();
include 'db_connect.php'; // Ensure this file has a valid DB connection

if (!isset($_SESSION['user_id'])) {
    header("Location: loginn.php");
    exit();
}

// Check if user is either an organizer or admin
$allowed_roles = ['organizer', 'admin'];
if (!in_array($_SESSION['rolee'], $allowed_roles)) {
    header("Location: loginn.php");
    exit();
}

// Use session variables as needed
$user_id = $_SESSION['user_id'];
$role = $_SESSION['rolee'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    //$created_by = $_SESSION['user_id'];
    $created_by ='A002'; // Admin's user_id

    // Insert into the database
    $sql = "INSERT INTO noticeboard (title, description, created_by) VALUES ('$title', '$description', '$created_by')";

    if (mysqli_query($conn, $sql)) {
        $message = "Notice added successfully!";
    } else {
        $message = "Error adding notice: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Notice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            text-align: center;
            background: #f4f4f4;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        h2 {
            margin-bottom: 20px;
        }
        input, textarea, button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
        button {
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #218838;
        }
        .message {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add Notice</h2>
        
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

        <form method="POST">
            <input type="text" name="title" placeholder="Enter Notice Title" required>
            <textarea name="description" placeholder="Enter Notice Description" rows="5" required></textarea>
            <button type="submit">Add Notice</button>
        </form>

        <br>
       
    </div>
</body>
</html>
