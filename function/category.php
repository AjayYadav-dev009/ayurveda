<?php require_once __DIR__ . '/../config/database.php'; ?>
<?php

// Absolute path on disk where category images are physically stored.
if (!defined('CATEGORY_IMAGE_UPLOAD_DIR')) {
    define('CATEGORY_IMAGE_UPLOAD_DIR', __DIR__ . '/../uploads/categories/');
}

// Public web path used to build <img src="..."> URLs.
if (!defined('CATEGORY_IMAGE_PUBLIC_PATH')) {
    define('CATEGORY_IMAGE_PUBLIC_PATH', '/uploads/categories/');
}

if (!defined('CATEGORY_IMAGE_MAX_BYTES')) {
    define('CATEGORY_IMAGE_MAX_BYTES', 5 * 1024 * 1024); // 5 MB
}

/**
 * Validate and store an uploaded category image, returning the relative
 * path to save in the `categories.image` column.
 *
 * This is the piece that was missing before: $_FILES['image'] is an array
 * (tmp_name, error, size, ...), never something you can hand straight to a
 * mysqli string bind_param. Always run it through this function first and
 * pass the returned *string* (or null) into addCategory()/updateCategory().
 *
 * @param array|null $file One entry from $_FILES, e.g. $_FILES['image']
 * @return string|null Relative path to store in the DB, or null if no file was chosen
 * @throws InvalidArgumentException If a file was chosen but is invalid
 * @throws RuntimeException If a valid file can't be moved to disk
 */
function uploadCategoryImage($file)
{
    // No file input, or the field was left empty — that's fine, just keep
    // whatever image the category already has.
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

    if ($file['size'] > CATEGORY_IMAGE_MAX_BYTES) {
        throw new InvalidArgumentException('The image is larger than ' . (CATEGORY_IMAGE_MAX_BYTES / 1024 / 1024) . 'MB.');
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
        $slug = 'category';
    }
    $slug = substr($slug, 0, 60);

    $filename = $slug . '-' . bin2hex(random_bytes(6)) . '.' . $extension;

    if (!is_dir(CATEGORY_IMAGE_UPLOAD_DIR) && !mkdir(CATEGORY_IMAGE_UPLOAD_DIR, 0755, true) && !is_dir(CATEGORY_IMAGE_UPLOAD_DIR)) {
        throw new RuntimeException('Could not create the upload directory.');
    }

    $destination = rtrim(CATEGORY_IMAGE_UPLOAD_DIR, '/') . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Could not move the uploaded image to its destination.');
    }

    chmod($destination, 0644);

    return $filename;
}

/**
 * Build the public URL for a stored category image path.
 *
 * @param ?string $imagePath As stored in categories.image
 * @return ?string
 */
function getCategoryImageUrl($imagePath)
{
    if ($imagePath === null || $imagePath === '') {
        return null;
    }
    return rtrim(CATEGORY_IMAGE_PUBLIC_PATH, '/') . '/' . ltrim($imagePath, '/');
}

/**
 * Insert a new category.
 *
 * @param mysqli $conn
 * @param int|null $parent_id
 * @param string $name
 * @param string $slug
 * @param string $meta_title
 * @param string $meta_description
 * @param string|null $description
 * @param string|null $image
 * @param string $status   Must be one of CATEGORY_STATUSES.
 * @param int $sort_order
 * @return int Newly created category id.
 * @throws Exception
 * @throws InvalidArgumentException
 */
