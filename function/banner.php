<?php require_once __DIR__ . '/../config/database.php'; ?>
<?php

/**
 * Banner data-access layer.
 *
 * Backs both the public homepage hero slider AND the admin banner
 * management screen, on top of the existing `banners` table — no new
 * table, no new columns. Follows the same mysqli / prepared-statement /
 * error-handling conventions as function/category.php and
 * function/product.php.
 */

const BANNER_STATUS_ACTIVE = 1;
const BANNER_STATUS_INACTIVE = 0;

/** Used to populate a status <select> in the admin form. */
const BANNER_STATUS_OPTIONS = [
    BANNER_STATUS_ACTIVE => 'Active',
    BANNER_STATUS_INACTIVE => 'Inactive',
];

// Absolute path on disk where banner images are physically stored.
if (!defined('BANNER_IMAGE_UPLOAD_DIR')) {
    define('BANNER_IMAGE_UPLOAD_DIR', __DIR__ . '/../uploads/banners/');
}

// Public web path used to build <img src="..."> URLs for banner images.
if (!defined('BANNER_IMAGE_PUBLIC_PATH')) {
    define('BANNER_IMAGE_PUBLIC_PATH', '/uploads/banners/');
}

if (!defined('BANNER_IMAGE_MAX_BYTES')) {
    define('BANNER_IMAGE_MAX_BYTES', 5 * 1024 * 1024); // 5 MB
}

/* =============================================================================
 * Public read helpers (used by the homepage hero slider)
 * ============================================================================= */

/**
 * Fetch active banners for display, ordered for the slider.
 *
 * @param mysqli $conn
 * @param string|null $position Optional value of the `position` column to
 *                               filter by (e.g. 'homepage_hero'), if/when
 *                               banners start being tagged by placement.
 *                               Left null, every active banner is eligible.
 * @return array<int, array<string, mixed>> Active banners as assoc arrays,
 *                                          ordered by sort_order ASC.
 * @throws Exception
 */
function getActiveBanners($conn, $position = null)
{
    $sql = "SELECT * FROM banners WHERE status = " . BANNER_STATUS_ACTIVE;
    $types = '';
    $params = [];

    if ($position !== null && $position !== '') {
        $sql .= " AND position = ?";
        $types .= 's';
        $params[] = $position;
    }

    $sql .= " ORDER BY sort_order ASC, id ASC";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error fetching active banners: " . mysqli_error($conn));
    }

    $result = mysqli_stmt_get_result($stmt);
    if ($result === false) {
        throw new Exception("Error fetching active banners: " . mysqli_error($conn));
    }

    $banners = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $banners[] = $row;
    }

    mysqli_stmt_close($stmt);

    return $banners;
}

/**
 * Build the public URL for a stored banner image path.
 *
 * @param ?string $imagePath As stored in banners.image
 * @return ?string
 */
function getBannerImageUrl($imagePath)
{
    if ($imagePath === null || $imagePath === '') {
        return null;
    }

    $path = rtrim(BANNER_IMAGE_PUBLIC_PATH, '/') . '/' . ltrim($imagePath, '/');

    if (defined('BASE_URL') && BASE_URL !== '') {
        return rtrim(BASE_URL, '/') . $path;
    }

    return $path;
}

/* =============================================================================
 * Admin read helpers
 * ============================================================================= */

/**
 * Fetch every banner (any status), for the admin listing screen.
 *
 * @param mysqli $conn
 * @return array<int, array<string, mixed>>
 * @throws Exception
 */
function getAllBanners($conn)
{
    $sql = "SELECT * FROM banners ORDER BY sort_order ASC, id ASC";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        throw new Exception("Error fetching banners: " . mysqli_error($conn));
    }

    $banners = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $banners[] = $row;
    }

    return $banners;
}

/**
 * Fetch a single banner by id.
 *
 * @param mysqli $conn
 * @param int $id
 * @return array<string, mixed>|null
 * @throws Exception
 */
function getBannerById($conn, $id)
{
    $id = (int) $id;

    $sql = "SELECT * FROM banners WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error fetching banner: " . mysqli_error($conn));
    }

    $result = mysqli_stmt_get_result($stmt);
    if ($result === false) {
        throw new Exception("Error fetching banner: " . mysqli_error($conn));
    }

    $row = mysqli_fetch_assoc($result);
    return $row ?: null;
}

/* =============================================================================
 * Image upload
 * ============================================================================= */

