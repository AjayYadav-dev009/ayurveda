<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/product.php';
require_once __DIR__ . '/../function/product-image.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/review.php';
require_once __DIR__ . '/../function/csrf.php';
require_once __DIR__ . '/../function/helper.php';

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

// Snapshot exactly what the Add to Cart form needs into dedicated scalars,
// taken BEFORE header.php is include()'d below. PHP includes share scope,
// and header.php's mega-menu is already known to silently overwrite
// generic variable names declared before it (see the $category/$products
// collision fixed in categories-product.php) — the form itself renders
// after the header include, so if it read $product['id'] /
// $defaultVariant['id'] directly at that point, a same-named variable
// reused inside header.php would silently swap in the wrong product/variant
// id without any visible error until the cart rejects the mismatch.
$addToCartProductId = (int) $product['id'];
$addToCartVariantId = $hasVariants ? (int) $defaultVariant['id'] : '';

// Full snapshot, not just the two cart fields above: header.php's mega-menu
// does `foreach ($products as $product) { ... }` while building its panels
// (confirmed in header.php directly) — that's the exact same shared-scope
// collision already fixed once for $category/$products in
// categories-product.php, except this time it clobbers $product itself.
// Everything below that renders after the header include (title, breadcrumb,
// image alt text, badges, description) must read from this copy, never
// from $product directly, or it silently shows whatever product the
// mega-menu happened to loop through last.
$viewProduct = $product;

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

/**
 * ---------------------------------------------------------------------
 * Reviews
 * ---------------------------------------------------------------------
 * Handled entirely in this top block, BEFORE header.php is included, for
 * two reasons: a POST needs to redirect() (which calls header()) before
 * any HTML has been emitted, and every value read here needs to be
 * snapshotted into its own variable name — same reasoning as
 * $viewProduct above — since header.php's mega-menu loop overwrites
 * $product/$products and would otherwise silently corrupt anything that
 * read from those names after the include.
 *
 * The review form POSTs back to this same page (BASE_URL +
 * products/product_details.php?slug=...), matching the way
 * login.php/register.php handle their own POST. On success this
 * redirects (POST-Redirect-GET) so a page refresh can't resubmit the
 * review; on a validation error it falls through and re-renders the
 * page with $reviewErrors set.
 */
$reviewErrors = [];
$reviewFormRating = '';
$reviewFormText = '';
$reviewJustSubmitted = isset($_GET['review']) && $_GET['review'] === 'submitted';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $reviewFormRating = $_POST['rating'] ?? '';
    $reviewFormText = $_POST['review'] ?? '';

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $reviewErrors['general'] = 'Your session has expired. Please refresh the page and try again.';
    } elseif (!isCustomerLogin($conn)) {
        $reviewErrors['general'] = 'Please log in to write a review.';
    } else {
        try {
            // user_id comes from the session, product_id from the
            // server-resolved $product above — never from $_POST — so a
            // customer can never review as someone else or attach a
            // review to a different product than the one they're on.
            submitProductReview(
                $conn,
                $_SESSION['customer_id'],
                $product['id'],
                $reviewFormRating,
                $reviewFormText
            );
            redirect(BASE_URL . 'products/product_details.php?slug=' . urlencode($slug) . '&review=submitted');
        } catch (InvalidArgumentException $e) {
            $reviewErrors['general'] = $e->getMessage();
        } catch (Exception $e) {
            error_log('Review submission failed: ' . $e->getMessage());
            $reviewErrors['general'] = 'Something went wrong while submitting your review. Please try again.';
        }
    }
}

try {
    $reviewSummary = getProductReviewSummary($conn, $product['id']);
    $ratingDistribution = getRatingDistribution($conn, $product['id']);

    $reviewPage = isset($_GET['review_page']) ? max(1, (int) $_GET['review_page']) : 1;
    $reviewsData = getProductReviews($conn, $product['id'], $reviewPage, 5);
} catch (Exception $e) {
    error_log('Failed to load reviews: ' . $e->getMessage());
    $reviewSummary = ['average' => 0.0, 'count' => 0];
    $ratingDistribution = [5 => ['count' => 0, 'percent' => 0.0], 4 => ['count' => 0, 'percent' => 0.0], 3 => ['count' => 0, 'percent' => 0.0], 2 => ['count' => 0, 'percent' => 0.0], 1 => ['count' => 0, 'percent' => 0.0]];
    $reviewsData = ['reviews' => [], 'total' => 0, 'page' => 1, 'pages' => 1];
}

