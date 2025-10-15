<?php

session_start();
require_once '../backend/dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: /PekoPop/pages/login.php");
        exit();
    }

    $query = "SELECT user_id, email, password_hash, full_name, phone 
             FROM users 
             WHERE email = ?";

    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        $_SESSION['error'] = "System error occurred. Please try again.";
        header("Location: /PekoPop/pages/login.php");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $email);

    if (!mysqli_stmt_execute($stmt)) {
        $_SESSION['error'] = "Database error occurred. Please try again.";
        header("Location: /PekoPop/pages/login.php");
        exit();
    }

    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['phone'] = $user['phone'];

            mergeGuestCart($conn);
            header("Location: /PekoPop/index.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid email or password";
            header("Location: /PekoPop/pages/login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Invalid email or password";
        header("Location: /PekoPop/pages/login.php");
        exit();
    }

    mysqli_stmt_close($stmt);
} else {
    $_SESSION['error'] = "Invalid request method";
    header("Location: /PekoPop/pages/login.php");
    exit();
}

function mergeGuestCart($conn)
{
    // Add this after successful login, before redirect
    if (isset($_COOKIE['guest_cart'])) {
        $guest_cart = json_decode($_COOKIE['guest_cart'], true);
        echo 'this runs';
        foreach ($guest_cart as $item) {
            $query = "INSERT INTO cart_items (user_id, product_id, set_type, quantity) 
                 VALUES (?, ?, ?, ?) 
                 ON DUPLICATE KEY UPDATE quantity = quantity + ?";

            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param(
                $stmt,
                "iisii",
                $_SESSION['user_id'],
                $item['product_id'],
                $item['set_type'],
                $item['quantity'],
                $item['quantity'],

            );
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        // Clear the cookie
        setcookie('guest_cart', '', time() - 3600, '/');
    }
}

mysqli_close($conn);
