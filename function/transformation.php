<?php

/**
 * Data access layer for the `transformations` table.
 *
 * Same conventions as function/banner.php and function/promotional_video.php:
 * mysqli prepared statements throughout, STATUS constants shared with the
 * admin UI, and upload*() helpers that return a relative path (or null)
 * rather than handling raw $_FILES anywhere else.
 *
 * Note: the earlier includes/transformation-section.php docblock sketched
 * `p.name AS product_name` for the products join. This project's `products`
 * table actually uses `title` (see ayurveda_db.sql), so every query below
 * joins on `p.title AS product_name` and `p.slug` instead — the column the
 * frontend section reads is unaffected either way.
 */

const TRANSFORMATION_STATUS_INACTIVE = 'Inactive';
const TRANSFORMATION_STATUS_ACTIVE = 'Active';

const TRANSFORMATION_STATUS_OPTIONS = [
    TRANSFORMATION_STATUS_ACTIVE => 'Active',
    TRANSFORMATION_STATUS_INACTIVE => 'Inactive',
];

const TRANSFORMATION_UPLOAD_DIR = __DIR__ . '/../uploads/transformations/';
const TRANSFORMATION_UPLOAD_PATH = 'uploads/transformations/';

/* =============================================================================
 * Uploads
 * ============================================================================= */

/**
 * Handles a before/after image input. $prefix is just used to make the
 * saved filename readable ('before' or 'after'); it has no bearing on
 * validation. Returns the relative path, or null when no new file was
 * chosen (e.g. on edit, keeping the existing image).
 *
 * @throws InvalidArgumentException on a present-but-invalid upload.
 */
function uploadTransformationImage($file, $prefix = 'image')
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('The image failed to upload. Please try again.');
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new InvalidArgumentException('Please upload a JPG, PNG, or WebP image.');
    }

    if (!is_dir(TRANSFORMATION_UPLOAD_DIR)) {
        mkdir(TRANSFORMATION_UPLOAD_DIR, 0755, true);
    }

    $filename = $prefix . '-' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], TRANSFORMATION_UPLOAD_DIR . $filename)) {
        throw new InvalidArgumentException('Could not save the uploaded image. Please try again.');
    }

    return TRANSFORMATION_UPLOAD_PATH . $filename;
}

function getTransformationImageUrl($path)
{
    return $path ? BASE_URL . ltrim($path, '/') : null;
}

/* =============================================================================
 * CRUD
 * ============================================================================= */

function addTransformation($conn, $customerName, $beforeImage, $afterImage, $description, $productId, $duration, $isVerified, $status, $sortOrder)
{
    if (!$beforeImage || !$afterImage) {
        throw new InvalidArgumentException('Please upload both a before and an after image.');
    }
    if (trim((string) $customerName) === '') {
        throw new InvalidArgumentException('Please enter the customer name.');
    }

    $stmt = $conn->prepare(
        'INSERT INTO transformations
            (customer_name, before_image, after_image, description, product_id, duration, is_verified, status, sort_order)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param(
        'sssssiisi',
        $customerName,
        $beforeImage,
        $afterImage,
        $description,
        $productId,
        $duration,
        $isVerified,
        $status,
        $sortOrder
    );
    $stmt->execute();
    $stmt->close();
}

function updateTransformation($conn, $id, $customerName, $beforeImage, $afterImage, $description, $productId, $duration, $isVerified, $status, $sortOrder)
{
    $existing = getTransformationById($conn, $id);
    if (!$existing) {
        throw new InvalidArgumentException('Transformation not found.');
    }

    // Keep the existing images when no new file was uploaded.
    $beforeImage = $beforeImage ?? $existing['before_image'];
    $afterImage = $afterImage ?? $existing['after_image'];

    if (trim((string) $customerName) === '') {
        throw new InvalidArgumentException('Please enter the customer name.');
    }

    $stmt = $conn->prepare(
        'UPDATE transformations
         SET customer_name = ?, before_image = ?, after_image = ?, description = ?,
             product_id = ?, duration = ?, is_verified = ?, status = ?, sort_order = ?
         WHERE id = ?'
    );
    $stmt->bind_param(
        'sssssiisii',
        $customerName,
        $beforeImage,
        $afterImage,
        $description,
        $productId,
        $duration,
        $isVerified,
        $status,
        $sortOrder,
        $id
    );
    $stmt->execute();
    $stmt->close();
}

function deleteTransformation($conn, $id)
{
    $t = getTransformationById($conn, $id);

    $stmt = $conn->prepare('DELETE FROM transformations WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    // Best-effort cleanup; a missing file should never block the delete.
    if ($t) {
        if (!empty($t['before_image'])) {
            @unlink(__DIR__ . '/../' . $t['before_image']);
        }
        if (!empty($t['after_image'])) {
            @unlink(__DIR__ . '/../' . $t['after_image']);
        }
    }
}

function updateTransformationStatus($conn, $id, $status)
{
    $stmt = $conn->prepare('UPDATE transformations SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $status, $id);
    $stmt->execute();
    $stmt->close();
}

/**
 * @param array $order [transformation_id => sort_order]
 */
function reorderTransformations($conn, array $order)
{
    $stmt = $conn->prepare('UPDATE transformations SET sort_order = ? WHERE id = ?');
    foreach ($order as $id => $sortOrder) {
        $id = (int) $id;
        $sortOrder = (int) $sortOrder;
        $stmt->bind_param('ii', $sortOrder, $id);
        $stmt->execute();
    }
    $stmt->close();
}

/**
 * All transformations for the admin list, newest product info joined in.
 */
function getAllTransformations($conn)
{
    $result = $conn->query(
        'SELECT t.*, p.title AS product_name, p.slug AS product_slug
         FROM transformations t
         LEFT JOIN products p ON p.id = t.product_id
         ORDER BY t.sort_order ASC, t.id ASC'
    );
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getTransformationById($conn, $id)
{
    $stmt = $conn->prepare('SELECT * FROM transformations WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

/**
 * For the admin form's product dropdown: id + title only, active products.
 */
function getProductsForSelect($conn)
{
    $result = $conn->query(
        "SELECT id, title FROM products WHERE status = 'Active' ORDER BY title ASC"
    );
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

/**
 * For the homepage "Customer Transformations" section: active
 * transformations only, in display order, with product name/slug joined in.
 * Matches the row shape includes/transformation-section.php expects
 * ('id', 'customer_name', 'before_image', 'after_image', 'description',
 * 'product_name', 'product_url', 'duration', 'is_verified').
 */
function getFeaturedTransformations($conn, $limit = 12)
{
    $stmt = $conn->prepare(
        'SELECT t.id, t.customer_name, t.before_image, t.after_image, t.description,
                t.duration, t.is_verified, p.title AS product_name, p.slug AS product_slug
         FROM transformations t
         LEFT JOIN products p ON p.id = t.product_id
         WHERE t.status = ?
         ORDER BY t.sort_order ASC, t.id ASC
         LIMIT ?'
    );
    $status = TRANSFORMATION_STATUS_ACTIVE;
    $stmt->bind_param('si', $status, $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Shape each row to match what transformation-section.php expects,
    // building product_url from the slug (blank when there's no product,
    // which the section already treats as "not a link").
    foreach ($rows as &$row) {
        $row['product_url'] = !empty($row['product_slug']) ? '/product/' . $row['product_slug'] : '';
        $row['is_verified'] = (bool) $row['is_verified'];
        unset($row['product_slug']);
    }

    return $rows;
}
