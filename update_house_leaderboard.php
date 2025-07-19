<?php
include 'db_connect.php';

$houses = ['Rajputs', 'Mughals', 'Aryans', 'Spartans', 'Vikings'];
$fest_name = 'Bharatam';

// Fetch Bharatam events
$event_query = "SELECT e.event_id, e.event_name, e.first_points, e.second_points, e.third_points 
                FROM events e 
                JOIN event_portals ep ON e.portal_id = ep.portal_id 
                WHERE ep.portal_name = 'Bharatam' AND e.status = 'approved'";
$event_result = $conn->query($event_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = (int)$_POST['event_id'];
    $first_house = mysqli_real_escape_string($conn, $_POST['first_house']);
    $second_house = mysqli_real_escape_string($conn, $_POST['second_house']);
    $third_house = mysqli_real_escape_string($conn, $_POST['third_house']);

    // Fetch points from the selected event
    $points_query = "SELECT first_points, second_points, third_points FROM events WHERE event_id = $event_id";
    $points_result = $conn->query($points_query);
    $points = $points_result->fetch_assoc();

    if ($points) {
        $updates = [
            [$first_house, $points['first_points']],
            [$second_house, $points['second_points']],
            [$third_house, $points['third_points']]
        ];

        foreach ($updates as [$house_name, $points]) {
            if (!empty($house_name) && in_array($house_name, $houses)) {
                $check_query = "SELECT points FROM house_leaderboard WHERE fest_name = '$fest_name' AND house_name = '$house_name'";
                $check_result = $conn->query($check_query);

                if ($check_result->num_rows > 0) {
                    $current = $check_result->fetch_assoc();
                    $new_points = $current['points'] + $points;
                    $conn->query("UPDATE house_leaderboard SET points = $new_points WHERE fest_name = '$fest_name' AND house_name = '$house_name'");
                } else {
                    $conn->query("INSERT INTO house_leaderboard (fest_name, house_name, points) VALUES ('$fest_name', '$house_name', $points)");
                }
            }
        }
        echo "<script>alert('Leaderboard updated successfully!');</script>";
    } else {
        echo "<script>alert('Error: Event not found.');</script>";
    }
}

// Fetch current leaderboard
$leaderboard = [];
$query = "SELECT house_name, points FROM house_leaderboard WHERE fest_name = '$fest_name'";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $leaderboard[$row['house_name']] = $row['points'];
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bharatam House Leaderboard</title>
    <style>
        body { font-family: Arial; margin: 50px; }
        .container { max-width: 800px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #999; text-align: center; }
        .form-group { margin-bottom: 15px; text-align: left; }
        label { font-weight: bold; display: block; margin-bottom: 5px; }
        select, button { padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #28a745; color: white; border: none; cursor: pointer; }
        button:hover { background: #218838; }
        .back-btn { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        .back-btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Bharatam House Leaderboard</h2>

        <!-- Form to select event and positions -->
        <form method="POST">
            <div class="form-group">
                <label>Select Event:</label>
                <select name="event_id" required>
                    <option value="">-- Select Event --</option>
                    <?php while ($event = $event_result->fetch_assoc()): ?>
                        <option value="<?php echo $event['event_id']; ?>">
                            <?php echo htmlspecialchars($event['event_name']) . " (1st: {$event['first_points']}, 2nd: {$event['second_points']}, 3rd: {$event['third_points']})"; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>First Place:</label>
                <select name="first_house">
                    <option value="">-- Select House --</option>
                    <?php foreach ($houses as $house): ?>
                        <option value="<?php echo $house; ?>"><?php echo $house; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Second Place:</label>
                <select name="second_house">
                    <option value="">-- Select House --</option>
                    <?php foreach ($houses as $house): ?>
                        <option value="<?php echo $house; ?>"><?php echo $house; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Third Place:</label>
                <select name="third_house">
                    <option value="">-- Select House --</option>
                    <?php foreach ($houses as $house): ?>
                        <option value="<?php echo $house; ?>"><?php echo $house; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit">Update Leaderboard</button>
        </form>

        <!-- Current Leaderboard -->
        <h3>Current Standings</h3>
        <table>
            <tr>
                <th>House</th>
                <th>Points</th>
            </tr>
            <?php foreach ($houses as $house_name): ?>
                <tr>
                    <td><?php echo htmlspecialchars($house_name); ?></td>
                    <td><?php echo isset($leaderboard[$house_name]) ? $leaderboard[$house_name] : 0; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <a href="organizer_dashboard.php" class="back-btn">Back to Dashboard</a>
    </div>
</body>
</html>

