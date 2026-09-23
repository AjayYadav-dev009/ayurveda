<?php
require_once __DIR__ . '/../config/database.php';

/**
 * =============================================================================
 * Product Review Functions
 * =============================================================================
 * Everything related to the storefront `reviews` table:
 *   - Customer-facing: rating summary, star distribution, paginated review
 *     list, submission (with duplicate-guard and verified-purchase logic).
 *   - Admin moderation: filterable/paginated list, approve/reject, delete.
 *
 * Table: reviews
 *   id                 bigint UNSIGNED PK
 *   product_id         bigint UNSIGNED  (FK -> products.id)
 *   user_id            bigint UNSIGNED  (FK -> users.id)
 *   rating             tinyint UNSIGNED (1-5)
 *   review             text nullable
 *   status             enum('Pending','Active','Rejected') default 'Pending'
 *   verified_purchase  tinyint(1) default 0
 *   created_at, updated_at
 *   UNIQUE(product_id, user_id)
 *
 * No new table is created and the existing `reviews` table/columns are
 * used as-is. Every function: mysqli prepared statements, $conn first,
 * throws Exception on a genuine DB failure, throws InvalidArgumentException
 * on bad/rejected input, wrapped in function_exists() so this file is safe
 * to require_once from anywhere, matching function/product.php,
 * function/order.php and function/customer.php.
 * =============================================================================
 */

const REVIEW_STATUSES = ['Pending', 'Active', 'Rejected'];

/** Max length (characters) accepted for review text. */
if (!defined('REVIEW_TEXT_MAX_LENGTH')) {
    define('REVIEW_TEXT_MAX_LENGTH', 2000);
}

/**
 * orders.order_status value that counts as a completed purchase for the
 * verified_purchase flag. Matches ORDER_STATUSES in function/order.php
 * ('pending','confirmed','processing','shipped','delivered','cancelled','returned').
 */
if (!defined('REVIEW_VERIFIED_ORDER_STATUS')) {
    define('REVIEW_VERIFIED_ORDER_STATUS', 'delivered');
}

/* -----------------------------------------------------------------------
 * Customer-facing: summary + rating distribution
 * ---------------------------------------------------------------------*/

if (!function_exists('getProductReviewSummary')) {
    /**
     * Average rating + total count of ACTIVE reviews for a product.
     * Division-by-zero safe: returns average 0.0 with count 0 when the
     * product has no active reviews yet.
     *
     * @param mysqli $conn
     * @param int $productId
     * @return array{average: float, count: int}
     * @throws Exception
     */
    function getProductReviewSummary($conn, $productId)
    {
        $productId = (int) $productId;

        $stmt = mysqli_prepare(
            $conn,
            "SELECT COUNT(*) AS total, COALESCE(AVG(rating), 0) AS avg_rating
             FROM reviews WHERE product_id = ? AND status = 'Active'"
        );
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error fetching review summary: " . mysqli_stmt_error($stmt));
        }
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        return [
            'average' => round((float) $row['avg_rating'], 1),
            'count'   => (int) $row['total'],
        ];
    }
}

if (!function_exists('getRatingDistribution')) {
    /**
     * Count + percentage of ACTIVE reviews per star rating. Always
     * returns all five keys (5..1) even when a rating has zero reviews,
     * and is division-by-zero safe: every percent is 0 when the product
     * has no active reviews at all.
     *
     * @param mysqli $conn
     * @param int $productId
     * @return array<int, array{count:int, percent:float}> keyed 5,4,3,2,1
     * @throws Exception
     */
    function getRatingDistribution($conn, $productId)
    {
        $productId = (int) $productId;

        $distribution = [
            5 => ['count' => 0, 'percent' => 0.0],
            4 => ['count' => 0, 'percent' => 0.0],
            3 => ['count' => 0, 'percent' => 0.0],
            2 => ['count' => 0, 'percent' => 0.0],
            1 => ['count' => 0, 'percent' => 0.0],
        ];

        $stmt = mysqli_prepare(
            $conn,
            "SELECT rating, COUNT(*) AS c FROM reviews
             WHERE product_id = ? AND status = 'Active'
             GROUP BY rating"
        );
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error fetching rating distribution: " . mysqli_stmt_error($stmt));
        }
        $result = mysqli_stmt_get_result($stmt);

        $total = 0;
        $counts = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rating = (int) $row['rating'];
            $count = (int) $row['c'];
            if ($rating >= 1 && $rating <= 5) {
                $counts[$rating] = $count;
                $total += $count;
            }
        }

        foreach ($counts as $rating => $count) {
            $distribution[$rating]['count'] = $count;
            $distribution[$rating]['percent'] = $total > 0 ? round(($count / $total) * 100) : 0.0;
        }

        return $distribution;
    }
}

