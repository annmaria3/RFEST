<?php
session_start();
include 'db_connect.php';

$user_id = $_GET['user_id'] ?? null;
$event_id = $_GET['event_id'] ?? null;
$amount = $_GET['amount'] ?? 0;

if (!$user_id || !$event_id || $amount <= 0) {
    die("Invalid payment request.");
}

// Here, you'd integrate with a real payment gateway
// Redirect to the payment provider
header("Location: payment_provider_url_here?amount=$amount&event_id=$event_id&user_id=$user_id");
exit();
?>
