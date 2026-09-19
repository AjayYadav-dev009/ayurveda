<?php include __DIR__ . '/../../function/product.php'; ?>
<?php include __DIR__ . '/../../function/category.php'; ?>
<?php include __DIR__ . '/../../function/helper.php'; ?>
<?php require_once __DIR__ . '/../../config/database.php'; ?>
<?php include __DIR__ . '/../../includes/auth.php'; ?>

<?php
$errors = [];

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    die('Invalid product id.');
}

$relations = getProductWithRelations($conn, $id);
if ($relations['product'] === null) {
    die('Product not found.');
}

$categoriesResult = getCategories($conn);
$categories = [];
while ($row = mysqli_fetch_assoc($categoriesResult)) {
    $categories[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ---- 1. Update the product's core fields, details, categories ----

    $slug = null;
    try {
        $slug = createSlug($_POST['title'] ?? '');
    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    }

    $metaTitle = null;
    if (trim($_POST['meta_title'] ?? '') !== '') {
        try {
            $metaTitle = createMetaTitle($_POST['meta_title']);
        } catch (InvalidArgumentException $e) {
            $errors[] = $e->getMessage();
        }
    }

    $metaDescription = null;
    if (trim($_POST['meta_description'] ?? '') !== '') {
        try {
            $metaDescription = createMetaDescription($_POST['meta_description']);
        } catch (InvalidArgumentException $e) {
            $errors[] = $e->getMessage();
        }
    }

    $categoryIds = array_map('intval', $_POST['category_ids'] ?? []);

    if (empty($errors)) {
        $data = [
            'title' => $_POST['title'] ?? '',
            'slug' => $slug,
            'short_description' => $_POST['short_description'] ?? null,
            'description' => $_POST['description'] ?? null,
            'base_price' => $_POST['base_price'] ?? null,
            'base_sale_price' => $_POST['base_sale_price'] ?? '',
            'has_variants' => isset($_POST['has_variants']),
            'featured' => isset($_POST['featured']),
            'bestseller' => isset($_POST['bestseller']),
            'trending' => isset($_POST['trending']),
            'status' => $_POST['status'] ?? 'Draft',
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'details' => [
                'ingredients' => $_POST['ingredients'] ?? null,
                'benefits' => $_POST['benefits'] ?? null,
                'directions' => $_POST['directions'] ?? null,
                'dosage' => $_POST['dosage'] ?? null,
                'precautions' => $_POST['precautions'] ?? null,
                'manufacturer' => $_POST['manufacturer'] ?? null,
                'country_of_origin' => $_POST['country_of_origin'] ?? 'India',
                'shelf_life' => $_POST['shelf_life'] ?? null,
            ],
            'category_ids' => $categoryIds,
            'primary_category_id' => isset($_POST['primary_category_id']) ? (int) $_POST['primary_category_id'] : null,
        ];

        try {
            updateProduct($conn, $id, $data);
        } catch (InvalidArgumentException $e) {
            $errors[] = $e->getMessage();
        } catch (Exception $e) {
            $errors[] = 'Something went wrong while updating the product.';
        }
    }

    // ---- 2. Images: deletions, then additions ----
    // (No updateProductImage() exists, so existing images can only be
    // deleted here, not edited in place -- add a replacement instead.)

    if (empty($errors)) {
        foreach ($_POST['delete_images'] ?? [] as $imageId) {
            try {
                deleteProductImage($conn, (int) $imageId);
            } catch (InvalidArgumentException $e) {
                $errors[] = $e->getMessage();
            } catch (Exception $e) {
                $errors[] = 'Something went wrong while deleting an image.';
            }
        }
    }

    if (empty($errors)) {
        // PHP groups a nested file input like new_images[i][file] as
        // $_FILES['new_images']['name'][i]['file'], ['tmp_name'][i]['file'],
        // etc — reshape that into one plain file array per image slot.
        $newImageFiles = [];
        foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $attr) {
            foreach ($_FILES['new_images'][$attr] ?? [] as $i => $group) {
                $newImageFiles[$i][$attr] = $group['file'] ?? null;
            }
        }

        foreach ($newImageFiles as $i => $file) {
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue; // No file chosen for this slot.
            }
            $meta = $_POST['new_images'][$i] ?? [];
            try {
                $relativePath = uploadProductImage($file, $id);
                addProductImage(
                    $conn,
                    $id,
                    $relativePath,
                    trim($meta['alt_text'] ?? '') !== '' ? $meta['alt_text'] : null,
                    isset($meta['is_primary']),
                    (int) ($meta['sort_order'] ?? 0)
                );
            } catch (InvalidArgumentException $e) {
                $errors[] = 'Image ' . ($i + 1) . ': ' . $e->getMessage();
            } catch (Exception $e) {
                $errors[] = 'Something went wrong while adding image ' . ($i + 1) . '.';
            }
        }
    }

    // ---- 3. Variants: deletions, then edits to existing, then new ones ----

    if (empty($errors)) {
        foreach ($_POST['delete_variants'] ?? [] as $variantId) {
            try {
                deleteProductVariant($conn, (int) $variantId);
            } catch (InvalidArgumentException $e) {
                $errors[] = $e->getMessage();
            } catch (Exception $e) {
                $errors[] = 'Something went wrong while deleting a variant.';
            }
        }
    }

    if (empty($errors)) {
        foreach ($_POST['existing_variants'] ?? [] as $variantId => $v) {
            if (in_array($variantId, $_POST['delete_variants'] ?? [])) {
                continue;
            }
            try {
                updateProductVariant($conn, (int) $variantId, [
                    'variant_name' => $v['variant_name'] ?? '',
                    'sku' => $v['sku'] ?? '',
                    'price' => $v['price'] ?? 0,
                    'sale_price' => $v['sale_price'] ?? '',
                    'stock' => $v['stock'] ?? 0,
                    'weight_grams' => $v['weight_grams'] ?? '',
                    'is_default' => isset($v['is_default']),
                    'status' => $v['status'] ?? 'Active',
                    'sort_order' => $v['sort_order'] ?? 0,
                ]);
            } catch (InvalidArgumentException $e) {
                $errors[] = $e->getMessage();
            } catch (Exception $e) {
                $errors[] = 'Something went wrong while updating a variant.';
            }
        }
    }

    if (empty($errors)) {
        foreach ($_POST['new_variants'] ?? [] as $v) {
            if (trim($v['variant_name'] ?? '') !== '' && trim($v['sku'] ?? '') !== '') {
                try {
                    addProductVariant($conn, $id, [
                        'variant_name' => $v['variant_name'],
                        'sku' => $v['sku'],
                        'price' => $v['price'] ?? 0,
                        'sale_price' => $v['sale_price'] ?? '',
                        'stock' => $v['stock'] ?? 0,
                        'weight_grams' => $v['weight_grams'] ?? '',
                        'is_default' => isset($v['is_default']),
                        'status' => $v['status'] ?? 'Active',
                        'sort_order' => $v['sort_order'] ?? 0,
                    ]);
                } catch (InvalidArgumentException $e) {
                    $errors[] = $e->getMessage();
                } catch (Exception $e) {
                    $errors[] = 'Something went wrong while adding a variant.';
                }
            }
        }
    }

    if (empty($errors)) {
        header('Location: index.php?updated=1');
        exit;
    }

    // Something failed partway through -- reload current DB state so the
    // form reflects whatever did or didn't get saved.
    $relations = getProductWithRelations($conn, $id);
}

