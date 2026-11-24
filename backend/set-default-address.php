<?php
// Implement Later
session_start();
require_once 'dbconnect.php';

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$new_default = $data['address_id'];

// Remove old default
mysqli_query($conn, "UPDATE user_addresses SET is_default = 0 WHERE user_id = '$user_id'");

// Set new default
mysqli_query($conn, "UPDATE user_addresses SET is_default = 1 WHERE user_id = '$user_id' AND address_id = '$new_default'");

echo json_encode(['success' => true]);
