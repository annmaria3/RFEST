<?php
include 'db_connect.php';

// Get filter values
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : null;

// Fetch all events for filter dropdown
$events_query = "SELECT event_id, event_name FROM events ORDER BY event_name";
$events_result = $conn->query($events_query);

// Build feedback query with filters
$feedback_query = "SELECT f.*, e.event_name, u.name as user_name 
                  FROM feedback f
                  JOIN events e ON f.event_id = e.event_id
                  JOIN users u ON f.user_id = u.user_id";
                  
if ($event_id) {
    $feedback_query .= " WHERE f.event_id = $event_id";
}

$feedback_query .= " ORDER BY f.feedback_date DESC";
$feedback_result = $conn->query($feedback_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Event Feedback</h1>
        
        <!-- Filter Form -->
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-6">
                    <label for="event_id" class="form-label">Filter by Event</label>
                    <select class="form-select" id="event_id" name="event_id" onchange="this.form.submit()">
                        <option value="">All Events</option>
                        <?php while ($event = $events_result->fetch_assoc()): ?>
                            <option value="<?= $event['event_id'] ?>" <?= ($event_id == $event['event_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($event['event_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
        </form>
        
        <!-- Feedback Results -->
        <?php if ($feedback_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Event</th>
                            <th>User</th>
                            <th>Rating</th>
                            <th>Comments</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($feedback = $feedback_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($feedback['event_name']) ?></td>
                                <td><?= htmlspecialchars($feedback['user_name']) ?></td>
                                <td><?= $feedback['rating'] ?>/5</td>
                                <td><?= htmlspecialchars($feedback['comments']) ?></td>
                                <td><?= date('M j, Y', strtotime($feedback['feedback_date'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No feedback found.</div>
        <?php endif; ?>
    </div>
</body>
</html>