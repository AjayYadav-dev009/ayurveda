<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/helper.php';
require_once __DIR__ . '/../function/order.php';

if (!isCustomerLogin()) {
    redirect(BASE_URL . 'account/login.php?redirect=account/my-orders.php');
}

$user = getUserById($conn, $_SESSION['customer_id']);

if ($user === null) {
    logoutUser();
    redirect(BASE_URL . 'account/login.php');
}

$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($orderId <= 0) {
    redirect(BASE_URL . 'account/my-orders.php');
}

try {
    // getOrderDetailsForCustomer() checks user_id itself, so this also
    // returns null (not someone else's order) if this order doesn't
    // belong to the logged-in customer.
    $order = getOrderDetailsForCustomer($conn, $orderId, $user['id']);
} catch (Exception $e) {
    $order = null;
}

if ($order === null) {
    redirect(BASE_URL . 'account/my-orders.php');
}

$activeNav = 'orders';
require __DIR__ . '/account-sidebar.php';
?>

<style>
    .order-details__back {
        display: inline-block;
        margin-bottom: 16px;
        font-size: 14px;
        font-weight: 600;
        color: var(--color-text-light);
        text-decoration: none;
    }

    .order-details__back:hover {
        color: var(--color-primary-dark);
    }

    .order-details__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
    }

    .order-details__number {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: var(--color-text);
    }

    .order-details__date {
        font-size: 13px;
        color: var(--color-text-light);
    }

    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-badge--delivered {
        background: var(--color-primary-light);
        color: var(--color-primary-dark);
    }

    .status-badge--shipped {
        background: #eaf1fb;
        color: #1c5aa8;
    }

    .status-badge--processing {
        background: #fff3cd;
        color: #856404;
    }

    .status-badge--pending {
        background: #f0f0f0;
        color: #555555;
    }

    .status-badge--confirmed {
        background: #eaf1fb;
        color: #1c5aa8;
    }

    .status-badge--cancelled {
        background: #fbeceb;
        color: #8a1c14;
    }

    .status-badge--returned {
        background: #f3e8fd;
        color: #6a1b9a;
    }

    .order-details__section {
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        padding: 16px 20px;
        margin-bottom: 16px;
    }

    .order-details__section h2 {
        margin: 0 0 12px;
        font-size: 14px;
        font-weight: 800;
        color: var(--color-primary-dark);
    }

    .order-details__item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 8px 0;
        border-bottom: 1px solid var(--color-border);
        font-size: 14px;
    }

    .order-details__item:last-child {
        border-bottom: none;
    }

    .order-details__item-name a {
        color: var(--color-text);
        font-weight: 600;
        text-decoration: none;
    }

    .order-details__item-name a:hover {
        color: var(--color-primary-dark);
        text-decoration: underline;
    }

    .order-details__item-meta {
        color: var(--color-text-light);
        font-size: 13px;
    }

    .order-details__item-price {
        font-weight: 700;
        color: var(--color-text);
        white-space: nowrap;
    }

    .order-details__row {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        color: var(--color-text-light);
        padding: 4px 0;
    }

    .order-details__row--total {
        margin-top: 6px;
        padding-top: 10px;
        border-top: 1px solid var(--color-border);
        font-size: 15px;
        font-weight: 800;
        color: var(--color-text);
    }

    .order-details__address {
        font-size: 14px;
        color: var(--color-text-light);
        line-height: 1.6;
    }

    .order-details__address strong {
        color: var(--color-text);
    }
</style>

<a class="order-details__back" href="<?= htmlspecialchars(BASE_URL . 'account/my-orders.php') ?>">&larr; Back to My Orders</a>

<?php $statusClass = 'status-badge--' . strtolower($order['status']); ?>
<div class="order-details__head">
    <div>
        <p class="order-details__number">Order #<?= htmlspecialchars($order['number']) ?></p>
        <p class="order-details__date">Placed on <?= htmlspecialchars(date('d M Y', strtotime($order['created_at']))) ?></p>
    </div>
    <span class="status-badge <?= htmlspecialchars($statusClass) ?>"><?= htmlspecialchars($order['status']) ?></span>
</div>

<div class="order-details__section">
    <h2>Items</h2>
    <?php foreach ($order['items'] as $item): ?>
        <div class="order-details__item">
            <div>
                <div class="order-details__item-name">
                    <?php if ($item['url']): ?>
                        <a href="<?= htmlspecialchars($item['url']) ?>"><?= htmlspecialchars($item['name']) ?></a>
                    <?php else: ?>
                        <?= htmlspecialchars($item['name']) ?>
                    <?php endif; ?>
                </div>
                <div class="order-details__item-meta">
                    <?php if (!empty($item['variant_name'])): ?>
                        <?= htmlspecialchars($item['variant_name']) ?> &middot;
                    <?php endif; ?>
                    Qty <?= (int) $item['quantity'] ?> &times; &#8377;<?= number_format($item['price'], 2) ?>
                </div>
            </div>
            <div class="order-details__item-price">&#8377;<?= number_format($item['subtotal'], 2) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="order-details__section">
    <h2>Order Summary</h2>
    <div class="order-details__row">
        <span>Subtotal</span>
        <span>&#8377;<?= number_format($order['subtotal'], 2) ?></span>
    </div>
    <?php if ($order['discount'] > 0): ?>
        <div class="order-details__row">
            <span>Discount</span>
            <span>&minus;&#8377;<?= number_format($order['discount'], 2) ?></span>
        </div>
    <?php endif; ?>
    <?php if ($order['tax'] > 0): ?>
        <div class="order-details__row">
            <span>Tax</span>
            <span>&#8377;<?= number_format($order['tax'], 2) ?></span>
        </div>
    <?php endif; ?>
    <div class="order-details__row">
        <span>Shipping</span>
        <span><?= $order['shipping_cost'] > 0 ? '&#8377;' . number_format($order['shipping_cost'], 2) : 'Free' ?></span>
    </div>
    <div class="order-details__row order-details__row--total">
        <span>Total</span>
        <span>&#8377;<?= number_format($order['total'], 2) ?></span>
    </div>
    <div class="order-details__row">
        <span>Payment</span>
        <span><?= htmlspecialchars($order['payment_method'] ?? '—') ?> &middot; <?= htmlspecialchars($order['payment_status']) ?></span>
    </div>
    <?php if (!empty($order['tracking_number'])): ?>
        <div class="order-details__row">
            <span>Tracking</span>
            <span>
                <?= htmlspecialchars($order['tracking_number']) ?>
                <?php if (!empty($order['shipping_provider'])): ?>
                    (<?= htmlspecialchars($order['shipping_provider']) ?>)
                <?php endif; ?>
            </span>
        </div>
    <?php endif; ?>
</div>

<div class="order-details__section">
    <h2>Shipping Address</h2>
    <p class="order-details__address">
        <strong><?= htmlspecialchars($order['shipping_name']) ?></strong><br>
        <?= htmlspecialchars($order['shipping_address']) ?><br>
        <?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_state']) ?> <?= htmlspecialchars($order['shipping_pincode']) ?><br>
        <?= htmlspecialchars($order['shipping_country']) ?><br>
        Phone: <?= htmlspecialchars($order['shipping_phone']) ?>
    </p>
</div>

</main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>