function addCategory($conn, $parent_id, $name, $slug, $meta_title, $meta_description, $description, $image, $status, $sort_order)
{
    $name = trim($name);

    if ($name === '') {
        throw new InvalidArgumentException('Category name is required.');
    }

    $meta_title = createMetaTitle($meta_title);
    $meta_description = createMetaDescription($meta_description);
    $slug = createSlug($name);

    if (!in_array($status, CATEGORY_STATUSES, true)) {
        throw new InvalidArgumentException(
            'Invalid status. Allowed values: ' . implode(', ', CATEGORY_STATUSES)
        );
    }

    // Normalize optional fields.
    $parent_id = ($parent_id === '' || $parent_id === null) ? null : (int) $parent_id;
    $sort_order = (int) $sort_order;

    if ($parent_id !== null) {
        // Make sure the chosen parent actually exists.
        $parentResult = getCategoryById($conn, $parent_id);
        if (mysqli_num_rows($parentResult) === 0) {
            throw new InvalidArgumentException('Selected parent category does not exist.');
        }
    }

    if (slugExists($conn, $slug)) {
        throw new InvalidArgumentException('A category with this slug already exists.');
    }

    $sql = "INSERT INTO categories
            (parent_id, name, slug, meta_title, meta_description, description, image, status, sort_order, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'isssssssi', $parent_id, $name, $slug, $meta_title, $meta_description, $description, $image, $status, $sort_order);

    if (!mysqli_stmt_execute($stmt)) {
        // 1062 = duplicate entry (race condition guard, in addition to the
        // slugExists() pre-check above).
        if (mysqli_errno($conn) === 1062) {
            throw new InvalidArgumentException('A category with this slug already exists.');
        }
        throw new Exception('Error adding category: ' . mysqli_error($conn));
    }

    $result = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $result;
}


/**
 * Walk up the parent chain starting at $startId and check whether
 * $targetId appears anywhere in it. Used to stop a category being
 * re-parented under one of its own descendants (which would create
 * a cycle in the tree).
 *
 * @param mysqli $conn
 * @param int $startId   The proposed new parent_id.
 * @param int $targetId  The category being edited.
 * @return bool True if $targetId is an ancestor of $startId (i.e. a cycle).
 * @throws Exception
 */
function wouldCreateCircularReference($conn, $startId, $targetId)
{
    $currentId = $startId;
    $visited = [];

    while ($currentId !== null) {
        if ($currentId === $targetId) {
            return true;
        }

        // Guard against any pre-existing bad data looping forever.
        if (isset($visited[$currentId])) {
            break;
        }
        $visited[$currentId] = true;

        $result = getCategoryById($conn, $currentId);
        $row = mysqli_fetch_assoc($result);
        if (!$row) {
            break;
        }

        $currentId = $row['parent_id'] !== null ? (int) $row['parent_id'] : null;
    }

    return false;
}

/**
 * Update an existing category.
 *
 * @param mysqli $conn
 * @param int $id
 * @param int|null $parent_id
 * @param string $name
 * @param string $slug
 * @param string|null $description
 * @param string|null $image
 * @param string $status   Must be one of CATEGORY_STATUSES.
 * @param int $sort_order
 * @return bool
 * @throws Exception
 * @throws InvalidArgumentException
 */
function updateCategory($conn, $id, $parent_id, $name, $slug, $meta_title, $meta_description, $description, $image, $status, $sort_order)
{
    $id = (int) $id;
    $name = trim($name);

    if ($name === '') {
        throw new InvalidArgumentException('Category name is required.');
    }

    $meta_title = createMetaTitle($meta_title);
    $meta_description = createMetaDescription($meta_description);
    $slug = createSlug($name);

    if (!in_array($status, CATEGORY_STATUSES, true)) {
        throw new InvalidArgumentException(
            'Invalid status. Allowed values: ' . implode(', ', CATEGORY_STATUSES)
        );
    }

    $parent_id = ($parent_id === '' || $parent_id === null) ? null : (int) $parent_id;
    $sort_order = (int) $sort_order;

    if ($parent_id !== null) {
        if ($parent_id === $id) {
            throw new InvalidArgumentException('A category cannot be its own parent.');
        }

        $parentResult = getCategoryById($conn, $parent_id);
        if (mysqli_num_rows($parentResult) === 0) {
            throw new InvalidArgumentException('Selected parent category does not exist.');
        }

        if (wouldCreateCircularReference($conn, $parent_id, $id)) {
            throw new InvalidArgumentException('Cannot set parent: this would create a circular category tree.');
        }
    }

    if (slugExists($conn, $slug, $id)) {
        throw new InvalidArgumentException('A category with this slug already exists.');
    }

    $sql = "UPDATE categories
            SET parent_id = ?, name = ?, slug = ?, meta_title = ?, meta_description = ?, description = ?, image = ?, status = ?, sort_order = ?, updated_at = NOW()
            WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'isssssssii', $parent_id, $name, $slug, $meta_title, $meta_description, $description, $image, $status, $sort_order, $id);

    if (!mysqli_stmt_execute($stmt)) {
        if (mysqli_errno($conn) === 1062) {
            throw new InvalidArgumentException('A category with this slug already exists.');
        }
        throw new Exception('Error updating category: ' . mysqli_error($conn));
    }

    return true;
}


