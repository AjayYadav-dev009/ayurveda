<?php
/**
 * write-review.php  (site root)
 *
 * Reviews are now written from the customer's account, and only for
 * products they have bought. This file is kept only so old links and
 * bookmarks keep working: it forwards to account/write-review.php, which
 * checks that the customer is logged in AND has purchased the product.
 *
 * It no longer accepts or saves anything itself.
 */

require_once __DIR__ . '/config/config.php';

$slug   = trim((string) ($_GET['slug'] ?? ''));
$target = BASE_URL . 'account/write-review.php' . ($slug !== '' ? '?slug=' . urlencode($slug) : '');

header('Location: ' . $target);
exit;