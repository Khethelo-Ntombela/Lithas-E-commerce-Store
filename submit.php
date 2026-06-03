<?php
// 1. Force errors to show up on screen if the database drops out
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db_connect.php';

// 2. Fallback: If someone visits this page directly via URL without submitting the form, send them back to register
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: index1.html");
    exit();
}

// 3. Collect and safely escape strings so MySQL doesn't break on cloud hosting
$name      = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
$surname   = mysqli_real_escape_string($conn, $_POST['surname'] ?? '');
$user_name = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
$pass      = mysqli_real_escape_string($conn, $_POST['password'] ?? '');

// 4. Capture your new security requirements
$dob       = mysqli_real_escape_string($conn, $_POST['dob'] ?? '2000-01-01');
$id_number = mysqli_real_escape_string($conn, $_POST['id_number'] ?? '');

// 5. Build the query to include ALL columns now inside your live table
$sql = "INSERT INTO users (name, surname, username, password, dob, id_number, role) 
        VALUES ('$name', '$surname', '$user_name', '$pass', '$dob', '$id_number', 'buyer')";

if (mysqli_query($conn, $sql)) {
    // Automatically log them in by saving the new user details straight into the session
    session_start();
    $_SESSION['user_id'] = mysqli_insert_id($conn);
    $_SESSION['username'] = $user_name;
    $_SESSION['role'] = 'buyer';

    // Registration success! Redirect directly to the storefront
    header("Location: shop.html");
    exit();
} else {
    echo "<h3>Database Storage Error:</h3> " . mysqli_error($conn);
}

mysqli_close($conn);
?>