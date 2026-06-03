<?php
session_start();

include 'db_connect.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $conn->real_escape_string($_POST['password']);

    //below is where we fetch the user including their assigned role
    $sql = "SELECT * FROM users WHERE username = '$user' AND password = '$pass'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        //this saves data to session memory
        $_SESSION['user_id'] = $row['id']; 
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role']; // Storing 'user' or 'admin'

        // the dual routing logic:
        if ($_SESSION['role'] === 'admin') {
            // Take System Admins straight to the master dashboard
            header("Location: global_admin.php");
            exit();
        } else {
            //this takes the regular users straight to browsing the marketplace
            header("Location: shop.html");
            exit();
        }
    } else {
        echo "<script>alert('Invalid Username or Password'); window.location.href='login.html';</script>";
    }
}
$conn->close();
?>