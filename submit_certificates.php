<?php
include 'db_connect.php';

$event_id = $_POST['event_id'];
$cert_types = $_POST['cert_type'];

// Loop and insert into certificates table
foreach ($cert_types as $user_id => $cert_type) {
    // Check if already exists
    $check = $conn->prepare("SELECT * FROM certificates WHERE event_id = ? AND user_id = ?");
    $check->bind_param("is", $event_id, $user_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO certificates (user_id, event_id, certificate_type, certificate_status)
                                VALUES (?, ?, ?, 'Generated')");
        $stmt->bind_param("sis", $user_id, $event_id, $cert_type);
        $stmt->execute();
    }
}

echo "Certificates added successfully. You can now generate the PDFs!";
?>
