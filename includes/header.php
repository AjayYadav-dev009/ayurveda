<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/global.css">
    <style>
        .site-header {
            font-family: Arial, sans-serif;
        }

        /* Announcement bar */
        .announcement-bar {
            background: #000000;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 9px 32px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .announcement-bar__side {
            flex: 1;
            white-space: nowrap;
        }

        .announcement-bar__side--right {
            text-align: right;
        }

        .announcement-bar__center {
            flex: 2;
            text-align: center;
        }

        /* Main header */
        .main-header {
            background: #fdfbf3;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            padding: 12px 32px;
            border-bottom: 1px solid var(--color-border, #eee);
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
            color: #a9852f;
            text-transform: uppercase;
        }

        .logo__tagline {
            font-size: 12px;
            font-weight: 600;
            color: #a9852f;
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
            color: #2b2b2b;
            padding-bottom: 4px;
            border-bottom: 2px solid transparent;
            transition: color 0.15s ease, border-color 0.15s ease;
        }

        .main-nav a:hover {
            color: #a9852f;
        }

        .main-nav a.active {
            color: #a9852f;
            border-bottom-color: #d99a3f;
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
            color: #2b2b2b;
        }

        .header-actions svg {
            width: 22px;
            height: 22px;
        }

        .header-actions .icon-account {
            color: #e08a1e;
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

            .announcement-bar {
                flex-direction: column;
                text-align: center;
                gap: 4px;
            }

            .announcement-bar__side--right {
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <header class="site-header">
        <div class="announcement-bar">
            <div class="announcement-bar__side">Free Shipping Above ₹599</div>
            <div class="announcement-bar__center">2% Off On Prepaid Orders</div>
            <div class="announcement-bar__side announcement-bar__side--right">+91 97110 22343 (Mon&ndash;Sat, 10am&ndash;6pm)</div>
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
                <a href="<?= BASE_URL ?>shop.php" class="has-dropdown">Shop All <span class="caret">&#9662;</span></a>
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