/**
 * Validate and store an uploaded banner image, returning the relative
 * path to save in the `banners.image` column. Mirrors
 * uploadCategoryImage() in function/category.php.
 *
 * @param array|null $file One entry from $_FILES, e.g. $_FILES['image']
 * @return string|null Relative path to store in the DB, or null if no file was chosen
 * @throws InvalidArgumentException If a file was chosen but is invalid
 * @throws RuntimeException If a valid file can't be moved to disk
 */
function uploadBannerImage($file)
{
    if (!is_array($file) || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE   => 'The image exceeds the server upload_max_filesize limit.',
            UPLOAD_ERR_FORM_SIZE  => 'The image exceeds the form MAX_FILE_SIZE limit.',
            UPLOAD_ERR_PARTIAL    => 'The image was only partially uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary upload folder on the server.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write the image to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the image upload.',
        ];
        throw new InvalidArgumentException($uploadErrors[$file['error']] ?? 'Unknown upload error.');
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        throw new InvalidArgumentException('Invalid upload.');
    }

    if ($file['size'] <= 0) {
        throw new InvalidArgumentException('The uploaded image is empty.');
    }

    if ($file['size'] > BANNER_IMAGE_MAX_BYTES) {
        throw new InvalidArgumentException('The image is larger than ' . (BANNER_IMAGE_MAX_BYTES / 1024 / 1024) . 'MB.');
    }

    $allowedMimeToExt = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    // Detect the real MIME type from file contents; never trust the
    // client-supplied Content-Type header.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!isset($allowedMimeToExt[$mime])) {
        throw new InvalidArgumentException('Unsupported file type. Allowed types: JPG, PNG, WEBP, GIF.');
    }

    if (@getimagesize($file['tmp_name']) === false) {
        throw new InvalidArgumentException('The file does not appear to be a valid image.');
    }

    $extension = $allowedMimeToExt[$mime];

    $base = pathinfo($file['name'], PATHINFO_FILENAME);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $base), '-'));
    if ($slug === '') {
        $slug = 'banner';
    }
    $slug = substr($slug, 0, 60);

    $filename = $slug . '-' . bin2hex(random_bytes(6)) . '.' . $extension;

    if (!is_dir(BANNER_IMAGE_UPLOAD_DIR) && !mkdir(BANNER_IMAGE_UPLOAD_DIR, 0755, true) && !is_dir(BANNER_IMAGE_UPLOAD_DIR)) {
        throw new RuntimeException('Could not create the upload directory.');
    }

    $destination = rtrim(BANNER_IMAGE_UPLOAD_DIR, '/') . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Could not move the uploaded image to its destination.');
    }

    chmod($destination, 0644);

    return $filename;
}

/**
 * Delete a banner image file from disk, if it exists. Best-effort: never
 * throws, since a missing/already-removed file shouldn't block a DB update.
 *
 * @param ?string $imagePath As stored in banners.image
 * @return void
 */
function deleteBannerImageFile($imagePath)
{
    if ($imagePath === null || $imagePath === '') {
        return;
    }

    $fullPath = rtrim(BANNER_IMAGE_UPLOAD_DIR, '/') . '/' . ltrim($imagePath, '/');
    if (is_file($fullPath)) {
        @unlink($fullPath);
    }
}

/* =============================================================================
 * Admin write operations
 * ============================================================================= */

/**
 * Insert a new banner.
 *
 * @param mysqli $conn
 * @param string|null $title
 * @param string|null $subtitle
 * @param string $image        Relative path from uploadBannerImage(). Required.
 * @param string|null $button_text
 * @param string|null $button_url
 * @param string|null $position
 * @param int $status          One of BANNER_STATUS_ACTIVE / BANNER_STATUS_INACTIVE.
 * @param int $sort_order
 * @return int Newly created banner id.
 * @throws Exception
 * @throws InvalidArgumentException
 */
function addBanner($conn, $title, $subtitle, $image, $button_text, $button_url, $position, $status, $sort_order)
{
    $image = trim((string) $image);
    if ($image === '') {
        throw new InvalidArgumentException('A banner image is required.');
    }

    $status = (int) $status;
    if (!array_key_exists($status, BANNER_STATUS_OPTIONS)) {
        throw new InvalidArgumentException('Invalid status.');
    }

    $title = normalizeNullableBannerText($title ?? null);
    $subtitle = normalizeNullableBannerText($subtitle ?? null);
    $button_text = normalizeNullableBannerText($button_text ?? null);
    $button_url = normalizeNullableBannerText($button_url ?? null);
    $position = normalizeNullableBannerText($position ?? null);
    $sort_order = max(0, (int) $sort_order); // column is UNSIGNED

    $sql = "INSERT INTO banners
            (title, subtitle, image, button_text, button_url, position, status, sort_order, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'ssssssii', $title, $subtitle, $image, $button_text, $button_url, $position, $status, $sort_order);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error adding banner: ' . mysqli_error($conn));
    }

    $result = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $result;
}

