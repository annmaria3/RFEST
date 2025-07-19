<?php
include 'db_connect.php'; // Ensure database connection

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['event_id'])) {
    $event_id = mysqli_real_escape_string($conn, $_POST['event_id']);

    // Delete the event
    $query = "DELETE FROM events WHERE event_id = '$event_id'";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Event deleted successfully!'); window.location.href = 'view_events.php';</script>";
    } else {
        echo "<script>alert('Error deleting event.'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.history.back();</script>";
}
?>
