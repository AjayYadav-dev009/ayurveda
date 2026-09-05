<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../function/product-image.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$productId = isset($input['product_id']) ? (int) $input['product_id'] : 0;
$orderedIds = isset($input['image_ids']) && is_array($input['image_ids'])
    ? array_map('intval', $input['image_ids'])
    : [];

if ($productId <= 0 || empty($orderedIds)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'product_id and image_ids are required.']);
    exit;
}

try {
    reorderProductImages($conn, $productId, $orderedIds);
    echo json_encode(['success' => true]);
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Something went wrong while reordering images.']);
}