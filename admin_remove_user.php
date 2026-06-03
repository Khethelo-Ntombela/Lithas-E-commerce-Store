<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied.");
}

$conn = new mysqli("127.0.0.1", "root", "", "user_registration", 3307);

if (isset($_GET['id'])) {
    $user_id = $conn->real_escape_string($_GET['id']);

    // 1. Remove user items first
    $conn->query("DELETE FROM products WHERE user_id = '$user_id'");
    
    // 2. Remove the actual account
    $conn->query("DELETE FROM users WHERE id = '$user_id'");

    header("Location: global_admin.php");
    exit();
}
?>