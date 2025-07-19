<?php
include 'db_connect.php';

// Fetch all pending events with portal name
$query = "SELECT e.event_id, e.event_name, e.event_date, e.venue, ep.portal_name 
          FROM events e 
          LEFT JOIN event_portals ep ON e.portal_id = ep.portal_id 
          WHERE e.status = 'pending'";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Faculty Event Approval Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }

        h2 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #004080;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
        }

        .approve {
            background-color: #28a745;
        }

        .reject {
            background-color: #dc3545;
        }
    </style>
</head>
<body>

<h2>Pending Event Approvals</h2>

<?php if ($result->num_rows > 0): ?>
<table>
    <thead>
        <tr>
            <th>Event Name</th>
            <th>Date</th>
            <th>Venue</th>
            <th>Portal</th> <!-- Changed from "Organizer" to "Portal" -->
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['event_name']) ?></td>
                <td><?= htmlspecialchars($row['event_date']) ?></td>
                <td><?= htmlspecialchars($row['venue']) ?></td>
                <td><?= htmlspecialchars($row['portal_name'] ?? 'N/A') ?></td> <!-- Display portal_name -->
                <td>
                    <form method="POST" action="process_event_status.php" style="display:inline;">
                        <input type="hidden" name="event_id" value="<?= $row['event_id'] ?>">
                        <button type="submit" name="action" value="approve" class="btn approve">Approve</button>
                    </form>
                    <form method="POST" action="process_event_status.php" style="display:inline;">
                        <input type="hidden" name="event_id" value="<?= $row['event_id'] ?>">
                        <button type="submit" name="action" value="reject" class="btn reject">Reject</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php else: ?>
    <p style="text-align:center;">No pending events to review.</p>
<?php endif; ?>

</body>
</html>
<?php $conn->close(); ?>