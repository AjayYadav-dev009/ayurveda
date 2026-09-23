<?php
$pageTitle = 'View Product';
$activeNav = 'products';
?>
<?php include __DIR__ . '/../../function/product.php'; ?>
<?php include __DIR__ . '/../../function/helper.php'; ?>
<?php include __DIR__ . '/../../includes/auth.php'; ?>
<?php include __DIR__ . '/../include/header.php'; ?>

<style>
    .padmin {
        max-width: 900px;
        margin: 0 auto;
        padding: 28px 28px 80px;
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

    .padmin .card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: 22px 24px;
        margin-bottom: 18px;
    }

    .padmin .card h2 {
        font-size: 1.02rem;
        font-weight: 800;
        margin: 0 0 14px;
    }

    .padmin .empty-note {
        font-size: 0.87rem;
        color: var(--muted);
        background: var(--mist);
        border-radius: 9px;
        padding: 12px 14px;
    }

    .padmin .badge {
        display: inline-block;
        padding: 4px 11px;
        border-radius: 99px;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .padmin .badge-active {
        background: var(--leaf-tint);
        color: var(--leaf-dark);
    }

    .padmin .badge-inactive {
        background: var(--mist);
        color: var(--muted);
    }

    .padmin .badge-draft {
        background: #fdf1de;
        color: #93650f;
    }

    .padmin .flag {
        display: inline-block;
        padding: 3px 10px;
        margin-right: 6px;
        border-radius: 99px;
        background: var(--sky-tint);
        color: var(--sky);
        font-size: 0.74rem;
        font-weight: 700;
    }

    .padmin dl.meta-list {
        margin: 0;
    }

    .padmin dl.meta-list dt {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--muted);
        margin-top: 16px;
    }

    .padmin dl.meta-list dt:first-child {
        margin-top: 0;
    }

    .padmin dl.meta-list dd {
        margin: 4px 0 0;
        white-space: pre-wrap;
    }

    .padmin .price-sale {
        color: #b3382c;
        font-weight: 700;
    }

    .padmin .price-original {
        text-decoration: line-through;
        color: var(--muted);
        margin-left: 8px;
        font-size: 0.9em;
    }

    .padmin .product-thumb {
        width: 110px;
        height: 110px;
        object-fit: cover;
        border-radius: 9px;
        border: 1px solid var(--line);
        display: inline-block;
        margin: 0 10px 10px 0;
    }

    .padmin .stock-count {
        font-variant-numeric: tabular-nums;
        font-weight: 700;
    }

    .padmin .stock-count.is-low {
        color: #b3382c;
    }

    .padmin .stock-count.is-zero {
        color: #b3382c;
    }

    .padmin .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.88rem;
    }

    .padmin .data-table th,
    .padmin .data-table td {
        text-align: left;
        padding: 10px 12px;
        border-bottom: 1px solid var(--line);
    }

    .padmin .data-table th {
        font-weight: 700;
        color: var(--muted);
        font-size: 0.78rem;
    }

    .padmin .data-table tr:last-child td {
        border-bottom: none;
    }
</style>

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

function stock_class($qty): string
{
    $qty = (int) $qty;
    if ($qty <= 0) return 'is-zero';
    if ($qty <= 10) return 'is-low';
    return '';
}
?>

<div class="padmin">

    <div class="topbar">
        <div>
            <h1><?php echo htmlspecialchars($product['title']); ?></h1>
            <span class="subtitle">Product #<?php echo (int) $product['id']; ?></span>
        </div>
        <div class="topbar-actions">
            <a href="index.php" class="btn btn-secondary">&laquo; Back to products</a>
            <a href="edit.php?id=<?php echo (int) $product['id']; ?>" class="btn btn-primary">Edit</a>
        </div>
    </div>

    <div class="card">
        <p style="margin-top:0;">
            <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($product['status']); ?></span>
            <?php if ((int) $product['featured'] === 1) : ?><span class="flag">Featured</span><?php endif; ?>
            <?php if ((int) $product['bestseller'] === 1) : ?><span class="flag">Bestseller</span><?php endif; ?>
            <?php if ((int) $product['trending'] === 1) : ?><span class="flag">Trending</span><?php endif; ?>
            <?php if ((int) $product['has_variants'] === 1) : ?><span class="flag">Has variants</span><?php endif; ?>
        </p>

        <dl class="meta-list">
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

            <dt>Stock</dt>
            <dd>
                <?php if ((int) $product['has_variants'] === 1) : ?>
                    <?php $variantTotal = array_sum(array_map(fn($v) => (int) $v['stock'], $variants)); ?>
                    <span class="stock-count <?php echo stock_class($variantTotal); ?>"><?php echo $variantTotal; ?> units</span>
                    <span style="font-size:0.82rem; color:var(--muted); margin-left:6px;">(total across variants)</span>
                <?php else : ?>
                    <span class="stock-count <?php echo stock_class($product['stock']); ?>"><?php echo (int) $product['stock']; ?> units</span>
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
    </div>

    <div class="card">
        <h2>Product details</h2>
        <?php if ($details === null) : ?>
            <p class="empty-note">No additional details recorded.</p>
        <?php else : ?>
            <dl class="meta-list">
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
    </div>

    <div class="card">
        <h2>Images</h2>
        <?php if (empty($images)) : ?>
            <p class="empty-note">No images uploaded.</p>
        <?php else : ?>
            <div>
                <?php foreach ($images as $image) : ?>
                    <div style="display:inline-block; text-align:center; margin: 0 10px 10px 0;">
                        <img class="product-thumb" src="<?php echo htmlspecialchars(getProductImageUrl($image['image'])); ?>" alt="<?php echo htmlspecialchars($image['alt_text'] ?? ''); ?>"><br>
                        <?php if ((int) $image['is_primary'] === 1) : ?>
                            <span class="badge badge-active">Primary</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>Variants</h2>
        <?php if (empty($variants)) : ?>
            <p class="empty-note">No variants &mdash; this product's stock is managed directly (see Stock above).</p>
        <?php else : ?>
            <div style="overflow-x:auto;">
                <table class="data-table">
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
                                <td><span class="stock-count <?php echo stock_class($variant['stock']); ?>"><?php echo (int) $variant['stock']; ?></span></td>
                                <td><?php echo $variant['weight_grams'] !== null ? (int) $variant['weight_grams'] : '&mdash;'; ?></td>
                                <td><?php echo (int) $variant['is_default'] === 1 ? 'Yes' : 'No'; ?></td>
                                <td><?php echo htmlspecialchars($variant['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php include __DIR__ . '/../include/footer.php'; ?>