<?php include __DIR__ . '/../../function/product.php'; ?>
<?php include __DIR__ . '/../../function/category.php'; ?>
<?php include __DIR__ . '/../../function/helper.php'; ?>
<?php require_once __DIR__ . '/../../config/database.php'; ?>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<?php
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    die('Invalid product id.');
}

$relations = getProductWithRelations($conn, $id);
if ($relations['product'] === null) {
    die('Product not found.');
}

$product = $relations['product'];
$details = $relations['details'];
$images = $relations['images'];
$variants = $relations['variants'];
$categories = $relations['categories'];

$hasSale = $product['base_sale_price'] !== null && $product['base_sale_price'] !== '';

$statusClass = match ($product['status']) {
    'Active' => 'badge-active',
    'Inactive' => 'badge-inactive',
    default => 'badge-draft',
};
?>

<style>
    table {
        border-collapse: collapse;
        width: 100%;
        font-family: sans-serif;
        margin-bottom: 20px;
    }

    th,
    td {
        border: 2px solid #000000;
        padding: 8px;
        text-align: left;
        vertical-align: top;
    }

    th {
        background-color: #dddcdc;
        font-weight: 900;
        color: #000000;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    dl {
        font-family: sans-serif;
    }

    dt {
        font-weight: 700;
        margin-top: 10px;
    }

    dd {
        margin-left: 0;
        margin-bottom: 5px;
        white-space: pre-wrap;
    }

    .product-thumb {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 4px;
        display: inline-block;
        margin-right: 10px;
    }

    .price-sale {
        color: #dc3545;
        font-weight: 600;
    }

    .price-original {
        text-decoration: line-through;
        color: #888;
        margin-left: 6px;
        font-size: 0.9em;
    }

    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .badge-active {
        background-color: #d4edda;
        color: #155724;
    }

    .badge-inactive {
        background-color: #f1f1f1;
        color: #555555;
    }

    .badge-draft {
        background-color: #fff3cd;
        color: #856404;
    }

    .flag {
        display: inline-block;
        padding: 3px 8px;
        margin-right: 4px;
        border-radius: 10px;
        background-color: #e2e3e5;
        color: #383d41;
        font-size: 0.75rem;
    }

    .btn {
        display: inline-block;
        padding: 10px 20px;
        font-size: 1rem;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-edit {
        background-color: #ffc107;
        color: #212529;
    }

    .btn-back {
        background-color: #6c757d;
    }
</style>

<div style="margin-bottom: 15px;">
    <a href="index.php" class="btn btn-back">&laquo; Back to products</a>
    <a href="edit.php?id=<?php echo (int) $product['id']; ?>" class="btn btn-edit">Edit</a>
</div>

<h2><?php echo htmlspecialchars($product['title']); ?></h2>
<p>
    <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($product['status']); ?></span>
    <?php if ((int) $product['featured'] === 1) : ?><span class="flag">Featured</span><?php endif; ?>
    <?php if ((int) $product['bestseller'] === 1) : ?><span class="flag">Bestseller</span><?php endif; ?>
    <?php if ((int) $product['trending'] === 1) : ?><span class="flag">Trending</span><?php endif; ?>
    <?php if ((int) $product['has_variants'] === 1) : ?><span class="flag">Has variants</span><?php endif; ?>
</p>

<dl>
    <dt>Slug</dt>
    <dd><?php echo htmlspecialchars($product['slug']); ?></dd>

    <dt>Price</dt>
    <dd>
        <?php if ($hasSale) : ?>
            <span class="price-sale">₹<?php echo number_format((float) $product['base_sale_price'], 2); ?></span>
            <span class="price-original">₹<?php echo number_format((float) $product['base_price'], 2); ?></span>
        <?php else : ?>
            ₹<?php echo number_format((float) $product['base_price'], 2); ?>
        <?php endif; ?>
    </dd>

    <dt>Categories</dt>
    <dd>
        <?php if (empty($categories)) : ?>
            &mdash;
        <?php else : ?>
            <?php foreach ($categories as $category) : ?>
                <?php echo htmlspecialchars($category['name']); ?><?php echo !empty($category['is_primary']) ? ' (primary)' : ''; ?><br>
            <?php endforeach; ?>
        <?php endif; ?>
    </dd>

    <dt>Short description</dt>
    <dd><?php echo $product['short_description'] !== null ? nl2br(htmlspecialchars($product['short_description'])) : '&mdash;'; ?></dd>

    <dt>Description</dt>
    <dd><?php echo $product['description'] !== null ? nl2br(htmlspecialchars($product['description'])) : '&mdash;'; ?></dd>

    <dt>Meta title</dt>
    <dd><?php echo $product['meta_title'] !== null ? htmlspecialchars($product['meta_title']) : '&mdash;'; ?></dd>

    <dt>Meta description</dt>
    <dd><?php echo $product['meta_description'] !== null ? htmlspecialchars($product['meta_description']) : '&mdash;'; ?></dd>

    <dt>Created at</dt>
    <dd><?php echo htmlspecialchars($product['created_at']); ?></dd>

    <dt>Updated at</dt>
    <dd><?php echo htmlspecialchars($product['updated_at']); ?></dd>
</dl>

<h3>Product details</h3>

<?php if ($details === null) : ?>
    <p>No additional details recorded.</p>
<?php else : ?>
    <dl>
        <dt>Ingredients</dt>
        <dd><?php echo $details['ingredients'] !== null ? nl2br(htmlspecialchars($details['ingredients'])) : '&mdash;'; ?></dd>

        <dt>Benefits</dt>
        <dd><?php echo $details['benefits'] !== null ? nl2br(htmlspecialchars($details['benefits'])) : '&mdash;'; ?></dd>

        <dt>Directions</dt>
        <dd><?php echo $details['directions'] !== null ? nl2br(htmlspecialchars($details['directions'])) : '&mdash;'; ?></dd>

        <dt>Dosage</dt>
        <dd><?php echo $details['dosage'] !== null ? nl2br(htmlspecialchars($details['dosage'])) : '&mdash;'; ?></dd>

        <dt>Precautions</dt>
        <dd><?php echo $details['precautions'] !== null ? nl2br(htmlspecialchars($details['precautions'])) : '&mdash;'; ?></dd>

        <dt>Manufacturer</dt>
        <dd><?php echo $details['manufacturer'] !== null ? htmlspecialchars($details['manufacturer']) : '&mdash;'; ?></dd>

        <dt>Country of origin</dt>
        <dd><?php echo $details['country_of_origin'] !== null ? htmlspecialchars($details['country_of_origin']) : '&mdash;'; ?></dd>

        <dt>Shelf life</dt>
        <dd><?php echo $details['shelf_life'] !== null ? htmlspecialchars($details['shelf_life']) : '&mdash;'; ?></dd>
    </dl>
<?php endif; ?>

<h3>Images</h3>

<?php if (empty($images)) : ?>
    <p>No images uploaded.</p>
<?php else : ?>
    <div>
        <?php foreach ($images as $image) : ?>
            <div style="display:inline-block; text-align:center; margin: 0 10px 10px 0;">
                <img class="product-thumb" src="<?php echo htmlspecialchars($image['image']); ?>" alt="<?php echo htmlspecialchars($image['alt_text'] ?? ''); ?>"><br>
                <?php if ((int) $image['is_primary'] === 1) : ?>
                    <span class="badge badge-active">Primary</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h3>Variants</h3>

<?php if (empty($variants)) : ?>
    <p>No variants.</p>
<?php else : ?>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>SKU</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Weight (g)</th>
                <th>Default</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($variants as $variant) : ?>
                <?php $variantHasSale = $variant['sale_price'] !== null && $variant['sale_price'] !== ''; ?>
                <tr>
                    <td><?php echo htmlspecialchars($variant['variant_name']); ?></td>
                    <td><?php echo htmlspecialchars($variant['sku']); ?></td>
                    <td>
                        <?php if ($variantHasSale) : ?>
                            <span class="price-sale">₹<?php echo number_format((float) $variant['sale_price'], 2); ?></span>
                            <span class="price-original">₹<?php echo number_format((float) $variant['price'], 2); ?></span>
                        <?php else : ?>
                            ₹<?php echo number_format((float) $variant['price'], 2); ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo (int) $variant['stock']; ?></td>
                    <td><?php echo $variant['weight_grams'] !== null ? (int) $variant['weight_grams'] : '&mdash;'; ?></td>
                    <td><?php echo (int) $variant['is_default'] === 1 ? 'Yes' : 'No'; ?></td>
                    <td><?php echo htmlspecialchars($variant['status']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>