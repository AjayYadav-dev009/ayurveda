<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';
?>

<main>
    <?php include __DIR__ . '/hero.php'; ?>
    <?php include __DIR__ . '/condition.php'; ?>
    <?php include __DIR__ . '/why-choose.php'; ?>
    <?php include __DIR__ . '/testimonials.php'; ?>
    <?php include __DIR__ . '/journey.php'; ?>
    <?php include __DIR__ . '/pricing.php'; ?>
    <?php include __DIR__ . '/cta.php'; ?>
    <?php include __DIR__ . '/faqs.php'; ?>

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>