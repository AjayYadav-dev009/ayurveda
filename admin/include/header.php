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
                <a href="/admin/index.php" class="<?= $activeNav === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
                <a href="/admin/products/index.php" class="<?= $activeNav === 'products' ? 'active' : '' ?>">Products</a>
                <a href="/admin/categories/index.php" class="<?= $activeNav === 'categories' ? 'active' : '' ?>">Categories</a>
            </nav>
        </aside>
        <main class="main">