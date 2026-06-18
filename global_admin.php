<?php
session_start();

// Security check: Only let admins view this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.html");
    exit();
}

include 'db_connect.php';

// --- HELP DESK ACTIONS ROUTER: FLIPS TICKET STATE TO RESOLVED ---
if (isset($_GET['action']) && $_GET['action'] == 'resolve_ticket') {
    $ticket_id = intval($_GET['id']);
    
    // Update local row status state directly to clear the open dashboard item
    $update_query = "UPDATE support_tickets SET status = 'Resolved' WHERE id = $ticket_id";
    if (mysqli_query($conn, $update_query)) {
        echo "<script>alert('Ticket #$ticket_id has been marked as Resolved!'); window.location.href='admin_dashboard.php';</script>";
        exit();
    }
}

// --- BACKGROUND AJAX HANDLER: RUNS SILENTLY WITHOUT LOADING PAGES ---
if (isset($_GET['ajax_action']) && $_GET['ajax_action'] === 'clear_payout') {
    $seller_id = intval($_GET['seller_id']);
    
    // Updates order rows to 'Paid Out' matching this seller's products
    $update_payouts_sql = "UPDATE orders o
                           JOIN products p ON o.summary LIKE CONCAT('%', p.product_name, '%')
                           SET o.status = 'Paid Out'
                           WHERE p.user_id = $seller_id AND o.status = 'Product Received'";
    
    if ($conn->query($update_payouts_sql)) {
        echo "success";
    } else {
        echo "error: " . $conn->error;
    }
    exit(); // Stop script execution here for background actions
}

// 1. Fetch Summary Totals for top cards
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'buyer' OR role = 'seller'")->fetch_assoc()['count'];
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_value = $conn->query("SELECT SUM(product_price) as total FROM products")->fetch_assoc()['total'] ?? 0;

// 2. Fetch Users and add up their total item prices
$users_sql = "SELECT users.id, users.username, users.role, SUM(products.product_price) as total_made 
              FROM users 
              LEFT JOIN products ON users.id = products.user_id 
              GROUP BY users.id";
$users_result = $conn->query($users_sql);

// 3. Fetch All Products across the whole app
$products_sql = "SELECT products.id, products.product_name, products.product_price, products.created_at, users.username 
                 FROM products 
                 LEFT JOIN users ON products.user_id = users.id 
                 ORDER BY products.id DESC";
$products_result = $conn->query($products_sql);

// 4. FIXED CORRECTED QUERY: Tracks gross metrics via product unit prices (p.product_price) instead of multi-item grand totals
$seller_payouts_sql = "SELECT 
                            u.id AS seller_id, 
                            u.username AS seller_name,
                            GROUP_CONCAT(DISTINCT p.product_name SEPARATOR ', ') AS sold_items,
                            SUM(CASE WHEN o.status = 'Product Received' OR o.status = 'Paid Out' THEN p.product_price ELSE 0 END) AS gross_sales,
                            MAX(o.status) as order_status
                       FROM orders o
                       JOIN products p ON o.summary LIKE CONCAT('%', p.product_name, '%')
                       JOIN users u ON p.user_id = u.id
                       WHERE o.status = 'Product Received' OR o.status = 'Paid Out'
                       GROUP BY u.id";
$seller_payouts_result = $conn->query($seller_payouts_sql);

