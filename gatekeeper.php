<?php
session_start();
include 'db_connect.php';

// If they aren't logged in, send them to login first
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = intval($_SESSION['user_id']);

// Check their live status in the database
$sql = "SELECT role FROM users WHERE id = $user_id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    if ($user['role'] === 'seller') {
        // One click straight to the form if they are a seller
        header("Location: upload.html?status=activated");
        exit();
    }
}

// Otherwise, send them to the activation landing page
header("Location: become_seller.html");
exit();
?>