if (!function_exists('getProductReviews')) {
    /**
     * Paginated ACTIVE reviews for a product, newest first, each with the
     * reviewer's display name. Only reviews.* (minus user_id) and
     * users.name are selected — never id, email, phone or password.
     *
     * @param mysqli $conn
     * @param int $productId
     * @param int $page 1-based
     * @param int $perPage
     * @return array{reviews: array, total: int, page: int, pages: int}
     * @throws Exception
     */
    function getProductReviews($conn, $productId, $page = 1, $perPage = 5)
    {
        $productId = (int) $productId;
        $perPage = max(1, (int) $perPage);

        $countStmt = mysqli_prepare(
            $conn,
            "SELECT COUNT(*) AS c FROM reviews WHERE product_id = ? AND status = 'Active'"
        );
        if (!$countStmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($countStmt, 'i', $productId);
        if (!mysqli_stmt_execute($countStmt)) {
            throw new Exception("Error counting reviews: " . mysqli_stmt_error($countStmt));
        }
        $total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['c'];

        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) $page), $pages);
        $offset = ($page - 1) * $perPage;

        $reviews = [];
        if ($total > 0) {
            $stmt = mysqli_prepare(
                $conn,
                "SELECT r.id, r.rating, r.review, r.verified_purchase, r.created_at,
                        u.name AS customer_name
                 FROM reviews r
                 INNER JOIN users u ON u.id = r.user_id
                 WHERE r.product_id = ? AND r.status = 'Active'
                 ORDER BY r.created_at DESC
                 LIMIT ? OFFSET ?"
            );
            if (!$stmt) {
                throw new Exception("Error preparing statement: " . mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmt, 'iii', $productId, $perPage, $offset);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error fetching reviews: " . mysqli_stmt_error($stmt));
            }
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $reviews[] = [
                    'id'                => (int) $row['id'],
                    'customer_name'     => $row['customer_name'],
                    'rating'            => (int) $row['rating'],
                    'review'            => $row['review'],
                    'verified_purchase' => (bool) $row['verified_purchase'],
                    'created_at'        => $row['created_at'],
                ];
            }
        }

        return ['reviews' => $reviews, 'total' => $total, 'page' => $page, 'pages' => $pages];
    }
}

/* -----------------------------------------------------------------------
 * Customer-facing: can-review / duplicate checks + submission
 * ---------------------------------------------------------------------*/

if (!function_exists('hasUserReviewedProduct')) {
    /**
     * @param mysqli $conn
     * @param int $userId
     * @param int $productId
     * @return bool True if this user already has a review (any status)
     *              for this product.
     * @throws Exception
     */
    function hasUserReviewedProduct($conn, $userId, $productId)
    {
        $userId = (int) $userId;
        $productId = (int) $productId;

        $stmt = mysqli_prepare($conn, "SELECT id FROM reviews WHERE product_id = ? AND user_id = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'ii', $productId, $userId);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error checking existing review: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_store_result($stmt);
        return mysqli_stmt_num_rows($stmt) > 0;
    }
}

if (!function_exists('getUserReviewForProduct')) {
    /**
     * The logged-in customer's own review for this product (any status),
     * so the page can show "your review is pending/rejected" instead of
     * just refusing a second submission.
     *
     * @param mysqli $conn
     * @param int $userId
     * @param int $productId
     * @return array|null
     * @throws Exception
     */
    function getUserReviewForProduct($conn, $userId, $productId)
    {
        $userId = (int) $userId;
        $productId = (int) $productId;

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, rating, review, status, verified_purchase, created_at
             FROM reviews WHERE product_id = ? AND user_id = ? LIMIT 1"
        );
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'ii', $productId, $userId);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error fetching review: " . mysqli_stmt_error($stmt));
        }
        return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;
    }
}

