<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_id = $_POST['payment_id'];

    // Update payment status
    $query = "UPDATE payments SET status = 'Completed' WHERE payment_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $payment_id);

    if ($stmt->execute()) {
        header("Location: admin_verify.php?success=payment_verified");
    } else {
        header("Location: admin_verify.php?error=update_failed");
    }

    $stmt->close();
    $conn->close();
}
?>
