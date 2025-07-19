<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
$name = "Guest";
$points_balance = 0;
$archives = [];

if ($user_id) {
    // Get user name from users table
    $stmt = $conn->prepare("SELECT name FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($name);
    $stmt->fetch();
    $stmt->close();

    // Get points from activitypoints table
    $point_stmt = $conn->prepare("SELECT points_balance FROM activitypoints WHERE user_id = ?");
    $point_stmt->bind_param("i", $user_id);
    $point_stmt->execute();
    $point_stmt->bind_result($points_balance);
    $point_stmt->fetch();
    $point_stmt->close();

    // Fetch past events
    $event_stmt = $conn->prepare("SELECT event_name FROM events WHERE event_date < NOW()");
    $event_stmt->execute();
    $result = $event_stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $archives[] = $row['event_name'];
    }
    $event_stmt->close();
}
?>

<link rel="stylesheet" href="sidebar.css">

<aside class="sidebar user-sidebar">
    <div class="user-profile">
        <div class="avatar"></div>
        <div class="user-info">
            <h3>NAME: <?php echo htmlspecialchars($name); ?></h3>
            <p>ACTIVITY POINTS: <?php echo htmlspecialchars($points_balance); ?></p>
        </div>
    </div>

    <div class="archives">
        <h3>EVENT ARCHIVES</h3>
        <ul class="archive-list">
            <?php if (!empty($archives)): ?>
                <?php foreach ($archives as $event): ?>
                    <li><?php echo htmlspecialchars($event); ?></li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No Past Events</li>
            <?php endif; ?>
        </ul>
    </div>

    <button class="logout-btn" onclick="window.location.href='logout.php';">LOGOUT</button>
</aside>
