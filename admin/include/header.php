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
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>

<body>
    <div class="layout">
        <aside class="sidebar">
            <p class="sidebar__brand">Ayurveda Admin</p>
            <nav>
                <a href="products/index.php" class="<?= $activeNav === 'products' ? 'active' : '' ?>">Products</a>
                <a href="categories/index.php" class="<?= $activeNav === 'categories' ? 'active' : '' ?>">Categories</a>
                <a href="users/index.php" class="<?= $activeNav === 'users' ? 'active' : '' ?>">Users</a>
                <a href="banner-management.php">Banner Management</a>
            </nav>
        </aside>
        <main class="main">