<?php require_once __DIR__ . '/../config/database.php'; ?>

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


/**
 * Create a new product, along with its details, category links, images
 * and (optionally) variants, all in a single transaction.
 *
 * Expected shape of $data:
 * [
 *   'title'              => string (required),
 *   'slug'               => string (required, unique),
 *   'short_description'  => string|null,
 *   'description'        => string|null,
 *   'base_price'         => float (required),
 *   'base_sale_price'    => float|null,
 *   'has_variants'       => bool,
 *   'featured'           => bool,
 *   'bestseller'         => bool,
 *   'trending'           => bool,
 *   'status'             => string, one of PRODUCT_STATUSES,
 *   'meta_title'         => string|null,
 *   'meta_description'   => string|null,
 *
 *   'details' => [                       // optional
 *     'ingredients' => string|null, 'benefits' => string|null,
 *     'directions' => string|null, 'dosage' => string|null,
 *     'precautions' => string|null, 'manufacturer' => string|null,
 *     'country_of_origin' => string|null, 'shelf_life' => string|null,
 *   ],
 *
 *   'category_ids' => int[],             // required, at least 1
 *   'primary_category_id' => int|null,   // must be in category_ids; defaults to the first
 *
 *   'images' => [                        // optional
 *     ['image' => string, 'alt_text' => string|null, 'is_primary' => bool, 'sort_order' => int],
 *     ...
 *   ],
 *
 *   'variants' => [                      // required if has_variants = true
 *     ['variant_name' => string, 'sku' => string, 'price' => float,
 *      'sale_price' => float|null, 'stock' => int, 'weight_grams' => int|null,
 *      'is_default' => bool, 'status' => string, 'sort_order' => int],
 *     ...
 *   ],
 * ]
 *
 * @param mysqli $conn
 * @param array $data
 * @return int Newly created product id.
 * @throws Exception
 * @throws InvalidArgumentException
 */
