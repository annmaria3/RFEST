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


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = intval($_POST['event_id']);
    $rating = intval($_POST['rating']);
    $comments = trim($_POST['comments']);
    
    // Validate inputs
    if ($rating < 1 || $rating > 5) {
        $error = "Rating must be between 1 and 5";
    } elseif (empty($comments)) {
        $error = "Comments cannot be empty";
    } else {
        // Insert feedback
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, event_id, rating, comments, feedback_date) 
                               VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("siis", $user_id, $event_id, $rating, $comments);
        
        if ($stmt->execute()) {
            $success = "Thank you for your feedback!";
        } else {
            $error = "Error submitting feedback. Please try again.";
        }
        $stmt->close();
    }
}

// Fetch all available events
$events_query = "SELECT event_id, event_name FROM events ORDER BY event_name";
$events_result = $conn->query($events_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="mb-4">Event Feedback</h2>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="event_id" class="form-label">Select Event</label>
                        <select class="form-select" id="event_id" name="event_id" required>
                            <option value="">-- Select an event --</option>
                            <?php while ($event = $events_result->fetch_assoc()): ?>
                                <option value="<?= $event['event_id'] ?>"><?= htmlspecialchars($event['event_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="rating" class="form-label">Rating (1-5)</label>
                        <input type="number" class="form-control" id="rating" name="rating" 
                               min="1" max="5" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="comments" class="form-label">Comments</label>
                        <textarea class="form-control" id="comments" name="comments" rows="3" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Submit Feedback</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>