<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Require PHPMailer classes
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; // Use your email provider's SMTP
    $mail->SMTPAuth   = true;
    $mail->Username   = 'achyuthpradeep05@gmail.com';  // Replace with your email
    $mail->Password   = 'fryl chbd beuw xxbz';  // Replace with your email password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('achyuthpradeep05@gmail.com', 'Achyuth Pradeep');
    $mail->addAddress('achyuthpradeep05@gmail.com');

    $mail->Subject = 'PHPMailer Test';
    $mail->Body    = 'This is a test email using PHPMailer!';

    $mail->send();
    echo 'Email sent successfully!';
} catch (Exception $e) {
    echo "Email could not be sent. Error: {$mail->ErrorInfo}";
}
?>
