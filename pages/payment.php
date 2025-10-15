<?php
session_start();
require_once '../backend/dbconnect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}


$user_id = $_SESSION['user_id'] ?? null;
$selected_items = $_SESSION['selected_items'] ?? [];
$shipping_fee = $_SESSION['shipping_fee'];
$total = $_SESSION['total'];

if (!$user_id || empty($selected_items)) {
    echo "<script>alert('No items selected for checkout.'); window.location='cart.php';</script>";
    exit;
}
// Get delivery type from previous checkout step
$delivery_type = $_SESSION['delivery_type'] ?? 'pickup';

// Fetch saved addresses if delivery
$addresses = [];
if ($delivery_type === 'delivery') {
    $address_query = "SELECT * FROM addresses WHERE user_id = ?";
    $stmt = $conn->prepare($address_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $addresses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Fetch pickup locations if pickup
$pickup_locations = [];
if ($delivery_type === 'pickup') {
    $pickup_query = "SELECT * FROM pickup_locations";
    $pickup_locations = $conn->query($pickup_query)->fetch_all(MYSQLI_ASSOC);
}

// Fetch cart items
$placeholders = implode(',', array_fill(0, count($selected_items), '?'));
$types = str_repeat('i', count($selected_items) + 1);
$query = "
    SELECT p.product_id, p.name, pi.image_url, c.quantity, p.price, c.set_type
    FROM cart_items c
    JOIN products p ON c.product_id = p.product_id
    JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_main = TRUE
    WHERE c.user_id = ? AND c.product_id IN ($placeholders)
";


$stmt = $conn->prepare($query);
$stmt->bind_param($types, $user_id, ...$selected_items);
$stmt->execute();
$cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment | PekoPop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <style>
        body {
            background: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            background: #fff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            padding: 30px;
        }

        .section-title {
            font-weight: 600;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .address-block,
        .pickup-block {
            display: flex;
            /* flex for left content + optional badge */
            align-items: flex-start;
            /* align top */
            justify-content: space-between;
            /* push badge to right */
            background: #fafafa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: 0.2s;
        }

        .address-block.add-new-address {
            display: block;
        }

        .address-block:hover,
        .pickup-block:hover {
            border-color: #999;
        }

        .address-content,
        .pickup-content {
            display: inline-block;
            vertical-align: middle;
            flex: 1;
            /* take remaining width */
        }

        .address-details,
        .pickup-details {
            font-size: 14px;
            color: #555;
            line-height: 1.4;
        }

        .address-block .badge {
            font-size: 12px;
            height: fit-content;
            padding: 4px 8px;
            border-radius: 6px;
            background-color: #6c757d;
            color: #fff;
            flex-shrink: 0;
            /* prevent shrinking */
            margin-left: 10px;
        }

        .address-block:hover,
        .pickup-block:hover {
            border-color: #999;
        }



        .address-block input,
        .pickup-block input {
            margin-right: 12px;
            margin-top: 3px;
            /* align with top of text */
        }

        .order-item {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }

        .order-item img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            margin-right: 12px;
        }

        .payment-methods label {
            display: flex;
            align-items: center;
            background: #fafafa;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 10px;
            cursor: pointer;
            margin-bottom: 8px;
        }

        .payment-methods input {
            margin-right: 10px;
        }

        .summary-box {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }

        .pay-now-btn {
            display: block;
            width: 100%;
            background: #6c63ff;
            color: white;
            font-weight: 600;
            padding: 14px;
            border-radius: 10px;
            text-align: center;
            font-size: 16px;
            border: none;
            transition: 0.3s;
        }

        .pay-now-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Checkout Payment</h2>

        <!-- SHIPPING / PICKUP SECTION -->
        <div class="section">
            <h3 class="section-title"><i class="fa-solid fa-location-dot"></i>
                <?php echo ($delivery_type === 'delivery') ? 'Shipping Information' : 'Pickup Location'; ?>
            </h3>

            <?php if ($delivery_type === 'delivery'): ?>
                <?php if (!empty($addresses)): ?>
                    <?php foreach ($addresses as $addr): ?>
                        <label class="address-block">
                            <input type="radio" name="selected_address" value="<?php echo $addr['address_id']; ?>">
                            <div class="address-content">
                                <strong><?php echo htmlspecialchars($addr['recipient_name']); ?></strong>
                                <div class="address-details">
                                    <?php echo htmlspecialchars($addr['address_line1']); ?><br>
                                    <?php echo htmlspecialchars($addr['address_line2']); ?><br>
                                    <?php echo htmlspecialchars($addr['city']); ?>,
                                    <?php echo htmlspecialchars($addr['state']); ?> -
                                    <?php echo htmlspecialchars($addr['postal_code']); ?>
                                </div>
                            </div>
                            <?php if ($addr['is_default'] == true): ?>
                                <span class="badge text-bg-secondary">Default</span>
                            <?php endif; ?>
                        </label>


                    <?php endforeach; ?>
                <?php endif; ?>

                <label class="address-block add-new-address">
                    <input type="radio" name="selected_address" value="new"> Add new address
                    <div class="address-content">
                        <div id="newAddressForm" style="display:none; margin-left: 1.8em; margin-top: 1em;">
                            <form class="row g-3">
                                <div class="col-md-4">
                                    <label for="validationServer01" class="form-label">Recipient name</label>
                                    <input type="text" class="form-control is-valid" id="validationServer01" value="Mark" required>
                                    <div class="valid-feedback">
                                        Looks good!
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="validationServer02" class="form-label">Phone</label>
                                    <input type="text" class="form-control is-valid" id="validationServer02" value="Otto" required>
                                    <div class="valid-feedback">
                                        Looks good!
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="validationServer03" class="form-label">Address Line 1</label>
                                    <input type="text" class="form-control is-invalid" id="validationServer03" aria-describedby="validationServer03Feedback" required>
                                    <div id="validationServer03Feedback" class="invalid-feedback">
                                        Please provide a valid city.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="validationServer03" class="form-label">Address Line 2</label>
                                    <input type="text" class="form-control is-invalid" id="validationServer03" aria-describedby="validationServer03Feedback" required>
                                    <div id="validationServer03Feedback" class="invalid-feedback">
                                        Please provide a valid city.
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="validationServer03" class="form-label">City</label>
                                    <input type="text" class="form-control is-invalid" id="validationServer03" aria-describedby="validationServer03Feedback" required>
                                    <div id="validationServer03Feedback" class="invalid-feedback">
                                        Please provide a valid city.
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="validationServer04" class="form-label">State</label>
                                    <select class="form-select is-invalid" id="validationServer04" aria-describedby="validationServer04Feedback" required>
                                        <option selected disabled value="">Select State...</option>
                                        <option value="Johor">Johor</option>
                                        <option value="Kedah">Kedah</option>
                                        <option value="Kelantan">Kelantan</option>
                                        <option value="Melaka">Melaka</option>
                                        <option value="Negeri Sembilan">Negeri Sembilan</option>
                                        <option value="Pahang">Pahang</option>
                                        <option value="Perlis">Perlis</option>
                                        <option value="Perak">Perak</option>
                                        <option value="Pulau Pinang">Pulau Pinang</option>
                                        <option value="Sarawak">Sarawak</option>
                                        <option value="Sabah">Sabah</option>
                                        <option value="Selangor">Selangor</option>
                                        <option value="Terengganu">Terengganu</option>
                                        <option value="WP Kuala Lumpur">WP Kuala Lumpur</option>
                                        <option value="WP Labuan">WP Labuan</option>
                                        <option value="WP Putrajaya">WP Putrajaya</option>
                                    </select>
                                    <div id="validationServer04Feedback" class="invalid-feedback">
                                        Please select a valid state.
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="validationServer05" class="form-label">Zip</label>
                                    <input type="text" class="form-control is-invalid" id="validationServer05" aria-describedby="validationServer05Feedback" required>
                                    <div id="validationServer05Feedback" class="invalid-feedback">
                                        Please provide a valid zip.
                                    </div>
                                </div>
                                <div class="form-check form-switch form-check-reverse">
                                    <input class="form-check-input" type="checkbox" id="switchCheckReverse">
                                    <label class="form-check-label" for="switchCheckReverse">Default Address</label>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary" type="submit">Save Address</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </label>

            <?php else: ?>
                <?php foreach ($pickup_locations as $loc): ?>
                    <label class="pickup-block">
                        <input type="radio" name="pickup_location" value="<?php echo $loc['pickup_id']; ?>">
                        <div class="pickup-content">
                            <strong><?php echo htmlspecialchars($loc['name']); ?></strong>
                            <div class="pickup-details">
                                <?php echo htmlspecialchars($loc['address_line1']); ?><br>
                                <?php echo htmlspecialchars($loc['address_line2']); ?><br>
                                <?php echo htmlspecialchars($loc['city']); ?>,
                                <?php echo htmlspecialchars($loc['state']); ?> -
                                <?php echo htmlspecialchars($loc['postal_code']); ?>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>

            <?php endif; ?>
        </div>

        <!-- ORDER SUMMARY -->
        <div class=" section">
            <h3 class="section-title"><i class="fa-solid fa-box"></i> Order Summary</h3>
            <?php foreach ($cartItems as $item): ?>
                <div class="order-item">
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <div>
                        <div><strong><?php echo htmlspecialchars($item['name']); ?></strong></div>
                        <div>Qty: <?php echo $item['quantity']; ?></div>
                        <div>RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- PAYMENT METHOD -->
        <div class="section">
            <h3 class="section-title"><i class="fa-solid fa-credit-card"></i> Payment Method</h3>
            <div class="payment-methods">
                <label><input type="radio" name="payment_method" value="credit"> Credit / Debit Card</label>
                <label><input type="radio" name="payment_method" value="ewallet"> E-Wallet</label>
            </div>
        </div>

        <!-- TOTAL SUMMARY -->
        <div class="summary-box"
            data-subtotal="<?php echo $subtotal; ?>"
            data-shipping="<?php echo $shipping_fee; ?>"
            data-total="<?php echo $total; ?>">

            <p>Subtotal: <span style="float:right;">RM <?php echo number_format($subtotal, 2); ?></span></p>
            <p>Shipping: <span style="float:right;">RM <?php echo number_format($shipping_fee, 2); ?></span></p>
            <hr>
            <h4>Total: <span style="float:right;">RM <?php echo number_format($total, 2); ?></span></h4>
            <button id="payNowBtn" class="pay-now-btn" disabled>Pay Now</button>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const payNowBtn = document.getElementById('payNowBtn');
            const paymentInputs = document.querySelectorAll('input[name="payment_method"]');
            const addressInputs = document.querySelectorAll('input[name="selected_address"], input[name="pickup_location"]');
            const newAddr = document.querySelector('input[value="new"]');
            const newAddrForm = document.getElementById('newAddressForm');

            function checkReady() {
                let paymentSelected = document.querySelector('input[name="payment_method"]:checked');
                let addressSelected = document.querySelector('input[name="selected_address"]:checked, input[name="pickup_location"]:checked');
                payNowBtn.disabled = !(paymentSelected && addressSelected);
            }

            paymentInputs.forEach(el => el.addEventListener('change', checkReady));
            addressInputs.forEach(el => el.addEventListener('change', () => {
                checkReady();
                if (newAddr && newAddr.checked) {
                    newAddrForm.style.display = 'block';
                } else {
                    newAddrForm.style.display = 'none';
                }
            }));
        });
    </script>

</body>

</html>