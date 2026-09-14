<?php require_once __DIR__ . '/../config/database.php'; ?>

<?php

const REVIEW_STATUSES = ['Pending', 'Active', 'Rejected'];

/**
 * All reviews for the admin list, most recent first, joined with the
 * customer's name/email and the product's title/slug (reviews.product_id
 * is NOT NULL per schema, so this is always a real product).
 *
 * @param mysqli $conn
 * @param array $filters Optional: ['status' => 'Pending'|'Active'|'Rejected']
 * @return array<int, array>
 * @throws Exception
 */
function getAllReviews($conn, array $filters = [])
{
    $where = [];
    $types = '';
    $params = [];

    if (!empty($filters['status']) && in_array($filters['status'], REVIEW_STATUSES, true)) {
        $where[] = 'rv.status = ?';
        $types .= 's';
        $params[] = $filters['status'];
    }

    $whereSql = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

    $sql = "SELECT rv.*, u.name AS customer_name, u.email AS customer_email,
                   p.title AS product_title, p.slug AS product_slug
            FROM reviews rv
            INNER JOIN users u ON u.id = rv.user_id
            INNER JOIN products p ON p.id = rv.product_id
            $whereSql
            ORDER BY rv.created_at DESC";

    if (empty($params)) {
        $result = mysqli_query($conn, $sql);
        if (!$result) {
            throw new Exception("Error fetching reviews: " . mysqli_error($conn));
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error fetching reviews: " . mysqli_error($conn));
    }

    return mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);
}

/**
 * Single review, joined the same way as getAllReviews(), for the admin
 * detail/edit pages.
 *
 * @param mysqli $conn
 * @param int $id
 * @return array|null
 */
function getReviewById($conn, $id)
{
    $id = (int) $id;
    $sql = "SELECT rv.*, u.name AS customer_name, u.email AS customer_email,
                   p.title AS product_title, p.slug AS product_slug
            FROM reviews rv
            INNER JOIN users u ON u.id = rv.user_id
            INNER JOIN products p ON p.id = rv.product_id
            WHERE rv.id = ?
            LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    return $row ?: null;
}

/**
 * Quick moderation action from the list page — approve or reject without
 * opening the full edit form.
 *
 * @param mysqli $conn
 * @param int $id
 * @param string $status One of REVIEW_STATUSES
 * @throws Exception|InvalidArgumentException
 */
function updateReviewStatus($conn, $id, $status)
{
    if (!in_array($status, REVIEW_STATUSES, true)) {
        throw new InvalidArgumentException('Invalid review status.');
    }

    $id = (int) $id;
    $stmt = mysqli_prepare($conn, "UPDATE reviews SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $status, $id);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error updating review status: ' . mysqli_error($conn));
    }
    if (mysqli_stmt_affected_rows($stmt) === 0 && getReviewById($conn, $id) === null) {
        throw new InvalidArgumentException('Review not found.');
    }
}

/**
 * Full edit: an admin correcting an obviously mistyped rating, redacting a
 * problem line from the review text, or changing status, all at once.
 *
 * @param mysqli $conn
 * @param int $id
 * @param array $data ['rating' => int 1-5, 'review' => ?string, 'status' => string]
 * @throws Exception|InvalidArgumentException
 */
function updateReview($conn, $id, array $data)
{
    $id = (int) $id;
    $rating = (int) ($data['rating'] ?? 0);
    $review = isset($data['review']) && trim((string) $data['review']) !== '' ? trim((string) $data['review']) : null;
    $status = $data['status'] ?? 'Pending';

    if ($rating < 1 || $rating > 5) {
        throw new InvalidArgumentException('Rating must be between 1 and 5.');
    }
    if (!in_array($status, REVIEW_STATUSES, true)) {
        throw new InvalidArgumentException('Invalid review status.');
    }

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE reviews SET rating = ?, review = ?, status = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'issi', $rating, $review, $status, $id);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error updating review: ' . mysqli_error($conn));
    }
}

