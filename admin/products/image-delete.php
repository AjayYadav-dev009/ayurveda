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

$image = getProductImageById($conn, $imageId);
if ($image === null || (int) $image['product_id'] !== $productId) {
    die('Image not found for this product.');
}

try {
    deleteProductImage($conn, $imageId);
    header('Location: image.php?product_id=' . $productId . '&deleted=1');
    exit;
} catch (Exception $e) {
    die('Something went wrong while deleting the image.');
}