<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: loginn.php");
    exit();
}

// Check if user is a participant (student)
if ($_SESSION['rolee'] !== 'student') {
    header("Location: loginn.php");
    exit();
}

// Use session variables as needed
$user_id = $_SESSION['user_id'];
$role = $_SESSION['rolee'];

// Get portal name from the URL
$portal = isset($_GET['portal']) ? trim($_GET['portal']) : '';
if (empty($portal)) {
    die("<script>alert('No portal specified.'); window.location.href='dashboard.php';</script>");
}

// Fetch user details (unchanged)
$user_query = "SELECT users.name, 
                      COALESCE(clubandhouse.house, 'Not Assigned') AS house, 
                      COALESCE(clubandhouse.clubs, 'Not Assigned') AS club, 
                      COALESCE(activitypoints.total_points, 0) AS total_points 
               FROM users 
               LEFT JOIN clubandhouse ON users.user_id = clubandhouse.user_id 
               LEFT JOIN activitypoints ON users.user_id = activitypoints.student_id 
               WHERE users.user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

// Fetch portal ID (unchanged)
$query = "SELECT portal_id FROM event_portals WHERE portal_name = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $portal);
$stmt->execute();
$result = $stmt->get_result();
$portal_data = $result->fetch_assoc();
$stmt->close();

if (!$portal_data) {
    die("<script>alert('Invalid portal specified.'); window.location.href='dashboard.php';</script>");
}

$portal_id = $portal_data['portal_id'];

// Event filter logic (unchanged)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'upcoming';
$where_clause = "WHERE portal_id = ? AND status = 'approved' AND event_date >= CURDATE()";
switch($filter) {
    case 'past':
        $where_clause = "WHERE portal_id = ? AND status = 'approved' AND event_date < CURDATE()";
        break;
    case 'free':
        $where_clause .= " AND registration_fee = 0";
        break;
    case 'paid':
        $where_clause .= " AND registration_fee > 0";
        break;
    case 'certified':
        $where_clause .= " AND event_type = 'certified'";
        break;
    case 'group':
        $where_clause .= " AND event_type = 'group'";
        break;
    case 'individual':
        $where_clause .= " AND event_type = 'individual'";
        break;
    case 'virtual':
        $where_clause .= " AND is_virtual = 1";
        break;
    default:
        $where_clause = "WHERE portal_id = ? AND status = 'approved' AND event_date >= CURDATE()";
}

// Fetch events (unchanged)
$query = "SELECT event_id, event_name, event_date, start_time, end_time, venue, 
                 registration_fee, event_type, is_virtual, event_description 
          FROM events $where_clause ORDER BY event_date";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $portal_id);
$stmt->execute();
$result = $stmt->get_result();
$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}
$stmt->close();

// Fetch past events (unchanged)
$past_events = [];
$query = "SELECT event_id, event_name, event_date 
          FROM events 
          WHERE portal_id = ? AND event_date < CURDATE() 
          ORDER BY event_date DESC LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $portal_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $past_events[] = $row;
}
$stmt->close();

