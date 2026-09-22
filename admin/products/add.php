<?php
$pageTitle = 'Add Product';
$activeNav = 'products';
?>
<?php include __DIR__ . '/../../function/product.php'; ?>
<?php include __DIR__ . '/../../function/helper.php'; ?>
<?php include __DIR__ . '/../../includes/auth.php'; ?>
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

    .padmin .btn-danger {
        background: #b3382c;
        color: #fff;
    }

    .padmin .btn-danger:hover {
        background: #8f2c22;
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

<?php
$errors = [];

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

    $variants = [];
    foreach ($_POST['variants'] ?? [] as $v) {
        if (trim($v['variant_name'] ?? '') !== '' && trim($v['sku'] ?? '') !== '') {
            $variants[] = [
                'variant_name' => $v['variant_name'],
                'sku' => $v['sku'],
                'price' => $v['price'] ?? 0,
                'sale_price' => $v['sale_price'] ?? '',
                'stock' => $v['stock'] ?? 0,
                'weight_grams' => $v['weight_grams'] ?? '',
                'is_default' => isset($v['is_default']),
                'status' => $v['status'] ?? 'Active',
                'sort_order' => $v['sort_order'] ?? 0,
            ];
        }
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
            // Only used for simple (no-variant) products — addProduct()
            // forces this to 0 internally when has_variants is true.
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
            'variants' => $variants,
        ];

        try {
            $newProductId = addProduct($conn, $data);

            $imageErrors = [];
            $files = [];
            foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $attr) {
                foreach ($_FILES['images'][$attr] ?? [] as $i => $group) {
                    $files[$i][$attr] = $group['file'] ?? null;
                }
            }

            foreach ($files as $i => $file) {
                if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $meta = $_POST['images'][$i] ?? [];
                try {
                    $relativePath = uploadProductImage($file, $newProductId);
                    addProductImage(
                        $conn,
                        $newProductId,
                        $relativePath,
                        trim($meta['alt_text'] ?? '') !== '' ? $meta['alt_text'] : null,
                        isset($meta['is_primary']),
                        (int) ($meta['sort_order'] ?? 0)
                    );
                } catch (Exception $e) {
                    $imageErrors[] = 'Image ' . ($i + 1) . ': ' . $e->getMessage();
                }
            }

            if (!empty($imageErrors)) {
                redirect('edit.php?id=' . $newProductId . '&image_errors=' . urlencode(implode(' | ', $imageErrors)));
            }

            redirect('index.php?added=1');
        } catch (InvalidArgumentException $e) {
            $errors[] = $e->getMessage();
        } catch (Exception $e) {
            $errors[] = 'Something went wrong while saving the product: ' . $e->getMessage();
        }
    }
}

$postedHasVariants = isset($_POST['has_variants']);
$initialVariants = $_SERVER['REQUEST_METHOD'] === 'POST' ? array_values($_POST['variants'] ?? []) : [];
$initialImages = $_SERVER['REQUEST_METHOD'] === 'POST' ? array_values($_POST['images'] ?? []) : [];

function render_variant_row(int $i, array $v = []): void
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
                <input type="text" name="variants[<?php echo $i; ?>][variant_name]" value="<?php echo htmlspecialchars($name); ?>" placeholder="e.g. 100 g">
            </div>
            <div class="field">
                <label>SKU<span class="req">*</span></label>
                <input type="text" name="variants[<?php echo $i; ?>][sku]" value="<?php echo htmlspecialchars($sku); ?>">
            </div>
            <div class="field">
                <label>Status</label>
                <select name="variants[<?php echo $i; ?>][status]">
                    <?php foreach (PRODUCT_VARIANT_STATUSES as $vStatus) : ?>
                        <option value="<?php echo htmlspecialchars($vStatus); ?>" <?php echo $status === $vStatus ? 'selected' : ''; ?>><?php echo htmlspecialchars($vStatus); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Price (₹)<span class="req">*</span></label>
                <input type="number" step="0.01" min="0" name="variants[<?php echo $i; ?>][price]" value="<?php echo htmlspecialchars($price); ?>">
            </div>
            <div class="field">
                <label>Sale price (₹) <span class="opt">optional</span></label>
                <input type="number" step="0.01" min="0" name="variants[<?php echo $i; ?>][sale_price]" value="<?php echo htmlspecialchars($salePrice); ?>">
            </div>
            <div class="field">
                <label>Stock<span class="req">*</span></label>
                <input type="number" min="0" name="variants[<?php echo $i; ?>][stock]" value="<?php echo htmlspecialchars($stock); ?>">
            </div>
            <div class="field">
                <label>Weight (grams)</label>
                <input type="number" min="0" name="variants[<?php echo $i; ?>][weight_grams]" value="<?php echo htmlspecialchars($weight); ?>">
            </div>
            <div class="field">
                <label>Sort order</label>
                <input type="number" name="variants[<?php echo $i; ?>][sort_order]" value="<?php echo htmlspecialchars($sort); ?>">
            </div>
            <div class="field" style="justify-content:center;">
                <label class="checkbox-row" style="margin-top:20px;">
                    <input type="checkbox" class="default-variant-checkbox" name="variants[<?php echo $i; ?>][is_default]" value="1" <?php echo $isDefault ? 'checked' : ''; ?>>
                    Default variant
                </label>
            </div>
        </div>
    </div>