if (!function_exists('canUserReviewProduct')) {
    /**
     * @param mysqli $conn
     * @param int $userId
     * @param int $productId
     * @return bool True if this logged-in customer may submit a NEW
     *              review for this product: hasn't already reviewed it
     *              AND has a delivered order containing it. Only
     *              customers who actually bought and received a product
     *              may review it.
     * @throws Exception
     */
    function canUserReviewProduct($conn, $userId, $productId)
    {
        if (hasUserReviewedProduct($conn, $userId, $productId)) {
            return false;
        }
        return customerHasDeliveredOrderForProduct($conn, $userId, $productId);
    }
}

if (!function_exists('getReviewableProductsForCustomer')) {
    /**
     * Every product this customer has a DELIVERED order for and hasn't
     * reviewed yet — the "write a review" list on account/reviews.php.
     *
     * @param mysqli $conn
     * @param int $userId
     * @return array<int, array{id:int, title:string, slug:string}>
     * @throws Exception
     */
    function getReviewableProductsForCustomer($conn, $userId)
    {
        $userId = (int) $userId;
        $deliveredStatus = REVIEW_VERIFIED_ORDER_STATUS;

        $stmt = mysqli_prepare(
            $conn,
            "SELECT DISTINCT p.id, p.title, p.slug
             FROM order_items oi
             INNER JOIN orders o ON o.id = oi.order_id
             INNER JOIN products p ON p.id = oi.product_id
             WHERE o.user_id = ? AND o.order_status = ?
               AND NOT EXISTS (
                   SELECT 1 FROM reviews r WHERE r.product_id = p.id AND r.user_id = o.user_id
               )
             ORDER BY p.title ASC"
        );
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'is', $userId, $deliveredStatus);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error fetching reviewable products: " . mysqli_stmt_error($stmt));
        }
        $result = mysqli_stmt_get_result($stmt);

        $products = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = ['id' => (int) $row['id'], 'title' => $row['title'], 'slug' => $row['slug']];
        }
        return $products;
    }
}

if (!function_exists('getReviewsForCustomer')) {
    /**
     * This customer's own reviews (any status), newest first, with
     * product title/slug attached — the "your reviews" history list on
     * account/reviews.php.
     *
     * @param mysqli $conn
     * @param int $userId
     * @return array<int, array>
     * @throws Exception
     */
    function getReviewsForCustomer($conn, $userId)
    {
        $userId = (int) $userId;

        $stmt = mysqli_prepare(
            $conn,
            "SELECT r.id, r.rating, r.review, r.status, r.verified_purchase, r.created_at,
                    p.id AS product_id, p.title AS product_title, p.slug AS product_slug
             FROM reviews r
             INNER JOIN products p ON p.id = r.product_id
             WHERE r.user_id = ?
             ORDER BY r.created_at DESC"
        );
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error fetching customer reviews: " . mysqli_stmt_error($stmt));
        }
        $result = mysqli_stmt_get_result($stmt);

        $reviews = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $reviews[] = [
                'id'                => (int) $row['id'],
                'product_id'        => (int) $row['product_id'],
                'product_title'     => $row['product_title'],
                'product_slug'      => $row['product_slug'],
                'rating'            => (int) $row['rating'],
                'review'            => $row['review'],
                'status'            => $row['status'],
                'verified_purchase' => (bool) $row['verified_purchase'],
                'created_at'        => $row['created_at'],
            ];
        }
        return $reviews;
    }
}

