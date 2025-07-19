<?php
include 'db_connect.php';

// Hardcoded user_id for now, same as ind.php
$user_id = 'u2203025'; // Replace with $_SESSION['user_id'] when session is active

// Get event_id from GET request
$event_id = $_GET['event_id'] ?? null;

if (!$event_id) {
    echo "<p>Error: No event ID provided.</p>";
    exit();
}

// Fetch event details
$query = "SELECT event_name, event_date, start_time, end_time, venue, event_description, 
                 registration_fee, event_type, is_virtual 
          FROM events 
          WHERE event_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();

if (!$event) {
    echo "<p>Error: Event not found.</p>";
    exit();
}

// Check if user is registered
$query = "SELECT COUNT(*) FROM registrations WHERE user_id = ? AND event_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $user_id, $event_id);
$stmt->execute();
$is_registered = $stmt->get_result()->fetch_row()[0] > 0;
$stmt->close();

$conn->close();
?>

<h2 style="color:black";><?php echo htmlspecialchars($event['event_name']); ?></h2>
<p style="color:black";><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
<p style="color:black"; ><strong>Time:</strong> <?php echo htmlspecialchars($event['start_time']); ?> - <?php echo htmlspecialchars($event['end_time']); ?></p>
<p style="color:black"><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue'] ?? 'TBA'); ?></p>
<p style="color:black"><strong>Type:</strong> <?php echo ucfirst(htmlspecialchars($event['event_type'])); ?></p>
<p style="color:black"><strong>Fee:</strong> <?php echo $event['registration_fee'] > 0 ? '₹' . htmlspecialchars($event['registration_fee']) : 'Free'; ?></p>
<p style="color:black"><strong>Mode:</strong> <?php echo $event['is_virtual'] ? 'Virtual' : 'In-Person'; ?></p>
<p style="color:black"><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($event['event_description'] ?? 'No description available.')); ?></p>
<p style="color:black"><strong>Registration Status:</strong> <?php echo $is_registered ? 'Already Registered' : 'Not Registered'; ?></p>