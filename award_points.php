<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $activity_id = $_POST['activity_id'];
    $points = $_POST['points'];

    // Validate inputs
    if (!empty($activity_id) && is_numeric($points)) {
        // Update points in activitypoints table
        $sql = "UPDATE activitypoints SET points_awarded = ? WHERE activity_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $points, $activity_id);

        if ($stmt->execute()) {
            echo "Activity points updated successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Invalid input. Please provide valid values.";
    }
}

$conn->close();
?>
