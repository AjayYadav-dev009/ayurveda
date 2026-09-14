<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';
?>

<main>
    <?php include __DIR__ . '/hero.php'; ?>
    <?php include __DIR__ . '/information.php'; ?>
    <?php include __DIR__ . '/highlight.php'; ?>
    <?php include __DIR__ . '/timeline.php'; ?>
    <?php include __DIR__ . '/cta.php'; ?>
    <?php include __DIR__ . '/team.php'; ?>
    <?php include __DIR__ . '/faqs.php'; ?>
</main>



<?php include __DIR__ . '/../includes/footer.php'; ?>