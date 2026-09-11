<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/cart.php';
require_once __DIR__ . '/../function/helper.php';

// Cart rows belong to a user_id (there's no guest-cart support in the
// schema), so you have to be logged in to have a cart at all.
if (!isCustomerLogin()) {
    redirect(BASE_URL . 'account/login.php?redirect=cart.php');
}

$userId = $_SESSION['customer_id'];

$errors = [];
$notice = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // -------------------------------------------------------------------
    // Every branch below re-derives current stock from the database via
    // cart.php's functions — the quantity typed into the form is only ever
    // treated as "what the customer is asking for", never as the truth
    // about what's available. See resolveCartLineContext() in cart.php.
    // -------------------------------------------------------------------

    if ($action === 'add') {
        // Product pages can POST here to add an item (productId/variantId
        // come from a hidden field tied to an actual product listing, not
        // freeform client state).
        $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
        $variantId = isset($_POST['variant_id']) && $_POST['variant_id'] !== '' ? (int) $_POST['variant_id'] : null;
        $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

        try {
            addToCart($conn, $userId, $productId, $variantId, $quantity);
            $notice = 'Item added to your cart.';
        } catch (InsufficientStockException $e) {
            $errors['general'] = $e->getMessage();
        } catch (InvalidArgumentException $e) {
            $errors['general'] = $e->getMessage();
        } catch (Exception $e) {
            $errors['general'] = 'Something went wrong while adding this item to your cart.';
        }
    } elseif ($action === 'update') {
        $cartId = isset($_POST['cart_id']) ? (int) $_POST['cart_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;

        try {
            updateCartQuantity($conn, $userId, $cartId, $quantity);
            $notice = 'Cart updated.';
        } catch (InsufficientStockException $e) {
            $errors['general'] = $e->getMessage();
        } catch (InvalidArgumentException $e) {
            $errors['general'] = $e->getMessage();
        } catch (Exception $e) {
            $errors['general'] = 'Something went wrong while updating your cart.';
        }
    } elseif ($action === 'remove') {
        $cartId = isset($_POST['cart_id']) ? (int) $_POST['cart_id'] : 0;

        try {
            removeFromCart($conn, $userId, $cartId);
            $notice = 'Item removed from your cart.';
        } catch (Exception $e) {
            $errors['general'] = 'Something went wrong while removing this item.';
        }
    } elseif ($action === 'clear') {
        try {
            clearCart($conn, $userId);
            $notice = 'Your cart has been emptied.';
        } catch (Exception $e) {
            $errors['general'] = 'Something went wrong while clearing your cart.';
        }
    }

    // Post/Redirect/Get: never render straight after a POST. Stash the
    // outcome as a one-time flash and 302 to a plain GET of this same page
    // instead — otherwise the browser has the POST body cached as "how this
    // page was last loaded", and hitting back or refresh re-prompts to
    // resubmit it (repeating an add/update/remove/clear). Redirecting means
    // the URL the browser actually has in history is a harmless GET.
    $_SESSION['cart_flash'] = ['notice' => $notice, 'errors' => $errors];
    redirect('index.php');
}

// Pull the one-time flash left by a redirect above, if any. Read-then-unset
// so refreshing the GET page a second time doesn't keep re-showing it.
if (isset($_SESSION['cart_flash'])) {
    $notice = $_SESSION['cart_flash']['notice'];
    $errors = $_SESSION['cart_flash']['errors'];
    unset($_SESSION['cart_flash']);
}

try {
    $totals = getCartTotals($conn, $userId);
} catch (Exception $e) {
    $totals = ['item_count' => 0, 'subtotal' => 0.0, 'has_invalid_items' => false, 'can_checkout' => false, 'items' => []];
    $errors['general'] = $errors['general'] ?? 'Something went wrong while loading your cart.';
}

