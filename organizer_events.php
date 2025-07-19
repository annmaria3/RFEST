<?php
session_start();
include 'db_connect.php';

$user_id = 'org123'; // Temporary hardcoded value

$portal_name = isset($_GET['portal']) ? trim($_GET['portal']) : '';
if (empty($portal_name)) {
    die("<script>alert('No portal specified.'); window.location.href='organizer_portal.php';</script>");
}

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

if (!$organizer) {
    die("<script>alert('Organizer details not found.'); window.location.href='loginn.php';</script>");
}

$portal_query = "SELECT portal_id FROM event_portals WHERE portal_name = ?";
$stmt = $conn->prepare($portal_query);
$stmt->bind_param("s", $portal_name);
$stmt->execute();
$portal_result = $stmt->get_result();
$portal_data = $portal_result->fetch_assoc();
$stmt->close();

if (!$portal_data) {
    die("<script>alert('Invalid portal specified.'); window.location.href='organizer_portal.php';</script>");
}
$portal_id = $portal_data['portal_id'];

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
    <title><?php echo htmlspecialchars($portal_name); ?> - Organizer Events</title>
    <link rel="stylesheet" href="styleor1.css">
    <style>
        .dashboard { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: #f4f4f4; padding: 20px; color: #333; }
        .main-content { flex: 1; padding: 20px; }
        .notice-board { width: 250px; background: #f4f4f4; padding: 20px; }
        .event-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .event-card { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .event-card:hover { box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .add-btn, .edit-btn, .participants-btn, .cert-btn { background: #28a745; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; text-decoration: none; display: inline-block; }
        .add-btn:hover, .edit-btn:hover, .participants-btn:hover, .cert-btn:hover { background: #218838; }
        .edit-btn { background: #007bff; }
        .edit-btn:hover { background: #0056b3; }
        .participants-btn { background: #ffc107; color: #333; }
        .participants-btn:hover { background: #e0a800; }
        .cert-btn { background: #6f42c1; }
        .cert-btn:hover { background: #5a32a3; }
        .logout-btn { background: #dc3545; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; width: 100%; margin-top: 20px; }
        .logout-btn:hover { background: #c82333; }
        .event-details p { margin: 5px 0; }
    </style>
</head>
<body>

<div class="dashboard">

    <aside class="sidebar">
        <div class="organizer-profile">
            <div class="organizer-info">
                <h3><?php echo htmlspecialchars($organizer['name']); ?></h3>
                <p>Organization: <?php echo htmlspecialchars($organizer['organization_name'] ?? 'Not Assigned'); ?></p>
                <p>Position: <?php echo htmlspecialchars($organizer['position'] ?? 'Not Assigned'); ?></p>
            </div>
        </div>
        <a href="upload_image.php" class="add-btn">Upload Image to Gallery</a>
        <button class="logout-btn" onclick="window.location.href='loginn.php'">Logout</button>
    </aside>

    <main class="main-content">
        <header class="header">
            <nav class="nav">
                <h1><?php echo htmlspecialchars($portal_name); ?> Events</h1>
                <div class="nav-links">
                    <a href="organizer_portal.php">Back to Dashboard</a>
                </div>
            </nav>
        </header>

        <section class="event-dashboard">
            <a href="add_event.php?organizer_id=<?php echo $user_id; ?>&portal_id=<?php echo $portal_id; ?>" class="add-btn">Add New Event</a>
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
                                    <a href="view_participants.php?event_id=<?php echo $event['event_id']; ?>" class="participants-btn">
                                        <?php echo $event['event_type'] === 'group' ? 'View Teams' : 'View Participants'; ?>
                                    </a>
                                    <a href="generate_certificates.php?event_id=<?php echo $event['event_id']; ?>" class="cert-btn">Generate Certificates</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No events found in this portal.</p>
                <?php endif; ?>
            </div>
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

</body>
</html>