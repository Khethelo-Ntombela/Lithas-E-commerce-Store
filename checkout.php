<?php
session_start();
include 'db_connect.php';

// Ensure the user is logged in and has items in their cart
if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header("Location: shop.html");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$cart_total = 0.00;
$summary_text = "";

// Calculate the total order amount from your live database session cart and generate summary
foreach ($_SESSION['cart'] as $product_id => $qty) {
    $clean_id = intval($product_id);
    $quantity = intval($qty);
    
    $sql = "SELECT product_name, product_price FROM products WHERE id = $clean_id";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        $price = floatval($row['product_price']);
        $subtotal = $price * $quantity;
        $cart_total += $subtotal;
        
        // Build clear line summary entries
        $summary_text .= $quantity . " x " . $row['product_name'] . " (R " . number_format($price, 2) . " each) - Subtotal: R " . number_format($subtotal, 2) . "\n";
    }
}

// TEMPORARY SANDBOX TEST CONFIG: Points to PayFast's open test accounts
$merchant_id  = "10000100"; 
$merchant_key = "46f0cd694581a"; 

// Create a unique tracking reference for this specific sale order
$order_id = time() . "_" . $user_id;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Payment - FarmBook</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; text-align: center; padding: 50px; }
        .payment-box { background: white; padding: 40px; max-width: 450px; margin: 0 auto; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h2 { color: #004751; }
        .total-amount { font-size: 24px; font-weight: bold; color: #2ec249; margin: 20px 0; }
        .btn-pay { background: #ffa41c; color: white; border: none; padding: 12px 30px; font-size: 16px; font-weight: bold; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-pay:hover { background: #e08b10; }
    </style>
</head>
<body>

<div class="payment-box">
    <h2>Secure Checkout (Sandbox Mode)</h2>
    <p>Your livestock and produce order reference: <strong><?php echo $order_id; ?></strong></p>
    <div class="total-amount">Total Due: R <?php echo number_format($cart_total, 2); ?></div>

    <form action="https://sandbox.payfast.co.za/eng/process" method="POST">
        <input type="hidden" name="merchant_id" value="<?php echo $merchant_id; ?>">
        <input type="hidden" name="merchant_key" value="<?php echo $merchant_key; ?>">

        <input type="hidden" name="return_url" value="https://lithas-store.infinityfreeapp.com/order_sucess.php?id=<?php echo $order_id; ?>">
        <input type="hidden" name="cancel_url" value="https://lithas-store.infinityfreeapp.com/view_cart.php">
        <input type="hidden" name="notify_url" value="https://lithas-store.infinityfreeapp.com/process_payment.php">

        <input type="hidden" name="m_payment_id" value="<?php echo $order_id; ?>">
        <input type="hidden" name="amount" value="<?php echo number_format($cart_total, 2, '.', ''); ?>">
        <input type="hidden" name="item_name" value="Litha's Store Order #<?php echo $order_id; ?>">

        <input type="hidden" name="email_address" value="ntombelakhethelo15@gmail.com">

        <input type="hidden" name="custom_str1" value="<?php echo htmlspecialchars(trim($summary_text)); ?>">

        <button type="submit" class="btn-pay">💳 Pay Securely with PayFast Sandbox</button>
    </form>
</div>

</body>
</html>