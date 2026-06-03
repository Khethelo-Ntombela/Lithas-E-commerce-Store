<?php
session_start();

// Security check to only let admins view this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}

include 'db_connect.php';

//Fetch Summary Totals for top cards
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch_assoc()['count'];
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_value = $conn->query("SELECT SUM(product_price) as total FROM products")->fetch_assoc()['total'] ?? 0;

//Fetch Users and add up their total item prices
$users_sql = "SELECT users.id, users.username, users.role, SUM(products.product_price) as total_made 
              FROM users 
              LEFT JOIN products ON users.id = products.user_id 
              GROUP BY users.id";
$users_result = $conn->query($users_sql);

//Fetch All Products across the whole app
$products_sql = "SELECT products.id, products.product_name, products.product_price, products.created_at, users.username 
                 FROM products 
                 LEFT JOIN users ON products.user_id = users.id 
                 ORDER BY products.id DESC";
$products_result = $conn->query($products_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f9f9f9; }
        header { background: #004751; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        
        /* Simple 3-column stats area */
        .stats-container { display: flex; gap: 20px; margin-bottom: 30px; }
        .stat-box { background: white; padding: 20px; flex: 1; border: 1px solid #ddd; border-radius: 6px; text-align: center; }
        
        /* Basic Tables styling */
        .section-box { background: white; padding: 20px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        
        .btn-danger { color: red; text-decoration: none; font-weight: bold; }
        .btn-danger:hover { text-decoration: underline; }
        .nav-btn { background: #72d54e; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>

<header>
    <span style="float: right;">
        <a href="shop.html" class="nav-btn" style="background:#0076bd;">Go to Shop</a>
        <a href="logout.php" class="nav-btn" style="background:red;">Logout</a>
    </span>
    <h1>Litha's Store- Admin Dashboard</h1>
</header>

<div class="stats-container">
    <div class="stat-box">
        <h3>Total Users</h3>
        <h2><?php echo $total_users; ?></h2>
    </div>
    <div class="stat-box">
        <h3>Total Products</h3>
        <h2><?php echo $total_products; ?></h2>
    </div>
    <div class="stat-box">
        <h3>Total Marketplace Value</h3>
        <h2>R <?php echo number_format($total_value, 2); ?></h2>
    </div>
</div>

<div class="section-box">
    <h2>Registered Accounts & User Value</h2>
    <table>
        <tr>
            <th>User ID</th>
            <th>Username</th>
            <th>Account Role</th>
            <th>Total Value Listed</th>
            <th>Actions</th>
        </tr>
        <?php while($user = $users_result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $user['id']; ?></td>
            <td><?php echo $user['username']; ?></td>
            <td><strong><?php echo strtoupper($user['role']); ?></strong></td>
            <td>R <?php echo number_format($user['total_made'] ?? 0, 2); ?></td>
            <td>
                <?php if ($user['role'] !== 'admin'): ?>
                    <a href="admin_remove_user.php?id=<?php echo $user['id']; ?>" class="btn-danger" onclick="return confirm('Wipe this user account and all their items?')">Delete User</a>
                <?php else: ?>
                    <span style="color: gray;">System Protected</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<div class="section-box">
    <h2>All Active Listings (Global Oversight)</h2>
    <table>
        <tr>
            <th>Item ID</th>
            <th>Product Name</th>
            <th>Price</th>
            <th>Uploaded By</th>
            <th>Date Uploaded</th>
            <th>Actions</th>
        </tr>
        <?php while($prod = $products_result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $prod['id']; ?></td>
            <td><?php echo $prod['product_name']; ?></td>
            <td>R <?php echo number_format($prod['product_price'], 2); ?></td>
            <td><?php echo $prod['username'] ?? 'Deleted User'; ?></td>
            <td><?php echo $prod['created_at']; ?></td>
            <td>
                <a href="admin_delete_item.php?id=<?php echo $prod['id']; ?>" class="btn-danger" onclick="return confirm('Remove this product?')">Delete Item</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>