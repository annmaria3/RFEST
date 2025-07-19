<?php
session_start();
include 'db_connect.php';

$user_id = 'org123'; // Replace with $_SESSION['user_id'] after login integration

// Fetch organizer details
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

// Check if Bharatam-related organization
$organization_name = $organizer['organization_name'];
$position = $organizer['position'] ?? 'Committee Member'; // Default to Committee Member
$portal_name = 'Bharatam';
$is_bharatam = in_array($organization_name, ['Bharatam', 'Rajputs', 'Mughals', 'Spartans', 'Vikings', 'Aryans']);

if (!$is_bharatam) {
    die("<script>alert('This dashboard is for Bharatam organizers only.'); window.location.href='organizer_dashboard.php';</script>");
}

// Fetch portal_id
$portal_query = "SELECT portal_id FROM event_portals WHERE portal_name = ?";
$stmt = $conn->prepare($portal_query);
$stmt->bind_param("s", $portal_name);
$stmt->execute();
$portal_result = $stmt->get_result();
$portal_data = $portal_result->fetch_assoc();
$stmt->close();

if (!$portal_data) {
    die("<script>alert('Bharatam portal not found.'); window.location.href='loginn.php';</script>");
}
$portal_id = $portal_data['portal_id'];

// Fetch events for Bharatam portal
$event_query = "SELECT event_id, event_name, event_type, event_date, start_time, end_time, venue, 
                       registration_fee, is_virtual, max_participants, current_participants
                FROM events 
                WHERE portal_id = ? AND status = 'approved' 
                ORDER BY event_date ASC";
$stmt = $conn->prepare($event_query);
$stmt->bind_param("i", $portal_id);
$stmt->execute();
$event_result = $stmt->get_result();
$events = [];
while ($row = $event_result->fetch_assoc()) {
    $events[] = $row;
}
$stmt->close();

// Fetch notices
$notices = [];
$notice_query = "SELECT title, description FROM noticeboard ORDER BY creation_date DESC LIMIT 5";
$result = $conn->query($notice_query);
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
    <title>RFEST - Bharatam Dashboard</title>
    <link rel="stylesheet" href="styleor1.css">
    <style>
        .dashboard { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: #f4f4f4; padding: 20px; color: #333; }
        .main-content { flex: 1; padding: 20px; }
        .notice-board { width: 250px; background: #f4f4f4; padding: 20px; }
        .event-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .event-card { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .event-card:hover { box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .add-btn, .edit-btn, .cert-btn, .select-btn { background: #28a745; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; text-decoration: none; display: inline-block; }
        .add-btn:hover, .edit-btn:hover, .cert-btn:hover, .select-btn:hover { background: #218838; }
        .edit-btn { background: #007bff; }
        .edit-btn:hover { background: #0056b3; }
        .cert-btn { background: #6f42c1; }
        .cert-btn:hover { background: #5a32a3; }
        .select-btn { background: #fd7e14; }
        .select-btn:hover { background: #e06b12; }
        .logout-btn { background: #dc3545; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; width: 100%; margin-top: 20px; }
        .logout-btn:hover { background: #c82333; }
        .event-details p { margin: 5px 0; }
        .action-buttons { margin-top: 20px; }
        .image-gallery { margin-top: 20px; }
        .gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; }
        .gallery img { width: 100%; height: auto; border-radius: 5px; }
    </style>
</head>
<body>

<div class="dashboard">

    <!-- Left Sidebar -->
    <aside class="sidebar">
        <div class="organizer-profile">
            <div class="organizer-info">
                <h3><?php echo htmlspecialchars($organizer['name']); ?></h3>
                <p>Position: <?php echo htmlspecialchars($position); ?></p>
            </div>
        </div>
        <a href="upload_image.php" class="add-btn">Upload Image to Gallery</a>
        <button class="logout-btn" onclick="window.location.href='loginn.php'">Logout</button>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="header">
            <h1>Bharatam - Organizer Dashboard</h1>
        </header>

        <section class="event-dashboard">
            <!-- Add Event (House Leaders only) -->
            <?php if ($position === 'House Leader'): ?>
                <a href="add_event.php?organizer_id=<?php echo $user_id; ?>&portal_id=<?php echo $portal_id; ?>" class="add-btn">Add New Event</a>
            <?php endif; ?>

            <!-- Event List -->
            <div class="event-grid">
                <?php if (!empty($events)): ?>
                    <?php foreach ($events as $event): ?>
                        <div class="event-card">
                            <div class="event-details">
                                <h3><?php echo htmlspecialchars($event['event_name']); ?></h3>
                                <p><strong>Type:</strong> <?php echo htmlspecialchars($event['event_type']); ?></p>
                                <p><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
                                <p><strong>Time:</strong> <?php echo htmlspecialchars($event['start_time']); ?> - <?php echo htmlspecialchars($event['end_time']); ?></p>
                                <p><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue'] ?? 'TBA'); ?></p>
                                <p><strong>Fee:</strong> <?php echo $event['registration_fee'] > 0 ? '₹' . $event['registration_fee'] : 'Free'; ?></p>
                                <p><strong>Participants:</strong> <?php echo $event['current_participants'] . '/' . $event['max_participants']; ?></p>
                                <p><strong>Virtual:</strong> <?php echo $event['is_virtual'] ? 'Yes' : 'No'; ?></p>
                                <div class="buttons">
                                    <a href="edit_event.php?event_id=<?php echo $event['event_id']; ?>" class="edit-btn">Edit</a>
                                    <a href="generate_certificates.php?event_id=<?php echo $event['event_id']; ?>" class="cert-btn">Generate Certificates</a>
                                    <?php if ($position === 'House Leader'): ?>
                                        <a href="select_participants.php?event_id=<?php echo $event['event_id']; ?>" class="select-btn">Select Participants</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No events found in Bharatam portal.</p>
                <?php endif; ?>
            </div>

            <!-- Action Buttons (House Leaders only) -->
            <?php if ($position === 'House Leader'): ?>
                <div class="action-buttons">
                    <a href="add_notice.php" class="add-btn">Add Notice</a>
                    <a href="create_poll.php" class="add-btn">Add Poll</a>
                </div>
            <?php endif; ?>

            <!-- Image Gallery -->
            <section class="image-gallery">
                <h2>Image Gallery</h2>
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
            </section>
        </section>
    </main>

    <!-- Right Sidebar: Notice Board -->
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
    </aside>

</div>

</body>
</html>