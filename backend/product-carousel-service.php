<?php
// You May Like - Homepage carousel
$carouselQuery = "
    SELECT p.product_id, p.name, pi.image_url, MIN(p.price) AS price, MAX(pd.is_new) AS is_new
    FROM products AS p
    JOIN product_images AS pi ON p.product_id = pi.product_id AND pi.is_main = TRUE
    JOIN product_details AS pd ON p.product_id = pd.product_id
    GROUP BY p.product_id, p.name, pi.image_url
    ORDER BY RAND()
    LIMIT 15
";

$stmtRec = mysqli_prepare($conn, $carouselQuery);
mysqli_stmt_execute($stmtRec);
$resultRec = mysqli_stmt_get_result($stmtRec);

$recommendations = [];
while ($row = mysqli_fetch_assoc($resultRec)) {
    $recommendations[] = $row;
}
mysqli_stmt_close($stmtRec);
