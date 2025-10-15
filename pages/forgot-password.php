<!DOCTYPE html>
<html lang="en">

<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="/PekoPop/css/forgot-password.css">
</head>

<body class="forgot-password-page">
    <div class="forgot-password-container">
        <h1 class="forgot-title">Forgot Password</h1>
        <p class="forgot-subtitle">
            Enter your email address and we’ll send you a verification code to reset your password.
        </p>

        <form class="forgot-form" action="/PekoPop/php/send-code.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>

            <input type="submit" class="checkout-button" value="Send Code">

            <div class="forgot-links">
                <a href="/PekoPop/pages/login.php">Back to Login</a>
            </div>
        </form>
    </div>
</body>

</html>