// Fetch notices (unchanged)
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
    <title><?php echo htmlspecialchars($portal); ?> Events</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Existing styles unchanged */
        .event-filters { margin: 20px 0; display: flex; flex-wrap: wrap; gap: 10px; }
        .filter-btn { padding: 8px 15px; background: #f0f0f0; border: 1px solid #ddd; border-radius: 5px; cursor: pointer; transition: all 0.3s; }
        .filter-btn.active { background: #003566; color: white; }
        .register-btn { background: #28a745 !important; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; }
        .register-btn:hover { background: #218838 !important; }
        .details-btn { background: #003566 !important; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; }
        .event-archive-list { list-style: none; padding: 0; }
        .event-archive-list li { padding: 8px 0; border-bottom: 1px solid #eee; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fefefe; margin: 10% auto; padding: 20px; border-radius: 8px; width: 60%; max-width: 800px; position: relative; }
        .close { position: absolute; right: 20px; top: 10px; font-size: 28px; font-weight: bold; cursor: pointer; }
        .event-tag { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 12px; margin-right: 5px; background: #e0e0e0; }
        .virtual-tag { background: #4CAF50; color: white; }
        .certified-tag { background: #2196F3; color: white; }
        .group-tag { background: #9C27B0; color: white; }
        .paid-tag { background: #FF9800; color: white; }
        .free-tag { background: #607D8B; color: white; }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar unchanged -->
        <aside class="sidebar user-sidebar">
            <div class="user-profile">
                <div class="user-info">
                    <h3>NAME: <?php echo htmlspecialchars($user['name']); ?></h3>
                    <p>HOUSE: <?php echo htmlspecialchars($user['house']); ?></p>
                    <p>CLUB: <?php echo htmlspecialchars($user['club']); ?></p>
                    <p>ACTIVITY POINTS: <?php echo $user['total_points']; ?></p>
                </div>
            </div>
            <div class="view-tickets">
                <h3>EVENT ARCHIVES</h3>
                <ul class="event-archive-list">
                    <?php if (!empty($past_events)): ?>
                        <?php foreach ($past_events as $event): ?>
                            <li>
                                <?php echo htmlspecialchars($event['event_name']); ?>
                                <br>
                                <small><?php echo htmlspecialchars($event['event_date']); ?></small>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No past events found</li>
                    <?php endif; ?>
                </ul>
            </div>
            <button class="logout-btn" onclick="window.location.href='loginn.php'">LOGOUT</button>
        </aside>

        <main class="main-content">
            <header class="header">
                <nav class="nav">
                    <h1><?php echo htmlspecialchars($portal); ?> EVENTS</h1>
                    <div class="nav-links">
                        <a href="dashboard.php">Back to Dashboard</a>
                    </div>
                </nav>
            </header>

            <!-- Event Filters unchanged -->
            <div class="event-filters">
                <button class="filter-btn <?php echo $filter === 'upcoming' ? 'active' : ''; ?>" 
                        onclick="window.location.href='?portal=<?php echo urlencode($portal); ?>&filter=upcoming'">
                    Upcoming Events
                </button>
                <button class="filter-btn <?php echo $filter === 'past' ? 'active' : ''; ?>" 
                        onclick="window.location.href='?portal=<?php echo urlencode($portal); ?>&filter=past'">
                    Past Events
                </button>
                <button class="filter-btn <?php echo $filter === 'free' ? 'active' : ''; ?>" 
                        onclick="window.location.href='?portal=<?php echo urlencode($portal); ?>&filter=free'">
                    Free Events
                </button>
                <button class="filter-btn <?php echo $filter === 'paid' ? 'active' : ''; ?>" 
                        onclick="window.location.href='?portal=<?php echo urlencode($portal); ?>&filter=paid'">
                    Paid Events
                </button>
                <button class="filter-btn <?php echo $filter === 'certified' ? 'active' : ''; ?>" 
                        onclick="window.location.href='?portal=<?php echo urlencode($portal); ?>&filter=certified'">
                    Certified Events
                </button>
                <button class="filter-btn <?php echo $filter === 'group' ? 'active' : ''; ?>" 
                        onclick="window.location.href='?portal=<?php echo urlencode($portal); ?>&filter=group'">
                    Group Events
                </button>
                <button class="filter-btn <?php echo $filter === 'individual' ? 'active' : ''; ?>" 
                        onclick="window.location.href='?portal=<?php echo urlencode($portal); ?>&filter=individual'">
                    Individual Events
                </button>
                <button class="filter-btn <?php echo $filter === 'virtual' ? 'active' : ''; ?>" 
                        onclick="window.location.href='?portal=<?php echo urlencode($portal); ?>&filter=virtual'">
                    Virtual Events
                </button>
            </div>

            <section class="event-dashboard">
                <div class="event-grid">
                    <?php if (!empty($events)): ?>
                        <?php foreach ($events as $event): ?>
                            <div class="event-card">
                                <div class="event-details">
                                    <h3><?php echo htmlspecialchars($event['event_name']); ?></h3>
                                    <div class="event-tags">
                                        <span class="event-tag <?php echo $event['event_type'] === 'certified' ? 'certified-tag' : ''; ?>">
                                            <?php echo ucfirst($event['event_type']); ?>
                                        </span>
                                        <?php if ($event['is_virtual']): ?>
                                            <span class="event-tag virtual-tag">Virtual</span>
                                        <?php endif; ?>
                                        <span class="event-tag <?php echo $event['registration_fee'] > 0 ? 'paid-tag' : 'free-tag'; ?>">
                                            <?php echo $event['registration_fee'] > 0 ? '₹'.$event['registration_fee'] : 'Free'; ?>
                                        </span>
                                    </div>
                                    <p><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
                                    <p><strong>Time:</strong> <?php echo htmlspecialchars($event['start_time']); ?> - <?php echo htmlspecialchars($event['end_time']); ?></p>
                                    <p><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue'] ?? 'TBA'); ?></p>
                                    <div class="buttons">
                                        <?php if ($event['registration_fee'] == 0): ?>
                                            <a href="register.php?event_id=<?php echo $event['event_id']; ?>&user_id=<?php echo $user_id; ?>&portal=<?php echo urlencode($portal); ?>" 
                                               class="register-btn">Register</a>
                                        <?php else: ?>
                                            <a href="register.php?event_id=<?php echo $event['event_id']; ?>&user_id=<?php echo $user_id; ?>&portal=<?php echo urlencode($portal); ?>&amount=<?php echo $event['registration_fee']; ?>" 
                                               class="register-btn">Register (₹<?php echo $event['registration_fee']; ?>)</a>
                                        <?php endif; ?>
                                        <button class="details-btn" onclick="showEventDetails(<?php echo $event['event_id']; ?>)">Details</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-events">No events found matching your criteria.</p>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Image Gallery unchanged -->
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
                    </div>
                </div>
            </section>
        </main>

        <!-- Notice Board unchanged -->
        <aside class="sidebar notice-board">
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

    <div id="eventDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">×</span>
            <div id="eventDetailsContent"></div>
        </div>
    </div>

    <script>
        function showEventDetails(eventId) {
            fetch('get_event_details.php?event_id=' + eventId)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('eventDetailsContent').innerHTML = data;
                    document.getElementById('eventDetailsModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('eventDetailsContent').innerHTML = '<p>Error loading event details.</p>';
                    document.getElementById('eventDetailsModal').style.display = 'block';
                });
        }

        function closeModal() {
            document.getElementById('eventDetailsModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('eventDetailsModal')) {
                closeModal();
            }
        }

        // Handle messages from register.php as popups
        <?php if (isset($_GET['error'])): ?>
            alert('Error: <?php echo addslashes($_GET['error']); ?>');
            window.history.replaceState({}, document.title, '?portal=<?php echo urlencode($portal); ?>&filter=<?php echo urlencode($filter); ?>');
        <?php elseif (isset($_GET['success'])): ?>
            alert('Success: <?php echo addslashes($_GET['success']); ?>');
            window.history.replaceState({}, document.title, '?portal=<?php echo urlencode($portal); ?>&filter=<?php echo urlencode($filter); ?>');
        <?php endif; ?>
    </script>
</body>
</html>