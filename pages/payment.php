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
$savedCards = [
    [
        'card_id' => 1,
        'last4' => '1234',
        'expiry' => '09/27',
        'card_type' => 'visa'         // Visa card
    ],
    [
        'card_id' => 2,
        'last4' => '9876',
        'expiry' => '12/28',
        'card_type' => 'mastercard'   // MasterCard
    ],
    [
        'card_id' => 3,
        'last4' => '4567',
        'expiry' => '11/26',
        'card_type' => 'amex'          // American Express
    ],
    [
        'card_id' => 4,
        'last4' => '7890',
        'expiry' => '08/25',
        'card_type' => 'unionpay'      // UnionPay
    ]
];

$onlineBanking = [
    'affinbank' => 'Affin Bank',
    'alliancebank' => 'Alliance Bank',
    'ambank' => 'AmBank',
    'bankislam' => 'Bank Islam',
    'bankmuamalat' => 'Bank Muamalat',
    'bankrakyat' => 'Bank Rakyat',
    'bsn' => 'BSN',
    'cimb' => 'CIMB Bank',
    'hongleong' => 'Hong Leong Bank',
    'hsbc' => 'HSBC Bank',
    'maybank' => 'Maybank',
    'ocbc' => 'OCBC Bank',
    'public' => 'Public Bank',
    'rhb' => 'RHB Bank',
    'scb' => 'Standard Chartered Bank',
    'uob' => 'UOB Bank'
];

$ewallet = [
    'alipay' => 'AliPay 支付宝',
    'boost' => 'Boost',
    'grab' => 'GrabPay',
    'mbe' => 'Maybank eWallet',
    'shopee' => 'ShopeePay',
    'tng' => "Touch 'n Go",
    'wechat' => 'WeChatPay',
]


/*
function detectCardBrand(string $num): string {
    // Normalize: keep digits only
    $n = preg_replace('/\D+/', '', $num);

    if ($n === '') {
        return 'unknown';
    }

    // Visa: starts with 4
    if (preg_match('/^4/', $n)) {
        return 'visa';
    }

    // MasterCard: 51-55 OR 2221-2720
    if (preg_match('/^(5[1-5])/', $n) || preg_match('/^2(2[2-9][0-9]|[3-6][0-9]{2}|7([01][0-9]|20))/', $n)) {
        return 'mastercard';
    }

    // American Express: 34 or 37
    if (preg_match('/^3[47]/', $n)) {
        return 'amex';
    }

    // UnionPay: typically starts with 62
    if (preg_match('/^62/', $n)) {
        return 'unionpay';
    }

    return 'unknown';
}
*/
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment | PekoPop</title>
    <link rel="stylesheet" href="/PekoPop/css/payment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">


</head>

