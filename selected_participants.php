<?php
session_start();
include 'db_connect.php';

$user_id = 'org111'; // Replace with $_SESSION['user_id']
$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

if ($event_id <= 0) {
    die("<script>alert('No event specified.'); window.location.href='bharatam_dashboard.php';</script>");
}

// Fetch event details
$event_query = "SELECT event_name, portal_id FROM events WHERE event_id = ?";
$stmt = $conn->prepare($event_query);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event_result = $stmt->get_result();
$event = $event_result->fetch_assoc();
$stmt->close();

if (!$event) {
    die("<script>alert('Event not found.'); window.location.href='bharatam_dashboard.php';</script>");
}

// Fetch portal name
$portal_query = "SELECT portal_name FROM event_portals WHERE portal_id = ?";
$stmt = $conn->prepare($portal_query);
$stmt->bind_param("i", $event['portal_id']);
$stmt->execute();
$portal_result = $stmt->get_result();
$portal = $portal_result->fetch_assoc();
$portal_name = $portal['portal_name'];
$stmt->close();

// Fetch registered participants (using 'registrations' table)
$query = "SELECT u.user_id, u.name 
          FROM registrations r 
          JOIN users u ON r.user_id = u.user_id 
          WHERE r.event_id = ? AND r.payment_status = 'completed'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$participants = [];
while ($row = $result->fetch_assoc()) {
    $participants[] = $row;
}
$stmt->close();

// Fetch previously selected participants (if any)
$selected_query = "SELECT user_id FROM selected_participants WHERE event_id = ?";
$stmt = $conn->prepare($selected_query);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$selected_result = $stmt->get_result();
$selected_users = [];
while ($row = $selected_result->fetch_assoc()) {
    $selected_users[] = $row['user_id'];
}
$stmt->close();

// Handle selection submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_users_post = isset($_POST['selected_users']) ? $_POST['selected_users'] : [];
    
    // Clear previous selections
    $conn->query("DELETE FROM selected_participants WHERE event_id = $event_id");
    
    // Insert new selections
    if (!empty($selected_users_post)) {
        $insert_query = "INSERT INTO selected_participants (event_id, user_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        foreach ($selected_users_post as $selected_user_id) {
            $stmt->bind_param("is", $event_id, $selected_user_id);
            $stmt->execute();
        }
        $stmt->close();
    }
    echo "<script>alert('Participants selected successfully!'); window.location.href='bharatam_dashboard.php';</script>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Participants - <?php echo htmlspecialchars($event['event_name']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f8f9fa; }
        button { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px; }
        button:hover { background: #218838; }
        .back-btn { background: #007bff; display: inline-block; text-decoration: none; padding: 10px 20px; border-radius: 5px; color: white; margin-left: 10px; }
        .back-btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Select Participants for "<?php echo htmlspecialchars($event['event_name']); ?>"</h2>

        <form method="post">
            <table>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Select</th>
                </tr>
                <?php foreach ($participants as $participant): ?>
                <tr>
                    <td><?php echo htmlspecialchars($participant['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($participant['name']); ?></td>
                    <td>
                        <input type="checkbox" name="selected_users[]" value="<?php echo $participant['user_id']; ?>"
                            <?php echo in_array($participant['user_id'], $selected_users) ? 'checked' : ''; ?>>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>

            <button type="submit">Save Selection</button>
            <a href="bharatam_dashboard.php" class="back-btn">Back</a>
        </form>
    </div>
</body>
</html>