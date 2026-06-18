<?php
// FORCE ERROR REPORTING ON - This replaces the 500 error with the exact line number that broke!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include 'db_connect.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle removing an item from the cart
if (isset($_GET['action']) && $_GET['action'] == 'remove') {
    $id = intval($_GET['id']);
    unset($_SESSION['cart'][$id]);
    
    // SAFETY FIX: If the cart is now completely empty, clear the seller lock entirely
    if (empty($_SESSION['cart'])) {
        unset($_SESSION['cart_seller_id']);
    }
    
    header("Location: view_cart.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Cart - Store</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; padding: 20px; margin: 0; }
        header { background: #004751; color: white; padding: 15px; font-size: 20px; font-weight: bold; margin-bottom: 30px; }
        .cart-container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h2 { border-bottom: 2px solid #3e3347; padding-bottom: 10px; color: #3e3347; margin-top: 0; }
        
        /* Notice Styling for Vendor Enforcement */
        .vendor-alert {
            background-color: #fdf2f2; 
            color: #9b1c1c; 
            border-left: 5px solid #f05252; 
            padding: 15px; 
            margin-top: 15px;
            margin-bottom: 15px; 
            border-radius: 4px; 
            font-size: 14px;
            line-height: 1.5;
        }
        .vendor-alert strong {
            display: block;
            margin-bottom: 3px;
            font-size: 15px;
        }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #004751; color: white; }
        .total-row { font-size: 18px; font-weight: bold; text-align: right; }
        
        /* Button & Link Layout Styles */
        .btn { display: inline-block; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: bold; }
        .btn-checkout { background-color: #ffa41c; color: white; float: right; }
        .btn-checkout:hover { background: #e08b10; }
        .btn-remove { color: #ff4d4d; text-decoration: none; font-size: 14px; }
        
        /* Styled link to go back to shop */
        .btn-back { background-color: #e5e7eb; color: #374151; float: left; transition: background 0.2s; }
        .btn-back:hover { background-color: #d1d5db; }
        
        .empty-message { text-align: center; padding: 40px; font-size: 18px; color: #666; }
        .shop-link { color: #0076bd; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<header>
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">Litha's FarmBook</div>
</header>

<div class="cart-container">
    <h2>Your Shopping Cart</h2>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'different_seller'): ?>
        <div class="vendor-alert">
            <strong>🛒 One Vendor per Checkout Notice</strong>
            You can only process items from one vendor at a time. Please complete your current order or remove your current items before adding products from a different seller.
        </div>
    <?php endif; ?>

    <?php if (empty($_SESSION['cart'])): ?>
        <p class="empty-message">Your cart is empty. <a href="shop.html" class="shop-link">Go back to shopping!</a></p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grand_total = 0;
                
                foreach ($_SESSION['cart'] as $product_id => $quantity) {
                    $clean_id = intval($product_id);
                    $sql = "SELECT * FROM products WHERE id = $clean_id";
                    $result = $conn->query($sql);
                    
                    if ($result && $result->num_rows > 0) {
                        $product = $result->fetch_assoc();
                        
                        $price = floatval($product['product_price']);
                        $subtotal = $price * intval($quantity);
                        $grand_total += $subtotal;
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($product['product_name']); ?></strong></td>
                            <td>R <?php echo number_format($price, 2); ?></td>
                            <td><?php echo intval($quantity); ?></td>
                            <td>R <?php echo number_format($subtotal, 2); ?></td>
                            <td><a href="view_cart.php?action=remove&id=<?php echo $clean_id; ?>" class="btn-remove">Remove</a></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                <tr>
                    <td colspan="3" class="total-row">Grand Total:</td>
                    <td colspan="2" class="total-row" style="text-align: left;">R <?php echo number_format($grand_total, 2); ?></td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 20px; overflow: auto;">
            <a href="shop.html" class="btn btn-back">← Back to Store</a>
            <a href="checkout.php" class="btn btn-checkout">Proceed to Checkout</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>