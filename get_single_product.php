<?php
header('Content-Type: application/json');
include 'db_connect.php';

$id = $_GET['id'];
$sql = "SELECT * FROM products WHERE id = $id";
$result = mysqli_query($conn, $sql);

echo json_encode(mysqli_fetch_assoc($result));
?>