<?php

require_once '../backend/dbconnect.php';

// Save the original category value
$current_category = isset($_GET['category']) ? $_GET['category'] : 'all';

$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'Default';

switch ($sort) {
    case 'Price_asc':
        $orderBy = 'p.price ASC';
        break;
    case 'Price_desc':
        $orderBy = 'p.price DESC';
        break;
    case 'New':
        // newer first: sort by the is_new flag (product_details)
        $orderBy = 'pd.is_new DESC';
        break;
    case 'Best':
        // best-selling: use total_sold
        $orderBy = 'pd.total_sold DESC';
        break;
    default:
        $orderBy = 'RAND()';  // default: newest product first
        break;
}

// === 3. Pagination setup ===
$resultsPerPage = 60;
$totalProducts = 0;
$categoryId = null;

// Count how many distinct products (only one variant per product) 
if ($category === 'all') {
    $countQuery = "
        SELECT COUNT(DISTINCT p.product_id) AS total
        FROM products AS p
        JOIN product_details AS pd ON p.product_id = pd.product_id
        WHERE pd.set_type = 'Single'
    ";
    $stmt = mysqli_prepare($conn, $countQuery);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $totalProducts);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
} else {
    // get the category_id
    $stmt = mysqli_prepare($conn, "SELECT category_id FROM categories WHERE name = ?");
    mysqli_stmt_bind_param($stmt, "s", $category);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $categoryId);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($categoryId) {
        $countQuery = "
            SELECT COUNT(DISTINCT p.product_id) AS total
            FROM products AS p
            JOIN product_details AS pd ON p.product_id = pd.product_id
            WHERE p.category_id = ? AND pd.set_type = 'Single'
        ";
        $stmt2 = mysqli_prepare($conn, $countQuery);
        mysqli_stmt_bind_param($stmt2, "i", $categoryId);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_bind_result($stmt2, $totalProducts);
        mysqli_stmt_fetch($stmt2);
        mysqli_stmt_close($stmt2);
    }
}

// Avoid division by zero, ensure at least 1 page
$totalPages = ($totalProducts > 0) ? ceil($totalProducts / $resultsPerPage) : 1;

// Clamp current page within bounds
if ($currentPage < 1) {
    $currentPage = 1;
} elseif ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

// Calculate offset
$offset = ($currentPage - 1) * $resultsPerPage;

// === 4. Query products with the needed joins (image, details) ===
if ($category === 'all') {
    $query = "
        SELECT p.*, pd.stock, pd.is_new, pd.total_sold, pi.image_url
        FROM products AS p
        JOIN product_details AS pd
            ON p.product_id = pd.product_id
            AND pd.set_type = 'Single'
        JOIN product_images AS pi
            ON p.product_id = pi.product_id
            AND pi.is_main = TRUE
        ORDER BY {$orderBy}
        LIMIT ?, ?
    ";
    $stmt3 = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt3, "ii", $offset, $resultsPerPage);
} else {
    $query = "
        SELECT p.*, pd.stock, pd.is_new, pd.total_sold, pi.image_url
        FROM products AS p
        JOIN product_details AS pd
            ON p.product_id = pd.product_id
            AND pd.set_type = 'Single'
        JOIN product_images AS pi
            ON p.product_id = pi.product_id
            AND pi.is_main = TRUE
        WHERE p.category_id = ?
        ORDER BY {$orderBy}
        LIMIT ?, ?
    ";
    $stmt3 = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt3, "iii", $categoryId, $offset, $resultsPerPage);
}

mysqli_stmt_execute($stmt3);
$results = mysqli_stmt_get_result($stmt3);
mysqli_stmt_close($stmt3);

// Helper function to build page URL (keeping category & sort)
function buildPageUrl($category, $page, $sort = 'default')
{
    return "/PekoPop/pages/products.php?category=" . urlencode($category)
        . "&page=" . $page
        . "&sort=" . urlencode($sort);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Product Listing <?php ?></title>
    <link rel="stylesheet" href="/PekoPop/css/header.css">
    <link rel="stylesheet" href="/PekoPop/css/product.css">
    <link rel="stylesheet" href="/PekoPop/css/footer.css">
    <script src="https://kit.fontawesome.com/a737b063d1.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body>

    <header>
        <?php 
        // Temporarily unset category to avoid conflicts with header.php
        $category = null;
        include '../include/header.php';
        // Restore the category value
        $category = $current_category;
        
        // Display the page heading
        if ($category === 'all') {
            echo '<h2>All Products</h2>';
        } else{
            echo '<h2>'. $category. ' Series</h2>';
        }
        ?>
    </header>
    <section>
        <div class="filter">
            <span class="filter-title">
                Sort By:
                <?php echo '<b>' . htmlspecialchars($sort) . '<i id="sort-icon" class="fa-solid fa-caret-down fa-2xs"></i></b>'; ?>
            </span>
            <div class="filter-window">
                <div class="filter-choice">
                    <form method="GET" action="products.php">
                        <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                        <input type="hidden" name="page" value="<?= $currentPage ?>">

                        <label><input type="radio" name="sort" value="Default" <?= $sort == 'Default' ? 'checked' : '' ?>> Default</label><br>
                        <label><input type="radio" name="sort" value="New" <?= $sort == 'New' ? 'checked' : '' ?>> New Arrival</label><br>
                        <label><input type="radio" name="sort" value="Best" <?= $sort == 'Best' ? 'checked' : '' ?>> Best Selling</label><br>
                        <label><input type="radio" name="sort" value="Price_asc" <?= $sort == 'Price_asc' ? 'checked' : '' ?>> Price Ascending</label><br>
                        <label><input type="radio" name="sort" value="Price_desc" <?= $sort == 'Price_desc' ? 'checked' : '' ?>> Price Descending</label><br>

                        <input type="submit" value="Apply Changes">
                    </form>
                </div>

            </div>
        </div>

        <div class="product-listing">
            <?php
            if ($results && mysqli_num_rows($results) > 0) {
                while ($row = mysqli_fetch_assoc($results)) {
                    echo '<a href="/PekoPop/pages/product-detail.php?id=' . $row['product_id'] . '">';
                    echo '<div class="product-card">';
                    echo '<img src="' . htmlspecialchars($row['image_url']) . '" alt="' . htmlspecialchars($row['name']) . '">';
                    echo '<div class="product-desc">';

                    if ($row['is_new']) {
                        echo '<p><span class="badge rounded-pill text-bg-success">NEW</span> ' . htmlspecialchars($row['name']) . '</p>';
                    } else {
                        echo '<p>' . htmlspecialchars($row['name']) . '</p>';
                    }

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

        <div class="pagination">
            <!-- Previous page button -->
            <?php if ($currentPage > 1): ?>
                <a href="<?= buildPageUrl($category, $currentPage - 1, $sort) ?>">&lt;</a>
            <?php else: ?>
                <span class="disabled">&lt;</span>
            <?php endif; ?>

            <!-- Page numbers (simple version showing 1 to totalPages) -->
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i == $currentPage): ?>
                    <span class="current"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= buildPageUrl($category, $i, $sort) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <!-- Next page button -->
            <?php if ($currentPage < $totalPages): ?>
                <a href="<?= buildPageUrl($category, $currentPage + 1, $sort) ?>">&gt;</a>
            <?php else: ?>
                <span class="disabled">&gt;</span>
            <?php endif; ?>
        </div>

    </section>

    <?php include '../include/footer.php'; ?>
    <script src="../js/product-listing-filter.js"></script>


</body>

</html>