/**
 * @param mysqli $conn
 * @param int $id
 * @throws Exception|InvalidArgumentException
 */
function deleteReview($conn, $id)
{
    $id = (int) $id;
    $stmt = mysqli_prepare($conn, "DELETE FROM reviews WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error deleting review: ' . mysqli_error($conn));
    }
    if (mysqli_stmt_affected_rows($stmt) === 0) {
        throw new InvalidArgumentException('Review not found.');
    }
}

/**
 * Reviews for the homepage "Trusted By" testimonial carousel: Active,
 * highest-rated and verified-purchase first, across all products (this is
 * a site-wide trust section, not tied to any one product page).
 *
 * @param mysqli $conn
 * @param int $limit
 * @return array<int, array>
 * @throws Exception
 */
function getFeaturedReviews($conn, $limit = 9)
{
    $limit = max(1, (int) $limit);

    $sql = "SELECT rv.id, rv.rating, rv.review, rv.verified_purchase, rv.created_at,
                   u.name AS customer_name
            FROM reviews rv
            INNER JOIN users u ON u.id = rv.user_id
            WHERE rv.status = 'Active' AND rv.review IS NOT NULL AND rv.review != ''
            ORDER BY rv.verified_purchase DESC, rv.rating DESC, rv.created_at DESC
            LIMIT ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $limit);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);
}

/**
 * Does this customer already have a review (in any status) for this
 * product? One review per customer per product — write-review.php uses
 * this to block a second submission rather than silently creating a
 * duplicate the admin queue would have to sort out.
 *
 * @param mysqli $conn
 * @param int $userId
 * @param int $productId
 * @return array|null The existing review row, or null if none exists.
 */
function getUserReviewForProduct($conn, $userId, $productId)
{
    $userId = (int) $userId;
    $productId = (int) $productId;

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM reviews WHERE user_id = ? AND product_id = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $productId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    return $row ?: null;
}

/**
 * Whether this customer has a delivered order containing this product —
 * the actual basis for verified_purchase, computed server-side rather than
 * trusting anything from the review form itself.
 *
 * @param mysqli $conn
 * @param int $userId
 * @param int $productId
 * @return bool
 */
function userHasPurchasedProduct($conn, $userId, $productId)
{
    $userId = (int) $userId;
    $productId = (int) $productId;

    $sql = "SELECT 1
            FROM order_items oi
            INNER JOIN orders o ON o.id = oi.order_id
            WHERE o.user_id = ? AND oi.product_id = ? AND o.order_status = 'delivered'
            LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $productId);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt)->num_rows > 0;
}

/**
 * Create a customer's review. Always goes in as 'Pending' — moderation
 * happens in admin/reviews/, never automatically, regardless of rating.
 * verified_purchase is computed here from real order history, never taken
 * from the form.
 *
 * @param mysqli $conn
 * @param int $userId
 * @param int $productId
 * @param int $rating 1-5
 * @param ?string $reviewText
 * @return int The new review's id
 * @throws InvalidArgumentException|Exception
 */
function createReview($conn, $userId, $productId, $rating, $reviewText)
{
    $userId = (int) $userId;
    $productId = (int) $productId;
    $rating = (int) $rating;
    $reviewText = $reviewText !== null && trim((string) $reviewText) !== '' ? trim((string) $reviewText) : null;

    if ($rating < 1 || $rating > 5) {
        throw new InvalidArgumentException('Rating must be between 1 and 5.');
    }

    if (getUserReviewForProduct($conn, $userId, $productId) !== null) {
        throw new InvalidArgumentException('You have already reviewed this product.');
    }

    $verifiedPurchase = userHasPurchasedProduct($conn, $userId, $productId) ? 1 : 0;
    $status = 'Pending';

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO reviews (product_id, user_id, rating, review, status, verified_purchase)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, 'iiissi', $productId, $userId, $rating, $reviewText, $status, $verifiedPurchase);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error saving review: ' . mysqli_error($conn));
    }

    return mysqli_insert_id($conn);
}