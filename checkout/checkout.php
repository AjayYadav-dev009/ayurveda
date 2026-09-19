<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/helper.php';

// checkout function file has no per-order shipping row in `settings` yet
// (see its own note above calculateShipping()) — flat rate lives here until
// that's moved into the settings table / admin panel.
if (!defined('SHIPPING_FLAT_RATE')) {
    define('SHIPPING_FLAT_RATE', 49);
}

require_once __DIR__ . '/../function/checkout.php';

if (!isCustomerLogin()) {
    redirect(BASE_URL . 'account/login.php?redirect=checkout.php');
}

$userId = $_SESSION['customer_id'];

$errors = [];
$notice = null;

/**
 * Cart items whose price/stock no longer line up with what checkout needs:
 * inactive product/variant, or (for variant items) requested qty > live stock.
 * Base-product-only lines have no stock column here (see getCartItems()'s
 * own note — stock is only tracked at the variant level in this schema), so
 * they're only screened on status.
 *
 * @param array $cartItems
 * @return array
 */
function getUnavailableCartItems(array $cartItems)
{
    $unavailable = [];
    foreach ($cartItems as $item) {
        if (!$item['is_available']) {
            $unavailable[] = $item;
        } elseif ($item['stock'] !== null && $item['quantity'] > $item['stock']) {
            $unavailable[] = $item;
        }
    }
    return $unavailable;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'select_address') {
        $addressId = isset($_POST['address_id']) ? (int) $_POST['address_id'] : 0;
        if (getAddressById($conn, $addressId, $userId)) {
            $_SESSION['checkout_address_id'] = $addressId;
        } else {
            $errors['general'] = 'That address could not be found.';
        }
        $_SESSION['checkout_flash'] = ['notice' => $notice, 'errors' => $errors];
        redirect('checkout.php');
    }

    if ($action === 'add_address') {
        $data = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address_line1' => trim($_POST['address_line1'] ?? ''),
            'address_line2' => trim($_POST['address_line2'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'state' => trim($_POST['state'] ?? ''),
            'country' => trim($_POST['country'] ?? '') ?: 'India',
            'pincode' => trim($_POST['pincode'] ?? ''),
            'is_default' => isset($_POST['is_default']) ? 1 : 0,
        ];

        $required = ['full_name', 'phone', 'address_line1', 'city', 'state', 'pincode'];
        $missing = false;
        foreach ($required as $field) {
            if ($data[$field] === '') {
                $missing = true;
                break;
            }
        }

        if ($missing) {
            $errors['general'] = 'Please fill in all required address fields.';
            $_SESSION['checkout_flash'] = ['notice' => $notice, 'errors' => $errors];
            redirect('checkout.php');
        }

        try {
            $newAddressId = addAddress($conn, $userId, $data);
            $_SESSION['checkout_address_id'] = $newAddressId;
            $notice = 'Address saved.';
        } catch (Exception $e) {
            $errors['general'] = 'Something went wrong while saving this address.';
        }

        $_SESSION['checkout_flash'] = ['notice' => $notice, 'errors' => $errors];
        redirect('checkout.php');
    }

    if ($action === 'apply_coupon') {
        $code = trim($_POST['coupon_code'] ?? '');
        $cartItems = getCartItems($conn, $userId);
        $subtotal = calculateCartSubtotal($cartItems);

        if ($code === '') {
            $errors['coupon'] = 'Enter a coupon code.';
        } else {
            $result = applyCouponCode($conn, $code, $userId, $subtotal);
            if ($result['valid']) {
                $_SESSION['checkout_coupon'] = ['code' => strtoupper($code)];
                $notice = $result['message'];
            } else {
                unset($_SESSION['checkout_coupon']);
                $errors['coupon'] = $result['message'];
            }
        }

        $_SESSION['checkout_flash'] = ['notice' => $notice, 'errors' => $errors];
        redirect('checkout.php');
    }

    if ($action === 'remove_coupon') {
        unset($_SESSION['checkout_coupon']);
        $_SESSION['checkout_flash'] = ['notice' => 'Coupon removed.', 'errors' => []];
        redirect('checkout.php');
    }

    if ($action === 'place_order') {
        $cartItems = getCartItems($conn, $userId);

        if (empty($cartItems)) {
            $_SESSION['cart_flash'] = ['notice' => null, 'errors' => ['general' => 'Your cart is empty.']];
            redirect(BASE_URL . 'cart/index.php');
        }

        $unavailable = getUnavailableCartItems($cartItems);
        if (!empty($unavailable)) {
            $_SESSION['cart_flash'] = ['notice' => null, 'errors' => ['general' => 'Some items in your cart are no longer available at the requested quantity. Please review your cart.']];
            redirect(BASE_URL . 'cart/index.php');
        }

        $addressId = isset($_POST['address_id']) ? (int) $_POST['address_id'] : (int) ($_SESSION['checkout_address_id'] ?? 0);
        $address = $addressId ? getAddressById($conn, $addressId, $userId) : null;

        if (!$address) {
            $errors['general'] = 'Please select or add a delivery address.';
            $_SESSION['checkout_flash'] = ['notice' => $notice, 'errors' => $errors];
            redirect('checkout.php');
        }

        $subtotal = calculateCartSubtotal($cartItems);

        $coupon = null;
        $discount = 0.0;
        if (!empty($_SESSION['checkout_coupon']['code'])) {
            $result = applyCouponCode($conn, $_SESSION['checkout_coupon']['code'], $userId, $subtotal);
            if ($result['valid']) {
                $coupon = $result['coupon'];
                $discount = $result['discount'];
            } else {
                // Coupon stopped being valid between applying it and placing
                // the order (used up, expired, etc.) — drop it and make the
                // customer confirm again rather than silently re-pricing.
                unset($_SESSION['checkout_coupon']);
                $_SESSION['checkout_flash'] = ['notice' => null, 'errors' => ['general' => 'Your coupon is no longer valid (' . $result['message'] . '). Please review your order and try again.']];
                redirect('checkout.php');
            }
        }

        $shipping = calculateShipping($conn, $subtotal);
        $tax = calculateTax($conn, $subtotal - $discount);
        $total = $subtotal - $discount + $tax + $shipping;
        $paymentMethod = 'COD';

        try {
            $orderId = createOrder($conn, $userId, $address, $cartItems, $coupon, $discount, $subtotal, $tax, $shipping, $total, $paymentMethod);

            unset($_SESSION['checkout_coupon']);
            unset($_SESSION['checkout_address_id']);

            redirect(BASE_URL . 'order-success.php?order_id=' . $orderId);
        } catch (Exception $e) {
            $errors['general'] = $e->getMessage() ?: 'Something went wrong while placing your order.';
            $_SESSION['checkout_flash'] = ['notice' => $notice, 'errors' => $errors];
            redirect('checkout.php');
        }
    }
}

