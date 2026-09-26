<?php

require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../function/category.php';
require_once __DIR__ . '/../function/product.php';

try {
    $browsableCategories = getAllCategoriesWithProducts($conn);
} catch (Exception $e) {
    $browsableCategories = [];
}

$maxProductsPerCategory = 8;
$megaMenuProducts = [];

foreach ($browsableCategories as $category) {
    $products = [];

    $productResult = getProductsByCategorySlug($conn, $category['slug']);
    if ($productResult && mysqli_num_rows($productResult) > 0) {
        $count = 0;
        while ($count < $maxProductsPerCategory && ($product = mysqli_fetch_assoc($productResult))) {
            $products[] = $product;
            $count++;
        }
    }

    $megaMenuProducts[$category['id']] = $products;
}


require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/cart.php';
require_once __DIR__ . '/../function/wishlist.php';

$headerCartCount = 0;
$headerWishlistCount = 0;
if (isCustomerLogin()) {
    try {
        $headerCartTotals = getCartTotals($conn, $_SESSION['customer_id']);
        $headerCartCount = (int) $headerCartTotals['item_count'];
    } catch (Exception $e) {
        $headerCartCount = 0;
    }
    try {
        $headerWishlistCount = getWishlistCount($conn, $_SESSION['customer_id']);
    } catch (Exception $e) {
        $headerWishlistCount = 0;
    }
}

$siteSettings = [];
try {
    $settingsResult = mysqli_query($conn, "SELECT setting_key, setting_value FROM settings");
    if ($settingsResult) {
        while ($settingRow = mysqli_fetch_assoc($settingsResult)) {
            $siteSettings[$settingRow['setting_key']] = $settingRow['setting_value'];
        }
    }
} catch (Exception $e) {
    $siteSettings = [];
}

$siteName = ($siteSettings['site_name'] ?? '') !== ''
    ? $siteSettings['site_name']
    : (defined('SITE_NAME') ? SITE_NAME : 'Store');
