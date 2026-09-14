<?php

require_once __DIR__ . '/../config/config.php';
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

$headerCartCount = 0;
if (isCustomerLogin()) {
    try {
        $headerCartTotals = getCartTotals($conn, $_SESSION['customer_id']);
        $headerCartCount = (int) $headerCartTotals['item_count'];
    } catch (Exception $e) {
        $headerCartCount = 0;
    }
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
                                            <a href="<?= BASE_URL ?>product_details.php?slug=<?= urlencode($product['slug']) ?>">
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
                <a href="detux/index.php">Gut Detox</a>
                <a href="#">Consult A Vaidya</a>
                <a href="#">Dosha Test</a>
                <a href="#">Blog</a>
            </nav>

            <div class="header-actions">
                <a href="#" title="Track order">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                        <path fill="#17483D" d="M64 96c0-35.3 28.7-64 64-64l288 0c35.3 0 64 28.7 64 64l0 32 50.7 0c17 0 33.3 6.7 45.3 18.7L621.3 192c12 12 18.7 28.3 18.7 45.3L640 384c0 35.3-28.7 64-64 64l-3.3 0c-10.4 36.9-44.4 64-84.7 64s-74.2-27.1-84.7-64l-102.6 0c-10.4 36.9-44.4 64-84.7 64s-74.2-27.1-84.7-64l-3.3 0c-35.3 0-64-28.7-64-64l0-48-40 0c-13.3 0-24-10.7-24-24s10.7-24 24-24l112 0c13.3 0 24-10.7 24-24s-10.7-24-24-24L24 240c-13.3 0-24-10.7-24-24s10.7-24 24-24l176 0c13.3 0 24-10.7 24-24s-10.7-24-24-24L24 144c-13.3 0-24-10.7-24-24S10.7 96 24 96l40 0zM576 288l0-50.7-45.3-45.3-50.7 0 0 96 96 0zM256 424a40 40 0 1 0 -80 0 40 40 0 1 0 80 0zm232 40a40 40 0 1 0 0-80 40 40 0 1 0 0 80z" />
                    </svg>
                </a>
                <a href="search.php" title="Search">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path fill="#17483D" d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376C296.3 401.1 253.9 416 208 416 93.1 416 0 322.9 0 208S93.1 0 208 0 416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z" />
                    </svg>
                </a>
                <a href="account/index.php" class="icon-account" title="Account">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path fill="#17483D" d="M224 248a120 120 0 1 0 0-240 120 120 0 1 0 0 240zm-29.7 56C95.8 304 16 383.8 16 482.3 16 498.7 29.3 512 45.7 512l356.6 0c16.4 0 29.7-13.3 29.7-29.7 0-98.5-79.8-178.3-178.3-178.3l-59.4 0z" />
                    </svg>
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
    </header>