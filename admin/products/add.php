<?php include __DIR__ . '/../../function/product.php'; ?>
<?php include __DIR__ . '/../../function/category.php'; ?>
<?php include __DIR__ . '/../../function/helper.php'; ?>
<?php require_once __DIR__ . '/../../config/database.php'; ?>
<?php include __DIR__ . '/../../includes/auth.php'; ?>

<?php
// A short, fixed list of common countries of origin. "Other" reveals a free
// text field (admin.js: wireCountryOther) so admins aren't stuck typing
// "India" from scratch every time but can still enter anything.
const PRODUCT_COUNTRY_OPTIONS = ['India', 'Nepal', 'Sri Lanka', 'USA', 'Other'];

/**
 * Render the fields for one image repeater row. Used both for the
 * server-rendered row(s) on initial/error load and — with $idx set to the
 * literal string "__INDEX__" — for the <template> that admin.js clones
 * when the admin clicks "Add image".
 */
function renderImageRowFields($idx, $altText = '', $isPrimary = false, $sortOrder = 0)
{
    ob_start();

    include __DIR__ . '/../include/header.php';
?>
    <div class="field">
        <label>Image file</label>
        <input type="file" name="images[<?php echo $idx; ?>][file]" accept="image/jpeg,image/png,image/webp,image/gif">
    </div>
    <div class="field">
        <label>Alt text</label>
        <input type="text" name="images[<?php echo $idx; ?>][alt_text]" value="<?php echo htmlspecialchars($altText); ?>" placeholder="Describe the image">
    </div>
    <div class="field">
        <label>Sort order</label>
        <input type="number" name="images[<?php echo $idx; ?>][sort_order]" value="<?php echo (int) $sortOrder; ?>">
    </div>
    <div class="field">
        <label>&nbsp;</label>
        <label class="checkbox-row">
            <input type="checkbox" class="primary-image-checkbox" name="images[<?php echo $idx; ?>][is_primary]" value="1" <?php echo $isPrimary ? 'checked' : ''; ?>>
            Primary
        </label>
    </div>
    <div class="field">
        <label>&nbsp;</label>
        <button type="button" class="btn btn-ghost btn-sm" data-remove-row>Remove</button>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Render one variant repeater card (two rows of fields plus header/remove).
 * Same "__INDEX__" trick as renderImageRowFields() for the JS template.
 */
function renderVariantCard($idx, array $v = [])
{
    ob_start();
?>
    <div class="variant-card repeater-item">
        <div class="variant-card__header">
            <span>Variant</span>
            <button type="button" class="btn btn-ghost btn-sm" data-remove-row>Remove</button>
        </div>
        <div class="variant-row">
            <div class="field">
                <label>Name</label>
                <input type="text" name="variants[<?php echo $idx; ?>][variant_name]" value="<?php echo htmlspecialchars($v['variant_name'] ?? ''); ?>" placeholder="e.g. 100g pack">
            </div>
            <div class="field">
                <label>SKU</label>
                <input type="text" name="variants[<?php echo $idx; ?>][sku]" value="<?php echo htmlspecialchars($v['sku'] ?? ''); ?>">
            </div>
            <div class="field">
                <label>Price</label>
                <input type="number" step="0.01" min="0" name="variants[<?php echo $idx; ?>][price]" value="<?php echo htmlspecialchars($v['price'] ?? ''); ?>">
            </div>
            <div class="field">
                <label>Sale price</label>
                <input type="number" step="0.01" min="0" name="variants[<?php echo $idx; ?>][sale_price]" value="<?php echo htmlspecialchars($v['sale_price'] ?? ''); ?>">
            </div>
            <div class="field">
                <label>Stock</label>
                <input type="number" min="0" name="variants[<?php echo $idx; ?>][stock]" value="<?php echo htmlspecialchars($v['stock'] ?? '0'); ?>">
            </div>
            <div class="field">
                <label>Weight (g)</label>
                <input type="number" min="0" name="variants[<?php echo $idx; ?>][weight_grams]" value="<?php echo htmlspecialchars($v['weight_grams'] ?? ''); ?>">
            </div>
        </div>
        <div class="variant-secondary">
            <div class="field">
                <label>Status</label>
                <select name="variants[<?php echo $idx; ?>][status]">
                    <?php foreach (PRODUCT_VARIANT_STATUSES as $vStatus) : ?>
                        <option value="<?php echo htmlspecialchars($vStatus); ?>" <?php echo (($v['status'] ?? 'Active') === $vStatus) ? 'selected' : ''; ?>><?php echo htmlspecialchars($vStatus); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Sort order</label>
                <input type="number" name="variants[<?php echo $idx; ?>][sort_order]" value="<?php echo (int) ($v['sort_order'] ?? 0); ?>">
            </div>
            <div class="field">
                <label>&nbsp;</label>
                <label class="checkbox-row">
                    <input type="checkbox" class="default-variant-checkbox" name="variants[<?php echo $idx; ?>][is_default]" value="1" <?php echo !empty($v['is_default']) ? 'checked' : ''; ?>>
                    Default variant
                </label>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}

$errors = [];

$categoriesResult = getCategories($conn);
$categories = [];
while ($row = mysqli_fetch_assoc($categoriesResult)) {
    $categories[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Slug is derived from the title instead of typed by hand.
    $slug = null;
    try {
        $slug = createSlug($_POST['title'] ?? '');
    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    }

    // Meta title/description are optional on the product, so only run
    // them through the helpers (which reject empty input) when the
    // admin actually filled them in.
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

    // "Other" reveals a free-text country field on the client; resolve it
    // back down to a single string here.
    $countryOfOrigin = $_POST['country_of_origin'] ?? 'India';
    if ($countryOfOrigin === 'Other') {
        $customCountry = trim($_POST['country_of_origin_other'] ?? '');
        $countryOfOrigin = $customCountry !== '' ? $customCountry : 'Other';
    }

    // Images are handled after the product is saved (see below) — actual
    // files need to be uploaded and validated, and uploadProductImage()
    // needs a real product id to build the storage path, which doesn't
    // exist yet at this point for a brand new product.

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
            'has_variants' => isset($_POST['has_variants']),
            // Simple (non-variant) products track stock directly on the
            // product row. addProduct() already ignores this when
            // has_variants is true, so it's always safe to send it.
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
            'primary_category_id' => isset($_POST['primary_category_id']) ? (int) $_POST['primary_category_id'] : null,
            'variants' => $variants,
        ];

        try {
            $newProductId = addProduct($conn, $data);

            // Now that the product exists, actually upload and attach any
            // image files the admin selected. PHP groups a nested file
            // input like images[i][file] as $_FILES['images']['name'][i]['file'],
            // ['tmp_name'][i]['file'], etc — reshape that into one plain
            // file array per image slot before touching uploadProductImage().
            $imageErrors = [];
            $files = [];
            foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $attr) {
                foreach ($_FILES['images'][$attr] ?? [] as $i => $group) {
                    $files[$i][$attr] = $group['file'] ?? null;
                }
            }

            foreach ($files as $i => $file) {
                if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    continue; // No file chosen for this slot — fine, skip it.
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
                // The product itself saved fine; only some images failed.
                // Send the admin to the product's image manager so they can
                // see what went in and retry the failed ones there.
                header('Location: image.php?product_id=' . $newProductId . '&image_errors=' . urlencode(implode(' | ', $imageErrors)));
                exit;
            }

            header('Location: index.php?added=1');
            exit;
        } catch (InvalidArgumentException $e) {
            $errors[] = $e->getMessage();
        } catch (Exception $e) {
            $errors[] = 'Something went wrong while saving the product.';
        }
    }
}

