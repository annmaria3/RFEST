<?php
session_start();
include_once 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Approve event
if (isset($_GET['approve_id'])) {
    $event_id = $_GET['approve_id'];
    $updateQuery = "UPDATE events SET is_approved = 1 WHERE event_id = '$event_id'";
    mysqli_query($conn, $updateQuery);
    header("Location: approve_events.php");
    exit();
}

// Reject or Delete event
if (isset($_GET['reject_id'])) {
    $event_id = $_GET['reject_id'];
    $deleteQuery = "DELETE FROM events WHERE event_id = '$event_id'";
    mysqli_query($conn, $deleteQuery);
    header("Location: approve_events.php");
    exit();
}

// Fetch all events
$allEventsQuery = "SELECT * FROM events";
$allEventsResult = mysqli_query($conn, $allEventsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Approval Panel</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            padding: 40px;
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        table {
            width: 95%;
            margin: auto;
            border-collapse: collapse;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 16px 20px;
            text-align: left;
            border-bottom: 1px solid #eee;
            vertical-align: top;
            font-size: 15px;
        }

        th {
            background: #2c3e50;
            color: white;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        td:nth-child(2) {
            max-width: 300px;
            word-break: break-word;
        }

        .btn {
            padding: 8px 14px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 14px;
            color: #fff;
            text-decoration: none;
            margin: 4px 2px;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .approve {
            background-color: #28a745;
        }

        .approve:hover {
            background-color: #218838;
        }

        .reject, .delete {
            background-color: #dc3545;
        }

        .reject:hover, .delete:hover {
            background-color: #c82333;
        }

        .status {
            font-weight: bold;
            color: #28a745;
        }

        .no-events {
            text-align: center;
            color: #888;
            font-size: 18px;
            margin-top: 50px;
        }

        @media screen and (max-width: 768px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }

            th {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }

            td {
                position: relative;
                padding-left: 50%;
                white-space: normal;
                text-align: left;
            }

            td:before {
                position: absolute;
                top: 12px;
                left: 20px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                font-weight: bold;
                color: #333;
            }

            td:nth-of-type(1):before { content: "Event Name"; }
            td:nth-of-type(2):before { content: "Description"; }
            td:nth-of-type(3):before { content: "Date"; }
            td:nth-of-type(4):before { content: "Venue"; }
            td:nth-of-type(5):before { content: "Organizer"; }
            td:nth-of-type(6):before { content: "Status / Actions"; }
        }
    </style>
</head>
<body>

    <h1>Event Approval Panel</h1>

    <?php if (mysqli_num_rows($allEventsResult) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Event Name</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Venue</th>
                    <th>Organizer</th>
                    <th>Status / Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($allEventsResult)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['event_name']) ?></td>
                        <td><?= htmlspecialchars($row['event_desc']) ?></td>
                        <td><?= htmlspecialchars($row['event_date']) ?></td>
                        <td><?= htmlspecialchars($row['venue']) ?></td>
                        <td><?= htmlspecialchars($row['organizer_id']) ?></td>
                        <td>
                            <?php if ($row['is_approved'] == 0): ?>
                                <a href="approve_events.php?approve_id=<?= $row['event_id'] ?>" class="btn approve">Approve</a>
                                <a href="approve_events.php?reject_id=<?= $row['event_id'] ?>" class="btn reject" onclick="return confirm('Reject this event?');">Reject</a>
                            <?php else: ?>
                                <span class="status">Approved</span><br>
                                <a href="approve_events.php?reject_id=<?= $row['event_id'] ?>" class="btn delete" onclick="return confirm('Delete this approved event?');">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-events">No events found.</p>
    <?php endif; ?>

</body>
</html>