if (isset($_SESSION['checkout_flash'])) {
    $notice = $_SESSION['checkout_flash']['notice'];
    $errors = $_SESSION['checkout_flash']['errors'];
    unset($_SESSION['checkout_flash']);
}

$cartItems = getCartItems($conn, $userId);

if (empty($cartItems)) {
    redirect(BASE_URL . 'cart/index.php');
}

$unavailableItems = getUnavailableCartItems($cartItems);
$subtotal = calculateCartSubtotal($cartItems);

$addresses = getUserAddresses($conn, $userId);
$selectedAddressId = $_SESSION['checkout_address_id'] ?? null;
if ($selectedAddressId === null) {
    foreach ($addresses as $addr) {
        if ((int) $addr['is_default'] === 1) {
            $selectedAddressId = (int) $addr['id'];
            break;
        }
    }
    if ($selectedAddressId === null && !empty($addresses)) {
        $selectedAddressId = (int) $addresses[0]['id'];
    }
}

$appliedCoupon = null;
$discount = 0.0;
if (!empty($_SESSION['checkout_coupon']['code'])) {
    $couponCheck = applyCouponCode($conn, $_SESSION['checkout_coupon']['code'], $userId, $subtotal);
    if ($couponCheck['valid']) {
        $appliedCoupon = $couponCheck['coupon'];
        $discount = $couponCheck['discount'];
    } else {
        unset($_SESSION['checkout_coupon']);
    }
}

$shipping = calculateShipping($conn, $subtotal);
$tax = calculateTax($conn, $subtotal - $discount);
$total = $subtotal - $discount + $tax + $shipping;

