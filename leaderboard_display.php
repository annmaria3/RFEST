<?php
include 'db_connect.php';

// Check if an event is selected
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : null;

// Get all events that have leaderboard entries
$events_query = "SELECT DISTINCT e.event_id, e.event_name 
                 FROM student_leaderboard sl
                 JOIN events e ON sl.event_id = e.event_id
                 ORDER BY e.event_name";
$events_result = $conn->query($events_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Leaderboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #333;
            text-align: center;
        }
        .event-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            margin-bottom: 30px;
        }
        .event-btn {
            padding: 10px 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .event-btn:hover {
            background: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #333;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .back-btn {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background: #333;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            width: 150px;
        }
        .medal-gold {
            color: gold;
            font-weight: bold;
        }
        .medal-silver {
            color: silver;
            font-weight: bold;
        }
        .medal-bronze {
            color: #cd7f32; /* bronze */
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Student Leaderboard</h1>
        
        <?php if ($event_id): ?>
            <?php
            // Get the event name and type
            $event_query = "SELECT event_name, event_type FROM events WHERE event_id = ?";
            $stmt = $conn->prepare($event_query);
            $stmt->bind_param("i", $event_id);
            $stmt->execute();
            $event_result = $stmt->get_result();
            $event = $event_result->fetch_assoc();
            $event_name = $event['event_name'];
            $event_type = $event['event_type'];
            $stmt->close();
            
            // Get leaderboard for the selected event
            $leaderboard_query = "SELECT sl.*, s.name as student_name 
                                FROM student_leaderboard sl
                                LEFT JOIN users s ON sl.student_id = s.user_id
                                WHERE sl.event_id = ? 
                                ORDER BY sl.points DESC";
            $stmt = $conn->prepare($leaderboard_query);
            $stmt->bind_param("i", $event_id);
            $stmt->execute();
            $result = $stmt->get_result();
            ?>
            
            <h2><?= htmlspecialchars($event_name) ?> Leaderboard</h2>
            
            <a href="?" class="back-btn">Back to All Events</a>
            
            <table>
                <tr>
                    <th>Rank</th>
                    <th><?= $event_type === 'group' ? 'Group Name' : 'Student Name' ?></th>
                    <th>Position</th>
                    <th>Points</th>
                </tr>
                <?php 
                $rank = 1;
                while ($row = $result->fetch_assoc()): 
                    // Add medal classes for top 3 positions
                    $medal_class = '';
                    if ($rank == 1) $medal_class = 'medal-gold';
                    elseif ($rank == 2) $medal_class = 'medal-silver';
                    elseif ($rank == 3) $medal_class = 'medal-bronze';
                ?>
                <tr>
                    <td class="<?= $medal_class ?>"><?= $rank++ ?></td>
                    <td>
                        <?= $event_type === 'group' 
                            ? htmlspecialchars($row['group_name'])
                            : htmlspecialchars($row['student_name']) ?>
                    </td>
                    <td><?= htmlspecialchars($row['position']) ?></td>
                    <td><?= htmlspecialchars($row['points']) ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
            
        <?php else: ?>
            <h2>Select an Event</h2>
            
            <div class="event-list">
                <?php if ($events_result->num_rows > 0): ?>
                    <?php while ($event = $events_result->fetch_assoc()): ?>
                        <a href="?event_id=<?= $event['event_id'] ?>" class="event-btn">
                            <?= htmlspecialchars($event['event_name']) ?>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No events with leaderboard data found.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>