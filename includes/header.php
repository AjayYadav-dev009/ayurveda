<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../function/category.php';
require_once __DIR__ . '/../function/product.php';

// Mega-menu data: root categories are organisational only and never shown.
// getSubcategoriesWithProducts() returns the flat list of real, browsable
// categories (has a parent, Active, has at least one Active product) —
// this is what scales to 100+ categories, since it's just a scrollable
// sidebar list rather than a tree. Each entry's own products are preloaded
// here so the panel-switch on hover needs no extra query.

$browsableCategories = [];
$categoryResult = getSubcategoriesWithProducts($conn);
while ($row = mysqli_fetch_assoc($categoryResult)) {
    $browsableCategories[] = $row;
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

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
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
            flex-direction: column;
            align-items: center;
            text-align: center;
            line-height: 1.1;
        }

        .logo__icon {
            width: 38px;
            height: 38px;
            margin-bottom: 2px;
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

        /* =========================================
                Shop All Mega Menu
                (scrollable category sidebar + product panel,
                 built to handle 100+ categories)
        ========================================= */

        .nav-dropdown {
            position: relative;
        }

        .mega-menu {
            position: absolute;
            top: calc(100% + 12px);
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

        .mega-menu__sidebar-item > a {
            display: block;
            padding: 10px 12px;
            color: var(--color-text);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border-radius: var(--radius-sm);
            transition: background 0.15s ease, color 0.15s ease;
        }

        .mega-menu__sidebar-item > a:hover {
            color: var(--color-primary);
        }

        .mega-menu__sidebar-item.is-active > a {
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
            gap: 4px 20px;
        }

        .mega-menu__products a {
            display: block;
            padding: 6px 0;
            color: var(--color-text-light);
            font-size: 13px;
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
            font-size: 13px;
            font-weight: 700;
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
                Mobile / Tablet
        ========================================= */

        @media (max-width: 900px) {

            .mega-menu {
                position: static;
                flex-direction: column;

                width: 100%;
                max-height: none;

                transform: none;

                box-shadow: none;

                opacity: 1;
                visibility: visible;
                pointer-events: auto;

                display: none;
            }

            .nav-dropdown:hover .mega-menu {
                display: flex;
                transform: none;
            }

            .mega-menu__sidebar {
                flex: none;
                max-height: 220px;
                border-right: none;
                border-bottom: 1px solid var(--color-border);
            }

            .mega-menu__panel.is-active {
                display: flex;
            }
        }

        @media (max-width: 900px) {
            .main-header {
                flex-wrap: wrap;
                row-gap: 12px;
            }

            .main-nav {
                order: 3;
                width: 100%;
                justify-content: center;
                gap: 18px;
            }

            .announcement-bar__viewport {
                mask-image: linear-gradient(to right, transparent 0, #000 20px, #000 calc(100% - 20px), transparent 100%);
                -webkit-mask-image: linear-gradient(to right, transparent 0, #000 20px, #000 calc(100% - 20px), transparent 100%);
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
            <a href="<?= BASE_URL ?>index.php" class="logo">
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
                <span class="logo__name"><?= htmlspecialchars(SITE_NAME) ?></span>
                <span class="logo__tagline">&ndash; ayurveda &ndash;</span>
            </a>

            <nav class="main-nav">
                <a href="<?= BASE_URL ?>index.php" class="active">Home</a>
                <div class="nav-dropdown">
                    <a href="<?= BASE_URL ?>categories.php" class="has-dropdown">
                        Shop All <span class="caret">&#9662;</span>
                    </a>
                    <div class="mega-menu">
                        <ul class="mega-menu__sidebar">
                            <?php foreach ($browsableCategories as $index => $category): ?>
                                <li class="mega-menu__sidebar-item<?= $index === 0 ? ' is-active' : '' ?>" data-panel-target="mega-panel-<?= (int) $category['id'] ?>">
                                    <a href="<?= BASE_URL ?>products.php?category_slug=<?= urlencode($category['slug']) ?>">
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
                                            <a href="<?= BASE_URL ?>products.php?slug=<?= urlencode($product['slug']) ?>">
                                                <?= htmlspecialchars($product['title']) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                    <a class="mega-menu__view-all" href="<?= BASE_URL ?>products.php?category_slug=<?= urlencode($category['slug']) ?>">
                                        View all in <?= htmlspecialchars($category['name']) ?>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <a href="#">Gut Detox</a>
                <a href="#">Consult A Vaidya</a>
                <a href="#">Dosha Test</a>
                <a href="#">Blog</a>
            </nav>

            <div class="header-actions">
                <a href="#" title="Track order">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="7" width="14" height="10" rx="1"></rect>
                        <path d="M15 10h4l3 3v4h-7z"></path>
                        <circle cx="6" cy="19" r="1.7"></circle>
                        <circle cx="18" cy="19" r="1.7"></circle>
                    </svg>
                </a>
                <a href="search.php" title="Search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </a>
                <a href="account/index.php" class="icon-account" title="Account">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="7" r="3.2"></circle>
                        <path d="M4.5 19c0-3 2.3-5.2 5.2-5.2"></path>
                        <path d="M17 8l-3.2 5.4h2.6L14 19l6-7h-2.8z" fill="currentColor" stroke="none"></path>
                    </svg>
                </a>
                <a href="cart/index.php" title="Cart">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 8h12l-1 12H7z"></path>
                        <path d="M9 8V6a3 3 0 0 1 6 0v2"></path>
                    </svg>
                </a>
            </div>
        </div>
    </header>