<?php
header('Content-Type: application/json');
$conn = mysqli_connect("localhost:3307", "root", "", "user_registration");

$id = $_GET['id'];
$sql = "SELECT * FROM products WHERE id = $id";
$result = mysqli_query($conn, $sql);

echo json_encode(mysqli_fetch_assoc($result));
?>