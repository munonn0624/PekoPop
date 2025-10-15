<?php
session_start();

// Get old form data if it exists
$old = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
unset($_SESSION['form_data']); // Clear it after retrieving
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Login</title>
    <link rel="stylesheet" href="/PekoPop/css/header.css">
    <link rel="stylesheet" href="/PekoPop/css/register.css">
    <link rel="stylesheet" href="/PekoPop/css/footer.css">
    <script src="https://kit.fontawesome.com/a737b063d1.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body>
    <?php include '../include/header.php'; ?>

    <section class="register-page">
        <div class="register-container">
            <h1 class="register-title">Create Account</h1>
            <?php
            if (isset($_SESSION['register_errors'])) {
                foreach ($_SESSION['register_errors'] as $error) {
                    echo '<div class="alert alert-danger" role="alert">';
                    echo htmlspecialchars($error);
                    echo '</div>';
                }
                unset($_SESSION['register_errors']);
            }
            ?>
            <form class="register-form" action="/PekoPop/backend/register-process.php" method="POST">
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname"
                           value="<?php echo isset($old['fullname']) ? htmlspecialchars($old['fullname']) : ''; ?>"
                           novalidate>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo isset($old['email']) ? htmlspecialchars($old['email']) : ''; ?>"
                           novalidate>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" novalidate>
                </div>

                <div class="form-group">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" id="confirm-password" name="confirm_password" novalidate>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone"
                           value="<?php echo isset($old['phone']) ? htmlspecialchars($old['phone']) : ''; ?>"
                           novalidate>
                </div>

                <input type="submit" class="checkout-button" value="Register">

                <div class="register-links">
                    Already have an account? <a href="/PekoPop/pages/login.php">Log In</a>
                </div>
            </form>
        </div>
    </section>
    <?php include '../include/footer.php'; ?>

</body>

</html>