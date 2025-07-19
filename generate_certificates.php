<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rolee'] !== 'organizer') {
  header("Location: loginn.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['rolee'];

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : null;
if (!$event_id) {
    echo "Invalid Event ID";
    exit();
}

// Fetch event details including portal_id
$event_query = "SELECT event_name, portal_id FROM events WHERE event_id = ?";
$stmt = $conn->prepare($event_query);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event_result = $stmt->get_result();
$event = $event_result->fetch_assoc();
$stmt->close();

if (!$event) {
    echo "Event not found";
    exit();
}

// Fetch portal name for back link
$portal_query = "SELECT portal_name FROM event_portals WHERE portal_id = ?";
$stmt = $conn->prepare($portal_query);
$stmt->bind_param("i", $event['portal_id']);
$stmt->execute();
$portal_result = $stmt->get_result();
$portal = $portal_result->fetch_assoc();
$portal_name = $portal['portal_name'];
$stmt->close();

// Fetch participants (assuming 'tickets' table from your code)
$query = "SELECT users.user_id, users.name 
          FROM tickets 
          JOIN users ON tickets.user_id = users.user_id 
          WHERE tickets.event_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Certificates</title>
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
        <h2>Generate Certificates for "<?php echo htmlspecialchars($event['event_name']); ?>"</h2>

        <form method="post" action="submit_certificates.php">
            <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">

            <table>
                <tr>
                    <th>Participant ID</th>
                    <th>Name</th>
                    <th>Certificate Type</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td>
                        <select name="cert_type[<?php echo $row['user_id']; ?>]">
                            <option value="Participant">Participant</option>
                            <option value="First Prize">First Prize</option>
                            <option value="Second Prize">Second Prize</option>
                            <option value="Third Prize">Third Prize</option>
                        </select>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>

            <button type="submit">Generate Certificates</button>
            <a href="organizer_events.php?portal=<?php echo urlencode($portal_name); ?>" class="back-btn">Back</a>
        </form>
    </div>
</body>
</html>