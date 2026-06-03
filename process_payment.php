<?php
// Start session to access and clear cart data upon validation success
session_start();
include 'db_connect.php';

//Tell PayFast we acknowledge receipt of the notification
header('HTTP/1.1 200 OK');

// Grab all incoming POST data sent securely from PayFast
$pfData = $_POST;

// Log the transaction attempt data internally for debugging records
file_put_contents('payfast_log.txt', print_r($pfData, true), FILE_APPEND);

if (empty($pfData)) {
    die("No notification data payload received.");
}

// Extract key data fields passed back from the gateway
$m_payment_id   = $conn->real_escape_string($pfData['m_payment_id']);
$pf_payment_id  = $conn->real_escape_string($pfData['pf_payment_id']);
$payment_status = $pfData['payment_status'];
$amount_gross   = floatval($pfData['amount_gross']);

// Validate payment success credentials
if ($payment_status === 'COMPLETE') {
    
    //Success State confirmed! 
    $_SESSION['cart'] = [];
    
    echo "Payment confirmed and local cart session flushed successfully.";
} else {
    echo "Payment status logged as incomplete: " . $payment_status;
}

$conn->close();
?>