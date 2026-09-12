<?php

/**
 * Shared shell for the customer "My Account" area: a left sidebar (avatar,
 * name, nav links, logout) plus an opening <main class="account-content">
 * that the including page fills in and then closes itself.
 *
 * Usage in a page:
 *
 *   $user = getUserById($conn, $_SESSION['customer_id']);
 *   $activeNav = 'orders'; // one of: dashboard, orders, wishlist, addresses, profile
 *   require __DIR__ . '/../includes/account-sidebar.php';
 *   ?>
 *   <h1>Page content here</h1>
 *   ...
 *   </main>
 *   </div><!-- /.account-shell -->
 *
 * Expects $conn/$user/session/auth checks to already be handled by the
 * including page (same as every other account page in this project).
 */

if (!isset($activeNav)) {
    $activeNav = '';
}

// wishlist.php / addresses.php / profile.php don't exist yet — the links
// below point at them anyway so the nav is complete; they'll 404 until
// those pages are built once their backend functions are ready.
$navItems = [
    'dashboard' => ['label' => 'Dashboard', 'href' => 'index.php'],
    'orders'    => ['label' => 'My Orders', 'href' => 'my-orders.php'],
    'wishlist'  => ['label' => 'Wishlist', 'href' => 'wishlist.php'],
    'addresses' => ['label' => 'Addresses', 'href' => 'addresses.php'],
    'profile'   => ['label' => 'Profile', 'href' => 'profile.php'],
];

$sidebarName = isset($user['name']) ? $user['name'] : '';
$sidebarInitial = $sidebarName !== '' ? mb_strtoupper(mb_substr($sidebarName, 0, 1)) : '?';
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<style>
    .account-shell {
        display: flex;
        align-items: flex-start;
        gap: 28px;
        max-width: var(--container-width);
        margin: 40px auto;
        padding: 0 20px;
    }

    .account-sidebar {
        flex: 0 0 240px;
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-soft);
        padding: 24px 20px;
        position: sticky;
        top: 20px;
    }

    .account-sidebar__user {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 18px;
        margin-bottom: 14px;
        border-bottom: 1px solid var(--color-border);
    }

    .account-avatar {
        flex-shrink: 0;
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--color-primary-light);
        color: var(--color-primary-dark);
        font-size: 16px;
        font-weight: 800;
    }

    .account-sidebar__name {
        font-size: 14px;
        font-weight: 700;
        color: var(--color-text);
        word-break: break-word;
    }

    .account-nav {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .account-nav__link {
        display: block;
        padding: 10px 12px;
        font-size: 14px;
        font-weight: 600;
        color: var(--color-text-light);
        border-radius: var(--radius-sm);
        transition: background 0.15s ease, color 0.15s ease;
    }

    .account-nav__link:hover {
        background: var(--color-primary-light);
        color: var(--color-primary-dark);
    }

    .account-nav__link.is-active {
        background: var(--color-primary);
        color: var(--color-white);
    }

    .account-nav__logout-form {
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid var(--color-border);
    }

    .account-nav__link--logout {
        width: 100%;
        text-align: left;
        font-family: inherit;
        background: none;
        border: none;
        cursor: pointer;
        color: #b3261e;
    }

    .account-nav__link--logout:hover {
        background: #fbeceb;
        color: #8a1c14;
    }

    .account-content {
        flex: 1 1 0;
        min-width: 0;
    }

    .account-content>h1 {
        margin: 0 0 20px;
        font-size: 22px;
        font-weight: 800;
        color: var(--color-primary-dark);
    }

    @media (max-width: 780px) {
        .account-shell {
            flex-direction: column;
        }

        .account-sidebar {
            flex: 1 1 auto;
            width: 100%;
            position: static;
        }

        .account-nav {
            flex-direction: row;
            flex-wrap: wrap;
        }
    }
</style>

<div class="account-shell">
    <aside class="account-sidebar">
        <div class="account-sidebar__user">
            <div class="account-avatar"><?= htmlspecialchars($sidebarInitial) ?></div>
            <div class="account-sidebar__name"><?= htmlspecialchars($sidebarName) ?></div>
        </div>

        <nav class="account-nav">
            <?php foreach ($navItems as $key => $item): ?>
                <a
                    href="<?= htmlspecialchars($item['href']) ?>"
                    class="account-nav__link<?= $activeNav === $key ? ' is-active' : '' ?>"><?= htmlspecialchars($item['label']) ?></a>
            <?php endforeach; ?>
        </nav>

        <form method="post" action="logout.php" class="account-nav__logout-form">
            <button type="submit" class="account-nav__link account-nav__link--logout">Logout</button>
        </form>
    </aside>

    <main class="account-content">

