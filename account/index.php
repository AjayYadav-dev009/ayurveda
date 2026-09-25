<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/helper.php';

// Not logged in? Send them to log in first, and bring them back here after.
if (!isCustomerLogin()) {
    redirect(BASE_URL . 'account/login.php?redirect=account/index.php');
}

$user = getUserById($conn, $_SESSION['customer_id']);

// Session points at an account that no longer exists (or was deleted) —
// log the stale session out rather than showing a broken page.
if ($user === null) {
    logoutUser();
    redirect(BASE_URL . 'account/login.php');
}

$welcome = isset($_GET['welcome']);

// -----------------------------------------------------------------------
// PLACEHOLDER: no order functions exist yet. Swap this out for something
// like getRecentOrdersForCustomer($conn, $user['id'], 2) once that lands,
// and drop the "no orders" fallback below the loop.
// -----------------------------------------------------------------------
$recentOrders = [
    ['number' => 'ORD1234', 'item_count' => 3, 'total' => 1299, 'status' => 'Delivered'],
    ['number' => 'ORD1233', 'item_count' => 2, 'total' => 899, 'status' => 'Shipped'],
];

$activeNav = 'dashboard';
require __DIR__ . '/account-sidebar.php';
?>

<style>
    .dashboard-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        padding: 22px 26px;
        margin-bottom: 22px;
        border-radius: var(--acc-radius-lg);
        color: #fff;
        background:
            radial-gradient(rgba(244, 239, 226, 0.07) 1px, transparent 1.2px) 0 0 / 22px 22px,
            linear-gradient(120deg, var(--acc-side) 0%, var(--acc-side-deep) 100%);
        box-shadow: var(--acc-shadow);
    }

    .dashboard-hero__greeting {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .dashboard-hero__avatar {
        flex-shrink: 0;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--acc-gold);
        color: var(--acc-side);
        font-size: 19px;
        font-weight: 800;
    }

    .dashboard-hero__name {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
    }

    .dashboard-hero__meta {
        margin: 3px 0 0;
        font-size: 13px;
        color: rgba(244, 239, 226, 0.75);
    }

    .dashboard-hero__cta {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 999px;
        background: rgba(216, 182, 120, 0.18);
        border: 1px solid rgba(216, 182, 120, 0.5);
        color: var(--acc-gold);
        font-size: 13.5px;
        font-weight: 700;
        text-decoration: none;
        transition: background 0.15s ease;
    }

    .dashboard-hero__cta:hover {
        background: rgba(216, 182, 120, 0.28);
    }

    .dashboard-hero__cta svg {
        width: 16px;
        height: 16px;
    }

    .dashboard-welcome {
        margin: 0 0 20px;
        padding: 12px 16px;
        font-size: 13px;
        font-weight: 600;
        color: var(--acc-side);
        background: rgba(47, 158, 110, 0.1);
        border: 1px solid rgba(47, 158, 110, 0.25);
        border-radius: var(--acc-radius-sm);
        line-height: 1.5;
    }

    /* ---- Quick actions ---- */

    .quick-actions {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 12px;
        margin-bottom: 26px;
    }

    .quick-action {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        text-align: center;
        padding: 16px 8px;
        background: var(--acc-white);
        border: 1px solid var(--acc-border);
        border-radius: var(--acc-radius-md);
        color: var(--acc-text);
        text-decoration: none;
        transition: box-shadow 0.15s ease, border-color 0.15s ease, transform 0.15s ease;
    }

    .quick-action:hover {
        box-shadow: var(--acc-shadow-hover);
        border-color: var(--acc-gold);
        transform: translateY(-2px);
    }

    .quick-action__icon {
        display: grid;
        place-items: center;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(23, 72, 61, 0.08);
        color: var(--acc-side);
    }

    .quick-action__icon svg {
        width: 18px;
        height: 18px;
    }

    .quick-action__label {
        font-size: 12.5px;
        font-weight: 700;
    }

    /* ---- Sections ---- */

    .dashboard-section {
        background: var(--acc-white);
        border: 1px solid var(--acc-border);
        border-radius: var(--acc-radius-lg);
        box-shadow: var(--acc-shadow);
        padding: 24px 28px;
        margin-bottom: 22px;
    }

    .dashboard-section__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .dashboard-section__header h2 {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        color: var(--acc-text);
    }

    .dashboard-section__header a {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 13px;
        font-weight: 700;
        color: var(--acc-side);
        text-decoration: none;
    }

    .dashboard-section__header a:hover {
        color: var(--acc-gold-dark);
    }

    .order-row {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 0;
        border-bottom: 1px solid var(--acc-border);
        font-size: 14px;
    }

    .order-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .order-row__icon {
        flex-shrink: 0;
        display: grid;
        place-items: center;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: rgba(23, 72, 61, 0.08);
        color: var(--acc-side);
    }

    .order-row__icon svg {
        width: 19px;
        height: 19px;
    }

    .order-row__body {
        flex: 1;
        min-width: 0;
    }

    .order-row__number {
        font-weight: 700;
        color: var(--acc-text);
    }

    .order-row__meta {
        color: var(--acc-text-light);
        font-size: 12.5px;
        margin-top: 1px;
    }

    .order-row__right {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }

    .order-row__total {
        font-weight: 700;
        color: var(--acc-text);
        white-space: nowrap;
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

    .status-badge--cancelled {
        background: #fbeceb;
        color: #8a1c14;
    }

    .orders-empty {
        font-size: 14px;
        color: var(--acc-text-light);
    }

    .account-details {
        margin: 0;
    }

    .account-row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 16px;
        padding: 14px 0;
        border-bottom: 1px solid var(--acc-border);
    }

    .account-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .account-row dt {
        margin: 0;
        font-size: 13px;
        font-weight: 600;
        color: var(--acc-text-light);
        white-space: nowrap;
    }

    .account-row dd {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: var(--acc-text);
        text-align: right;
        word-break: break-word;
    }

    @media (max-width: 900px) {
        .quick-actions {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 560px) {
        .quick-actions {
            grid-template-columns: repeat(2, 1fr);
        }

        .dashboard-section {
            padding: 20px;
        }
    }
</style>

<h1>Dashboard</h1>

<?php if ($welcome): ?>
    <p class="dashboard-welcome">Welcome, <?= htmlspecialchars($user['name']) ?> — your account is all set up.</p>
<?php endif; ?>

<div class="dashboard-hero">
    <div class="dashboard-hero__greeting">
        <div class="dashboard-hero__avatar"><?= htmlspecialchars($sidebarInitial) ?></div>
        <div>
            <p class="dashboard-hero__name">Welcome back, <?= htmlspecialchars($user['name']) ?></p>
            <p class="dashboard-hero__meta"><?= htmlspecialchars($user['email']) ?></p>
        </div>
    </div>
    <a class="dashboard-hero__cta" href="profile.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
        Edit profile
    </a>
</div>

<div class="quick-actions">
    <?php foreach ($navItems as $key => $item): if ($key === 'dashboard') continue; ?>
        <a class="quick-action" href="<?= htmlspecialchars($item['href']) ?>">
            <span class="quick-action__icon"><?= $navIcon($key) ?></span>
            <span class="quick-action__label"><?= htmlspecialchars($item['label']) ?></span>
        </a>
    <?php endforeach; ?>
</div>

<div class="dashboard-section">
    <div class="dashboard-section__header">
        <h2>Recent Orders</h2>
        <a href="my-orders.php">View all &rarr;</a>
    </div>

    <?php if (empty($recentOrders)): ?>
        <p class="orders-empty">You haven't placed any orders yet.</p>
    <?php else: ?>
        <?php foreach ($recentOrders as $order): ?>
            <?php $statusClass = 'status-badge--' . strtolower($order['status']); ?>
            <div class="order-row">
                <span class="order-row__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                </span>
                <div class="order-row__body">
                    <div class="order-row__number">#<?= htmlspecialchars($order['number']) ?></div>
                    <div class="order-row__meta"><?= (int) $order['item_count'] ?> items</div>
                </div>
                <div class="order-row__right">
                    <span class="status-badge <?= htmlspecialchars($statusClass) ?>"><?= htmlspecialchars($order['status']) ?></span>
                    <span class="order-row__total">&#8377;<?= number_format((float) $order['total'], 2) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="dashboard-section">
    <div class="dashboard-section__header">
        <h2>Profile</h2>
        <a href="profile.php">Edit &rarr;</a>
    </div>

    <dl class="account-details">
        <div class="account-row">
            <dt>Name</dt>
            <dd><?= htmlspecialchars($user['name']) ?></dd>
        </div>
        <div class="account-row">
            <dt>Email</dt>
            <dd><?= htmlspecialchars($user['email']) ?></dd>
        </div>
        <div class="account-row">
            <dt>Phone</dt>
            <dd><?= $user['phone'] !== null ? htmlspecialchars($user['phone']) : '&mdash;' ?></dd>
        </div>
    </dl>
</div>

</main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>