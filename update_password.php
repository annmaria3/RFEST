<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $new_password = password_hash($_POST["new_password"], PASSWORD_BCRYPT);

    // Update password in the users table
    $stmt = $conn->prepare("UPDATE users SET pwd = ? WHERE email = ?");
    $stmt->bind_param("ss", $new_password, $email);

    if ($stmt->execute()) {
        // Delete the reset token from the database
        $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        echo "<script>alert('Password reset successfully!'); window.location.href='login.php';</script>";
    } else {
        echo "Error updating password.";
    }
}
?>
