<?php
include 'db_connection.php'; // Include database connection

$sql = "SELECT * FROM notice ORDER BY created_at DESC"; 
$result = mysqli_query($conn, $sql);

$notices = [];
while ($row = mysqli_fetch_assoc($result)) {
    $notices[] = $row;
}

echo json_encode($notices);
?>
