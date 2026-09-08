<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/function/checkout.php';
require_once __DIR__ . '/function/helper.php';

// Flat shipping rate used below the free-shipping threshold. There's no
// column for this in `settings` yet - move it there if you want it
// editable from the admin panel instead of hardcoded here.
define('SHIPPING_FLAT_RATE', 50.00);

// ---- Login required ----
if (!isset($_SESSION['user_id'])) {
    // NOTE: there's no customer-facing login page in the project yet -
    // this assumes one will exist at /login.php with a `redirect` param.
    redirect(BASE_URL . 'login.php?redirect=checkout.php');
}

$userId = (int) $_SESSION['user_id'];

$pageError = null;
$pageSuccess = null;

// ---- Handle actions (address selection, coupon, placing the order) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'select_address') {

        $addressId = (int) ($_POST['address_id'] ?? 0);
        $address = getAddressById($conn, $addressId, $userId);

        if ($address) {
            $_SESSION['checkout_address_id'] = $address['id'];
        } else {
            $pageError = 'Please select a valid address.';
        }
    } elseif ($action === 'add_address') {

        $required = ['full_name', 'phone', 'address_line1', 'city', 'state', 'pincode'];
        $missing = false;

        foreach ($required as $field) {
            if (trim($_POST[$field] ?? '') === '') {
                $missing = true;
            }
        }

        if ($missing) {
            $pageError = 'Please fill in all required address fields.';
        } else {
            $newAddressId = addAddress($conn, $userId, [
                'full_name' => trim($_POST['full_name']),
                'phone' => trim($_POST['phone']),
                'address_line1' => trim($_POST['address_line1']),
                'address_line2' => trim($_POST['address_line2'] ?? ''),
                'city' => trim($_POST['city']),
                'state' => trim($_POST['state']),
                'country' => trim($_POST['country'] ?? 'India'),
                'pincode' => trim($_POST['pincode']),
                'is_default' => !empty($_POST['is_default']),
            ]);

            $_SESSION['checkout_address_id'] = $newAddressId;
        }
    } elseif ($action === 'apply_coupon') {

        $code = trim($_POST['coupon_code'] ?? '');
        $cartItems = getCartItems($conn, $userId);
        $subtotal = calculateCartSubtotal($cartItems);

        if ($code === '') {
            $pageError = 'Enter a coupon code.';
        } else {
            $result = applyCouponCode($conn, $code, $userId, $subtotal);

            if ($result['valid']) {
                $_SESSION['checkout_coupon_code'] = $code;
                $pageSuccess = $result['message'];
            } else {
                unset($_SESSION['checkout_coupon_code']);
                $pageError = $result['message'];
            }
        }
    } elseif ($action === 'remove_coupon') {

        unset($_SESSION['checkout_coupon_code']);
    } elseif ($action === 'place_order') {

        // Everything below is recalculated from the database - nothing
        // from the submitted form is trusted for pricing.
        $cartItems = getCartItems($conn, $userId);
        $unavailable = array_filter($cartItems, fn($item) => !$item['is_available']);
        $outOfStock = array_filter($cartItems, fn($item) => $item['stock'] !== null && $item['quantity'] > $item['stock']);

        $addressId = (int) ($_SESSION['checkout_address_id'] ?? 0);
        $address = $addressId ? getAddressById($conn, $addressId, $userId) : null;

        $paymentMethod = $_POST['payment_method'] ?? '';
        $validPaymentMethods = ['cod', 'online'];

        if (empty($cartItems)) {
            $pageError = 'Your cart is empty.';
        } elseif (!empty($unavailable)) {
            $pageError = 'Some items in your cart are no longer available. Please remove them before checking out.';
        } elseif (!empty($outOfStock)) {
            $pageError = 'Some items in your cart no longer have enough stock. Please update your quantities.';
        } elseif (!$address) {
            $pageError = 'Please select a delivery address.';
        } elseif (!in_array($paymentMethod, $validPaymentMethods, true)) {
            $pageError = 'Please select a payment method.';
        } else {

            $subtotal = calculateCartSubtotal($cartItems);

            $coupon = null;
            $discount = 0.0;
            if (!empty($_SESSION['checkout_coupon_code'])) {
                $couponResult = applyCouponCode($conn, $_SESSION['checkout_coupon_code'], $userId, $subtotal);
                if ($couponResult['valid']) {
                    $coupon = $couponResult['coupon'];
                    $discount = $couponResult['discount'];
                } else {
                    unset($_SESSION['checkout_coupon_code']);
                }
            }

            $afterDiscount = round($subtotal - $discount, 2);
            $tax = calculateTax($conn, $afterDiscount);
            $shipping = calculateShipping($conn, $afterDiscount);
            $total = round($afterDiscount + $tax + $shipping, 2);

            try {
                $orderId = createOrder(
                    $conn,
                    $userId,
                    $address,
                    $cartItems,
                    $coupon,
                    $discount,
                    $subtotal,
                    $tax,
                    $shipping,
                    $total,
                    $paymentMethod
                );

                unset($_SESSION['checkout_coupon_code'], $_SESSION['checkout_address_id']);

                // No order-confirmation page exists yet - redirect there once you build one.
                redirect(BASE_URL . 'order-success.php?order_id=' . $orderId);
            } catch (Exception $e) {
                $pageError = 'We could not place your order: ' . $e->getMessage();
            }
        }
    }
}

