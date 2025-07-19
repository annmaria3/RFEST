<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rolee'] !== 'organizer') {
    header("Location: loginn.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['rolee'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $target_dir = "gallery/";
    // Ensure the directory exists
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . basename($_FILES['image']['name']);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

    // Validate file type
    if (!in_array($imageFileType, $allowed_types)) {
        echo "<script>alert('Only JPG, JPEG, PNG, and GIF files are allowed.');</script>";
    }
    // Validate file size (5MB max)
    elseif ($_FILES['image']['size'] > 5000000) {
        echo "<script>alert('File is too large. Max size is 5MB.');</script>";
    }
    // Check if file already exists and handle upload
    elseif (file_exists($target_file)) {
        echo "<script>alert('File already exists. Please rename your file and try again.');</script>";
    }
    else {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            // Insert image path into database
            $query = "INSERT INTO gallery_images (image_path, uploaded_by) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $target_file, $user_id);
            if ($stmt->execute()) {
                echo "<script>alert('Image uploaded successfully!'); window.location.href='organizer_dashboard.php';</script>";
            } else {
                echo "<script>alert('Error saving image to database: " . $stmt->error . "'); window.location.href='upload_image.php';</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Error uploading image.');</script>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Image to Gallery</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .form-container { max-width: 400px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        input[type="file"] { margin: 10px 0; }
        button { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #218838; }
        .back-btn { background: #007bff; display: inline-block; padding: 10px 20px; border-radius: 5px; color: white; text-decoration: none; margin-top: 10px; }
        .back-btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Upload Image to Gallery</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="image" accept="image/*" required>
            <button type="submit">Upload</button>
            <a href="organizer_dashboard.php" class="back-btn">Back</a>
        </form>
    </div>
</body>
</html>