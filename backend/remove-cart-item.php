<?php
session_start();
require_once 'dbconnect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request data']);
    exit;
}

if (isset($_SESSION['user_id']) && isset($data['cart_item_id'])) {
    // Logged-in user: remove from DB
    $query = "DELETE FROM cart_items WHERE cart_item_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        exit;
    }
    mysqli_stmt_bind_param($stmt, "ii", $data['cart_item_id'], $_SESSION['user_id']);
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Item not found']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_stmt_error($stmt)]);
    }
    mysqli_stmt_close($stmt);
} elseif (isset($data['product_id'])) {
    // Guest user: remove from cookie cart
    if (isset($_COOKIE['guest_cart'])) {
        $cart = json_decode($_COOKIE['guest_cart'], true);
        $key = $data['product_id'];  // string key like "4-single"

        if (isset($cart[$key])) {
            unset($cart[$key]);
            setcookie('guest_cart', json_encode($cart), time() + 3600, '/');
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Item not found in guest cart']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No guest cart found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request data']);
}

mysqli_close($conn);
