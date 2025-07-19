<?php
session_start();
include 'db_connect.php'; // path to your database connection

// Redirect if not logged in (uncomment when ready)
/*if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}*/

// Temporary hardcoded user data until session is active
$user_id = 'u2203025'; // Replace with $_SESSION['user_id'] when login works
$name = 'Test User';   // Replace with $_SESSION['name']
$email = 'test@example.com'; // Replace with $_SESSION['email']
$role = 'student';     // Replace with $_SESSION['rolee'] (note the typo in schema: 'rolee')

// Fetch activity points and certificates
$query = "SELECT COALESCE(ap.total_points, 0) AS total_points, 
                 COUNT(c.certificate_id) AS total_certificates
          FROM users u
          LEFT JOIN activitypoints ap ON u.user_id = ap.student_id
          LEFT JOIN certificates c ON u.user_id = c.user_id AND ap.event_id = c.event_id
          WHERE u.user_id = ?
          GROUP BY u.user_id";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id); // user_id is VARCHAR(50), so use "s"
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $points_balance = $row['total_points'];
    $total_certificates = $row['total_certificates'];
} else {
    $points_balance = 0; // Default if no activitypoints record
    $total_certificates = 0; // Default if no certificates
}
$stmt->close();

// Fetch event portals dynamically
$portals = [];
$portal_query = "SELECT portal_id, portal_name FROM event_portals ORDER BY portal_name ASC";
$portal_result = $conn->query($portal_query);

if ($portal_result->num_rows > 0) {
    while ($row = $portal_result->fetch_assoc()) {
        $portals[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFEST - Event Management Dashboard</title>
    <link rel="stylesheet" href="styleor1.css">
</head>
<body>

<div class="dashboard">

    <!-- LEFT SIDEBAR -->
    <?php include '../includes/left_sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <!-- NAVBAR -->
        <?php include '../includes/navbar.php'; ?>

        <!-- Event Dashboard -->
        <section class="event-dashboard">
            <div class="event-grid">
                <?php if (!empty($portals)): ?>
                    <?php foreach ($portals as $portal): ?>
                        <a href="ind.php?portal=<?php echo urlencode($portal['portal_name']); ?>" class="event-card">
                            <h3><?php echo htmlspecialchars($portal['portal_name']); ?></h3>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No event portals available.</p>
                <?php endif; ?>
            </div>

            <!-- Featured Events Carousel -->
            <section class="recommended-events">
                <h2>FEATURED EVENTS</h2>
                <div class="event-carousel">
                    <button class="carousel-btn prev">←</button>
                    <div class="carousel-items">
                        <div class="carousel-item">Event 1</div>
                        <div class="carousel-item">Event 2</div>
                        <div class="carousel-item">Event 3</div>
                        <div class="carousel-item">Event 4</div>
                        <div class="carousel-item">Event 5</div>
                        <div class="carousel-item">Event 6</div>
                        <div class="carousel-item">Event 7</div>
                        <div class="carousel-item">Event 8</div>
                    </div>
                    <button class="carousel-btn next">→</button>
                </div>
            </section>

            <!-- Image Gallery -->
            <section class="image-gallery">
                <h2>Image Gallery</h2>
                <div class="gallery-container">
                    <div class="gallery">
                        <img src="image1.jpg" alt="Image 1">
                        <img src="image2.jpg" alt="Image 2">
                        <img src="image3.jpg" alt="Image 3">
                        <img src="image4.jpg" alt="Image 4">
                        <img src="image5.jpg" alt="Image 5">
                        <img src="image6.jpg" alt="Image 6">
                        <img src="image7.jpg" alt="Image 7">
                        <img src="image8.jpg" alt="Image 8">
                    </div>
                </div>
            </section>
        </section>
    </main>

    <!-- RIGHT SIDEBAR -->
    <?php include '../includes/right_sidebar.php'; ?>

</div>

<script src="org.js"></script>
</body>
</html>