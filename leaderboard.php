<?php
include 'db_connect.php';

// Process form submission if POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'] ?? null;
    $competition_type = $_POST['competition_type'] ?? null;
    $entries = $_POST['entries'] ?? [];

    // Validate inputs
    if (!$event_id || !$competition_type || empty($entries)) {
        die("<script>alert('Invalid submission data.'); window.location.href='leaderboard.php';</script>");
    }

    // Verify event exists
    $eventQuery = $conn->prepare("SELECT event_id FROM events WHERE event_id = ?");
    $eventQuery->bind_param("i", $event_id);
    $eventQuery->execute();
    $eventResult = $eventQuery->get_result();
    $eventQuery->close();

    if ($eventResult->num_rows === 0) {
        die("<script>alert('Event not found in database.'); window.location.href='leaderboard.php';</script>");
    }

    // Process leaderboard entries
    foreach ($entries as $position => $data) {
        $name = $data['name'] ?? '';
        $points = $data['points'] ?? 0;

        if (empty($name) || $points <= 0) {
            continue; // Skip invalid entries
        }

        if ($competition_type === 'group') {
            $query = "INSERT INTO student_leaderboard (event_id, competition_type, group_name, position, points)
                      VALUES (?, ?, ?, ?, ?)
                      ON DUPLICATE KEY UPDATE points = VALUES(points)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("issii", $event_id, $competition_type, $name, $position, $points);
        } else {
            $query = "INSERT INTO student_leaderboard (event_id, competition_type, student_id, position, points)
                      VALUES (?, ?, ?, ?, ?)
                      ON DUPLICATE KEY UPDATE points = VALUES(points)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("issii", $event_id, $competition_type, $name, $position, $points);
        }

        $stmt->execute();
        $stmt->close();
    }

    $conn->close();
    echo "<script>alert('Leaderboard Updated!'); window.location.href='leaderboard.php';</script>";
    exit();
}

// Fetch all events for the dropdown
$query = "SELECT event_id, event_name FROM events ORDER BY event_name ASC";
$result = $conn->query($query);
$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Leaderboard Entry</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            padding: 40px;
        }
        .form-container {
            background: #ffffff;
            padding: 30px;
            max-width: 800px;
            margin: auto;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
        }
        select, input[type="text"], input[type="number"] {
            padding: 10px;
            width: 100%;
            margin: 8px 0;
            box-sizing: border-box;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 12px 20px;
            border: none;
            margin-top: 20px;
            border-radius: 5px;
            cursor: pointer;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Student Leaderboard Entry</h2>
        <form method="POST" action="leaderboard.php">
            <label>Event Name:</label>
            <select name="event_id" required>
                <option value="" disabled selected>Select an Event</option>
                <?php foreach ($events as $event): ?>
                    <option value="<?php echo htmlspecialchars($event['event_id']); ?>">
                        <?php echo htmlspecialchars($event['event_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Competition Type:</label>
            <select name="competition_type" id="competition_type" required onchange="toggleFields()">
                <option value="individual">Individual</option>
                <option value="group">Group</option>
            </select>

            <table>
                <thead>
                    <tr>
                        <th>Position</th>
                        <th id="label1">Student ID</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1st</td>
                        <td><input type="text" name="entries[1][name]" required></td>
                        <td><input type="number" name="entries[1][points]" required></td>
                    </tr>
                    <tr>
                        <td>2nd</td>
                        <td><input type="text" name="entries[2][name]" required></td>
                        <td><input type="number" name="entries[2][points]" required></td>
                    </tr>
                    <tr>
                        <td>3rd</td>
                        <td><input type="text" name="entries[3][name]" required></td>
                        <td><input type="number" name="entries[3][points]" required></td>
                    </tr>
                </tbody>
            </table>

            <button type="submit">Submit Leaderboard</button>
        </form>
    </div>

    <script>
        function toggleFields() {
            const type = document.getElementById("competition_type").value;
            const label = document.getElementById("label1");
            label.innerText = type === "group" ? "Group Name" : "Student ID";
        }
        window.onload = toggleFields;
    </script>
</body>
</html>