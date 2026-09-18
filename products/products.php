<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/product.php';
require_once __DIR__ . '/../function/category.php';

$categorySlug = isset($_GET['category_slug']) ? trim($_GET['category_slug']) : '';

if ($categorySlug === '') {
    header('Location: ' . BASE_URL . 'categories.php');
    exit;
}

// Look up the category itself (for the page header) — only Active categories
// are a valid browse target, same rule getSubcategoriesWithProducts() applies.
//
// NOTE: these are deliberately NOT named $category / $products. header.php
// is include()'d below and shares this file's variable scope — its mega-menu
// loop reuses those exact names internally, and since the include runs
// between this fetch and the render further down, it was silently
// overwriting both with whatever it last looped over. The sidebar fetch
// below follows the same rule: $categorySidebarItems / $sidebarCategory,
// never $category(ies) / $products.
$currentCategory = null;
$stmt = mysqli_prepare($conn, "SELECT id, name, slug, description FROM categories WHERE slug = ? AND status = 'Active' LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $categorySlug);
mysqli_stmt_execute($stmt);
$currentCategory = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$currentCategory) {
    header('Location: ' . BASE_URL . 'categories.php');
    exit;
}

$categoryProducts = [];
$productResult = getProductsByCategorySlug($conn, $categorySlug);
while ($row = mysqli_fetch_assoc($productResult)) {
    $categoryProducts[] = $row;
}

