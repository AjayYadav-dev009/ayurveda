<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../function/review.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$status = $_POST['status'] ?? '';
$redirectStatus = $_POST['redirect_status'] ?? '';

if ($id <= 0 || !in_array($status, REVIEW_STATUSES, true)) {
    die('Invalid request.');
}

$redirectQuery = in_array($redirectStatus, REVIEW_STATUSES, true) ? '&status=' . urlencode($redirectStatus) : '';

try {
    updateReviewStatus($conn, $id, $status);
    $flag = $status === 'Active' ? 'approved' : 'rejected';
    header('Location: index.php?' . $flag . '=1' . $redirectQuery);
    exit;
} catch (InvalidArgumentException $e) {
    die(htmlspecialchars($e->getMessage()));
} catch (Exception $e) {
    die('Something went wrong while updating the review.');
}