<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];


include 'db_connect.php';
$result = $conn->query("SELECT * FROM products WHERE user_id = '$user_id'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Seller Dashboard - <?php echo $username; ?></title>
   <style>

    body{
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
    }

    header{
        background-color: #004751;
        color: white;
        text-align: center;
        padding: 20px;
    }

    .container{
        max-width: 900px;
        background-color: white;
        margin: 20px auto;
        padding: 20px;
        border-radius: 8px;
    }

    table{
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th,
    td{
        border: 1px solid #ccc;
        padding: 12px;
        text-align: left;
    }

    th{
        background-color: #f2f2f2;
    }

    .btn-delete{
        color: red;
        text-decoration: none;
        font-weight: bold;
    }

    .back-btn{
        text-decoration: none;
        color: #004751;
        font-weight: bold;
        display: inline-block;
        margin-bottom: 20px;
    }

</style>
</head>
<body>

<header>
    <h1>Welcome to your Dashboard, <?php echo $username; ?></h1>
</header>
    
    
    <div style="background: #fff; padding: 15px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 6px;">
    <span>Want to stop selling items?</span>
    <a href="toggle_role.php" style="background: #e74c3c; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-weight: bold; margin-left: 10px;" onclick="return confirm('This will hide your dashboard access until you opt back in. Continue?')">
        Switch Account Back to Buyer
    </a>
</div>

<div class="container">
    <a href="shop.html" class="back-btn">← Back to Shop</a>
    <h2>Your Active Listings</h2>
    <table>
        <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Date Uploaded</th>
            <th>Actions</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['product_name']; ?></td>
            <td>R <?php echo number_format($row['product_price'], 2); ?></td>
            <td><?php echo $row['created_at']; ?></td>
            <td>
                <a href="delete_product.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>