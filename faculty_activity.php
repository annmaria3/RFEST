<?php
include 'db_connect.php';

// Handle approval or rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_id = $_POST['submission_id'];

    if (isset($_POST['approve'])) {
        $points = (int)$_POST['points'];
        $remarks = $_POST['remarks'];

        // Update submission status
        $stmt = $conn->prepare("UPDATE activity_submissions SET status='approved', points_awarded=?, faculty_remarks=? WHERE submission_id=?");
        $stmt->bind_param("isi", $points, $remarks, $submission_id);
        $stmt->execute();
        $stmt->close();

        // Get student_id from the submission
        $stmt = $conn->prepare("SELECT student_id FROM activity_submissions WHERE submission_id=?");
        $stmt->bind_param("i", $submission_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $submission = $result->fetch_assoc();
        $student_id = $submission['student_id'];
        $stmt->close();

        // Check if student has an entry in activity_points
        $check_query = "SELECT total_points FROM activitypoints WHERE student_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing total_points
            $row = $result->fetch_assoc();
            $new_total_points = $row['total_points'] + $points;
            $update_query = "UPDATE activitypoints SET total_points = ? WHERE student_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("is", $new_total_points, $student_id);
            $stmt->execute();
        } else {
            // Insert new row with points
            $insert_query = "INSERT INTO activitypoints (student_id, total_points) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("si", $student_id, $points);
            $stmt->execute();
        }
        $stmt->close();
    }

    if (isset($_POST['reject'])) {
        $stmt = $conn->prepare("DELETE FROM activity_submissions WHERE submission_id=?");
        $stmt->bind_param("i", $submission_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch pending submissions
$result = $conn->query("SELECT * FROM activity_submissions WHERE status = 'pending'");
$submissions = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Faculty Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            padding: 30px;
            background: #f4f6f8;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background: #007bff;
            color: white;
        }

        form {
            margin: 0;
        }

        input[type="number"], textarea {
            width: 80px;
            padding: 5px;
            font-size: 14px;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
        }

        .btn-approve {
            background-color: #28a745;
            color: white;
        }

        .btn-reject {
            background-color: #dc3545;
            color: white;
        }

        .btn-view {
            text-decoration: none;
            color: #007bff;
        }

        .btn-view:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h1>Pending Activity Submissions</h1>

<?php if (count($submissions) > 0): ?>
    <table>
        <tr>
            <th>Student ID</th>
            <th>Event Name</th>
            <th>Event Type</th>
            <th>Start</th>
            <th>End</th>
            <th>Certificate Type</th>
            <th>Certificate</th>
            <th>Points</th>
            <th>Remarks</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($submissions as $sub): ?>
            <tr>
                <form method="POST">
                    <input type="hidden" name="submission_id" value="<?= $sub['submission_id'] ?>">
                    <td><?= htmlspecialchars($sub['student_id']) ?></td>
                    <td><?= htmlspecialchars($sub['event_name']) ?></td>
                    <td><?= htmlspecialchars($sub['event_type']) ?></td>
                    <td><?= $sub['start_time'] ?></td>
                    <td><?= $sub['end_time'] ?></td>
                    <td><?= $sub['certificate_type'] ?></td>
                    <td><a class="btn-view" href="<?= $sub['certificate_path'] ?>" target="_blank">View</a></td>
                    <td><input type="number" name="points" required min="0"></td>
                    <td><textarea name="remarks" rows="1" cols="15"></textarea></td>
                    <td>
                        <button class="btn btn-approve" type="submit" name="approve">Approve</button><br><br>
                        <button class="btn btn-reject" type="submit" name="reject" onclick="return confirm('Reject this submission?')">Reject</button>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p style="text-align:center;">No pending submissions.</p>
<?php endif; ?>

</body>
</html>