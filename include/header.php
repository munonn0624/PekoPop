<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . '/../backend/dbconnect.php';

$query = "
SELECT c.category_id, c.name, pi.image_url
FROM categories c
JOIN products p ON p.category_id = c.category_id
JOIN product_images pi ON pi.product_id = p.product_id AND pi.is_main = TRUE
WHERE p.product_id = (
    SELECT p2.product_id
    FROM products p2
    WHERE p2.category_id = c.category_id
    ORDER BY p2.product_id ASC
    LIMIT 1
)
GROUP BY c.category_id;
";

$result = mysqli_query($conn, $query);

if (!$result) {
    die('Query failed: ' . mysqli_error($conn));
}

$categoryNav = [];
while ($row = mysqli_fetch_assoc($result)) {
    $categoryNav[] = $row;
}

mysqli_free_result($result);


?>

<div class="nav">
    <div class="nav-logo">
        <?php
        //if ($curr_page === 'index.php') {
        echo '<a href=""><img src="/PekoPop/static/images/logo.jpeg" alt="Logo" class="logo"></a>';
        //}
        ?>
    </div>
    <div class="nav-bar">
        <ul class="nav-bar-content">
            <li><a href="/PekoPop">Home</a></li>
            <li id="categories-nav"><i id="categories-icon" class="fa-solid fa-caret-down fa-2xs"></i> Categories</li>
            <li><a href="">About Us</a></li>
        </ul>
    </div>

    <div class="nav-login-cart">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/PekoPop/pages/profile.php" class="nav-icon">
                <i class="fa-solid fa-user"></i>
            </a>
        <?php else: ?>
            <a href="/PekoPop/pages/login.php" class="nav-icon">
                <i class="fa-solid fa-user"></i>
            </a>
        <?php endif; ?>
        <a href="/PekoPop/pages/cart.php" class="nav-icon">
            <i class="fa-solid fa-bag-shopping"></i>
        </a>
    </div>
</div>
<div class="categories">
    <?php foreach ($categoryNav as $catItem): ?>
        <a class="categories-card" href="/PekoPop/pages/products.php?category=<?= urlencode($catItem['name']) ?>&page=1&sort=Default">
            <img src="<?= htmlspecialchars($catItem['image_url']) ?>" alt="<?= htmlspecialchars($catItem['name']) ?>" style="width: 45px;">
            <h6><?= htmlspecialchars($catItem['name']) ?></h6>
        </a>
    <?php endforeach; ?>

    <a class="categories-card" href="/PekoPop/pages/products.php?category=all&page=1&sort=Default">
        <img src="" alt="All Categories" style="width: 45px;">
        <h6>All</h6>
    </a>
</div>
<script src="/PekoPop/js/nav-extend.js"></script>
<script src="/PekoPop/js/toggle-caret.js"></script>