<?php
session_start();
require_once '../backend/dbconnect.php';

$isLoggedIn = isset($_SESSION['user_id']);

function getCartItems($conn)
{
    if (isset($_SESSION['user_id'])) {
        // Get items from database
        $query = "SELECT 
    ci.*, 
    p.name, 
    p.price, 
    pi.image_url, 
    pd.stock, 
    pd.set_type 
FROM cart_items ci
JOIN products p ON ci.product_id = p.product_id
JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_main = TRUE
JOIN product_details pd 
    ON p.product_id = pd.product_id 
    AND ci.set_type = pd.set_type
WHERE ci.user_id = ?
";

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }

        mysqli_stmt_close($stmt);

        return $items;
    } else {
        $items = [];
        if (isset($_COOKIE['guest_cart'])) {
            $cart = json_decode($_COOKIE['guest_cart'], true);

            if ($cart) {
                foreach ($cart as $productId => $item) {
                    // Query to get product details for each cookie item
                    $query = "SELECT p.name, p.price, pi.image_url, pd.stock, pd.set_type 
                              FROM products p
                              JOIN product_images pi ON p.product_id = pi.product_id
                              JOIN product_details pd ON p.product_id = pd.product_id
                              WHERE p.product_id = ? AND pi.is_main = TRUE 
                              AND pd.set_type = ?";

                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "is", $productId, $item['set_type']);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                    mysqli_stmt_close($stmt);

                    if ($result) {
                        $items[] = [
                            'cookie_key' => $productId, // <-- Add this line here to store the key from cookie
                            'product_id' => $productId,
                            'name' => $result['name'],
                            'price' => $item['price'],
                            'quantity' => $item['quantity'],
                            'set_type' => $result['set_type'],
                            'image_url' => $result['image_url'],
                            'stock' => $result['stock']
                        ];
                    }
                }
            }
        }
        return $items;
    }
}

$cartItems = getCartItems($conn);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Shopping Bag</title>
    <link rel="stylesheet" href="/PekoPop/css/header.css">
    <link rel="stylesheet" href="/PekoPop/css/cart.css">
    <link rel="stylesheet" href="/PekoPop/css/footer.css">
    <script src="https://kit.fontawesome.com/a737b063d1.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

</head>

<body>
    <?php include '../include/header.php'; ?>
    <section class="cart-page">
        <!-- Header Controls -->
        <div class="left-cart">
            <div class="cart-header">
                <label>
                    <input type="checkbox" name="select-all" id="select-all">
                    Select All
                </label>
            </div>

            <!-- Cart Items -->

            <?php foreach ($cartItems as $item): ?>
                <div class="cart-item" data-stock="<?php echo htmlspecialchars($item['stock']); ?>">
                    <div class="cart-item-select">
                        <input type="checkbox" name="select-item" class="select-item" value="<?php echo htmlspecialchars($item['product_id']); ?>">
                    </div>

                    <div class="cart-item-image">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>"
                            alt="<?php echo htmlspecialchars($item['name']); ?>">
                    </div>

                    <div class="cart-item-details">
                        <div class="product-info">
                            <h2 class="product-name"><?php echo htmlspecialchars($item['name']); ?></h2>

                            <div class="product-options" data-set-type="<?php echo htmlspecialchars($item['set_type']); ?>">
                                <p>Set Type: <?php echo htmlspecialchars($item['set_type']); ?> </p>
                            </div>

                            <div class="product-price" data-unit-price="<?php echo $item['price']; ?>">
                                RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </div>


                            <div class="quantity-control">
                                <div class="quantity-wrapper">
                                    <button class="quantity-decrease"
                                        data-product-id="<?php echo htmlspecialchars($item['product_id']); ?>"
                                        data-set-type="<?php echo htmlspecialchars($item['set_type']); ?>"
                                        data-stock="<?php echo htmlspecialchars($item['stock']); ?>">−</button>

                                    <input type="number"
                                        class="quantity-display"
                                        value="<?php echo htmlspecialchars($item['quantity']); ?>"
                                        min="1"
                                        max="<?php echo htmlspecialchars($item['stock']); ?>"
                                        data-product-id="<?php echo htmlspecialchars($item['product_id']); ?>"
                                        data-set-type="<?php echo htmlspecialchars($item['set_type']); ?>"
                                        data-stock="<?php echo htmlspecialchars($item['stock']); ?>">

                                    <button class="quantity-increase"
                                        data-product-id="<?php echo htmlspecialchars($item['product_id']); ?>"
                                        data-set-type="<?php echo htmlspecialchars($item['set_type']); ?>"
                                        data-stock="<?php echo htmlspecialchars($item['stock']); ?>">+</button>
                                </div>


                                <?php if (isset($item['stock']) && (int)$item['stock'] <= 5): ?>
                                    <div class="stock-status">
                                        <span class="low-stock">Only <?php echo $item['stock']; ?> left!</span>

                                        <span class="badge rounded-pill text-bg-danger">Low Stock!</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="remove-item">
                                <?php if ($isLoggedIn): ?>
                                    <a href="#" class="remove-link" data-cart-item-id="<?php echo (int)$item['cart_item_id']; ?>">Remove</a>
                                <?php else: ?>
                                    <a href="#" class="remove-link" data-product-id="<?php echo htmlspecialchars($item['cookie_key']); ?>">Remove</a>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Cart Summary Section -->
        <div class="cart-summary">
            <?php
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            $shipping = 0.00; // Default matches UnboxOnline_ClaimInstore
            $total = $subtotal + $shipping;

            ?>
            <div class="summary-row">
                <div class="summary-label">Subtotal</div>
                <div class="summary-value">RM <?php echo number_format($subtotal, 2); ?></div>
            </div>
            <div class="summary-row">
                <div class="summary-label" style="position: relative;">
                    Delivery Type
                    <i class="fa-solid fa-circle-info info-icon"></i>
                    <div class="delivery-info">
                        📦 <strong>Pickup:</strong> 
                        <br>Unbox online, collect in-store.
                        
                        <br><br>
                        🚚 <strong>Delivery:</strong>
                        <br> Get it delivered, unbox at home. Shipping from RM30.
                    </div>

                </div>
                <div class="summary-value">
                    <select name="delivery_type" id="delivery_type" required>
                        <option value="pickup">Pickup</option>
                        <option value="delivery">Delivery</option>
                    </select>
                </div>
            </div>


            <div class="summary-row">
                <div class="summary-label">Shipping</div>
                <div class="summary-value">RM <?php echo number_format($shipping, 2); ?></div>
            </div>
            <div class="summary-row total">
                <div class="summary-label">Total (<?php echo array_sum(array_column($cartItems, 'quantity')); ?>)</div>
                <div class="summary-value">RM <?php echo number_format($total, 2); ?></div>
            </div>
            <input type="submit" class="checkout-button" value="CHECK OUT">
        </div>

    </section>

    <?php include '../include/footer.php'; ?>
    <script src="/PekoPop/js/cart.js"></script>
</body>

</html>