if (!function_exists('customerHasDeliveredOrderForProduct')) {
    /**
     * Whether this customer has a DELIVERED order containing this
     * product — the basis for the verified_purchase flag. Built entirely
     * from the existing orders/order_items relationship (see
     * function/order.php's ORDER_STATUSES); no separate purchase-tracking
     * table is created.
     *
     * @param mysqli $conn
     * @param int $userId
     * @param int $productId
     * @return bool
     * @throws Exception
     */
    function customerHasDeliveredOrderForProduct($conn, $userId, $productId)
    {
        $userId = (int) $userId;
        $productId = (int) $productId;

        $stmt = mysqli_prepare(
            $conn,
            "SELECT 1
             FROM order_items oi
             INNER JOIN orders o ON o.id = oi.order_id
             WHERE o.user_id = ?
               AND oi.product_id = ?
               AND o.order_status = ?
             LIMIT 1"
        );
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        $deliveredStatus = REVIEW_VERIFIED_ORDER_STATUS;
        mysqli_stmt_bind_param($stmt, 'iis', $userId, $productId, $deliveredStatus);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error checking purchase history: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_store_result($stmt);
        return mysqli_stmt_num_rows($stmt) > 0;
    }
}

if (!function_exists('submitProductReview')) {
    /**
     * Insert a new review as a logged-in customer. Every value that must
     * never come from the client — user_id, status, verified_purchase —
     * is computed here from the authenticated session / server-side
     * lookups. The caller must likewise pass $productId from the
     * server-resolved product context (e.g. the slug -> id lookup the
     * page already did), never from $_POST['product_id'].
     *
     * @param mysqli $conn
     * @param int $userId      From $_SESSION['customer_id'].
     * @param int $productId   Server-resolved product id.
     * @param mixed $ratingRaw Raw submitted rating, validated here (must
     *                         be exactly an integer 1-5).
     * @param mixed $reviewRaw Raw submitted review text, validated/
     *                         trimmed here (optional).
     * @return int Newly created review id.
     * @throws InvalidArgumentException On bad rating/text or a duplicate review.
     * @throws Exception On a database error.
     */
    function submitProductReview($conn, $userId, $productId, $ratingRaw, $reviewRaw)
    {
        $userId = (int) $userId;
        $productId = (int) $productId;

        if ($userId <= 0) {
            throw new InvalidArgumentException('You must be logged in to write a review.');
        }

        if (!productExists($conn, $productId)) {
            throw new InvalidArgumentException('This product could not be found.');
        }

        // Must be exactly an integer 1-5. FILTER_VALIDATE_INT rejects
        // "4.5", "abc" and "" the same way it rejects out-of-range ints,
        // so this one check covers every case the spec calls out (0, 6,
        // -1, 4.5, abc all fail it).
        $rating = filter_var($ratingRaw, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 5],
        ]);
        if ($rating === false) {
            throw new InvalidArgumentException('Please select a rating from 1 to 5 stars.');
        }

        $reviewText = trim((string) $reviewRaw);
        if ($reviewText === '') {
            $reviewText = null;
        } elseif (mb_strlen($reviewText) > REVIEW_TEXT_MAX_LENGTH) {
            throw new InvalidArgumentException('Your review is too long (max ' . REVIEW_TEXT_MAX_LENGTH . ' characters).');
        }

        // Application-level duplicate check first (for a clean error
        // message); the UNIQUE(product_id, user_id) constraint is still
        // the source of truth and is handled below in case of a race.
        if (hasUserReviewedProduct($conn, $userId, $productId)) {
            throw new InvalidArgumentException('You have already reviewed this product.');
        }

        // Only customers who actually bought and received this product
        // may review it at all — this is the same check used for the
        // verified_purchase flag, so every review that gets inserted is
        // verified_purchase = 1 by construction.
        if (!customerHasDeliveredOrderForProduct($conn, $userId, $productId)) {
            throw new InvalidArgumentException('You can only review products you have purchased and received.');
        }

        $verifiedPurchase = 1;

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO reviews (product_id, user_id, rating, review, status, verified_purchase, created_at, updated_at)
             VALUES (?, ?, ?, ?, 'Pending', ?, NOW(), NOW())"
        );
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'iiisi', $productId, $userId, $rating, $reviewText, $verifiedPurchase);

        if (!mysqli_stmt_execute($stmt)) {
            if (mysqli_errno($conn) === 1062) {
                throw new InvalidArgumentException('You have already reviewed this product.');
            }
            throw new Exception('Error submitting review: ' . mysqli_stmt_error($stmt));
        }

        return (int) mysqli_insert_id($conn);
    }
}

