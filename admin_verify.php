<?php
session_start();
include 'db_connect.php';

// Fetch pending payments
$query = "SELECT p.payment_id, p.user_id, p.event_id, p.transaction_id, e.event_name 
          FROM payments p JOIN events e ON p.event_id = e.event_id 
          WHERE p.status = 'Pending'";
$result = $conn->query($query);

echo "<h2>Pending Payments</h2>";
echo "<table border='1'>";
echo "<tr><th>User ID</th><th>Event</th><th>Transaction ID</th><th>Action</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['user_id']}</td>";
    echo "<td>{$row['event_name']}</td>";
    echo "<td>{$row['transaction_id']}</td>";
    echo "<td>
            <form action='approve_payment.php' method='POST'>
                <input type='hidden' name='payment_id' value='{$row['payment_id']}'>
                <button type='submit'>Approve</button>
            </form>
          </td>";
    echo "</tr>";
}
echo "</table>";
?>
