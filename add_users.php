<?php
// Include database connection
include 'db.php';

function addUser($conn, $name, $email, $password, $role, $phone_no, $status) {
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // SQL query to insert user
    $query = "INSERT INTO users (name, email, pwd, rolee, phone_no, registration_date, status) 
              VALUES (?, ?, ?, ?, ?, NOW(), ?)";

    // Prepare and bind parameters
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssss", $name, $email, $hashed_password, $role, $phone_no, $status);

    // Execute the query
    if ($stmt->execute()) {
        echo "User '$name' added successfully!<br>";
    } else {
        echo "Error: " . $stmt->error . "<br>";
    }

    // Close statement
    $stmt->close();
}

// Add users
addUser($conn, "Dilsha", "dilsha@example.com", "password123", "organizer", "9876543210", "Active");
addUser($conn, "Kavi", "kavi@example.com", "securepass456", "faculty", "9123456780", "Active");

// Close the database connection
$conn->close();
?>
