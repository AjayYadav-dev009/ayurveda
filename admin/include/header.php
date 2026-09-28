<?php
$pageTitle = $pageTitle ?? 'Admin';
$activeNav = $activeNav ?? '';

/**
 * URL path of the /admin folder (e.g. "/ayurveda/admin"), so sidebar links
 * work from any page depth. Derived from the running script's file path and
 * URL, so it needs no config and works in a subfolder or at the site root.
 */
if (!isset($adminBase)) {
    $adminBase = '/admin'; // last-resort fallback
    $adminDir   = realpath(__DIR__ . '/..');
    $scriptFile = isset($_SERVER['SCRIPT_FILENAME']) ? realpath($_SERVER['SCRIPT_FILENAME']) : false;
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

    if ($adminDir !== false && $scriptFile !== false) {
        $adminDir   = rtrim(str_replace('\\', '/', $adminDir), '/');
        $scriptFile = str_replace('\\', '/', $scriptFile);

        if (stripos($scriptFile, $adminDir . '/') === 0) {
            // e.g. "/settings/index.php" — the script's path inside /admin
            $rel = substr($scriptFile, strlen($adminDir));
            if (
                strlen($scriptName) >= strlen($rel)
                && strcasecmp(substr($scriptName, -strlen($rel)), $rel) === 0
            ) {
                $adminBase = substr($scriptName, 0, strlen($scriptName) - strlen($rel));
            }
        }
    }
}
$adminUrl = function ($path) use ($adminBase) {
    return htmlspecialchars($adminBase . '/' . ltrim($path, '/'), ENT_QUOTES, 'UTF-8');
};

// Storefront home = the folder above /admin (e.g. "/ayurveda/" or "/").
$siteUrl = rtrim(str_replace('\\', '/', dirname($adminBase)), '/') . '/';

// Signed-in admin (set by includes/auth.php).
$adminName  = trim((string) ($_SESSION['admin_name'] ?? 'Admin'));
$adminEmail = trim((string) ($_SESSION['admin_email'] ?? ''));
$adminInitial = strtoupper(function_exists('mb_substr') ? mb_substr($adminName ?: 'A', 0, 1) : substr($adminName ?: 'A', 0, 1));

/* ---------------------------------------------------------------------------
   Navigation — single source of truth. The dashboard (index.php) reuses this
   list for its quick-action tiles, so add a page here once and it appears in
   both places. 'key' must match the $activeNav a page sets.
   ------------------------------------------------------------------------ */
$adminIcons = [
    'dashboard'       => '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
    'products'        => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
    'categories'      => '<path d="M12 3 3 8l9 5 9-5-9-5Z"/><path d="m3 13 9 5 9-5"/>',
    'orders'          => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
    'users'           => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    'blog'            => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Z"/><path d="M14 3v5h5"/><path d="M9 13h6M9 17h6"/>',
    'team'            => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="11" r="2.5"/><path d="M5.5 17a3.5 3.5 0 0 1 7 0"/><path d="M15 10h3M15 14h3"/>',
    'banners'         => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="1.8"/><path d="m21 15-4.5-4.5L6 21"/>',
    'reviews'         => '<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9L12 3Z"/>',
    'promo'           => '<circle cx="12" cy="12" r="9"/><path d="m10 8.5 5 3.5-5 3.5Z"/>',
    'transformations' => '<path d="M12 3l1.8 4.7L18.5 9.5l-4.7 1.8L12 16l-1.8-4.7L5.5 9.5l4.7-1.8L12 3Z"/><path d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8L19 15Z"/>',
    'settings'        => '<path d="M4 6h9M17 6h3M4 12h3M11 12h9M4 18h11M19 18h1"/><circle cx="15" cy="6" r="2"/><circle cx="9" cy="12" r="2"/><circle cx="17" cy="18" r="2"/>',
    'contact-messages' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
    'dosha-leads'     => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
];

