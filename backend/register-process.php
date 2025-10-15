<?php
session_start();
require_once '../backend/dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize form data
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);

    // Validation
    $errors = [];

    // Validate full name (only letters, spaces, and basic punctuation)
    if (empty($fullname) || strlen($fullname) < 2) {
        $errors[] = "Valid full name is required";
    } elseif (!preg_match("/^[a-zA-Z]+(?: [a-zA-Z]+)*$/", $fullname)) {
        $errors[] = "Full name can only contain letters and single spaces between words";
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email address is required";
    }

    // Validate password
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }

    // Confirm passwords match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Validate phone number
    if (!preg_match("/^60/", $phone)) {
        $errors[] = "Phone number must start with '60'";
    } else {
        // Check for 6011 numbers (must be 12 digits)
        if (strpos($phone, '6011') === 0) {
            if (!preg_match("/^6011\d{8}$/", $phone)) {
                $errors[] = "Phone numbers must be valid (starting with 60) ";
            }
        }
        // Other numbers must be 11 digits
        else {
            if (!preg_match("/^60[1-9]\d{8}$/", $phone)) {
                $errors[] = "Phone number must be valid (starting with 60)";
            }
        }
    }

    // Check if email already exists
    $stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        $errors[] = "Email address already registered";
    }
    mysqli_stmt_close($stmt);

    // If there are errors, redirect back with error messages
    if (!empty($errors)) {
        $_SESSION['register_errors'] = $errors;
        // Save form data except passwords
        $_SESSION['form_data'] = [
            'fullname' => $fullname,
            'email' => $email,
            'phone' => $phone
        ];
        header("Location: /PekoPop/pages/register.php");
        exit();
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Prepare insert statement
    $stmt = mysqli_prepare($conn, "INSERT INTO users (full_name, email, password_hash, phone) VALUES (?, ?, ?, ?)");
    
    if (!$stmt) {
        $_SESSION['register_errors'] = ["System error occurred. Please try again."];
        header("Location: /PekoPop/pages/register.php");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "ssss", $fullname, $email, $password_hash, $phone);
    
    // Execute insert
    if (mysqli_stmt_execute($stmt)) {
        // Get the new user's ID
        $user_id = mysqli_insert_id($conn);
        
        // Set session variables
        $_SESSION['user_id'] = $user_id;
        $_SESSION['email'] = $email;
        $_SESSION['full_name'] = $fullname;
        $_SESSION['phone'] = $phone;
        
        // Redirect to home page
        header("Location: /PekoPop/index.php");
        exit();
    } else {
        $_SESSION['register_errors'] = ["Registration failed. Please try again."];
        header("Location: /PekoPop/pages/register.php");
        exit();
    }

    mysqli_stmt_close($stmt);
} else {
    header("Location: /PekoPop/pages/register.php");
    exit();
}

mysqli_close($conn);
?>