<?php
session_start();
include 'db_connect.php'; // Include your database connection file

$error_message = ""; // Initialize error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = trim($_POST['user_id']);
    $password = trim($_POST['password']);

    // Prepare SQL query to fetch user credentials
    $sql = "SELECT user_id, pwd, rolee FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Verify password (assuming password is stored hashed)
        if (password_verify($password, $row['pwd'])) {
            $_SESSION['user_id'] = $row['user_id']; // Store session
            $_SESSION['rolee'] = $row['rolee']; // Store role session

            // Redirect based on role
            if ($row['rolee'] === "admin") {
                header("Location: admin.php");
            } elseif ($row['rolee'] === "organizer") {
                header("Location: organizer_dashboard.php");
            } elseif ($row['rolee'] === "student") {
                header("Location: dashboard.php");
            } elseif ($row['rolee'] === "faculty") {
                header("Location: faculty.php");
            } else {
                $error_message = "Invalid role assigned.";
            }
            exit();
        } else {
            $error_message = "Incorrect password.";
        }
    } else {
        $error_message = "User not found.";
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
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: url('rfest.png') no-repeat center center/cover;
        }

        /* Banner Styling */
        .banner {
            font-size: 24px;
            font-weight: bold;
            color: white;
            background-color: #007bff;
            padding: 15px;
            text-align: center;
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
        }

        /* Login Container */
        .login-container {
            width: 350px;
            padding: 20px;
            background: rgba(255, 250, 205, 0.9);
            backdrop-filter: blur(5px);
            border-radius: 10px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
            margin-top: 80px;
        }

        /* Title */
        .login-container h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        /* Input Fields */
        .input-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .input-group label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .input-group input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        /* Login Button */
        .login-btn {
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

        .login-btn:hover {
            background-color: #0056b3;
        }

        /* Links Styling */
        .extra-links {
            margin-top: 10px;
        }

        .extra-links a {
            display: block;
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
            margin-top: 5px;
        }

        .extra-links a:hover {
            text-decoration: underline;
        }

        /* Error Message */
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h1>LOGIN</h1>

        <!-- Display error message if any -->
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <form action="loginn.php" method="post">
            <div class="input-group">
                <label for="user_id">User ID</label>
                <input type="text" id="user_id" name="user_id" placeholder="Enter your User ID" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="login-btn">Login</button>

            <div class="extra-links">
                <a href="forgot_password.php">Forgot Password?</a>
                <a href="signup.html">Don't have an account? Sign Up</a>
            </div>
        </form>
    </div>

</body>
</html>