$adminNav = [
    'Overview' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => 'index.php', 'desc' => 'Overview of your store'],
    ],
    'Catalog' => [
        ['key' => 'products', 'label' => 'Products', 'href' => 'products/index.php', 'desc' => 'Add, edit and organise your catalogue'],
        ['key' => 'categories', 'label' => 'Categories', 'href' => 'categories/index.php', 'desc' => 'Manage the Shop By Category collections'],
    ],
    'Sales' => [
        ['key' => 'orders', 'label' => 'Orders', 'href' => 'orders/index.php', 'desc' => 'Review and update customer orders'],
    ],
    'People & Content' => [
        ['key' => 'users', 'label' => 'Users', 'href' => 'users/index.php', 'desc' => 'View registered customers'],
        ['key' => 'contact-messages', 'label' => 'Contact Messages', 'href' => 'contact/index.php', 'desc' => 'Messages submitted through the Contact page'],
        ['key' => 'dosha-leads', 'label' => 'Dosha Test Leads', 'href' => 'dosha/index.php', 'desc' => 'Leads captured by the Dosha Test modal'],
        ['key' => 'blog', 'label' => 'Blog Management', 'href' => 'blog/', 'desc' => 'Write and manage blog posts'],
        ['key' => 'team', 'label' => 'Team Management', 'href' => 'team-management.php', 'desc' => 'Ayurvedic experts shown on the homepage'],
    ],
    'Storefront' => [
        ['key' => 'banners', 'label' => 'Banner Management', 'href' => 'banner-management.php', 'desc' => 'Homepage banners and hero slides'],
        ['key' => 'reviews', 'label' => 'Review Management', 'href' => 'review-management.php', 'desc' => 'Moderate customer reviews'],
        ['key' => 'promo', 'label' => 'Promo Video Management', 'href' => 'promo-video-management.php', 'desc' => 'Videos in Stories From Our Brand'],
        ['key' => 'transformations', 'label' => 'Transformation Management', 'href' => 'transformation-management.php', 'desc' => 'Customer transformation journeys'],
    ],
    'System' => [
        ['key' => 'settings', 'label' => 'Settings Management', 'href' => 'settings/index.php', 'desc' => 'Store-wide settings'],
    ],
];

$adminNavIndex = [];
foreach ($adminNav as $group) {
    foreach ($group as $item) {
        $adminNavIndex[$item['key']] = $item;
    }
}

$adminIcon = function ($key, $extra = '') use ($adminIcons) {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"' . $extra . '>' . ($adminIcons[$key] ?? '') . '</svg>';
};