<?php
}

function render_image_row(int $i, array $img = []): void
{
    $alt = $img['alt_text'] ?? '';
    $sort = $img['sort_order'] ?? '0';
    $isPrimary = isset($img['is_primary']);
?>
    <div class="repeater-item" data-image-row>
        <div class="repeater-head">
            <span class="repeater-title">Image</span>
            <button type="button" class="repeater-remove" data-remove-row>Remove</button>
        </div>
        <div class="field-grid">
            <div class="field span-2">
                <label>Image file</label>
                <input type="file" name="images[<?php echo $i; ?>][file]" accept="image/jpeg,image/png,image/webp,image/gif">
            </div>
            <div class="field">
                <label>Alt text</label>
                <input type="text" name="images[<?php echo $i; ?>][alt_text]" value="<?php echo htmlspecialchars($alt); ?>" placeholder="Describe the image">
            </div>
            <div class="field">
                <label>Sort order</label>
                <input type="number" name="images[<?php echo $i; ?>][sort_order]" value="<?php echo htmlspecialchars($sort); ?>">
            </div>
            <div class="field" style="justify-content:center;">
                <label class="checkbox-row" style="margin-top:20px;">
                    <input type="checkbox" class="primary-image-checkbox" name="images[<?php echo $i; ?>][is_primary]" value="1" <?php echo $isPrimary ? 'checked' : ''; ?>>
                    Primary image
                </label>
            </div>
        </div>
    </div>
<?php
}
?>

