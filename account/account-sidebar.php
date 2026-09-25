<?php

if (!isset($activeNav)) {
    $activeNav = '';
}

$navItems = [
    'dashboard' => ['label' => 'Dashboard', 'href' => BASE_URL . 'account/index.php'],
    'orders'    => ['label' => 'My Orders', 'href' => BASE_URL . 'account/my-orders.php'],
    'wishlist'  => ['label' => 'Wishlist', 'href' => BASE_URL . 'account/wishlist.php'],
    'reviews'   => ['label' => 'My Reviews', 'href' => BASE_URL . 'account/reviews.php'],
    'addresses' => ['label' => 'Addresses', 'href' => BASE_URL . 'account/address/index.php'],
    'profile'   => ['label' => 'Profile', 'href' => BASE_URL . 'account/profile.php'],
];

// Inline stroke icons, one per nav item — same visual language as the
// admin sidebar (currentColor stroke, 1.8 weight).
$navIcons = [
    'dashboard' => '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
    'orders'    => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
    'wishlist'  => '<path d="M20.8 8.6c0-3-2.3-5.4-5.2-5.4-1.8 0-3.4.9-4.4 2.4C10.2 4.1 8.6 3.2 6.8 3.2c-2.9 0-5.2 2.4-5.2 5.4 0 6.1 8.6 11 8.6 11s8.6-4.9 8.6-11Z"/>',
    'reviews'   => '<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9L12 3Z"/>',
    'addresses' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
    'profile'   => '<circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>',
];

$navIcon = function ($key) use ($navIcons) {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($navIcons[$key] ?? '') . '</svg>';
};

$sidebarName = '';
if (isset($user['name']) && $user['name'] !== '') {
    $sidebarName = $user['name'];
} elseif (isset($_SESSION['customer_name'])) {
    $sidebarName = $_SESSION['customer_name'];
}
$sidebarInitial = $sidebarName !== '' ? mb_strtoupper(mb_substr($sidebarName, 0, 1)) : '?';