function addProduct($conn, array $data)
{
    $title = trim($data['title'] ?? '');
    $slug = trim($data['slug'] ?? '');
    $status = $data['status'] ?? 'Draft';
    $basePrice = $data['base_price'] ?? null;
    $categoryIds = array_values(array_unique(array_map('intval', $data['category_ids'] ?? [])));

    if ($title === '') {
        throw new InvalidArgumentException('Product title is required.');
    }
    if ($slug === '') {
        throw new InvalidArgumentException('Product slug is required.');
    }
    if (!in_array($status, PRODUCT_STATUSES, true)) {
        throw new InvalidArgumentException('Invalid status. Allowed values: ' . implode(', ', PRODUCT_STATUSES));
    }
    if ($basePrice === null || !is_numeric($basePrice) || $basePrice < 0) {
        throw new InvalidArgumentException('A valid base price is required.');
    }
    if (empty($categoryIds)) {
        throw new InvalidArgumentException('At least one category is required.');
    }

    $hasVariants = !empty($data['has_variants']) ? 1 : 0;
    $variants = $data['variants'] ?? [];
    if ($hasVariants && empty($variants)) {
        throw new InvalidArgumentException('At least one variant is required when "has variants" is enabled.');
    }

    if (productSlugExists($conn, $slug)) {
        throw new InvalidArgumentException('A product with this slug already exists.');
    }

    assertCategoriesExist($conn, $categoryIds);

    // Work out which category is primary.
    $primaryCategoryId = isset($data['primary_category_id']) ? (int) $data['primary_category_id'] : $categoryIds[0];
    if (!in_array($primaryCategoryId, $categoryIds, true)) {
        throw new InvalidArgumentException('Primary category must be one of the selected categories.');
    }

    $basePrice = (float) $basePrice;
    $baseSalePrice = isset($data['base_sale_price']) && $data['base_sale_price'] !== '' ? (float) $data['base_sale_price'] : null;
    $featured = !empty($data['featured']) ? 1 : 0;
    $bestseller = !empty($data['bestseller']) ? 1 : 0;
    $trending = !empty($data['trending']) ? 1 : 0;
    $shortDescription = $data['short_description'] ?? null;
    $description = $data['description'] ?? null;
    $metaTitle = $data['meta_title'] ?? null;
    $metaDescription = $data['meta_description'] ?? null;

    mysqli_begin_transaction($conn);

    try {
        $sql = "INSERT INTO products
                (title, slug, short_description, description, base_price, base_sale_price,
                 has_variants, featured, bestseller, trending, status, meta_title, meta_description,
                 created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param(
            $stmt,
            'ssssddiiiisss',
            $title,
            $slug,
            $shortDescription,
            $description,
            $basePrice,
            $baseSalePrice,
            $hasVariants,
            $featured,
            $bestseller,
            $trending,
            $status,
            $metaTitle,
            $metaDescription
        );
        if (!mysqli_stmt_execute($stmt)) {
            if (mysqli_errno($conn) === 1062) {
                throw new InvalidArgumentException('A product with this slug already exists.');
            }
            throw new Exception('Error adding product: ' . mysqli_error($conn));
        }
        $productId = mysqli_insert_id($conn);

        // Optional details row.
        if (!empty($data['details'])) {
            $d = $data['details'];
            $sql = "INSERT INTO product_details
                    (product_id, ingredients, benefits, directions, dosage, precautions, manufacturer, country_of_origin, shelf_life, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception("Error preparing statement: " . mysqli_error($conn));
            }
            $ingredients = $d['ingredients'] ?? null;
            $benefits = $d['benefits'] ?? null;
            $directions = $d['directions'] ?? null;
            $dosage = $d['dosage'] ?? null;
            $precautions = $d['precautions'] ?? null;
            $manufacturer = $d['manufacturer'] ?? null;
            $countryOfOrigin = $d['country_of_origin'] ?? 'India';
            $shelfLife = $d['shelf_life'] ?? null;
            mysqli_stmt_bind_param(
                $stmt,
                'issssssss',
                $productId,
                $ingredients,
                $benefits,
                $directions,
                $dosage,
                $precautions,
                $manufacturer,
                $countryOfOrigin,
                $shelfLife
            );
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Error adding product details: ' . mysqli_error($conn));
            }
        }

        // Category links.
        $sql = "INSERT INTO product_categories (product_id, category_id, is_primary) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        foreach ($categoryIds as $categoryId) {
            $isPrimary = ($categoryId === $primaryCategoryId) ? 1 : 0;
            mysqli_stmt_bind_param($stmt, 'iii', $productId, $categoryId, $isPrimary);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Error linking category: ' . mysqli_error($conn));
            }
        }

        // Images.
        if (!empty($data['images'])) {
            $sql = "INSERT INTO product_images (product_id, image, alt_text, is_primary, sort_order, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception("Error preparing statement: " . mysqli_error($conn));
            }
            foreach ($data['images'] as $img) {
                $image = trim($img['image'] ?? '');
                if ($image === '') {
                    continue;
                }
                $altText = $img['alt_text'] ?? null;
                $isPrimary = !empty($img['is_primary']) ? 1 : 0;
                $sortOrder = (int) ($img['sort_order'] ?? 0);
                mysqli_stmt_bind_param($stmt, 'issii', $productId, $image, $altText, $isPrimary, $sortOrder);
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception('Error adding product image: ' . mysqli_error($conn));
                }
            }
        }

        // Variants (only meaningful when has_variants = true, but stored if provided regardless).
        if (!empty($variants)) {
            $sql = "INSERT INTO product_variants
                    (product_id, variant_name, sku, price, sale_price, stock, weight_grams, is_default, status, sort_order, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception("Error preparing statement: " . mysqli_error($conn));
            }
            foreach ($variants as $v) {
                $variantName = trim($v['variant_name'] ?? '');
                $sku = trim($v['sku'] ?? '');
                if ($variantName === '' || $sku === '') {
                    throw new InvalidArgumentException('Each variant needs a name and SKU.');
                }
                $vStatus = $v['status'] ?? 'Active';
                if (!in_array($vStatus, PRODUCT_VARIANT_STATUSES, true)) {
                    throw new InvalidArgumentException('Invalid variant status: ' . $vStatus);
                }
                $price = (float) ($v['price'] ?? 0);
                $salePrice = isset($v['sale_price']) && $v['sale_price'] !== '' ? (float) $v['sale_price'] : null;
                $stock = (int) ($v['stock'] ?? 0);
                $weightGrams = isset($v['weight_grams']) && $v['weight_grams'] !== '' ? (int) $v['weight_grams'] : null;
                $isDefault = !empty($v['is_default']) ? 1 : 0;
                $sortOrder = (int) ($v['sort_order'] ?? 0);
                mysqli_stmt_bind_param(
                    $stmt,
                    'issddiiisi',
                    $productId,
                    $variantName,
                    $sku,
                    $price,
                    $salePrice,
                    $stock,
                    $weightGrams,
                    $isDefault,
                    $vStatus,
                    $sortOrder
                );
                if (!mysqli_stmt_execute($stmt)) {
                    if (mysqli_errno($conn) === 1062) {
                        throw new InvalidArgumentException("Variant SKU '{$sku}' already exists.");
                    }
                    throw new Exception('Error adding product variant: ' . mysqli_error($conn));
                }
            }
        }

        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }

    return $productId;
}


