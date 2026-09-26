<?php

/**
 * function/wishlist.php
 *
 * Backed by the `wishlist` table: id, user_id, product_id, created_at
 * (unique key on user_id+product_id, FKs to users/products ON DELETE
 * CASCADE — see ayurveda_db.sql). Only depends on mysqli, so it's safe
 * to include from both the storefront and the account area.
 *
 * NOTE on product images: product_images.image is stored as
 * "{product_id}/filename.jpg" (see the seed data), so the browsable URL
 * is built as BASE_URL . 'uploads/products/' . image. If your app
 * already has a shared image-url helper for products elsewhere, swap
 * the body of wishlistProductImageUrl() below to call that instead —
 * it's kept as a separate, distinctly-named function for exactly that
 * reason (won't collide with an existing helper of the same purpose).
 */

if (!function_exists('wishlistProductImageUrl')) {
    function wishlistProductImageUrl($image)
    {
        $image = trim((string) $image);
        if ($image === '' || strpos($image, '..') !== false) {
            return null;
        }

        $image = str_replace('\\', '/', $image);
        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';

        $segments = explode('/', trim($image, '/'));
        $segments = array_map('rawurlencode', $segments);

        return $base . '/uploads/products/' . implode('/', $segments);
    }
}

if (!function_exists('getWishlistForCustomer')) {
    /**
     * All wishlisted products for a customer, newest first, with the
     * fields the account/wishlist.php page needs to render each card.
     *
     * Price handling mirrors products.base_price / base_sale_price:
     * if a sale price is set and lower than the base price, 'price' is
     * the sale price and 'compare_at_price' is the original; otherwise
     * 'compare_at_price' is null.
     */
    function getWishlistForCustomer(mysqli $conn, $userId): array
    {
        $userId = (int) $userId;

        $sql = "SELECT
                    w.id AS wishlist_id,
                    w.created_at AS added_at,
                    p.id AS product_id,
                    p.title,
                    p.slug,
                    p.base_price,
                    p.base_sale_price,
                    p.has_variants,
                    p.stock,
                    p.status,
                    pi.image
                FROM wishlist w
                INNER JOIN products p ON p.id = w.product_id
                LEFT JOIN product_images pi ON pi.id = (
                    SELECT pi2.id FROM product_images pi2
                    WHERE pi2.product_id = p.id
                    ORDER BY pi2.is_primary DESC, pi2.sort_order ASC
                    LIMIT 1
                )
                WHERE w.user_id = ?
                ORDER BY w.created_at DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $items = [];
        foreach ($rows as $row) {
            $basePrice = (float) $row['base_price'];
            $salePrice = $row['base_sale_price'] !== null ? (float) $row['base_sale_price'] : null;

            $hasSale = $salePrice !== null && $salePrice < $basePrice;
            $price = $hasSale ? $salePrice : $basePrice;
            $compareAtPrice = $hasSale ? $basePrice : null;

            $items[] = [
                'wishlist_id'      => (int) $row['wishlist_id'],
                'added_at'         => $row['added_at'],
                'product_id'       => (int) $row['product_id'],
                'title'            => $row['title'],
                'slug'             => $row['slug'],
                'url'              => (defined('BASE_URL') ? BASE_URL : '') . 'products/product_details.php?slug=' . $row['slug'],
                'image_url'        => $row['image'] !== null ? wishlistProductImageUrl($row['image']) : null,
                'price'            => $price,
                'compare_at_price' => $compareAtPrice,
                'has_variants'     => (bool) $row['has_variants'],
                'in_stock'         => $row['status'] === 'Active' && (int) $row['stock'] > 0,
            ];
        }

        return $items;
    }
}

if (!function_exists('getWishlistCount')) {
    /** Lightweight count for a header badge, mirroring getCartTotals()'s item_count. */
    function getWishlistCount(mysqli $conn, $userId): int
    {
        $userId = (int) $userId;
        $stmt = $conn->prepare('SELECT COUNT(*) AS c FROM wishlist WHERE user_id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int) ($row['c'] ?? 0);
    }
}

if (!function_exists('isInWishlist')) {
    function isInWishlist(mysqli $conn, $userId, $productId): bool
    {
        $userId = (int) $userId;
        $productId = (int) $productId;

        $stmt = $conn->prepare('SELECT id FROM wishlist WHERE user_id = ? AND product_id = ? LIMIT 1');
        $stmt->bind_param('ii', $userId, $productId);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }
}

if (!function_exists('addToWishlist')) {
    /**
     * Add a product to a customer's wishlist. Returns true if a new row
     * was inserted, false if it was already on the wishlist (not an
     * error — the end state the caller wants is already true).
     *
     * @throws InvalidArgumentException if the product doesn't exist.
     */
    function addToWishlist(mysqli $conn, $userId, $productId): bool
    {
        $userId = (int) $userId;
        $productId = (int) $productId;

        $check = $conn->prepare('SELECT id FROM products WHERE id = ? LIMIT 1');
        $check->bind_param('i', $productId);
        $check->execute();
        $check->store_result();
        $productExists = $check->num_rows > 0;
        $check->close();

        if (!$productExists) {
            throw new InvalidArgumentException('That product could not be found.');
        }

        // INSERT IGNORE relies on the uq_wishlist(user_id, product_id)
        // unique key to silently no-op on a duplicate add.
        $stmt = $conn->prepare('INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)');
        $stmt->bind_param('ii', $userId, $productId);
        $stmt->execute();
        $inserted = $stmt->affected_rows > 0;
        $stmt->close();

        return $inserted;
    }
}

if (!function_exists('removeFromWishlist')) {
    /** Returns true if a row was removed, false if it wasn't on the wishlist. */
    function removeFromWishlist(mysqli $conn, $userId, $productId): bool
    {
        $userId = (int) $userId;
        $productId = (int) $productId;

        $stmt = $conn->prepare('DELETE FROM wishlist WHERE user_id = ? AND product_id = ?');
        $stmt->bind_param('ii', $userId, $productId);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }
}

if (!function_exists('toggleWishlist')) {
    /**
     * Add if absent, remove if present. Returns the new state: true
     * means the product is now on the wishlist, false means it was just
     * removed.
     *
     * @throws InvalidArgumentException if the product doesn't exist.
     */
    function toggleWishlist(mysqli $conn, $userId, $productId): bool
    {
        if (isInWishlist($conn, $userId, $productId)) {
            removeFromWishlist($conn, $userId, $productId);
            return false;
        }

        addToWishlist($conn, $userId, $productId);
        return true;
    }
}
