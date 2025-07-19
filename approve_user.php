<?php
include 'db_connect.php'; // Database connection

// Fetch all pending users
$sql = "SELECT user_id, name, email, rolee, phone_no FROM users WHERE status = 'Pending'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Users</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
            text-align: center;
        }
        .container {
            width: 90%;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        .approve-btn {
            background: green;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
        }
        .reject-btn {
            background: red;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
        }
        .approve-btn:hover { background: darkgreen; }
        .reject-btn:hover { background: darkred; }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-btn:hover { background: #0056b3; }
    </style>
</head>
<body>

    <div class="container">
        <h2>Pending User Approvals</h2>
        <?php if ($result->num_rows > 0) { ?>
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Additional Details</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    while ($row = $result->fetch_assoc()) { 
                        $user_id = $row['user_id'];
                        $additional_details = "N/A";

                        // Fetch additional details based on the role
                        if (strtolower($row['rolee']) === "student") {
                            // Get department
                            $dept_sql = "SELECT department FROM department WHERE user_id = ?";
                            $stmt = $conn->prepare($dept_sql);
                            $stmt->bind_param("s", $user_id);
                            $stmt->execute();
                            $dept_result = $stmt->get_result();
                            $dept = $dept_result->fetch_assoc()['department'] ?? 'N/A';
                            $stmt->close();
                            
                            // Get club and house details
                            $club_sql = "SELECT batch, house, clubs FROM clubandhouse WHERE user_id = ?";
                            $stmt = $conn->prepare($club_sql);
                            $stmt->bind_param("s", $user_id);
                            $stmt->execute();
                            $club_result = $stmt->get_result();
                            
                            if ($club_row = $club_result->fetch_assoc()) {
                                $additional_details = "Dept: " . $dept . 
                                                    ", Batch: " . $club_row['batch'] . 
                                                    ", House: " . $club_row['house'] . 
                                                    ", Clubs: " . $club_row['clubs'];
                            } else {
                                $additional_details = "Dept: " . $dept;
                            }
                            $stmt->close();
                            
                        } elseif (strtolower($row['rolee']) === "organizer") {
                            // Get department
                            $dept_sql = "SELECT department FROM department WHERE user_id = ?";
                            $stmt = $conn->prepare($dept_sql);
                            $stmt->bind_param("s", $user_id);
                            $stmt->execute();
                            $dept_result = $stmt->get_result();
                            $dept = $dept_result->fetch_assoc()['department'] ?? 'N/A';
                            $stmt->close();
                            
                            // Get organizer position
                            $org_sql = "SELECT organization_name, position FROM organizers_position WHERE user_id = ?";
                            $stmt = $conn->prepare($org_sql);
                            $stmt->bind_param("s", $user_id);
                            $stmt->execute();
                            $org_result = $stmt->get_result();
                            
                            if ($org_row = $org_result->fetch_assoc()) {
                                $additional_details = "Dept: " . $dept . 
                                                    ", Org: " . $org_row['organization_name'] . 
                                                    ", Position: " . $org_row['position'];
                            } else {
                                $additional_details = "Dept: " . $dept;
                            }
                            $stmt->close();
                            
                        } elseif (strtolower($row['rolee']) === "faculty") {
                            // Get department
                            $dept_sql = "SELECT department FROM department WHERE user_id = ?";
                            $stmt = $conn->prepare($dept_sql);
                            $stmt->bind_param("s", $user_id);
                            $stmt->execute();
                            $dept_result = $stmt->get_result();
                            
                            if ($dept_row = $dept_result->fetch_assoc()) {
                                $additional_details = "Dept: " . $dept_row['department'];
                            }
                            $stmt->close();
                        }
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone_no']); ?></td>
                            <td><?php echo htmlspecialchars($row['rolee']); ?></td>
                            <td><?php echo htmlspecialchars($additional_details); ?></td>
                            <td>
                                <a href="process_approval.php?user_id=<?php echo urlencode($row['user_id']); ?>&action=approve" class="approve-btn">Approve</a>
                                <a href="process_approval.php?user_id=<?php echo urlencode($row['user_id']); ?>&action=reject" class="reject-btn">Reject</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No pending user approvals.</p>
        <?php } ?>
        <a href="admin.php" class="back-btn">Back to Dashboard</a>
    </div>

</body>
</html>

<?php
$conn->close();
?>