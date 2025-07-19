<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['image'])) {
    $image_name = $_FILES['image']['name'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $upload_dir = "uploads/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $image_path = $upload_dir . basename($image_name);

    if (move_uploaded_file($image_tmp_name, $image_path)) {
        $stmt = $conn->prepare("INSERT INTO image_gallery (images) VALUES (?)");
        $stmt->bind_param("s", $image_path);

        if ($stmt->execute()) {
            echo "<script>alert('Image uploaded successfully!'); window.location.href='org1.php';</script>";
        } else {
            echo "<script>alert('Error uploading image.'); window.location.href='org1.php';</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Error moving image file.'); window.location.href='org1.php';</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Image</title>
</head>
<body>
    <h2>Upload Image</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <label>Select Image:</label>
        <input type="file" name="image" required>
        <br>
        <button type="submit">Upload</button>
    </form>
</body>
</html>
