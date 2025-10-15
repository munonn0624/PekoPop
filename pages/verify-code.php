<!DOCTYPE html>
<html lang="en">

<head>
    <title>Verify Code</title>
    <link rel="stylesheet" href="/PekoPop/css/verify-code.css">
</head>

<body>
    <div class="verify-container">
        <h2>Enter Verification Code</h2>
        <p>We sent a 6-digit code to your email.</p>

        <form action="/submit-verification" method="POST">
            <div class="code-inputs">
                <input type="text" maxlength="1" inputmode="numeric" pattern="\d*" required>
                <input type="text" maxlength="1" inputmode="numeric" pattern="\d*" required>
                <input type="text" maxlength="1" inputmode="numeric" pattern="\d*" required>
                <input type="text" maxlength="1" inputmode="numeric" pattern="\d*" required>
                <input type="text" maxlength="1" inputmode="numeric" pattern="\d*" required>
                <input type="text" maxlength="1" inputmode="numeric" pattern="\d*" required>
            </div>

            <button type="submit">Verify</button>
        </form>

        <p class="resend">Didn't receive the code? <a href="#">Resend</a></p>
    </div>
    <script src="../js/verify-code-auto-jump.js"></script>

</body>

</html>