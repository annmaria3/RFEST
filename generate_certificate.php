<?php
require('fpdf/fpdf.php');
include 'db_connect.php';

// Get certificate ID from GET request
$certificate_id = $_GET['certificate_id'] ?? null;

if (!$certificate_id) {
    die("No certificate ID provided.");
}

// Fetch certificate details from the database with portal name
$query = "SELECT c.*, u.name, e.event_name, e.event_date, e.venue, ep.portal_name
          FROM certificates c
          JOIN users u ON c.user_id = u.user_id
          JOIN events e ON c.event_id = e.event_id
          JOIN event_portals ep ON e.portal_id = ep.portal_id
          WHERE c.certificate_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $certificate_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die("Invalid certificate ID.");
}

// Decide wording based on certificate type
$achievementText = "participated in";
switch (strtolower($data['certificate_type'])) {
    case 'first prize':
        $achievementText = "secured the First Prize";
        break;
    case 'second prize':
        $achievementText = "secured the Second Prize";
        break;
    case 'third prize':
        $achievementText = "secured the Third Prize";
        break;
}

// Create PDF in landscape mode
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

// Background image (ensure it's 297x210 mm size, landscape)
$pdf->Image('certificate_bg.png', 0, 0, 297, 210);

// Certificate Title
$pdf->SetFont('Arial', 'B', 28);
$pdf->Ln(20);
$pdf->Cell(0, 20, 'Certificate of ' . ucfirst($data['certificate_type']), 0, 1, 'C');

$pdf->Ln(10);

// Certify Text
$pdf->SetFont('Arial', '', 16);
$pdf->MultiCell(0, 10, "This is to certify that", 0, 'C');

$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 24);
$pdf->Cell(0, 15, strtoupper($data['name']), 0, 1, 'C');

$pdf->Ln(2);
$pdf->SetFont('Arial', '', 16);
$pdf->MultiCell(0, 10,
    "has $achievementText in the event \"" . $data['event_name'] . "\"\n" .
    "organized by " . $data['portal_name'] . " on " . $data['event_date'] .
    " at " . $data['venue'] . ".", 0, 'C');

$pdf->Ln(5);
$pdf->SetFont('Arial', 'I', 15);
$pdf->Cell(0, 10, "We appreciate their enthusiasm and commendable performance.", 0, 1, 'C');

// Optional signature lines
$pdf->SetY(-50);
$pdf->SetFont('Arial', '', 14);
$pdf->Cell(130, 10, '_________________________', 0, 0, 'C');
$pdf->Cell(130, 10, '_________________________', 0, 1, 'C');
$pdf->Cell(130, 10, 'Organizer Signature', 0, 0, 'C');
$pdf->Cell(130, 10, 'Faculty Coordinator', 0, 1, 'C');

ob_clean(); // Clear any unwanted output
$pdf->Output();
exit();
?>