<?php include __DIR__ . '/../../function/product.php'; ?>
<?php include __DIR__ . '/../../function/category.php'; ?>
<?php include __DIR__ . '/../../function/helper.php'; ?>
<?php require_once __DIR__ . '/../../config/database.php'; ?>

<?php
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

    $images = [];
    foreach ($_POST['images'] ?? [] as $img) {
        if (trim($img['image'] ?? '') !== '') {
            $images[] = [
                'image' => $img['image'],
                'alt_text' => $img['alt_text'] ?? null,
                'is_primary' => isset($img['is_primary']),
                'sort_order' => (int) ($img['sort_order'] ?? 0),
            ];
        }
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
            'images' => $images,
            'variants' => $variants,
        ];

        try {
            $newProductId = addProduct($conn, $data);
            header('Location: index.php?added=1');
            exit;
        } catch (InvalidArgumentException $e) {
            $errors[] = $e->getMessage();
        } catch (Exception $e) {
            $errors[] = 'Something went wrong while saving the product.';
        }
    }
}
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


<form method="POST" action="add.php">

    <h3>Basic info</h3>

    <label for="title">Title</label><br>
    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required><br>
    <small>The URL slug will be generated automatically from the title.</small><br>

    <label for="short_description">Short description</label><br>
    <textarea id="short_description" name="short_description" rows="2" cols="50"><?php echo htmlspecialchars($_POST['short_description'] ?? ''); ?></textarea><br>

    <label for="description">Description</label><br>
    <textarea id="description" name="description" rows="5" cols="50"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea><br>

    <label for="base_price">Base price</label><br>
    <input type="number" step="0.01" min="0" id="base_price" name="base_price" value="<?php echo htmlspecialchars($_POST['base_price'] ?? ''); ?>" required><br>

    <label for="base_sale_price">Base sale price</label><br>
    <input type="number" step="0.01" min="0" id="base_sale_price" name="base_sale_price" value="<?php echo htmlspecialchars($_POST['base_sale_price'] ?? ''); ?>"><br>

    <label for="status">Status</label><br>
    <select id="status" name="status">
        <?php foreach (PRODUCT_STATUSES as $statusOption) : ?>
            <option value="<?php echo htmlspecialchars($statusOption); ?>" <?php echo (($_POST['status'] ?? 'Draft') === $statusOption) ? 'selected' : ''; ?>><?php echo htmlspecialchars($statusOption); ?></option>
        <?php endforeach; ?>
    </select><br>

    <label>
        <input type="checkbox" name="featured" value="1" <?php echo isset($_POST['featured']) ? 'checked' : ''; ?>>
        Featured
    </label><br>

    <label>
        <input type="checkbox" name="bestseller" value="1" <?php echo isset($_POST['bestseller']) ? 'checked' : ''; ?>>
        Bestseller
    </label><br>

    <label>
        <input type="checkbox" name="trending" value="1" <?php echo isset($_POST['trending']) ? 'checked' : ''; ?>>
        Trending
    </label><br>

    <label for="meta_title">Meta title</label><br>
    <input type="text" id="meta_title" name="meta_title" value="<?php echo htmlspecialchars($_POST['meta_title'] ?? ''); ?>"><br>

    <label for="meta_description">Meta description</label><br>
    <textarea id="meta_description" name="meta_description" rows="2" cols="50"><?php echo htmlspecialchars($_POST['meta_description'] ?? ''); ?></textarea><br>
    <small>Auto-trimmed to 200 characters if longer.</small><br>

    <h3>Categories</h3>

    <?php foreach ($categories as $category) : ?>
        <label>
            <input type="checkbox" name="category_ids[]" value="<?php echo (int) $category['id']; ?>">
            <?php echo htmlspecialchars($category['name']); ?>
        </label><br>
    <?php endforeach; ?>

    <label for="primary_category_id">Primary category</label><br>
    <select id="primary_category_id" name="primary_category_id" required>
        <option value="">-- select --</option>
        <?php foreach ($categories as $category) : ?>
            <option value="<?php echo (int) $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
        <?php endforeach; ?>
    </select><br>

    <h3>Product details</h3>

    <label for="ingredients">Ingredients</label><br>
    <textarea id="ingredients" name="ingredients" rows="2" cols="50"><?php echo htmlspecialchars($_POST['ingredients'] ?? ''); ?></textarea><br>

    <label for="benefits">Benefits</label><br>
    <textarea id="benefits" name="benefits" rows="2" cols="50"><?php echo htmlspecialchars($_POST['benefits'] ?? ''); ?></textarea><br>

    <label for="directions">Directions</label><br>
    <textarea id="directions" name="directions" rows="2" cols="50"><?php echo htmlspecialchars($_POST['directions'] ?? ''); ?></textarea><br>

    <label for="dosage">Dosage</label><br>
    <textarea id="dosage" name="dosage" rows="2" cols="50"><?php echo htmlspecialchars($_POST['dosage'] ?? ''); ?></textarea><br>

    <label for="precautions">Precautions</label><br>
    <textarea id="precautions" name="precautions" rows="2" cols="50"><?php echo htmlspecialchars($_POST['precautions'] ?? ''); ?></textarea><br>

    <label for="manufacturer">Manufacturer</label><br>
    <input type="text" id="manufacturer" name="manufacturer" value="<?php echo htmlspecialchars($_POST['manufacturer'] ?? ''); ?>"><br>

    <label for="country_of_origin">Country of origin</label><br>
    <input type="text" id="country_of_origin" name="country_of_origin" value="<?php echo htmlspecialchars($_POST['country_of_origin'] ?? 'India'); ?>"><br>

    <label for="shelf_life">Shelf life</label><br>
    <input type="text" id="shelf_life" name="shelf_life" value="<?php echo htmlspecialchars($_POST['shelf_life'] ?? ''); ?>"><br>

    <h3>Images</h3>

    <?php for ($i = 0; $i < 5; $i++) : ?>
        <p>
            Image <?php echo $i + 1; ?><br>
            <label>Path/URL</label>
            <input type="text" name="images[<?php echo $i; ?>][image]"><br>
            <label>Alt text</label>
            <input type="text" name="images[<?php echo $i; ?>][alt_text]"><br>
            <label>
                <input type="checkbox" name="images[<?php echo $i; ?>][is_primary]" value="1">
                Primary image
            </label><br>
            <label>Sort order</label>
            <input type="number" name="images[<?php echo $i; ?>][sort_order]" value="0">
        </p>
    <?php endfor; ?>

    <h3>Variants</h3>

    <label>
        <input type="checkbox" name="has_variants" value="1" <?php echo isset($_POST['has_variants']) ? 'checked' : ''; ?>>
        This product has variants
    </label><br><br>

    <?php for ($i = 0; $i < 5; $i++) : ?>
        <p>
            Variant <?php echo $i + 1; ?><br>
            <label>Name</label>
            <input type="text" name="variants[<?php echo $i; ?>][variant_name]"><br>
            <label>SKU</label>
            <input type="text" name="variants[<?php echo $i; ?>][sku]"><br>
            <label>Price</label>
            <input type="number" step="0.01" min="0" name="variants[<?php echo $i; ?>][price]"><br>
            <label>Sale price</label>
            <input type="number" step="0.01" min="0" name="variants[<?php echo $i; ?>][sale_price]"><br>
            <label>Stock</label>
            <input type="number" min="0" name="variants[<?php echo $i; ?>][stock]" value="0"><br>
            <label>Weight (grams)</label>
            <input type="number" min="0" name="variants[<?php echo $i; ?>][weight_grams]"><br>
            <label>
                <input type="checkbox" name="variants[<?php echo $i; ?>][is_default]" value="1">
                Default variant
            </label><br>
            <label>Status</label>
            <select name="variants[<?php echo $i; ?>][status]">
                <?php foreach (PRODUCT_VARIANT_STATUSES as $vStatus) : ?>
                    <option value="<?php echo htmlspecialchars($vStatus); ?>"><?php echo htmlspecialchars($vStatus); ?></option>
                <?php endforeach; ?>
            </select><br>
            <label>Sort order</label>
            <input type="number" name="variants[<?php echo $i; ?>][sort_order]" value="0">
        </p>
    <?php endfor; ?>

    <br>
    <button type="submit">Save product</button>
</form>