<?php
session_start();

//  Checks if the user is actually logged in
if (!isset($_SESSION['user_id'])) {
    die("Access Denied. Please log in first.");
}

include 'db_connect.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 3. Check if a product ID was passed through the URL link
if (isset($_GET['id'])) {
    $product_id = $conn->real_escape_string($_GET['id']);
    $logged_in_user = $_SESSION['user_id'];

    // SECURITY GATE: Match both product ID AND the logged-in user's ID
    $sql = "DELETE FROM products WHERE id = '$product_id' AND user_id = '$logged_in_user'";
    
    if ($conn->query($sql)) {
        // Successfully deleted! Send them straight back to their dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error deleting product: " . $conn->error;
    }
} else {
    // If no ID was sent, just redirect back to safety
    header("Location: dashboard.php");
    exit();
}

$conn->close();
?>