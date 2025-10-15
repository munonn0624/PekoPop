<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug session data
error_log('Session data in profile.php: ' . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /PekoPop/pages/login.php");
    exit();
}

// Get user data from session
$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];
$email = $_SESSION['email'];
$phone = $_SESSION['phone'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <link rel="stylesheet" href="/PekoPop/css/header.css">
    <link rel="stylesheet" href="/PekoPop/css/profile.css">
    <link rel="stylesheet" href="/PekoPop/css/footer.css">
    <script src="https://kit.fontawesome.com/a737b063d1.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../include/header.php'; ?>
    
    <section class="profile-page">
        <div class="profile-container">
            <h1>My Profile</h1>
            <div class="profile-info">
                <div class="info-group">
                    <label>Full Name:</label>
                    <p><?php echo htmlspecialchars($full_name); ?></p>
                </div>
                <div class="info-group">
                    <label>Email:</label>
                    <p><?php echo htmlspecialchars($email); ?></p>
                </div>
                <div class="info-group">
                    <label>Phone:</label>
                    <p><?php echo htmlspecialchars($phone); ?></p>
                </div>
                <a href="/PekoPop/backend/logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </section>

    <?php include '../include/footer.php'; ?>
</body>
</html>