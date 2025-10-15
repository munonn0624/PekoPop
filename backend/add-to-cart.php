<?php
session_start();
require_once 'dbconnect.php';

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required data
if (
    !isset($data['product_id']) ||
    !isset($data['quantity']) ||
    !isset($data['set_type']) ||
    !isset($data['price'])
) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required data']);
    exit;
}

$productId = (int)$data['product_id'];
$newQuantity = (int)$data['quantity'];
$setType = $data['set_type'];
$unitPrice = (float)$data['price'];

// Get available stock
$availableStock = getProductStock($conn, $productId);
if ($availableStock <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Product is out of stock']);
    exit;
}

if (isset($_SESSION['user_id'])) {
    // ✅ Logged-in user (Database cart)
    $userId = (int)$_SESSION['user_id'];

    // Get current quantity
    $currentQty = getExistingCartQuantity($conn, $userId, $productId, $setType);
    $remaining = $availableStock - $currentQty;

    if ($remaining <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Cannot add more — stock limit reached']);
        exit;
    }

    $toAdd = min($newQuantity, $remaining);

    $query = "INSERT INTO cart_items (user_id, product_id, set_type, quantity)
              VALUES (?, ?, ?, ?)
              ON DUPLICATE KEY UPDATE quantity = LEAST(quantity + VALUES(quantity), ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iisii", $userId, $productId, $setType, $toAdd, $availableStock);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Added to cart']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
    mysqli_stmt_close($stmt);
} else {
    // ✅ Guest user (Cookie cart)
    $cart = isset($_COOKIE['guest_cart']) ? json_decode($_COOKIE['guest_cart'], true) : [];
    $itemKey = "{$productId}_{$setType}";

    $currentQty = isset($cart[$itemKey]) ? $cart[$itemKey]['quantity'] : 0;
    $remaining = $availableStock - $currentQty;

    if ($remaining <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Cannot add more — stock limit reached']);
        exit;
    }

    if ($newQuantity > $remaining) {
        echo json_encode([
            'status' => 'error',
            'message' => "Only {$remaining} more available in stock"
        ]);
        exit;
    }

    $toAdd = $newQuantity;


    $cart[$itemKey] = [
        'product_id' => $productId,
        'set_type' => $setType,
        'quantity' => $currentQty + $toAdd,
        'price' => $unitPrice
    ];

    setcookie('guest_cart', json_encode($cart, JSON_UNESCAPED_UNICODE), time() + 3600, '/');
    echo json_encode(['status' => 'success', 'message' => 'Added to cart']);
}

mysqli_close($conn);


/**
 * ✅ Get user's current quantity for this item
 */
function getExistingCartQuantity($conn, $userId, $productId, $setType)
{
    $query = "SELECT quantity FROM cart_items 
              WHERE user_id = ? AND product_id = ? AND set_type = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iis", $userId, $productId, $setType);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $quantity);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    return (int)$quantity;
}

/**
 * ✅ Get available stock from products table
 */
function getProductStock($conn, $productId)
{
    $query = "SELECT stock FROM product_details WHERE product_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $productId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $stock);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    return (int)$stock;
}