$isCustomerLoggedIn = isCustomerLogin($conn);
$myReview = null;
if ($isCustomerLoggedIn) {
    try {
        $myReview = getUserReviewForProduct($conn, $_SESSION['customer_id'], $product['id']);
    } catch (Exception $e) {
        error_log('Failed to load the customer\'s own review: ' . $e->getMessage());
    }
}
$reviewCsrfToken = generateCSRFToken();
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

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
        <span aria-current="page"><?php echo htmlspecialchars($viewProduct['title']); ?></span>
    </nav>

    <div class="pd-layout">

        <div class="pd-gallery">
            <div class="pd-main-image-wrap">

                <div class="pd-badges">
                    <span class="pd-badge pd-badge--discount" id="js-discount-badge" style="<?php echo $hasSale ? '' : 'display:none;'; ?>">
                        <?php echo $hasSale ? (int) round((1 - ($currentSalePrice / $currentPrice)) * 100) : 0; ?>% off
                    </span>
                    <?php if (!empty($viewProduct['bestseller'])): ?>
                        <span class="pd-badge">Bestseller</span>
                    <?php elseif (!empty($viewProduct['featured'])): ?>
                        <span class="pd-badge">Featured</span>
                    <?php elseif (!empty($viewProduct['trending'])): ?>
                        <span class="pd-badge">Trending</span>
                    <?php endif; ?>
                </div>

                <span class="pd-badge pd-badge--out-of-stock" id="js-oos-badge" style="<?php echo $isOutOfStock ? '' : 'display:none;'; ?>">Out of Stock</span>

                <?php if (!empty($images)): ?>
                    <img
                        src="<?php echo getProductImageUrl($images[0]['image']); ?>"
                        alt="<?php echo htmlspecialchars($images[0]['alt_text'] ?: $viewProduct['title']); ?>"
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
                            data-full="<?php echo getProductImageUrl($image['image']); ?>"
                            aria-label="View image <?php echo $i + 1; ?>">
                            <img
                                src="<?php echo getProductImageUrl($image['image']); ?>"
                                alt="<?php echo htmlspecialchars($image['alt_text'] ?: $viewProduct['title']); ?>">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="pd-info">

            <?php if ($primaryCategory): ?>
                <span class="pd-category-tag"><?php echo htmlspecialchars($primaryCategory['name']); ?></span>
            <?php endif; ?>

            <h1 class="pd-title"><?php echo htmlspecialchars($viewProduct['title']); ?></h1>

            <?php if ($reviewSummary['count'] > 0): ?>
                <a href="#customer-reviews" class="pd-rating-summary-link">
                    <span class="pd-stars">
                        <?php $roundedAvgTop = (int) round($reviewSummary['average']); ?>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="<?php echo $i <= $roundedAvgTop ? 'filled' : ''; ?>">&#9733;</span>
                        <?php endfor; ?>
                    </span>
                    <?php echo number_format($reviewSummary['average'], 1); ?> &#9733; (<?php echo (int) $reviewSummary['count']; ?> Review<?php echo $reviewSummary['count'] === 1 ? '' : 's'; ?>)
                </a>
            <?php else: ?>
                <a href="#customer-reviews" class="pd-rating-summary-link pd-rating-summary-link--empty">No reviews yet &middot; Be the first to review this product</a>
            <?php endif; ?>

            <div class="pd-price-row">
                <span class="pd-price-current" id="js-price-current">
                    &#8377;<?php echo number_format($hasSale ? $currentSalePrice : $currentPrice, 0); ?>
                </span>
                <span class="pd-price-full" id="js-price-full" style="<?php echo $hasSale ? '' : 'display:none;'; ?>">
                    &#8377;<?php echo number_format($currentPrice, 0); ?>
                </span>
            </div>

            <?php if (!empty($viewProduct['short_description'])): ?>
                <p class="pd-short-desc"><?php echo htmlspecialchars($viewProduct['short_description']); ?></p>
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

            <form method="POST" action="<?php echo BASE_URL; ?>cart/index.php">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?php echo $addToCartProductId; ?>">
                <input type="hidden" id="js-selected-variant-id" name="variant_id" value="<?php echo $addToCartVariantId; ?>">

                <div class="pd-qty-row">
                    <span class="pd-block-label" style="margin-bottom:0;">Quantity</span>
                    <div class="pd-qty">
                        <button type="button" id="js-qty-minus" aria-label="Decrease quantity">&minus;</button>
                        <input type="number" id="js-qty-input" name="quantity" value="1" min="1" max="<?php echo max($currentStock, 1); ?>" inputmode="numeric">
                        <button type="button" id="js-qty-plus" aria-label="Increase quantity">+</button>
                    </div>
                </div>

                <div class="pd-actions">
                    <button
                        type="submit"
                        class="pd-add-to-cart"
                        id="js-add-to-cart"
                        <?php echo $isOutOfStock ? 'disabled' : ''; ?>>
                        <?php echo $isOutOfStock ? 'Out of Stock' : 'Add to Cart'; ?>
                    </button>
                </div>
            </form>

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

        <?php if (!empty($viewProduct['description'])): ?>
            <div class="pd-accordion-item is-open">
                <button type="button" class="pd-accordion-trigger">
                    Description
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div class="pd-accordion-panel"><?php echo htmlspecialchars($viewProduct['description']); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($details['benefits'])): ?>
            <div class="pd-accordion-item">
                <button type="button" class="pd-accordion-trigger">
                    Benefits
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div class="pd-accordion-panel"><?php echo htmlspecialchars($details['benefits']); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($details['ingredients'])): ?>
            <div class="pd-accordion-item">
                <button type="button" class="pd-accordion-trigger">
                    Ingredients
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div class="pd-accordion-panel"><?php echo htmlspecialchars($details['ingredients']); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($details['directions']) || !empty($details['dosage'])): ?>
            <div class="pd-accordion-item">
                <button type="button" class="pd-accordion-trigger">
                    Directions &amp; Dosage
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div class="pd-accordion-panel"><?php $parts = [];
                                                if (!empty($details['directions'])) {
                                                    $parts[] = $details['directions'];
                                                }
                                                if (!empty($details['dosage'])) {
                                                    $parts[] = 'Dosage: ' . $details['dosage'];
                                                }
                                                echo htmlspecialchars(implode("\n\n", $parts)); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($details['precautions'])): ?>
            <div class="pd-accordion-item">
                <button type="button" class="pd-accordion-trigger">
                    Precautions
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div class="pd-accordion-panel"><?php echo htmlspecialchars($details['precautions']); ?></div>
            </div>
        <?php endif; ?>

    </div>

