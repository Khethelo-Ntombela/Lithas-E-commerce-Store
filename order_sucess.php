<?php
// Capture the customer's name from the URL string safely
$name = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : 'Customer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmed - Litha's FarmBook</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; text-align: center; padding: 50px 20px; }
        .success-container { max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .success-icon { font-size: 60px; color: #72d54e; margin-bottom: 20px; }
        h2 { color: #004751; margin-top: 0; }
        p { color: #555; line-height: 1.6; font-size: 16px; }
        .btn-home { display: inline-block; background-color: #ffa41c; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; margin-top: 25px; transition: 0.2s; }
        .btn-home:hover { background-color: #e08b10; }
    </style>
</head>
<body>

<div class="success-container">
    <div class="success-icon">✓</div>
    <h2>Thank You, <?php echo $name; ?>!</h2>
    <p>Order has been processed successfully.</p>
    <p>The shopping cart session has been cleared out. You can now safely return to the shop front.</p>
    
    <a href="shop.html" class="btn-home">Return to Storefront</a>
</div>

</body>
</html>