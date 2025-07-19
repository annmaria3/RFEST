<?php
session_start();
include 'db_connect.php';

$user_id = 'org123'; // Temporary hardcoded value

$org_query = "SELECT u.name, op.organization_name, op.position
              FROM users u
              LEFT JOIN organizers_position op ON u.user_id = op.user_id
              WHERE u.user_id = ?";
$stmt = $conn->prepare($org_query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$org_result = $stmt->get_result();
$organizer = $org_result->fetch_assoc();
$stmt->close();

if (!$organizer || !$organizer['organization_name']) {
    die("<script>alert('Organizer details not found.'); window.location.href='loginn.php';</script>");
}

$organization_name = $organizer['organization_name'];
$portal_name = '';
switch ($organization_name) {
    case 'Cyberblitz':
        $portal_name = 'Cyberblitz';
        break;
    case 'Bharatam':
    case 'Rajputs':
    case 'Mughals':
    case 'Spartans':
    case 'Vikings':
    case 'Aryans':
        $portal_name = 'Bharatam';
        break;
    default:
        die("<script>alert('No matching portal for this organization.'); window.location.href='dashboard.php';</script>");
}

$portals = [];
$portal_query = "SELECT portal_id, portal_name FROM event_portals WHERE portal_name = ? LIMIT 1";
$stmt = $conn->prepare($portal_query);
$stmt->bind_param("s", $portal_name);
$stmt->execute();
$portal_result = $stmt->get_result();
if ($portal_result->num_rows > 0) {
    $portals[] = $portal_result->fetch_assoc();
} else {
    die("<script>alert('Portal not found for this organization.'); window.location.href='dashboard.php';</script>");
}
$stmt->close();

$notices = [];
$query = "SELECT title, description FROM noticeboard ORDER BY creation_date DESC LIMIT 5";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $notices[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFEST - Organizer Dashboard</title>
    <link rel="stylesheet" href="styleor1.css">
    <style>
        .dashboard { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: #f4f4f4; padding: 20px; color: #333; } /* Darker text color */
        .main-content { flex: 1; padding: 20px; }
        .notice-board { width: 250px; background: #f4f4f4; padding: 20px; }
        .event-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .event-card { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; text-decoration: none; color: #333; }
        .event-card:hover { box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .add-btn { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 10px 0; display: block; text-decoration: none; text-align: center; }
        .add-btn:hover { background: #218838; }
        .logout-btn { background: #dc3545; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; width: 100%; margin-top: 20px; }
        .logout-btn:hover { background: #c82333; }
        .organizer-info h3, .organizer-info p { margin: 10px 0; }
    </style>
</head>
<body>

<div class="dashboard">

    <!-- Left Sidebar: Organizer Info with New Option -->
    <aside class="sidebar">
        <div class="organizer-profile">
            <div class="organizer-info">
                <h3><?php echo htmlspecialchars($organizer['name']); ?></h3>
                <p>Organization: <?php echo htmlspecialchars($organizer['organization_name'] ?? 'Not Assigned'); ?></p>
                <p>Position: <?php echo htmlspecialchars($organizer['position'] ?? 'Not Assigned'); ?></p>
            </div>
        </div>
        <a href="upload_image.php" class="add-btn">Upload Image to Gallery</a>
        <a href="generate_certificates.php" class="add-btn">Generate Certificates</a>
        <button class="logout-btn" onclick="window.location.href='loginn.php'">Logout</button>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="header">
            <nav class="nav">
                <h1>RFEST Organizer Dashboard</h1>
            </nav>
        </header>

        <section class="event-dashboard">
            <a href="add_event.php?organizer_id=<?php echo $user_id; ?>&portal_name=<?php echo urlencode($portal_name); ?>" class="add-btn">Add New Event</a>
            <div class="event-grid">
                <?php if (!empty($portals)): ?>
                    <?php foreach ($portals as $portal): ?>
                        <a href="organizer_events.php?portal=<?php echo urlencode($portal['portal_name']); ?>" class="event-card">
                            <h3><?php echo htmlspecialchars($portal['portal_name']); ?></h3>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No matching portal available.</p>
                <?php endif; ?>
            </div>

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

    <aside class="notice-board">
        <h2>NOTICE BOARD</h2>
        <div class="notices">
            <?php if (!empty($notices)): ?>
                <?php foreach ($notices as $notice): ?>
                    <div class="notice">
                        <h3><?php echo htmlspecialchars($notice['title']); ?></h3>
                        <p><?php echo htmlspecialchars($notice['description']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No new notices.</p>
            <?php endif; ?>
        </div>
        <button class="add-btn" onclick="window.location.href='add_notice.php'">Add Notice</button>
        <button class="add-btn" onclick="window.location.href='create_poll.php'">Add Poll</button>
    </aside>

</div>

<script src="org.js"></script>
</body>
</html>