</section>

<style>
    .pd-reviews {
        max-width: var(--container-width);
        margin: 0 auto 64px;
        padding: 0 40px;
    }

    .pd-reviews h2 {
        margin: 0 0 24px;
        font-size: 22px;
        font-weight: 800;
        color: var(--color-text);
    }

    .pd-reviews-layout {
        display: grid;
        grid-template-columns: minmax(0, 280px) minmax(0, 1fr);
        gap: 48px;
        align-items: start;
    }

    /* --- Summary --- */

    .pd-review-summary {
        text-align: center;
        padding: 24px;
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        background: var(--color-white);
    }

    .pd-review-avg {
        font-size: 44px;
        font-weight: 800;
        color: var(--color-text);
        line-height: 1;
    }

    .pd-review-avg-stars {
        margin: 10px 0 6px;
        font-size: 18px;
        letter-spacing: 2px;
        color: var(--color-border);
    }

    .pd-review-avg-stars .filled {
        color: var(--color-accent);
    }

    .pd-review-count {
        font-size: 13px;
        color: var(--color-text-light);
        margin-bottom: 20px;
    }

    .pd-review-dist-row {
        display: grid;
        grid-template-columns: 42px 1fr 34px;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
        font-size: 12.5px;
        color: var(--color-text-light);
    }

    .pd-review-dist-bar {
        height: 7px;
        border-radius: 99px;
        background: var(--color-primary-light);
        overflow: hidden;
    }

    .pd-review-dist-fill {
        height: 100%;
        background: var(--color-accent);
        border-radius: 99px;
    }

    /* --- Compact rating line under the product title --- */

    .pd-rating-summary-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: -4px 0 16px;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--color-text-light);
        text-decoration: none;
    }

    .pd-rating-summary-link:hover {
        color: var(--color-primary);
    }

    .pd-rating-summary-link--empty {
        font-weight: 500;
    }

    /* --- Star display (read-only) --- */

    .pd-stars {
        color: var(--color-border);
        letter-spacing: 1px;
        font-size: 15px;
        white-space: nowrap;
    }

    .pd-stars .filled {
        color: var(--color-accent);
    }

    /* --- Review list --- */

    .pd-review-card {
        padding: 18px 0;
        border-bottom: 1px solid var(--color-border);
    }

    .pd-review-card:first-child {
        padding-top: 0;
    }

    .pd-review-card-head {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 6px;
    }

    .pd-review-name {
        font-weight: 700;
        font-size: 14px;
        color: var(--color-text);
    }

    .pd-review-verified {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 99px;
        background: var(--color-primary-light);
        color: var(--color-primary-dark);
        font-size: 11px;
        font-weight: 700;
    }

    .pd-review-date {
        font-size: 12px;
        color: var(--color-text-light);
        margin-left: auto;
    }

    .pd-review-text {
        margin: 6px 0 0;
        font-size: 14px;
        line-height: 1.65;
        color: var(--color-text);
        white-space: pre-line;
    }

    .pd-review-empty {
        padding: 28px 0;
        color: var(--color-text-light);
        font-size: 14px;
    }

    .pd-review-empty strong {
        display: block;
        margin-bottom: 6px;
        color: var(--color-text);
        font-size: 15px;
    }

    /* --- Pagination --- */

    .pd-review-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin-top: 24px;
    }

    .pd-review-pagination a,
    .pd-review-pagination span {
        min-width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 8px;
        border-radius: var(--radius-sm);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        color: var(--color-text);
        border: 1px solid var(--color-border);
    }

    .pd-review-pagination a:hover {
        border-color: var(--color-primary);
        color: var(--color-primary);
    }

    .pd-review-pagination .is-current {
        background: var(--color-primary);
        border-color: var(--color-primary);
        color: var(--color-white);
    }

    .pd-review-pagination .is-disabled {
        opacity: 0.4;
        pointer-events: none;
    }

    /* --- Write a review --- */

    .pd-write-review {
        margin-top: 40px;
        padding: 24px;
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        background: var(--color-white);
    }

    .pd-write-review h3 {
        margin: 0 0 16px;
        font-size: 16px;
        font-weight: 700;
        color: var(--color-text);
    }

    .pd-login-prompt {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        font-size: 14px;
        color: var(--color-text-light);
    }

    .pd-login-prompt a {
        flex-shrink: 0;
        padding: 10px 20px;
        border-radius: var(--radius-md);
        background: var(--color-primary);
        color: var(--color-white);
        font-size: 13.5px;
        font-weight: 700;
        text-decoration: none;
    }

    .pd-login-prompt a:hover {
        background: var(--color-primary-dark);
    }

    .pd-my-review-status {
        font-size: 14px;
        color: var(--color-text-light);
    }

    .pd-my-review-status strong {
        color: var(--color-text);
    }

    .pd-review-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .pd-review-badge--pending {
        background: #fdf3e3;
        color: #92650f;
    }

    .pd-review-badge--rejected {
        background: #fbeceb;
        color: #8a1c14;
    }

    .pd-review-form .form-error {
        margin: 0 0 16px;
        padding: 12px 14px;
        font-size: 13px;
        color: #8a1c14;
        background: #fbeceb;
        border: 1px solid #f2c6c2;
        border-radius: var(--radius-sm);
    }

    /* Pure-CSS interactive star rating: markup is 5,4,3,2,1 (reversed),
       displayed left-to-right via row-reverse, so ~ selects "this star
       and everything to its left" for both hover and :checked. */
    .pd-star-input {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
        gap: 2px;
        border: none;
        padding: 0;
        margin: 0 0 18px;
        width: max-content;
    }

    .pd-star-input input {
        position: absolute;
        opacity: 0;
        width: 1px;
        height: 1px;
    }

    .pd-star-input label {
        font-size: 30px;
        line-height: 1;
        color: var(--color-border);
        cursor: pointer;
        transition: color 0.1s ease;
    }

    .pd-star-input label:hover,
    .pd-star-input label:hover ~ label,
    .pd-star-input input:checked ~ label {
        color: var(--color-accent);
    }

    .pd-review-form textarea {
        width: 100%;
        min-height: 110px;
        padding: 12px 14px;
        font-size: 14px;
        font-family: inherit;
        color: var(--color-text);
        background: var(--color-bg);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-sm);
        box-sizing: border-box;
        resize: vertical;
        margin-bottom: 16px;
    }

    .pd-review-form textarea:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px var(--color-primary-light);
    }

    .pd-review-form button[type="submit"] {
        padding: 12px 26px;
        border: none;
        border-radius: var(--radius-md);
        background: var(--color-primary);
        color: var(--color-white);
        font-size: 14.5px;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .pd-review-form button[type="submit"]:hover {
        background: var(--color-primary-dark);
    }

    .pd-review-success {
        margin: 0 0 16px;
        padding: 12px 14px;
        font-size: 13px;
        color: var(--color-primary-dark);
        background: var(--color-primary-light);
        border: 1px solid var(--color-primary);
        border-radius: var(--radius-sm);
        line-height: 1.5;
    }

    @media (max-width: 860px) {
        .pd-reviews-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 500px) {
        .pd-reviews {
            padding: 0 20px;
        }
    }
</style>

<section class="pd-reviews">
    <h2>Customer Reviews</h2>

    <div class="pd-reviews-layout">

        <!-- Summary + rating distribution -->
        <div class="pd-review-summary">
            <?php if ($reviewSummary['count'] > 0): ?>
                <div class="pd-review-avg"><?php echo number_format($reviewSummary['average'], 1); ?></div>
                <div class="pd-review-avg-stars">
                    <?php
                    $roundedAvg = (int) round($reviewSummary['average']);
                    for ($i = 1; $i <= 5; $i++):
                    ?>
                        <span class="<?php echo $i <= $roundedAvg ? 'filled' : ''; ?>">&#9733;</span>
                    <?php endfor; ?>
                </div>
                <div class="pd-review-count">Based on <?php echo (int) $reviewSummary['count']; ?> review<?php echo $reviewSummary['count'] === 1 ? '' : 's'; ?></div>

                <?php for ($star = 5; $star >= 1; $star--): ?>
                    <?php $d = $ratingDistribution[$star]; ?>
                    <div class="pd-review-dist-row">
                        <span><?php echo $star; ?> &#9733;</span>
                        <span class="pd-review-dist-bar"><span class="pd-review-dist-fill" style="width:<?php echo (float) $d['percent']; ?>%;"></span></span>
                        <span><?php echo (int) $d['percent']; ?>%</span>
                    </div>
                <?php endfor; ?>
            <?php else: ?>
                <div class="pd-review-avg" style="font-size:20px;color:var(--color-text-light);">No reviews yet</div>
            <?php endif; ?>
        </div>

        <!-- Review list + write-a-review -->
        <div>
            <?php if ($reviewsData['total'] > 0): ?>
                <?php foreach ($reviewsData['reviews'] as $review): ?>
                    <div class="pd-review-card">
                        <div class="pd-review-card-head">
                            <span class="pd-review-name"><?php echo htmlspecialchars($review['customer_name']); ?></span>
                            <span class="pd-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="<?php echo $i <= $review['rating'] ? 'filled' : ''; ?>">&#9733;</span>
                                <?php endfor; ?>
                            </span>
                            <?php if ($review['verified_purchase']): ?>
                                <span class="pd-review-verified">&#10003; Verified Purchase</span>
                            <?php endif; ?>
                            <span class="pd-review-date"><?php echo date('d M Y', strtotime($review['created_at'])); ?></span>
                        </div>
                        <?php if (!empty($review['review'])): ?>
                            <p class="pd-review-text"><?php echo htmlspecialchars($review['review']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <?php if ($reviewsData['pages'] > 1): ?>
                    <nav class="pd-review-pagination" aria-label="Review pages">
                        <?php
                        $reviewBaseUrl = BASE_URL . 'products/product_details.php?slug=' . urlencode($slug);
                        $curPage = $reviewsData['page'];
                        $totalPages = $reviewsData['pages'];
                        ?>
                        <a href="<?php echo $reviewBaseUrl . '&review_page=' . max(1, $curPage - 1); ?>#customer-reviews" class="<?php echo $curPage <= 1 ? 'is-disabled' : ''; ?>">&laquo;</a>
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <?php if ($p === $curPage): ?>
                                <span class="is-current"><?php echo $p; ?></span>
                            <?php else: ?>
                                <a href="<?php echo $reviewBaseUrl . '&review_page=' . $p; ?>#customer-reviews"><?php echo $p; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <a href="<?php echo $reviewBaseUrl . '&review_page=' . min($totalPages, $curPage + 1); ?>#customer-reviews" class="<?php echo $curPage >= $totalPages ? 'is-disabled' : ''; ?>">&raquo;</a>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="pd-review-empty">
                    <strong>No reviews yet</strong>
                    Be the first customer to review this product.
                </div>
            <?php endif; ?>

            <!-- Write a review -->
            <div class="pd-write-review" id="customer-reviews">
                <?php if (!$isCustomerLoggedIn): ?>
                    <h3>Write a Review</h3>
                    <div class="pd-login-prompt">
                        <span>Please login to write a review.</span>
                        <a href="<?php echo BASE_URL; ?>account/login.php?redirect=<?php echo urlencode('products/product_details.php?slug=' . $slug); ?>">Login to write a review</a>
                    </div>

                <?php elseif ($myReview): ?>
                    <h3>Your Review</h3>
                    <p class="pd-my-review-status">
                        <?php if ($myReview['status'] === 'Pending'): ?>
                            <span class="pd-review-badge pd-review-badge--pending">Pending</span>
                            &nbsp;Your review has been submitted and is awaiting approval.
                        <?php elseif ($myReview['status'] === 'Rejected'): ?>
                            <span class="pd-review-badge pd-review-badge--rejected">Rejected</span>
                            &nbsp;Your review was not approved for publication.
                        <?php else: ?>
                            You have already reviewed this product. Thank you!
                        <?php endif; ?>
                    </p>

                <?php else: ?>
                    <h3><?php echo $reviewJustSubmitted ? 'Write a Review' : (($reviewsData['total'] === 0) ? 'Write the first review' : 'Write a Review'); ?></h3>

                    <?php if ($reviewJustSubmitted): ?>
                        <p class="pd-review-success">Thanks! Your review has been submitted and will appear once approved.</p>
                    <?php endif; ?>

                    <?php if (!empty($reviewErrors['general'])): ?>
                        <p class="form-error"><?php echo htmlspecialchars($reviewErrors['general']); ?></p>
                    <?php endif; ?>

                    <form class="pd-review-form" method="POST" action="<?php echo BASE_URL; ?>products/product_details.php?slug=<?php echo urlencode($slug); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($reviewCsrfToken); ?>">
                        <input type="hidden" name="submit_review" value="1">

                        <span class="pd-block-label">Your Rating</span>
                        <fieldset class="pd-star-input">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" id="pd-star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php echo ((string) $reviewFormRating === (string) $i) ? 'checked' : ''; ?> required>
                                <label for="pd-star<?php echo $i; ?>" title="<?php echo $i; ?> star<?php echo $i === 1 ? '' : 's'; ?>">&#9733;</label>
                            <?php endfor; ?>
                        </fieldset>

                        <label for="pd-review-text" class="pd-block-label">Your Review (optional)</label>
                        <textarea id="pd-review-text" name="review" maxlength="2000" placeholder="Write your review..."><?php echo htmlspecialchars($reviewFormText); ?></textarea>

                        <button type="submit">Submit Review</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<script>
    (function() {
        var currency = function(n) {
            return '\u20B9' + Math.round(n).toLocaleString('en-IN');
        };

        // --- Thumbnail gallery ---
        var mainImage = document.getElementById('js-main-image');
        document.querySelectorAll('.pd-thumb').forEach(function(thumb) {
            thumb.addEventListener('click', function() {
                document.querySelectorAll('.pd-thumb').forEach(function(t) {
                    t.classList.remove('is-active');
                });
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

        document.querySelectorAll('.pd-variant-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (btn.disabled) return;

                document.querySelectorAll('.pd-variant-btn').forEach(function(b) {
                    b.classList.remove('is-selected');
                });
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
        document.getElementById('js-qty-minus').addEventListener('click', function() {
            var val = parseInt(qtyInput.value, 10) || 1;
            if (val > 1) qtyInput.value = val - 1;
        });
        document.getElementById('js-qty-plus').addEventListener('click', function() {
            var val = parseInt(qtyInput.value, 10) || 1;
            var max = parseInt(qtyInput.max, 10) || 1;
            if (val < max) qtyInput.value = val + 1;
        });

        // --- Accordion ---
        document.querySelectorAll('.pd-accordion-trigger').forEach(function(trigger) {
            trigger.addEventListener('click', function() {
                trigger.closest('.pd-accordion-item').classList.toggle('is-open');
            });
        });

        // --- Add to cart ---
        // The button is now a real submit inside the form above, which
        // POSTs action=add / product_id / variant_id / quantity straight to
        // cart.php (same endpoint the cart page's own Update/Remove/Clear
        // forms already use). No JS needed here beyond what already keeps
        // js-selected-variant-id and the quantity input in sync above.
    })();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>