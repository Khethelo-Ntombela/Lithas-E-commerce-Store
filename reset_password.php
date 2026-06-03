<?php
// Start session to track messages
session_start();

// Include your central database connection settings file
require_once 'db_connect.php';

$message = "";
$message_class = "";

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize user inputs matching the 'name' attributes in your HTML form
    $username = trim($_POST['username']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic Validation Checks
    if (empty($username) || empty($new_password) || empty($confirm_password)) {
        $message = "Please fill in all fields.";
        $message_class = "error";
    } elseif ($new_password !== $confirm_password) {
        $message = "Passwords do not match!";
        $message_class = "error";
    } else {
        // Check if the username exists in the users table
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $message = "Username not found in our system.";
            $message_class = "error";
        } else {
            // Update password directly using safe SQL statements
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $update_stmt->bind_param("ss", $new_password, $username);

            if ($update_stmt->execute()) {
                $message = "Password reset successfully! Redirecting to login...";
                $message_class = "success";
                // Redirect back to login page after 2 seconds
                header("refresh:2;url=login.html");
            } else {
                $message = "Something went wrong. Please try again.";
                $message_class = "error";
            }
            $update_stmt->close();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - FarmBook</title>
    <style>
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', Arial, sans-serif;
        }

        body{
            background-color: #f0f2f5;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column; /* Allows alert message to stack nicely */
        }

        .reset-box { 
            background: white; 
            padding: 40px; 
            border-radius: 15px; 
            box-shadow: 0 8px 24px rgba(0,0,0,0.05); 
            width: 100%; 
            max-width: 420px; 
        }

        h2 { 
            color: #1e8449; 
            margin-bottom: 25px; 
            font-size: 24px; 
            text-align: center;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 600; 
            font-size: 14px; 
            color: #444; 
        }

        .input-group input { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #ccc; 
            border-radius: 6px; 
            font-size: 14px; 
            outline: none;
            transition: 0.2s; 
        }

        .input-group input:focus { 
            border-color: #1e8449; 
        }

        .btn-reset { 
            background: #1e8449; 
            color: white; 
            border: none; 
            width: 100%; 
            padding: 14px; 
            font-weight: bold; 
            font-size: 16px; 
            border-radius: 6px; 
            cursor: pointer; 
            transition: background 0.2s; 
        }

        .btn-reset:hover { 
            background-color: #196f3d; 
        }

        .links { 
            text-align: center; 
            margin-top: 25px; 
            font-size: 14px;
        }

        .links a { 
            color: #777; 
            text-decoration: none; 
        }
        
        .links a:hover {
            text-decoration: underline;
        }

        /* Added clean alert styles to match your UI format */
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
            font-size: 14px;
            width: 100%;
            max-width: 420px;
        }
        .error { background-color: #fde8e8; color: #9b1c1c; border: 1px solid #f8b4b4; }
        .success { background-color: #def7ec; color: #03543f; border: 1px solid #bcf0da; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>

<?php if (!empty($message)): ?>
    <div class="alert <?php echo $message_class; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="reset-box">
    <h2>Reset Your Password</h2>
    
    <form action="reset_password.php" method="POST">
        <div class="input-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required>
        </div>

        <div class="input-group">
            <label for="new_password">Choose New Password</label>
            <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
        </div>

        <div class="input-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat new password" required>
        </div>

        <button type="submit" class="btn-reset">Update Password</button>
    </form>

    <div class="links">
        <a href="login.html">← Back to Login</a>
    </div>
</div>

</body>
</html>