/**
 * Thrown when a product delete is blocked pending confirmation because
 * the product has order history. Deleting is still safe (past orders
 * keep their own snapshot of product_name/sku/price in order_items, and
 * order_items.product_id is set to NULL automatically), but the admin
 * should be told the live product link will be gone.
 */
class ProductHasOrderHistoryException extends InvalidArgumentException
{
    public int $orderItemCount;

    public function __construct(int $orderItemCount, string $message)
    {
        parent::__construct($message);
        $this->orderItemCount = $orderItemCount;
    }
}

/**
 * Delete a product.
 *
 * product_details, product_images, product_variants, reviews, wishlist,
 * and cart rows are removed automatically (ON DELETE CASCADE). Past
 * order_items rows are kept for order history but have their
 * product_id set to NULL (ON DELETE SET NULL) -- they already store
 * their own product_name/sku/price snapshot, so historical orders
 * remain intact and readable, they just lose the clickable link back
 * to this product.
 *
 * If the product appears in any past orders, confirmation is required
 * first (same two-step pattern as categories):
 *
 *   1. Call with $force = false (default). If the product has order
 *      history, nothing is deleted -- a ProductHasOrderHistoryException
 *      is thrown so the caller can warn the user and ask to confirm.
 *   2. Call again with $force = true to actually delete.
 *
 * A product with no order history is deleted immediately regardless
 * of $force.
 *
 * @param mysqli $conn
 * @param int $id
 * @param bool $force
 * @return bool
 * @throws Exception
 * @throws InvalidArgumentException
 * @throws ProductHasOrderHistoryException
 */
function deleteProduct($conn, $id, $force = false)
{
    $id = (int) $id;

    $existing = getProductById($conn, $id);
    $productRow = mysqli_fetch_assoc($existing);
    if (!$productRow) {
        throw new InvalidArgumentException('Product not found.');
    }

    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS cnt FROM order_items WHERE product_id = ?");
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error checking order history: " . mysqli_error($conn));
    }
    $result = mysqli_stmt_get_result($stmt);
    $orderItemCount = (int) mysqli_fetch_assoc($result)['cnt'];

    if ($orderItemCount > 0 && !$force) {
        throw new ProductHasOrderHistoryException(
            $orderItemCount,
            'This product appears in ' . $orderItemCount . ' past order item' . ($orderItemCount === 1 ? '' : 's') .
                '. Those orders will keep their own record of the product name, price and SKU, but the live ' .
                'link back to this product will be removed and it will disappear from the store. Do you still want to delete it?'
        );
    }

    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error deleting product: ' . mysqli_error($conn));
    }

    if (mysqli_stmt_affected_rows($stmt) === 0) {
        throw new InvalidArgumentException('Product not found.');
    }

    return true;
}


/**
 * Update a product's core fields, details, and category links.
 * Images and variants are managed separately via the focused functions
 * below (addProductImage, deleteProductImage, addProductVariant, etc.)
 * since admin UIs typically add/remove those one at a time rather than
 * replacing the whole set on every save.
 *
 * $data uses the same shape as addProduct(), minus 'images'/'variants'.
 *
 * @param mysqli $conn
 * @param int $id
 * @param array $data
 * @return bool
 * @throws Exception
 * @throws InvalidArgumentException
 */
