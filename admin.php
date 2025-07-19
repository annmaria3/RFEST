<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: loginn.php");
    exit();
}



// Use session variables as needed
$user_id = $_SESSION['user_id'];
$role = $_SESSION['rolee'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            text-align: center;
            background-image: url('backadmin.jpg'); /* Ensure this file exists */
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #fdfafa;
        }
        .header {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
            background: rgba(8, 78, 72, 0.425);
            position: relative;
        }
        .header h2 {
            text-transform: uppercase;
        }
        .logout {
            position: absolute;
            right: 20px;
            color: #fff;
            text-decoration: none;
            font-weight: bold;
            padding: 8px 12px;
            background: red;
            border-radius: 5px;
        }
        .logout:hover {
            background: darkred;
        }
        .container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .box {
            border: 1px solid #fff;
            padding: 20px;
            width: 250px;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 10px;
        }
        select, button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            font-size: 14px;
        }
        .manage-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 10px;
        }
        .manage-buttons a button {
            width: 100%;
            padding: 10px;
            cursor: pointer;
            background: #2d8f85; /* Dark Green */
            color: white;
            border: none;
            border-radius: 5px;
            text-align: center;
        }
        .manage-buttons a button:hover {
            background: #1a5d57;
        }
        /* Green Buttons */
        .green-button {
            background: #28a745 !important; /* Bootstrap Green */
        }
        .green-button:hover {
            background: #218838 !important;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>RFEST - Admin Dashboard</h2>
        <a href="loginn.php" class="logout">Logout</a>
    </div>
    
    <h2>ADMIN OPTIONS</h2>
    
    <div class="container">
        <!-- Create Event Portal -->
        <div class="box">
            <h3>Create Portal</h3>
            <form action="create_portal.php" method="POST">
                <label for="club-name">Club Name</label>
                <select name="club_name" id="club-name">
                    <option value="NDLI">NDLI</option>
                    <option value="Techshetra">TECHSHETRA</option>
                    <option value="BHARATAM">BHARATAM</option>
                    <option value="CYBERBLITZ">CYBERBLITZ</option>
                    <option value="IEEE">IEEE</option>
                    <option value="IEDC">IEDC</option>
                </select>
                <button type="submit" class="green-button">SUBMIT</button>
            </form>
        </div>

        <!-- Manage Users -->
        <div class="box">
            <h3>Manage Users</h3>
            <div class="manage-buttons">
                <a href="approve_user.php"><button class="green-button">Approve User</button></a>
                <a href="view_users.php"><button class="green-button">View Users</button></a>
            </div>

            <h3>Manage Events</h3>
            <div class="manage-buttons">
                <a href="add_event.php"><button class="green-button">Add Event</button></a>
                <a href="view_events.php"><button class="green-button">View Events</button></a>
            </div>

            <h3>Manage Notices</h3>
            <div class="manage-buttons">
                <a href="add_notice.php"><button class="green-button">Add Notice</button></a>
                <a href="view_notices.php"><button class="green-button">View Notice</button></a>
            </div>

           
        </div>
    </div>
</body>
</html>
