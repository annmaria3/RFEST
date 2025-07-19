<?php
session_start();
include 'db_connect.php';
require('fpdf/fpdf.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: loginn.php");
    exit();
}

// Check if user is a participant (student)
if ($_SESSION['rolee'] !== 'student') {
    header("Location: loginn.php");
    exit();
}

// Use session variables as needed
$user_id = $_SESSION['user_id'];
$role = $_SESSION['rolee'];
// Fetch certificates
$query = "SELECT c.certificate_id, c.certificate_type, c.certificate_status, e.event_name 
          FROM certificates c 
          JOIN events e ON c.event_id = e.event_id 
          WHERE c.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$certificates = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// PDF generation logic
if (isset($_GET['certificate_id'])) {
    //
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Certificates</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 30px;
        }
        .container {
            background: white;
            padding: 25px;
            max-width: 900px;
            margin: auto;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 12px;
            text-align: center;
        }
        th {
            background: #333;
            color: white;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        a.btn {
            background: #007bff;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 5px;
        }
        a.btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>My Certificates</h1>

        <?php if (count($certificates) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Certificate Type</th>
                        <th>Status</th>
                        <th>Download</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($certificates as $cert): ?>
                        <tr>
                            <td><?= htmlspecialchars($cert['event_name']) ?></td>
                            <td><?= htmlspecialchars($cert['certificate_type']) ?></td>
                            <td><?= htmlspecialchars($cert['certificate_status']) ?></td>
                            <td>
                                <?php if ($cert['certificate_status'] == 'Generated') { ?>
                                    <a class="btn" href="generate_certificate.php?certificate_id=<?= $cert['certificate_id']; ?>" target="_blank">Download</a>

                                <?php } else { ?>
                                    Not Available
                                <?php } ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center;">You have not received any certificates yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>
