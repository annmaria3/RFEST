<?php
include 'db_connect.php'; // Include the database connection

// Fetch events from the database
$sql = "SELECT * FROM events ORDER BY event_date DESC";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Dashboard</title>
    <!-- <link rel="stylesheet" href="styleor1.css"> Link your CSS -->
</head>
<body>

<div class="dashboard">
    <h2>Organizer Dashboard</h2>
    
    <h3>Upcoming Events</h3>
    <div class="events-list">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='event'>";
                echo "<h4>" . $row['event_name'] . "</h4>";
                echo "<p><strong>Date:</strong> " . $row['event_date'] . "</p>";
                echo "<p><strong>Venue:</strong> " . $row['venue'] . "</p>";
                echo "<p><strong>Participants:</strong> " . $row['current_participants'] . "/" . $row['max_participants'] . "</p>";
                echo "</div>";
            }
        } else {
            echo "<p>No events found.</p>";
        }
        ?>
    </div>

</div>

</body>
</html>
