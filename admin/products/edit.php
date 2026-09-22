<?php
$pageTitle = 'Edit Product';
$activeNav = 'products';
?>
<?php include __DIR__ . '/../../function/product.php'; ?>
<?php include __DIR__ . '/../../function/helper.php'; ?>
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

$categoriesResult = mysqli_query($conn, "SELECT id, name FROM categories ORDER BY name ASC");
$categories = [];
while ($row = mysqli_fetch_assoc($categoriesResult)) {
    $categories[] = $row;
}

$commonCountries = ['India', 'Nepal', 'Sri Lanka', 'United States', 'United Kingdom', 'Other'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    $hasVariants = isset($_POST['has_variants']);

    $countryOfOrigin = $_POST['country_of_origin'] ?? 'India';
    if ($countryOfOrigin === 'Other') {
        $countryOfOrigin = trim($_POST['country_of_origin_other'] ?? '') !== '' ? $_POST['country_of_origin_other'] : 'India';
    }

    if (empty($errors)) {
        $data = [
            'title' => $_POST['title'] ?? '',
            'slug' => $slug,
            'short_description' => $_POST['short_description'] ?? null,
            'description' => $_POST['description'] ?? null,
            'base_price' => $_POST['base_price'] ?? null,
            'base_sale_price' => $_POST['base_sale_price'] ?? '',
            'has_variants' => $hasVariants,
            'stock' => $_POST['stock'] ?? 0,
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
                'country_of_origin' => $countryOfOrigin,
                'shelf_life' => $_POST['shelf_life'] ?? null,
            ],
            'category_ids' => $categoryIds,
            'primary_category_id' => isset($_POST['primary_category_id']) && $_POST['primary_category_id'] !== '' ? (int) $_POST['primary_category_id'] : null,
        ];

        try {
            updateProduct($conn, $id, $data);
        } catch (InvalidArgumentException $e) {
            $errors[] = $e->getMessage();
        } catch (Exception $e) {
            $errors[] = 'Something went wrong while updating the product: ' . $e->getMessage();
        }
    }

    if (empty($errors)) {
        foreach ($_POST['delete_images'] ?? [] as $imageId) {
            try {
                deleteProductImage($conn, (int) $imageId);
            } catch (Exception $e) {
                $errors[] = 'Something went wrong while deleting an image.';
            }
        }
    }

    if (empty($errors)) {
        $newImageFiles = [];
        foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $attr) {
            foreach ($_FILES['new_images'][$attr] ?? [] as $i => $group) {
                $newImageFiles[$i][$attr] = $group['file'] ?? null;
            }
        }
        foreach ($newImageFiles as $i => $file) {
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
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
        redirect('index.php?updated=1');
    }

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

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';

$val = function ($postKey, $dbValue) use ($isPost) {
    return $isPost ? ($_POST[$postKey] ?? '') : ($dbValue ?? '');
};

$postedHasVariants = $isPost ? isset($_POST['has_variants']) : ((int) $product['has_variants'] === 1);
$currentCountry = $val('country_of_origin', $details['country_of_origin'] ?? 'India');
$countryIsOther = !in_array($currentCountry, $commonCountries, true);

$initialNewVariants = $isPost ? array_values($_POST['new_variants'] ?? []) : [];
$initialNewImages = $isPost ? array_values($_POST['new_images'] ?? []) : [];
$deleteVariantIds = $isPost ? ($_POST['delete_variants'] ?? []) : [];
$deleteImageIds = $isPost ? ($_POST['delete_images'] ?? []) : [];