$items = $totals['items'];
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<style>
    .cart-section {
        padding: 48px 40px 80px;
        background: var(--color-bg);
    }

    .cart-wrap {
        max-width: var(--container-width);
        margin: 0 auto;
    }

    .cart-title {
        font-size: 28px;
        font-weight: 700;
        color: var(--color-text);
        margin: 0 0 4px;
    }

    .cart-subtitle {
        color: var(--color-text-light);
        font-size: 14px;
        margin: 0 0 28px;
    }

    /* --- Notices --- */

    .cart-alert {
        padding: 12px 16px;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 20px;
    }

    .cart-alert--success {
        background: var(--color-primary-light);
        color: var(--color-primary-dark);
        border: 1px solid var(--color-primary);
    }

    .cart-alert--error {
        background: #fdecea;
        color: #b3261e;
        border: 1px solid #f2b8b5;
    }

    /* --- Layout --- */

    .cart-layout {
        display: grid;
        grid-template-columns: minmax(0, 7fr) minmax(0, 3fr);
        gap: 32px;
        align-items: start;
    }

    @media (max-width: 860px) {
        .cart-layout {
            grid-template-columns: 1fr;
        }
    }

    /* --- Item list --- */

    .cart-items {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .cart-item {
        display: flex;
        gap: 16px;
        align-items: flex-start;
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-soft);
        padding: 16px;
    }

    .cart-item.is-invalid {
        border-color: #f2b8b5;
        background: #fffaf9;
    }

    .cart-item-image {
        width: 88px;
        height: 88px;
        border-radius: var(--radius-md);
        overflow: hidden;
        background: var(--color-primary-light);
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cart-item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .cart-item-image svg {
        width: 34px;
        height: 34px;
        color: var(--color-accent);
    }

    .cart-item-body {
        flex: 1;
        min-width: 0;
    }

    .cart-item-name {
        font-weight: 600;
        font-size: 15px;
        color: var(--color-text);
        margin: 0 0 6px;
    }

    .cart-item-price {
        color: var(--color-text-light);
        font-size: 13.5px;
        margin: 0 0 8px;
    }

    .cart-item-price strong {
        color: var(--color-text);
        font-weight: 700;
    }

    .cart-item-issue {
        display: inline-block;
        color: #b3261e;
        background: #fdecea;
        font-weight: 600;
        font-size: 12px;
        padding: 3px 8px;
        border-radius: var(--radius-sm);
        margin-bottom: 8px;
    }

    .cart-item-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        margin-top: 4px;
    }

    /* Quantity stepper */

    .cart-qty {
        display: inline-flex;
        align-items: center;
        border: 1.5px solid var(--color-border);
        border-radius: var(--radius-md);
        overflow: hidden;
    }

    .cart-qty button {
        width: 32px;
        height: 34px;
        border: none;
        background: var(--color-white);
        color: var(--color-primary);
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
    }

    .cart-qty button:hover {
        background: var(--color-primary-light);
    }

    .cart-qty input {
        width: 40px;
        height: 34px;
        border: none;
        border-left: 1.5px solid var(--color-border);
        border-right: 1.5px solid var(--color-border);
        text-align: center;
        font-size: 14px;
        font-weight: 700;
        color: var(--color-text);
    }

    .cart-qty input::-webkit-outer-spin-button,
    .cart-qty input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .cart-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 700;
        border-radius: var(--radius-md);
        border: 1px solid transparent;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.2s ease, border-color 0.2s ease;
    }

    .cart-btn--primary {
        background: var(--color-primary);
        color: var(--color-white);
    }

    .cart-btn--primary:hover {
        background: var(--color-primary-dark);
    }

    .cart-btn--ghost {
        background: transparent;
        border-color: var(--color-border);
        color: var(--color-text-light);
    }

    .cart-btn--ghost:hover {
        border-color: var(--color-text-light);
        color: var(--color-text);
    }

    .cart-btn--danger-text {
        background: transparent;
        border: none;
        color: #b3261e;
        padding: 8px 4px;
    }

    .cart-btn--danger-text:hover {
        text-decoration: underline;
    }

    .cart-clear-row {
        display: flex;
        justify-content: flex-end;
        margin-top: 4px;
    }

    /* --- Summary card --- */

    .cart-summary {
        position: sticky;
        top: 24px;
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-soft);
        padding: 22px;
    }

    .cart-summary h2 {
        font-size: 16px;
        font-weight: 700;
        color: var(--color-text);
        margin: 0 0 16px;
    }

    .cart-summary-row {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        color: var(--color-text-light);
        margin-bottom: 10px;
    }

    .cart-summary-total {
        display: flex;
        justify-content: space-between;
        font-size: 18px;
        font-weight: 700;
        color: var(--color-text);
        padding-top: 14px;
        margin-top: 6px;
        border-top: 1px solid var(--color-border);
    }

    .cart-summary-note {
        font-size: 12.5px;
        color: #b3261e;
        margin-top: 10px;
    }

    .cart-checkout-btn {
        display: block;
        width: 100%;
        text-align: center;
        margin-top: 18px;
        padding: 13px 20px;
        font-size: 15px;
        font-weight: 700;
        border-radius: var(--radius-md);
        border: none;
        background: var(--color-primary);
        color: var(--color-white);
        text-decoration: none;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .cart-checkout-btn:hover {
        background: var(--color-primary-dark);
    }

    .cart-checkout-btn[aria-disabled="true"] {
        background: var(--color-border);
        color: var(--color-text-light);
        cursor: not-allowed;
        pointer-events: none;
    }

    .cart-continue {
        display: block;
        text-align: center;
        margin-top: 12px;
        font-size: 13px;
        color: var(--color-text-light);
        text-decoration: none;
    }

    .cart-continue:hover {
        color: var(--color-primary);
    }

    /* --- Empty state --- */

    .cart-empty {
        text-align: center;
        padding: 72px 24px;
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
    }

    .cart-empty svg {
        width: 56px;
        height: 56px;
        color: var(--color-accent);
        margin-bottom: 16px;
    }

    .cart-empty p {
        color: var(--color-text-light);
        font-size: 15px;
        margin: 0 0 20px;
    }
</style>

<section class="cart-section">
    <div class="cart-wrap">
        <h1 class="cart-title">Your Cart</h1>
        <p class="cart-subtitle">
            <?php echo (int) $totals['item_count']; ?> item<?php echo $totals['item_count'] === 1 ? '' : 's'; ?> in your cart
        </p>

        <?php if ($notice !== null): ?>
            <div class="cart-alert cart-alert--success"><?php echo htmlspecialchars($notice); ?></div>
        <?php endif; ?>

        <?php if (!empty($errors['general'])): ?>
            <div class="cart-alert cart-alert--error"><?php echo htmlspecialchars($errors['general']); ?></div>
        <?php endif; ?>

        <?php if (empty($items)): ?>
            <div class="cart-empty">
                <svg viewBox="0 0 64 64" fill="currentColor" aria-hidden="true">
                    <path d="M32 6c-6 8-10 16-10 24 0 6 4 10 10 10s10-4 10-10c0-8-4-16-10-24z" />
                    <path d="M12 24c8 0 14 6 16 14-8 2-16-2-20-8-1.5-2.4-1-4.6 4-6z" />
                    <path d="M52 24c-8 0-14 6-16 14 8 2 16-2 20-8 1.5-2.4 1-4.6-4-6z" />
                </svg>
                <p>Your cart is empty.</p>
                <a href="<?php echo BASE_URL; ?>products.php" class="cart-btn cart-btn--primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <div class="cart-items">
                    <?php foreach ($items as $item): ?>
                        <div class="cart-item<?php echo $item['is_valid'] ? '' : ' is-invalid'; ?>">
                            <div class="cart-item-image">
                                <?php if ($item['image']): ?>
                                    <img src="<?php echo htmlspecialchars(getProductImageUrl($item['image'])); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <?php else: ?>
                                    <svg viewBox="0 0 64 64" fill="currentColor" aria-hidden="true">
                                        <path d="M32 6c-6 8-10 16-10 24 0 6 4 10 10 10s10-4 10-10c0-8-4-16-10-24z" />
                                        <path d="M12 24c8 0 14 6 16 14-8 2-16-2-20-8-1.5-2.4-1-4.6 4-6z" />
                                        <path d="M52 24c-8 0-14 6-16 14 8 2 16-2 20-8 1.5-2.4 1-4.6-4-6z" />
                                    </svg>
                                <?php endif; ?>
                            </div>

                            <div class="cart-item-body">
                                <p class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></p>
                                <p class="cart-item-price">
                                    &#8377;<?php echo number_format($item['unit_price'], 2); ?> &times; <?php echo (int) $item['quantity']; ?>
                                    = <strong>&#8377;<?php echo number_format($item['line_total'], 2); ?></strong>
                                </p>

                                <?php if ($item['issue'] !== null): ?>
                                    <span class="cart-item-issue"><?php echo htmlspecialchars($item['issue']); ?></span><br>
                                <?php endif; ?>

                                <div class="cart-item-actions">
                                    <form method="POST" action="index.php" class="cart-qty-form" style="display:flex; align-items:center; gap:10px;">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="cart_id" value="<?php echo (int) $item['id']; ?>">
                                        <div class="cart-qty">
                                            <button type="button" class="cart-qty-minus" aria-label="Decrease quantity">&minus;</button>
                                            <input
                                                type="number"
                                                name="quantity"
                                                class="cart-qty-input"
                                                value="<?php echo (int) $item['quantity']; ?>"
                                                min="1"
                                                max="<?php echo max(1, (int) $item['available_stock']); ?>"
                                                inputmode="numeric">
                                            <button type="button" class="cart-qty-plus" aria-label="Increase quantity">+</button>
                                        </div>
                                        <button type="submit" class="cart-btn cart-btn--primary">Update</button>
                                    </form>

                                    <form method="POST" action="index.php">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="cart_id" value="<?php echo (int) $item['id']; ?>">
                                        <button type="submit" class="cart-btn cart-btn--danger-text">Remove</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="cart-clear-row">
                        <form method="POST" action="index.php" onsubmit="return confirm('Empty your entire cart?');">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="cart-btn cart-btn--ghost">Clear Cart</button>
                        </form>
                    </div>
                </div>

                <div class="cart-summary">
                    <h2>Order Summary</h2>
                    <div class="cart-summary-row">
                        <span><?php echo (int) $totals['item_count']; ?> item<?php echo $totals['item_count'] === 1 ? '' : 's'; ?></span>
                        <span>&#8377;<?php echo number_format($totals['subtotal'], 2); ?></span>
                    </div>
                    <div class="cart-summary-total">
                        <span>Subtotal</span>
                        <span>&#8377;<?php echo number_format($totals['subtotal'], 2); ?></span>
                    </div>

                    <?php if ($totals['has_invalid_items']): ?>
                        <p class="cart-summary-note">Resolve the issues above before checking out.</p>
                    <?php endif; ?>

                    <a
                        href="<?php echo BASE_URL; ?>checkout/checkout.php"
                        class="cart-checkout-btn"
                        <?php echo $totals['can_checkout'] ? '' : 'aria-disabled="true" onclick="return false;"'; ?>>
                        Proceed to Checkout
                    </a>
                    <a href="<?php echo BASE_URL; ?>products.php" class="cart-continue">Continue Shopping</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
    (function () {
        document.querySelectorAll('.cart-qty-form').forEach(function (form) {
            var input = form.querySelector('.cart-qty-input');
            var minusBtn = form.querySelector('.cart-qty-minus');
            var plusBtn = form.querySelector('.cart-qty-plus');

            minusBtn.addEventListener('click', function () {
                var val = parseInt(input.value, 10) || 1;
                var min = parseInt(input.min, 10) || 1;
                if (val > min) input.value = val - 1;
            });

            plusBtn.addEventListener('click', function () {
                var val = parseInt(input.value, 10) || 1;
                var max = parseInt(input.max, 10) || 1;
                if (val < max) input.value = val + 1;
            });
        });
    })();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>