<div class="padmin">

    <div class="topbar">
        <div>
            <h1>Add product</h1>
            <span class="subtitle">New catalog item</span>
        </div>
        <div>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
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

    <form method="POST" action="add.php" enctype="multipart/form-data" id="product-form" novalidate>

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
                    <p class="card-hint">The URL slug is generated automatically from the title.</p>

                    <div class="field-grid">
                        <div class="field span-2">
                            <label for="title">Title<span class="req">*</span></label>
                            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                        </div>

                        <div class="field span-2">
                            <label for="short_description">Short description</label>
                            <textarea id="short_description" name="short_description" rows="2"><?php echo htmlspecialchars($_POST['short_description'] ?? ''); ?></textarea>
                        </div>

                        <div class="field span-2">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="field">
                            <label for="status">Status<span class="req">*</span></label>
                            <select id="status" name="status" required>
                                <?php foreach (PRODUCT_STATUSES as $statusOption) : ?>
                                    <option value="<?php echo htmlspecialchars($statusOption); ?>" <?php echo (($_POST['status'] ?? 'Draft') === $statusOption) ? 'selected' : ''; ?>><?php echo htmlspecialchars($statusOption); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="field-help">Draft products are hidden from the storefront.</p>
                        </div>

                        <div class="field">
                            <label>Visibility flags</label>
                            <div class="flag-row" style="margin-top:6px;">
                                <label class="checkbox-row">
                                    <input type="checkbox" name="featured" value="1" <?php echo isset($_POST['featured']) ? 'checked' : ''; ?>>
                                    Featured
                                </label>
                                <label class="checkbox-row">
                                    <input type="checkbox" name="bestseller" value="1" <?php echo isset($_POST['bestseller']) ? 'checked' : ''; ?>>
                                    Bestseller
                                </label>
                                <label class="checkbox-row">
                                    <input type="checkbox" name="trending" value="1" <?php echo isset($_POST['trending']) ? 'checked' : ''; ?>>
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
                            <input type="number" step="0.01" min="0" id="base_price" name="base_price" value="<?php echo htmlspecialchars($_POST['base_price'] ?? ''); ?>" required>
                        </div>
                        <div class="field">
                            <label for="base_sale_price">Sale price (₹) <span class="opt">optional</span></label>
                            <input type="number" step="0.01" min="0" id="base_sale_price" name="base_sale_price" value="<?php echo htmlspecialchars($_POST['base_sale_price'] ?? ''); ?>">
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
                            <input type="number" min="0" id="stock" name="stock" value="<?php echo htmlspecialchars($_POST['stock'] ?? '0'); ?>">
                            <p class="field-help">How many units are available to sell right now.</p>
                        </div>
                    </div>

                    <div id="variants-block" style="display:none;">
                        <p class="variants-note">At least one variant is required while this is on. Stock is tracked per variant, not on the field above.</p>

                        <div id="variant-rows">
                            <?php foreach ($initialVariants as $i => $v) : render_variant_row($i, $v);
                            endforeach; ?>
                        </div>

                        <button type="button" class="add-row-btn" id="add-variant-btn">+ Add another variant</button>
                    </div>
                </section>

                <section class="card" id="sec-categories">
                    <h2>Categories</h2>
                    <p class="card-hint">Pick every category this product belongs to, then choose one as primary. At least one is required.</p>

                    <div class="checkbox-grid" id="category-checkboxes">
                        <?php foreach ($categories as $category) : ?>
                            <label class="checkbox-tile">
                                <input type="checkbox" class="category-checkbox" name="category_ids[]" value="<?php echo (int) $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="field" style="max-width:340px; margin-top:16px;">
                        <label for="primary_category_id">Primary category<span class="req">*</span></label>
                        <select id="primary_category_id" name="primary_category_id" required>
                            <option value="">Select a checked category above</option>
                            <?php foreach ($categories as $category) : ?>
                                <option value="<?php echo (int) $category['id']; ?>" disabled><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </section>

                <section class="card" id="sec-details">
                    <h2>Product details</h2>

                    <div class="field-grid">
                        <div class="field span-2">
                            <label for="ingredients">Ingredients</label>
                            <textarea id="ingredients" name="ingredients" rows="2"><?php echo htmlspecialchars($_POST['ingredients'] ?? ''); ?></textarea>
                        </div>
                        <div class="field span-2">
                            <label for="benefits">Benefits</label>
                            <textarea id="benefits" name="benefits" rows="2"><?php echo htmlspecialchars($_POST['benefits'] ?? ''); ?></textarea>
                        </div>
                        <div class="field span-2">
                            <label for="directions">Directions</label>
                            <textarea id="directions" name="directions" rows="2"><?php echo htmlspecialchars($_POST['directions'] ?? ''); ?></textarea>
                        </div>
                        <div class="field span-2">
                            <label for="dosage">Dosage</label>
                            <textarea id="dosage" name="dosage" rows="2"><?php echo htmlspecialchars($_POST['dosage'] ?? ''); ?></textarea>
                        </div>
                        <div class="field span-2">
                            <label for="precautions">Precautions</label>
                            <textarea id="precautions" name="precautions" rows="2"><?php echo htmlspecialchars($_POST['precautions'] ?? ''); ?></textarea>
                        </div>
                        <div class="field">
                            <label for="manufacturer">Manufacturer</label>
                            <input type="text" id="manufacturer" name="manufacturer" value="<?php echo htmlspecialchars($_POST['manufacturer'] ?? ''); ?>">
                        </div>
                        <div class="field">
                            <label for="shelf_life">Shelf life</label>
                            <input type="text" id="shelf_life" name="shelf_life" value="<?php echo htmlspecialchars($_POST['shelf_life'] ?? ''); ?>" placeholder="e.g. 24 months">
                        </div>
                        <div class="field">
                            <label for="country_of_origin">Country of origin</label>
                            <?php $postedCountry = $_POST['country_of_origin'] ?? 'India'; ?>
                            <select id="country_of_origin" name="country_of_origin">
                                <?php foreach ($commonCountries as $c) : ?>
                                    <option value="<?php echo htmlspecialchars($c); ?>" <?php echo ($postedCountry === $c) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field" id="country_other_field" style="display:none;">
                            <label for="country_of_origin_other">Specify country</label>
                            <input type="text" id="country_of_origin_other" name="country_of_origin_other" value="<?php echo htmlspecialchars($_POST['country_of_origin_other'] ?? ''); ?>">
                        </div>
                    </div>
                </section>

                <section class="card" id="sec-images">
                    <h2>Images</h2>
                    <p class="card-hint">Leave a slot empty to skip it. You can add or remove more later on the edit page.</p>

                    <div id="image-rows">
                        <?php foreach ($initialImages as $i => $img) : render_image_row($i, $img);
                        endforeach; ?>
                    </div>

                    <button type="button" class="add-row-btn" id="add-image-btn">+ Add another image</button>
                </section>

                <section class="card" id="sec-seo">
                    <h2>SEO</h2>

                    <div class="field-grid">
                        <div class="field span-2">
                            <label for="meta_title">Meta title <span class="opt">optional</span></label>
                            <input type="text" id="meta_title" name="meta_title" value="<?php echo htmlspecialchars($_POST['meta_title'] ?? ''); ?>">
                        </div>
                        <div class="field span-2">
                            <label for="meta_description">Meta description <span class="opt">optional</span></label>
                            <textarea id="meta_description" name="meta_description" rows="2"><?php echo htmlspecialchars($_POST['meta_description'] ?? ''); ?></textarea>
                            <p class="field-help">Auto-trimmed to 200 characters if longer.</p>
                        </div>
                    </div>
                </section>

                <div class="save-bar">
                    <span class="save-note">All fields marked <span class="req">*</span> are required.</span>
                    <button type="submit" class="btn btn-primary">Save product</button>
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
                <input type="text" name="variants[__INDEX__][variant_name]" placeholder="e.g. 100 g">
            </div>
            <div class="field">
                <label>SKU<span class="req">*</span></label>
                <input type="text" name="variants[__INDEX__][sku]">
            </div>
            <div class="field">
                <label>Status</label>
                <select name="variants[__INDEX__][status]">
                    <?php foreach (PRODUCT_VARIANT_STATUSES as $vStatus) : ?>
                        <option value="<?php echo htmlspecialchars($vStatus); ?>"><?php echo htmlspecialchars($vStatus); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Price (₹)<span class="req">*</span></label>
                <input type="number" step="0.01" min="0" name="variants[__INDEX__][price]">
            </div>
            <div class="field">
                <label>Sale price (₹) <span class="opt">optional</span></label>
                <input type="number" step="0.01" min="0" name="variants[__INDEX__][sale_price]">
            </div>
            <div class="field">
                <label>Stock<span class="req">*</span></label>
                <input type="number" min="0" name="variants[__INDEX__][stock]" value="0">
            </div>
            <div class="field">
                <label>Weight (grams)</label>
                <input type="number" min="0" name="variants[__INDEX__][weight_grams]">
            </div>
            <div class="field">
                <label>Sort order</label>
                <input type="number" name="variants[__INDEX__][sort_order]" value="0">
            </div>
            <div class="field" style="justify-content:center;">
                <label class="checkbox-row" style="margin-top:20px;">
                    <input type="checkbox" class="default-variant-checkbox" name="variants[__INDEX__][is_default]" value="1">
                    Default variant
                </label>
            </div>
        </div>
    </div>
