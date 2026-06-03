<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($username) || empty($new_password) || empty($confirm_password)) {
        die("<p style='color:red; text-align:center; font-family:sans-serif; margin-top:50px;'>Please fill in all fields.</p>");
    }

    // Verify if passwords match before querying the database
    if ($new_password !== $confirm_password) {
        die("<div style='text-align:center; font-family:sans-serif; margin-top:50px; color:red;'>
                <h2>Passwords Do Not Match</h2>
                <p>The confirmation password did not match your new choice.</p>
                <br>
                <a href='reset_password.html' style='color:#1e8449; font-weight:bold; text-decoration:none;'>← Try Again</a>
              </div>");
    }

    // Look up the unique account by username
    $check_sql = "SELECT id FROM users WHERE username = '$username'";
    $result = $conn->query($check_sql);

    if ($result && $result->num_rows > 0) {
        // Securely hash the verified password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the account row
        $update_sql = "UPDATE users SET password = '$hashed_password' WHERE username = '$username'";

        if ($conn->query($update_sql) === TRUE) {
            echo "<div style='text-align:center; font-family:sans-serif; margin-top:50px;'>
                    <h2 style='color:#1e8449;'>Password Updated Successfully!</h2>
                    <p>Your new credentials are live. You can log in immediately.</p>
                    <br><br>
                    <a href='login.html' style='background:#1e8449; color:white; padding:12px 20px; text-decoration:none; border-radius:6px; font-weight:bold;'>Sign In</a>
                  </div>";
        } else {
            echo "<p style='color:red; text-align:center; font-family:sans-serif;'>Error executing update: " . $conn->error . "</p>";
        }
    } else {
        echo "<div style='text-align:center; font-family:sans-serif; margin-top:50px; color:red;'>
                <h2>Username Not Found</h2>
                <p>The username provided is not registered on FarmBook.</p>
                <br>
                <a href='reset_password.html' style='color:#1e8449; font-weight:bold; text-decoration:none;'>← Try Again</a>
              </div>";
    }

    $conn->close();
} else {
    header("Location: reset_password.html");
    exit();
}
?>