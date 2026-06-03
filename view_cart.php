<?php
session_start();

include 'db_connect.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//handle removing an item from the cart
if (isset($_GET['action']) && $_GET['action'] == 'remove') {
    $id = intval($_GET['id']);
    unset($_SESSION['cart'][$id]);
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
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #004751; color: white; }
        .total-row { font-size: 18px; font-weight: bold; text-align: right; }
        .btn { display: inline-block; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: bold; }
        .btn-checkout { background-color: #ffa41c; color: white; float: right; }
        .btn-checkout:hover { background: #e08b10; }
        .btn-remove { color: #ff4d4d; text-decoration: none; font-size: 14px; }
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
            <a href="checkout.php" class="btn btn-checkout">Proceed to Checkout</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>