// Rebuild the repeater rows from posted data on a failed submit, so the
// admin doesn't lose everything they typed. Re-indexed to a contiguous
// 0..n-1 range — admin.js's "add row" logic assumes that shape.
$postedImages = array_values($_POST['images'] ?? []);
if (empty($postedImages)) {
    $postedImages = [[]];
}

$postedVariants = array_values($_POST['variants'] ?? []);
if (empty($postedVariants)) {
    $postedVariants = [[]];
}

$selectedCategoryIds = array_map('intval', $_POST['category_ids'] ?? []);
$postedCountry = $_POST['country_of_origin'] ?? 'India';
$postedCountryOther = in_array($postedCountry, PRODUCT_COUNTRY_OPTIONS, true) ? '' : $postedCountry;
if ($postedCountryOther !== '') {
    $postedCountry = 'Other';
}
?>

<div class="page-header">
    <div>
        <h1>Add new product</h1>
        <p>Fields marked <strong>*</strong> are required. Images and variants can also be edited later from the product's own page.</p>
    </div>
    <a href="index.php" class="btn btn-secondary">&larr; Back to products</a>
</div>

<?php if (!empty($errors)) : ?>
    <div class="notice notice-error">
        <ul style="margin:0; padding-left:18px;">
            <?php foreach ($errors as $error) : ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="product-form-layout">
    <nav id="section-nav" class="form-section-nav">
        <a href="#section-basic">Basic info</a>
        <a href="#section-categories">Categories</a>
        <a href="#section-details">Product details</a>
        <a href="#section-images">Images</a>
        <a href="#section-variants">Variants &amp; stock</a>
    </nav>

    <form method="POST" action="add.php" enctype="multipart/form-data" class="card product-form">

        <fieldset id="section-basic">
            <legend>Basic info</legend>
            <div class="form-grid">
                <div class="field full">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                    <p class="hint">The URL slug will be generated automatically from the title.</p>
                </div>

                <div class="field full">
                    <label for="short_description">Short description</label>
                    <textarea id="short_description" name="short_description" rows="2"><?php echo htmlspecialchars($_POST['short_description'] ?? ''); ?></textarea>
                </div>

                <div class="field full">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <div class="field">
                    <label for="base_price">Base price *</label>
                    <input type="number" step="0.01" min="0" id="base_price" name="base_price" value="<?php echo htmlspecialchars($_POST['base_price'] ?? ''); ?>" required>
                </div>

                <div class="field">
                    <label for="base_sale_price">Base sale price</label>
                    <input type="number" step="0.01" min="0" id="base_sale_price" name="base_sale_price" value="<?php echo htmlspecialchars($_POST['base_sale_price'] ?? ''); ?>">
                </div>

                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <?php foreach (PRODUCT_STATUSES as $statusOption) : ?>
                            <option value="<?php echo htmlspecialchars($statusOption); ?>" <?php echo (($_POST['status'] ?? 'Draft') === $statusOption) ? 'selected' : ''; ?>><?php echo htmlspecialchars($statusOption); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label>&nbsp;</label>
                    <div style="display:flex; gap:18px; padding-top:8px;">
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

                <div class="field full">
                    <label for="meta_title">Meta title</label>
                    <input type="text" id="meta_title" name="meta_title" value="<?php echo htmlspecialchars($_POST['meta_title'] ?? ''); ?>">
                </div>

                <div class="field full">
                    <label for="meta_description">Meta description</label>
                    <textarea id="meta_description" name="meta_description" rows="2"><?php echo htmlspecialchars($_POST['meta_description'] ?? ''); ?></textarea>
                    <p class="hint">Auto-trimmed to 200 characters if longer.</p>
                </div>
            </div>
        </fieldset>

        <fieldset id="section-categories">
            <legend>Categories</legend>

            <div id="category-checkboxes" class="category-grid">
                <?php foreach ($categories as $category) : ?>
                    <label class="checkbox-row">
                        <input type="checkbox" class="category-checkbox" name="category_ids[]" value="<?php echo (int) $category['id']; ?>" <?php echo in_array((int) $category['id'], $selectedCategoryIds, true) ? 'checked' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="field" style="max-width:340px;">
                <label for="primary_category_id">Primary category *</label>
                <select id="primary_category_id" name="primary_category_id" required>
                    <option value="">-- select --</option>
                    <?php foreach ($categories as $category) : ?>
                        <option value="<?php echo (int) $category['id']; ?>" <?php echo (isset($_POST['primary_category_id']) && (int) $_POST['primary_category_id'] === (int) $category['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="hint">Check a category above to enable it here.</p>
            </div>
        </fieldset>

        <fieldset id="section-details">
            <legend>Product details</legend>
            <div class="form-grid">
                <div class="field">
                    <label for="ingredients">Ingredients</label>
                    <textarea id="ingredients" name="ingredients" rows="3"><?php echo htmlspecialchars($_POST['ingredients'] ?? ''); ?></textarea>
                </div>

                <div class="field">
                    <label for="benefits">Benefits</label>
                    <textarea id="benefits" name="benefits" rows="3"><?php echo htmlspecialchars($_POST['benefits'] ?? ''); ?></textarea>
                </div>

                <div class="field">
                    <label for="directions">Directions</label>
                    <textarea id="directions" name="directions" rows="3"><?php echo htmlspecialchars($_POST['directions'] ?? ''); ?></textarea>
                </div>

                <div class="field">
                    <label for="dosage">Dosage</label>
                    <textarea id="dosage" name="dosage" rows="3"><?php echo htmlspecialchars($_POST['dosage'] ?? ''); ?></textarea>
                </div>

                <div class="field full">
                    <label for="precautions">Precautions</label>
                    <textarea id="precautions" name="precautions" rows="2"><?php echo htmlspecialchars($_POST['precautions'] ?? ''); ?></textarea>
                </div>

                <div class="field">
                    <label for="manufacturer">Manufacturer</label>
                    <input type="text" id="manufacturer" name="manufacturer" value="<?php echo htmlspecialchars($_POST['manufacturer'] ?? ''); ?>">
                </div>

                <div class="field">
                    <label for="country_of_origin">Country of origin</label>
                    <select id="country_of_origin" name="country_of_origin">
                        <?php foreach (PRODUCT_COUNTRY_OPTIONS as $countryOption) : ?>
                            <option value="<?php echo htmlspecialchars($countryOption); ?>" <?php echo ($postedCountry === $countryOption) ? 'selected' : ''; ?>><?php echo htmlspecialchars($countryOption); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div id="country-other-wrap" class="country-other" style="<?php echo $postedCountry === 'Other' ? '' : 'display:none;'; ?>">
                        <input type="text" name="country_of_origin_other" value="<?php echo htmlspecialchars($postedCountryOther); ?>" placeholder="Enter country">
                    </div>
                </div>

                <div class="field">
                    <label for="shelf_life">Shelf life</label>
                    <input type="text" id="shelf_life" name="shelf_life" value="<?php echo htmlspecialchars($_POST['shelf_life'] ?? ''); ?>" placeholder="e.g. 24 months">
                </div>
            </div>
        </fieldset>

        <fieldset id="section-images">
            <legend>Images</legend>
            <p class="hint" style="margin-top:0;">Add as many images as you need. You can also add, remove, or reorder them later from the product's image manager.</p>

            <div id="image-rows">
                <?php foreach ($postedImages as $i => $img) : ?>
                    <div class="repeat-row image-row repeater-item">
                        <?php echo renderImageRowFields($i, $img['alt_text'] ?? '', isset($img['is_primary']), $img['sort_order'] ?? 0); ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <template id="image-row-template">
                <div class="repeat-row image-row repeater-item">
                    <?php echo renderImageRowFields('__INDEX__'); ?>
                </div>
            </template>

            <button type="button" id="add-image-btn" class="btn btn-secondary btn-sm">+ Add image</button>
        </fieldset>

        <fieldset id="section-variants">
            <legend>Variants &amp; stock</legend>

            <div class="inventory-toggle">
                <label class="checkbox-row">
                    <input type="checkbox" id="has_variants" name="has_variants" value="1" <?php echo isset($_POST['has_variants']) ? 'checked' : ''; ?>>
                    This product has variants (different sizes, packs, etc.)
                </label>
            </div>

            <div id="stock-simple-block" class="field" style="max-width:220px;">
                <label for="stock">Stock quantity *</label>
                <input type="number" id="stock" min="0" name="stock" value="<?php echo htmlspecialchars($_POST['stock'] ?? '0'); ?>">
                <p class="hint">Units currently available. Not used once variants are turned on — stock is tracked per variant instead.</p>
            </div>

            <div id="variants-block" style="display:none;">
                <div id="variant-rows">
                    <?php foreach ($postedVariants as $i => $v) : ?>
                        <?php echo renderVariantCard($i, $v); ?>
                    <?php endforeach; ?>
                </div>

                <template id="variant-row-template">
                    <?php echo renderVariantCard('__INDEX__'); ?>
                </template>

                <button type="button" id="add-variant-btn" class="btn btn-secondary btn-sm">+ Add variant</button>
            </div>
        </fieldset>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save product</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>

<script>
    AdminProductForm.init({
        variantContainer: '#variant-rows',
        variantTemplate: '#variant-row-template',
        addVariantBtn: '#add-variant-btn',
        minVariantRows: 1,
        imageContainer: '#image-rows',
        imageTemplate: '#image-row-template',
        addImageBtn: '#add-image-btn',
        minImageRows: 1,
        hasVariantsCheckbox: '#has_variants',
        stockSimpleBlock: '#stock-simple-block',
        variantsBlock: '#variants-block',
        countrySelect: '#country_of_origin',
        countryOtherField: '#country-other-wrap'
    });
</script>