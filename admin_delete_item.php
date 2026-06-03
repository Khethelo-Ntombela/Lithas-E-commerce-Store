<?php
session_start();

// Security Gatekeeper: Make sure only an admin is running this
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied.");
}

$conn = new mysqli("127.0.0.1", "root", "", "user_registration", 3307);

if (isset($_GET['id'])) {
    $product_id = $conn->real_escape_string($_GET['id']);

    // Admin override query: Delete by ID cleanly
    $sql = "DELETE FROM products WHERE id = '$product_id'";
    
    if ($conn->query($sql)) {
        // Send back to your global admin page instantly
        header("Location: global_admin.php");
        exit();
    } else {
        echo "Error deleting listing: " . $conn->error;
    }
} else {
    header("Location: global_admin.php");
    exit();
}

$conn->close();
?>