/** Slug used to find/create the fallback category for orphaned products. */
const UNCATEGORIZED_SLUG = 'uncategorised';
const UNCATEGORIZED_NAME = 'Uncategorised';

/**
 * Thrown when a delete is blocked pending user confirmation. Carries a
 * full breakdown of what the delete will do, so the caller can show it
 * to the user before they confirm.
 */
class CategoryDeletionImpactException extends InvalidArgumentException
{
    /** @var array<int,array{id:int,name:string}> Subcategories that will be deleted (excludes the category itself). */
    public array $subcategories;

    /** @var array<int,array{id:int,title:string}> Products that will be moved to "Uncategorised" (no other category). */
    public array $productsToUncategorize;

    /** @var array<int,array{id:int,title:string}> Products that will just be unlinked from this category/subtree but kept. */
    public array $productsToUnlink;

    public function __construct(array $subcategories, array $productsToUncategorize, array $productsToUnlink, string $message)
    {
        parent::__construct($message);
        $this->subcategories = $subcategories;
        $this->productsToUncategorize = $productsToUncategorize;
        $this->productsToUnlink = $productsToUnlink;
    }
}

/**
 * Find the "Uncategorised" category, creating it if it doesn't exist yet.
 * Used as the fallback home for products that would otherwise be left
 * with zero categories after a category (sub)tree is deleted.
 *
 * @param mysqli $conn
 * @return int Category id.
 * @throws Exception
 */
function getOrCreateUncategorizedCategory($conn)
{
    $stmt = mysqli_prepare($conn, "SELECT id FROM categories WHERE slug = ? LIMIT 1");
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }
    $slug = UNCATEGORIZED_SLUG;
    mysqli_stmt_bind_param($stmt, 's', $slug);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error looking up uncategorised category: " . mysqli_error($conn));
    }
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    if ($row) {
        return (int) $row['id'];
    }

    $name = UNCATEGORIZED_NAME;
    $sql = "INSERT INTO categories (parent_id, name, slug, meta_title, meta_description, description, image, status, sort_order, created_at, updated_at)
            VALUES (NULL, ?, ?, '', '', 'Automatically created holding category for products left without a category.', NULL, 'Active', 0, NOW(), NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 'ss', $name, $slug);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error creating uncategorised category: " . mysqli_error($conn));
    }

    return mysqli_insert_id($conn);
}

/**
 * Delete a category and its entire subtree (all descendant categories,
 * at any depth).
 *
 * Two-step confirmation flow:
 *
 *   1. Call with $force = false (default). If deleting would affect
 *      anything beyond the category itself (it has subcategories,
 *      and/or products are linked to it or its subcategories),
 *      nothing is deleted. A CategoryDeletionImpactException is thrown
 *      with a full breakdown so the caller can show the user exactly
 *      what will happen and ask for confirmation.
 *
 *   2. If the user confirms, call again with $force = true:
 *        - The category and every descendant category are deleted.
 *        - For every product linked to any of those categories:
 *            - if it has NO other category outside this subtree, it
 *              is re-linked to the "Uncategorised" category (created
 *              automatically if it doesn't exist yet) instead of
 *              being deleted, so no product data is ever lost.
 *            - if it still has at least one category outside this
 *              subtree, it is simply unlinked from the deleted
 *              categories and otherwise left untouched.
 *
 *   A category with no subcategories and no linked products is
 *   deleted immediately regardless of $force.
 *
 * The whole operation runs in a single transaction: if any step
 * fails, everything is rolled back.
 *
 * @param mysqli $conn
 * @param int $id
 * @param bool $force  Set true only after the user has explicitly
 *                      confirmed the deletion after seeing the impact.
 * @return array{deletedCategoryIds: int[], uncategorizedProductIds: int[], unlinkedProductIds: int[]}
 * @throws Exception
 * @throws InvalidArgumentException
 * @throws CategoryDeletionImpactException
 */
