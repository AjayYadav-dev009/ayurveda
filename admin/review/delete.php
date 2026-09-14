<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../function/review.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$redirectStatus = $_POST['redirect_status'] ?? '';

if ($id <= 0) {
    die('Invalid review id.');
}

$redirectQuery = in_array($redirectStatus, REVIEW_STATUSES, true) ? '&status=' . urlencode($redirectStatus) : '';

$review = getReviewById($conn, $id);
if ($review === null) {
    die('Review not found.');
}

try {
    deleteReview($conn, $id);
    header('Location: index.php?deleted=1' . $redirectQuery);
    exit;
} catch (Exception $e) {
    die('Something went wrong while deleting the review.');
}