function render_new_variant_row(int $i, array $v = []): void
{
    $name = $v['variant_name'] ?? '';
    $sku = $v['sku'] ?? '';
    $price = $v['price'] ?? '';
    $salePrice = $v['sale_price'] ?? '';
    $stock = $v['stock'] ?? '0';
    $weight = $v['weight_grams'] ?? '';
    $sort = $v['sort_order'] ?? '0';
    $status = $v['status'] ?? 'Active';
    $isDefault = isset($v['is_default']);
?>
    <div class="repeater-item" data-variant-row>
        <div class="repeater-head">
            <span class="repeater-title"><span class="pill">New</span> Variant</span>
            <button type="button" class="repeater-remove" data-remove-row>Remove</button>
        </div>
        <div class="field-grid cols-3">
            <div class="field">
                <label>Name<span class="req">*</span></label>
                <input type="text" name="new_variants[<?php echo $i; ?>][variant_name]" value="<?php echo htmlspecialchars($name); ?>" placeholder="e.g. 100 g">
            </div>
            <div class="field">
                <label>SKU<span class="req">*</span></label>
                <input type="text" name="new_variants[<?php echo $i; ?>][sku]" value="<?php echo htmlspecialchars($sku); ?>">
            </div>
            <div class="field">
                <label>Status</label>
                <select name="new_variants[<?php echo $i; ?>][status]">
                    <?php foreach (PRODUCT_VARIANT_STATUSES as $vStatus) : ?>
                        <option value="<?php echo htmlspecialchars($vStatus); ?>" <?php echo $status === $vStatus ? 'selected' : ''; ?>><?php echo htmlspecialchars($vStatus); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Price (₹)<span class="req">*</span></label>
                <input type="number" step="0.01" min="0" name="new_variants[<?php echo $i; ?>][price]" value="<?php echo htmlspecialchars($price); ?>">
            </div>
            <div class="field">
                <label>Sale price (₹) <span class="opt">optional</span></label>
                <input type="number" step="0.01" min="0" name="new_variants[<?php echo $i; ?>][sale_price]" value="<?php echo htmlspecialchars($salePrice); ?>">
            </div>
            <div class="field">
                <label>Stock<span class="req">*</span></label>
                <input type="number" min="0" name="new_variants[<?php echo $i; ?>][stock]" value="<?php echo htmlspecialchars($stock); ?>">
            </div>
            <div class="field">
                <label>Weight (grams)</label>
                <input type="number" min="0" name="new_variants[<?php echo $i; ?>][weight_grams]" value="<?php echo htmlspecialchars($weight); ?>">
            </div>
            <div class="field">
                <label>Sort order</label>
                <input type="number" name="new_variants[<?php echo $i; ?>][sort_order]" value="<?php echo htmlspecialchars($sort); ?>">
            </div>
            <div class="field" style="justify-content:center;">
                <label class="checkbox-row" style="margin-top:20px;">
                    <input type="checkbox" class="default-variant-checkbox" name="new_variants[<?php echo $i; ?>][is_default]" value="1" <?php echo $isDefault ? 'checked' : ''; ?>>
                    Default variant
                </label>
            </div>
        </div>
    </div>
<?php
}

function render_new_image_row(int $i, array $img = []): void
{
    $alt = $img['alt_text'] ?? '';
    $sort = $img['sort_order'] ?? '0';
    $isPrimary = isset($img['is_primary']);
?>
    <div class="repeater-item" data-image-row>
        <div class="repeater-head">
            <span class="repeater-title">New image</span>
            <button type="button" class="repeater-remove" data-remove-row>Remove</button>
        </div>
        <div class="field-grid">
            <div class="field span-2">
                <label>Image file</label>
                <input type="file" name="new_images[<?php echo $i; ?>][file]" accept="image/jpeg,image/png,image/webp,image/gif">
            </div>
            <div class="field">
                <label>Alt text</label>
                <input type="text" name="new_images[<?php echo $i; ?>][alt_text]" value="<?php echo htmlspecialchars($alt); ?>" placeholder="Describe the image">
            </div>
            <div class="field">
                <label>Sort order</label>
                <input type="number" name="new_images[<?php echo $i; ?>][sort_order]" value="<?php echo htmlspecialchars($sort); ?>">
            </div>
            <div class="field" style="justify-content:center;">
                <label class="checkbox-row" style="margin-top:20px;">
                    <input type="checkbox" class="primary-image-checkbox" name="new_images[<?php echo $i; ?>][is_primary]" value="1" <?php echo $isPrimary ? 'checked' : ''; ?>>
                    Primary image
                </label>
            </div>
        </div>
    </div>
<?php
}
?>

<?php include __DIR__ . '/../include/header.php'; ?>

