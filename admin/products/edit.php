<?php require_once __DIR__ . '/../../config/database.php'; ?>
<?php require_once __DIR__ . '/index.php'; ?>

<?php

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