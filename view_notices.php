<?php
session_start();
include 'db_connect.php'; // Ensure database connection

// Fetch all notices
$sql = "SELECT * FROM noticeboard ORDER BY creation_date DESC";
$result = mysqli_query($conn, $sql);

// Handle Delete Request
if (isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $delete_query = "DELETE FROM noticeboard WHERE notice_id = '$delete_id'";

    if (mysqli_query($conn, $delete_query)) {
        echo "<script>alert('Notice deleted successfully!'); window.location.href='view_notices.php';</script>";
    } else {
        echo "<script>alert('Error deleting notice: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Notices</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            text-align: center;
            background: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        h2 {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
        }
        th {
            background: #007bff;
            color: white;
        }
        button {
            padding: 8px 12px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
        }
        .delete-btn {
            background: red;
            color: white;
        }
        .delete-btn:hover {
            background: darkred;
        }
        .back-btn {
            background: #007bff;
            color: white;
            margin-top: 20px;
        }
        .back-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>All Notices</h2>
        
        <?php if (mysqli_num_rows($result) > 0) { ?>
            <table>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Created By</th>
                    <th>Creation Date</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo htmlspecialchars($row['created_by']); ?></td>
                        <td><?php echo $row['creation_date']; ?></td>
                        <td>
                            <button class="delete-btn" onclick="confirmDelete(<?php echo $row['notice_id']; ?>)">Delete</button>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php } else { ?>
            <p>No notices found.</p>
        <?php } ?>

        <br>
        <a href="admin.php"><button class="back-btn">Back to Dashboard</button></a>
    </div>

    <script>
        function confirmDelete(noticeId) {
            if (confirm("Are you sure you want to delete this notice?")) {
                window.location.href = "view_notices.php?delete_id=" + noticeId;
            }
        }
    </script>
</body>
</html>
