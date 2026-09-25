<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../includes/header.php';
?>

<main>
    <?php include __DIR__ . '/hero.php'; ?>
    <?php include __DIR__ . '/our-story.php'; ?>
    <?php include __DIR__ . '/our-values.php'; ?>
    <?php include __DIR__ . '/our-impact.php'; ?>
    <?php include __DIR__ . '/about-team.php'; ?>
    <?php include __DIR__ . '/wellness-cta.php'; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>