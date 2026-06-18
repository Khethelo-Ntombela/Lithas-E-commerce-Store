<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db_connect.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("<h3>Access Denied.</h3> Please <a href='login.html'>login</a>.");
}

$user_id = intval($_SESSION['user_id']);
$grand_total = 0.00;
$summary_lines = [];

$order_tracking_ref = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : time();

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $qty) {
        $clean_id = intval($product_id);
        $quantity = intval($qty);
        
        $sql = "SELECT product_name, product_price FROM products WHERE id = $clean_id";
        $result = $conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            $price = floatval($row['product_price']);
            $subtotal = $price * $quantity;
            $grand_total += $subtotal;
            
            $summary_lines[] = $quantity . " x " . $row['product_name'] . " (R " . number_format($price, 2) . " each)";
        }
    }
}

$check_duplicate = $conn->query("SELECT id FROM orders WHERE summary LIKE '%[Ref: $order_tracking_ref]%' LIMIT 1");

if ($check_duplicate && $check_duplicate->num_rows == 0) {
    if ($grand_total > 0) {
        $final_summary = implode("\n", $summary_lines) . "\n[Ref: " . $order_tracking_ref . "]";
        $safe_summary = mysqli_real_escape_string($conn, $final_summary);
        
        $insert_sql = "INSERT INTO orders (user_id, total_amount, summary, status) 
                       VALUES ($user_id, $grand_total, '$safe_summary', 'Pending Delivery')";
        
        if ($conn->query($insert_sql)) {
            unset($_SESSION['cart']);
            unset($_SESSION['cart_seller_id']);
        }
    }
} else {
    $grand_total = 1.00; 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmed - Litha's Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f4f4; text-align: center; padding: 50px 20px; }
        .success-container { max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .success-icon { font-size: 60px; color: #72d54e; margin-bottom: 20px; }
        h2 { color: #004751; margin-top: 0; margin-bottom: 10px; }
        p { color: #555; line-height: 1.6; font-size: 16px; margin-bottom: 15px; }
        
        /* New Call-to-Action Button Styling */
        .btn-orders { 
            display: inline-block; 
            background-color: #0076bd; 
            color: white; 
            padding: 14px 28px; 
            text-decoration: none; 
            border-radius: 5px; 
            font-weight: bold; 
            font-size: 16px; 
            margin-top: 15px; 
            box-shadow: 0 4px 6px rgba(0,118,189,0.2);
            transition: 0.2s; 
        }
        .btn-orders:hover { 
            background-color: #005d96; 
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<div class="success-container">
    <div class="success-icon">✓</div>
    <h2>Payment Successful!</h2>
    <p>Thank you! Your order has been registered securely.</p>
    <p style="margin-bottom: 25px;">Order Reference: <strong><?php echo htmlspecialchars($order_tracking_ref); ?></strong></p>
    
    <a href="orders.php" class="btn-orders">📦 Go to Track Orders</a>
</div>

</body>
</html>