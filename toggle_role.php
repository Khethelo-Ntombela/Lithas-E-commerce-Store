<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$conn = new mysqli("127.0.0.1", "root", "", "user_registration", 3307);
$user_id = $_SESSION['user_id'];
$current_role = $_SESSION['role'];

// Keep the admin account protection gate
if ($current_role === 'admin') {
    die("Master administrator profiles cannot be toggled.");
}

// Flip the role state
$new_role = ($current_role === 'buyer') ? 'seller' : 'buyer';

$sql = "UPDATE users SET role = '$new_role' WHERE id = '$user_id'";

if ($conn->query($sql)) {
    $_SESSION['role'] = $new_role;
    
    // SMART ROUTING:
    if ($new_role === 'seller') {
        // Take them straight to the upload form with a friendly alert flag
        header("Location: upload.html?status=activated");
    } else {
        // If they opt-out, take them back to the marketplace
        header("Location: shop.html?status=returned_to_buyer");
    }
    exit();
} else {
    echo "Error updating profile state: " . $conn->error;
}
$conn->close();
?>