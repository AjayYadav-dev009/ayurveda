<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
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
    .dashboard-welcome {
        margin: 0 0 20px;
        padding: 12px 14px;
        font-size: 13px;
        color: var(--color-primary-dark);
        background: var(--color-primary-light);
        border: 1px solid var(--color-primary);
        border-radius: var(--radius-sm);
        line-height: 1.5;
    }

    .dashboard-section {
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-soft);
        padding: 24px 28px;
        margin-bottom: 24px;
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
        color: var(--color-text);
    }

    .dashboard-section__header a {
        font-size: 13px;
        font-weight: 700;
        color: var(--color-primary);
    }

    .dashboard-section__header a:hover {
        color: var(--color-primary-dark);
        text-decoration: underline;
    }

    .order-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid var(--color-border);
        font-size: 14px;
    }

    .order-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .order-row__number {
        font-weight: 700;
        color: var(--color-text);
    }

    .order-row__meta {
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

    .status-badge--cancelled {
        background: #fbeceb;
        color: #8a1c14;
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
        border-bottom: 1px solid var(--color-border);
    }

    .account-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .account-row dt {
        margin: 0;
        font-size: 13px;
        font-weight: 600;
        color: var(--color-text-light);
        white-space: nowrap;
    }

    .account-row dd {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: var(--color-text);
        text-align: right;
        word-break: break-word;
    }
</style>

<h1>Dashboard</h1>

<?php if ($welcome): ?>
    <p class="dashboard-welcome">Welcome, <?= htmlspecialchars($user['name']) ?></p>
<?php endif; ?>

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