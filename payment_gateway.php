<?php
session_start();
include 'db_connect.php';
require 'lib/encdec_paytm.php';
require 'phpqrcode/qrlib.php';

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Paytm Test Mode credentials
define('PAYTM_MERCHANT_ID', 'DIY12386817555501617');
define('PAYTM_MERCHANT_KEY', 'bKMfNxPPf_QdZppa');
define('PAYTM_WEBSITE', 'WEBSTAGING');
define('PAYTM_INDUSTRY_TYPE', 'Retail');
define('PAYTM_CHANNEL_ID', 'WEB');
define('PAYTM_ENVIRONMENT', 'TEST');

$paytm_url = (PAYTM_ENVIRONMENT === 'TEST') 
    ? 'https://securegw-stage.paytm.in/order/process' 
    : 'https://securegw.paytm.in/order/process';

// Get payment parameters from redirect
$user_id = $_GET['user_id'] ?? null;
$event_id = $_GET['event_id'] ?? null;
$portal = $_GET['portal'] ?? null;
$amount = $_GET['amount'] ?? 0;
$item_name = $_GET['item_name'] ?? 'Event Registration';
$group_id = $_GET['group_id'] ?? null;

// Validate parameters
if (!$user_id || !$event_id || !$portal || $amount <= 0) {
    header("Location: ind.php?portal=" . urlencode($portal) . "&error=invalid_payment_data");
    exit();
}

// Fetch event details to verify
$eventQuery = "SELECT event_name, registration_fee, is_group FROM events WHERE event_id = ?";
$stmt = $conn->prepare($eventQuery);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$eventResult = $stmt->get_result();

if (!$eventResult || $eventResult->num_rows == 0) {
    header("Location: ind.php?portal=" . urlencode($portal) . "&error=invalid_event");
    exit();
}

$eventData = $eventResult->fetch_assoc();
$expectedFee = $eventData['registration_fee'];
$isGroupEvent = $eventData['is_group'] === 'yes';

if ($amount != $expectedFee) {
    header("Location: ind.php?portal=" . urlencode($portal) . "&error=amount_mismatch");
    exit();
}

// Generate transaction order ID
$order_id = "ORDER_" . $event_id . "_" . $user_id . "_" . time();

// Paytm parameters
$paytmParams = [
    "MID" => PAYTM_MERCHANT_ID,
    "ORDER_ID" => $order_id,
    "CUST_ID" => "CUST_$user_id",
    "INDUSTRY_TYPE_ID" => PAYTM_INDUSTRY_TYPE,
    "CHANNEL_ID" => PAYTM_CHANNEL_ID,
    "TXN_AMOUNT" => number_format($amount, 2, '.', ''),
    "WEBSITE" => PAYTM_WEBSITE,
    "CALLBACK_URL" => "http://localhost/rfest/paytm_callback.php?portal=" . urlencode($portal),
];

// Generate checksum
$checksum = getChecksumFromArray($paytmParams, PAYTM_MERCHANT_KEY);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to Paytm</title>
</head>
<body onload="document.paytm_form.submit()">
    <h2>Redirecting to Paytm Payment Gateway...</h2>
    <form method="POST" action="<?php echo $paytm_url; ?>" name="paytm_form">
        <?php
        foreach ($paytmParams as $name => $value) {
            echo '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($value) . '">';
        }
        ?>
        <input type="hidden" name="CHECKSUMHASH" value="<?php echo $checksum; ?>">
    </form>
</body>
</html>
<?php
$conn->close();
?>