<?php include '../backend/product-detail-service.php'; ?>

<!DOCTYPE html>
<html>

<head>
    <title>Product Detail <?php ?></title>
    <link rel="stylesheet" href="/PekoPop/css/header.css">
    <link rel="stylesheet" href="/PekoPop/css/product-detail.css">
    <link rel="stylesheet" href="/PekoPop/css/footer.css">
    <script src="https://kit.fontawesome.com/a737b063d1.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

</head>

<body>
    <?php include '../include/header.php'; ?>
    <section class="product">
        <div class="product-image">
            <div class="img-options">
                <div class="product-img-option">
                    <img src="<?= htmlspecialchars($mainImage['image_url'] ?? '/default.jpg') ?>" alt="Main image">
                </div>
                <?php foreach ($imageOptionResults as $img): ?>
                    <div class="product-img-option">
                        <img src="<?= htmlspecialchars($img['image_url']) ?>" alt="Product image">
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="product-img-display">
                <img src="<?= htmlspecialchars($mainImage['image_url'] ?? '/default.jpg') ?>" alt="Main image">
            </div>
        </div>

        <div class="product-info">
            <div class="product-name">
                <h3><?= htmlspecialchars($product['name']) ?? 'Unknown Product' ?></h3>
            </div>
            <div class="product-price">
                <h4>RM <?= number_format($product['price'], 2) ?? '0.00' ?></h4>
            </div>

            <div class="product-variation">
                <h5>TYPE</h5>
                <div class="variation-options">
                    <?php foreach ($varInfo as $variation): ?>
                        <?php
                        $type = ($variation['set_type'] === 'Whole') ? 'Whole Set' : 'Single Box';
                        $imgSrc = isset($variation['image_url']) ? $variation['image_url'] : '/PekoPop/static/images/placeholder.jpg';
                        ?>
                        <div class="variation-option"
                            data-stock="<?= $variation['stock'] ?>"
                            data-value="<?= strtolower($variation['set_type']) ?>">
                            <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($type) ?>">
                            <p><?= $type ?> (Stock: <?= $variation['stock'] ?>)</p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="quantity-control">
                <div class="quantity-wrapper">
                    <button class="quantity-decrease" data-stock="10">−</button>
                    <input type="number" class="quantity-display" value="1" min="1" max="10" data-stock="10">
                    <button class="quantity-increase" data-stock="10">+</button>
                </div>

                <div class="stock-status">
                    <?= $product['stock'] <= 5 ? 'Low Stock' : '' ?>
                </div>
            </div>

            <div class="product-cta">
                <button id="addcart-btn">Add To Cart</button>
                <button id="buynow-btn">Buy Now</button>
            </div>

            <div class="product-details">
                <div class="details-section product-detail">
                    <div class="product-detail-title">
                        <h6>Product Details</h6>
                        <i class="fa-solid fa-caret-down fa-l"></i>
                    </div>
                    <div class="product-detail-desc">
                        <p><?= htmlspecialchars($product['description'] ?? 'No description available.') ?></p>
                    </div>
                </div>

                <div class="details-section shipping-detail">
                    <div class="shipping-detail-title">
                        <h6>Shipping Details</h6>
                        <i class="fa-solid fa-caret-down fa-l"></i>
                    </div>
                    <div class="shipping-detail-desc">
                        <p>Shipping available nationwide. Estimated delivery: 3–5 business days.</p>
                    </div>
                </div>
            </div>
        </div>

    </section>
    <section class="product-detail-img">
        <?php foreach ($productDetailImages as $detailImg): ?>
            <img src="<?= htmlspecialchars($detailImg['image_url']) ?>" alt="Detail image">
        <?php endforeach; ?>
    </section>

    <section class="recommendation">
        <h2>You May Also Like</h2>
        <div class="recommendation-list">
            <?php
            if ($recommendations > 0) {
                foreach ($recommendations as $row) {
                    echo '<a href="/PekoPop/pages/product-detail.php?id=' . $row['product_id'] . '">';
                    echo '<div class="product-card">';
                    echo '<img src="' . htmlspecialchars($row['image_url']) . '" alt="' . htmlspecialchars($row['name']) . '">';
                    echo '<div class="product-desc">';

                    echo '<div class="product-name">';
                    if ($row['is_new']) {
                        echo '<span class="badge rounded-pill text-bg-success">NEW</span> ';
                    }
                    echo htmlspecialchars($row['name']);
                    echo '</div>';

                    echo '<div class="product-price">';
                    echo '<p>Starting From:</p>';
                    echo '<h5>RM ' . number_format($row['price'], 2) . '</h5>';
                    echo '</div>';  // end product-price

                    echo '</div>';  // end product-desc
                    echo '</div>';  // end product-card
                    echo '</a>';
                }
            } else {
                echo "<p>No products found.</p>";
            }
            ?>
        </div>
    </section>

    <?php include '../include/footer.php'; ?>
    <script src="../js/product-detail.js"></script>


</body>

</html>