<?php
include 'db_connect.php'; // Ensure you have a database connection file

// Fetch all events with portal name
$query = "SELECT e.event_id, e.event_name, e.event_type, e.event_date, e.start_time, e.end_time, e.venue, 
                 e.max_participants, e.current_participants, ep.portal_name 
          FROM events e
          LEFT JOIN event_portals ep ON e.portal_id = ep.portal_id";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Events</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 20px;
            background-color: #f2f2f2;
        }
        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            padding: 10px;
            border: 1px solid black;
            text-align: center;
        }
        th {
            background-color: #28a745;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        button {
            padding: 5px 10px;
            background-color: red;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        button:hover {
            background-color: darkred;
        }
        .back-button {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<h2>List of Events</h2>

<table>
    <tr>
        <th>Event ID</th>
        <th>Event Name</th>
        <th>Type</th>
        <th>Date</th>
        <th>Start Time</th>
        <th>End Time</th>
        <th>Venue</th>
        <th>Organizing Body</th>
        <th>Max Participants</th>
        <th>Current Participants</th>
        <th>Delete</th>
    </tr>
    
    <?php
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                <td>{$row['event_id']}</td>
                <td>{$row['event_name']}</td>
                <td>{$row['event_type']}</td>
                <td>{$row['event_date']}</td>
                <td>{$row['start_time']}</td>
                <td>{$row['end_time']}</td>
                <td>{$row['venue']}</td>
                <td>" . ($row['portal_name'] ? htmlspecialchars($row['portal_name']) : 'Not Assigned') . "</td>
                <td>{$row['max_participants']}</td>
                <td>{$row['current_participants']}</td>
                <td>
                    <form method='POST' action='delete_event.php' onsubmit='return confirm(\"Are you sure you want to delete this event?\");'>
                        <input type='hidden' name='event_id' value='{$row['event_id']}'>
                        <button type='submit'>Delete</button>
                    </form>
                </td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='11'>No events found</td></tr>";
    }
    ?>
</table>

<a href="admin.php" class="back-button">Back to Dashboard</a>

</body>
</html>