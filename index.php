<?php
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/config/database.php';
    include __DIR__ . '/includes/header.php';
?>

<main>
    <?php include __DIR__ . '/hero-banner.php'; ?>
    <?php include __DIR__ . '/shopbycategory.php'; ?>
    <?php include __DIR__ . '/veda-cta.php'; ?>
    <?php include __DIR__ . '/producthighlight.php'; ?>
    <?php include __DIR__ . '/bestsaleproduct.php'; ?>
    <?php include __DIR__ . '/ups.php'; ?>
    <?php include __DIR__ . '/trandingproduct.php'; ?>
    <?php include __DIR__ . '/seasonalproduct.php'; ?>
    <?php include __DIR__ . '/dosha-banner.php'; ?>
    <?php include __DIR__ . '/ourteam.php'; ?>
    <?php include __DIR__ . '/review.php'; ?>
</main>



<?php include __DIR__ . '/includes/footer.php'; ?>