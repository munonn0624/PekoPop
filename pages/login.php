<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Login</title>
    <link rel="stylesheet" href="/PekoPop/css/header.css">
    <link rel="stylesheet" href="/PekoPop/css/login.css">
    <link rel="stylesheet" href="/PekoPop/css/footer.css">
    <script src="https://kit.fontawesome.com/a737b063d1.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body>
    <?php include '../include/header.php'; ?>

    <section class="login-page">
        <div class="login-container">
            <h1 class="login-title">Sign In</h1>
            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger" role="alert">';
                echo htmlspecialchars($_SESSION['error']);
                echo '</div>';
                unset($_SESSION['error']);
            }
            ?>
            <form class="login-form" action="/PekoPop/backend/login-process.php" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" >
                </div>

                <input type="submit" class="checkout-button" value="Log In">

                <div class="login-links">
                    <a href="/PekoPop/pages/forgot-password.php">Forgot Password?</a>
                    <span> | </span>
                    <a href="/PekoPop/pages/register.php">Create Account</a>
                </div>
            </form>
        </div>
    </section>
    <?php include '../include/footer.php'; ?>

</body>

</html>