<body>

    <div class="payment-container">
        <h2>Checkout Payment</h2>

        <!-- SHIPPING / PICKUP SECTION -->
        <div class="section">
            <h3 class="section-title"><i class="fa-solid fa-location-dot"></i>
                <?php echo ($delivery_type === 'delivery') ? 'Shipping Information' : 'Pickup Location'; ?>
            </h3>
            <?php if ($delivery_type === 'delivery'): ?>
                <?php if (!empty($addresses)): ?>
                    <?php foreach ($addresses as $addr): ?>
                        <?php if ($addr['is_default'] == true): ?>
                            <label class="address-block">
                                <input type="radio" name="selected_address" value="<?php echo $addr['address_id']; ?>" checked>

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

                                <div class="address-actions with-badge">
                                    <span class="badge text-bg-secondary">Default</span>
                                    <i class="fa-solid fa-pen fa-lg edit-icon"></i>
                                </div>
                            </label>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>


                <!-- Overlay for managing all addresses -->
                <div id="addressOverlay" class="overlay">
                    <div class="overlay-content">
                        <h4>Manage Saved Addresses</h4>

                        <div class="address-list">
                            <?php foreach ($addresses as $addr): ?>
                                <?php if ($addr['is_default'] == true): ?>
                                    <div class="overlay-address-item">
                                    <?php else: ?>
                                        <div class="overlay-address-item no-badge">

                                        <?php endif; ?>


                                        <div>
                                            <strong><?php echo htmlspecialchars($addr['recipient_name']); ?></strong><br>
                                            <div class="address-details">
                                                <?php echo htmlspecialchars($addr['address_line1']); ?><br>
                                                <?php echo htmlspecialchars($addr['address_line2']); ?><br>
                                                <?php echo htmlspecialchars($addr['city']); ?>,
                                                <?php echo htmlspecialchars($addr['state']); ?> -
                                                <?php echo htmlspecialchars($addr['postal_code']); ?>
                                            </div>
                                        </div>
                                        <?php if ($addr['is_default'] == true): ?>
                                            <div class="overlay-address-actions with-badge">

                                                <span class="badge text-bg-secondary">Default</span>
                                                <i class="fa-solid fa-trash fa-lg delete-icon" data-id="<?php echo $addr['address_id']; ?>"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="overlay-address-actions no-badge">
                                                <i class="fa-solid fa-trash fa-lg delete-icon" data-id="<?php echo $addr['address_id']; ?>"></i>
                                                <button class="set-default-btn" data-id="<?php echo $addr['address_id']; ?>">Set as Default</button>

                                            </div>

                                        <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    </div>

                                    <div class="overlay-buttons">
                                        <button id="closeOverlay" class="btn btn-secondary">Close</button>
                                    </div>
                        </div>
                    </div>

                    <!-- Delete Confirmation Popup -->
                    <div id="deleteConfirmOverlay" class="overlay">
                        <div class="overlay-content small">
                            <p>Are you sure you want to delete this address?</p>
                            <div class="overlay-buttons">
                                <button id="confirmDelete" class="btn btn-danger">Delete</button>
                                <button id="cancelDelete" class="btn btn-secondary">Cancel</button>
                            </div>
                        </div>
                    </div>
                    <div id="setDefaultAddConfirmOverlay" class="overlay">
                        <div class="overlay-content small">
                            <p>Are you sure you want to set this address as your default? This will replace your current default address.</p>
                            <div class="overlay-buttons">
                                <button id="confirmSetDefault" class="btn btn-success">Set as Default</button>
                                <button id="cancelSetDefault" class="btn btn-outline-secondary">Cancel</button>
                            </div>
                        </div>
                    </div>

                    <label class="address-block add-new-address">
                        <input type="radio" name="selected_address" value="new"> Add new address
                        <div class="address-content">
                            <div id="newAddressForm" style="display:none; margin-left: 1.8em; margin-top: 1em;">
                                <form class="row g-3">
                                    <div class="col-md-4">
                                        <label for="validationServer01" class="form-label">Recipient name</label>
                                        <input type="text" class="form-control is-valid" id="validationServer01" value="Mark" novalidate>
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="validationServer02" class="form-label">Phone</label>
                                        <input type="text" class="form-control is-valid" id="validationServer02" value="Otto" novalidate>
                                        <div class="valid-feedback">
                                            Looks good!
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="validationServer03" class="form-label">Address Line 1</label>
                                        <input type="text" class="form-control is-invalid" id="validationServer03" aria-describedby="validationServer03Feedback" novalidate>
                                        <div id="validationServer03Feedback" class="invalid-feedback">
                                            Please provide a valid city.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="validationServer03" class="form-label">Address Line 2</label>
                                        <input type="text" class="form-control is-invalid" id="validationServer03" aria-describedby="validationServer03Feedback" novalidate>
                                        <div id="validationServer03Feedback" class="invalid-feedback">
                                            Please provide a valid city.
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="validationServer03" class="form-label">City</label>
                                        <input type="text" class="form-control is-invalid" id="validationServer03" aria-describedby="validationServer03Feedback" novalidate>
                                        <div id="validationServer03Feedback" class="invalid-feedback">
                                            Please provide a valid city.
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="validationServer04" class="form-label">State</label>
                                        <select class="form-select is-invalid" id="validationServer04" aria-describedby="validationServer04Feedback" novalidate>
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
                                        <input type="text" class="form-control is-invalid" id="validationServer05" aria-describedby="validationServer05Feedback" novalidate>
                                        <div id="validationServer05Feedback" class="invalid-feedback">
                                            Please provide a valid zip.
                                        </div>
                                    </div>
                                    <div class="form-check form-switch form-check-reverse">
                                        <input class="form-check-input" type="checkbox" id="switchCheckReverse">
                                        <label class="form-check-label" for="switchCheckReverse">Default Address</label>
                                    </div>
                                    <div class="col-12">
                                        <button class="save-address-btn" type="submit">Save Address</button>
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
                    <h3 class="section-title">
                        <i class="fa-solid fa-credit-card"></i> Payment Method
                    </h3>

                    <div class="payment-methods">
                        <label>
                            <input type="radio" name="payment_method" value="card" id="methodCard">
                            Credit / Debit Card (Visa / MasterCard)
                        </label>


                        <!-- Card Section (shows saved cards from DB) -->
                        <div id="cardSection" class="payment-option-section hidden">
                            <?php if (!empty($savedCards)): ?>
                                <?php foreach ($savedCards as $item): ?>
                                    <label><input type="radio" name="bank" value="<?php echo htmlspecialchars($item['card_type']); ?>">
                                        <div class="card-item">
                                            <img src="/PekoPop/static/images/payment_method/card/<?php echo htmlspecialchars($item['card_type']); ?>.png" alt="<?php echo htmlspecialchars($item['card_type']); ?>">
                                            <div class="card-details">
                                                <strong>
                                                    <?php echo ucfirst($item['card_type']); ?>
                                                </strong>
                                                <div>**** **** **** <?php echo htmlspecialchars($item['last4']); ?></div>
                                                <div>Expiry: <?php echo htmlspecialchars($item['expiry']); ?></div>
                                            </div>
                                            <button class="delete-card-btn" data-card-id="<?php echo $item['card_id']; ?>">Delete</button>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No saved cards found.</p>
                            <?php endif; ?>
                            <label class="add-new-card-btn">
                                <img src="../static/images/logo.jpeg" style="width: 35px;">
                                <button id="addNewCardBtn">Add New Card</button>
                            </label>
                        </div>

                        <!-- Online Banking -->
                        <label>
                            <input type="radio" name="payment_method" value="fpx" id="methodFPX">
                            Online Banking (FPX)
                        </label>
                        <div id="fpxSection" class="payment-option-section hidden">
                            <div class="bank-list">
                                <?php foreach ($onlineBanking as $key => $item): ?>
                                    <label>
                                        <input type="radio" name="bank" value="<?php echo htmlspecialchars($key); ?>">
                                        <img src="/PekoPop/static/images/payment_method/bank/<?php echo $key; ?>.svg" alt="<?php echo htmlspecialchars($item); ?>">
                                    </label>

                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- E-Wallet -->
                        <label>
                            <input type="radio" name="payment_method" value="ewallet" id="methodEwallet">
                            E-Wallet (e.g. Touch 'n Go, GrabPay)
                        </label>
                        <div id="ewalletSection" class="payment-option-section hidden">
                            <div class="ewallet-list">
                                <label><input type="radio" name="ewallet" value="TNG"> Touch 'n Go</label>
                                <label><input type="radio" name="ewallet" value="GrabPay"> GrabPay</label>
                                <label><input type="radio" name="ewallet" value="Boost"> Boost</label>
                                <label><input type="radio" name="ewallet" value="ShopeePay"> ShopeePay</label>

                                <?php foreach ($ewallet as $key => $item): ?>
                                    <label>
                                        <input type="radio" name="ewallet" value="<?php echo htmlspecialchars($key); ?>">
                                        <img src="/PekoPop/static/images/payment_method/ewallet/<?php echo $key; ?>.svg" alt="<?php echo htmlspecialchars($item); ?>">
                                        <?php echo $item; ?>
                                    </label>

                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overlay for delete or add new confirmation -->
                <div id="cardActionOverlay" class="overlay">
                    <div class="overlay-content small">
                        <p id="cardOverlayMsg">Are you sure you want to delete this card?</p>
                        <div class="overlay-buttons">
                            <button id="confirmCardAction" class="btn btn-success">Yes</button>
                            <button id="cancelCardAction" class="btn btn-outline-secondary">Cancel</button>
                        </div>
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
                    let addNewAddressSelected = document.querySelector('input[name="selected_address"][value="new"]')?.checked;

                    payNowBtn.disabled = !(paymentSelected && addressSelected) || addNewAddressSelected;
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

            /*Overlay*/
            document.addEventListener('DOMContentLoaded', function() {
                const editIcon = document.querySelector('.edit-icon');
                const overlay = document.getElementById('addressOverlay');
                const deleteOverlay = document.getElementById('deleteConfirmOverlay');
                const setDefaultOverlay = document.getElementById('setDefaultAddConfirmOverlay');
                const closeOverlayBtn = document.getElementById('closeOverlay');
                const confirmDelete = document.getElementById('confirmDelete');
                const cancelDelete = document.getElementById('cancelDelete');
                const confirmSetDefault = document.getElementById('confirmSetDefault');
                const cancelSetDefault = document.getElementById('cancelSetDefault');
                let deleteId = null;
                let setDefaultId = null;

                // 🟢 Open address management overlay
                editIcon?.addEventListener('click', () => {
                    overlay.style.display = 'flex';
                });

                // 🔴 Close address management overlay
                closeOverlayBtn.addEventListener('click', () => {
                    overlay.style.display = 'none';
                });

                // 🗑️ Open delete confirmation
                document.querySelectorAll('.delete-icon').forEach(icon => {
                    icon.addEventListener('click', (e) => {
                        e.stopPropagation();
                        deleteId = icon.dataset.id;
                        deleteOverlay.style.display = 'flex';
                    });
                });

                // ✅ Confirm delete
                confirmDelete.addEventListener('click', () => {
                    if (deleteId) {
                        fetch('delete_address.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: `address_id=${deleteId}`
                            })
                            .then(res => res.text())
                            .then(() => location.reload());
                    }
                });

                // ❌ Cancel delete
                cancelDelete.addEventListener('click', () => {
                    deleteOverlay.style.display = 'none';
                });

                // ⭐ Open "Set as Default" confirmation
                document.querySelectorAll('.set-default-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        setDefaultId = btn.dataset.id;
                        setDefaultOverlay.style.display = 'flex';
                    });
                });

                // ✅ Confirm "Set as Default"
                confirmSetDefault.addEventListener('click', () => {
                    if (setDefaultId) {
                        fetch('set_default_address.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: `address_id=${setDefaultId}`
                            })
                            .then(res => res.text())
                            .then(() => location.reload());
                    }
                });

                // ❌ Cancel "Set as Default"
                cancelSetDefault.addEventListener('click', () => {
                    setDefaultOverlay.style.display = 'none';
                });
            });

            //payment method
            document.addEventListener("DOMContentLoaded", () => {
                const sections = {
                    card: document.getElementById("cardSection"),
                    fpx: document.getElementById("fpxSection"),
                    ewallet: document.getElementById("ewalletSection")
                };

                const radios = document.querySelectorAll("input[name='payment_method']");
                radios.forEach(radio => {
                    radio.addEventListener("change", () => {
                        Object.values(sections).forEach(sec => sec.classList.add("hidden"));
                        const selected = radio.value;
                        if (sections[selected]) sections[selected].classList.remove("hidden");
                    });
                });

                // Example: delete confirmation overlay
                document.querySelectorAll(".delete-card-btn").forEach(btn => {
                    btn.addEventListener("click", () => {
                        const overlay = document.getElementById("cardActionOverlay");
                        const confirmBtn = document.getElementById("confirmCardAction");
                        const cancelBtn = document.getElementById("cancelCardAction");
                        overlay.style.display = "flex";

                        confirmBtn.onclick = () => {
                            const cardId = btn.getAttribute("data-card-id");
                            // TODO: AJAX delete card from DB
                            overlay.style.display = "none";
                        };

                        cancelBtn.onclick = () => {
                            overlay.style.display = "none";
                        };
                    });
                });

                // Example: add new card overlay
                document.getElementById("addNewCardBtn")?.addEventListener("click", () => {
                    const overlay = document.getElementById("cardActionOverlay");
                    document.getElementById("cardOverlayMsg").textContent = "Add a new card (Form coming soon)";
                    overlay.style.display = "flex";
                });
            });
        </script>

</body>

</html>