$product = $relations['product'];
$details = $relations['details'] ?? [];
$images = $relations['images'];
$variants = $relations['variants'];
$productCategoryIds = array_column($relations['categories'], 'id');
$primaryCategoryId = null;
foreach ($relations['categories'] as $cat) {
    if (!empty($cat['is_primary'])) {
        $primaryCategoryId = (int) $cat['id'];
    }
}

// Helper to pick POSTed value on validation failure, otherwise DB value.
$val = function ($postKey, $dbValue) {
    return $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST[$postKey] ?? '') : ($dbValue ?? '');
};
?>

<?php if (!empty($errors)) : ?>
    <ul>
        <?php foreach ($errors as $error) : ?>
            <li><?php echo htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<style>
    form {
        max-width: 520px;
        margin: 0 auto;
        font-family: sans-serif;
        font-size: 0.95rem;
    }

    form label {
        display: block;
        margin-top: 14px;
        margin-bottom: 5px;
        font-weight: 600;
        color: #333;
    }

    form select,
    form input[type="text"],
    form textarea {
        width: 100%;
        padding: 9px 12px;
        font-size: 0.95rem;
        font-family: inherit;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    form select:focus,
    form input[type="text"]:focus,
    form textarea:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
    }

    form textarea {
        min-height: 80px;
        resize: vertical;
    }

    form input[type="submit"] {
        margin-top: 20px;
        padding: 10px 24px;
        font-size: 1rem;
        font-weight: 600;
        color: #fff;
        background-color: #28a745;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    form input[type="submit"]:hover {
        background-color: #218838;
    }

    form input[type="submit"]:focus-visible {
        outline: 2px solid #28a745;
        outline-offset: 2px;
    }
