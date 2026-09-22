<?php
require_once __DIR__ . '/../config/database.php';

/**
 * =============================================================================
 * Product Image Functions
 * =============================================================================
 * Handles everything related to product_images: validating uploaded files,
 * generating safe filenames, storing files on disk, and all CRUD + ordering
 * operations against the `product_images` table.
 *
 * Table: product_images
 *   id          bigint UNSIGNED PK
 *   product_id  bigint UNSIGNED  (FK -> products.id, ON DELETE CASCADE)
 *   image       varchar(500)     (relative path stored on disk, e.g. products/12/abc.webp)
 *   alt_text    varchar(255)     nullable
 *   is_primary  tinyint(1)       default 0
 *   sort_order  int UNSIGNED     default 0
 *   created_at  timestamp
 * =============================================================================
 */

// Absolute path on disk where product images are physically stored.
if (!defined('PRODUCT_IMAGE_UPLOAD_DIR')) {
    define('PRODUCT_IMAGE_UPLOAD_DIR', __DIR__ . '/../uploads/products/');
}

// Public web path used to build <img src="..."> URLs. Adjust to match your
// site's document root layout if uploads/ is served from somewhere else.
if (!defined('PRODUCT_IMAGE_PUBLIC_PATH')) {
    define('PRODUCT_IMAGE_PUBLIC_PATH', '/uploads/products/');
}

if (!defined('PRODUCT_IMAGE_MAX_BYTES')) {
    define('PRODUCT_IMAGE_MAX_BYTES', 5 * 1024 * 1024); // 5 MB
}

/**
 * Validate a single uploaded file from $_FILES before it's touched.
 *
 * @param array $file One entry from $_FILES, e.g. $_FILES['image']
 * @return array{valid: bool, errors: string[], mime: ?string, extension: ?string}
 */
function validateProductImage($file)
{
    $errors = [];
    $allowedMimeToExt = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    if (!is_array($file) || !isset($file['error'])) {
        return ['valid' => false, 'errors' => ['No file was uploaded.'], 'mime' => null, 'extension' => null];
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['valid' => false, 'errors' => ['No file was selected.'], 'mime' => null, 'extension' => null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE   => 'The file exceeds the server upload_max_filesize limit.',
            UPLOAD_ERR_FORM_SIZE  => 'The file exceeds the form MAX_FILE_SIZE limit.',
            UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary upload folder on the server.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write the file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
        ];
        $errors[] = $uploadErrors[$file['error']] ?? 'Unknown upload error.';
        return ['valid' => false, 'errors' => $errors, 'mime' => null, 'extension' => null];
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        return ['valid' => false, 'errors' => ['Invalid upload (possible attack).'], 'mime' => null, 'extension' => null];
    }

    if ($file['size'] <= 0) {
        $errors[] = 'The uploaded file is empty.';
    }

    if ($file['size'] > PRODUCT_IMAGE_MAX_BYTES) {
        $errors[] = 'The file is larger than ' . (PRODUCT_IMAGE_MAX_BYTES / 1024 / 1024) . 'MB.';
    }

    // Detect the real MIME type from file contents, never trust the
    // client-supplied Content-Type header.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!isset($allowedMimeToExt[$mime])) {
        $errors[] = 'Unsupported file type. Allowed types: JPG, PNG, WEBP, GIF.';
        $mime = null;
    }

    // Confirm it's actually a readable image (guards against a renamed
    // non-image file that happens to pass the MIME sniff).
    if ($mime !== null && @getimagesize($file['tmp_name']) === false) {
        $errors[] = 'The file does not appear to be a valid image.';
        $mime = null;
    }

    $extension = $mime !== null ? $allowedMimeToExt[$mime] : null;

    return [
        'valid'     => empty($errors),
        'errors'    => $errors,
        'mime'      => $mime,
        'extension' => $extension,
    ];
}

/**
 * Build a unique, filesystem-safe filename for a product image.
 *
 * @param int $productId
 * @param string $originalName Original client filename (used only for a readable slug)
 * @param string $extension File extension without the dot, e.g. 'jpg'
 * @return string e.g. "asgandha-pill-68b9f2a1c4e3d.jpg"
 */
function generateProductImageName($productId, $originalName, $extension)
{
    $base = pathinfo($originalName, PATHINFO_FILENAME);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $base), '-'));

    if ($slug === '') {
        $slug = 'product-' . (int) $productId;
    }

    // Keep the slug short so filenames don't grow unbounded.
    $slug = substr($slug, 0, 60);

    $unique = bin2hex(random_bytes(6));

    return $slug . '-' . $unique . '.' . $extension;
}

