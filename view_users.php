<?php
include 'db_connect.php'; // Database connection

// Fetch user role filter if applied
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';

// SQL query to fetch all approved users (with optional role filter)
$sql = "SELECT user_id, name, email, rolee, phone_no, registration_date FROM users WHERE status = 'Approved'";

if (!empty($role_filter)) {
    $sql .= " AND rolee = ?";
}

$stmt = $conn->prepare($sql);

if (!empty($role_filter)) {
    $stmt->bind_param("s", $role_filter);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            text-align: center;
            padding: 20px;
        }
        .container {
            width: 80%;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #007bff;
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
        .edit-btn, .delete-btn {
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            color: white;
        }
        .edit-btn { background: green; }
        .edit-btn:hover { background: darkgreen; }
        .delete-btn { background: red; }
        .delete-btn:hover { background: darkred; }
        .filter-section {
            margin-bottom: 20px;
        }
        select, button {
            padding: 8px;
            margin-right: 10px;
        }
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
        .message {
            color: green;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .error {
            color: red;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Approved Users</h2>

        <!-- Display success or error messages -->
        <?php if (isset($_GET['message'])) { echo "<p class='message'>{$_GET['message']}</p>"; } ?>
        <?php if (isset($_GET['error'])) { echo "<p class='error'>{$_GET['error']}</p>"; } ?>

        <!-- Filter Users by Role -->
        <div class="filter-section">
            <form method="GET" action="view_users.php">
                <label for="role">Filter by Role:</label>
                <select name="role" id="role">
                    <option value="">All Roles</option>
                    <option value="admin" <?php if ($role_filter == 'admin') echo 'selected'; ?>>Admin</option>
                    <option value="student" <?php if ($role_filter == 'student') echo 'selected'; ?>>Student</option>
                    <option value="organizer" <?php if ($role_filter == 'organizer') echo 'selected'; ?>>Organizer</option>
                    <option value="faculty" <?php if ($role_filter == 'faculty') echo 'selected'; ?>>Faculty</option>
                </select>
                <button type="submit">Filter</button>
            </form>
        </div>

        <?php if ($result->num_rows > 0) { ?>
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Phone Number</th>
                        <th>Registration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['user_id']; ?></td>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo ucfirst($row['rolee']); ?></td>
                            <td><?php echo $row['phone_no']; ?></td>
                            <td><?php echo $row['registration_date']; ?></td>
                            <td>
                                <a href="edit_user.php?user_id=<?php echo $row['user_id']; ?>" class="edit-btn">Edit</a>
                                <a href="delete_user.php?user_id=<?php echo $row['user_id']; ?>" class="delete-btn"
                                   onclick="return confirm('Are you sure you want to delete this user?');">
                                   Delete
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No approved users found.</p>
        <?php } ?>

        <a href="admin.php" class="back-btn">Back to Dashboard</a>
    </div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
