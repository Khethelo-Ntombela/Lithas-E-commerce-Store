<?php
// Set headers to output clean JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allows your frontend to fetch smoothly

// Pull your working live database credentials
include 'db_connect.php';

// 1. Check if a search query was passed from the front-end
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// 2. Run the product query
if (!empty($search)) {
    $sql = "SELECT * FROM products WHERE product_name LIKE '%$search%' OR product_info LIKE '%$search%' ORDER BY id DESC";
} else {
    $sql = "SELECT * FROM products ORDER BY id DESC";
}

$result = $conn->query($sql);
$products = [];

// 3. Build a true array list
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// 4. Force output to always be a valid list format array
echo json_encode($products);

$conn->close();
?>