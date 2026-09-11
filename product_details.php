<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/function/product.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if ($slug === '') {
    header('Location: ' . BASE_URL . 'products.php');
    exit;
}

// No getProductBySlug() exists in function/product.php, and adding one just
// to return an id would duplicate what getProductWithRelations() already
// does from an id — so resolve slug -> id here, then hand off to the
// existing shared function for everything else.
$stmt = mysqli_prepare($conn, "SELECT id FROM products WHERE slug = ? AND status = 'Active' LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $slug);
mysqli_stmt_execute($stmt);
$productRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$productRow) {
    header('Location: ' . BASE_URL . 'products.php');
    exit;
}

$data = getProductWithRelations($conn, (int) $productRow['id']);
$product = $data['product'];
$details = $data['details'];
$images = $data['images'];
$categories = $data['categories'];

if (!$product) {
    header('Location: ' . BASE_URL . 'products.php');
    exit;
}

// Storefront only ever shows Active variants, regardless of what admin has saved.
$variants = array_values(array_filter($data['variants'], function ($v) {
    return $v['status'] === 'Active';
}));

$hasVariants = !empty($product['has_variants']) && !empty($variants);

$defaultVariant = null;
if ($hasVariants) {
    foreach ($variants as $v) {
        if (!empty($v['is_default'])) {
            $defaultVariant = $v;
            break;
        }
    }
    if (!$defaultVariant) {
        $defaultVariant = $variants[0];
    }
}

// Effective price/stock for whatever is selected by default on page load —
// JS takes over from here when the user picks a different variant.
if ($hasVariants) {
    $currentPrice = (float) $defaultVariant['price'];
    $currentSalePrice = $defaultVariant['sale_price'] !== null ? (float) $defaultVariant['sale_price'] : null;
    $currentStock = (int) $defaultVariant['stock'];
} else {
    $currentPrice = (float) $product['base_price'];
    $currentSalePrice = !empty($product['base_sale_price']) ? (float) $product['base_sale_price'] : null;
    $currentStock = (int) $product['stock'];
}

$hasSale = $currentSalePrice !== null && $currentSalePrice < $currentPrice;
$isOutOfStock = $currentStock <= 0;

// Primary category, if any, drives the breadcrumb.
$primaryCategory = null;
foreach ($categories as $cat) {
    if (!empty($cat['is_primary'])) {
        $primaryCategory = $cat;
        break;
    }
}
if (!$primaryCategory && !empty($categories)) {
    $primaryCategory = $categories[0];
}