function updateProduct($conn, $id, array $data)
{
    $id = (int) $id;

    $existing = getProductById($conn, $id);
    if (mysqli_num_rows($existing) === 0) {
        throw new InvalidArgumentException('Product not found.');
    }

    $title = trim($data['title'] ?? '');
    $slug = trim($data['slug'] ?? '');
    $status = $data['status'] ?? 'Draft';
    $basePrice = $data['base_price'] ?? null;
    $categoryIds = array_values(array_unique(array_map('intval', $data['category_ids'] ?? [])));

    if ($title === '') {
        throw new InvalidArgumentException('Product title is required.');
    }
    if ($slug === '') {
        throw new InvalidArgumentException('Product slug is required.');
    }
    if (!in_array($status, PRODUCT_STATUSES, true)) {
        throw new InvalidArgumentException('Invalid status. Allowed values: ' . implode(', ', PRODUCT_STATUSES));
    }
    if ($basePrice === null || !is_numeric($basePrice) || $basePrice < 0) {
        throw new InvalidArgumentException('A valid base price is required.');
    }
    if (empty($categoryIds)) {
        throw new InvalidArgumentException('At least one category is required.');
    }

    if (productSlugExists($conn, $slug, $id)) {
        throw new InvalidArgumentException('A product with this slug already exists.');
    }

    assertCategoriesExist($conn, $categoryIds);

    $primaryCategoryId = isset($data['primary_category_id']) ? (int) $data['primary_category_id'] : $categoryIds[0];
    if (!in_array($primaryCategoryId, $categoryIds, true)) {
        throw new InvalidArgumentException('Primary category must be one of the selected categories.');
    }

    $hasVariants = !empty($data['has_variants']) ? 1 : 0;
    $basePrice = (float) $basePrice;
    $baseSalePrice = isset($data['base_sale_price']) && $data['base_sale_price'] !== '' ? (float) $data['base_sale_price'] : null;
    $featured = !empty($data['featured']) ? 1 : 0;
    $bestseller = !empty($data['bestseller']) ? 1 : 0;
    $trending = !empty($data['trending']) ? 1 : 0;
    $shortDescription = $data['short_description'] ?? null;
    $description = $data['description'] ?? null;
    $metaTitle = $data['meta_title'] ?? null;
    $metaDescription = $data['meta_description'] ?? null;

    mysqli_begin_transaction($conn);

    try {
        $sql = "UPDATE products
                SET title = ?, slug = ?, short_description = ?, description = ?, base_price = ?,
                    base_sale_price = ?, has_variants = ?, featured = ?, bestseller = ?, trending = ?,
                    status = ?, meta_title = ?, meta_description = ?, updated_at = NOW()
                WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param(
            $stmt,
            'ssssddiiiisssi',
            $title,
            $slug,
            $shortDescription,
            $description,
            $basePrice,
            $baseSalePrice,
            $hasVariants,
            $featured,
            $bestseller,
            $trending,
            $status,
            $metaTitle,
            $metaDescription,
            $id
        );
        if (!mysqli_stmt_execute($stmt)) {
            if (mysqli_errno($conn) === 1062) {
                throw new InvalidArgumentException('A product with this slug already exists.');
            }
            throw new Exception('Error updating product: ' . mysqli_error($conn));
        }

        // Upsert details.
        if (isset($data['details'])) {
            $d = $data['details'];
            $ingredients = $d['ingredients'] ?? null;
            $benefits = $d['benefits'] ?? null;
            $directions = $d['directions'] ?? null;
            $dosage = $d['dosage'] ?? null;
            $precautions = $d['precautions'] ?? null;
            $manufacturer = $d['manufacturer'] ?? null;
            $countryOfOrigin = $d['country_of_origin'] ?? 'India';
            $shelfLife = $d['shelf_life'] ?? null;

            $sql = "INSERT INTO product_details
                    (product_id, ingredients, benefits, directions, dosage, precautions, manufacturer, country_of_origin, shelf_life, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE
                        ingredients = VALUES(ingredients), benefits = VALUES(benefits),
                        directions = VALUES(directions), dosage = VALUES(dosage),
                        precautions = VALUES(precautions), manufacturer = VALUES(manufacturer),
                        country_of_origin = VALUES(country_of_origin), shelf_life = VALUES(shelf_life),
                        updated_at = NOW()";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception("Error preparing statement: " . mysqli_error($conn));
            }
            mysqli_stmt_bind_param(
                $stmt,
                'issssssss',
                $id,
                $ingredients,
                $benefits,
                $directions,
                $dosage,
                $precautions,
                $manufacturer,
                $countryOfOrigin,
                $shelfLife
            );
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Error updating product details: ' . mysqli_error($conn));
            }
        }

        // Sync category links: remove ones no longer selected, add new
        // ones, and make sure exactly one is marked primary.
        $sql = "DELETE FROM product_categories WHERE product_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Error updating product categories: ' . mysqli_error($conn));
        }

        $sql = "INSERT INTO product_categories (product_id, category_id, is_primary) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        foreach ($categoryIds as $categoryId) {
            $isPrimary = ($categoryId === $primaryCategoryId) ? 1 : 0;
            mysqli_stmt_bind_param($stmt, 'iii', $id, $categoryId, $isPrimary);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Error linking category: ' . mysqli_error($conn));
            }
        }

        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }

    return true;
}

