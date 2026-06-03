<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = "sql104.infinityfree.com";
$user = "if0_42023814";
$pass = "Litha2466"; 
$db   = "if0_42023814_store";

include 'db_connect.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>