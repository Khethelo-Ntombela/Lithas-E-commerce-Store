<?php
// Start the session to track who is currently logged in uploading this ad
session_start();

// 1. Clear any hidden errors by forcing clean reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Pull the live cloud connection details
include 'db_connect.php';

// 3. Process form data on submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Grab the logged-in user's ID from the session. Fallback to 0 if not set.
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
    
    // Safely collect text inputs from the form
    $product_name  = $conn->real_escape_string($_POST['product_name'] ?? '');
    $product_size  = $conn->real_escape_string($_POST['product_size'] ?? ''); // Maps to 'Weight' input
    $product_price = floatval($_POST['product_price'] ?? 0);
    $product_info  = $conn->real_escape_string($_POST['product_description'] ?? '');
    
    // CAPTURE NEW INTERACTIVE QUANTITY FIELD
    $product_qty   = isset($_POST['product_qty']) ? intval($_POST['product_qty']) : 1;
    
    // Setup target storage directory for product images
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Array to temporarily hold file names for our 3 photos
    $uploaded_images = ["", "", ""];

    // Loop through the 3 potential file inputs
    for ($i = 1; $i <= 3; $i++) {
        $file_key = "photo" . $i; // Expecting input names: photo1, photo2, photo3
        
        // Alternative fallback check if your input names are arrays or use identical keys
        if (!isset($_FILES[$file_key]) && isset($_FILES['photos']['name'][$i-1])) {
            // Handled if files were submitted as an array name="photos[]"
            $file_name = $_FILES['photos']['name'][$i-1];
            $file_tmp  = $_FILES['photos']['tmp_name'][$i-1];
            $file_error = $_FILES['photos']['error'][$i-1];
        } else {
            // Standard explicit naming: photo1, photo2, photo3
            $file_name = $_FILES[$file_key]['name'] ?? '';
            $file_tmp  = $_FILES[$file_key]['tmp_name'] ?? '';
            $file_error = $_FILES[$file_key]['error'] ?? UPLOAD_ERR_NO_FILE;
        }

        if ($file_error === UPLOAD_ERR_OK && !empty($file_name)) {
            // Prepend unique timestamp to prevent identical file naming conflicts
            $unique_name = time() . "_" . basename($file_name);
            $target_file = $target_dir . $unique_name;

            if (move_uploaded_file($file_tmp, $target_file)) {
                $uploaded_images[$i - 1] = $unique_name;
            }
        }
    }

    // Assign mapped array assets to individual structure columns
    $img1 = $uploaded_images[0];
    $img2 = $uploaded_images[1];
    $img3 = $uploaded_images[2];

    // FIXED: Added user_id field into the SQL map setup to fulfill table requirements
    $sql = "INSERT INTO products (product_name, product_size, product_price, product_info, product_qty, img1, img2, img3, user_id) 
            VALUES ('$product_name', '$product_size', $product_price, '$product_info', $product_qty, '$img1', '$img2', '$img3', $user_id)";

    if ($conn->query($sql) === TRUE) {
        // Successfully posted! Direct seller back to storefront gallery
        header("Location: shop.html");
        exit();
    } else {
        echo "Database Error: " . $conn->error;
    }
}

$conn->close();
?>