/**
 * Add a single image to a product.
 *
 * @param mysqli $conn
 * @param int $productId
 * @param string $image
 * @param string|null $altText
 * @param bool $isPrimary  If true, unsets is_primary on the product's other images first.
 * @param int $sortOrder
 * @return int New image id.
 * @throws Exception
 * @throws InvalidArgumentException
 */
function addProductImage($conn, $productId, $image, $altText = null, $isPrimary = false, $sortOrder = 0)
{
    $productId = (int) $productId;
    $image = trim($image);
    if ($image === '') {
        throw new InvalidArgumentException('Image path/URL is required.');
    }

    mysqli_begin_transaction($conn);
    try {
        if ($isPrimary) {
            $stmt = mysqli_prepare($conn, "UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $productId);
            mysqli_stmt_execute($stmt);
        }

        $isPrimaryInt = $isPrimary ? 1 : 0;
        $sortOrder = (int) $sortOrder;
        $sql = "INSERT INTO product_images (product_id, image, alt_text, is_primary, sort_order, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'issii', $productId, $image, $altText, $isPrimaryInt, $sortOrder);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Error adding product image: ' . mysqli_error($conn));
        }
        $imageId = mysqli_insert_id($conn);

        mysqli_commit($conn);
        return $imageId;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }
}

/**
 * Delete a single product image.
 *
 * @param mysqli $conn
 * @param int $imageId
 * @return bool
 * @throws Exception
 * @throws InvalidArgumentException
 */
function deleteProductImage($conn, $imageId)
{
    $imageId = (int) $imageId;
    $sql = "DELETE FROM product_images WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 'i', $imageId);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error deleting product image: ' . mysqli_error($conn));
    }
    if (mysqli_stmt_affected_rows($stmt) === 0) {
        throw new InvalidArgumentException('Image not found.');
    }
    return true;
}

/**
 * Add a variant to a product.
 *
 * @param mysqli $conn
 * @param int $productId
 * @param array $variant  ['variant_name','sku','price','sale_price','stock','weight_grams','is_default','status','sort_order']
 * @return int New variant id.
 * @throws Exception
 * @throws InvalidArgumentException
 */
