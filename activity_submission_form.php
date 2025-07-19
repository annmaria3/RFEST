<?php
include 'db_connect.php';





// Fetch all organizing bodies (portals/clubs) for dropdown
$orgQuery = "SELECT portal_id, portal_name FROM event_portals";
$orgResult = $conn->query($orgQuery);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Activity Submission Form</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f2f5;
            padding: 30px;
        }

        .form-container {
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
        }

        input[type="text"],
        input[type="datetime-local"],
        select,
        input[type="file"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }

        button:hover {
            background-color: #45a049;
        }

        select:invalid {
            color: #888;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Submit Activity for Points</h2>
    <form action="submit_activity.php" method="POST" enctype="multipart/form-data">
        <label>Student ID:</label>
        <input type="text" name="student_id" required>

        <label>Organizing Body:</label>
        <select name="organizing_body_id" required>
            <option value="" disabled selected>Select</option>
            <?php while ($row = $orgResult->fetch_assoc()) { ?>
                <option value="<?= $row['portal_id'] ?>"><?= $row['portal_name'] ?></option>
            <?php } ?>
        </select>

        <label>Event Name:</label>
        <input type="text" name="event_name" required>

        <label>Event Type:</label>
        <select name="event_type" required>
            <option value="Workshop">Workshop</option>
            <option value="Seminar">Seminar</option>
            <option value="Hackathon">Hackathon</option>
            <option value="Competition">Competition</option>
            <option value="Other">Other</option>
        </select>

        <label>Start Time:</label>
        <input type="datetime-local" name="start_time" required>

        <label>End Time:</label>
        <input type="datetime-local" name="end_time" required>

        <label>Certificate Type:</label>
        <select name="certificate_type" required>
            <option value="Participant">Participant</option>
            <option value="Winner">Winner</option>
            <option value="Runner-up">Runner-up</option>
        </select>

        <label>Upload Certificate:</label>
        <input type="file" name="certificate_file" accept=".pdf,.jpg,.jpeg,.png" required>

        <button type="submit">Submit</button>
    </form>
</div>

</body>
</html>