</style>

<form method="POST" action="edit.php?id=<?php echo (int) $id; ?>" enctype="multipart/form-data">

    <h3>Basic info</h3>

    <label for="title">Title</label><br>
    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($val('title', $product['title'])); ?>" required><br>
    <small>The URL slug will be regenerated from the title. Current slug: <?php echo htmlspecialchars($product['slug']); ?></small><br>

    <label for="short_description">Short description</label><br>
    <textarea id="short_description" name="short_description" rows="2" cols="50"><?php echo htmlspecialchars($val('short_description', $product['short_description'])); ?></textarea><br>

    <label for="description">Description</label><br>
    <textarea id="description" name="description" rows="5" cols="50"><?php echo htmlspecialchars($val('description', $product['description'])); ?></textarea><br>

    <label for="base_price">Base price</label><br>
    <input type="number" step="0.01" min="0" id="base_price" name="base_price" value="<?php echo htmlspecialchars($val('base_price', $product['base_price'])); ?>" required><br>

    <label for="base_sale_price">Base sale price</label><br>
    <input type="number" step="0.01" min="0" id="base_sale_price" name="base_sale_price" value="<?php echo htmlspecialchars($val('base_sale_price', $product['base_sale_price'])); ?>"><br>

    <label for="status">Status</label><br>
    <select id="status" name="status">
        <?php $currentStatus = $val('status', $product['status']); ?>
        <?php foreach (PRODUCT_STATUSES as $statusOption) : ?>
            <option value="<?php echo htmlspecialchars($statusOption); ?>" <?php echo ($currentStatus === $statusOption) ? 'selected' : ''; ?>><?php echo htmlspecialchars($statusOption); ?></option>
        <?php endforeach; ?>
    </select><br>

    <label>
        <input type="checkbox" name="featured" value="1" <?php echo ($_SERVER['REQUEST_METHOD'] === 'POST' ? isset($_POST['featured']) : (int) $product['featured'] === 1) ? 'checked' : ''; ?>>
        Featured
    </label><br>

    <label>
        <input type="checkbox" name="bestseller" value="1" <?php echo ($_SERVER['REQUEST_METHOD'] === 'POST' ? isset($_POST['bestseller']) : (int) $product['bestseller'] === 1) ? 'checked' : ''; ?>>
        Bestseller
    </label><br>

    <label>
        <input type="checkbox" name="trending" value="1" <?php echo ($_SERVER['REQUEST_METHOD'] === 'POST' ? isset($_POST['trending']) : (int) $product['trending'] === 1) ? 'checked' : ''; ?>>
        Trending
    </label><br>

    <label for="meta_title">Meta title</label><br>
    <input type="text" id="meta_title" name="meta_title" value="<?php echo htmlspecialchars($val('meta_title', $product['meta_title'])); ?>"><br>

    <label for="meta_description">Meta description</label><br>
    <textarea id="meta_description" name="meta_description" rows="2" cols="50"><?php echo htmlspecialchars($val('meta_description', $product['meta_description'])); ?></textarea><br>
    <small>Auto-trimmed to 200 characters if longer.</small><br>

    <h3>Categories</h3>

    <?php $checkedCategoryIds = $_SERVER['REQUEST_METHOD'] === 'POST' ? array_map('intval', $_POST['category_ids'] ?? []) : $productCategoryIds; ?>
    <?php foreach ($categories as $category) : ?>
        <label>
            <input type="checkbox" name="category_ids[]" value="<?php echo (int) $category['id']; ?>" <?php echo in_array((int) $category['id'], $checkedCategoryIds, true) ? 'checked' : ''; ?>>
            <?php echo htmlspecialchars($category['name']); ?>
        </label><br>
    <?php endforeach; ?>

    <label for="primary_category_id">Primary category</label><br>
    <?php $selectedPrimary = $_SERVER['REQUEST_METHOD'] === 'POST' ? (int) ($_POST['primary_category_id'] ?? 0) : $primaryCategoryId; ?>
    <select id="primary_category_id" name="primary_category_id" required>
        <option value="">-- select --</option>
        <?php foreach ($categories as $category) : ?>
            <option value="<?php echo (int) $category['id']; ?>" <?php echo ((int) $category['id'] === $selectedPrimary) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
        <?php endforeach; ?>
    </select><br>

    <h3>Product details</h3>

    <label for="ingredients">Ingredients</label><br>
    <textarea id="ingredients" name="ingredients" rows="2" cols="50"><?php echo htmlspecialchars($val('ingredients', $details['ingredients'] ?? '')); ?></textarea><br>

    <label for="benefits">Benefits</label><br>
    <textarea id="benefits" name="benefits" rows="2" cols="50"><?php echo htmlspecialchars($val('benefits', $details['benefits'] ?? '')); ?></textarea><br>

    <label for="directions">Directions</label><br>
    <textarea id="directions" name="directions" rows="2" cols="50"><?php echo htmlspecialchars($val('directions', $details['directions'] ?? '')); ?></textarea><br>

    <label for="dosage">Dosage</label><br>
    <textarea id="dosage" name="dosage" rows="2" cols="50"><?php echo htmlspecialchars($val('dosage', $details['dosage'] ?? '')); ?></textarea><br>

    <label for="precautions">Precautions</label><br>
    <textarea id="precautions" name="precautions" rows="2" cols="50"><?php echo htmlspecialchars($val('precautions', $details['precautions'] ?? '')); ?></textarea><br>

    <label for="manufacturer">Manufacturer</label><br>
    <input type="text" id="manufacturer" name="manufacturer" value="<?php echo htmlspecialchars($val('manufacturer', $details['manufacturer'] ?? '')); ?>"><br>

    <label for="country_of_origin">Country of origin</label><br>
    <input type="text" id="country_of_origin" name="country_of_origin" value="<?php echo htmlspecialchars($val('country_of_origin', $details['country_of_origin'] ?? 'India')); ?>"><br>

    <label for="shelf_life">Shelf life</label><br>
    <input type="text" id="shelf_life" name="shelf_life" value="<?php echo htmlspecialchars($val('shelf_life', $details['shelf_life'] ?? '')); ?>"><br>

    <h3>Existing images</h3>

    <?php if (empty($images)) : ?>
        <p>No images yet.</p>
    <?php endif; ?>

    <?php foreach ($images as $image) : ?>
        <p>
            <img src="<?php echo htmlspecialchars(getProductImageUrl($image['image'])); ?>" alt="<?php echo htmlspecialchars($image['alt_text'] ?? ''); ?>" width="80"><br>
            <?php echo htmlspecialchars($image['image']); ?>
            <?php if ((int) $image['is_primary'] === 1) : ?>
                (primary)
            <?php endif; ?><br>
            <label>
                <input type="checkbox" name="delete_images[]" value="<?php echo (int) $image['id']; ?>">
                Delete this image
            </label>
        </p>
    <?php endforeach; ?>
    <p><small>To change an existing image's alt text or make it primary, delete it and add it again below.</small></p>

    <h4>Add new images</h4>

    <?php for ($i = 0; $i < 3; $i++) : ?>
        <p>
            New image <?php echo $i + 1; ?><br>
            <label>Image file</label>
            <input type="file" name="new_images[<?php echo $i; ?>][file]" accept="image/jpeg,image/png,image/webp,image/gif"><br>
            <label>Alt text</label>
            <input type="text" name="new_images[<?php echo $i; ?>][alt_text]"><br>
            <label>
                <input type="checkbox" name="new_images[<?php echo $i; ?>][is_primary]" value="1">
                Primary image
            </label><br>
            <label>Sort order</label>
            <input type="number" name="new_images[<?php echo $i; ?>][sort_order]" value="0">
        </p>
    <?php endfor; ?>

    <h3>Variants</h3>

    <label>
        <input type="checkbox" name="has_variants" value="1" <?php echo ($_SERVER['REQUEST_METHOD'] === 'POST' ? isset($_POST['has_variants']) : (int) $product['has_variants'] === 1) ? 'checked' : ''; ?>>
        This product has variants
    </label><br><br>

    <?php if (empty($variants)) : ?>
        <p>No variants yet.</p>
    <?php endif; ?>

    <?php foreach ($variants as $variant) : ?>
        <p>
            Variant: <?php echo htmlspecialchars($variant['variant_name']); ?> (SKU <?php echo htmlspecialchars($variant['sku']); ?>)<br>
            <label>Name</label>
            <input type="text" name="existing_variants[<?php echo (int) $variant['id']; ?>][variant_name]" value="<?php echo htmlspecialchars($variant['variant_name']); ?>"><br>
            <label>SKU</label>
            <input type="text" name="existing_variants[<?php echo (int) $variant['id']; ?>][sku]" value="<?php echo htmlspecialchars($variant['sku']); ?>"><br>
            <label>Price</label>
            <input type="number" step="0.01" min="0" name="existing_variants[<?php echo (int) $variant['id']; ?>][price]" value="<?php echo htmlspecialchars($variant['price']); ?>"><br>
            <label>Sale price</label>
            <input type="number" step="0.01" min="0" name="existing_variants[<?php echo (int) $variant['id']; ?>][sale_price]" value="<?php echo htmlspecialchars($variant['sale_price'] ?? ''); ?>"><br>
            <label>Stock</label>
            <input type="number" min="0" name="existing_variants[<?php echo (int) $variant['id']; ?>][stock]" value="<?php echo htmlspecialchars($variant['stock']); ?>"><br>
            <label>Weight (grams)</label>
            <input type="number" min="0" name="existing_variants[<?php echo (int) $variant['id']; ?>][weight_grams]" value="<?php echo htmlspecialchars($variant['weight_grams'] ?? ''); ?>"><br>
            <label>
                <input type="checkbox" name="existing_variants[<?php echo (int) $variant['id']; ?>][is_default]" value="1" <?php echo (int) $variant['is_default'] === 1 ? 'checked' : ''; ?>>
                Default variant
            </label><br>
            <label>Status</label>
            <select name="existing_variants[<?php echo (int) $variant['id']; ?>][status]">
                <?php foreach (PRODUCT_VARIANT_STATUSES as $vStatus) : ?>
                    <option value="<?php echo htmlspecialchars($vStatus); ?>" <?php echo $variant['status'] === $vStatus ? 'selected' : ''; ?>><?php echo htmlspecialchars($vStatus); ?></option>
                <?php endforeach; ?>
            </select><br>
            <label>Sort order</label>
            <input type="number" name="existing_variants[<?php echo (int) $variant['id']; ?>][sort_order]" value="<?php echo htmlspecialchars($variant['sort_order']); ?>"><br>
            <label>
                <input type="checkbox" name="delete_variants[]" value="<?php echo (int) $variant['id']; ?>">
                Delete this variant
            </label>
        </p>
    <?php endforeach; ?>

    <h4>Add new variants</h4>

    <?php for ($i = 0; $i < 3; $i++) : ?>
        <p>
            New variant <?php echo $i + 1; ?><br>
            <label>Name</label>
            <input type="text" name="new_variants[<?php echo $i; ?>][variant_name]"><br>
            <label>SKU</label>
            <input type="text" name="new_variants[<?php echo $i; ?>][sku]"><br>
            <label>Price</label>
            <input type="number" step="0.01" min="0" name="new_variants[<?php echo $i; ?>][price]"><br>
            <label>Sale price</label>
            <input type="number" step="0.01" min="0" name="new_variants[<?php echo $i; ?>][sale_price]"><br>
            <label>Stock</label>
            <input type="number" min="0" name="new_variants[<?php echo $i; ?>][stock]" value="0"><br>
            <label>Weight (grams)</label>
            <input type="number" min="0" name="new_variants[<?php echo $i; ?>][weight_grams]"><br>
            <label>
                <input type="checkbox" name="new_variants[<?php echo $i; ?>][is_default]" value="1">
                Default variant
            </label><br>
            <label>Status</label>
            <select name="new_variants[<?php echo $i; ?>][status]">
                <?php foreach (PRODUCT_VARIANT_STATUSES as $vStatus) : ?>
                    <option value="<?php echo htmlspecialchars($vStatus); ?>"><?php echo htmlspecialchars($vStatus); ?></option>
                <?php endforeach; ?>
            </select><br>
            <label>Sort order</label>
            <input type="number" name="new_variants[<?php echo $i; ?>][sort_order]" value="0">
        </p>
    <?php endfor; ?>

    <br>
    <button type="submit">Save changes</button>
</form>