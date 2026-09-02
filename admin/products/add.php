<?php require_once __DIR__ . '/../../config/database.php'; ?>
<?php require_once __DIR__ . '/index.php'; ?>

<?php

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