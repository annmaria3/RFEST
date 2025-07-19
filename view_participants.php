<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rolee'] !== 'organizer') {
    header("Location: loginn.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['rolee'];

// Check if event_id is provided
$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
if ($event_id <= 0) {
    die("<script>alert('No event specified.'); window.location.href='organizer_portal.php';</script>");
}

// Fetch event details
$event_query = "SELECT event_name, event_type, portal_id FROM events WHERE event_id = ?";
$stmt = $conn->prepare($event_query);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event_result = $stmt->get_result();
$event = $event_result->fetch_assoc();
$stmt->close();

if (!$event) {
    die("<script>alert('Event not found.'); window.location.href='organizer_portal.php';</script>");
}

// Fetch portal name
$portal_query = "SELECT portal_name FROM event_portals WHERE portal_id = ?";
$stmt = $conn->prepare($portal_query);
$stmt->bind_param("i", $event['portal_id']);
$stmt->execute();
$portal_result = $stmt->get_result();
$portal = $portal_result->fetch_assoc();
$portal_name = $portal['portal_name'];
$stmt->close();

// Fetch participants or teams
$participants = [];
if ($event['event_type'] === 'group') {
    $query = "SELECT group_name FROM groups WHERE event_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $participants[] = $row['group_name'];
    }
} else {
    $query = "SELECT u.name 
              FROM registrations r 
              JOIN users u ON r.user_id = u.user_id 
              WHERE r.event_id = ? AND r.payment_status = 'completed'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $participants[] = $row['name'];
    }
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participants - <?php echo htmlspecialchars($event['event_name']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        ul { list-style: none; padding: 0; }
        li { padding: 10px; border-bottom: 1px solid #ddd; }
        .back-btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; text-decoration: none; display: inline-block; margin-top: 20px; }
        .back-btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h2><?php echo htmlspecialchars($event['event_name']); ?> - <?php echo $event['event_type'] === 'group' ? 'Teams' : 'Participants'; ?></h2>
        <ul>
            <?php if (!empty($participants)): ?>
                <?php foreach ($participants as $participant): ?>
                    <li><?php echo htmlspecialchars($participant); ?></li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No <?php echo $event['event_type'] === 'group' ? 'teams' : 'participants'; ?> registered yet.</li>
            <?php endif; ?>
        </ul>
        <a href="organizer_events.php?portal=<?php echo urlencode($portal_name); ?>" class="back-btn">Back to Events</a>
    </div>
</body>
</html>