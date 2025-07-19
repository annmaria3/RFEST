
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

// Fetch user details
$query = "
    SELECT users.name, users.email, 
           COALESCE(clubandhouse.house, 'Not Assigned') AS house, 
           COALESCE(clubandhouse.clubs, 'Not Assigned') AS club, 
           COALESCE(activitypoints.total_points, 0) AS total_points 
    FROM users 
    LEFT JOIN clubandhouse ON users.user_id = clubandhouse.user_id 
    LEFT JOIN activitypoints ON users.user_id = activitypoints.student_id 
    WHERE users.user_id = ?
    GROUP BY users.user_id, clubandhouse.house, clubandhouse.clubs
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$name = $user['name'];
$email = $user['email'];
$house = $user['house'];
$club = $user['club'];
$activity_points = $user['total_points'];
$stmt->close();

// Fetch certificate count
$cert_count = 0;
$query = "SELECT COUNT(*) AS cert_count FROM certificates WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $cert_count = $row['cert_count'];
}
$stmt->close();

// Fetch event portals
$event_portals = [];
$query = "SELECT portal_name FROM event_portals";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $event_portals[] = $row['portal_name'];
}

// Fetch notices
$notices = [];
$query = "SELECT title, description FROM noticeboard ORDER BY creation_date DESC LIMIT 5";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $notices[] = $row;
}

// Fetch popular events
$popular_events = [];
$query = "SELECT e.event_id, e.event_name, e.current_participants, p.portal_name 
          FROM events e
          JOIN event_portals p ON e.portal_id = p.portal_id
          ORDER BY e.current_participants DESC LIMIT 5";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $popular_events[] = [
        "id" => $row['event_id'],
        "name" => $row['event_name'],
        "participants" => $row['current_participants'],
        "portal" => $row['portal_name']
    ];
}