function deleteCategory($conn, $id, $force = false)
{
    $id = (int) $id;

    $existing = getCategoryById($conn, $id);
    $categoryRow = mysqli_fetch_assoc($existing);
    if (!$categoryRow) {
        throw new InvalidArgumentException('Category not found.');
    }

    if (strcasecmp($categoryRow['slug'], UNCATEGORIZED_SLUG) === 0) {
        throw new InvalidArgumentException('The "Uncategorised" category cannot be deleted; it is used as the fallback for orphaned products.');
    }

    $subtreeIds = getCategorySubtreeIds($conn, $id);
    $subcategoryIds = array_values(array_diff($subtreeIds, [$id]));

    $subcategories = [];
    if (!empty($subcategoryIds)) {
        $placeholders = implode(',', array_fill(0, count($subcategoryIds), '?'));
        $types = str_repeat('i', count($subcategoryIds));
        $sql = "SELECT id, name FROM categories WHERE id IN ($placeholders)";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        $bindArgs = [$types];
        foreach ($subcategoryIds as $key => $value) {
            $bindArgs[] = &$subcategoryIds[$key];
        }
        call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $bindArgs));
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error fetching subcategories: " . mysqli_error($conn));
        }
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $subcategories[] = ['id' => (int) $row['id'], 'name' => $row['name']];
        }
    }

    $productImpact = getProductImpactForSubtree($conn, $subtreeIds);
    $productsToUncategorize = $productImpact['toDelete'];
    $productsToUnlink = $productImpact['toUnlink'];

    $hasImpact = !empty($subcategories) || !empty($productsToUncategorize) || !empty($productsToUnlink);

    if ($hasImpact && !$force) {
        $parts = [];
        if (!empty($subcategories)) {
            $parts[] = count($subcategories) . ' subcategor' . (count($subcategories) === 1 ? 'y' : 'ies') .
                ' (' . implode(', ', array_column($subcategories, 'name')) . ')';
        }
        if (!empty($productsToUncategorize)) {
            $parts[] = count($productsToUncategorize) . ' product' . (count($productsToUncategorize) === 1 ? '' : 's') .
                ' that will be moved to "' . UNCATEGORIZED_NAME . '", since ' . (count($productsToUncategorize) === 1 ? 'it has' : 'they have') .
                ' no other category (' . implode(', ', array_column($productsToUncategorize, 'title')) . ')';
        }
        if (!empty($productsToUnlink)) {
            $parts[] = count($productsToUnlink) . ' product' . (count($productsToUnlink) === 1 ? '' : 's') .
                ' that will just be unlinked from this category but kept, since ' .
                (count($productsToUnlink) === 1 ? 'it still has' : 'they still have') . ' another category';
        }

        throw new CategoryDeletionImpactException(
            $subcategories,
            $productsToUncategorize,
            $productsToUnlink,
            'Deleting "' . $categoryRow['name'] . '" will also affect: ' . implode('; ', $parts) . '. Do you still want to delete it?'
        );
    }

    // Either nothing but the category itself is affected, or the user has
    // already confirmed (force = true). Run everything atomically.
    mysqli_begin_transaction($conn);

    try {
        // Resolve (or create) the fallback category *before* deleting
        // anything, so if this fails we haven't touched real data yet.
        $uncategorizedId = null;
        if (!empty($productsToUncategorize)) {
            $uncategorizedId = getOrCreateUncategorizedCategory($conn);
        }

        // Deleting the categories cascades to product_categories rows
        // automatically (fk_pc_category ON DELETE CASCADE).
        $placeholders = implode(',', array_fill(0, count($subtreeIds), '?'));
        $types = str_repeat('i', count($subtreeIds));
        $sql = "DELETE FROM categories WHERE id IN ($placeholders)";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        $bindArgs = [$types];
        $idsForBind = $subtreeIds;
        foreach ($idsForBind as $key => $value) {
            $bindArgs[] = &$idsForBind[$key];
        }
        call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $bindArgs));
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Error deleting categories: ' . mysqli_error($conn));
        }

        // Re-link orphaned products to "Uncategorised" instead of
        // deleting them. Their old product_categories rows for the
        // deleted subtree are already gone via the cascade above.
        if (!empty($productsToUncategorize) && $uncategorizedId !== null) {
            $sql = "INSERT INTO product_categories (product_id, category_id, is_primary) VALUES (?, ?, 1)";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception("Error preparing statement: " . mysqli_error($conn));
            }
            foreach ($productsToUncategorize as $product) {
                $productId = (int) $product['id'];
                mysqli_stmt_bind_param($stmt, 'ii', $productId, $uncategorizedId);
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception('Error moving product to Uncategorised: ' . mysqli_error($conn));
                }
            }
        }

        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }

    return [
        'deletedCategoryIds' => $subtreeIds,
        'uncategorizedProductIds' => array_column($productsToUncategorize, 'id'),
        'unlinkedProductIds' => array_column($productsToUnlink, 'id'),
    ];
}

