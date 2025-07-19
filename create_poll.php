<?php
session_start();
include 'db_connect.php';

$user_id = 'org123'; // Replace with $_SESSION['user_id'] when login is implemented

// Fetch approved Bharatam events (assuming polls are Bharatam-specific; adjust if needed)
$events_query = "
    SELECT e.event_id, e.event_name 
    FROM events e 
    JOIN event_portals ep ON e.portal_id = ep.portal_id 
    WHERE e.status = 'approved' AND ep.portal_name = 'Bharatam'";
$events_result = $conn->query($events_query);
$events = [];
while ($row = $events_result->fetch_assoc()) {
    $events[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = (int)$_POST['event_id'];
    $question = trim($_POST['question']);
    $options_input = trim($_POST['options']); // Comma-separated
    $created_at = date('Y-m-d H:i:s');

    // Validate inputs
    if (empty($question) || empty($options_input)) {
        echo "<script>alert('Please fill in all fields.');</script>";
    } else {
        // Start transaction to ensure data consistency
        $conn->begin_transaction();

        try {
            // Insert poll
            $insert_poll_query = "INSERT INTO polls (event_id, title, created_by, created_at) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_poll_query);
            $stmt->bind_param("isss", $event_id, $question, $user_id, $created_at);
            $stmt->execute();
            $poll_id = $conn->insert_id; // Get the newly created poll ID
            $stmt->close();

            // Split options and insert into poll_options
            $options = array_filter(array_map('trim', explode(',', $options_input))); // Remove empty options
            if (count($options) < 2) {
                throw new Exception("Please provide at least two options.");
            }

            $insert_option_query = "INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_option_query);
            foreach ($options as $option) {
                $stmt->bind_param("is", $poll_id, $option);
                $stmt->execute();
            }
            $stmt->close();

            // Commit transaction
            $conn->commit();
            echo "<script>alert('Poll created successfully!'); window.location.href='organizer_dashboard.php';</script>";
        } catch (Exception $e) {
            // Roll back on error
            $conn->rollback();
            echo "<script>alert('Error creating poll: " . $e->getMessage() . "');</script>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Poll - Bharatam</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .form-container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        label { display: block; margin: 10px 0 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        textarea { resize: vertical; }
        button { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #218838; }
        .back-btn { background: #007bff; display: inline-block; padding: 10px 20px; border-radius: 5px; color: white; text-decoration: none; margin-top: 10px; }
        .back-btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Create New Poll (Bharatam)</h2>
        <form method="POST">
            <label>Select Event</label>
            <select name="event_id" required>
                <option value="">-- Select Event --</option>
                <?php foreach ($events as $event): ?>
                    <option value="<?php echo $event['event_id']; ?>">
                        <?php echo htmlspecialchars($event['event_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label>Poll Question</label>
            <input type="text" name="question" placeholder="e.g., Which house performed best?" required>
            
            <label>Options (comma-separated, e.g., Rajputs, Mughals, Aryans)</label>
            <textarea name="options" rows="3" placeholder="Enter options separated by commas" required></textarea>
            
            <button type="submit">Create Poll</button>
            <a href="organizer_dashboard.php" class="back-btn">Back</a>
        </form>
    </div>
</body>
</html>