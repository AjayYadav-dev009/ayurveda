<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
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
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        padding: 16px 20px;
        margin-bottom: 14px;
        transition: box-shadow 0.15s ease, border-color 0.15s ease;
    }

    .order-card:hover {
        box-shadow: var(--shadow-soft);
        border-color: var(--color-primary);
    }

    .order-card__number {
        margin: 0 0 8px;
        font-size: 15px;
        font-weight: 800;
        color: var(--color-text);
    }

    .order-card__number-link {
        color: inherit;
        text-decoration: none;
    }

    .order-card__number-link:hover {
        color: var(--color-primary-dark);
        text-decoration: underline;
    }

    .order-card__items {
        list-style: none;
        margin: 0 0 12px;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .order-card__item {
        font-size: 14px;
        color: var(--color-text-light);
    }

    .order-card__item a {
        color: var(--color-text);
        font-weight: 600;
        text-decoration: none;
    }

    .order-card__item a:hover {
        color: var(--color-primary-dark);
        text-decoration: underline;
    }

    .order-card__item-qty {
        color: var(--color-text-light);
    }

    .order-card__meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        font-size: 14px;
        color: var(--color-text-light);
    }

    .order-card__price {
        font-weight: 700;
        color: var(--color-text);
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

    .orders-empty {
        font-size: 14px;
        color: var(--color-text-light);
    }
</style>

<h1>My Orders</h1>

<?php if (empty($orders)): ?>
    <p class="orders-empty">You haven't placed any orders yet.</p>
<?php else: ?>
    <?php foreach ($orders as $order): ?>
        <?php $statusClass = 'status-badge--' . strtolower($order['status']); ?>
        <div class="order-card">
            <p class="order-card__number">
                <a class="order-card__number-link" href="<?= htmlspecialchars(BASE_URL . 'account/order-details.php?id=' . $order['id']) ?>">#<?= htmlspecialchars($order['number']) ?></a>
            </p>

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
                <span><?= (int) $order['item_count'] ?> Items</span>
                <span class="order-card__price">&#8377;<?= number_format((float) $order['total'], 2) ?></span>
                <span class="status-badge <?= htmlspecialchars($statusClass) ?>"><?= htmlspecialchars($order['status']) ?></span>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</main>
</div>


<?php include __DIR__ . '/../includes/footer.php'; ?>