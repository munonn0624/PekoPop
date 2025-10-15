<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'dbconnect.php';

// Decode JSON body
$data = json_decode(file_get_contents('php://input'), true);
error_log("DEBUG: PHP is running this script!");

error_log("DEBUG: Raw input = " . file_get_contents('php://input'));
error_log("DEBUG: Decoded data = " . json_encode($data));

if (!$data || !isset($data['product_id']) || !isset($data['quantity'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

$productId = (int)$data['product_id'];
$newQuantity = (int)$data['quantity'];
$set_type = $data['set_type'];


// Step 1: Get stock availability
$stockQuery = "SELECT stock FROM product_details WHERE product_id = ? AND set_type = ?";
$stockStmt = mysqli_prepare($conn, $stockQuery);
mysqli_stmt_bind_param($stockStmt, "is", $productId, $set_type);
mysqli_stmt_execute($stockStmt);
mysqli_stmt_bind_result($stockStmt, $stock);
mysqli_stmt_fetch($stockStmt);
mysqli_stmt_close($stockStmt);

if (!isset($stock)) {
    echo json_encode(['status' => 'error', 'message' => 'Product not found']);
    exit;
}

// Step 2: Handle logged-in user cart
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Check current quantity in cart
    $cartCheckQuery = "SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ? AND set_type = ?";
    $cartStmt = mysqli_prepare($conn, $cartCheckQuery);
    mysqli_stmt_bind_param($cartStmt, "iis", $userId, $productId, $set_type);
    mysqli_stmt_execute($cartStmt);
    mysqli_stmt_bind_result($cartStmt, $currentQty);
    mysqli_stmt_fetch($cartStmt);
    mysqli_stmt_close($cartStmt);

    if ($newQuantity > $stock) {
        echo json_encode(['status' => 'error', 'message' => 'Exceeds available stock.']);
        exit;
    }

    // Update quantity
    $updateQuery = "UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ? AND set_type = ?";
    $updateStmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($updateStmt, "iiis", $newQuantity, $userId, $productId, $set_type);
    $success = mysqli_stmt_execute($updateStmt);
    $error = mysqli_error($conn);
    mysqli_stmt_close($updateStmt);

    if ($success) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $error]);
    }

    mysqli_close($conn);
    exit;
}

// Step 3: Guest user cart
if (isset($_COOKIE['guest_cart'])) {
    $cart = json_decode($_COOKIE['guest_cart'], true);
    $found = false;

    foreach ($cart as &$item) {
        if ($item['product_id'] == $productId) {
            if ($newQuantity > $stock) {
                echo json_encode(['status' => 'error', 'message' => 'Exceeds available stock.']);
                exit;
            }
            $item['quantity'] = $newQuantity;
            $found = true;
            break;
        }
    }

    if ($found) {
        setcookie('guest_cart', json_encode($cart), time() + 3600, '/');
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Product not found in guest cart']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No guest_cart cookie found']);
}

mysqli_close($conn);