/**
 *
 * @param mysqli $conn
 * @param int $id
 * @return array{
 *     category: array,
 *     subcategories: array<int,array{id:int,name:string}>,
 *     productsToUncategorize: array<int,array{id:int,title:string}>,
 *     productsToUnlink: array<int,array{id:int,title:string}>,
 *     hasImpact: bool
 * }
 * @throws Exception
 * @throws InvalidArgumentException
 */
function previewCategoryDeletion($conn, $id)
{
    $id = (int) $id;

    $categoryRow = mysqli_fetch_assoc(getCategoryById($conn, $id));
    if (!$categoryRow) {
        throw new InvalidArgumentException('Category not found.');
    }

    if (strcasecmp($categoryRow['slug'], UNCATEGORIZED_SLUG) === 0) {
        throw new InvalidArgumentException('The "Uncategorised" category cannot be deleted; it is used as the fallback for orphaned products.');
    }

    $subtreeIds = getCategorySubtreeIds($conn, $id);
    $subcategoryIds = array_values(array_diff($subtreeIds, [$id]));

    $subcategories = [];
    if (!empty($subcategoryIds)) {
        $placeholders = implode(',', array_fill(0, count($subcategoryIds), '?'));
        $types = str_repeat('i', count($subcategoryIds));
        $sql = "SELECT id, name FROM categories WHERE id IN ($placeholders)";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        $bindArgs = [$types];
        foreach ($subcategoryIds as $key => $value) {
            $bindArgs[] = &$subcategoryIds[$key];
        }
        call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $bindArgs));
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error fetching subcategories: " . mysqli_error($conn));
        }
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $subcategories[] = ['id' => (int) $row['id'], 'name' => $row['name']];
        }
    }

    $productImpact = getProductImpactForSubtree($conn, $subtreeIds);

    return [
        'category' => $categoryRow,
        'subcategories' => $subcategories,
        'productsToUncategorize' => $productImpact['toDelete'],
        'productsToUnlink' => $productImpact['toUnlink'],
        'hasImpact' => !empty($subcategories) || !empty($productImpact['toDelete']) || !empty($productImpact['toUnlink']),
    ];
}

const CATEGORY_STATUSES = ['Active', 'Inactive'];

/**
 * Fetch all categories.
 *
 * @param mysqli $conn
 * @return mysqli_result
 * @throws Exception
 */
function getCategories($conn)
{
    $sql = "SELECT * FROM categories ORDER BY sort_order ASC, id ASC";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        throw new Exception("Error fetching categories: " . mysqli_error($conn));
    }

    return $result;
}

/**
 * Fetch every non-root category (i.e. it has a parent_id) that is Active
 * and has at least one Active product directly linked to it via
 * product_categories.
 *
 * Root categories are organisational buckets, not browsable destinations,
 * and a category with nothing in stock is a dead end for a customer — so
 * neither belongs on a storefront surface. This is the one place that rule
 * lives; the mega-menu and the category grid both call this instead of
 * each re-implementing the same filter.
 *
 * @param mysqli $conn
 * @return mysqli_result
 * @throws Exception
 */
