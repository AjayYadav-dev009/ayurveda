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

try {
    $orders = getOrdersForCustomer($conn, $user['id']);
} catch (Exception $e) {
    $orders = [];
}

$activeNav = 'orders';
require __DIR__ . '/account-sidebar.php';
?>

<style>
    .order-card {
        background: var(--acc-white);
        border: 1px solid var(--acc-border);
        border-radius: var(--acc-radius-lg);
        box-shadow: var(--acc-shadow);
        padding: 18px 22px;
        margin-bottom: 16px;
        transition: box-shadow 0.15s ease, border-color 0.15s ease;
    }

    .order-card:hover {
        box-shadow: var(--acc-shadow-hover);
        border-color: var(--acc-gold);
    }

    .order-card__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-bottom: 12px;
        margin-bottom: 12px;
        border-bottom: 1px solid var(--acc-border);
    }

    .order-card__head-left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .order-card__icon {
        flex-shrink: 0;
        display: grid;
        place-items: center;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(23, 72, 61, 0.08);
        color: var(--acc-side);
    }

    .order-card__icon svg {
        width: 18px;
        height: 18px;
    }

    .order-card__number {
        margin: 0;
        font-size: 15px;
        font-weight: 800;
        color: var(--acc-text);
    }

    .order-card__number-link {
        color: inherit;
        text-decoration: none;
    }

    .order-card__number-link:hover {
        color: var(--acc-side);
        text-decoration: underline;
    }

    .order-card__items {
        list-style: none;
        margin: 0 0 14px;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .order-card__item {
        font-size: 14px;
        color: var(--acc-text-light);
    }

    .order-card__item a {
        color: var(--acc-text);
        font-weight: 600;
        text-decoration: none;
    }

    .order-card__item a:hover {
        color: var(--acc-side);
        text-decoration: underline;
    }

    .order-card__item-qty {
        color: var(--acc-text-light);
    }

    .order-card__meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        font-size: 14px;
        color: var(--acc-text-light);
        padding-top: 12px;
        border-top: 1px solid var(--acc-border);
    }

    .order-card__price {
        font-weight: 700;
        color: var(--acc-text);
    }

    .order-card__view {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 13px;
        font-weight: 700;
        color: var(--acc-side);
        text-decoration: none;
    }

    .order-card__view:hover {
        color: var(--acc-gold-dark);
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
        background: rgba(47, 158, 110, 0.14);
        color: #1c6a48;
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

    .orders-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 12px;
        padding: 48px 20px;
        background: var(--acc-white);
        border: 1px dashed var(--acc-border);
        border-radius: var(--acc-radius-lg);
    }

    .orders-empty-state__icon {
        display: grid;
        place-items: center;
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: rgba(23, 72, 61, 0.08);
        color: var(--acc-side);
    }

    .orders-empty-state__icon svg {
        width: 24px;
        height: 24px;
    }

    .orders-empty-state p {
        margin: 0;
        font-size: 14px;
        color: var(--acc-text-light);
    }

    .orders-empty-state a {
        margin-top: 4px;
        display: inline-block;
        padding: 9px 20px;
        border-radius: 999px;
        background: var(--acc-side);
        color: #fff;
        font-size: 13.5px;
        font-weight: 700;
        text-decoration: none;
    }

    .orders-empty-state a:hover {
        background: var(--acc-side-deep);
    }
</style>

<h1>My Orders</h1>

<?php if (empty($orders)): ?>
    <div class="orders-empty-state">
        <span class="orders-empty-state__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        </span>
        <p>You haven't placed any orders yet.</p>
        <a href="<?= htmlspecialchars(BASE_URL) ?>">Start shopping</a>
    </div>
<?php else: ?>
    <?php foreach ($orders as $order): ?>
        <?php $statusClass = 'status-badge--' . strtolower($order['status']); ?>
        <div class="order-card">
            <div class="order-card__head">
                <div class="order-card__head-left">
                    <span class="order-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    </span>
                    <p class="order-card__number">
                        <a class="order-card__number-link" href="<?= htmlspecialchars(BASE_URL . 'account/order-details.php?id=' . $order['id']) ?>">#<?= htmlspecialchars($order['number']) ?></a>
                    </p>
                </div>
                <span class="status-badge <?= htmlspecialchars($statusClass) ?>"><?= htmlspecialchars($order['status']) ?></span>
            </div>

            <?php if (!empty($order['items'])): ?>
                <ul class="order-card__items">
                    <?php foreach ($order['items'] as $item): ?>
                        <li class="order-card__item">
                            <?php if ($item['url']): ?>
                                <a href="<?= htmlspecialchars($item['url']) ?>"><?= htmlspecialchars($item['name']) ?></a>
                            <?php else: ?>
                                <?= htmlspecialchars($item['name']) ?>
                            <?php endif; ?>
                            <?php if (!empty($item['variant_name'])): ?>
                                <span class="order-card__item-qty"> (<?= htmlspecialchars($item['variant_name']) ?>)</span>
                            <?php endif; ?>
                            <span class="order-card__item-qty"> &times; <?= (int) $item['quantity'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="order-card__meta">
                <span><?= (int) $order['item_count'] ?> Items &middot; <span class="order-card__price">&#8377;<?= number_format((float) $order['total'], 2) ?></span></span>
                <a class="order-card__view" href="<?= htmlspecialchars(BASE_URL . 'account/order-details.php?id=' . $order['id']) ?>">View details &rarr;</a>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</main>
</div>


<?php include __DIR__ . '/../includes/footer.php'; ?>