<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'db_connect.php';

$response = ['is_seller' => false];

// Check if the user is authenticated
if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
    
    // Query the live database directly to check the absolute truth
    $sql = "SELECT role FROM users WHERE id = $user_id";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // If they are marked as a seller in the DB, sync the session instantly
        if ($user['role'] === 'seller') {
            $_SESSION['role'] = 'seller'; // Keep the session pinned
            $response['is_seller'] = true;
        }
    }
}

echo json_encode($response);
$conn->close();
?>