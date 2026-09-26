<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/csrf.php';
require_once __DIR__ . '/../function/wishlist.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

if (!isCustomerLogin()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'login_required']);
    exit;
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'invalid_csrf']);
    exit;
}

$productId = (int) ($_POST['product_id'] ?? 0);

if ($productId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'invalid_product']);
    exit;
}

try {
    $inWishlist = toggleWishlist($conn, $_SESSION['customer_id'], $productId);
    $count = getWishlistCount($conn, $_SESSION['customer_id']);

    echo json_encode(['ok' => true, 'in_wishlist' => $inWishlist, 'count' => $count]);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'server_error']);
}