function addProductVariant($conn, $productId, array $variant)
{
    $productId = (int) $productId;
    $variantName = trim($variant['variant_name'] ?? '');
    $sku = trim($variant['sku'] ?? '');
    $status = $variant['status'] ?? 'Active';

    if ($variantName === '' || $sku === '') {
        throw new InvalidArgumentException('Variant name and SKU are required.');
    }
    if (!in_array($status, PRODUCT_VARIANT_STATUSES, true)) {
        throw new InvalidArgumentException('Invalid variant status: ' . $status);
    }

    $price = (float) ($variant['price'] ?? 0);
    $salePrice = isset($variant['sale_price']) && $variant['sale_price'] !== '' ? (float) $variant['sale_price'] : null;
    $stock = (int) ($variant['stock'] ?? 0);
    $weightGrams = isset($variant['weight_grams']) && $variant['weight_grams'] !== '' ? (int) $variant['weight_grams'] : null;
    $isDefault = !empty($variant['is_default']) ? 1 : 0;
    $sortOrder = (int) ($variant['sort_order'] ?? 0);

    mysqli_begin_transaction($conn);
    try {
        if ($isDefault) {
            $stmt = mysqli_prepare($conn, "UPDATE product_variants SET is_default = 0 WHERE product_id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $productId);
            mysqli_stmt_execute($stmt);
        }

        $sql = "INSERT INTO product_variants
                (product_id, variant_name, sku, price, sale_price, stock, weight_grams, is_default, status, sort_order, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param(
            $stmt,
            'issddiiisi',
            $productId,
            $variantName,
            $sku,
            $price,
            $salePrice,
            $stock,
            $weightGrams,
            $isDefault,
            $status,
            $sortOrder
        );
        if (!mysqli_stmt_execute($stmt)) {
            if (mysqli_errno($conn) === 1062) {
                throw new InvalidArgumentException("Variant SKU '{$sku}' already exists.");
            }
            throw new Exception('Error adding product variant: ' . mysqli_error($conn));
        }
        $variantId = mysqli_insert_id($conn);

        mysqli_commit($conn);
        return $variantId;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }
}

/**
 * Update an existing variant.
 *
 * @param mysqli $conn
 * @param int $variantId
 * @param array $variant  Same shape as addProductVariant()'s $variant.
 * @return bool
 * @throws Exception
 * @throws InvalidArgumentException
 */
function updateProductVariant($conn, $variantId, array $variant)
{
    $variantId = (int) $variantId;
    $variantName = trim($variant['variant_name'] ?? '');
    $sku = trim($variant['sku'] ?? '');
    $status = $variant['status'] ?? 'Active';

    if ($variantName === '' || $sku === '') {
        throw new InvalidArgumentException('Variant name and SKU are required.');
    }
    if (!in_array($status, PRODUCT_VARIANT_STATUSES, true)) {
        throw new InvalidArgumentException('Invalid variant status: ' . $status);
    }

    $price = (float) ($variant['price'] ?? 0);
    $salePrice = isset($variant['sale_price']) && $variant['sale_price'] !== '' ? (float) $variant['sale_price'] : null;
    $stock = (int) ($variant['stock'] ?? 0);
    $weightGrams = isset($variant['weight_grams']) && $variant['weight_grams'] !== '' ? (int) $variant['weight_grams'] : null;
    $sortOrder = (int) ($variant['sort_order'] ?? 0);

    $sql = "UPDATE product_variants
            SET variant_name = ?, sku = ?, price = ?, sale_price = ?, stock = ?, weight_grams = ?,
                status = ?, sort_order = ?, updated_at = NOW()
            WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param(
        $stmt,
        'ssddiisii',
        $variantName,
        $sku,
        $price,
        $salePrice,
        $stock,
        $weightGrams,
        $status,
        $sortOrder,
        $variantId
    );
    if (!mysqli_stmt_execute($stmt)) {
        if (mysqli_errno($conn) === 1062) {
            throw new InvalidArgumentException("Variant SKU '{$sku}' already exists.");
        }
        throw new Exception('Error updating product variant: ' . mysqli_error($conn));
    }
    if (mysqli_stmt_affected_rows($stmt) === 0) {
        // Either not found, or no actual change -- check existence to disambiguate.
        $check = mysqli_prepare($conn, "SELECT id FROM product_variants WHERE id = ?");
        mysqli_stmt_bind_param($check, 'i', $variantId);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);
        if (mysqli_stmt_num_rows($check) === 0) {
            throw new InvalidArgumentException('Variant not found.');
        }
    }

    return true;
}

/**
 * Delete a single variant.
 *
 * @param mysqli $conn
 * @param int $variantId
 * @return bool
 * @throws Exception
 * @throws InvalidArgumentException
 */
function deleteProductVariant($conn, $variantId)
{
    $variantId = (int) $variantId;
    $sql = "DELETE FROM product_variants WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 'i', $variantId);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error deleting product variant: ' . mysqli_error($conn));
    }
    if (mysqli_stmt_affected_rows($stmt) === 0) {
        throw new InvalidArgumentException('Variant not found.');
    }
    return true;
}