function getSubcategoriesWithProducts($conn)
{
    $sql = "SELECT c.*
            FROM categories c
            WHERE c.parent_id IS NOT NULL
              AND c.status = 'Active'
              AND EXISTS (
                  SELECT 1
                  FROM product_categories pc
                  INNER JOIN products p ON p.id = pc.product_id AND p.status = 'Active'
                  WHERE pc.category_id = c.id
              )
            ORDER BY c.sort_order ASC, c.name ASC";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        throw new Exception("Error fetching subcategories with products: " . mysqli_error($conn));
    }

    return $result;
}

/**
 * Fetch a single category by id.
 *
 * @param mysqli $conn
 * @param int $id
 * @return mysqli_result
 * @throws Exception
 */
function getCategoryById($conn, $id)
{
    $sql = "SELECT * FROM categories WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error fetching category by ID: " . mysqli_error($conn));
    }

    $result = mysqli_stmt_get_result($stmt);
    if ($result === false) {
        throw new Exception("Error fetching category by ID: " . mysqli_error($conn));
    }

    return $result;
}

/**
 * Fetch all direct children of a given category (used for delete guards
 * and building a tree view).
 *
 * @param mysqli $conn
 * @param int $parentId
 * @return mysqli_result
 * @throws Exception
 */
function getChildCategories($conn, $parentId)
{
    $sql = "SELECT id, name FROM categories WHERE parent_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'i', $parentId);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error fetching child categories: " . mysqli_error($conn));
    }

    $result = mysqli_stmt_get_result($stmt);
    if ($result === false) {
        throw new Exception("Error fetching child categories: " . mysqli_error($conn));
    }

    return $result;
}

/**
 * Check whether a slug is already used by another category.
 *
 * @param mysqli $conn
 * @param string $slug
 * @param int|null $excludeId  Category id to exclude (used when editing).
 * @return bool
 * @throws Exception
 */
function slugExists($conn, $slug, $excludeId = null)
{
    if ($excludeId !== null) {
        $sql = "SELECT id FROM categories WHERE slug = ? AND id != ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'si', $slug, $excludeId);
    } else {
        $sql = "SELECT id FROM categories WHERE slug = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 's', $slug);
    }

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error checking slug: " . mysqli_error($conn));
    }

    mysqli_stmt_store_result($stmt);
    return mysqli_stmt_num_rows($stmt) > 0;
}

/**
 * Get every descendant category id of $rootId, at any depth, including
 * $rootId itself. Used when cascade-deleting a whole category subtree.
 *
 * @param mysqli $conn
 * @param int $rootId
 * @return int[]
 * @throws Exception
 */
function getCategorySubtreeIds($conn, $rootId)
{
    $ids = [(int) $rootId];
    $levelIds = [(int) $rootId];

    while (!empty($levelIds)) {
        $placeholders = implode(',', array_fill(0, count($levelIds), '?'));
        $types = str_repeat('i', count($levelIds));

        $sql = "SELECT id FROM categories WHERE parent_id IN ($placeholders)";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }

        $bindArgs = [];
        $bindArgs[] = $types;
        foreach ($levelIds as $key => $value) {
            $bindArgs[] = &$levelIds[$key];
        }
        call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $bindArgs));

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error fetching category subtree: " . mysqli_error($conn));
        }

        $result = mysqli_stmt_get_result($stmt);
        $nextLevel = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $nextLevel[] = (int) $row['id'];
        }

        $ids = array_merge($ids, $nextLevel);
        $levelIds = $nextLevel;
    }

    return $ids;
}

/**
 * Work out exactly what deleting a category subtree will do to products:
 * which ones will be fully deleted (no category left outside the subtree)
 * vs. which ones will simply be unlinked from these categories but survive
 * (because they're still linked to at least one category outside the subtree).
 *
 * @param mysqli $conn
 * @param int[] $subtreeIds
 * @return array{toDelete: array<int,array{id:int,title:string}>, toUnlink: array<int,array{id:int,title:string}>}
 * @throws Exception
 */