/* -----------------------------------------------------------------------
 * Admin moderation
 * ---------------------------------------------------------------------*/

if (!function_exists('getReviewsForAdmin')) {
    /**
     * Paginated, filterable review list for the admin review management
     * page.
     *
     * @param mysqli $conn
     * @param array $filters Optional: ['status' => ..., 'product_id' => ..., 'rating' => ...]
     * @param int $page
     * @param int $perPage
     * @return array{reviews: array, total: int, page: int, pages: int}
     * @throws Exception
     */
    function getReviewsForAdmin($conn, array $filters = [], $page = 1, $perPage = 20)
    {
        $where = [];
        $types = '';
        $params = [];

        if (!empty($filters['status']) && in_array($filters['status'], REVIEW_STATUSES, true)) {
            $where[] = 'r.status = ?';
            $types .= 's';
            $params[] = $filters['status'];
        }
        if (!empty($filters['product_id'])) {
            $where[] = 'r.product_id = ?';
            $types .= 'i';
            $params[] = (int) $filters['product_id'];
        }
        if (!empty($filters['rating'])) {
            $where[] = 'r.rating = ?';
            $types .= 'i';
            $params[] = (int) $filters['rating'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = mysqli_prepare($conn, "SELECT COUNT(*) AS c FROM reviews r $whereSql");
        if (!$countStmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        if ($params) {
            mysqli_stmt_bind_param($countStmt, $types, ...$params);
        }
        if (!mysqli_stmt_execute($countStmt)) {
            throw new Exception("Error counting reviews: " . mysqli_stmt_error($countStmt));
        }
        $total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['c'];

        $perPage = max(1, (int) $perPage);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) $page), $pages);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT r.id, r.rating, r.review, r.status, r.verified_purchase, r.created_at,
                       u.name AS customer_name,
                       p.title AS product_title
                FROM reviews r
                LEFT JOIN users u ON u.id = r.user_id
                LEFT JOIN products p ON p.id = r.product_id
                $whereSql
                ORDER BY r.created_at DESC
                LIMIT $perPage OFFSET $offset";

        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        if ($params) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error fetching reviews: " . mysqli_stmt_error($stmt));
        }
        $result = mysqli_stmt_get_result($stmt);

        $reviews = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $reviews[] = [
                'id'                => (int) $row['id'],
                'customer_name'     => $row['customer_name'] ?? '(deleted user)',
                'product_title'     => $row['product_title'] ?? '(deleted product)',
                'rating'            => (int) $row['rating'],
                'review'            => $row['review'],
                'status'            => $row['status'],
                'verified_purchase' => (bool) $row['verified_purchase'],
                'created_at'        => $row['created_at'],
            ];
        }

        return ['reviews' => $reviews, 'total' => $total, 'page' => $page, 'pages' => $pages];
    }
}

