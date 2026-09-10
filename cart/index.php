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
}

try {
    $totals = getCartTotals($conn, $userId);
} catch (Exception $e) {
    $totals = ['item_count' => 0, 'subtotal' => 0.0, 'has_invalid_items' => false, 'can_checkout' => false, 'items' => []];
    $errors['general'] = $errors['general'] ?? 'Something went wrong while loading your cart.';
}

$items = $totals['items'];
?>

<style>
    .cart-wrap {
        max-width: 720px;
        margin: 0 auto;
        font-family: sans-serif;
    }

    .cart-wrap h1 {
        margin-bottom: 6px;
    }

    .form-success {
        background-color: #d4edda;
        color: #155724;
        padding: 10px 15px;
        border-radius: 4px;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .form-error {
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px 15px;
        border-radius: 4px;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .cart-item {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        border: 1px solid #e2e2e2;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 12px;
    }

    .cart-item.invalid {
        border-color: #dc3545;
        background-color: #fff5f5;
    }

    .cart-item img {
        width: 72px;
        height: 72px;
        object-fit: cover;
        border-radius: 4px;
        background: #f1f1f1;
        flex-shrink: 0;
    }

    .cart-item-body {
        flex: 1;
    }

    .cart-item-name {
        font-weight: 600;
        margin: 0 0 4px 0;
    }

    .cart-item-price {
        color: #555;
        margin: 0 0 6px 0;
    }

    .cart-item-issue {
        color: #dc3545;
        font-weight: 600;
        font-size: 0.85rem;
        margin: 4px 0;
    }

    .cart-item-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
    }

    .cart-item-actions input[type="number"] {
        width: 60px;
        padding: 6px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .btn {
        display: inline-block;
        padding: 7px 14px;
        font-size: 0.85rem;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-update { background-color: #007bff; }
    .btn-remove { background-color: #dc3545; }
    .btn-clear { background-color: #6c757d; }
    .btn-checkout {
        background-color: #28a745;
        padding: 12px 24px;
        font-size: 1rem;
    }
    .btn-checkout:disabled,
    .btn-checkout[aria-disabled="true"] {
        background-color: #a5d6b1;
        cursor: not-allowed;
        pointer-events: none;
    }

    .cart-summary {
        border-top: 2px solid #333;
        margin-top: 20px;
        padding-top: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .cart-summary-total {
        font-size: 1.2rem;
        font-weight: 700;
    }

    .empty-state {
        padding: 40px;
        text-align: center;
        color: #777;
    }
</style>

<div class="cart-wrap">
    <h1>Your Cart</h1>

    <?php if ($notice !== null): ?>
        <p class="form-success"><?= htmlspecialchars($notice) ?></p>
    <?php endif; ?>

    <?php if (!empty($errors['general'])): ?>
        <p class="form-error"><?= htmlspecialchars($errors['general']) ?></p>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <p class="empty-state">Your cart is empty.</p>
    <?php else: ?>
        <?php foreach ($items as $item): ?>
            <div class="cart-item <?= $item['is_valid'] ? '' : 'invalid' ?>">
                <?php if ($item['image']): ?>
                    <img src="<?= htmlspecialchars(getProductImageUrl($item['image'])) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                <?php else: ?>
                    <img src="" alt="" style="visibility:hidden;">
                <?php endif; ?>

                <div class="cart-item-body">
                    <p class="cart-item-name"><?= htmlspecialchars($item['name']) ?></p>
                    <p class="cart-item-price">
                        ₹<?= number_format($item['unit_price'], 2) ?> &times; <?= (int) $item['quantity'] ?>
                        = <strong>₹<?= number_format($item['line_total'], 2) ?></strong>
                    </p>

                    <?php if ($item['issue'] !== null): ?>
                        <p class="cart-item-issue"><?= htmlspecialchars($item['issue']) ?></p>
                    <?php endif; ?>

                    <div class="cart-item-actions">
                        <form method="POST" action="cart.php" style="display:flex; align-items:center; gap:8px;">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="cart_id" value="<?= (int) $item['id'] ?>">
                            <input
                                type="number"
                                name="quantity"
                                value="<?= (int) $item['quantity'] ?>"
                                min="1"
                                max="<?= max(1, (int) $item['available_stock']) ?>">
                            <button type="submit" class="btn btn-update">Update</button>
                        </form>

                        <form method="POST" action="cart.php">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="cart_id" value="<?= (int) $item['id'] ?>">
                            <button type="submit" class="btn btn-remove">Remove</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <form method="POST" action="cart.php" onsubmit="return confirm('Empty your entire cart?');" style="margin-top:8px;">
            <input type="hidden" name="action" value="clear">
            <button type="submit" class="btn btn-clear">Clear Cart</button>
        </form>

        <div class="cart-summary">
            <div>
                <?= (int) $totals['item_count'] ?> item<?= $totals['item_count'] === 1 ? '' : 's' ?>
                <?php if ($totals['has_invalid_items']): ?>
                    <div class="cart-item-issue">Resolve the issues above before checking out.</div>
                <?php endif; ?>
            </div>
            <div class="cart-summary-total">Subtotal: ₹<?= number_format($totals['subtotal'], 2) ?></div>
        </div>

        <p style="text-align:right; margin-top:16px;">
            <a
                href="checkout.php"
                class="btn btn-checkout"
                <?= $totals['can_checkout'] ? '' : 'aria-disabled="true" onclick="return false;"' ?>>
                Proceed to Checkout
            </a>
        </p>
    <?php endif; ?>
</div>