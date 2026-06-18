<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db_connect.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Safety check: User validation
if (!isset($_SESSION['user_id'])) {
    die("<h3>Access Denied.</h3> Please <a href='login.html'>login</a> to view your orders.");
}

$logged_in_user_id = intval($_SESSION['user_id']);

// ACTION HANDLER: When the buyer clicks "Confirm Delivery Received"
if (isset($_POST['action']) && $_POST['action'] === 'confirm_received') {
    $order_id_to_update = intval($_POST['order_id']);
    
    $update_sql = "UPDATE orders SET status = 'Product Received' WHERE id = $order_id_to_update AND user_id = $logged_in_user_id";
    if ($conn->query($update_sql)) {
        header("Location: orders.php?status_updated=1");
        exit();
    } else {
        echo "Error updating order: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Litha's Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { background-color: #f4f6f9; padding: 20px; }
        
        header { 
            background: #004751; 
            color: white; 
            padding: 15px; 
            font-size: 20px; 
            font-weight: bold; 
            margin-bottom: 30px; 
            border-radius: 4px; 
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .container { max-width: 800px; margin: 0 auto; }
        h2 { color: #333; margin-bottom: 20px; }
        
        .alert-msg { background-color: #def7ec; color: #03543f; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; }
        
        .order-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px; border-left: 5px solid #ffa41c; }
        .order-card.received { border-left-color: #0e9f6e; }
        .order-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 12px; margin-bottom: 15px; }
        
        .order-id { font-size: 1.1em; font-weight: 600; color: #004751; text-decoration: none; }
        .order-id:hover { text-decoration: underline; }
        .order-date { color: #777; font-size: 0.9em; margin-top: 4px; }
        
        .status-badge { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 0.85em; font-weight: bold; text-transform: uppercase; }
        .status-pending { background-color: #feecdc; color: #b45309; }
        .status-completed { background-color: #def7ec; color: #03543f; }
        
        .btn { display: inline-block; padding: 8px 16px; border: none; border-radius: 4px; font-weight: bold; text-decoration: none; cursor: pointer; font-size: 0.9em; }
        .btn-confirm { background-color: #0e9f6e; color: white; float: right; margin-top: -5px; }
        .btn-confirm:hover { background-color: #057a55; }
        .btn-back { background-color: #e5e7eb; color: #374151; margin-bottom: 20px; }

        .details-box { background: #fafafa; padding: 15px; border-radius: 6px; border: 1px solid #eee; margin-top: 15px; }
        .total-display { font-size: 1.2em; font-weight: bold; color: #004751; margin-top: 15px; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>

<header>
    <div style="max-width: 1200px; padding: 0 10px;">Litha's Store</div>
</header>

<div class="container">

    <?php if (isset($_GET['order_success'])): ?>
        <div class="alert-msg">🎉 Order placed successfully! Thank you for shopping with us.</div>
    <?php endif; ?>

    <?php if (isset($_GET['status_updated'])): ?>
        <div class="alert-msg">✓ Delivery confirmed! Your confirmation has been recorded successfully.</div>
    <?php endif; ?>

    <?php if (isset($_GET['view_order_id'])): 
        $view_order_id = intval($_GET['view_order_id']);
        
        $order_sql = "SELECT * FROM orders WHERE id = $view_order_id AND user_id = $logged_in_user_id LIMIT 1";
        $order_res = $conn->query($order_sql);
        
        if ($order_res && $order_res->num_rows > 0):
            $order_info = $order_res->fetch_assoc();
            ?>
            <a href="orders.php" class="btn btn-back">← Back to Orders List</a>
            <h2>Details for Order #<?php echo $view_order_id; ?></h2>
            
            <div class="order-card <?php echo ($order_info['status'] === 'Product Received') ? 'received' : ''; ?>" style="border-left-width: 0; padding-top: 5px;">
                <p style="margin-bottom: 10px;"><strong>Order Date / Time:</strong> <?php echo $order_info['created_at']; ?></p>
                <p style="margin-bottom: 15px;"><strong>Current Status:</strong> 
                    <span class="status-badge <?php echo ($order_info['status'] === 'Product Received') ? 'status-completed' : 'status-pending'; ?>">
                        <?php echo htmlspecialchars($order_info['status']); ?>
                    </span>
                </p>

                <div class="details-box">
                    <strong style="color: #555; display: block; margin-bottom: 8px;">Items Summary:</strong>
                    <p style="white-space: pre-line; color: #333; line-height: 1.6; font-size: 15px;">
                        <?php echo htmlspecialchars($order_info['summary']); ?>
                    </p>
                </div>

                <div class="total-display">
                    Grand Total: R <?php echo number_format($order_info['total_amount'], 2); ?>
                </div>
            </div>
        <?php else: ?>
            <div class="alert-msg" style="background:#fde8e8; color:#9b1c1c;">Order not found or access denied.</div>
            <a href="orders.php" class="btn btn-back">Return to List</a>
        <?php endif; ?>

    <?php else: ?>
        <h2>My Order History</h2>
        
        <?php
        $list_sql = "SELECT * FROM orders WHERE user_id = $logged_in_user_id ORDER BY id DESC";
        $list_res = $conn->query($list_sql);
        
        if ($list_res && $list_res->num_rows > 0):
            while ($row = $list_res->fetch_assoc()):
                $is_received = ($row['status'] === 'Product Received');
                ?>
                <div class="order-card <?php echo $is_received ? 'received' : ''; ?>">
                    <div class="order-header">
                        <div>
                            <a href="orders.php?view_order_id=<?php echo $row['id']; ?>" class="order-id">
                                Order #<?php echo $row['id']; ?> (Click to view details)
                            </a>
                            <div class="order-date">Placed on: <?php echo $row['created_at']; ?></div>
                        </div>
                        <div>
                            <span class="status-badge <?php echo $is_received ? 'status-completed' : 'status-pending'; ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div>
                        <strong>Total:</strong> R <?php echo number_format($row['total_amount'], 2); ?>
                        
                        <?php if (!$is_received): ?>
                            <form action="orders.php" method="POST" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="action" value="confirm_received">
                                <button type="submit" class="btn btn-confirm">Confirm Delivery Received</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="order-card" style="text-align: center; border-left: none; padding: 40px;">
                <p style="color: #666; margin-bottom: 15px;">You haven't placed any orders yet.</p>
                <a href="shop.html" class="btn" style="background:#004751; color:white;">Go to Storefront</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>

<?php $conn->close(); ?>
</body>
</html>