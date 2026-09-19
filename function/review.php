<?php

if (!function_exists('REVIEW_ELIGIBLE_ORDER_STATUSES')) {
    /**
     * Order statuses that count as "bought". Change this one list to loosen
     * the rule, e.g. ['shipped', 'delivered'] to allow reviews once an order
     * has shipped.
     */
    function REVIEW_ELIGIBLE_ORDER_STATUSES()
    {
        return ['delivered'];
    }
}

if (!function_exists('REVIEW_MAX_LENGTH')) {
    function REVIEW_MAX_LENGTH()
    {
        return 2000;
    }
}

if (!function_exists('reviewPrepare')) {
    /** prepare() that always throws on failure, in either mysqli error mode. */
    function reviewPrepare(mysqli $conn, $sql)
    {
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException('Database error. Please try again.');
        }
        return $stmt;
    }
}

if (!function_exists('customerHasPurchasedProduct')) {
    /**
     * True if this customer has an eligible (see above) order containing the
     * product. This is the gatekeeper for reviewing.
     */
    function customerHasPurchasedProduct(mysqli $conn, $userId, $productId)
    {
        $userId    = (int) $userId;
        $productId = (int) $productId;
        if ($userId <= 0 || $productId <= 0) {
            return false;
        }

        $statuses = REVIEW_ELIGIBLE_ORDER_STATUSES();
        $marks    = implode(',', array_fill(0, count($statuses), '?'));

        $stmt = reviewPrepare(
            $conn,
            "SELECT 1
               FROM order_items oi
               JOIN orders o ON o.id = oi.order_id
              WHERE o.user_id = ? AND oi.product_id = ? AND o.order_status IN ($marks)
              LIMIT 1"
        );
        $params = array_merge([$userId, $productId], $statuses);
        $stmt->bind_param('ii' . str_repeat('s', count($statuses)), ...$params);
        $stmt->execute();
        $stmt->store_result();
        $found = $stmt->num_rows > 0;
        $stmt->close();
        return $found;
    }
}

if (!function_exists('getReviewableProducts')) {
    /**
     * Every distinct product this customer has bought (eligible orders only),
     * newest purchase first, each with their existing review if any:
     * id, title, slug, last_purchased, review_id, rating, review,
     * review_status, review_updated.
     */
    function getReviewableProducts(mysqli $conn, $userId)
    {
        $userId   = (int) $userId;
        $statuses = REVIEW_ELIGIBLE_ORDER_STATUSES();
        $marks    = implode(',', array_fill(0, count($statuses), '?'));

        $stmt = reviewPrepare(
            $conn,
            "SELECT p.id, p.title, p.slug,
                    MAX(o.created_at) AS last_purchased,
                    r.id         AS review_id,
                    r.rating     AS rating,
                    r.review     AS review,
                    r.status     AS review_status,
                    r.updated_at AS review_updated
               FROM order_items oi
               JOIN orders   o ON o.id = oi.order_id
               JOIN products p ON p.id = oi.product_id
          LEFT JOIN reviews  r ON r.product_id = p.id AND r.user_id = o.user_id
              WHERE o.user_id = ? AND o.order_status IN ($marks)
              GROUP BY p.id, p.title, p.slug, r.id, r.rating, r.review, r.status, r.updated_at
              ORDER BY last_purchased DESC, p.title ASC"
        );
        $params = array_merge([$userId], $statuses);
        $stmt->bind_param('i' . str_repeat('s', count($statuses)), ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
}

if (!function_exists('getProductForReview')) {
    /** Minimal product row (id, title, slug) or null. Looks up by id or slug. */
    function getProductForReview(mysqli $conn, $productId = 0, $slug = '')
    {
        $productId = (int) $productId;
        if ($productId > 0) {
            $stmt = reviewPrepare($conn, 'SELECT id, title, slug FROM products WHERE id = ? LIMIT 1');
            $stmt->bind_param('i', $productId);
        } elseif ($slug !== '') {
            $stmt = reviewPrepare($conn, 'SELECT id, title, slug FROM products WHERE slug = ? LIMIT 1');
            $stmt->bind_param('s', $slug);
        } else {
            return null;
        }
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }
}

if (!function_exists('getCustomerReview')) {
    /** This customer's review of the product, or null. */
    function getCustomerReview(mysqli $conn, $userId, $productId)
    {
        $userId    = (int) $userId;
        $productId = (int) $productId;
        $stmt = reviewPrepare($conn, 'SELECT * FROM reviews WHERE user_id = ? AND product_id = ? LIMIT 1');
        $stmt->bind_param('ii', $userId, $productId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }
}

if (!function_exists('validateReviewInput')) {
    /**
     * Returns [int $rating, string|null $text] or throws
     * InvalidArgumentException. The text is optional; empty becomes null.
     */
    function validateReviewInput($rating, $text)
    {
        if (!is_scalar($rating) || !preg_match('/^[1-5]$/D', trim((string) $rating))) {
            throw new InvalidArgumentException('Please choose a rating from 1 to 5 stars.');
        }

        $text = is_scalar($text) ? trim(str_replace("\r\n", "\n", (string) $text)) : '';
        if (mb_strlen($text) > REVIEW_MAX_LENGTH()) {
            throw new InvalidArgumentException('Your review is too long (maximum ' . REVIEW_MAX_LENGTH() . ' characters).');
        }

        return [(int) $rating, $text === '' ? null : $text];
    }
}

if (!function_exists('saveCustomerReview')) {
    /**
     * Create or edit the customer's review of a product.
     *
     * - Refuses unless the customer has bought the product.
     * - New and edited reviews are saved as 'Pending' so admin approves them
     *   before they appear on the storefront.
     * - verified_purchase is always 1 (that's what the check above proves).
     *
     * @return string 'created' or 'updated'
     * @throws InvalidArgumentException on bad input or a product they haven't bought
     * @throws RuntimeException on database failure
     */
    function saveCustomerReview(mysqli $conn, $userId, $productId, $rating, $text)
    {
        $userId    = (int) $userId;
        $productId = (int) $productId;
        list($rating, $text) = validateReviewInput($rating, $text);

        if (!customerHasPurchasedProduct($conn, $userId, $productId)) {
            throw new InvalidArgumentException('You can only review products you have purchased and received.');
        }

        $existing = getCustomerReview($conn, $userId, $productId);

        if ($existing) {
            $reviewId = (int) $existing['id'];
            $stmt = reviewPrepare(
                $conn,
                "UPDATE reviews SET rating = ?, review = ?, status = 'Pending', verified_purchase = 1
                  WHERE id = ? AND user_id = ?"
            );
            $stmt->bind_param('isii', $rating, $text, $reviewId, $userId);
            $ok = $stmt->execute();
            $stmt->close();
            if (!$ok) {
                throw new RuntimeException('Unable to save your review. Please try again.');
            }
            return 'updated';
        }

        // ON DUPLICATE KEY covers two tabs submitting at the same moment
        // (reviews has UNIQUE (product_id, user_id)).
        $stmt = reviewPrepare(
            $conn,
            "INSERT INTO reviews (product_id, user_id, rating, review, status, verified_purchase)
             VALUES (?, ?, ?, ?, 'Pending', 1)
             ON DUPLICATE KEY UPDATE rating = VALUES(rating), review = VALUES(review),
                                     status = 'Pending', verified_purchase = 1"
        );
        $stmt->bind_param('iiis', $productId, $userId, $rating, $text);
        $ok = $stmt->execute();
        $stmt->close();
        if (!$ok) {
            throw new RuntimeException('Unable to save your review. Please try again.');
        }
        return 'created';
    }
}