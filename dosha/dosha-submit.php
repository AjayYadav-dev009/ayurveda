<?php

/**
 * ajax/dosha-submit.php
 *
 * POST target for the "Take the Ayurvedic Dosha Test" modal form
 * (dosha-test-cta.php). Called via fetch() so the modal never has to
 * reload the page.
 *
 * NOTE ON PATH: this assumes an ajax/ folder at the project root, a level
 * up from config/ and includes/ (same depth as detux/ or account/). If your
 * project routes AJAX endpoints differently, move this file and adjust the
 * three require_once paths below to match — nothing else needs to change.
 *
 * Response shape (always JSON):
 *   success -> { "success": true, "redirect": "https://.../dosha-test.php?token=..." }
 *   failure -> { "success": false, "errors": { field: message, ... } }
 */

require_once __DIR__ . '/../includes/session.php';   // starts the session (needed for CSRF)
require_once __DIR__ . '/../config/database.php';    // $conn
require_once __DIR__ . '/../function/csrf.php';
require_once __DIR__ . '/../function/dosha.php';

if (function_exists('isCustomerLogin')) {
    require_once __DIR__ . '/../function/customer.php';
}

header('Content-Type: application/json');

function doshaRespond(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    doshaRespond(405, ['success' => false, 'errors' => ['form' => 'Invalid request method.']]);
}

if (!validateCSRFToken($_POST['csrf_token'] ?? null)) {
    doshaRespond(403, ['success' => false, 'errors' => ['form' => 'Your session expired. Please refresh the page and try again.']]);
}

$ip = doshaClientIp();

if (isDoshaRateLimited($conn, $ip)) {
    doshaRespond(429, ['success' => false, 'errors' => ['form' => 'Too many attempts. Please try again in a little while.']]);
}

$result = validateDoshaForm($_POST);

if (!empty($result['errors'])) {
    doshaRespond(422, ['success' => false, 'errors' => $result['errors']]);
}

$userId = (function_exists('isCustomerLogin') && isCustomerLogin($conn) && isset($_SESSION['customer_id']))
    ? (int) $_SESSION['customer_id']
    : null;

try {
    $lead = createDoshaLead(
        $conn,
        $result['data'],
        $ip,
        substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        $userId
    );
} catch (Throwable $e) {
    error_log('Dosha lead save failed: ' . $e->getMessage());
    doshaRespond(500, ['success' => false, 'errors' => ['form' => 'Something went wrong on our end. Please try again.']]);
}

// Lets a guest come back to the same in-progress test/result without an account.
setcookie(
    'dosha_session_token',
    $lead['session_token'],
    [
        'expires'  => time() + 60 * 60 * 24 * 30,
        'path'     => '/',
        'secure'   => (defined('APP_ENV') && APP_ENV === 'production'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]
);

doshaRespond(200, [
    'success'  => true,
    'redirect' => (defined('BASE_URL') ? BASE_URL : '/') . 'dosha-test.php?token=' . urlencode($lead['session_token']),
]);