// ---- Load current state fresh for rendering ----
$cartItems = getCartItems($conn, $userId);
$subtotal = calculateCartSubtotal($cartItems);
$addresses = getUserAddresses($conn, $userId);

$selectedAddressId = $_SESSION['checkout_address_id'] ?? null;
if ($selectedAddressId === null) {
    foreach ($addresses as $addr) {
        if ((int) $addr['is_default'] === 1) {
            $selectedAddressId = $addr['id'];
            break;
        }
    }
}
$selectedAddress = $selectedAddressId ? getAddressById($conn, (int) $selectedAddressId, $userId) : null;

$appliedCoupon = null;
$discount = 0.0;
if (!empty($_SESSION['checkout_coupon_code'])) {
    $couponResult = applyCouponCode($conn, $_SESSION['checkout_coupon_code'], $userId, $subtotal);
    if ($couponResult['valid']) {
        $appliedCoupon = $couponResult['coupon'];
        $discount = $couponResult['discount'];
    } else {
        // Coupon no longer valid (e.g. subtotal dropped below minimum) - drop it silently.
        unset($_SESSION['checkout_coupon_code']);
    }
}

$afterDiscount = round($subtotal - $discount, 2);
$tax = calculateTax($conn, $afterDiscount);
$shipping = calculateShipping($conn, $afterDiscount);
$total = round($afterDiscount + $tax + $shipping, 2);