// Sidebar: every Active category, at any depth (root, child, grandchild),
// that has at least one Active product assigned DIRECTLY to it — so the
// page doubles as a category switcher without linking to a bucket that
// would just show "no products". Deliberately NOT
// getTopLevelCategoriesWithProducts(): that only looks at root categories,
// which is why real (non-root) categories like "Digestive Powders" never
// showed up even while browsing them. getAllCategoriesWithProducts()
// already does the direct-only, any-depth check this page needs. Fails
// closed to an empty list (sidebar just won't render) rather than
// breaking the whole page if this query has a problem.
try {
    $categorySidebarItems = getAllCategoriesWithProducts($conn);
} catch (Exception $e) {
    $categorySidebarItems = [];
}
$hasSidebar = !empty($categorySidebarItems);
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<style>
    .product-section {
        padding: 56px 40px;
        background: var(--color-bg);
    }

    .product-header {
        max-width: 560px;
        margin: 0 auto 40px;
        text-align: center;
    }

    .product-header h2 {
        margin: 0 0 10px;
        font-size: 32px;
        font-weight: 800;
        color: var(--color-primary);
    }

    .product-header p {
        margin: 0;
        color: var(--color-text-light);
        font-size: 15px;
        line-height: 1.6;
    }

    /* ---- Sidebar + grid layout ---- */

    .category-layout {
        max-width: var(--container-width);
        margin: 0 auto;
        display: grid;
        grid-template-columns: 240px 1fr;
        gap: 32px;
        align-items: start;
    }

    .category-layout--no-sidebar {
        grid-template-columns: 1fr;
    }

    .category-sidebar {
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-soft);
        padding: 22px 18px;
        position: sticky;
        top: 24px;
    }

    .category-sidebar__label {
        margin: 0 0 14px;
        padding: 0 4px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--color-accent);
    }

    .category-sidebar__list {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .category-sidebar__item {
        display: block;
        padding: 10px 14px;
        border-radius: var(--radius-md);
        font-size: 14px;
        font-weight: 600;
        color: var(--color-text);
        transition: background 0.2s ease, color 0.2s ease;
    }

    .category-sidebar__item:hover {
        background: var(--color-primary-light);
        color: var(--color-primary-dark);
    }

    .category-sidebar__item.is-active {
        background: var(--color-primary);
        color: var(--color-white);
    }

    .category-sidebar__item:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 2px;
    }

    @media (max-width: 860px) {
        .category-layout {
            grid-template-columns: 1fr;
        }

        .category-sidebar {
            position: static;
            padding: 14px;
        }

        .category-sidebar__label {
            padding: 0 2px;
        }

        .category-sidebar__list {
            flex-direction: row;
            overflow-x: auto;
            gap: 8px;
            padding-bottom: 2px;
            scrollbar-width: none;
        }

        .category-sidebar__list::-webkit-scrollbar {
            display: none;
        }

        .category-sidebar__item {
            white-space: nowrap;
            flex: 0 0 auto;
        }
    }

    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 28px;
    }

    .product-card {
        display: flex;
        flex-direction: column;
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-soft);
        transition: border-color 0.25s ease;
    }

    .product-card:hover {
        border-color: var(--color-accent);
    }

    .product-card:hover .product-image {
        transform: scale(1.04);
    }

    .product-card:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 3px;
    }

    .product-image-wrap {
        position: relative;
        aspect-ratio: 4 / 3;
        overflow: hidden;
        background: var(--color-primary-light);
    }

    .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .product-card.is-out-of-stock .product-image {
        opacity: 0.55;
    }

    /* Shown instead of a broken <img> when a product has no primary image */
    .product-image-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .product-image-placeholder svg {
        width: 44px;
        height: 44px;
        color: var(--color-accent);
    }

    .product-badges {
        position: absolute;
        top: 12px;
        left: 12px;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }

    .product-badge {
        padding: 4px 10px;
        border-radius: var(--radius-sm);
        font-size: 11px;
        font-weight: 700;
        color: var(--color-white);
        background: var(--color-primary);
    }

    .product-badge--discount {
        background: var(--color-accent);
    }

    .product-badge--out-of-stock {
        position: absolute;
        top: 12px;
        right: 12px;
        background: var(--color-text);
    }

    .product-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        padding: 20px 22px 22px;
    }

    .product-category {
        margin: 0 0 4px;
        color: var(--color-accent);
        font-size: 12px;
        font-weight: 600;
    }

    .product-content h3 {
        margin: 0 0 8px;
        color: var(--color-text);
        font-size: 17px;
        font-weight: 700;
        line-height: 1.35;
    }

    .product-content p {
        margin: 0;
        flex: 1;
        color: var(--color-text-light);
        font-size: 14px;
        line-height: 1.6;

        /* Clamp so mismatched description lengths don't break card heights */
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-price-row {
        display: flex;
        align-items: baseline;
        gap: 8px;
        margin-top: 14px;
    }

    .product-price {
        color: var(--color-primary);
        font-size: 18px;
        font-weight: 800;
    }

    .product-price--full {
        color: var(--color-text-light);
        font-size: 13px;
        font-weight: 500;
        text-decoration: line-through;
    }

    .product-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 14px;
        color: var(--color-primary);
        font-size: 14px;
        font-weight: 700;
    }

    .product-card:hover .product-link {
        color: var(--color-primary-dark);
    }

    .product-link svg {
        width: 15px;
        height: 15px;
        transition: transform 0.2s ease;
    }

    .product-card:hover .product-link svg {
        transform: translateX(3px);
    }

    .product-empty {
        text-align: center;
        padding: 40px 20px;
        color: var(--color-text-light);
        font-size: 15px;
    }

    @media (max-width: 500px) {
        .product-section {
            padding: 36px 20px;
        }

        .product-header h2 {
            font-size: 26px;
        }
    }
</style>

