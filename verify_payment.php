<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $event_id = $_POST['event_id'];
    $transaction_id = $_POST['transaction_id'];

    if (!$user_id || !$event_id || !$transaction_id) {
        header("Location: ind.php?portal=" . $_GET['portal'] . "&error=invalid_data");
        exit();
    }

    // Save transaction details in the database
    $query = "INSERT INTO payments (user_id, event_id, transaction_id, status) VALUES (?, ?, ?, 'Pending')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sis", $user_id, $event_id, $transaction_id);

    if ($stmt->execute()) {
        header("Location: ind.php?portal=" . $_GET['portal'] . "&success=payment_pending");
    } else {
        header("Location: ind.php?portal=" . $_GET['portal'] . "&error=payment_failed");
    }

    $stmt->close();
    $conn->close();
}
?>
