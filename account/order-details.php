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
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 16px;
        font-size: 13.5px;
        font-weight: 700;
        color: var(--acc-text-light);
        text-decoration: none;
    }

    .order-details__back svg {
        width: 15px;
        height: 15px;
    }

    .order-details__back:hover {
        color: var(--acc-side);
    }

    .order-details__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        padding: 20px 24px;
        margin-bottom: 22px;
        border-radius: var(--acc-radius-lg);
        color: #fff;
        background: linear-gradient(120deg, var(--acc-side) 0%, var(--acc-side-deep) 100%);
        box-shadow: var(--acc-shadow);
    }

    .order-details__number {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
    }

    .order-details__date {
        margin-top: 3px;
        font-size: 13px;
        color: rgba(244, 239, 226, 0.75);
    }

    .status-badge {
        display: inline-block;
        padding: 4px 13px;
        border-radius: 999px;
        font-size: 12.5px;
        font-weight: 700;
        white-space: nowrap;
        background: rgba(216, 182, 120, 0.18);
        border: 1px solid rgba(216, 182, 120, 0.5);
        color: var(--acc-gold);
    }

    .status-badge--delivered { background: rgba(122, 214, 168, 0.2); border-color: rgba(122, 214, 168, 0.5); color: #9fe8c4; }
    .status-badge--shipped { background: rgba(122, 178, 224, 0.2); border-color: rgba(122, 178, 224, 0.5); color: #a9cdf2; }
    .status-badge--processing,
    .status-badge--pending { background: rgba(232, 199, 122, 0.2); border-color: rgba(232, 199, 122, 0.5); color: #eecf8f; }
    .status-badge--confirmed { background: rgba(122, 178, 224, 0.2); border-color: rgba(122, 178, 224, 0.5); color: #a9cdf2; }
    .status-badge--cancelled { background: rgba(224, 122, 122, 0.2); border-color: rgba(224, 122, 122, 0.5); color: #f2a9a9; }
    .status-badge--returned { background: rgba(190, 122, 224, 0.2); border-color: rgba(190, 122, 224, 0.5); color: #d6a9f2; }

    .order-details__layout {
        display: flex;
        align-items: flex-start;
        gap: 22px;
    }

    .order-details__main {
        flex: 1 1 0;
        min-width: 0;
    }

    .order-details__side {
        flex: 0 0 320px;
        position: sticky;
        top: 20px;
    }

    .order-details__section {
        background: var(--acc-white);
        border: 1px solid var(--acc-border);
        border-radius: var(--acc-radius-lg);
        box-shadow: var(--acc-shadow);
        padding: 20px 22px;
        margin-bottom: 18px;
    }

    .order-details__section h2 {
        margin: 0 0 14px;
        font-size: 14px;
        font-weight: 800;
        color: var(--acc-side);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .order-details__item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid var(--acc-border);
        font-size: 14px;
    }

    .order-details__item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .order-details__item:first-child {
        padding-top: 0;
    }

    .order-details__item-name a {
        color: var(--acc-text);
        font-weight: 600;
        text-decoration: none;
    }

    .order-details__item-name a:hover {
        color: var(--acc-side);
        text-decoration: underline;
    }

    .order-details__item-meta {
        color: var(--acc-text-light);
        font-size: 13px;
        margin-top: 2px;
    }

    .order-details__item-price {
        font-weight: 700;
        color: var(--acc-text);
        white-space: nowrap;
    }

    .order-details__row {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        color: var(--acc-text-light);
        padding: 5px 0;
    }

    .order-details__row--total {
        margin-top: 8px;
        padding-top: 12px;
        border-top: 1px solid var(--acc-border);
        font-size: 16px;
        font-weight: 800;
        color: var(--acc-text);
    }

    .order-details__address {
        font-size: 14px;
        color: var(--acc-text-light);
        line-height: 1.7;
    }

    .order-details__address strong {
        display: block;
        color: var(--acc-text);
        margin-bottom: 2px;
    }

    @media (max-width: 900px) {
        .order-details__layout {
            flex-direction: column;
        }

        .order-details__side {
            flex: 1 1 auto;
            width: 100%;
            position: static;
        }
    }
</style>

<a class="order-details__back" href="<?= htmlspecialchars(BASE_URL . 'account/my-orders.php') ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
    Back to My Orders
</a>

<?php $statusClass = 'status-badge--' . strtolower($order['status']); ?>
<div class="order-details__head">
    <div>
        <p class="order-details__number">Order #<?= htmlspecialchars($order['number']) ?></p>
        <p class="order-details__date">Placed on <?= htmlspecialchars(date('d M Y', strtotime($order['created_at']))) ?></p>
    </div>
    <span class="status-badge <?= htmlspecialchars($statusClass) ?>"><?= htmlspecialchars($order['status']) ?></span>
</div>

<div class="order-details__layout">
    <div class="order-details__main">
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
            <h2>Shipping Address</h2>
            <p class="order-details__address">
                <strong><?= htmlspecialchars($order['shipping_name']) ?></strong>
                <?= htmlspecialchars($order['shipping_address']) ?><br>
                <?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_state']) ?> <?= htmlspecialchars($order['shipping_pincode']) ?><br>
                <?= htmlspecialchars($order['shipping_country']) ?><br>
                Phone: <?= htmlspecialchars($order['shipping_phone']) ?>
            </p>
        </div>
    </div>

    <div class="order-details__side">
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
    </div>
</div>

</main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>