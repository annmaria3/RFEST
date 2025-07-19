<?php
session_start();
include 'db_connect.php';

// Fetch all gallery images
$query = "SELECT image_path, uploaded_at FROM gallery_images ORDER BY uploaded_at DESC";
$result = $conn->query($query);
$images = [];
while ($row = $result->fetch_assoc()) {
    $images[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFEST - Gallery</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f4f4f4;
            text-align: center;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        .gallery img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            object-fit: cover;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>RFEST Gallery</h1>
        <div class="gallery">
            <?php if (!empty($images)): ?>
                <?php foreach ($images as $image): ?>
                    <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="Gallery Image">
                <?php endforeach; ?>
            <?php else: ?>
                <p>No images have been uploaded to the gallery yet.</p>
            <?php endif; ?>
        </div>
        <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
    </div>
</body>
</html>