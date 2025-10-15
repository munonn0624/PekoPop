<?php
session_start();

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['selectedItems']) && isset($data['selectedItems'])   && isset($data['total']) && isset($data['shippingFee'])
    && is_array($data['selectedItems'])) {
    $_SESSION['selected_items'] = $data['selectedItems'];
    $_SESSION['delivery_type'] = $data['deliveryType'];
    $_SESSION['shipping_fee'] = $data['shippingFee'];
    $_SESSION['total'] = $data['total'];

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error']);
}
