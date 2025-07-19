<?php
session_start();
include 'db_connect.php';

$user_id = 'org123'; // Temporary user_id

// Fetch all events (no portal filtering for simplicity)
$events_query = "SELECT event_id, event_name FROM events WHERE status = 'approved'";
$events_result = $conn->query($events_query);
$events = [];
while ($row = $events_result->fetch_assoc()) {
    $events[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = (int)$_POST['event_id'];
    $question = trim($_POST['question']);
    $options = trim($_POST['options']); // Comma-separated
    $created_at = date('Y-m-d H:i:s');

    $insert_query = "INSERT INTO polls (event_id, question, options, created_by, created_at) 
                     VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("issss", $event_id, $question, $options, $user_id, $created_at);
    
    if ($stmt->execute()) {
        echo "<script>alert('Poll created successfully!'); window.location.href='organizer_events.php?portal=Cyberblitz';</script>";
    } else {
        echo "<script>alert('Error creating poll: " . $stmt->error . "');</script>";
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
    <title>Create Poll</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .form-container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        label { display: block; margin: 10px 0 5px; }
        input, select, textarea { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #218838; }
        .back-btn { background: #007bff; }
        .back-btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Create New Poll</h2>
        <form method="POST">
            <label>Select Event</label>
            <select name="event_id" required>
                <option value="">-- Select Event --</option>
                <?php foreach ($events as $event): ?>
                    <option value="<?php echo $event['event_id']; ?>"><?php echo htmlspecialchars($event['event_name']); ?></option>
                <?php endforeach; ?>
            </select>
            
            <label>Poll Question</label>
            <input type="text" name="question" required>
            
            <label>Options (comma-separated, e.g., Yes,No,Maybe)</label>
            <textarea name="options" rows="3" required></textarea>
            
            <button type="submit">Create Poll</button>
            <a href="organizer_events.php?portal=Cyberblitz" class="back-btn">Back</a>
        </form>
    </div>
</body>
</html>