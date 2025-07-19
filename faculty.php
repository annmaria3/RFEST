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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Faculty Dashboard</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: Arial, sans-serif;
      background-image: url('faculty.jpg'); /* Use your high-res image */
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center;
      background-attachment: fixed;
    }

    .overlay {
      background-color: rgba(255, 255, 255, 0.35); /* Lighter overlay */
      min-height: 100vh;
      padding: 20px;
    }

    .header-container {
      position: relative;
      text-align: center;
      margin-top: 30px;
    }

    .heading {
      display: inline-block;
      font-size: 24px;
      border: 2px solid black;
      padding: 8px 20px;
      background-color: rgba(255, 255, 255, 0.75); /* Let the background show */
      font-weight: bold;
    }

    .logout-button {
      position: absolute;
      top: 0;
      right: 50px;
      padding: 5px 15px;
      border: 2px solid #d32f2f;
      background-color: #ef5350;
      color: white;
      cursor: pointer;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }

    .logout-button:hover {
      background-color: #d32f2f;
    }

    .main {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-top: 40px;
    }

    .card {
      border: 2px solid black;
      padding: 30px 50px;
      background-color: rgba(255, 255, 255, 0.8); /* Light card with transparency */
      text-align: center;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    }

    .card h2 {
      font-size: 18px;
      margin-bottom: 20px;
      border: 1px solid black;
      display: inline-block;
      padding: 5px 10px;
      background-color: rgba(255, 255, 255, 0.8);
      font-weight: bold;
    }

    .option-button {
      display: block;
      width: 250px;
      margin: 12px auto;
      padding: 10px;
      border: 2px solid #2e7d32;
      background-color: #66bb6a;
      color: white;
      cursor: pointer;
      font-weight: bold;
      transition: background-color 0.3s ease;
      font-size: 16px;
    }

    .option-button:hover {
      background-color: #43a047;
    }
  </style>
</head>
<body>
  <div class="overlay">
    <div class="header-container">
      <div class="heading">Faculty Dashboard</div>
      <button class="logout-button" onclick="location.href='loginn.php'">Logout</button>
    </div>

    <div class="main">
      <div class="card">
        <h2>Select the options</h2>
        <button class="option-button" onclick="location.href='faculty_activity.php'">Point Distribution</button>
        <button class="option-button" onclick="location.href='faculty_event_dashboard.php'">Event Approval</button>
        <button class="option-button" onclick="location.href='leaderboard.php'">Student Leaderboard</button>
        <button class="option-button" onclick="location.href='update_house_leaderboard.php'">House Leaderboard</button>
      </div>
    </div>
  </div>
</body>
</html>
