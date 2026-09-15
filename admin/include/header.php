<?php
$pageTitle = $pageTitle ?? 'Admin';
$activeNav = $activeNav ?? '';
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
    <style>
        :root {
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
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Manrope', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--mist);
            color: var(--ink);
            margin: 0;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* ---------- Sidebar ---------- */
        .sidebar {
            width: 252px;
            flex-shrink: 0;
            background: var(--paper);
            border-right: 1px solid var(--line);
            padding: 28px 18px;
            display: flex;
            flex-direction: column;
            gap: 28px;
        }

        .sidebar__brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0 0 4px 6px;
            font-size: 17px;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: var(--ink);
        }

        .sidebar__brand-mark {
            display: grid;
            place-items: center;
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: linear-gradient(135deg, var(--leaf) 0%, var(--sky) 100%);
            color: #fff;
            flex-shrink: 0;
        }

        .sidebar__brand-mark svg {
            width: 18px;
            height: 18px;
        }

        .sidebar nav {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .nav__section {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
            margin: 14px 10px 4px;
        }

        .nav__section:first-child {
            margin-top: 0;
        }

        .sidebar nav a {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 12px;
            border-radius: 10px;
            color: var(--ink);
            text-decoration: none;
            font-size: 14.5px;
            font-weight: 600;
            border-left: 3px solid transparent;
            transition: background 0.15s ease, color 0.15s ease;
        }

        .sidebar nav a svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            color: var(--muted);
            transition: color 0.15s ease;
        }

        .sidebar nav a:hover {
            background: var(--sky-tint);
            color: var(--sky);
        }

        .sidebar nav a:hover svg {
            color: var(--sky);
        }

        .sidebar nav a.active {
            background: var(--leaf-tint);
            border-left-color: var(--leaf);
            color: var(--leaf-dark);
        }

        .sidebar nav a.active svg {
            color: var(--leaf);
        }

        .main {
            flex: 1;
        }
    </style>
</head>

<body>
    <div class="layout">
        <aside class="sidebar">
            <p class="sidebar__brand">
                <span class="sidebar__brand-mark">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 4 13V7a1 1 0 0 1 1-1h1a7 7 0 0 1 7 7v7Z"/><path d="M11 20v-7a7 7 0 0 1 7-7h1a1 1 0 0 1 1 1v1a7 7 0 0 1-7 7"/></svg>
                </span>
                Ayurveda Admin
            </p>
            <nav>
                <span class="nav__section">Catalog</span>
                <a href="products/index.php" class="<?= $activeNav === 'products' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                    Products
                </a>
                <a href="categories/index.php" class="<?= $activeNav === 'categories' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16"/><path d="M4 12h10"/><path d="M4 19h6"/></svg>
                    Categories
                </a>
                <span class="nav__section">People</span>
                <a href="users/index.php" class="<?= $activeNav === 'users' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Users
                </a>
                <a href="team-management.php" class="<?= $activeNav === 'team' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Team Management
                </a>
                <span class="nav__section">Storefront</span>
                <a href="banner-management.php" class="<?= $activeNav === 'banners' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 9 6 4-6 4"/><path d="M13 9h6"/><path d="M13 13h6"/></svg>
                    Banner Management
                </a>
                <a href="review/index.php" class="<?= $activeNav === 'banners' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 9 6 4-6 4"/><path d="M13 9h6"/><path d="M13 13h6"/></svg>
                    Review Management
                </a>
                <a href="settings/index.php" class="<?= $activeNav === 'banners' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 9 6 4-6 4"/><path d="M13 9h6"/><path d="M13 13h6"/></svg>
                    Settings Management
                </a>
            </nav>
        </aside>
        <main class="main">