<?php
session_start();
include 'db.php'; // Ensure this contains your database connection ($conn)

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $uid = $_POST['uid'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($uid) && !empty($password)) {
        // Securely fetch user details
        $query = "SELECT user_id, name, email, pwd, rolee FROM users WHERE user_id = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $uid);
        $stmt->execute();
        $stmt->store_result(); // Store result to check if the user exists

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $name, $email, $hashed_password, $role);
            $stmt->fetch();

            // Verify password
            if (password_verify($password, $hashed_password)) {
                // Store user details in session
                $_SESSION['user_id'] = $user_id;
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;

                // Redirect to dashboard
                header("Location: org1.php");
                exit();
            } else {
                $error_message = "Invalid UID or Password!";
            }
        } else {
            $error_message = "Invalid UID or Password!";
        }

        $stmt->close();
    } else {
        $error_message = "Please enter both UID and Password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College Event Management - Login</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: url('background.jpg') no-repeat center center/cover;
        }
        .login-box {
            width: 350px;
            padding: 20px;
            background: rgba(255, 250, 205, 0.7);
            backdrop-filter: blur(5px);
            border-radius: 10px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        .login-box h2 { margin-bottom: 20px; font-weight: 600; color: #333; }
        .login-box input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .login-box button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s ease-in-out;
        }
        .login-box button:hover { background-color: #0056b3; }
        .forgot-password { margin-top: 10px; }
        .forgot-password a { color: #007bff; text-decoration: none; font-size: 14px; }
        .forgot-password a:hover { text-decoration: underline; }
        .error-message {
            margin-top: 10px;
            color: red;
            font-size: 16px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>College Event Management System</h2>
        <?php if (!empty($error_message)) { echo "<p class='error-message'>$error_message</p>"; } ?>
        <form action="" method="POST">
            <input type="text" name="uid" placeholder="Enter UID" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            <button type="submit">Login</button>
        </form>
        <div class="forgot-password">
            <a href="#">Forgot Password?</a>
        </div>
    </div>
</body>
</html>
