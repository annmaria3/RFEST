<?php
include 'db_connect.php';

if (!isset($_GET['token'])) {
    die("❌ Invalid request: No token found.");
}

$token = urldecode($_GET['token']); // Ensure correct token formatting

// Retrieve token and expiry from database
$stmt = $conn->prepare("SELECT email, expiry FROM password_resets WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data) {
    $expiry_time = strtotime($data['expiry']);  // Convert expiry to timestamp
    $current_time = strtotime(date("Y-m-d H:i:s")); // Get current timestamp

    if ($expiry_time < $current_time) {
        die("❌ Token has expired! Request a new reset link.");
    }
} else {
    die("❌ Invalid token! Token does not exist in the database.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background: linear-gradient(135deg, #ff758c, #ff7eb3);
            color: white;
        }
        .container {
            max-width: 400px;
            margin: auto;
            background: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            border: none;
            font-size: 16px;
        }
        input {
            background: white;
            color: black;
        }
        button {
            background: #28a745;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background: #218838;
        }
        .message {
            margin-top: 10px;
            font-weight: bold;
            color: yellow;
        }
    </style>
</head>
<body>
    <h2>Reset Your Password</h2>
    <div class="container">
        <form action="update_password.php" method="POST">
            <input type="hidden" name="email" value="<?php echo $data['email']; ?>">
            <input type="password" name="new_password" placeholder="Enter New Password" required>
            <button type="submit">Reset Password</button>
        </form>
        <?php
        if (isset($_GET['status']) && $_GET['status'] == 'success') {
            echo "<p class='message'>✅ Password reset successfully!</p>";
        }
        ?>
    </div>
</body>
</html>
