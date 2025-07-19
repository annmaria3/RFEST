<?php
session_start();
include 'db_connect.php';
require('fpdf/fpdf.php'); // Include FPDF for PDF generation


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

// Fetch registered events
$query = "SELECT e.event_id, e.event_name, e.event_date, e.start_time, e.venue, r.registration_id 
          FROM registrations r 
          JOIN events e ON r.event_id = e.event_id 
          WHERE r.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$tickets = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Generate Ticket PDF
if (isset($_GET['registration_id'])) {
    $registration_id = $_GET['registration_id'];

    // Fetch the ticket_id and QR code from the `tickets` table using `registration_id`
    $query = "SELECT ticket_id, qr_code FROM tickets WHERE registration_id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $registration_id,);
    $stmt->execute();
    $stmt->bind_result($ticket_id, $qr_code);
    $stmt->fetch();
    $stmt->close();
    $conn->close();

    // Check if ticket exists
    if (!$ticket_id) {
        die("No ticket found for this registration.");
    }

    // Convert QR code (Base64) into an image
    $qr_path = "qrcodes/$ticket_id.png";
    //file_put_contents($qr_path, base64_decode($qr_code));

    // Generate PDF
    foreach ($tickets as $ticket) {
        if ($ticket['registration_id'] == $registration_id) {
            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 10, 'Ticket', 0, 1, 'C');
            $pdf->Ln(10);

            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 10, 'Event Name: ' . $ticket['event_name'], 0, 1);
            $pdf->Cell(0, 10, 'User Name: ' . 'u2203025', 0, 1);
            $pdf->Cell(0, 10, 'Ticket ID: ' . $ticket_id, 0, 1);
            $pdf->Cell(0, 10, 'Registration ID: ' . $ticket['registration_id'], 0, 1);
            $pdf->Cell(0, 10, 'Event Date & Time: ' . $ticket['event_date'] . ' ' . $ticket['start_time'], 0, 1);
            $pdf->Cell(0, 10, 'Venue: ' . $ticket['venue'], 0, 1);
            $pdf->Ln(10);

            // QR Code in PDF
            if (file_exists($qr_path)) {
                $pdf->Image($qr_path, 80, 100, 50, 50);
            } else {
                $pdf->Cell(0, 10, 'QR Code Not Available', 0, 1, 'C');
            }

            ob_clean(); // Clears any previous output to prevent errors
            $pdf->Output();
           // unlink($qr_path); // Remove temp QR code image
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>My Tickets</h1>
        <table border="1">
            <thead>
                <tr>
                    <th>Event Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $ticket) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ticket['event_name']); ?></td>
                        <td><a href="my_tickets.php?registration_id=<?php echo $ticket['registration_id']; ?>" target="_blank">View Ticket</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>
