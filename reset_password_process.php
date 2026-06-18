<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize user string inputs safely
    $user = $conn->real_escape_string(trim($_POST['username']));
    $new_pass = $conn->real_escape_string($_POST['new_password']);
    $confirm_pass = $conn->real_escape_string($_POST['confirm_password']);

    //Validation: Fields must not be empty
    if (empty($user) || empty($new_pass) || empty($confirm_pass)) {
        echo "<script>alert('Please fill in all fields.'); window.history.back();</script>";
        exit();
    }

    //Validation: Both matching password fields must cross-verify
    if ($new_pass !== $confirm_pass) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit();
    }

    // System Validation: Verify user row identity matches database records
    $check_sql = "SELECT id FROM users WHERE username = '$user'";
    $result = $conn->query($check_sql);

    if ($result && $result->num_rows === 0) {
        echo "<script>alert('Username not found in our system.'); window.history.back();</script>";
        exit();
    }

    //DATABASE WRITE: Updates row values safely to clear old profiles
    $update_sql = "UPDATE users SET password = '$new_pass' WHERE username = '$user'";

    if ($conn->query($update_sql)) {
        // --- CLEAN POPUP & ROUTING ACTION ---
        echo "<script>
                alert('Password updated successfully! Redirecting to login page...'); 
                window.location.href='login.html';
              </script>";
        exit();
    } else {
        echo "Database Write Error: " . $conn->error;
    }
}

$conn->close();
?>