<style>
    .padmin {
        max-width: 1120px;
        margin: 0 auto;
        padding: 28px 28px 120px;
    }

    .padmin * {
        box-sizing: border-box;
    }

    .padmin .topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .padmin .topbar h1 {
        font-size: 1.5rem;
        font-weight: 800;
        margin: 0;
        letter-spacing: -0.01em;
    }

    .padmin .topbar .subtitle {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--muted);
        margin-top: 2px;
    }

    .padmin .topbar-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .padmin .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 16px;
        font-size: 0.9rem;
        font-weight: 700;
        border-radius: 10px;
        border: 1px solid transparent;
        cursor: pointer;
        text-decoration: none;
    }

    .padmin .btn-primary {
        background: var(--leaf);
        color: #fff;
    }

    .padmin .btn-primary:hover {
        background: var(--leaf-dark);
    }

    .padmin .btn-secondary {
        background: #fff;
        color: var(--ink);
        border-color: var(--line);
    }

    .padmin .btn-secondary:hover {
        background: var(--mist);
    }

    .padmin .layout2 {
        display: grid;
        grid-template-columns: 190px 1fr;
        gap: 24px;
        align-items: start;
    }

    .padmin .section-nav {
        position: sticky;
        top: 20px;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .padmin .section-nav a {
        display: block;
        padding: 8px 12px;
        font-size: 0.86rem;
        font-weight: 700;
        color: var(--muted);
        text-decoration: none;
        border-radius: 8px;
        border-left: 2px solid transparent;
    }

    .padmin .section-nav a:hover {
        background: var(--sky-tint);
        color: var(--sky);
    }

    .padmin .section-nav a.is-active {
        color: var(--leaf-dark);
        background: var(--leaf-tint);
        border-left-color: var(--leaf);
    }

    .padmin .card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: 22px 24px;
        margin-bottom: 18px;
        scroll-margin-top: 20px;
    }

    .padmin .card h2 {
        font-size: 1.02rem;
        font-weight: 800;
        margin: 0 0 4px;
    }

    .padmin .card-hint {
        font-size: 0.84rem;
        color: var(--muted);
        margin: 0 0 16px;
    }

    .padmin .field-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px 18px;
    }

    .padmin .field-grid.cols-3 {
        grid-template-columns: repeat(3, 1fr);
    }

    .padmin .field {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .padmin .field.span-2 {
        grid-column: span 2;
    }

    .padmin label {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--ink);
    }

    .padmin label .req {
        color: #b3382c;
        margin-left: 2px;
    }

    .padmin label .opt {
        font-weight: 500;
        color: var(--muted);
        font-size: 0.78rem;
    }

    .padmin .field-help {
        font-size: 0.78rem;
        color: var(--muted);
        margin: 2px 0 0;
    }

    .padmin input[type="text"],
    .padmin input[type="number"],
    .padmin input[type="file"],
    .padmin select,
    .padmin textarea {
        width: 100%;
        padding: 9px 11px;
        font-size: 0.92rem;
        font-family: inherit;
        color: var(--ink);
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 9px;
    }

    .padmin textarea {
        min-height: 72px;
        resize: vertical;
        line-height: 1.5;
    }

    .padmin input:focus,
    .padmin select:focus,
    .padmin textarea:focus {
        outline: none;
        border-color: var(--leaf);
        box-shadow: 0 0 0 3px var(--leaf-tint);
    }

    .padmin select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='9'%3E%3Cpath d='M1 1l6 6 6-6' stroke='%2364798c' stroke-width='1.6' fill='none' fill-rule='evenodd'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 32px;
    }

    .padmin .checkbox-row {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
    }

    .padmin .checkbox-row input {
        width: 16px;
        height: 16px;
        accent-color: var(--leaf);
    }

    .padmin .flag-row {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }

    .padmin .checkbox-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px 14px;
        max-height: 340px;
        overflow-y: auto;
        padding-right: 4px;
    }

    .padmin .checkbox-tile {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border: 1px solid var(--line);
        border-radius: 9px;
        font-size: 0.87rem;
        font-weight: 600;
    }

    .padmin .checkbox-tile:has(input:checked) {
        border-color: var(--leaf);
        background: var(--leaf-tint);
    }

    .padmin .checkbox-tile input {
        accent-color: var(--leaf);
    }

    .padmin .repeater-item {
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 16px 18px;
        margin-bottom: 12px;
        background: #fff;
    }

    .padmin .repeater-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .padmin .repeater-title {
        font-size: 0.87rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .padmin .pill {
        display: inline-block;
        font-size: 0.7rem;
        font-weight: 800;
        padding: 2px 9px;
        border-radius: 99px;
        background: var(--sky-tint);
        color: var(--sky);
    }

    .padmin .repeater-remove {
        background: none;
        border: none;
        color: #b3382c;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        padding: 4px 8px;
    }

    .padmin .repeater-remove:hover {
        text-decoration: underline;
    }

    .padmin .add-row-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 10px 14px;
        background: var(--leaf-tint);
        color: var(--leaf-dark);
        border: 1px dashed var(--line);
        border-radius: 9px;
        font-weight: 700;
        font-size: 0.86rem;
        cursor: pointer;
        width: 100%;
    }

    .padmin .add-row-btn:hover {
        background: #dcf1e4;
    }

    .padmin .stock-box {
        border: 1px solid var(--line);
        background: var(--leaf-tint);
        border-radius: 12px;
        padding: 16px 18px;
    }

    .padmin .stock-box .field {
        max-width: 220px;
    }

    .padmin .variants-note {
        font-size: 0.84rem;
        color: var(--muted);
        background: var(--mist);
        padding: 10px 14px;
        border-radius: 9px;
        margin-bottom: 12px;
    }

    .padmin .alert {
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 0.88rem;
        margin-bottom: 18px;
        border: 1px solid transparent;
    }

    .padmin .alert-error {
        background: #fbeae7;
        border-color: #f0c2ba;
        color: #8f2c22;
    }

    .padmin .alert-error ul {
        margin: 4px 0 0;
        padding-left: 18px;
    }

    .padmin .save-bar {
        position: sticky;
        bottom: 0;
        margin-top: 20px;
        padding: 14px 18px;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 -2px 10px rgba(28, 38, 32, .06);
    }

    .padmin .save-note {
        font-size: 0.82rem;
        color: var(--muted);
    }

    .padmin .image-existing {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        padding: 12px;
        border: 1px solid var(--line);
        border-radius: 12px;
        margin-bottom: 10px;
    }

    .padmin .image-existing img {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid var(--line);
        flex-shrink: 0;
    }

    .padmin .image-existing-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .padmin .image-existing-path {
        font-size: 0.78rem;
        color: var(--muted);
        word-break: break-all;
    }

    .padmin .empty-note {
        font-size: 0.87rem;
        color: var(--muted);
        background: var(--mist);
        border-radius: 9px;
        padding: 12px 14px;
        margin-bottom: 12px;
    }

    @media (max-width: 860px) {
        .padmin .layout2 {
            grid-template-columns: 1fr;
        }

        .padmin .section-nav {
            position: static;
            flex-direction: row;
            overflow-x: auto;
            border-bottom: 1px solid var(--line);
            margin-bottom: 8px;
        }

        .padmin .field-grid,
        .padmin .field-grid.cols-3 {
            grid-template-columns: 1fr;
        }

        .padmin .field.span-2 {
            grid-column: span 1;
        }

        .padmin .checkbox-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="padmin">

    <div class="topbar">
        <div>
            <h1>Edit product</h1>
            <span class="subtitle"><?php echo htmlspecialchars($product['title']); ?></span>
        </div>
        <div class="topbar-actions">
            <a href="view.php?id=<?php echo (int) $id; ?>" class="btn btn-secondary">View</a>
            <a href="index.php" class="btn btn-secondary">Back to products</a>
        </div>
    </div>

    <?php if (!empty($errors)) : ?>
        <div class="alert alert-error">
            <strong>Fix the following before saving:</strong>
            <ul>
                <?php foreach ($errors as $error) : ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="edit.php?id=<?php echo (int) $id; ?>" enctype="multipart/form-data" id="product-form" novalidate>

        <div class="layout2">

            <nav class="section-nav" id="section-nav">
                <a href="#sec-basic" class="is-active">Basic info</a>
                <a href="#sec-pricing">Pricing</a>
                <a href="#sec-inventory">Inventory</a>
                <a href="#sec-categories">Categories</a>
                <a href="#sec-details">Product details</a>
                <a href="#sec-images">Images</a>
                <a href="#sec-seo">SEO</a>
            </nav>

            <div>

                <section class="card" id="sec-basic">
                    <h2>Basic info</h2>
                    <p class="card-hint">Current slug: <strong><?php echo htmlspecialchars($product['slug']); ?></strong> — it will be regenerated from the title if you change it.</p>

                    <div class="field-grid">
                        <div class="field span-2">
                            <label for="title">Title<span class="req">*</span></label>
                            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($val('title', $product['title'])); ?>" required>
                        </div>

                        <div class="field span-2">
                            <label for="short_description">Short description</label>
                            <textarea id="short_description" name="short_description" rows="2"><?php echo htmlspecialchars($val('short_description', $product['short_description'])); ?></textarea>
                        </div>

                        <div class="field span-2">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($val('description', $product['description'])); ?></textarea>
                        </div>

                        <div class="field">
                            <?php $currentStatus = $val('status', $product['status']); ?>
                            <label for="status">Status<span class="req">*</span></label>
                            <select id="status" name="status" required>
                                <?php foreach (PRODUCT_STATUSES as $statusOption) : ?>
                                    <option value="<?php echo htmlspecialchars($statusOption); ?>" <?php echo ($currentStatus === $statusOption) ? 'selected' : ''; ?>><?php echo htmlspecialchars($statusOption); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label>Visibility flags</label>
                            <div class="flag-row" style="margin-top:6px;">
                                <label class="checkbox-row">
                                    <input type="checkbox" name="featured" value="1" <?php echo ($isPost ? isset($_POST['featured']) : (int) $product['featured'] === 1) ? 'checked' : ''; ?>>
                                    Featured
                                </label>
                                <label class="checkbox-row">
                                    <input type="checkbox" name="bestseller" value="1" <?php echo ($isPost ? isset($_POST['bestseller']) : (int) $product['bestseller'] === 1) ? 'checked' : ''; ?>>
                                    Bestseller
                                </label>
                                <label class="checkbox-row">
                                    <input type="checkbox" name="trending" value="1" <?php echo ($isPost ? isset($_POST['trending']) : (int) $product['trending'] === 1) ? 'checked' : ''; ?>>
                                    Trending
                                </label>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="card" id="sec-pricing">
                    <h2>Pricing</h2>
                    <p class="card-hint">If this product has variants, each variant sets its own price below instead. Sale price can't be higher than the base price.</p>

                    <div class="field-grid">
                        <div class="field">
                            <label for="base_price">Base price (₹)<span class="req">*</span></label>
                            <input type="number" step="0.01" min="0" id="base_price" name="base_price" value="<?php echo htmlspecialchars($val('base_price', $product['base_price'])); ?>" required>
                        </div>
                        <div class="field">
                            <label for="base_sale_price">Sale price (₹) <span class="opt">optional</span></label>
                            <input type="number" step="0.01" min="0" id="base_sale_price" name="base_sale_price" value="<?php echo htmlspecialchars($val('base_sale_price', $product['base_sale_price'])); ?>">
                        </div>
                    </div>
                </section>

                <section class="card" id="sec-inventory">
                    <h2>Inventory</h2>
                    <p class="card-hint">Turn variants on only if this product is sold in more than one size, weight or pack.</p>

                    <label class="checkbox-row" style="margin-bottom:16px;">
                        <input type="checkbox" name="has_variants" id="has_variants" value="1" <?php echo $postedHasVariants ? 'checked' : ''; ?>>
                        This product has variants (different sizes, weights or packs)
                    </label>

                    <div id="stock-simple-block" class="stock-box">
                        <div class="field">
                            <label for="stock">Stock quantity<span class="req">*</span></label>
                            <input type="number" min="0" id="stock" name="stock" value="<?php echo htmlspecialchars($val('stock', $product['stock'])); ?>">
                            <p class="field-help">How many units are available to sell right now. Not used while variants are on.</p>
                        </div>
                    </div>

                    <div id="variants-block" style="display:none;">
                        <?php if (!empty($variants)) : ?>
                            <p class="card-hint" style="margin-bottom:14px;">Existing variants</p>
                            <?php foreach ($variants as $variant) : ?>
                                <div class="repeater-item" data-variant-row data-existing-variant>
                                    <div class="repeater-head">
                                        <span class="repeater-title"><?php echo htmlspecialchars($variant['variant_name']); ?> <span style="color:var(--muted); font-weight:500;">(<?php echo htmlspecialchars($variant['sku']); ?>)</span></span>
                                        <label class="checkbox-row" style="color:#b3382c; font-weight:700;">
                                            <input type="checkbox" name="delete_variants[]" class="delete-variant-checkbox" value="<?php echo (int) $variant['id']; ?>" <?php echo in_array((string) $variant['id'], $deleteVariantIds, true) ? 'checked' : ''; ?>>
                                            Delete
                                        </label>
                                    </div>
                                    <div class="field-grid cols-3">
                                        <div class="field">
                                            <label>Name<span class="req">*</span></label>
                                            <input type="text" name="existing_variants[<?php echo (int) $variant['id']; ?>][variant_name]" value="<?php echo htmlspecialchars($variant['variant_name']); ?>">
                                        </div>
                                        <div class="field">
                                            <label>SKU<span class="req">*</span></label>
                                            <input type="text" name="existing_variants[<?php echo (int) $variant['id']; ?>][sku]" value="<?php echo htmlspecialchars($variant['sku']); ?>">
                                        </div>
                                        <div class="field">
                                            <label>Status</label>
                                            <select name="existing_variants[<?php echo (int) $variant['id']; ?>][status]">
                                                <?php foreach (PRODUCT_VARIANT_STATUSES as $vStatus) : ?>
                                                    <option value="<?php echo htmlspecialchars($vStatus); ?>" <?php echo $variant['status'] === $vStatus ? 'selected' : ''; ?>><?php echo htmlspecialchars($vStatus); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="field">
                                            <label>Price (₹)<span class="req">*</span></label>
                                            <input type="number" step="0.01" min="0" name="existing_variants[<?php echo (int) $variant['id']; ?>][price]" value="<?php echo htmlspecialchars($variant['price']); ?>">
                                        </div>
                                        <div class="field">
                                            <label>Sale price (₹) <span class="opt">optional</span></label>
                                            <input type="number" step="0.01" min="0" name="existing_variants[<?php echo (int) $variant['id']; ?>][sale_price]" value="<?php echo htmlspecialchars($variant['sale_price'] ?? ''); ?>">
                                        </div>
                                        <div class="field">
                                            <label>Stock<span class="req">*</span></label>
                                            <input type="number" min="0" name="existing_variants[<?php echo (int) $variant['id']; ?>][stock]" value="<?php echo htmlspecialchars($variant['stock']); ?>">
                                        </div>
                                        <div class="field">
                                            <label>Weight (grams)</label>
                                            <input type="number" min="0" name="existing_variants[<?php echo (int) $variant['id']; ?>][weight_grams]" value="<?php echo htmlspecialchars($variant['weight_grams'] ?? ''); ?>">
                                        </div>
                                        <div class="field">
                                            <label>Sort order</label>
                                            <input type="number" name="existing_variants[<?php echo (int) $variant['id']; ?>][sort_order]" value="<?php echo htmlspecialchars($variant['sort_order']); ?>">
                                        </div>
                                        <div class="field" style="justify-content:center;">
                                            <label class="checkbox-row" style="margin-top:20px;">
                                                <input type="checkbox" class="default-variant-checkbox" name="existing_variants[<?php echo (int) $variant['id']; ?>][is_default]" value="1" <?php echo (int) $variant['is_default'] === 1 ? 'checked' : ''; ?>>
                                                Default variant
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <p class="variants-note">Stock for this product is tracked per variant, not on the field above.</p>

                        <div id="new-variant-rows">
                            <?php foreach ($initialNewVariants as $i => $v) : render_new_variant_row($i, $v);
                            endforeach; ?>
                        </div>

                        <button type="button" class="add-row-btn" id="add-variant-btn">+ Add another variant</button>
                    </div>
                </section>

                <section class="card" id="sec-categories">
                    <h2>Categories</h2>
                    <p class="card-hint">Pick every category this product belongs to, then choose one as primary. At least one is required.</p>

                    <?php $checkedCategoryIds = $isPost ? array_map('intval', $_POST['category_ids'] ?? []) : $productCategoryIds; ?>
                    <div class="checkbox-grid" id="category-checkboxes">
                        <?php foreach ($categories as $category) : ?>
                            <label class="checkbox-tile">
                                <input type="checkbox" class="category-checkbox" name="category_ids[]" value="<?php echo (int) $category['id']; ?>" <?php echo in_array((int) $category['id'], $checkedCategoryIds, true) ? 'checked' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <?php $selectedPrimary = $isPost ? (int) ($_POST['primary_category_id'] ?? 0) : $primaryCategoryId; ?>
                    <div class="field" style="max-width:340px; margin-top:16px;">
                        <label for="primary_category_id">Primary category<span class="req">*</span></label>
                        <select id="primary_category_id" name="primary_category_id" required>
                            <option value="">Select a checked category above</option>
                            <?php foreach ($categories as $category) : ?>
                                <?php $checked = in_array((int) $category['id'], $checkedCategoryIds, true); ?>
                                <option value="<?php echo (int) $category['id']; ?>" <?php echo !$checked ? 'disabled' : ''; ?> <?php echo ((int) $category['id'] === $selectedPrimary) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </section>

                <section class="card" id="sec-details">
                    <h2>Product details</h2>

                    <div class="field-grid">
                        <div class="field span-2">
                            <label for="ingredients">Ingredients</label>
                            <textarea id="ingredients" name="ingredients" rows="2"><?php echo htmlspecialchars($val('ingredients', $details['ingredients'] ?? '')); ?></textarea>
                        </div>
                        <div class="field span-2">
                            <label for="benefits">Benefits</label>
                            <textarea id="benefits" name="benefits" rows="2"><?php echo htmlspecialchars($val('benefits', $details['benefits'] ?? '')); ?></textarea>
                        </div>
                        <div class="field span-2">
                            <label for="directions">Directions</label>
                            <textarea id="directions" name="directions" rows="2"><?php echo htmlspecialchars($val('directions', $details['directions'] ?? '')); ?></textarea>
                        </div>
                        <div class="field span-2">
                            <label for="dosage">Dosage</label>
                            <textarea id="dosage" name="dosage" rows="2"><?php echo htmlspecialchars($val('dosage', $details['dosage'] ?? '')); ?></textarea>
                        </div>
                        <div class="field span-2">
                            <label for="precautions">Precautions</label>
                            <textarea id="precautions" name="precautions" rows="2"><?php echo htmlspecialchars($val('precautions', $details['precautions'] ?? '')); ?></textarea>
                        </div>
                        <div class="field">
                            <label for="manufacturer">Manufacturer</label>
                            <input type="text" id="manufacturer" name="manufacturer" value="<?php echo htmlspecialchars($val('manufacturer', $details['manufacturer'] ?? '')); ?>">
                        </div>
                        <div class="field">
                            <label for="shelf_life">Shelf life</label>
                            <input type="text" id="shelf_life" name="shelf_life" value="<?php echo htmlspecialchars($val('shelf_life', $details['shelf_life'] ?? '')); ?>" placeholder="e.g. 24 months">
                        </div>
                        <div class="field">
                            <label for="country_of_origin">Country of origin</label>
                            <select id="country_of_origin" name="country_of_origin">
                                <?php foreach ($commonCountries as $c) : ?>
                                    <option value="<?php echo htmlspecialchars($c); ?>" <?php echo (($countryIsOther ? 'Other' : $currentCountry) === $c) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field" id="country_other_field" style="<?php echo $countryIsOther ? '' : 'display:none;'; ?>">
                            <label for="country_of_origin_other">Specify country</label>
                            <input type="text" id="country_of_origin_other" name="country_of_origin_other" value="<?php echo htmlspecialchars($countryIsOther ? $currentCountry : ($_POST['country_of_origin_other'] ?? '')); ?>">
                        </div>
                    </div>
                </section>

                <section class="card" id="sec-images">
                    <h2>Images</h2>

                    <?php if (empty($images)) : ?>
                        <p class="empty-note">No images yet — add one below.</p>
                    <?php else : ?>
                        <p class="card-hint">To change an existing image's alt text or make it primary, delete it and add it again below.</p>
                        <?php foreach ($images as $image) : ?>
                            <div class="image-existing">
                                <img src="<?php echo htmlspecialchars(getProductImageUrl($image['image'])); ?>" alt="<?php echo htmlspecialchars($image['alt_text'] ?? ''); ?>">
                                <div class="image-existing-body">
                                    <div class="image-existing-path">
                                        <?php echo htmlspecialchars($image['image']); ?>
                                        <?php if ((int) $image['is_primary'] === 1) : ?>
                                            <span class="pill" style="margin-left:6px;">Primary</span>
                                        <?php endif; ?>
                                    </div>
                                    <label class="checkbox-row" style="color:#b3382c; font-weight:700;">
                                        <input type="checkbox" name="delete_images[]" value="<?php echo (int) $image['id']; ?>" <?php echo in_array((string) $image['id'], $deleteImageIds, true) ? 'checked' : ''; ?>>
                                        Delete this image
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <p class="card-hint" style="margin-top:18px;">Add new images</p>
                    <div id="new-image-rows">
                        <?php foreach ($initialNewImages as $i => $img) : render_new_image_row($i, $img);
                        endforeach; ?>
                    </div>
                    <button type="button" class="add-row-btn" id="add-image-btn">+ Add another image</button>
                </section>

                <section class="card" id="sec-seo">
                    <h2>SEO</h2>

                    <div class="field-grid">
                        <div class="field span-2">
                            <label for="meta_title">Meta title <span class="opt">optional</span></label>
                            <input type="text" id="meta_title" name="meta_title" value="<?php echo htmlspecialchars($val('meta_title', $product['meta_title'])); ?>">
                        </div>
                        <div class="field span-2">
                            <label for="meta_description">Meta description <span class="opt">optional</span></label>
                            <textarea id="meta_description" name="meta_description" rows="2"><?php echo htmlspecialchars($val('meta_description', $product['meta_description'])); ?></textarea>
                            <p class="field-help">Auto-trimmed to 200 characters if longer.</p>
                        </div>
                    </div>
                </section>

                <div class="save-bar">
                    <span class="save-note">All fields marked <span class="req">*</span> are required.</span>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>

            </div>
        </div>
    </form>

</div>

<template id="variant-template">
    <div class="repeater-item" data-variant-row>
        <div class="repeater-head">
            <span class="repeater-title"><span class="pill">New</span> Variant</span>
            <button type="button" class="repeater-remove" data-remove-row>Remove</button>
        </div>
        <div class="field-grid cols-3">
            <div class="field">
                <label>Name<span class="req">*</span></label>
                <input type="text" name="new_variants[__INDEX__][variant_name]" placeholder="e.g. 100 g">
            </div>
            <div class="field">
                <label>SKU<span class="req">*</span></label>
                <input type="text" name="new_variants[__INDEX__][sku]">
            </div>
            <div class="field">
                <label>Status</label>
                <select name="new_variants[__INDEX__][status]">
                    <?php foreach (PRODUCT_VARIANT_STATUSES as $vStatus) : ?>
                        <option value="<?php echo htmlspecialchars($vStatus); ?>"><?php echo htmlspecialchars($vStatus); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Price (₹)<span class="req">*</span></label>
                <input type="number" step="0.01" min="0" name="new_variants[__INDEX__][price]">
            </div>
            <div class="field">
                <label>Sale price (₹) <span class="opt">optional</span></label>
                <input type="number" step="0.01" min="0" name="new_variants[__INDEX__][sale_price]">
            </div>
            <div class="field">
                <label>Stock<span class="req">*</span></label>
                <input type="number" min="0" name="new_variants[__INDEX__][stock]" value="0">
            </div>
            <div class="field">
                <label>Weight (grams)</label>
                <input type="number" min="0" name="new_variants[__INDEX__][weight_grams]">
            </div>
            <div class="field">
                <label>Sort order</label>
                <input type="number" name="new_variants[__INDEX__][sort_order]" value="0">
            </div>
            <div class="field" style="justify-content:center;">
                <label class="checkbox-row" style="margin-top:20px;">
                    <input type="checkbox" class="default-variant-checkbox" name="new_variants[__INDEX__][is_default]" value="1">
                    Default variant
                </label>
            </div>
        </div>
    </div>
</template>

<template id="image-template">
    <div class="repeater-item" data-image-row>
        <div class="repeater-head">
            <span class="repeater-title">New image</span>
            <button type="button" class="repeater-remove" data-remove-row>Remove</button>
        </div>
        <div class="field-grid">
            <div class="field span-2">
                <label>Image file</label>
                <input type="file" name="new_images[__INDEX__][file]" accept="image/jpeg,image/png,image/webp,image/gif">
            </div>
            <div class="field">
                <label>Alt text</label>
                <input type="text" name="new_images[__INDEX__][alt_text]" placeholder="Describe the image">
            </div>
            <div class="field">
                <label>Sort order</label>
                <input type="number" name="new_images[__INDEX__][sort_order]" value="0">
            </div>
            <div class="field" style="justify-content:center;">
                <label class="checkbox-row" style="margin-top:20px;">
                    <input type="checkbox" class="primary-image-checkbox" name="new_images[__INDEX__][is_primary]" value="1">
                    Primary image
                </label>
            </div>
        </div>
    </div>
</template>

<script>
    (function() {
        function nextIndex(container) {
            return container.children.length;
        }

        function addRow(container, templateSelector) {
            const tpl = document.querySelector(templateSelector);
            if (!tpl) return null;
            const idx = nextIndex(container);
            const html = tpl.innerHTML.split('__INDEX__').join(String(idx));
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            const node = wrapper.firstElementChild;
            container.appendChild(node);
            return node;
        }

        function wireRemoveButtons(container) {
            container.addEventListener('click', function(e) {
                const btn = e.target.closest('[data-remove-row]');
                if (!btn) return;
                const row = btn.closest('.repeater-item');
                if (row) row.remove();
            });
        }

        function wireExclusiveCheckbox(container, className) {
            container.addEventListener('change', function(e) {
                if (!e.target.classList.contains(className) || !e.target.checked) return;
                container.querySelectorAll('.' + className).forEach(function(cb) {
                    if (cb !== e.target) cb.checked = false;
                });
            });
        }

        const newVariantContainer = document.querySelector('#new-variant-rows');
        const newImageContainer = document.querySelector('#new-image-rows');

        wireRemoveButtons(newVariantContainer);
        wireExclusiveCheckbox(document.querySelector('#variants-block'), 'default-variant-checkbox');
        wireRemoveButtons(newImageContainer);
        wireExclusiveCheckbox(newImageContainer, 'primary-image-checkbox');

        document.querySelector('#add-variant-btn').addEventListener('click', function() {
            addRow(newVariantContainer, '#variant-template');
        });
        document.querySelector('#add-image-btn').addEventListener('click', function() {
            addRow(newImageContainer, '#image-template');
        });

        const hasVariants = document.querySelector('#has_variants');
        const stockBlock = document.querySelector('#stock-simple-block');
        const variantsBlock = document.querySelector('#variants-block');

        function syncInventoryMode() {
            const on = hasVariants.checked;
            stockBlock.style.display = on ? 'none' : '';
            variantsBlock.style.display = on ? '' : 'none';
        }
        hasVariants.addEventListener('change', syncInventoryMode);
        syncInventoryMode();

        const checkboxContainer = document.querySelector('#category-checkboxes');
        const primarySelect = document.querySelector('#primary_category_id');

        function syncPrimaryOptions() {
            const checked = Array.from(checkboxContainer.querySelectorAll('.category-checkbox:checked')).map(function(cb) {
                return cb.value;
            });
            Array.from(primarySelect.options).forEach(function(opt) {
                if (opt.value === '') return;
                const isChecked = checked.includes(opt.value);
                opt.disabled = !isChecked;
                if (!isChecked && primarySelect.value === opt.value) primarySelect.value = '';
            });
            if (checked.length === 1 && primarySelect.value === '') primarySelect.value = checked[0];
        }
        checkboxContainer.addEventListener('change', syncPrimaryOptions);
        syncPrimaryOptions();

        const countrySelect = document.querySelector('#country_of_origin');
        const countryOtherField = document.querySelector('#country_other_field');

        function syncCountryOther() {
            countryOtherField.style.display = countrySelect.value === 'Other' ? '' : 'none';
        }
        countrySelect.addEventListener('change', syncCountryOther);
        syncCountryOther();

        const nav = document.querySelector('#section-nav');
        const links = Array.from(nav.querySelectorAll('a'));
        const sections = links.map(function(a) {
            return document.querySelector(a.getAttribute('href'));
        }).filter(Boolean);
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (!entry.isIntersecting) return;
                    const id = '#' + entry.target.id;
                    links.forEach(function(a) {
                        a.classList.toggle('is-active', a.getAttribute('href') === id);
                    });
                });
            }, {
                rootMargin: '-20% 0px -70% 0px'
            });
            sections.forEach(function(s) {
                observer.observe(s);
            });
        }
    })();
</script>

<?php include __DIR__ . '/../include/footer.php'; ?>