$logoutUrl = $adminUrl('logout.php'); // change if your logout script lives elsewhere
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> · Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <script>
        // Restore the collapsed-sidebar preference before first paint (no flash).
        try {
            if (localStorage.getItem('adm-sb') === '1') document.documentElement.classList.add('sb-collapsed');
        } catch (e) {}
    </script>
    <style>
        :root {
            /* Original tokens — other admin pages may rely on these, so the names are unchanged. */
            --leaf: #2f9e6e;
            --leaf-dark: #22794f;
            --leaf-tint: #e7f6ee;
            --ink: #1c2b3a;
            --sky: #0f6fb0;
            --sky-tint: #eaf4fb;
            --paper: #ffffff;
            --mist: #f4f8fb;
            --line: #e1e9f0;
            --muted: #64798c;

            /* Admin shell */
            --adm-bg: #f3f6f2;
            --adm-side: #17483d;
            --adm-side-deep: #10382f;
            --adm-cream: #f4efe2;
            --adm-gold: #d8b678;
            --adm-sidebar-w: 268px;
            --adm-sidebar-w-collapsed: 80px;
            --adm-topbar-h: 68px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Manrope', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--adm-bg);
            color: var(--ink);
            margin: 0;
            -webkit-font-smoothing: antialiased;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* ==================== Sidebar ==================== */

        .sidebar {
            position: sticky;
            top: 0;
            align-self: flex-start;
            flex: 0 0 var(--adm-sidebar-w);
            width: var(--adm-sidebar-w);
            height: 100vh;
            display: flex;
            flex-direction: column;
            color: var(--adm-cream);
            background:
                radial-gradient(rgba(244, 239, 226, 0.06) 1px, transparent 1.2px) 0 0 / 22px 22px,
                linear-gradient(180deg, var(--adm-side) 0%, var(--adm-side-deep) 100%);
            transition: width 0.25s ease, flex-basis 0.25s ease, transform 0.3s ease;
            z-index: 60;
        }

        .sidebar__brand {
            display: flex;
            align-items: center;
            gap: 12px;
            height: var(--adm-topbar-h);
            padding: 0 20px;
            margin: 0;
            flex-shrink: 0;
            border-bottom: 1px solid rgba(244, 239, 226, 0.09);
            color: #fff;
            text-decoration: none;
        }

        .sidebar__brand-mark {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            flex-shrink: 0;
            border-radius: 11px;
            background: rgba(216, 182, 120, 0.16);
            border: 1px solid rgba(216, 182, 120, 0.4);
            color: var(--adm-gold);
        }

        .sidebar__brand-mark svg {
            width: 20px;
            height: 20px;
        }

        .sidebar__brand-name {
            display: block;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: -0.01em;
            line-height: 1.2;
            white-space: nowrap;
        }

        .sidebar__brand-sub {
            display: block;
            margin-top: 2px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--adm-gold);
            opacity: 0.85;
        }

        .sidebar__nav {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding: 14px 12px 18px;
            scrollbar-width: thin;
            scrollbar-color: rgba(244, 239, 226, 0.25) transparent;
        }

        .sidebar__nav::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar__nav::-webkit-scrollbar-thumb {
            background: rgba(244, 239, 226, 0.22);
            border-radius: 6px;
        }

        .nav__section {
            display: block;
            margin: 18px 12px 6px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(244, 239, 226, 0.5);
            white-space: nowrap;
        }

        .nav__section:first-child {
            margin-top: 2px;
        }

        .nav__link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 13px;
            margin-bottom: 2px;
            padding: 10px 12px;
            border-radius: 11px;
            color: rgba(244, 239, 226, 0.78);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            transition: background 0.15s ease, color 0.15s ease;
        }

        .nav__icon {
            display: grid;
            place-items: center;
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .nav__icon svg {
            width: 20px;
            height: 20px;
        }

        .nav__label {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .nav__link:hover {
            background: rgba(244, 239, 226, 0.08);
            color: #fff;
        }

        .nav__link:focus-visible {
            outline: 2px solid var(--adm-gold);
            outline-offset: 2px;
        }

        .nav__link.active {
            background: rgba(216, 182, 120, 0.16);
            color: #fff;
        }

        .nav__link.active::before {
            content: "";
            position: absolute;
            left: -12px;
            top: 9px;
            bottom: 9px;
            width: 4px;
            border-radius: 0 4px 4px 0;
            background: var(--adm-gold);
        }

        .nav__link.active .nav__icon {
            color: var(--adm-gold);
        }

        /* Footer: signed-in user + sign out */

        .sidebar__foot {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 14px 16px;
            border-top: 1px solid rgba(244, 239, 226, 0.09);
            background: rgba(0, 0, 0, 0.14);
        }

        .avatar {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            flex-shrink: 0;
            border-radius: 50%;
            background: var(--adm-gold);
            color: var(--adm-side);
            font-size: 15px;
            font-weight: 800;
        }

        .sidebar__user {
            flex: 1;
            min-width: 0;
            line-height: 1.3;
        }

        .sidebar__user-name,
        .sidebar__user-email {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .sidebar__user-name {
            font-size: 13.5px;
            font-weight: 700;
            color: #fff;
        }

        .sidebar__user-email {
            font-size: 11.5px;
            color: rgba(244, 239, 226, 0.6);
        }

        .sidebar__logout {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            flex-shrink: 0;
            border-radius: 10px;
            color: rgba(244, 239, 226, 0.75);
            transition: background 0.15s ease, color 0.15s ease;
        }

        .sidebar__logout:hover {
            background: rgba(244, 239, 226, 0.1);
            color: #fff;
        }

        .sidebar__logout svg {
            width: 18px;
            height: 18px;
        }

        /* ---- Collapsed (desktop only) ---- */

        @media (min-width: 901px) {
            .sb-collapsed .sidebar {
                flex-basis: var(--adm-sidebar-w-collapsed);
                width: var(--adm-sidebar-w-collapsed);
            }

            .sb-collapsed .sidebar__brand {
                justify-content: center;
                padding: 0;
            }

            .sb-collapsed .sidebar__brand-text,
            .sb-collapsed .nav__label,
            .sb-collapsed .sidebar__user {
                display: none;
            }

            .sb-collapsed .nav__section {
                height: 1px;
                margin: 14px 14px;
                font-size: 0;
                background: rgba(244, 239, 226, 0.12);
            }

            .sb-collapsed .nav__link {
                justify-content: center;
                padding: 12px 0;
            }

            .sb-collapsed .nav__link.active::before {
                left: -12px;
            }

            .sb-collapsed .sidebar__foot {
                flex-direction: column;
                padding: 14px 0;
            }
        }

        /* ==================== Main area ==================== */

        .main {
            flex: 1;
            min-width: 0;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 40;
            display: flex;
            align-items: center;
            gap: 14px;
            height: var(--adm-topbar-h);
            padding: 0 28px;
            background: rgba(255, 255, 255, 0.86);
            -webkit-backdrop-filter: saturate(1.4) blur(10px);
            backdrop-filter: saturate(1.4) blur(10px);
            border-bottom: 1px solid var(--line);
        }

        .topbar__toggle {
            display: grid;
            place-items: center;
            width: 40px;
            height: 40px;
            padding: 0;
            border: 1px solid var(--line);
            border-radius: 11px;
            background: #fff;
            color: var(--adm-side);
            cursor: pointer;
            transition: background 0.15s ease, border-color 0.15s ease;
        }

        .topbar__toggle:hover {
            background: #eef4ef;
            border-color: #cfdcd2;
        }

        .topbar__toggle:focus-visible {
            outline: 2px solid var(--adm-side);
            outline-offset: 2px;
        }

        .topbar__toggle svg {
            width: 20px;
            height: 20px;
        }

        .topbar__crumbs {
            min-width: 0;
            line-height: 1.25;
        }

        .topbar__crumb-top {
            display: block;
            font-size: 11.5px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .topbar__title {
            display: block;
            margin: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: var(--ink);
        }

        .topbar__spacer {
            flex: 1;
        }

        .topbar__store {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #fff;
            color: var(--adm-side);
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.15s ease, border-color 0.15s ease;
        }

        .topbar__store:hover {
            background: #eef4ef;
            border-color: #cfdcd2;
        }

        .topbar__store svg {
            width: 16px;
            height: 16px;
        }

        .topbar__user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-left: 6px;
        }

        .topbar__user .avatar {
            width: 36px;
            height: 36px;
            font-size: 14px;
            background: var(--adm-side);
            color: var(--adm-gold);
        }

        .topbar__user-name {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--ink);
        }

        .content {
            padding: 28px;
        }

        .sb-overlay {
            display: none;
        }

        /* ==================== Mobile / tablet ==================== */

        @media (max-width: 900px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: min(84vw, 300px);
                flex-basis: auto;
                transform: translateX(-100%);
                visibility: hidden;
                box-shadow: 10px 0 40px rgba(0, 0, 0, 0.3);
                transition: transform 0.3s ease, visibility 0.3s ease;
            }

            .sb-open .sidebar {
                transform: none;
                visibility: visible;
            }

            .sb-overlay {
                display: block;
                position: fixed;
                inset: 0;
                z-index: 55;
                background: rgba(10, 30, 24, 0.5);
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease, visibility 0.3s ease;
            }

            .sb-open .sb-overlay {
                opacity: 1;
                visibility: visible;
            }

            .sb-open {
                overflow: hidden;
            }

            .topbar {
                padding: 0 16px;
            }

            .topbar__user-name,
            .topbar__store span {
                display: none;
            }

            .topbar__store {
                padding: 9px 11px;
            }

            .content {
                padding: 20px 16px;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .sidebar,
            .sb-overlay {
                transition: none;
            }
        }
    </style>
</head>

<body>
    <div class="layout">
        <aside class="sidebar" id="adminSidebar" aria-label="Admin navigation">
            <a class="sidebar__brand" href="<?= $adminUrl('index.php') ?>">
                <span class="sidebar__brand-mark">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M11 20A7 7 0 0 1 4 13V7a1 1 0 0 1 1-1h1a7 7 0 0 1 7 7v7Z" />
                        <path d="M11 20v-7a7 7 0 0 1 7-7h1a1 1 0 0 1 1 1v1a7 7 0 0 1-7 7" />
                    </svg>
                </span>
                <span class="sidebar__brand-text">
                    <span class="sidebar__brand-name">Ayurveda Admin</span>
                    <span class="sidebar__brand-sub">Control panel</span>
                </span>
            </a>

            <nav class="sidebar__nav">
                <?php foreach ($adminNav as $groupLabel => $items): ?>
                    <span class="nav__section"><?= htmlspecialchars($groupLabel) ?></span>
                    <?php foreach ($items as $item):
                        $isActive = $activeNav === $item['key'];
                    ?>
                        <a href="<?= $adminUrl($item['href']) ?>"
                            class="nav__link<?= $isActive ? ' active' : '' ?>"
                            title="<?= htmlspecialchars($item['label']) ?>"
                            <?= $isActive ? 'aria-current="page"' : '' ?>>
                            <span class="nav__icon"><?= $adminIcon($item['key']) ?></span>
                            <span class="nav__label"><?= htmlspecialchars($item['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </nav>

            <script>
                // Centre the active sidebar link inside the scrollable nav.
                (function() {
                    var nav = document.querySelector('.sidebar__nav');
                    var link = nav && nav.querySelector('.nav__link.active');
                    if (!link) return;

                    function centre() {
                        var n = nav.getBoundingClientRect();
                        var l = link.getBoundingClientRect();
                        var offset = (l.top - n.top) + nav.scrollTop;
                        nav.scrollTop = offset - (nav.clientHeight - link.offsetHeight) / 2;
                    }
                    centre();
                    window.addEventListener('load', centre);
                })();
            </script>

            <div class="sidebar__foot">
                <span class="avatar" aria-hidden="true"><?= htmlspecialchars($adminInitial) ?></span>
                <span class="sidebar__user">
                    <span class="sidebar__user-name"><?= htmlspecialchars($adminName) ?></span>
                    <?php if ($adminEmail !== ''): ?>
                        <span class="sidebar__user-email"><?= htmlspecialchars($adminEmail) ?></span>
                    <?php endif; ?>
                </span>
                <a class="sidebar__logout" href="<?= $logoutUrl ?>" title="Sign out" aria-label="Sign out">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <path d="m16 17 5-5-5-5" />
                        <path d="M21 12H9" />
                    </svg>
                </a>
            </div>
        </aside>

        <div class="sb-overlay" data-sb-overlay></div>

        <main class="main">
            <header class="topbar">
                <button type="button" class="topbar__toggle" data-sb-toggle aria-controls="adminSidebar" aria-expanded="true" aria-label="Toggle sidebar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="16" rx="2.5" />
                        <path d="M9 4v16" />
                    </svg>
                </button>

                <div class="topbar__crumbs">
                    <span class="topbar__crumb-top">Admin</span>
                    <span class="topbar__title"><?= htmlspecialchars($pageTitle) ?></span>
                </div>

                <span class="topbar__spacer"></span>

                <a class="topbar__store" href="<?= htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 9l1.5-5h15L21 9" />
                        <path d="M3 9a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0" />
                        <path d="M5 12v8h14v-8" />
                    </svg>
                    <span>View store</span>
                </a>

                <div class="topbar__user">
                    <span class="avatar" aria-hidden="true"><?= htmlspecialchars($adminInitial) ?></span>
                    <span class="topbar__user-name"><?= htmlspecialchars($adminName) ?></span>
                </div>
            </header>

            <div class="content">