$sidebarEmail = $user['email'] ?? ($_SESSION['customer_email'] ?? '');
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    .account-shell {
        /* ---- Shared account-section tokens ----
           Every account/*.php page includes this file first, so these
           custom properties are available (via inheritance) to whatever
           CSS each page adds afterwards inside .account-content. */
        --acc-side: #17483d;
        --acc-side-deep: #10382f;
        --acc-cream: #f4efe2;
        --acc-gold: #d8b678;
        --acc-gold-dark: #b3904f;

        --acc-white: var(--color-white, #ffffff);
        --acc-text: var(--color-text, #1c2b3a);
        --acc-text-light: var(--color-text-light, #64798c);
        --acc-border: var(--color-border, #e3e9e6);
        --acc-bg: var(--color-bg, #f6f9f7);

        --acc-radius-sm: var(--radius-sm, 8px);
        --acc-radius-md: var(--radius-md, 12px);
        --acc-radius-lg: var(--radius-lg, 16px);
        --acc-shadow: 0 8px 24px rgba(16, 56, 47, 0.07);
        --acc-shadow-hover: 0 10px 28px rgba(16, 56, 47, 0.12);

        display: flex;
        align-items: flex-start;
        gap: 28px;
        max-width: var(--container-width);
        margin: 40px auto;
        padding: 0 20px;
        font-family: 'Manrope', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    /* ==================== Sidebar ==================== */

    .account-sidebar {
        flex: 0 0 268px;
        border-radius: var(--acc-radius-lg);
        box-shadow: 0 10px 30px rgba(16, 56, 47, 0.16);
        padding: 22px 16px 16px;
        position: sticky;
        top: 20px;
        color: var(--acc-cream);
        background:
            radial-gradient(rgba(244, 239, 226, 0.06) 1px, transparent 1.2px) 0 0 / 22px 22px,
            linear-gradient(180deg, var(--acc-side) 0%, var(--acc-side-deep) 100%);
        overflow: hidden;
    }

    .account-sidebar__user {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 4px 8px 18px;
        margin-bottom: 10px;
        border-bottom: 1px solid rgba(244, 239, 226, 0.12);
    }

    .account-avatar {
        flex-shrink: 0;
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--acc-gold);
        color: var(--acc-side);
        font-size: 16px;
        font-weight: 800;
    }

    .account-sidebar__name {
        font-size: 14px;
        font-weight: 700;
        color: #fff;
        word-break: break-word;
        line-height: 1.3;
    }

    .account-sidebar__email {
        display: block;
        margin-top: 2px;
        font-size: 11.5px;
        font-weight: 500;
        color: rgba(244, 239, 226, 0.6);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .account-nav {
        display: flex;
        flex-direction: column;
        gap: 2px;
        padding: 0 4px;
    }

    .account-nav__link {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        font-size: 14px;
        font-weight: 600;
        color: rgba(244, 239, 226, 0.78);
        border-radius: 11px;
        text-decoration: none;
        transition: background 0.15s ease, color 0.15s ease;
    }

    .account-nav__link .nav-icon {
        display: grid;
        place-items: center;
        width: 18px;
        height: 18px;
        flex-shrink: 0;
    }

    .account-nav__link .nav-icon svg {
        width: 18px;
        height: 18px;
    }

    .account-nav__link:hover {
        background: rgba(244, 239, 226, 0.08);
        color: #fff;
    }

    .account-nav__link.is-active {
        background: rgba(216, 182, 120, 0.16);
        color: #fff;
    }

    .account-nav__link.is-active .nav-icon {
        color: var(--acc-gold);
    }

    .account-nav__link.is-active::before {
        content: "";
        position: absolute;
        left: -4px;
        top: 8px;
        bottom: 8px;
        width: 4px;
        border-radius: 0 4px 4px 0;
        background: var(--acc-gold);
    }

    .account-nav__logout-form {
        margin: 12px 4px 0;
        padding-top: 14px;
        border-top: 1px solid rgba(244, 239, 226, 0.12);
    }

    .account-nav__link--logout {
        width: 100%;
        text-align: left;
        font-family: inherit;
        background: none;
        border: none;
        cursor: pointer;
        color: #e79b91;
    }

    .account-nav__link--logout:hover {
        background: rgba(217, 92, 74, 0.16);
        color: #ffb3a7;
    }

    /* ==================== Content shell ==================== */

    .account-content {
        flex: 1 1 0;
        min-width: 0;
    }

    .account-content__eyebrow {
        margin: 0 0 4px;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--acc-gold-dark);
    }

    .account-content>h1 {
        margin: 0 0 22px;
        font-size: 24px;
        font-weight: 800;
        color: var(--acc-side);
        letter-spacing: -0.01em;
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

        .account-nav__link.is-active::before {
            display: none;
        }
    }
</style>

<div class="account-shell">
    <aside class="account-sidebar">
        <div class="account-sidebar__user">
            <div class="account-avatar"><?= htmlspecialchars($sidebarInitial) ?></div>
            <div>
                <div class="account-sidebar__name"><?= htmlspecialchars($sidebarName) ?></div>
                <?php if ($sidebarEmail !== ''): ?>
                    <span class="account-sidebar__email"><?= htmlspecialchars($sidebarEmail) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <nav class="account-nav">
            <?php foreach ($navItems as $key => $item): ?>
                <a
                    href="<?= htmlspecialchars($item['href']) ?>"
                    class="account-nav__link<?= $activeNav === $key ? ' is-active' : '' ?>">
                    <span class="nav-icon"><?= $navIcon($key) ?></span>
                    <span><?= htmlspecialchars($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <form method="post" action="<?= htmlspecialchars(BASE_URL . 'account/logout.php') ?>" class="account-nav__logout-form">
            <button type="submit" class="account-nav__link account-nav__link--logout">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg></span>
                <span>Logout</span>
            </button>
        </form>
    </aside>

    <main class="account-content">