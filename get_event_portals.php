<?php
include 'db_connection.php'; // Include your database connection

$sql = "SELECT * FROM event_portals"; 
$result = mysqli_query($conn, $sql);

$portals = [];
while ($row = mysqli_fetch_assoc($result)) {
    $portals[] = $row;
}

echo json_encode($portals);
?>