$canPlaceOrder = empty($unavailableItems) && $selectedAddressId !== null;
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<style>
    .checkout-section {
        padding: 48px 40px 80px;
        background: var(--color-bg);
    }

    .checkout-wrap {
        max-width: var(--container-width);
        margin: 0 auto;
    }

    .checkout-title {
        font-size: 28px;
        font-weight: 700;
        color: var(--color-text);
        margin: 0 0 28px;
    }

    .checkout-alert {
        padding: 12px 16px;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 20px;
    }

    .checkout-alert--success {
        background: var(--color-primary-light);
        color: var(--color-primary-dark);
        border: 1px solid var(--color-primary);
    }

    .checkout-alert--error {
        background: #fdecea;
        color: #b3261e;
        border: 1px solid #f2b8b5;
    }

    .checkout-layout {
        display: grid;
        grid-template-columns: minmax(0, 7fr) minmax(0, 3fr);
        gap: 32px;
        align-items: start;
    }

    @media (max-width: 860px) {
        .checkout-layout {
            grid-template-columns: 1fr;
        }
    }

    .checkout-card {
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-soft);
        padding: 24px;
        margin-bottom: 24px;
    }

    .checkout-card h2 {
        font-size: 17px;
        font-weight: 700;
        color: var(--color-text);
        margin: 0 0 16px;
    }

    /* --- Addresses --- */

    .address-option {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        padding: 14px 16px;
        margin-bottom: 10px;
        cursor: pointer;
    }

    .address-option:has(input:checked) {
        border-color: var(--color-primary);
        background: var(--color-primary-light);
    }

    .address-option input {
        margin-top: 3px;
        accent-color: var(--color-primary);
    }

    .address-name {
        font-weight: 700;
        color: var(--color-text);
        font-size: 14px;
        margin: 0 0 2px;
    }

    .address-lines {
        font-size: 13px;
        color: var(--color-text-light);
        line-height: 1.5;
    }

    .address-form {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 16px;
    }

    .address-form .full-width {
        grid-column: 1 / -1;
    }

    .address-form label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--color-text);
        margin-bottom: 4px;
    }

    .address-form input[type="text"],
    .address-form input[type="tel"] {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--color-border);
        border-radius: var(--radius-sm);
        font-size: 14px;
        color: var(--color-text);
    }

    .address-form input:focus {
        outline: none;
        border-color: var(--color-primary);
    }

    .address-form-toggle {
        background: none;
        border: none;
        color: var(--color-primary);
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        padding: 0;
        margin-top: 8px;
    }

    .address-form-toggle:hover {
        color: var(--color-primary-dark);
    }

    .checkout-default-check {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--color-text-light);
        margin-top: 4px;
    }

    /* --- Items review --- */

    .checkout-item {
        display: flex;
        gap: 14px;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid var(--color-border);
    }

    .checkout-item:last-child {
        border-bottom: none;
    }

    .checkout-item-image img,
    .checkout-item-image svg {
        width: 56px;
        height: 56px;
        object-fit: cover;
        border-radius: var(--radius-sm);
        background: var(--color-primary-light);
        color: var(--color-accent);
        padding: 6px;
    }

    .checkout-item-body {
        flex: 1;
        min-width: 0;
    }

    .checkout-item-name {
        font-size: 14px;
        font-weight: 600;
        color: var(--color-text);
        margin: 0 0 2px;
    }

    .checkout-item-meta {
        font-size: 13px;
        color: var(--color-text-light);
    }

    .checkout-item-issue {
        display: inline-block;
        margin-top: 4px;
        font-size: 12px;
        font-weight: 600;
        color: #b3261e;
    }

    .checkout-item-total {
        font-weight: 700;
        color: var(--color-text);
        font-size: 14px;
        white-space: nowrap;
    }

    /* --- Coupon --- */

    .coupon-row {
        display: flex;
        gap: 10px;
    }

    .coupon-row input[type="text"] {
        flex: 1;
        padding: 10px 12px;
        border: 1px solid var(--color-border);
        border-radius: var(--radius-sm);
        font-size: 14px;
    }

    .coupon-row input:focus {
        outline: none;
        border-color: var(--color-primary);
    }

    .coupon-applied {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--color-primary-light);
        color: var(--color-primary-dark);
        border-radius: var(--radius-sm);
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 600;
    }

    .coupon-applied button {
        background: none;
        border: none;
        color: var(--color-primary-dark);
        text-decoration: underline;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
    }

    /* --- Buttons --- */

    .checkout-btn {
        display: inline-block;
        padding: 10px 18px;
        border-radius: var(--radius-sm);
        border: 1px solid var(--color-border);
        background: var(--color-white);
        color: var(--color-text);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .checkout-btn--primary {
        background: var(--color-primary);
        border-color: var(--color-primary);
        color: var(--color-white);
    }

    .checkout-btn--primary:hover {
        background: var(--color-primary-dark);
    }

    /* --- Summary --- */

    .checkout-summary-row {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        color: var(--color-text-light);
        margin-bottom: 10px;
    }

    .checkout-summary-row--discount {
        color: var(--color-primary-dark);
    }

    .checkout-summary-total {
        display: flex;
        justify-content: space-between;
        font-size: 18px;
        font-weight: 700;
        color: var(--color-text);
        border-top: 1px solid var(--color-border);
        padding-top: 14px;
        margin-top: 4px;
        margin-bottom: 18px;
    }

    .place-order-btn {
        width: 100%;
        display: block;
        text-align: center;
        padding: 14px;
        border: none;
        border-radius: var(--radius-sm);
        font-size: 15px;
        font-weight: 700;
        background: var(--color-primary);
        color: var(--color-white);
        cursor: pointer;
    }

    .place-order-btn:hover {
        background: var(--color-primary-dark);
    }

    .place-order-btn:disabled {
        background: var(--color-border);
        color: var(--color-text-light);
        cursor: not-allowed;
    }

    .checkout-summary-note {
        font-size: 12px;
        color: #b3261e;
        margin: -8px 0 14px;
    }
</style>

<section class="checkout-section">
    <div class="checkout-wrap">
        <h1 class="checkout-title">Checkout</h1>

        <?php if ($notice !== null): ?>
            <div class="checkout-alert checkout-alert--success"><?php echo htmlspecialchars($notice); ?></div>
        <?php endif; ?>

        <?php if (!empty($errors['general'])): ?>
            <div class="checkout-alert checkout-alert--error"><?php echo htmlspecialchars($errors['general']); ?></div>
        <?php endif; ?>

        <div class="checkout-layout">
            <div class="checkout-main">

                <!-- Delivery address -->
                <div class="checkout-card">
                    <h2>Delivery Address</h2>

                    <?php if (!empty($errors['general']) && stripos($errors['general'], 'address') !== false): ?>
                        <div class="checkout-alert checkout-alert--error"><?php echo htmlspecialchars($errors['general']); ?></div>
                    <?php endif; ?>

                    <?php foreach ($addresses as $addr): ?>
                        <form method="POST" action="checkout.php">
                            <input type="hidden" name="action" value="select_address">
                            <input type="hidden" name="address_id" value="<?php echo (int) $addr['id']; ?>">
                            <label class="address-option">
                                <input
                                    type="radio"
                                    name="address_radio"
                                    onchange="this.form.submit()"
                                    <?php echo ((int) $addr['id'] === (int) $selectedAddressId) ? 'checked' : ''; ?>>
                                <span>
                                    <p class="address-name"><?php echo htmlspecialchars($addr['full_name']); ?> &middot; <?php echo htmlspecialchars($addr['phone']); ?></p>
                                    <p class="address-lines">
                                        <?php echo htmlspecialchars($addr['address_line1']); ?><?php echo !empty($addr['address_line2']) ? ', ' . htmlspecialchars($addr['address_line2']) : ''; ?><br>
                                        <?php echo htmlspecialchars($addr['city']); ?>, <?php echo htmlspecialchars($addr['state']); ?> - <?php echo htmlspecialchars($addr['pincode']); ?>, <?php echo htmlspecialchars($addr['country']); ?>
                                    </p>
                                </span>
                            </label>
                        </form>
                    <?php endforeach; ?>

                    <button type="button" class="address-form-toggle" onclick="document.getElementById('newAddressForm').style.display='grid'; this.style.display='none';">
                        + Add a new address
                    </button>

                    <form method="POST" action="checkout.php" class="address-form" id="newAddressForm" style="display:<?php echo empty($addresses) ? 'grid' : 'none'; ?>;">
                        <input type="hidden" name="action" value="add_address">

                        <div>
                            <label>Full Name</label>
                            <input type="text" name="full_name" required>
                        </div>
                        <div>
                            <label>Phone</label>
                            <input type="tel" name="phone" required>
                        </div>
                        <div class="full-width">
                            <label>Address Line 1</label>
                            <input type="text" name="address_line1" required>
                        </div>
                        <div class="full-width">
                            <label>Address Line 2 (optional)</label>
                            <input type="text" name="address_line2">
                        </div>
                        <div>
                            <label>City</label>
                            <input type="text" name="city" required>
                        </div>
                        <div>
                            <label>State</label>
                            <input type="text" name="state" required>
                        </div>
                        <div>
                            <label>Pincode</label>
                            <input type="text" name="pincode" required>
                        </div>
                        <div>
                            <label>Country</label>
                            <input type="text" name="country" value="India">
                        </div>
                        <div class="full-width">
                            <label class="checkout-default-check">
                                <input type="checkbox" name="is_default" value="1"> Set as default address
                            </label>
                        </div>
                        <div class="full-width">
                            <button type="submit" class="checkout-btn checkout-btn--primary">Save Address</button>
                        </div>
                    </form>
                </div>

                <!-- Order items -->
                <div class="checkout-card">
                    <h2>Order Items</h2>
                    <?php foreach ($cartItems as $item): ?>
                        <div class="checkout-item">
                            <div class="checkout-item-image">
                                <?php if ($item['image']): ?>
                                    <img src="<?php echo htmlspecialchars(getProductImageUrl($item['image'])); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <?php else: ?>
                                    <svg viewBox="0 0 64 64" fill="currentColor" aria-hidden="true">
                                        <path d="M32 6c-6 8-10 16-10 24 0 6 4 10 10 10s10-4 10-10c0-8-4-16-10-24z" />
                                    </svg>
                                <?php endif; ?>
                            </div>
                            <div class="checkout-item-body">
                                <p class="checkout-item-name"><?php echo htmlspecialchars($item['name']); ?></p>
                                <p class="checkout-item-meta">&#8377;<?php echo number_format($item['price'], 2); ?> &times; <?php echo (int) $item['quantity']; ?></p>
                                <?php if (!$item['is_available']): ?>
                                    <span class="checkout-item-issue">No longer available</span>
                                <?php elseif ($item['stock'] !== null && $item['quantity'] > $item['stock']): ?>
                                    <span class="checkout-item-issue">Only <?php echo (int) $item['stock']; ?> left in stock</span>
                                <?php endif; ?>
                            </div>
                            <div class="checkout-item-total">&#8377;<?php echo number_format($item['line_total'], 2); ?></div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (!empty($unavailableItems)): ?>
                        <p class="checkout-summary-note" style="margin-top:14px;">
                            Some items above need attention before you can place this order —
                            <a href="<?php echo BASE_URL; ?>cart/index.php">go back to your cart</a> to fix them.
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Coupon -->
                <div class="checkout-card">
                    <h2>Coupon</h2>

                    <?php if (!empty($errors['coupon'])): ?>
                        <div class="checkout-alert checkout-alert--error"><?php echo htmlspecialchars($errors['coupon']); ?></div>
                    <?php endif; ?>

                    <?php if ($appliedCoupon): ?>
                        <div class="coupon-applied">
                            <span><?php echo htmlspecialchars($appliedCoupon['code']); ?> applied &mdash; you saved &#8377;<?php echo number_format($discount, 2); ?></span>
                            <form method="POST" action="checkout.php" style="display:inline;">
                                <input type="hidden" name="action" value="remove_coupon">
                                <button type="submit">Remove</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="checkout.php" class="coupon-row">
                            <input type="hidden" name="action" value="apply_coupon">
                            <input type="text" name="coupon_code" placeholder="Enter coupon code" autocapitalize="characters">
                            <button type="submit" class="checkout-btn checkout-btn--primary">Apply</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Summary -->
            <div class="checkout-card">
                <h2>Order Summary</h2>
                <div class="checkout-summary-row">
                    <span>Subtotal</span>
                    <span>&#8377;<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <?php if ($discount > 0): ?>
                    <div class="checkout-summary-row checkout-summary-row--discount">
                        <span>Discount</span>
                        <span>&minus;&#8377;<?php echo number_format($discount, 2); ?></span>
                    </div>
                <?php endif; ?>
                <div class="checkout-summary-row">
                    <span>Shipping</span>
                    <span><?php echo $shipping > 0 ? '&#8377;' . number_format($shipping, 2) : 'Free'; ?></span>
                </div>
                <?php if ($tax > 0): ?>
                    <div class="checkout-summary-row">
                        <span>Tax</span>
                        <span>&#8377;<?php echo number_format($tax, 2); ?></span>
                    </div>
                <?php endif; ?>
                <div class="checkout-summary-total">
                    <span>Total</span>
                    <span>&#8377;<?php echo number_format($total, 2); ?></span>
                </div>

                <?php if (!$canPlaceOrder): ?>
                    <p class="checkout-summary-note">
                        <?php echo $selectedAddressId === null ? 'Add or select a delivery address to continue.' : 'Resolve the item issues above to continue.'; ?>
                    </p>
                <?php endif; ?>

                <form method="POST" action="checkout.php">
                    <input type="hidden" name="action" value="place_order">
                    <input type="hidden" name="address_id" value="<?php echo (int) $selectedAddressId; ?>">
                    <button type="submit" class="place-order-btn" <?php echo $canPlaceOrder ? '' : 'disabled'; ?>>
                        Place Order &middot; Cash on Delivery
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>