function getProductImpactForSubtree($conn, array $subtreeIds)
{
    $placeholders = implode(',', array_fill(0, count($subtreeIds), '?'));
    $types = str_repeat('i', count($subtreeIds));

    // Every distinct product linked to any category in the subtree, plus
    // how many category links that product has OUTSIDE the subtree.
    $sql = "SELECT p.id, p.title,
                   (
                       SELECT COUNT(*) FROM product_categories pc_out
                       WHERE pc_out.product_id = p.id
                         AND pc_out.category_id NOT IN ($placeholders)
                   ) AS other_category_count
            FROM products p
            INNER JOIN (
                SELECT DISTINCT product_id FROM product_categories WHERE category_id IN ($placeholders)
            ) affected ON affected.product_id = p.id";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    // Same subtree ids are needed twice (once per IN clause).
    $allIds = array_merge($subtreeIds, $subtreeIds);
    $allTypes = $types . $types;

    $bindArgs = [$allTypes];
    foreach ($allIds as $key => $value) {
        $bindArgs[] = &$allIds[$key];
    }
    call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $bindArgs));

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error checking product impact: " . mysqli_error($conn));
    }

    $result = mysqli_stmt_get_result($stmt);

    $toDelete = [];
    $toUnlink = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $entry = ['id' => (int) $row['id'], 'title' => $row['title']];
        if ((int) $row['other_category_count'] === 0) {
            $toDelete[] = $entry;
        } else {
            $toUnlink[] = $entry;
        }
    }

    return ['toDelete' => $toDelete, 'toUnlink' => $toUnlink];
}

/**
 * Homepage "Shop By Category" helpers.
 *
 * Adds only what the homepage section needs on top of the existing
 * function/category.php — no new table, no duplicated CRUD, no rewritten
 * category logic. Reuses getCategoryImageUrl() and CATEGORY_STATUSES from
 * category.php as-is.
 */

require_once __DIR__ . '/category.php';

// Shown when a category has no image, so a missing upload never breaks
// the card layout. Point this at the project's existing fallback image
// if one is already used elsewhere; this is just a sensible default.
if (!defined('CATEGORY_IMAGE_FALLBACK_PATH')) {
    define('CATEGORY_IMAGE_FALLBACK_PATH', '/assets/images/category-placeholder.png');
}

/**
 * Fetch active, top-level categories (parent_id IS NULL) for the
 * homepage "Shop By Category" slider.
 *
 * getCategories() in category.php returns every category regardless of
 * status/parent — right for the admin list, too broad for this section —
 * so this is a separate, narrower query rather than a change to that
 * function's behavior.
 *
 * @param mysqli $conn
 * @return array<int, array<string, mixed>> Ordered by sort_order ASC.
 * @throws Exception
 */
function getTopLevelActiveCategories($conn)
{
    $sql = "SELECT * FROM categories
            WHERE parent_id IS NULL AND status = ?
            ORDER BY sort_order ASC, id ASC";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    $status = 'Active'; // matches CATEGORY_STATUSES in category.php
    mysqli_stmt_bind_param($stmt, 's', $status);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error fetching top-level categories: " . mysqli_error($conn));
    }

    $result = mysqli_stmt_get_result($stmt);
    if ($result === false) {
        throw new Exception("Error fetching top-level categories: " . mysqli_error($conn));
    }

    $categories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }

    return $categories;
}

/**
 * Category image URL with a safe fallback when the category has no image.
 *
 * @param ?string $imagePath As stored in categories.image
 * @return string Always returns a usable URL, never null.
 */
function getCategoryImageUrlOrFallback($imagePath)
{
    $url = getCategoryImageUrl($imagePath);
    if ($url !== null) {
        return $url;
    }

    if (defined('BASE_URL') && BASE_URL !== '') {
        return rtrim(BASE_URL, '/') . CATEGORY_IMAGE_FALLBACK_PATH;
    }

    return CATEGORY_IMAGE_FALLBACK_PATH;
}

/**
 * Build a category's public URL from its slug.
 *
 * Matches the existing route used across the project (see categories.php):
 * products.php?category_slug=... — there is no /category/{slug} route.
 *
 * @param string $slug
 * @return string
 */
function getCategoryUrl($slug)
{
    $path = 'products.php?category_slug=' . urlencode($slug);

    if (defined('BASE_URL') && BASE_URL !== '') {
        return rtrim(BASE_URL, '/') . '/' . $path;
    }

    return $path;
}