/**
 * Move a validated uploaded file into the product images directory.
 * Creates a per-product subfolder: uploads/products/{product_id}/{filename}
 *
 * @param array $file One entry from $_FILES, already checked with validateProductImage()
 * @param int $productId
 * @return string The relative path stored in the DB, e.g. "12/asgandha-pill-68b9f2a1c4e3d.jpg"
 * @throws InvalidArgumentException If the file fails validation
 * @throws RuntimeException If the file can't be moved to disk
 */
function uploadProductImage($file, $productId)
{
    $validation = validateProductImage($file);

    if (!$validation['valid']) {
        throw new InvalidArgumentException(implode(' ', $validation['errors']));
    }

    $filename = generateProductImageName($productId, $file['name'], $validation['extension']);
    $productDir = rtrim(PRODUCT_IMAGE_UPLOAD_DIR, '/') . '/' . (int) $productId;

    if (!is_dir($productDir) && !mkdir($productDir, 0755, true) && !is_dir($productDir)) {
        throw new RuntimeException('Could not create the upload directory.');
    }

    $destination = $productDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Could not move the uploaded file to its destination.');
    }

    chmod($destination, 0644);

    // Relative path stored in the DB (product_id folder + filename).
    return (int) $productId . '/' . $filename;
}

/**
 * Insert a new product_images row.
 *
 * If $isPrimary is true, any existing primary image for the product is
 * unset first so there is only ever one primary image per product. If this
 * is the product's first image, it is made primary automatically.
 *
 * @param mysqli $conn
 * @param int $productId
 * @param string $imagePath Relative path as returned by uploadProductImage()
 * @param ?string $altText
 * @param bool $isPrimary
 * @param ?int $sortOrder Explicit sort order; defaults to end of the list
 * @return int The new image's id
 * @throws Exception
 */
