<?php
session_start();

// Security Gatekeeper: Make sure only an admin is running this
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied.");
}

include 'db_connect.php';

if (isset($_GET['id'])) {
    $product_id = $conn->real_escape_string($_GET['id']);

    // Admin override query: Delete by ID cleanly
    $sql = "DELETE FROM products WHERE id = '$product_id'";
    
    if ($conn->query($sql)) {
        // Send us back to the global admin page instantly
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