if (!function_exists('getReviewStatusCounts')) {
    /**
     * Count of reviews per status, for the filter tab badges.
     *
     * @param mysqli $conn
     * @return array{Pending:int, Active:int, Rejected:int}
     * @throws Exception
     */
    function getReviewStatusCounts($conn)
    {
        $counts = ['Pending' => 0, 'Active' => 0, 'Rejected' => 0];

        $result = mysqli_query($conn, "SELECT status, COUNT(*) AS c FROM reviews GROUP BY status");
        if (!$result) {
            throw new Exception("Error counting reviews by status: " . mysqli_error($conn));
        }
        while ($row = mysqli_fetch_assoc($result)) {
            if (isset($counts[$row['status']])) {
                $counts[$row['status']] = (int) $row['c'];
            }
        }

        return $counts;
    }
}

if (!function_exists('getReviewByIdForAdmin')) {
    /**
     * One review with its customer/product context, for the admin "view"
     * action. Includes the customer's email (admin-only context — never
     * exposed on the storefront) but nothing beyond name/email/id.
     *
     * @param mysqli $conn
     * @param int $id
     * @return array|null
     * @throws Exception
     */
    function getReviewByIdForAdmin($conn, $id)
    {
        $id = (int) $id;
        $stmt = mysqli_prepare(
            $conn,
            "SELECT r.id, r.product_id, r.user_id, r.rating, r.review, r.status,
                    r.verified_purchase, r.created_at, r.updated_at,
                    u.name AS customer_name, u.email AS customer_email,
                    p.title AS product_title
             FROM reviews r
             LEFT JOIN users u ON u.id = r.user_id
             LEFT JOIN products p ON p.id = r.product_id
             WHERE r.id = ?
             LIMIT 1"
        );
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error fetching review: " . mysqli_stmt_error($stmt));
        }
        return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;
    }
}

if (!function_exists('updateReviewStatusForAdmin')) {
    /**
     * Approve/reject (or otherwise change) a review's status.
     * Pending -> Active is "approve"; Pending -> Rejected is "reject".
     *
     * @param mysqli $conn
     * @param int $id
     * @param string $status One of REVIEW_STATUSES.
     * @return bool True if a row was actually changed.
     * @throws InvalidArgumentException On an invalid status.
     * @throws Exception On a database error.
     */
    function updateReviewStatusForAdmin($conn, $id, $status)
    {
        if (!in_array($status, REVIEW_STATUSES, true)) {
            throw new InvalidArgumentException('Invalid review status.');
        }

        $id = (int) $id;
        $stmt = mysqli_prepare($conn, "UPDATE reviews SET status = ?, updated_at = NOW() WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'si', $status, $id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error updating review: " . mysqli_stmt_error($stmt));
        }

        return mysqli_stmt_affected_rows($stmt) > 0;
    }
}

if (!function_exists('deleteReviewForAdmin')) {
    /**
     * @param mysqli $conn
     * @param int $id
     * @return bool True if a row was actually deleted.
     * @throws Exception
     */
    function deleteReviewForAdmin($conn, $id)
    {
        $id = (int) $id;
        $stmt = mysqli_prepare($conn, "DELETE FROM reviews WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error deleting review: " . mysqli_stmt_error($stmt));
        }

        return mysqli_stmt_affected_rows($stmt) > 0;
    }
}

if (!function_exists('getProductsForReviewFilter')) {
    /**
     * Lightweight id/title list for the admin filter's Product dropdown
     * — only products that actually have at least one review, so the
     * dropdown doesn't get cluttered with the whole catalog.
     *
     * @param mysqli $conn
     * @return array<int, array{id:int, title:string}>
     * @throws Exception
     */
    function getProductsForReviewFilter($conn)
    {
        $result = mysqli_query(
            $conn,
            "SELECT DISTINCT p.id, p.title
             FROM products p
             INNER JOIN reviews r ON r.product_id = p.id
             ORDER BY p.title ASC"
        );
        if (!$result) {
            throw new Exception("Error fetching products for filter: " . mysqli_error($conn));
        }

        $products = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = ['id' => (int) $row['id'], 'title' => $row['title']];
        }
        return $products;
    }
}