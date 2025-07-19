<?php

$host = 'localhost';         
$username = 'root';          
$password = 'password';      
$database = 'rfest';  
$conn = new mysqli($host, $username, $password, $database,3307);
//echo ".";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
