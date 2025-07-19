<?php
$host = "localhost";  
$user = "root";       
$pass = "";           
$dbname = "rfest";  // Change if necessary

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//echo "Server connected";
?>
