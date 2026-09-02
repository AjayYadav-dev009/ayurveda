<?php require_once __DIR__ . '/../../config/database.php'; ?>

<?php

const PRODUCT_STATUSES = ['Active', 'Inactive', 'Draft'];

const PRODUCT_VARIANT_STATUSES = ['Active', 'Inactive'];

/**
 *
 * @param mysqli $conn
 * @param array $filters  Optional: ['status' => ..., 'category_id' => ..., 'search' => ...]
 * @return mysqli_result
 * @throws Exception
 */
function getProducts($conn, array $filters = [])
{
    $where = [];
    $types = '';
    $params = [];

    if (!empty($filters['status'])) {
        $where[] = 'p.status = ?';
        $types .= 's';
        $params[] = $filters['status'];
    }

    if (!empty($filters['category_id'])) {
        $where[] = 'EXISTS (SELECT 1 FROM product_categories pc WHERE pc.product_id = p.id AND pc.category_id = ?)';
        $types .= 'i';
        $params[] = (int) $filters['category_id'];
    }

    if (!empty($filters['search'])) {
        $where[] = '(p.title LIKE ? OR p.slug LIKE ?)';
        $types .= 'ss';
        $like = '%' . $filters['search'] . '%';
        $params[] = $like;
        $params[] = $like;
    }

    $whereSql = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

    $sql = "SELECT p.*,
                   c.name AS primary_category_name,
                   pi.image AS primary_image
            FROM products p
            LEFT JOIN product_categories pc ON pc.product_id = p.id AND pc.is_primary = 1
            LEFT JOIN categories c ON c.id = pc.category_id
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
            $whereSql
            ORDER BY p.created_at DESC";

    if (empty($params)) {
        $result = mysqli_query($conn, $sql);
        if (!$result) {
            throw new Exception("Error fetching products: " . mysqli_error($conn));
        }
        return $result;
    }

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }
    $bindArgs = [$types];
    foreach ($params as $key => $value) {
        $bindArgs[] = &$params[$key];
    }
    call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $bindArgs));

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error fetching products: " . mysqli_error($conn));
    }

    return mysqli_stmt_get_result($stmt);
}

/**
 * Fetch a single product's core row (products table only).
 *
 * @param mysqli $conn
 * @param int $id
 * @return mysqli_result
 * @throws Exception
 */
function getProductById($conn, $id)
{
    $sql = "SELECT * FROM products WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error fetching product: " . mysqli_error($conn));
    }

    return mysqli_stmt_get_result($stmt);
}

/**
 * Fetch a product plus every related row (details, images, variants,
 * categories) for the edit/detail admin page.
 *
 * @param mysqli $conn
 * @param int $id
 * @return array{product: array|null, details: array|null, images: array, variants: array, categories: array}
 * @throws Exception
 */
function getProductWithRelations($conn, $id)
{
    $id = (int) $id;

    $productResult = getProductById($conn, $id);
    $product = mysqli_fetch_assoc($productResult);
    if (!$product) {
        return ['product' => null, 'details' => null, 'images' => [], 'variants' => [], 'categories' => []];
    }

    $details = null;
    $stmt = mysqli_prepare($conn, "SELECT * FROM product_details WHERE product_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $detailsResult = mysqli_stmt_get_result($stmt);
    $details = mysqli_fetch_assoc($detailsResult) ?: null;

    $images = [];
    $stmt = mysqli_prepare($conn, "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $imagesResult = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($imagesResult)) {
        $images[] = $row;
    }

    $variants = [];
    $stmt = mysqli_prepare($conn, "SELECT * FROM product_variants WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $variantsResult = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($variantsResult)) {
        $variants[] = $row;
    }

    $categories = [];
    $stmt = mysqli_prepare(
        $conn,
        "SELECT c.id, c.name, pc.is_primary
         FROM product_categories pc
         INNER JOIN categories c ON c.id = pc.category_id
         WHERE pc.product_id = ?
         ORDER BY pc.is_primary DESC, c.name ASC"
    );
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $categoriesResult = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($categoriesResult)) {
        $categories[] = $row;
    }

    return [
        'product' => $product,
        'details' => $details,
        'images' => $images,
        'variants' => $variants,
        'categories' => $categories,
    ];
}

/**
 * Check whether a product slug is already used by another product.
 *
 * @param mysqli $conn
 * @param string $slug
 * @param int|null $excludeId
 * @return bool
 * @throws Exception
 */
function productSlugExists($conn, $slug, $excludeId = null)
{
    if ($excludeId !== null) {
        $sql = "SELECT id FROM products WHERE slug = ? AND id != ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'si', $slug, $excludeId);
    } else {
        $sql = "SELECT id FROM products WHERE slug = ? LIMIT 1";
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
 * Make sure every category id in the array actually exists.
 * Throws on the first missing id.
 *
 * @param mysqli $conn
 * @param int[] $categoryIds
 * @throws Exception
 * @throws InvalidArgumentException
 */
function assertCategoriesExist($conn, array $categoryIds)
{
    foreach ($categoryIds as $categoryId) {
        $sql = "SELECT id FROM categories WHERE id = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        $categoryId = (int) $categoryId;
        mysqli_stmt_bind_param($stmt, 'i', $categoryId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) === 0) {
            throw new InvalidArgumentException("Category ID {$categoryId} does not exist.");
        }
    }
}