// product_details fields to render as accordion sections — label + column,
// skipped entirely if $details is null or the field is empty.
$detailSections = [
    'Benefits' => 'benefits',
    'Ingredients' => 'ingredients',
    'Directions & Dosage' => null, // handled separately, combines two columns
    'Precautions' => 'precautions',
];
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<style>
    .pd-section {
        padding: 48px 40px 64px;
        background: var(--color-bg);
    }

    .pd-breadcrumb {
        max-width: var(--container-width);
        margin: 0 auto 28px;
        font-size: 13px;
        color: var(--color-text-light);
    }

    .pd-breadcrumb a {
        color: var(--color-text-light);
        text-decoration: none;
    }

    .pd-breadcrumb a:hover {
        color: var(--color-primary);
    }

    .pd-breadcrumb span[aria-current] {
        color: var(--color-text);
        font-weight: 600;
    }

    .pd-layout {
        max-width: var(--container-width);
        margin: 0 auto;
        display: grid;
        grid-template-columns: minmax(0, 5fr) minmax(0, 4fr);
        gap: 48px;
        align-items: start;
    }

    /* --- Gallery --- */

    .pd-gallery {
        position: sticky;
        top: 24px;
    }

    .pd-main-image-wrap {
        position: relative;
        aspect-ratio: 1 / 1;
        border-radius: var(--radius-lg);
        overflow: hidden;
        background: var(--color-primary-light);
        border: 1px solid var(--color-border);
    }

    .pd-main-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pd-image-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pd-image-placeholder svg {
        width: 72px;
        height: 72px;
        color: var(--color-accent);
    }

    .pd-badges {
        position: absolute;
        top: 14px;
        left: 14px;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
        z-index: 2;
    }

    .pd-badge {
        padding: 4px 10px;
        border-radius: var(--radius-sm);
        font-size: 11px;
        font-weight: 700;
        color: var(--color-white);
        background: var(--color-primary);
    }

    .pd-badge--discount {
        background: var(--color-accent);
    }

    .pd-badge--out-of-stock {
        position: absolute;
        top: 14px;
        right: 14px;
        background: var(--color-text);
        z-index: 2;
    }

    .pd-thumbs {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
    }

    .pd-thumb {
        width: 68px;
        height: 68px;
        border-radius: var(--radius-md);
        overflow: hidden;
        border: 2px solid transparent;
        background: var(--color-primary-light);
        cursor: pointer;
        padding: 0;
    }

    .pd-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .pd-thumb.is-active {
        border-color: var(--color-primary);
    }

    /* --- Info panel --- */

    .pd-category-tag {
        display: inline-block;
        margin-bottom: 10px;
        color: var(--color-accent);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .pd-title {
        margin: 0 0 14px;
        color: var(--color-text);
        font-size: 30px;
        font-weight: 800;
        line-height: 1.25;
    }

    .pd-price-row {
        display: flex;
        align-items: baseline;
        gap: 10px;
        margin-bottom: 16px;
    }

    .pd-price-current {
        color: var(--color-primary);
        font-size: 28px;
        font-weight: 800;
    }

    .pd-price-full {
        color: var(--color-text-light);
        font-size: 16px;
        font-weight: 500;
        text-decoration: line-through;
    }

    .pd-price-discount {
        color: var(--color-accent);
        font-size: 13px;
        font-weight: 700;
    }

    .pd-short-desc {
        margin: 0 0 24px;
        color: var(--color-text-light);
        font-size: 15px;
        line-height: 1.7;
    }

    .pd-stock {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 22px;
        font-size: 13px;
        font-weight: 700;
    }

    .pd-stock-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--color-primary);
    }

    .pd-stock.is-out .pd-stock-dot {
        background: #c0392b;
    }

    .pd-stock.is-out {
        color: #c0392b;
    }

    .pd-stock:not(.is-out) {
        color: var(--color-primary);
    }

    .pd-block {
        margin-bottom: 24px;
    }

    .pd-block-label {
        display: block;
        margin-bottom: 10px;
        font-size: 13px;
        font-weight: 700;
        color: var(--color-text);
    }

    .pd-variants {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .pd-variant-btn {
        padding: 9px 16px;
        border-radius: var(--radius-md);
        border: 1.5px solid var(--color-border);
        background: var(--color-white);
        color: var(--color-text);
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: border-color 0.2s ease, color 0.2s ease;
    }

    .pd-variant-btn:hover {
        border-color: var(--color-accent);
    }

    .pd-variant-btn.is-selected {
        border-color: var(--color-primary);
        color: var(--color-primary);
        background: var(--color-primary-light);
    }

    .pd-variant-btn.is-oos {
        color: var(--color-text-light);
        text-decoration: line-through;
        cursor: not-allowed;
    }

    .pd-qty-row {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 22px;
    }

    .pd-qty {
        display: inline-flex;
        align-items: center;
        border: 1.5px solid var(--color-border);
        border-radius: var(--radius-md);
        overflow: hidden;
    }

    .pd-qty button {
        width: 38px;
        height: 40px;
        border: none;
        background: var(--color-white);
        color: var(--color-primary);
        font-size: 18px;
        font-weight: 700;
        cursor: pointer;
    }

    .pd-qty button:hover {
        background: var(--color-primary-light);
    }

    .pd-qty input {
        width: 44px;
        height: 40px;
        border: none;
        border-left: 1.5px solid var(--color-border);
        border-right: 1.5px solid var(--color-border);
        text-align: center;
        font-size: 15px;
        font-weight: 700;
        color: var(--color-text);
    }

    .pd-actions {
        display: flex;
        gap: 12px;
        margin-bottom: 32px;
    }

    .pd-add-to-cart {
        flex: 1;
        padding: 15px 24px;
        border: none;
        border-radius: var(--radius-md);
        background: var(--color-primary);
        color: var(--color-white);
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .pd-add-to-cart:hover:not(:disabled) {
        background: var(--color-primary-dark);
    }

    .pd-add-to-cart:disabled {
        background: var(--color-border);
        color: var(--color-text-light);
        cursor: not-allowed;
    }

    .pd-meta {
        padding-top: 20px;
        border-top: 1px solid var(--color-border);
        font-size: 13px;
        color: var(--color-text-light);
        line-height: 2;
    }

    .pd-meta strong {
        color: var(--color-text);
        font-weight: 600;
    }

    /* --- Accordion --- */

    .pd-details {
        max-width: var(--container-width);
        margin: 56px auto 0;
    }

    .pd-accordion-item {
        border-bottom: 1px solid var(--color-border);
    }

    .pd-accordion-trigger {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 4px;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 16px;
        font-weight: 700;
        color: var(--color-text);
        text-align: left;
    }

    .pd-accordion-trigger svg {
        width: 18px;
        height: 18px;
        color: var(--color-primary);
        transition: transform 0.2s ease;
        flex-shrink: 0;
    }

    .pd-accordion-item.is-open .pd-accordion-trigger svg {
        transform: rotate(180deg);
    }

    .pd-accordion-panel {
        display: none;
        padding: 0 4px 22px;
        color: var(--color-text-light);
        font-size: 14.5px;
        line-height: 1.75;
        white-space: pre-line;
    }

    .pd-accordion-item.is-open .pd-accordion-panel {
        display: block;
    }

    @media (max-width: 860px) {
        .pd-layout {
            grid-template-columns: 1fr;
        }

        .pd-gallery {
            position: static;
        }
    }

    @media (max-width: 500px) {
        .pd-section {
            padding: 32px 20px 48px;
        }

        .pd-title {
            font-size: 24px;
        }
    }
</style>

<section class="pd-section">

    <nav class="pd-breadcrumb" aria-label="Breadcrumb">
        <a href="<?php echo BASE_URL; ?>index.php">Home</a>
        <span aria-hidden="true"> / </span>
        <?php if ($primaryCategory): ?>
            <a href="<?php echo BASE_URL; ?>products.php?category_slug=<?php echo urlencode($primaryCategory['slug']); ?>"><?php echo htmlspecialchars($primaryCategory['name']); ?></a>
            <span aria-hidden="true"> / </span>
        <?php endif; ?>
        <span aria-current="page"><?php echo htmlspecialchars($product['title']); ?></span>
    </nav>

    <div class="pd-layout">

        <div class="pd-gallery">
            <div class="pd-main-image-wrap">

                <div class="pd-badges">
                    <span class="pd-badge pd-badge--discount" id="js-discount-badge" style="<?php echo $hasSale ? '' : 'display:none;'; ?>">
                        <?php echo $hasSale ? (int) round((1 - ($currentSalePrice / $currentPrice)) * 100) : 0; ?>% off
                    </span>
                    <?php if (!empty($product['bestseller'])): ?>
                        <span class="pd-badge">Bestseller</span>
                    <?php elseif (!empty($product['featured'])): ?>
                        <span class="pd-badge">Featured</span>
                    <?php elseif (!empty($product['trending'])): ?>
                        <span class="pd-badge">Trending</span>
                    <?php endif; ?>
                </div>

                <span class="pd-badge pd-badge--out-of-stock" id="js-oos-badge" style="<?php echo $isOutOfStock ? '' : 'display:none;'; ?>">Out of Stock</span>

                <?php if (!empty($images)): ?>
                    <img
                        src="<?php echo BASE_URL . ltrim($images[0]['image'], '/'); ?>"
                        alt="<?php echo htmlspecialchars($images[0]['alt_text'] ?: $product['title']); ?>"
                        class="pd-main-image"
                        id="js-main-image">
                <?php else: ?>
                    <div class="pd-image-placeholder" id="js-main-image" aria-hidden="true">
                        <svg viewBox="0 0 64 64" fill="currentColor">
                            <path d="M32 6c-6 8-10 16-10 24 0 6 4 10 10 10s10-4 10-10c0-8-4-16-10-24z" />
                            <path d="M12 24c8 0 14 6 16 14-8 2-16-2-20-8-1.5-2.4-1-4.6 4-6z" />
                            <path d="M52 24c-8 0-14 6-16 14 8 2 16-2 20-8 1.5-2.4 1-4.6-4-6z" />
                        </svg>
                    </div>
                <?php endif; ?>

            </div>

            <?php if (count($images) > 1): ?>
                <div class="pd-thumbs">
                    <?php foreach ($images as $i => $image): ?>
                        <button
                            type="button"
                            class="pd-thumb<?php echo $i === 0 ? ' is-active' : ''; ?>"
                            data-full="<?php echo BASE_URL . ltrim($image['image'], '/'); ?>"
                            aria-label="View image <?php echo $i + 1; ?>">
                            <img
                                src="<?php echo BASE_URL . ltrim($image['image'], '/'); ?>"
                                alt="<?php echo htmlspecialchars($image['alt_text'] ?: $product['title']); ?>">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="pd-info">

            <?php if ($primaryCategory): ?>
                <span class="pd-category-tag"><?php echo htmlspecialchars($primaryCategory['name']); ?></span>
            <?php endif; ?>

            <h1 class="pd-title"><?php echo htmlspecialchars($product['title']); ?></h1>

            <div class="pd-price-row">
                <span class="pd-price-current" id="js-price-current">
                    &#8377;<?php echo number_format($hasSale ? $currentSalePrice : $currentPrice, 0); ?>
                </span>
                <span class="pd-price-full" id="js-price-full" style="<?php echo $hasSale ? '' : 'display:none;'; ?>">
                    &#8377;<?php echo number_format($currentPrice, 0); ?>
                </span>
            </div>

            <?php if (!empty($product['short_description'])): ?>
                <p class="pd-short-desc"><?php echo htmlspecialchars($product['short_description']); ?></p>
            <?php endif; ?>

            <span class="pd-stock<?php echo $isOutOfStock ? ' is-out' : ''; ?>" id="js-stock-status">
                <span class="pd-stock-dot"></span>
                <span id="js-stock-text"><?php echo $isOutOfStock ? 'Out of Stock' : (($currentStock <= 5) ? 'Only ' . $currentStock . ' left' : 'In Stock'); ?></span>
            </span>

            <?php if ($hasVariants): ?>
                <div class="pd-block">
                    <span class="pd-block-label">Select Option</span>
                    <div class="pd-variants" id="js-variants">
                        <?php foreach ($variants as $variant): ?>
                            <?php $vOos = (int) $variant['stock'] <= 0; ?>
                            <button
                                type="button"
                                class="pd-variant-btn<?php echo $variant['id'] === $defaultVariant['id'] ? ' is-selected' : ''; ?><?php echo $vOos ? ' is-oos' : ''; ?>"
                                data-variant-id="<?php echo (int) $variant['id']; ?>"
                                data-price="<?php echo (float) $variant['price']; ?>"
                                data-sale-price="<?php echo $variant['sale_price'] !== null ? (float) $variant['sale_price'] : ''; ?>"
                                data-stock="<?php echo (int) $variant['stock']; ?>"
                                <?php echo $vOos ? 'disabled' : ''; ?>>
                                <?php echo htmlspecialchars($variant['variant_name']); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="pd-qty-row">
                <span class="pd-block-label" style="margin-bottom:0;">Quantity</span>
                <div class="pd-qty">
                    <button type="button" id="js-qty-minus" aria-label="Decrease quantity">&minus;</button>
                    <input type="number" id="js-qty-input" value="1" min="1" max="<?php echo max($currentStock, 1); ?>" inputmode="numeric">
                    <button type="button" id="js-qty-plus" aria-label="Increase quantity">+</button>
                </div>
            </div>

            <div class="pd-actions">
                <input type="hidden" id="js-selected-variant-id" value="<?php echo $hasVariants ? (int) $defaultVariant['id'] : ''; ?>">
                <button
                    type="button"
                    class="pd-add-to-cart"
                    id="js-add-to-cart"
                    data-product-id="<?php echo (int) $product['id']; ?>"
                    <?php echo $isOutOfStock ? 'disabled' : ''; ?>>
                    <?php echo $isOutOfStock ? 'Out of Stock' : 'Add to Cart'; ?>
                </button>
            </div>

            <div class="pd-meta">
                <?php if (!empty($details['manufacturer'])): ?>
                    <div><strong>Manufacturer:</strong> <?php echo htmlspecialchars($details['manufacturer']); ?></div>
                <?php endif; ?>
                <?php if (!empty($details['country_of_origin'])): ?>
                    <div><strong>Country of Origin:</strong> <?php echo htmlspecialchars($details['country_of_origin']); ?></div>
                <?php endif; ?>
                <?php if (!empty($details['shelf_life'])): ?>
                    <div><strong>Shelf Life:</strong> <?php echo htmlspecialchars($details['shelf_life']); ?></div>
                <?php endif; ?>
            </div>

        </div>

    </div>

    <div class="pd-details">

        <?php if (!empty($product['description'])): ?>
            <div class="pd-accordion-item is-open">
                <button type="button" class="pd-accordion-trigger">
                    Description
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="pd-accordion-panel"><?php echo htmlspecialchars($product['description']); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($details['benefits'])): ?>
            <div class="pd-accordion-item">
                <button type="button" class="pd-accordion-trigger">
                    Benefits
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="pd-accordion-panel"><?php echo htmlspecialchars($details['benefits']); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($details['ingredients'])): ?>
            <div class="pd-accordion-item">
                <button type="button" class="pd-accordion-trigger">
                    Ingredients
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="pd-accordion-panel"><?php echo htmlspecialchars($details['ingredients']); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($details['directions']) || !empty($details['dosage'])): ?>
            <div class="pd-accordion-item">
                <button type="button" class="pd-accordion-trigger">
                    Directions &amp; Dosage
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="pd-accordion-panel"><?php
                    $parts = [];
                    if (!empty($details['directions'])) {
                        $parts[] = $details['directions'];
                    }
                    if (!empty($details['dosage'])) {
                        $parts[] = 'Dosage: ' . $details['dosage'];
                    }
                    echo htmlspecialchars(implode("\n\n", $parts));
                ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($details['precautions'])): ?>
            <div class="pd-accordion-item">
                <button type="button" class="pd-accordion-trigger">
                    Precautions
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="pd-accordion-panel"><?php echo htmlspecialchars($details['precautions']); ?></div>
            </div>
        <?php endif; ?>

    </div>