/**
 * Update an existing banner. Pass $image as null to keep the existing image.
 *
 * @param mysqli $conn
 * @param int $id
 * @param string|null $title
 * @param string|null $subtitle
 * @param string|null $image        New relative path, or null to keep the current image.
 * @param string|null $button_text
 * @param string|null $button_url
 * @param string|null $position
 * @param int $status
 * @param int $sort_order
 * @return bool
 * @throws Exception
 * @throws InvalidArgumentException
 */
function updateBanner($conn, $id, $title, $subtitle, $image, $button_text, $button_url, $position, $status, $sort_order)
{
    $id = (int) $id;

    $existing = getBannerById($conn, $id);
    if (!$existing) {
        throw new InvalidArgumentException('Banner not found.');
    }

    $status = (int) $status;
    if (!array_key_exists($status, BANNER_STATUS_OPTIONS)) {
        throw new InvalidArgumentException('Invalid status.');
    }

    $image = ($image === null || trim((string) $image) === '') ? $existing['image'] : trim((string) $image);

    $title = normalizeNullableBannerText($title ?? null);
    $subtitle = normalizeNullableBannerText($subtitle ?? null);
    $button_text = normalizeNullableBannerText($button_text ?? null);
    $button_url = normalizeNullableBannerText($button_url ?? null);
    $position = normalizeNullableBannerText($position ?? null);
    $sort_order = max(0, (int) $sort_order); // column is UNSIGNED

    $sql = "UPDATE banners
            SET title = ?, subtitle = ?, image = ?, button_text = ?, button_url = ?, position = ?, status = ?, sort_order = ?, updated_at = NOW()
            WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'ssssssiii', $title, $subtitle, $image, $button_text, $button_url, $position, $status, $sort_order, $id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error updating banner: ' . mysqli_error($conn));
    }

    return true;
}

/**
 * Toggle (or explicitly set) a banner's status.
 *
 * @param mysqli $conn
 * @param int $id
 * @param int $status One of BANNER_STATUS_ACTIVE / BANNER_STATUS_INACTIVE.
 * @return bool
 * @throws Exception
 * @throws InvalidArgumentException
 */
function updateBannerStatus($conn, $id, $status)
{
    $id = (int) $id;
    $status = (int) $status;

    if (!array_key_exists($status, BANNER_STATUS_OPTIONS)) {
        throw new InvalidArgumentException('Invalid status.');
    }

    $sql = "UPDATE banners SET status = ?, updated_at = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'ii', $status, $id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error updating banner status: ' . mysqli_error($conn));
    }

    return mysqli_stmt_affected_rows($stmt) > 0;
}

/**
 * Persist a new display order for a set of banners in one transaction.
 *
 * @param mysqli $conn
 * @param array<int, int> $order Map of banner id => sort_order.
 * @return void
 * @throws Exception
 */
function reorderBanners($conn, array $order)
{
    if (empty($order)) {
        return;
    }

    mysqli_begin_transaction($conn);

    try {
        $sql = "UPDATE banners SET sort_order = ?, updated_at = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }

        foreach ($order as $id => $sortOrder) {
            $id = (int) $id;
            $sortOrder = max(0, (int) $sortOrder); // column is UNSIGNED
            mysqli_stmt_bind_param($stmt, 'ii', $sortOrder, $id);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Error saving banner order: ' . mysqli_error($conn));
            }
        }

        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }
}

/**
 * Delete a banner and remove its image file from disk.
 *
 * @param mysqli $conn
 * @param int $id
 * @return bool
 * @throws Exception
 * @throws InvalidArgumentException
 */
function deleteBanner($conn, $id)
{
    $id = (int) $id;

    $existing = getBannerById($conn, $id);
    if (!$existing) {
        throw new InvalidArgumentException('Banner not found.');
    }

    $sql = "DELETE FROM banners WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error deleting banner: ' . mysqli_error($conn));
    }

    deleteBannerImageFile($existing['image']);

    return true;
}

/**
 * Trim a string field down to null-or-trimmed-string, so empty optional
 * fields (subtitle, button_text, button_url, position) are stored as NULL
 * rather than empty strings.
 *
 * @param ?string $value
 * @return ?string
 */
function normalizeNullableBannerText($value)
{
    if ($value === null) {
        return null;
    }
    $trimmed = trim($value);
    return $trimmed === '' ? null : $trimmed;
}