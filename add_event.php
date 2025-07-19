<?php
session_start();
include 'db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: loginn.php");
    exit();
}

// Check if user is either an organizer or admin
$allowed_roles = ['organizer', 'admin'];
if (!in_array($_SESSION['rolee'], $allowed_roles)) {
    header("Location: loginn.php");
    exit();
}

// Use session variables as needed
$user_id = $_SESSION['user_id'];
$role = $_SESSION['rolee'];
$portal_query = "SELECT * FROM event_portals";
$portal_result = mysqli_query($conn, $portal_query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_name = mysqli_real_escape_string($conn, $_POST['event_name']);
    $event_type = mysqli_real_escape_string($conn, $_POST['event_type']);
    $event_date = $_POST['event_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $venue = mysqli_real_escape_string($conn, $_POST['venue']);
    $registration_fee = $_POST['registration_fee'];
    $portal_id = (int)$_POST['portal_id'];
    $event_description = mysqli_real_escape_string($conn, $_POST['event_description']);
    $is_virtual = isset($_POST['is_virtual']) ? 1 : 0;
    $certificate = isset($_POST['certificate']) ? 1 : 0;
    $is_group = isset($_POST['is_group']) ? 1 : 0;
    
    // Bharatam-specific fields
    $first_points = isset($_POST['first_points']) ? (int)$_POST['first_points'] : 0;
    $second_points = isset($_POST['second_points']) ? (int)$_POST['second_points'] : 0;
    $third_points = isset($_POST['third_points']) ? (int)$_POST['third_points'] : 0;

    // Handle is_registered based on portal
    $portal_name_query = "SELECT portal_name FROM event_portals WHERE portal_id = $portal_id";
    $portal_name_result = mysqli_query($conn, $portal_name_query);
    $portal_name = mysqli_fetch_assoc($portal_name_result)['portal_name'];
    $is_registered = (strtolower($portal_name) === 'bharatam') ? 0 : (isset($_POST['is_registered']) ? 1 : 0);

    $sql = "INSERT INTO events (
                event_name, event_type, event_date, start_time, end_time, 
                venue, registration_fee, portal_id, is_virtual, 
                max_participants, current_participants, certificate, 
                is_group, is_registered, event_description, 
                first_points, second_points, third_points
            ) VALUES (
                '$event_name', '$event_type', '$event_date', '$start_time', '$end_time', 
                '$venue', '$registration_fee', '$portal_id', '$is_virtual', 
                '{$_POST['max_participants']}', 0, '$certificate', 
                '$is_group', '$is_registered', '$event_description',
                '$first_points', '$second_points', '$third_points'
            )";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Event added successfully!'); window.location.href='organizer_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error adding event: " . mysqli_error($conn) . "');</script>";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            text-align: center;
            background: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select, textarea, button {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }
        .checkbox-group input {
            width: auto;
            margin-right: 10px;
        }
        button {
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
            padding: 12px;
        }
        button:hover {
            background: #218838;
        }
        #bharatam-points { display: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add New Event</h2>
        <form method="POST" action="add_event.php">
            <div class="form-group">
                <label>Event Name:</label>
                <input type="text" name="event_name" required>
            </div>

            <div class="form-group">
                <label>Event Type:</label>
                <select name="event_type" required>
                    <option value="Workshop">Workshop</option>
                    <option value="Game">Game</option>
                    <option value="Seminar">Seminar</option>
                    <option value="Cultural">Cultural</option>
                    <option value="Sports">Sports</option>
                </select>
            </div>

            <div class="form-group">
                <label>Event Date:</label>
                <input type="date" name="event_date" required>
            </div>

            <div class="form-group">
                <label>Start Time:</label>
                <input type="time" name="start_time" required>
            </div>

            <div class="form-group">
                <label>End Time:</label>
                <input type="time" name="end_time" required>
            </div>

            <div class="form-group">
                <label>Venue:</label>
                <input type="text" name="venue" required>
            </div>

            <div class="form-group">
                <label>Registration Fee:</label>
                <input type="number" name="registration_fee" step="0.01" min="0">
            </div>

            <div class="form-group">
                <label>Event Portal:</label>
                <select name="portal_id" id="portal_id" required>
                    <option value="">Select Portal</option>
                    <?php 
                    mysqli_data_seek($portal_result, 0);
                    while ($portal = mysqli_fetch_assoc($portal_result)) { ?>
                        <option value="<?php echo $portal['portal_id']; ?>">
                            <?php echo $portal['portal_name']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group" id="bharatam-points">
                <label>First Place Points (Bharatam):</label>
                <input type="number" name="first_points" min="0" value="30">
                <label>Second Place Points (Bharatam):</label>
                <input type="number" name="second_points" min="0" value="20">
                <label>Third Place Points (Bharatam):</label>
                <input type="number" name="third_points" min="0" value="10">
            </div>

            <div class="form-group">
                <label>Event Description:</label>
                <textarea name="event_description"></textarea>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" name="is_virtual" id="is_virtual">
                <label for="is_virtual">Virtual Event</label>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" name="certificate" id="certificate">
                <label for="certificate">Provide Certificate</label>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" name="is_group" id="is_group">
                <label for="is_group">Group Event</label>
            </div>

            <div class="checkbox-group" id="registered-container">
                <input type="checkbox" name="is_registered" id="is_registered">
                <label for="is_registered">Requires Registration</label>
            </div>

            <div class="form-group">
                <label>Max Participants:</label>
                <input type="number" name="max_participants" min="1" required>
            </div>

            <button type="submit">Add Event</button>
        </form>
    </div>

    <script>
        const portalSelect = document.getElementById('portal_id');
        const bharatamPoints = document.getElementById('bharatam-points');
        const regContainer = document.getElementById('registered-container');
        const regCheckbox = document.getElementById('is_registered');

        portalSelect.addEventListener('change', function() {
            const portalName = this.options[this.selectedIndex].text.toLowerCase();
            if (portalName === 'bharatam') {
                bharatamPoints.style.display = 'block';
                regContainer.style.display = 'none'; // Hide registration for Bharatam
                regCheckbox.checked = false;
            } else {
                bharatamPoints.style.display = 'none';
                regContainer.style.display = 'flex';
            }
        });
    </script>
</body>
</html>