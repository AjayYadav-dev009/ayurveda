<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/helper.php';

if (!isCustomerLogin()) {
    redirect(BASE_URL . 'account/login.php?redirect=account/my-orders.php');
}

$user = getUserById($conn, $_SESSION['customer_id']);

if ($user === null) {
    logoutUser();
    redirect(BASE_URL . 'account/login.php');
}

// -----------------------------------------------------------------------
// PLACEHOLDER: no order functions/tables exist yet. Swap this out for
// something like getOrdersForCustomer($conn, $user['id']) once that's
// ready — the markup below already expects that same
// number/item_count/total/status shape.
// -----------------------------------------------------------------------
$orders = [
    ['number' => 'ORD1234', 'item_count' => 3, 'total' => 1299, 'status' => 'Delivered'],
    ['number' => 'ORD1233', 'item_count' => 2, 'total' => 899, 'status' => 'Shipped'],
];

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

    .status-badge--cancelled {
        background: #fbeceb;
        color: #8a1c14;
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
            <p class="order-card__number">#<?= htmlspecialchars($order['number']) ?></p>
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