</template>

<template id="image-template">
    <div class="repeater-item" data-image-row>
        <div class="repeater-head">
            <span class="repeater-title">Image</span>
            <button type="button" class="repeater-remove" data-remove-row>Remove</button>
        </div>
        <div class="field-grid">
            <div class="field span-2">
                <label>Image file</label>
                <input type="file" name="images[__INDEX__][file]" accept="image/jpeg,image/png,image/webp,image/gif">
            </div>
            <div class="field">
                <label>Alt text</label>
                <input type="text" name="images[__INDEX__][alt_text]" placeholder="Describe the image">
            </div>
            <div class="field">
                <label>Sort order</label>
                <input type="number" name="images[__INDEX__][sort_order]" value="0">
            </div>
            <div class="field" style="justify-content:center;">
                <label class="checkbox-row" style="margin-top:20px;">
                    <input type="checkbox" class="primary-image-checkbox" name="images[__INDEX__][is_primary]" value="1">
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

        function ensureMinRows(container, templateSelector, min) {
            while (container.children.length < min) addRow(container, templateSelector);
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

        const variantContainer = document.querySelector('#variant-rows');
        const imageContainer = document.querySelector('#image-rows');

        wireRemoveButtons(variantContainer);
        wireExclusiveCheckbox(variantContainer, 'default-variant-checkbox');
        wireRemoveButtons(imageContainer);
        wireExclusiveCheckbox(imageContainer, 'primary-image-checkbox');
        ensureMinRows(imageContainer, '#image-template', 1);

        document.querySelector('#add-variant-btn').addEventListener('click', function() {
            addRow(variantContainer, '#variant-template');
        });
        document.querySelector('#add-image-btn').addEventListener('click', function() {
            addRow(imageContainer, '#image-template');
        });

        const hasVariants = document.querySelector('#has_variants');
        const stockBlock = document.querySelector('#stock-simple-block');
        const variantsBlock = document.querySelector('#variants-block');

        function syncInventoryMode() {
            const on = hasVariants.checked;
            stockBlock.style.display = on ? 'none' : '';
            variantsBlock.style.display = on ? '' : 'none';
            if (on) ensureMinRows(variantContainer, '#variant-template', 1);
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