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
$query = "SELECT event_name, event_type, event_date, start_time, end_time, venue, registration_fee, 
                 is_virtual, max_participants, portal_id 
          FROM events WHERE event_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();

if (!$event) {
    die("<script>alert('Event not found.'); window.location.href='organizer_portal.php';</script>");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_name = trim($_POST['event_name']);
    $event_type = trim($_POST['event_type']);
    $event_date = trim($_POST['event_date']);
    $start_time = trim($_POST['start_time']);
    $end_time = trim($_POST['end_time']);
    $venue = trim($_POST['venue']);
    $registration_fee = (float)$_POST['registration_fee'];
    $is_virtual = isset($_POST['is_virtual']) ? 1 : 0;
    $max_participants = (int)$_POST['max_participants'];

    $update_query = "UPDATE events SET event_name = ?, event_type = ?, event_date = ?, start_time = ?, 
                     end_time = ?, venue = ?, registration_fee = ?, is_virtual = ?, max_participants = ?
                     WHERE event_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssssssiii", $event_name, $event_type, $event_date, $start_time, $end_time, 
                      $venue, $registration_fee, $is_virtual, $max_participants, $event_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Event updated successfully!'); window.location.href='organizer_events.php?portal=" . urlencode($event['portal_id']) . "';</script>";
    } else {
        echo "<script>alert('Error updating event.');</script>";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .form-container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        label { display: block; margin: 10px 0 5px; }
        input, select { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #218838; }
        .back-btn { background: #007bff; }
        .back-btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Event</h2>
        <form method="POST">
            <label>Event Name</label>
            <input type="text" name="event_name" value="<?php echo htmlspecialchars($event['event_name']); ?>" required>
            
            <label>Event Type</label>
            <select name="event_type" required>
                <option value="individual" <?php echo $event['event_type'] === 'individual' ? 'selected' : ''; ?>>Individual</option>
                <option value="group" <?php echo $event['event_type'] === 'group' ? 'selected' : ''; ?>>Group</option>
                <option value="certified" <?php echo $event['event_type'] === 'certified' ? 'selected' : ''; ?>>Certified</option>
            </select>
            
            <label>Event Date</label>
            <input type="date" name="event_date" value="<?php echo htmlspecialchars($event['event_date']); ?>" required>
            
            <label>Start Time</label>
            <input type="time" name="start_time" value="<?php echo htmlspecialchars($event['start_time']); ?>" required>
            
            <label>End Time</label>
            <input type="time" name="end_time" value="<?php echo htmlspecialchars($event['end_time']); ?>" required>
            
            <label>Venue</label>
            <input type="text" name="venue" value="<?php echo htmlspecialchars($event['venue']); ?>" required>
            
            <label>Registration Fee</label>
            <input type="number" name="registration_fee" step="0.01" value="<?php echo htmlspecialchars($event['registration_fee']); ?>" required>
            
            <label>Is Virtual</label>
            <input type="checkbox" name="is_virtual" <?php echo $event['is_virtual'] ? 'checked' : ''; ?>>
            
            <label>Max Participants</label>
            <input type="number" name="max_participants" value="<?php echo htmlspecialchars($event['max_participants']); ?>" required>
            
            <button type="submit">Update Event</button>
            <a href="organizer_events.php?portal=<?php echo urlencode($event['portal_id']); ?>" class="back-btn">Back</a>
        </form>
    </div>
</body>
</html>