$siteTagline = $siteSettings['site_tagline'] ?? '';
$siteLogo = $siteSettings['site_logo'] ?? '';
$siteFavicon = $siteSettings['site_favicon'] ?? '';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteName) ?></title>
    <?php if ($siteFavicon !== ''): ?>
        <link rel="icon" href="<?= htmlspecialchars(BASE_URL . ltrim($siteFavicon, '/')) ?>">
    <?php else: ?>
        <link rel="icon" href="<?= BASE_URL ?>assets/img/favicon.ico">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/global.css">
    <script src="<?= BASE_URL ?>assets/js/global.js" defer></script>
    <style>
        .site-header {
            font-family: Arial, sans-serif;
        }

        /* =========================================
                Announcement / Ticker Bar
        ========================================= */

        .announcement-bar {
            position: relative;
            background: var(--color-primary-dark);
            color: var(--color-white);
            display: flex;
            align-items: center;
            overflow: hidden;
            padding: 9px 0;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .announcement-bar__viewport {
            flex: 1;
            overflow: hidden;
            mask-image: linear-gradient(to right, transparent 0, #000 32px, #000 calc(100% - 56px), transparent 100%);
            -webkit-mask-image: linear-gradient(to right, transparent 0, #000 32px, #000 calc(100% - 56px), transparent 100%);
        }

        .announcement-bar__track {
            display: flex;
            align-items: center;
            width: max-content;
            white-space: nowrap;
            animation: announcement-scroll 22s linear infinite;
        }

        .announcement-bar__track span {
            padding: 0 28px;
            display: inline-flex;
            align-items: center;
        }

        .announcement-bar__track span::after {
            content: "\2022";
            margin-left: 28px;
            color: var(--color-accent);
        }

        @keyframes announcement-scroll {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(-50%);
            }
        }

        .announcement-bar:hover .announcement-bar__track {
            animation-play-state: paused;
        }

        @media (prefers-reduced-motion: reduce) {
            .announcement-bar__track {
                animation: none;
            }
        }

        .announcement-bar__more {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            padding-right: 20px;
            color: var(--color-white);
            font-size: 16px;
            letter-spacing: 0;
        }

        /* Main header */
        .main-header {
            background: var(--color-white);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            padding: 12px 32px;
            border-bottom: 1px solid var(--color-border);
        }

        .logo {
            display: flex;
            align-items: center;
            text-align: center;
            line-height: 1.1;
        }

        .logo__icon {
            width: 60px;
            height: 60px;
            margin-bottom: 2px;
            object-fit: contain;
        }

        .logo__name {
            font-size: 19px;
            font-weight: 800;
            letter-spacing: 0.05em;
            color: var(--color-primary);
            text-transform: uppercase;
        }

        .logo__tagline {
            font-size: 12px;
            font-weight: 600;
            color: var(--color-accent);
            letter-spacing: 0.03em;
        }

        /* Nav */
        .main-nav {
            display: flex;
            align-items: center;
            gap: 30px;
            flex-wrap: wrap;
        }

        .main-nav a {
            font-size: 15px;
            font-weight: 500;
            color: var(--color-text);
            padding-bottom: 4px;
            border-bottom: 2px solid transparent;
            transition: color 0.15s ease, border-color 0.15s ease;
        }

        .main-nav a:hover {
            color: var(--color-primary);
        }

        .main-nav a.active {
            color: var(--color-primary);
            border-bottom-color: var(--color-accent);
        }

        .main-nav .has-dropdown {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .main-nav .caret {
            font-size: 11px;
            margin-top: 1px;
        }

        /* Header action icons */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-actions a {
            display: inline-flex;
            color: var(--color-text);
        }

        .header-actions svg {
            width: 22px;
            height: 22px;
        }

        .header-actions .icon-account {
            color: var(--color-accent);
        }

        .icon-cart {
            position: relative;
        }

        .icon-cart__badge {
            position: absolute;
            top: -7px;
            right: -9px;
            min-width: 16px;
            height: 16px;
            padding: 0 4px;
            border-radius: 999px;
            background: var(--color-primary);
            color: var(--color-white);
            font-size: 10px;
            font-weight: 700;
            line-height: 16px;
            text-align: center;
        }

        .icon-wishlist {
            position: relative;
        }

        .icon-wishlist__badge {
            position: absolute;
            top: -7px;
            right: -9px;
            min-width: 16px;
            height: 16px;
            padding: 0 4px;
            border-radius: 999px;
            background: var(--color-primary);
            color: var(--color-white);
            font-size: 10px;
            font-weight: 700;
            line-height: 16px;
            text-align: center;
        }



        .nav-dropdown {
            position: relative;
        }

        .mega-menu {
            position: absolute;
            top: calc(100% + 2px);
            left: 50%;
            transform: translateX(-50%) translateY(10px);

            display: flex;
            align-items: stretch;

            width: min(92vw, 820px);
            max-height: 440px;

            background: var(--color-white);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-soft);
            overflow: hidden;

            opacity: 0;
            visibility: hidden;
            pointer-events: none;

            transition:
                opacity 0.2s ease,
                transform 0.2s ease,
                visibility 0.2s ease;

            z-index: 1000;
        }

        .nav-dropdown:hover .mega-menu {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;

            transform: translateX(-50%) translateY(0);
        }

        /* Small invisible bridge between Shop All and the menu */

        .mega-menu::before {
            content: "";
            position: absolute;
            top: -13px;
            left: 0;
            width: 100%;
            height: 13px;
        }

        /* Left: scrollable list of top-level categories */

        .mega-menu__sidebar {
            flex: 0 0 220px;
            overflow-y: auto;
            padding: 10px;
            border-right: 1px solid var(--color-border);
            background: var(--color-primary-light);
        }

        .mega-menu__sidebar-item>a {
            display: block;
            padding: 10px 12px;
            color: var(--color-text);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border-radius: var(--radius-sm);
            transition: background 0.15s ease, color 0.15s ease;
        }

        .mega-menu__sidebar-item>a:hover {
            color: var(--color-primary);
        }

        .mega-menu__sidebar-item.is-active>a {
            background: var(--color-white);
            color: var(--color-primary);
            box-shadow: var(--shadow-soft);
        }

        /* Right: the active category's own products */

        .mega-menu__panels {
            flex: 1;
            overflow-y: auto;
            display: flex;
        }

        .mega-menu__panel {
            display: none;
            flex-direction: column;
            width: 100%;
            padding: 18px 22px;
        }

        .mega-menu__panel.is-active {
            display: flex;
        }

        .mega-menu__products {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px 20px;
        }

        .mega-menu__products a {
            display: block;
            padding: 6px 0;
            color: var(--color-text-light);
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .mega-menu__products a:hover {
            color: var(--color-primary);
        }

        .mega-menu__view-all {
            align-self: flex-start;
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid var(--color-border);
            color: var(--color-primary);
            font-size: 17px !important;
            font-weight: 700 !important;
            text-decoration: none;
        }

        .mega-menu__view-all:hover {
            color: var(--color-primary-dark);
        }

        /* =========================================
                    Dropdown Arrow
            ========================================= */

        .nav-dropdown>a .caret {
            transition: transform 0.2s ease;
        }

        .nav-dropdown:hover>a .caret {
            transform: rotate(180deg);
        }

        /* =========================================
                Mobile navigation (hamburger + drawer)
           Hidden on desktop; switched on inside the
           max-width: 900px block below.
        ========================================= */

        .nav-toggle,
        .main-nav__head,
        .nav-dropdown__toggle,
        .nav-overlay {
            display: none;
        }

        @media (max-width: 900px) {

            .announcement-bar {
                font-size: 10.5px;
            }

            /* Header bar: [hamburger]   [logo]   [icons] */
            .main-header {
                display: grid;
                grid-template-columns: 1fr auto 1fr;
                align-items: center;
                gap: 10px;
                padding: 10px 14px;
            }

            .nav-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                justify-self: start;
                width: 42px;
                height: 42px;
                margin-left: -8px;
                padding: 0;
                border: 0;
                border-radius: var(--radius-sm);
                background: none;
                color: var(--color-primary);
                cursor: pointer;
            }

            .nav-toggle svg {
                width: 26px;
                height: 26px;
            }

            .nav-toggle:focus-visible,
            .main-nav__close:focus-visible,
            .nav-dropdown__toggle:focus-visible {
                outline: 2px solid var(--color-primary);
                outline-offset: 2px;
            }

            .logo {
                justify-self: center;
            }

            .logo__icon {
                width: 30px;
                height: 30px;
            }

            .logo__name {
                font-size: 16px;
            }

            .logo__tagline {
                font-size: 10px;
            }

            .header-actions {
                justify-self: end;
                gap: 16px;
            }

            .header-actions svg {
                width: 21px;
                height: 21px;
            }

            /* ---- Slide-in drawer ---- */

            .main-nav {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                z-index: 1101;
                width: min(86vw, 340px);
                flex-direction: column;
                flex-wrap: nowrap;
                align-items: stretch;
                justify-content: flex-start;
                gap: 0;
                padding: 0 0 28px;
                background: var(--color-white);
                box-shadow: 8px 0 30px rgba(0, 0, 0, 0.18);
                overflow-y: auto;
                overscroll-behavior: contain;
                transform: translateX(-100%);
                visibility: hidden;
                transition: transform 0.3s ease, visibility 0.3s ease;
            }

            .main-nav.is-open {
                transform: none;
                visibility: visible;
            }

            .main-nav__head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 12px 12px 12px 22px;
                background: var(--color-primary-light);
                border-bottom: 1px solid var(--color-border);
            }

            .main-nav__title {
                font-size: 13px;
                font-weight: 700;
                letter-spacing: 0.14em;
                text-transform: uppercase;
                color: var(--color-primary);
            }

            .main-nav__close {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 42px;
                height: 42px;
                padding: 0;
                border: 0;
                border-radius: var(--radius-sm);
                background: none;
                color: var(--color-primary);
                cursor: pointer;
            }

            .main-nav__close svg {
                width: 22px;
                height: 22px;
            }

            .main-nav>a,
            .main-nav .nav-dropdown>a {
                display: block;
                padding: 15px 22px;
                font-size: 16px;
                font-weight: 600;
                border-bottom: 1px solid var(--color-border);
            }

            .main-nav a.active {
                background: var(--color-primary-light);
                border-bottom-color: var(--color-border);
                box-shadow: inset 3px 0 0 var(--color-accent);
            }

            /* "Shop All" row: link + chevron button that expands categories */
            .main-nav .nav-dropdown {
                display: flex;
                flex-wrap: wrap;
                border-bottom: 1px solid var(--color-border);
            }

            .main-nav .nav-dropdown>a {
                flex: 1 1 auto;
                border-bottom: 0;
            }

            .nav-dropdown>a .caret {
                display: none;
            }

            .nav-dropdown__toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 56px;
                padding: 0;
                border: 0;
                border-left: 1px solid var(--color-border);
                background: none;
                color: var(--color-primary);
                cursor: pointer;
            }

            .nav-dropdown__toggle svg {
                width: 18px;
                height: 18px;
                transition: transform 0.2s ease;
            }

            .nav-dropdown.is-open .nav-dropdown__toggle svg {
                transform: rotate(180deg);
            }

            /* Mega menu becomes a simple category list inside the drawer */
            .mega-menu,
            .nav-dropdown:hover .mega-menu {
                position: static;
                display: none;
                flex-direction: column;
                width: 100%;
                max-height: none;
                transform: none;
                border: 0;
                border-top: 1px solid var(--color-border);
                border-radius: 0;
                box-shadow: none;
                overflow: visible;
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
            }

            .nav-dropdown.is-open .mega-menu {
                display: flex;
            }

            .mega-menu::before {
                display: none;
            }

            .mega-menu__panels {
                display: none;
            }

            .mega-menu__sidebar {
                flex: none;
                max-height: none;
                margin: 0;
                padding: 4px 0 8px;
                list-style: none;
                overflow: visible;
                border: 0;
                background: var(--color-primary-light);
            }

            .mega-menu__sidebar-item>a {
                padding: 12px 22px 12px 36px;
                font-size: 14.5px;
                font-weight: 500;
                border-radius: 0;
            }

            .mega-menu__sidebar-item.is-active>a {
                background: none;
                box-shadow: none;
            }

            /* ---- Dim overlay behind the drawer ---- */

            .nav-overlay {
                display: block;
                position: fixed;
                inset: 0;
                z-index: 1100;
                background: rgba(15, 30, 24, 0.5);
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease, visibility 0.3s ease;
            }

            .nav-overlay.is-open {
                opacity: 1;
                visibility: visible;
            }

            body.nav-open {
                overflow: hidden;
            }

            .announcement-bar__viewport {
                mask-image: linear-gradient(to right, transparent 0, #000 20px, #000 calc(100% - 20px), transparent 100%);
                -webkit-mask-image: linear-gradient(to right, transparent 0, #000 20px, #000 calc(100% - 20px), transparent 100%);
            }
        }

        @media (max-width: 480px) {

            /* Keep the bar from crowding on small phones */
            .icon-track {
                display: none;
            }

            .header-actions {
                gap: 14px;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .main-nav,
            .nav-overlay,
            .nav-dropdown__toggle svg {
                transition: none;
            }
        }
    </style>
</head>

<body>

    <header class="site-header">
        <div class="announcement-bar">
            <div class="announcement-bar__viewport">
                <div class="announcement-bar__track">
                    <?php
                    $announcements = [
                        'COD Available',
                        'Free Shipping Above ₹599',
                        '2% Off On Prepaid Orders',
                        '+91 97110 22343 (Mon&ndash;Sat, 10am&ndash;6pm)',
                    ];
                    // Rendered twice back-to-back so the track can loop seamlessly.
                    for ($i = 0; $i < 2; $i++):
                        foreach ($announcements as $message):
                    ?>
                            <span><?= $message ?></span>
                    <?php endforeach;
                    endfor; ?>
                </div>
            </div>
            <span class="announcement-bar__more" title="More">&#8942;</span>
        </div>

        <div class="main-header">
            <button type="button" class="nav-toggle" data-nav-toggle aria-label="Open menu" aria-controls="site-nav" aria-expanded="false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>

            <a href="<?= BASE_URL ?>index.php" class="logo">
                <?php if ($siteLogo !== ''): ?>
                    <img class="logo__icon" src="<?= htmlspecialchars(BASE_URL . ltrim($siteLogo, '/')) ?>" alt="<?= htmlspecialchars($siteName) ?> logo">
                <?php else: ?>
                    <!-- No logo set in settings yet — falls back to the default mark. -->
                    <svg class="logo__icon" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <defs>
                            <linearGradient id="leafGrad" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0%" stop-color="#d9ac4f" />
                                <stop offset="100%" stop-color="#9c7328" />
                            </linearGradient>
                        </defs>
                        <path d="M32 6c-6 8-10 16-10 24 0 6 4 10 10 10s10-4 10-10c0-8-4-16-10-24z" fill="url(#leafGrad)" />
                        <path d="M12 24c8 0 14 6 16 14-8 2-16-2-20-8-1.5-2.4-1-4.6 4-6z" fill="url(#leafGrad)" />
                        <path d="M52 24c-8 0-14 6-16 14 8 2 16-2 20-8 1.5-2.4 1-4.6-4-6z" fill="url(#leafGrad)" />
                        <circle cx="32" cy="38" r="5" fill="#fdfbf3" stroke="#9c7328" stroke-width="1.5" />
                    </svg>
                <?php endif; ?>
                <span class="logo__name"><?= htmlspecialchars($siteName) ?></span>
                <?php if ($siteTagline !== ''): ?>
                    <span class="logo__tagline">&ndash; <?= htmlspecialchars($siteTagline) ?> &ndash;</span>
                <?php endif; ?>
            </a>

            <nav class="main-nav" id="site-nav" aria-label="Main navigation">
                <div class="main-nav__head">
                    <span class="main-nav__title">Menu</span>
                    <button type="button" class="main-nav__close" data-nav-close aria-label="Close menu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                            <path d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </div>
                <a href="<?= BASE_URL ?>index.php" class="active">Home</a>
                <div class="nav-dropdown">
                    <a href="<?= BASE_URL ?>categories/categories.php" class="has-dropdown">
                        Shop All <span class="caret">&#9662;</span>
                    </a>
                    <button type="button" class="nav-dropdown__toggle" data-nav-dropdown-toggle aria-label="Show categories" aria-expanded="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M6 9l6 6 6-6" />
                        </svg>
                    </button>
                    <div class="mega-menu">
                        <ul class="mega-menu__sidebar">
                            <?php foreach ($browsableCategories as $index => $category): ?>
                                <li class="mega-menu__sidebar-item<?= $index === 0 ? ' is-active' : '' ?>" data-panel-target="mega-panel-<?= (int) $category['id'] ?>">
                                    <a href="<?= BASE_URL ?>products/products.php?category_slug=<?= urlencode($category['slug']) ?>">
                                        <?= htmlspecialchars($category['name']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="mega-menu__panels">
                            <?php foreach ($browsableCategories as $index => $category): ?>
                                <?php $products = $megaMenuProducts[$category['id']]; ?>
                                <div class="mega-menu__panel<?= $index === 0 ? ' is-active' : '' ?>" id="mega-panel-<?= (int) $category['id'] ?>">
                                    <div class="mega-menu__products">
                                        <?php foreach ($products as $product): ?>
                                            <a href="<?= BASE_URL ?>products/product_details.php?slug=<?= urlencode($product['slug']) ?>">
                                                <?= htmlspecialchars($product['title']) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                    <a class="mega-menu__view-all" href="<?= BASE_URL ?>products/products.php?category_slug=<?= urlencode($category['slug']) ?>">
                                        View all in <?= htmlspecialchars($category['name']) ?>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>detux/index.php">Gut Detox</a>
                <a href="<?= BASE_URL ?>consult-veda/index.php">Consult A Vaidya</a>
                <a href="<?= BASE_URL ?>dosha/dosha-test-cta.php">Free Test</a>
                <a href="<?= BASE_URL ?>blog/blog.php">Blog</a>
                <a href="<?= BASE_URL ?>about/">About</a>
                <a href="<?= BASE_URL ?>contact/">Contact</a>
            </nav>

            <div class="header-actions">
                <a href="<?= BASE_URL ?>search.php" title="Search">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path fill="#17483D" d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376C296.3 401.1 253.9 416 208 416 93.1 416 0 322.9 0 208S93.1 0 208 0 416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z" />
                    </svg>
                </a>
                <a href="<?= BASE_URL ?>account/index.php" class="icon-account" title="Account">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path fill="#17483D" d="M224 248a120 120 0 1 0 0-240 120 120 0 1 0 0 240zm-29.7 56C95.8 304 16 383.8 16 482.3 16 498.7 29.3 512 45.7 512l356.6 0c16.4 0 29.7-13.3 29.7-29.7 0-98.5-79.8-178.3-178.3-178.3l-59.4 0z" />
                    </svg>
                </a>
                <a href="<?= BASE_URL ?>account/wishlist.php" class="icon-wishlist" id="js-header-wishlist" title="Wishlist">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path fill="#17483D" d="M462.3 62.6C407.5 15.9 326 24.3 275.7 76.2L256 96.5l-19.7-20.3C186.1 24.3 104.5 15.9 49.7 62.6c-62.8 53.6-66.1 149.8-9.9 207.9l193.5 199.8c12.5 12.9 32.8 12.9 45.3 0l193.5-199.8c56.3-58.1 53-154.3-9.8-207.9z" />
                    </svg>
                    <?php if ($headerWishlistCount > 0): ?>
                        <span class="icon-wishlist__badge" id="js-header-wishlist-badge"><?= $headerWishlistCount > 99 ? '99+' : $headerWishlistCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= BASE_URL ?>cart/index.php" class="icon-cart" title="Cart">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                        <path fill="#17483D" d="M24-16C10.7-16 0-5.3 0 8S10.7 32 24 32l45.3 0c3.9 0 7.2 2.8 7.9 6.6l52.1 286.3c6.2 34.2 36 59.1 70.8 59.1L456 384c13.3 0 24-10.7 24-24s-10.7-24-24-24l-255.9 0c-11.6 0-21.5-8.3-23.6-19.7l-5.1-28.3 303.6 0c30.8 0 57.2-21.9 62.9-52.2L568.9 69.9C572.6 50.2 557.5 32 537.4 32l-412.7 0-.4-2c-4.8-26.6-28-46-55.1-46L24-16zM208 512a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm224 0a48 48 0 1 0 0-96 48 48 0 1 0 0 96z" />
                    </svg>
                    <?php if ($headerCartCount > 0): ?>
                        <span class="icon-cart__badge"><?= $headerCartCount > 99 ? '99+' : $headerCartCount ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
        <div class="nav-overlay" data-nav-overlay></div>
    </header>

    <script>
        (function() {
            // Mobile menu: hamburger opens the drawer, "Shop All" chevron
            // expands the category list. Desktop keeps the hover mega menu.
            var nav = document.getElementById('site-nav');
            var toggle = document.querySelector('[data-nav-toggle]');
            var overlay = document.querySelector('[data-nav-overlay]');

            if (!nav || !toggle || !overlay) {
                return;
            }

            var closeBtn = nav.querySelector('[data-nav-close]');
            var mobile = window.matchMedia('(max-width: 900px)');

            function setOpen(open, moveFocus) {
                nav.classList.toggle('is-open', open);
                overlay.classList.toggle('is-open', open);
                document.body.classList.toggle('nav-open', open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');

                if (moveFocus) {
                    (open ? closeBtn : toggle).focus();
                }
            }

            toggle.addEventListener('click', function() {
                setOpen(!nav.classList.contains('is-open'), true);
            });

            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    setOpen(false, true);
                });
            }

            overlay.addEventListener('click', function() {
                setOpen(false, false);
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && nav.classList.contains('is-open')) {
                    setOpen(false, true);
                }
            });

            // Category list expand / collapse
            nav.querySelectorAll('[data-nav-dropdown-toggle]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var item = btn.closest('.nav-dropdown');
                    var isOpen = item.classList.toggle('is-open');
                    btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            });

            // Leaving mobile width (e.g. rotating a tablet): reset everything.
            function onBreakpoint(event) {
                if (!event.matches) {
                    setOpen(false, false);
                    nav.querySelectorAll('.nav-dropdown.is-open').forEach(function(item) {
                        item.classList.remove('is-open');
                    });
                }
            }

            if (mobile.addEventListener) {
                mobile.addEventListener('change', onBreakpoint);
            } else if (mobile.addListener) {
                mobile.addListener(onBreakpoint);
            }

            // Wishlist badge: product pages dispatch this event on
            // document after a successful toggle, so the header count
            // stays in sync without a full page reload.
            document.addEventListener('wishlist:updated', function(event) {
                var link = document.getElementById('js-header-wishlist');
                if (!link) return;

                var count = event.detail && typeof event.detail.count === 'number' ? event.detail.count : 0;
                var badge = document.getElementById('js-header-wishlist-badge');

                if (count > 0) {
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'icon-wishlist__badge';
                        badge.id = 'js-header-wishlist-badge';
                        link.appendChild(badge);
                    }
                    badge.textContent = count > 99 ? '99+' : String(count);
                } else if (badge) {
                    badge.remove();
                }
            });
        })();
    </script>