</section>

<script>
(function () {
    var currency = function (n) {
        return '\u20B9' + Math.round(n).toLocaleString('en-IN');
    };

    // --- Thumbnail gallery ---
    var mainImage = document.getElementById('js-main-image');
    document.querySelectorAll('.pd-thumb').forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            document.querySelectorAll('.pd-thumb').forEach(function (t) { t.classList.remove('is-active'); });
            thumb.classList.add('is-active');
            if (mainImage && mainImage.tagName === 'IMG') {
                mainImage.src = thumb.getAttribute('data-full');
            }
        });
    });

    // --- Variant selection ---
    var priceCurrent = document.getElementById('js-price-current');
    var priceFull = document.getElementById('js-price-full');
    var discountBadge = document.getElementById('js-discount-badge');
    var oosBadge = document.getElementById('js-oos-badge');
    var stockStatus = document.getElementById('js-stock-status');
    var stockText = document.getElementById('js-stock-text');
    var addToCartBtn = document.getElementById('js-add-to-cart');
    var qtyInput = document.getElementById('js-qty-input');
    var selectedVariantInput = document.getElementById('js-selected-variant-id');

    function applyStock(stock) {
        var isOos = stock <= 0;
        stockStatus.classList.toggle('is-out', isOos);
        stockText.textContent = isOos ? 'Out of Stock' : (stock <= 5 ? 'Only ' + stock + ' left' : 'In Stock');
        oosBadge.style.display = isOos ? '' : 'none';
        addToCartBtn.disabled = isOos;
        addToCartBtn.textContent = isOos ? 'Out of Stock' : 'Add to Cart';
        qtyInput.max = Math.max(stock, 1);
        if (parseInt(qtyInput.value, 10) > stock) {
            qtyInput.value = Math.max(stock, 1);
        }
    }

    document.querySelectorAll('.pd-variant-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (btn.disabled) return;

            document.querySelectorAll('.pd-variant-btn').forEach(function (b) { b.classList.remove('is-selected'); });
            btn.classList.add('is-selected');

            var price = parseFloat(btn.getAttribute('data-price'));
            var salePriceRaw = btn.getAttribute('data-sale-price');
            var salePrice = salePriceRaw !== '' ? parseFloat(salePriceRaw) : null;
            var stock = parseInt(btn.getAttribute('data-stock'), 10);

            var hasSale = salePrice !== null && salePrice < price;

            priceCurrent.textContent = currency(hasSale ? salePrice : price);
            if (hasSale) {
                priceFull.textContent = currency(price);
                priceFull.style.display = '';
                discountBadge.textContent = Math.round((1 - (salePrice / price)) * 100) + '% off';
                discountBadge.style.display = '';
            } else {
                priceFull.style.display = 'none';
                discountBadge.style.display = 'none';
            }

            selectedVariantInput.value = btn.getAttribute('data-variant-id');
            applyStock(stock);
        });
    });

    // --- Quantity stepper ---
    document.getElementById('js-qty-minus').addEventListener('click', function () {
        var val = parseInt(qtyInput.value, 10) || 1;
        if (val > 1) qtyInput.value = val - 1;
    });
    document.getElementById('js-qty-plus').addEventListener('click', function () {
        var val = parseInt(qtyInput.value, 10) || 1;
        var max = parseInt(qtyInput.max, 10) || 1;
        if (val < max) qtyInput.value = val + 1;
    });

    // --- Accordion ---
    document.querySelectorAll('.pd-accordion-trigger').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            trigger.closest('.pd-accordion-item').classList.toggle('is-open');
        });
    });

    // --- Add to cart ---
    // Cart backend doesn't exist yet (see project notes). Wire this up to
    // the real add-to-cart endpoint once it's built.
    addToCartBtn.addEventListener('click', function () {
        var payload = {
            product_id: addToCartBtn.getAttribute('data-product-id'),
            variant_id: selectedVariantInput.value || null,
            quantity: parseInt(qtyInput.value, 10) || 1
        };
        console.log('Add to cart (not yet wired to a backend):', payload);
    });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>