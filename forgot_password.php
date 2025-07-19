<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background: linear-gradient(135deg, #667eea, #764ba2);
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
            background: #ff6b6b;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background: #ff4757;
        }
        .message {
            margin-top: 10px;
            font-weight: bold;
            color: yellow;
        }
    </style>
</head>
<body>
    <h2>Forgot Password</h2>
    <div class="container">
        <form action="send_reset_link.php" method="POST">
            <input type="email" name="email" placeholder="Enter your registered email" required>
            <button type="submit">Send Reset Link</button>
        </form>
        <?php
        if (isset($_GET['status']) && $_GET['status'] == 'success') {
            echo "<p class='message'>✅ Email sent successfully! Check your inbox.</p>";
        }
        ?>
    </div>
</body>
</html>
