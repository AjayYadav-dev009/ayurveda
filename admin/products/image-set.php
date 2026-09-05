<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../function/product-image.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: image.php');
    exit;
}

$productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$imageId = isset($_POST['image_id']) ? (int) $_POST['image_id'] : 0;

if ($productId <= 0 || $imageId <= 0) {
    die('Invalid product or image id.');
}

try {
    setPrimaryProductImage($conn, $productId, $imageId);
    header('Location: image.php?product_id=' . $productId . '&primary_set=1');
    exit;
} catch (InvalidArgumentException $e) {
    die(htmlspecialchars($e->getMessage()));
} catch (Exception $e) {
    die('Something went wrong while updating the primary image.');
}