// 5. FETCH ACTIVE UNRESOLVED HELP DESK TICKETS
$tickets_result = mysqli_query($conn, "SELECT * FROM support_tickets WHERE status = 'Pending' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - Litha's Store</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f9f9f9; }
        header { background: #004751; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { margin: 0; font-size: 24px; }
        
        .stats-container { display: flex; gap: 20px; margin-bottom: 30px; }
        .stat-box { background: white; padding: 20px; flex: 1; border: 1px solid #ddd; border-radius: 6px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        
        .section-box { background: white; padding: 20px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .section-box h2 { color: #004751; border-bottom: 2px solid #f2f2f2; padding-bottom: 10px; margin-top: 0; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; color: #444; }
        
        .btn-danger { color: red; text-decoration: none; font-weight: bold; }
        .btn-danger:hover { text-decoration: underline; }
        .nav-btn { background: #72d54e; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; font-weight: bold; }
        
        .btn-pay { background-color: #0076bd; color: white; border: none; padding: 8px 16px; font-weight: bold; border-radius: 4px; cursor: pointer; transition: 0.2s; }
        .btn-pay:hover { background-color: #00568a; }
        
        .paid-text { color: #777; font-weight: bold; font-style: italic; }
        .btn-success { background: #72d54e; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 13px; font-weight: bold; display: inline-block; }
        .btn-success:hover { background: #5db33f; }
    </style>
</head>
<body>

<header>
    <h1>Litha's Store - Admin Dashboard</h1>
    <span>
        <a href="shop.html" class="nav-btn" style="background:#0076bd;">Go to Shop</a>
        <a href="logout.php" class="nav-btn" style="background:red;">Logout</a>
    </span>
</header>

<div class="stats-container">
    <div class="stat-box">
        <h3>Total Marketplace Users</h3>
        <h2><?php echo $total_users; ?></h2>
    </div>
    <div class="stat-box">
        <h3>Total Products Listed</h3>
        <h2><?php echo $total_products; ?></h2>
    </div>
    <div class="stat-box">
        <h3>Total Marketplace Value</h3>
        <h2>R <?php echo number_format($total_value, 2); ?></h2>
    </div>
</div>

<div class="section-box" style="border-left: 6px solid #0076bd;">
    <h2>Seller Escrow Payouts (10% Commission Tracking)</h2>
    <table>
        <thead>
            <tr>
                <th>Seller ID</th>
                <th>Seller Username</th>
                <th>Items Sold Summary</th>
                <th>Gross Earnings</th>
                <th>Our Commission (10%)</th>
                <th>Net Owed to Seller (90%)</th>
                <th>Status / Action</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($seller_payouts_result && $seller_payouts_result->num_rows > 0):
                while($payout = $seller_payouts_result->fetch_assoc()): 
                    $gross = floatval($payout['gross_sales']);
                    $commission = $gross * 0.10;
                    $net_owed = $gross - $commission;
                    $current_status = $payout['order_status'];
                    ?>
                    <tr>
                        <td>#<?php echo $payout['seller_id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($payout['seller_name']); ?></strong></td>
                        <td style="font-size: 13px; color: #555;"><?php echo htmlspecialchars($payout['sold_items']); ?></td>
                        <td>R <?php echo number_format($gross, 2); ?></td>
                        <td style="color: #ff4d4d;">R <?php echo number_format($commission, 2); ?></td>
                        <td style="color: #72d54e; font-weight: bold;">R <?php echo number_format($net_owed, 2); ?></td>
                        <td id="status-cell-<?php echo $payout['seller_id']; ?>">
                            <?php if ($current_status === 'Paid Out'): ?>
                                <span class="paid-text">✓ Paid out</span>
                            <?php else: ?>
                                <button type="button" class="btn-pay" onclick="processPayout(<?php echo $payout['seller_id']; ?>, '<?php echo $payout['seller_name']; ?>', <?php echo $net_owed; ?>)">💳 Mark Paid</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php 
                endwhile;
            else: 
            ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 25px; color: #777;">No pending seller payouts to settle at the moment.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
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
    <h2>All Active Listings</h2>
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

<div class="section-box" style="border-left: 6px solid #72d54e;">
    <h2>Active Help Desk Support Tickets</h2>
    <table>
        <thead>
            <tr>
                <th>Ticket ID</th>
                <th>Customer Name</th>
                <th>Email</th>
                <th>Issue Category</th>
                <th>Message Details</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($tickets_result) == 0): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #777; padding: 20px;">No active pending tickets on shelf.</td>
                </tr>
            <?php else: ?>
                <?php while($ticket = mysqli_fetch_assoc($tickets_result)): ?>
                    <tr>
                        <td>#<?php echo $ticket['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($ticket['full_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($ticket['email']); ?></td>
                        <td><span style="background: #e1f5fe; color: #0288d1; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold;"><?php echo htmlspecialchars($ticket['nature_of_inquiry']); ?></span></td>
                        <td style="max-width: 300px; word-wrap: break-word;"><?php echo htmlspecialchars($ticket['message']); ?></td>
                        <td>
                            <a href="?action=resolve_ticket&id=<?php echo $ticket['id']; ?>" class="btn-success">✓ Mark Resolved</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function processPayout(sellerId, sellerName, amountOwed) {
    const confirmationPrompt = confirm(`Confirm that you have transferred R${amountOwed.toFixed(2)} to ${sellerName} via manual EFT?`);
    
    if (confirmationPrompt) {
        const currentFile = window.location.pathname.split("/").pop();
        
        fetch(`${currentFile}?ajax_action=clear_payout&seller_id=${sellerId}`)
            .then(response => response.text())
            .then(data => {
                if (data.trim() === 'success') {
                    const targetCell = document.getElementById(`status-cell-${sellerId}`);
                    targetCell.innerHTML = '<span class="paid-text">✓ Paid out</span>';
                } else {
                    alert("Database write sync update failed. Please try again.");
                }
            })
            .catch(error => {
                console.error("Error updating seller payout row status:", error);
                alert("Network error connecting to your database server.");
            });
    }
}
</script>

</body>
</html>