<section class="product-section">

    <div class="product-header">
        <h2><?php echo htmlspecialchars($currentCategory['name']); ?></h2>
        <?php if (!empty($currentCategory['description'])): ?>
            <p><?php echo htmlspecialchars($currentCategory['description']); ?></p>
        <?php else: ?>
            <p>Explore our range of Ayurvedic and herbal wellness products</p>
        <?php endif; ?>
    </div>

    <div class="category-layout<?php echo $hasSidebar ? '' : ' category-layout--no-sidebar'; ?>">

        <?php if ($hasSidebar): ?>
            <aside class="category-sidebar">
                <p class="category-sidebar__label">Categories</p>
                <nav class="category-sidebar__list" aria-label="Product categories">
                    <?php foreach ($categorySidebarItems as $sidebarCategory): ?>
                        <?php $isActiveCategory = $sidebarCategory['slug'] === $currentCategory['slug']; ?>
                        <a
                            href="<?php echo htmlspecialchars(getCategoryUrl($sidebarCategory['slug']), ENT_QUOTES, 'UTF-8'); ?>"
                            class="category-sidebar__item<?php echo $isActiveCategory ? ' is-active' : ''; ?>"
                            <?php echo $isActiveCategory ? ' aria-current="page"' : ''; ?>>
                            <?php echo htmlspecialchars($sidebarCategory['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </aside>
        <?php endif; ?>

        <div class="category-layout__main">

            <?php if (empty($categoryProducts)): ?>

                <p class="product-empty">No products found in this category yet.</p>

            <?php else: ?>

                <div class="product-grid">

                    <?php foreach ($categoryProducts as $product): ?>

                        <?php
                        $hasSale = !empty($product['base_sale_price']) && (float) $product['base_sale_price'] < (float) $product['base_price'];
                        $discountPercent = $hasSale
                            ? (int) round((1 - ((float) $product['base_sale_price'] / (float) $product['base_price'])) * 100)
                            : 0;
                        $isOutOfStock = !$product['has_variants'] && (int) $product['stock'] <= 0;
                        ?>

                        <a
                            href="<?php echo BASE_URL; ?>products/product_details.php?slug=<?php echo urlencode($product['slug']); ?>"
                            class="product-card<?php echo $isOutOfStock ? ' is-out-of-stock' : ''; ?>">
                            <div class="product-image-wrap">
                                <?php if (!empty($product['primary_image'])): ?>
                                    <img
                                        src="<?php echo rtrim(BASE_URL, '/') . getProductImageUrl($product['primary_image']); ?>"
                                        alt="<?php echo htmlspecialchars($product['title']); ?>"
                                        class="product-image"
                                        loading="lazy">
                                <?php else: ?>
                                    <div class="product-image-placeholder" aria-hidden="true">
                                        <svg viewBox="0 0 64 64" fill="currentColor">
                                            <path d="M32 6c-6 8-10 16-10 24 0 6 4 10 10 10s10-4 10-10c0-8-4-16-10-24z" />
                                            <path d="M12 24c8 0 14 6 16 14-8 2-16-2-20-8-1.5-2.4-1-4.6 4-6z" />
                                            <path d="M52 24c-8 0-14 6-16 14 8 2 16-2 20-8 1.5-2.4 1-4.6-4-6z" />
                                        </svg>
                                    </div>
                                <?php endif; ?>

                                <div class="product-badges">
                                    <?php if ($hasSale): ?>
                                        <span class="product-badge product-badge--discount"><?php echo $discountPercent; ?>% off</span>
                                    <?php endif; ?>
                                    <?php if (!empty($product['bestseller'])): ?>
                                        <span class="product-badge">Bestseller</span>
                                    <?php elseif (!empty($product['featured'])): ?>
                                        <span class="product-badge">Featured</span>
                                    <?php elseif (!empty($product['trending'])): ?>
                                        <span class="product-badge">Trending</span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($isOutOfStock): ?>
                                    <span class="product-badge product-badge--out-of-stock">Out of Stock</span>
                                <?php endif; ?>
                            </div>

                            <div class="product-content">

                                <h3><?php echo htmlspecialchars($product['title']); ?></h3>

                                <?php if (!empty($product['short_description'])): ?>
                                    <p><?php echo htmlspecialchars($product['short_description']); ?></p>
                                <?php endif; ?>

                                <div class="product-price-row">
                                    <?php if ($hasSale): ?>
                                        <span class="product-price">&#8377;<?php echo number_format((float) $product['base_sale_price'], 0); ?></span>
                                        <span class="product-price--full">&#8377;<?php echo number_format((float) $product['base_price'], 0); ?></span>
                                    <?php else: ?>
                                        <span class="product-price"><?php echo $product['has_variants'] ? 'From ' : ''; ?>&#8377;<?php echo number_format((float) $product['base_price'], 0); ?></span>
                                    <?php endif; ?>
                                </div>

                                <span class="product-link">
                                    View Product
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <polyline points="12 5 19 12 12 19"></polyline>
                                    </svg>
                                </span>

                            </div>

                        </a>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>

    </div>

</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>