function addProductImage($conn, $productId, $imagePath, $altText = null, $isPrimary = false, $sortOrder = null)
{
    $conn->begin_transaction();

    try {
        $existingCount = 0;
        $countStmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM product_images WHERE product_id = ?");
        if (!$countStmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        $countStmt->bind_param('i', $productId);
        $countStmt->execute();
        $countRow = $countStmt->get_result()->fetch_assoc();
        $existingCount = (int) $countRow['cnt'];

        // First image for a product is always primary, regardless of what was passed in.
        if ($existingCount === 0) {
            $isPrimary = true;
        }

        if ($sortOrder === null) {
            $sortOrder = $existingCount;
        }

        if ($isPrimary) {
            $unsetStmt = $conn->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
            if (!$unsetStmt) {
                throw new Exception("Error preparing statement: " . mysqli_error($conn));
            }
            $unsetStmt->bind_param('i', $productId);
            $unsetStmt->execute();
        }

        $insertStmt = $conn->prepare(
            "INSERT INTO product_images (product_id, image, alt_text, is_primary, sort_order)
             VALUES (?, ?, ?, ?, ?)"
        );
        if (!$insertStmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }

        $isPrimaryInt = $isPrimary ? 1 : 0;
        $insertStmt->bind_param(
            'issii',
            $productId,
            $imagePath,
            $altText,
            $isPrimaryInt,
            $sortOrder
        );
        $insertStmt->execute();

        $newId = $conn->insert_id;

        $conn->commit();

        return (int) $newId;
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

/**
 * Get all images for a product, ordered for display.
 *
 * @param mysqli $conn
 * @param int $productId
 * @return array<int, array>
 * @throws Exception
 */
function getProductImages($conn, $productId)
{
    $sql = "SELECT id, product_id, image, alt_text, is_primary, sort_order, created_at
            FROM product_images
            WHERE product_id = ?
            ORDER BY is_primary DESC, sort_order ASC, id ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    $stmt->bind_param('i', $productId);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get a single product image by id.
 *
 * @param mysqli $conn
 * @param int $imageId
 * @return ?array
 * @throws Exception
 */
function getProductImageById($conn, $imageId)
{
    $sql = "SELECT id, product_id, image, alt_text, is_primary, sort_order, created_at
            FROM product_images
            WHERE id = ?
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    $stmt->bind_param('i', $imageId);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();

    return $result ?: null;
}

/**
 * Delete a product image: removes the DB row and the file on disk.
 * If the deleted image was the primary image, the next image (by sort_order)
 * is automatically promoted to primary so a product is never left without one.
 *
 * @param mysqli $conn
 * @param int $imageId
 * @return bool True on success
 * @throws Exception If the image doesn't exist or the delete fails
 */
function deleteProductImage($conn, $imageId)
{
    $image = getProductImageById($conn, $imageId);

    if ($image === null) {
        throw new Exception('Image not found.');
    }

    $conn->begin_transaction();

    try {
        $deleteStmt = $conn->prepare("DELETE FROM product_images WHERE id = ?");
        if (!$deleteStmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        $deleteStmt->bind_param('i', $imageId);
        $deleteStmt->execute();

        if ((int) $image['is_primary'] === 1) {
            $nextStmt = $conn->prepare(
                "SELECT id FROM product_images
                 WHERE product_id = ?
                 ORDER BY sort_order ASC, id ASC
                 LIMIT 1"
            );
            if (!$nextStmt) {
                throw new Exception("Error preparing statement: " . mysqli_error($conn));
            }
            $nextStmt->bind_param('i', $image['product_id']);
            $nextStmt->execute();
            $next = $nextStmt->get_result()->fetch_assoc();

            if ($next) {
                $promoteStmt = $conn->prepare("UPDATE product_images SET is_primary = 1 WHERE id = ?");
                if (!$promoteStmt) {
                    throw new Exception("Error preparing statement: " . mysqli_error($conn));
                }
                $promoteStmt->bind_param('i', $next['id']);
                $promoteStmt->execute();
            }
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

    // Only remove the file from disk once the DB change is safely committed.
    $filePath = rtrim(PRODUCT_IMAGE_UPLOAD_DIR, '/') . '/' . ltrim($image['image'], '/');
    if (is_file($filePath)) {
        @unlink($filePath);
    }

    return true;
}

/**
 * Update the sort_order of a single image.
 *
 * @param mysqli $conn
 * @param int $imageId
 * @param int $sortOrder
 * @return bool
 * @throws Exception
 */
function updateProductImageOrder($conn, $imageId, $sortOrder)
{
    $stmt = $conn->prepare("UPDATE product_images SET sort_order = ? WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    $sortOrder = (int) $sortOrder;
    $stmt->bind_param('ii', $sortOrder, $imageId);
    $stmt->execute();

    return $stmt->affected_rows > 0;
}

/**
 * Persist a full new order for a product's images in one go.
 * Pass the image ids in the exact order you want them displayed;
 * sort_order 0, 1, 2... is assigned accordingly.
 *
 * @param mysqli $conn
 * @param int $productId
 * @param int[] $orderedImageIds Image ids in their new display order
 * @return bool
 * @throws InvalidArgumentException If an id doesn't belong to the product
 * @throws Exception
 */
function reorderProductImages($conn, $productId, array $orderedImageIds)
{
    if (empty($orderedImageIds)) {
        return true;
    }

    // Make sure every id actually belongs to this product before touching anything.
    $placeholders = implode(',', array_fill(0, count($orderedImageIds), '?'));
    $types = 'i' . str_repeat('i', count($orderedImageIds));

    $checkStmt = $conn->prepare(
        "SELECT id FROM product_images WHERE product_id = ? AND id IN ($placeholders)"
    );
    if (!$checkStmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    $params = array_merge([$productId], $orderedImageIds);
    $checkStmt->bind_param($types, ...$params);
    $checkStmt->execute();
    $validIds = array_column($checkStmt->get_result()->fetch_all(MYSQLI_ASSOC), 'id');

    if (count($validIds) !== count($orderedImageIds)) {
        throw new InvalidArgumentException('One or more images do not belong to this product.');
    }

    $conn->begin_transaction();

    try {
        $updateStmt = $conn->prepare("UPDATE product_images SET sort_order = ? WHERE id = ? AND product_id = ?");
        if (!$updateStmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }

        foreach ($orderedImageIds as $position => $imageId) {
            $updateStmt->bind_param('iii', $position, $imageId, $productId);
            $updateStmt->execute();
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

    return true;
}

/**
 * Mark a single image as the primary image for its product, unsetting any
 * other primary image on that product.
 *
 * @param mysqli $conn
 * @param int $productId
 * @param int $imageId
 * @return bool
 * @throws InvalidArgumentException If the image doesn't belong to the product
 * @throws Exception
 */
function setPrimaryProductImage($conn, $productId, $imageId)
{
    $image = getProductImageById($conn, $imageId);

    if ($image === null || (int) $image['product_id'] !== (int) $productId) {
        throw new InvalidArgumentException('This image does not belong to the given product.');
    }

    $conn->begin_transaction();

    try {
        $unsetStmt = $conn->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
        if (!$unsetStmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        $unsetStmt->bind_param('i', $productId);
        $unsetStmt->execute();

        $setStmt = $conn->prepare("UPDATE product_images SET is_primary = 1 WHERE id = ?");
        if (!$setStmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        $setStmt->bind_param('i', $imageId);
        $setStmt->execute();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

    return true;
}

/**
 * Convenience helper: build the public URL for a stored image path.
 *
 * @param string $imagePath As stored in product_images.image
 * @return string
 */
function getProductImageUrl($imagePath)
{
    return rtrim(BASE_URL, '/') . '/uploads/products/' . ltrim($imagePath, '/');
}