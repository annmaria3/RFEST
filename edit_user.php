<?php
include 'db_connect.php'; // Database connection

// Check if user_id is provided in the URL
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch user details
    $stmt = $conn->prepare("SELECT name, email, rolee, phone_no FROM users WHERE user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
    } else {
        echo "User not found!";
        exit();
    }
    $stmt->close();
}

// Handle form submission for updating user details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $phone_no = $_POST['phone_no'];

    // Update user details in the database
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, rolee = ?, phone_no = ? WHERE user_id = ?");
    $stmt->bind_param("sssss", $name, $email, $role, $phone_no, $user_id);

    if ($stmt->execute()) {
        $message = "User details updated successfully!";
    } else {
        $message = "Error updating user details!";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            text-align: center;
            padding: 20px;
        }
        .container {
            width: 50%;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #007bff;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .save-btn {
            background: green;
            color: white;
            border: none;
            cursor: pointer;
        }
        .save-btn:hover { background: darkgreen; }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-btn:hover { background: #0056b3; }
        .message {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Edit User</h2>
        
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

        <form method="POST">
            <label for="name">Name:</label>
            <input type="text" name="name" value="<?php echo $user['name']; ?>" required>

            <label for="email">Email:</label>
            <input type="email" name="email" value="<?php echo $user['email']; ?>" required>

            <label for="role">Role:</label>
            <select name="role" required>
                <option value="admin" <?php if ($user['rolee'] == 'admin') echo 'selected'; ?>>Admin</option>
                <option value="student" <?php if ($user['role'] == 'student') echo 'selected'; ?>>Student</option>
                <option value="organizer" <?php if ($user['role'] == 'organizer') echo 'selected'; ?>>Organizer</option>
                <option value="faculty" <?php if ($user['role'] == 'faculty') echo 'selected'; ?>>Faculty</option>
            </select>

            <label for="phone_no">Phone Number:</label>
            <input type="text" name="phone_no" value="<?php echo $user['phone_no']; ?>">

            <button type="submit" class="save-btn">Save Changes</button>
        </form>

        <a href="view_users.php" class="back-btn">Back to Users</a>
    </div>

</body>
</html>
