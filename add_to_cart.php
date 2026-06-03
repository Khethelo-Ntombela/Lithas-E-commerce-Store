<?php
session_start();

// If the user doesn't have a basket initialized yet,this creates an empty array container
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Catch the dynamic product ID passed from the shop button URL (?id=X)
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']); // Clean the ID value to keep it safe
    
    // Catch the dynamic quantity passed from the URL (?qty=X), fallback to 1 if not provided
    $qty = isset($_GET['qty']) ? intval($_GET['qty']) : 1;
    if ($qty < 1) {
        $qty = 1; // Safety fallback to avoid zero or negative additions
    }
    
    // Check if the product is already in the cart - add the selected quantity if it is
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $qty;
    } else {
        // Otherwise, add it with the chosen starting quantity
        $_SESSION['cart'][$product_id] = $qty;
    }
    
    // Smoothly bounce them straight to your cart page to see their items
    header("Location: view_cart.php");
    exit();
} else {
    // Safety fallback: if no ID was specified, send them back to shop
    header("Location: shop.html");
    exit();
}
?>