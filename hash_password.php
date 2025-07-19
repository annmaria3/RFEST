<?php
$password = "admin1"; // Change this to your desired admin password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo "Hashed Password: " . $hashed_password;
?>