// Fetch upcoming registered events
$upcoming_events = [];
$query = "SELECT e.event_id, e.event_name, e.event_date, e.start_time 
          FROM registrations r 
          JOIN events e ON r.event_id = e.event_id 
          WHERE r.user_id = ? AND e.event_date >= CURDATE()
          ORDER BY e.event_date ASC LIMIT 3";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $upcoming_events[] = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFEST - Student Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Add styling for logout button near gallery */
        .image-gallery {
            position: relative; /* Allows absolute positioning of logout button */
            margin-top: 20px;
        }
        .logout-btn {
            position: absolute;
            top: 0;
            right: 0;
            background: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .logout-btn:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar user-sidebar">
            <div style="background-color:black;" class="user-profile">
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($name); ?></h3>
                    <p>HOUSE: <?php echo htmlspecialchars($house); ?></p>
                    <p>CLUB: <?php echo htmlspecialchars($club); ?></p>
                    <p>ACTIVITY POINTS: <?php echo $activity_points; ?></p>
                    <p>CERTIFICATES: <?php echo $cert_count; ?></p>
                </div>
            </div>
            
            <!-- Navigation buttons -->
            <div class="view-tickets">
                <h3>MY TICKETS</h3>
                <button class="tickets-btn" onclick="window.location.href='my_tickets.php'">View Tickets</button>
            </div>
            
            <div class="view-tickets">
                <h3>MY CERTIFICATES</h3>
                <button class="tickets-btn" onclick="window.location.href='my_certificates.php'">View Certificates</button>
            </div>
            
            <div class="view-tickets">
                <h3>LEADERBOARD</h3>
                <h3>HOUSE</h3>
                <button class="tickets-btn" onclick="window.location.href='house_leaderboard.php'">View Leaderboard</button>
                <h3>STUDENT</h3>
                <button class="tickets-btn" onclick="window.location.href='leaderboard_display.php'">View Leaderboard</button>
            </div>
            
            <div class="view-tickets">
                <h3>FEEDBACK</h3>
                <button class="tickets-btn" onclick="window.location.href='feedback_form.php'">Give Feedback</button>
            </div>
            
            <div class="view-tickets">
                <h3>ACTIVITY FORM</h3>
                <button class="tickets-btn" onclick="window.location.href='activity_submission_form.php'">Submit Activity</button>
            </div>

            <div class="view-tickets">
                <h3>POLLS</h3>
                <button class="tickets-btn" onclick="window.location.href='view_polls.php'">View Polls</button>
            </div>
            <!-- Logout button removed from here -->
        </aside>
        
        <main class="main-content">
            <header class="header">
                <nav class="nav">
                    <h1 style="color: black;">RFEST</h1>
                    <div class="nav-links">
                        <a href="#" class="active">Home</a>
                        <a href="gallery.php">Gallery</a>
                    </div>
                    <div class="nav-controls">
                        <button class="theme-toggle" onclick="window.location.href='calendar_integration.html'">Calendar</button>
                        <button class="help-btn">?</button>
                    </div>
                </nav>
            </header>
            
            <section class="event-dashboard">
                <div class="event-grid">
                    <?php foreach ($event_portals as $portal) { ?>
                        <a href="ind.php?portal=<?php echo urlencode($portal); ?>" class="event-link">
                            <div class="event-card"><?php echo htmlspecialchars($portal); ?></div>
                        </a>
                    <?php } ?>
                </div>
            </section>
            
            <section class="recommended-events">
                <h2 style="color: black;">FEATURED EVENTS</h2>
                <div class="event-carousel">
                    <button class="carousel-btn prev">←</button>
                    <div class="carousel-items">
                        <?php foreach ($popular_events as $event) { ?>
                            <div class="carousel-item" onclick="window.location.href='ind.php?portal=<?php echo urlencode($event['portal']); ?>&event_id=<?php echo $event['id']; ?>'">
                                <h3><?php echo htmlspecialchars($event['name']); ?></h3>
                                <p>Participants: <?php echo htmlspecialchars($event['participants']); ?></p>
                            </div>
                        <?php } ?> 
                    </div>
                    <button class="carousel-btn next">→</button>
                </div>
            </section>
            
            <section class="upcoming-events-carousel">
                <h2 style="color: black;">YOUR UPCOMING EVENTS</h2>
                <div class="upcoming-carousel-container">
                    <button class="upcoming-carousel-btn prev">←</button>
                    <div class="upcoming-carousel-items">
                        <?php if (!empty($upcoming_events)): ?>
                            <?php foreach ($upcoming_events as $event): ?>
                                <div class="upcoming-carousel-item">
                                    <div class="upcoming-event-card">
                                        <h4 style="color: black;"><?php echo htmlspecialchars($event['event_name']); ?></h4>
                                        <p style="color: black;"><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
                                        <p style="color: black;"><strong>Time:</strong> <?php echo htmlspecialchars($event['start_time']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="upcoming-carousel-item">
                                <div class="upcoming-event-card">
                                    <h4>No Upcoming Events</h4>
                                    <p>You haven't registered for any upcoming events yet.</p>
                                    <p>Check out the featured events to participate!</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button class="upcoming-carousel-btn next">→</button>
                </div>
            </section>
            
            <section class="image-gallery">
                <h2 style="color: black;">Image Gallery</h2>
                <!-- Logout button moved here -->
                <button class="logout-btn" onclick="window.location.href='loginn.php'">LOGOUT</button>
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
        
        <aside class="sidebar notice-board">
            <h2>NOTICE BOARD</h2>
            <div class="notices">
                <?php foreach ($notices as $notice) { ?>
                    <div class="notice">
                        <h3><?php echo htmlspecialchars($notice['title']); ?></h3>
                        <p><?php echo htmlspecialchars($notice['description']); ?></p>
                    </div>
                <?php } ?>
            </div>
        </aside>
    </div>

    <script>
        // Carousel functionality for featured events
        const featuredCarousel = document.querySelector('.carousel-items');
        const featuredItems = document.querySelectorAll('.carousel-item');
        const featuredItemWidth = featuredItems[0].offsetWidth + 20;
        let featuredPosition = 0;
        
        document.querySelector('.carousel-btn.next').addEventListener('click', function() {
            if (featuredPosition > -((featuredItems.length - 1) * featuredItemWidth)) {
                featuredPosition -= featuredItemWidth;
                featuredCarousel.style.transform = `translateX(${featuredPosition}px)`;
            }
        });
        
        document.querySelector('.carousel-btn.prev').addEventListener('click', function() {
            if (featuredPosition < 0) {
                featuredPosition += featuredItemWidth;
                featuredCarousel.style.transform = `translateX(${featuredPosition}px)`;
            }
        });
        
        // Carousel functionality for upcoming events
        const upcomingCarousel = document.querySelector('.upcoming-carousel-items');
        const upcomingItems = document.querySelectorAll('.upcoming-carousel-item');
        const upcomingItemWidth = upcomingItems[0].offsetWidth;
        let upcomingPosition = 0;
        
        document.querySelector('.upcoming-carousel-btn.next').addEventListener('click', function() {
            if (upcomingPosition > -((upcomingItems.length - 1) * upcomingItemWidth)) {
                upcomingPosition -= upcomingItemWidth;
                upcomingCarousel.style.transform = `translateX(${upcomingPosition}px)`;
            }
        });
        
        document.querySelector('.upcoming-carousel-btn.prev').addEventListener('click', function() {
            if (upcomingPosition < 0) {
                upcomingPosition += upcomingItemWidth;
                upcomingCarousel.style.transform = `translateX(${upcomingPosition}px)`;
            }
        });
    </script>
</body>
</html>
