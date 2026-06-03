<?php
session_start(); // Ensure sessions are active at the very top of the file

include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $conn->real_escape_string($_POST['password']);
    $dob  = $conn->real_escape_string($_POST['dob']);
    $id_no = $conn->real_escape_string($_POST['id_number']);

   

    // Insert the new account
    $sql = "INSERT INTO users (username, password, dob, id_number, role) 
            VALUES ('$user', '$pass', '$dob', '$id_no', 'user')";

    if ($conn->query($sql)) {
       
        $new_user_id = $conn->insert_id; 

        // Put their details straight into the session memory
        $_SESSION['user_id'] = $new_user_id;
        $_SESSION['username'] = $user;
        $_SESSION['role'] = 'user'; // Since they are a standard customer

        //Bypass the login page entirely and take them straight to browser
        echo "<script>
                alert('Welcome to Litha\'s FarmBook, " . $user . "! Your account has been created.'); 
                window.location.href='shop.html';
              </script>";
        exit();
        
    } else {
        echo "Error: " . $conn->error;
    }
}
$conn->close();
?>