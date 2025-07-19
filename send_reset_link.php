<?php
session_start();
include 'db_connect.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Delete expired tokens before generating a new one
    $stmt = $conn->prepare("DELETE FROM password_resets WHERE expiry < NOW()");
    $stmt->execute();

    // Check if email exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $user_id = $user['user_id'];

        // Generate unique token
        $token = bin2hex(random_bytes(50));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour")); // Increased expiry time

        // Store token in database
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expiry) VALUES (?, ?, ?)
                                ON DUPLICATE KEY UPDATE token=?, expiry=?");
        $stmt->bind_param("sssss", $email, $token, $expiry, $token, $expiry);
        $stmt->execute();

        // Send email
        $reset_link = "http://localhost/rfest/reset_password.php?token=" . urlencode($token);
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'achyuthpradeep05@gmail.com';
            $mail->Password = 'fryl chbd beuw xxbz'; // Use App Password, NOT your actual password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('achyuthpradeep05@gmail.com', 'Achyuth Pradeep');
            $mail->addAddress($email);
            $mail->Subject = "Password Reset Request";
            $mail->Body = "Click the link below to reset your password:\n\n$reset_link\n\nThis link expires in 1 hour.";

            $mail->send();
            header("Location: forgot_password.php?status=success");
        } catch (Exception $e) {
            echo "Error sending email: " . $mail->ErrorInfo;
        }
    } else {
        echo "Email not found!";
    }
}
?>