$hasUnavailableItems = count(array_filter($cartItems, fn($item) => !$item['is_available'])) > 0;
$hasOutOfStockItems = count(array_filter($cartItems, fn($item) => $item['stock'] !== null && $item['quantity'] > $item['stock'])) > 0;
?>
    <style>
        body {
            font-family: sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        section {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 16px;
        }

        h2 {
            margin-top: 0;
            font-size: 1.1rem;
        }

        .cart-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .cart-line img {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 10px;
        }

        .cart-line-left {
            display: flex;
            align-items: center;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
        }

        .totals-row.grand-total {
            font-weight: 700;
            font-size: 1.1rem;
            border-top: 1px solid #ccc;
            margin-top: 6px;
            padding-top: 10px;
        }

        .address-card {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 8px;
        }

        .address-card.selected {
            border-color: #0d6efd;
            background-color: #f0f6ff;
        }

        .error-box {
            background-color: #f8d7da;
            color: #842029;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 16px;
        }

        .success-box {
            background-color: #d1e7dd;
            color: #0f5132;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 16px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 1rem;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background-color: #0d6efd;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        input[type="text"],
        input[type="tel"],
        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 8px;
            box-sizing: border-box;
        }
    </style>

    <h1>Checkout</h1>

    <?php if ($pageError): ?>
        <div class="error-box"><?= htmlspecialchars($pageError) ?></div>
    <?php endif; ?>

    <?php if ($pageSuccess): ?>
        <div class="success-box"><?= htmlspecialchars($pageSuccess) ?></div>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>

        <section>
            <p>Your cart is empty.</p>
        </section>

    <?php else: ?>

        <!-- Review products -->
        <section>
            <h2>1. Review Your Cart</h2>

            <?php foreach ($cartItems as $item): ?>
                <div class="cart-line">
                    <div class="cart-line-left">
                        <?php if ($item['image']): ?>
                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="">
                        <?php endif; ?>
                        <div>
                            <div><?= htmlspecialchars($item['name']) ?></div>
                            <div style="font-size:0.85em;color:#666;">
                                Qty: <?= (int) $item['quantity'] ?> × ₹<?= number_format($item['price'], 2) ?>
                                <?php if (!$item['is_available']): ?>
                                    <span style="color:#c00;"> — no longer available</span>
                                <?php elseif ($item['stock'] !== null && $item['quantity'] > $item['stock']): ?>
                                    <span style="color:#c00;"> — only <?= (int) $item['stock'] ?> left in stock</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div>₹<?= number_format($item['line_total'], 2) ?></div>
                </div>
            <?php endforeach; ?>
        </section>

        <!-- Select address -->
        <section>
            <h2>2. Delivery Address</h2>

            <?php if (empty($addresses)): ?>
                <p>You don't have any saved addresses yet.</p>
            <?php else: ?>
                <form method="post" action="">
                    <input type="hidden" name="action" value="select_address">

                    <?php foreach ($addresses as $addr): ?>
                        <label class="address-card <?= (int) $addr['id'] === (int) $selectedAddressId ? 'selected' : '' ?>" style="display:block;">
                            <input
                                type="radio"
                                name="address_id"
                                value="<?= (int) $addr['id'] ?>"
                                <?= (int) $addr['id'] === (int) $selectedAddressId ? 'checked' : '' ?>>
                            <strong><?= htmlspecialchars($addr['full_name']) ?></strong> — <?= htmlspecialchars($addr['phone']) ?><br>
                            <?= htmlspecialchars($addr['address_line1']) ?><?= $addr['address_line2'] ? ', ' . htmlspecialchars($addr['address_line2']) : '' ?><br>
                            <?= htmlspecialchars($addr['city']) ?>, <?= htmlspecialchars($addr['state']) ?> - <?= htmlspecialchars($addr['pincode']) ?>, <?= htmlspecialchars($addr['country']) ?>
                        </label>
                    <?php endforeach; ?>

                    <button type="submit" class="btn btn-sm">Use Selected Address</button>
                </form>
            <?php endif; ?>

            <details style="margin-top: 12px;">
                <summary>Add a new address</summary>
                <form method="post" action="" style="margin-top: 10px;">
                    <input type="hidden" name="action" value="add_address">

                    <input type="text" name="full_name" placeholder="Full name" required>
                    <input type="tel" name="phone" placeholder="Phone number" required>
                    <input type="text" name="address_line1" placeholder="Address line 1" required>
                    <input type="text" name="address_line2" placeholder="Address line 2 (optional)">
                    <input type="text" name="city" placeholder="City" required>
                    <input type="text" name="state" placeholder="State" required>
                    <input type="text" name="country" placeholder="Country" value="India" required>
                    <input type="text" name="pincode" placeholder="Pincode" required>
                    <label><input type="checkbox" name="is_default" value="1"> Set as default address</label>

                    <button type="submit" class="btn btn-sm">Save Address</button>
                </form>
            </details>
        </section>

        <!-- Coupon -->
        <section>
            <h2>3. Coupon</h2>

            <?php if ($appliedCoupon): ?>
                <p>
                    Applied: <strong><?= htmlspecialchars($appliedCoupon['code']) ?></strong>
                    (&minus;₹<?= number_format($discount, 2) ?>)
                </p>
                <form method="post" action="">
                    <input type="hidden" name="action" value="remove_coupon">
                    <button type="submit" class="btn btn-sm">Remove Coupon</button>
                </form>
            <?php else: ?>
                <form method="post" action="" style="display:flex; gap:8px;">
                    <input type="hidden" name="action" value="apply_coupon">
                    <input type="text" name="coupon_code" placeholder="Enter coupon code" style="margin-bottom:0;">
                    <button type="submit" class="btn btn-sm">Apply</button>
                </form>
            <?php endif; ?>
        </section>

        <!-- Totals -->
        <section>
            <h2>4. Order Summary</h2>

            <div class="totals-row">
                <span>Subtotal</span>
                <span>₹<?= number_format($subtotal, 2) ?></span>
            </div>

            <?php if ($discount > 0): ?>
                <div class="totals-row">
                    <span>Discount</span>
                    <span>&minus;₹<?= number_format($discount, 2) ?></span>
                </div>
            <?php endif; ?>

            <div class="totals-row">
                <span>Shipping</span>
                <span><?= $shipping > 0 ? '₹' . number_format($shipping, 2) : 'Free' ?></span>
            </div>

            <div class="totals-row">
                <span>Tax</span>
                <span>₹<?= number_format($tax, 2) ?></span>
            </div>

            <div class="totals-row grand-total">
                <span>Total</span>
                <span id="js-display-total">₹<?= number_format($total, 2) ?></span>
            </div>
            <p style="font-size:0.8em;color:#888;">
                The amount above is for display only. It's recalculated from the database when you place the order,
                so nothing here can be tampered with client-side.
            </p>
        </section>

        <!-- Payment -->
        <section>
            <h2>5. Payment</h2>

            <form method="post" action="">
                <input type="hidden" name="action" value="place_order">

                <label><input type="radio" name="payment_method" value="cod" checked> Cash on Delivery</label><br>
                <label><input type="radio" name="payment_method" value="online"> Online Payment</label>

                <?php if (!$selectedAddress): ?>
                    <p style="color:#c00; margin-top:10px;">Select a delivery address before placing your order.</p>
                <?php endif; ?>

                <?php if ($hasUnavailableItems || $hasOutOfStockItems): ?>
                    <p style="color:#c00; margin-top:10px;">Resolve the cart issues above before placing your order.</p>
                <?php endif; ?>

                <div style="margin-top: 16px;">
                    <button
                        type="submit"
                        class="btn"
                        <?= (!$selectedAddress || $hasUnavailableItems || $hasOutOfStockItems) ? 'disabled' : '' ?>>
                        Place Order — ₹<?= number_format($total, 2) ?>
                    </button>
                </div>
            </form>
        </section>

    <?php endif; ?>
