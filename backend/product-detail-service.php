<?php
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    header('Location: /PekoPop/pages/products.php');
    exit();
}

require_once 'dbconnect.php';

// === Fetch Main Product Image ===
$mainImageQuery = "
    SELECT pi.image_url FROM product_images AS pi
    WHERE pi.product_id = ? AND pi.is_main = TRUE
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $mainImageQuery);
mysqli_stmt_bind_param($stmt, "i", $productId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$mainImage = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Image Options
$imageOptionQuery = "
    SELECT image_url
    FROM product_images
    WHERE product_id = ? AND is_main = FALSE AND is_detail = FALSE AND set_type IS NULL
    ORDER BY image_id ASC
";

$stmtImgOption = mysqli_prepare($conn, $imageOptionQuery);
mysqli_stmt_bind_param($stmtImgOption, "i", $productId);
mysqli_stmt_execute($stmtImgOption);
$imageOptionResults = mysqli_stmt_get_result($stmtImgOption);

$productImagesOptions = [];
while ($row = mysqli_fetch_assoc($imageOptionResults)) {
    $productImagesOptions[] = $row;
}
mysqli_stmt_close($stmtImgOption);

$variationInfoQuery = "
    SELECT pi.image_url, pi.set_type, pd.stock
    FROM product_images AS pi
    JOIN product_details AS pd ON pd.product_id = pi.product_id AND pd.set_type = pi.set_type
    WHERE pi.product_id = ? AND pi.set_type IS NOT NULL
    ORDER BY image_id ASC
";

$stmtVarInfo = mysqli_prepare($conn, $variationInfoQuery);
mysqli_stmt_bind_param($stmtVarInfo, "i", $productId);
mysqli_stmt_execute($stmtVarInfo);
$varInfoResults = mysqli_stmt_get_result($stmtVarInfo);

$varInfo = [];
while ($row = mysqli_fetch_assoc($varInfoResults)) {
    $varInfo[] = $row;
}

mysqli_stmt_close($stmtVarInfo);

// Product Detail Image
$productDetailImagesQuery = "
    SELECT pi.image_url
    FROM product_images AS pi
    WHERE pi.product_id = ? AND pi.is_detail = TRUE
    ORDER BY image_id ASC 
";

$stmtproductDetailImages = mysqli_prepare($conn, $productDetailImagesQuery);
mysqli_stmt_bind_param($stmtproductDetailImages, "i", $productId);
mysqli_stmt_execute($stmtproductDetailImages);
$productDetailImagesResults = mysqli_stmt_get_result($stmtproductDetailImages);

$productDetailImages = [];
while ($row = mysqli_fetch_assoc($productDetailImagesResults)) {
    $productDetailImages[] = $row;
}

mysqli_stmt_close($stmtproductDetailImages);

// Product Info
$productDetailsQuery = "
    SELECT p.name, p.price, p.description, pd.stock 
    FROM products AS p 
    JOIN product_details AS pd ON p.product_id = pd.product_id
    WHERE p.product_id = ?

";

$stmtProductInfo = mysqli_prepare($conn, $productDetailsQuery);
mysqli_stmt_bind_param($stmtProductInfo, "i", $productId);
mysqli_stmt_execute($stmtProductInfo);
$productResult = mysqli_stmt_get_result($stmtProductInfo);
$product = mysqli_fetch_assoc($productResult);
mysqli_stmt_close($stmtProductInfo);


// You May Like
$recommendationQuery = "
    SELECT p.product_id, p.name, pi.image_url, MIN(p.price) AS price, MAX(pd.is_new) AS is_new
    FROM products AS p
    JOIN product_images AS pi ON p.product_id = pi.product_id AND pi.is_main = TRUE
    JOIN product_details AS pd ON p.product_id = pd.product_id
    WHERE p.product_id != ?
    GROUP BY p.product_id, p.name, pi.image_url
    ORDER BY RAND()
    LIMIT 4
";

$stmtRec = mysqli_prepare($conn, $recommendationQuery);
mysqli_stmt_bind_param($stmtRec, "i", $productId);
mysqli_stmt_execute($stmtRec);
$resultRec = mysqli_stmt_get_result($stmtRec);

$recommendations = [];
while ($row = mysqli_fetch_assoc($resultRec)) {
    $recommendations[] = $row;
}
mysqli_stmt_close($stmtRec);

