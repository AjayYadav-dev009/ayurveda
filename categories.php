<?php 
require_once __DIR__ . '/config/config.php'; 
require_once __DIR__ . '/config/database.php'; 
require_once __DIR__ . '/function/category.php'; 

// Root categories are organisational only and never a browsable destination
// on their own; getSubcategoriesWithProducts() also skips anything with no
// Active products, so an empty card never reaches this grid.
$categories = [];
$categoryResult = getSubcategoriesWithProducts($conn);
while ($row = mysqli_fetch_assoc($categoryResult)) {
    $categories[] = $row;
}
?> 

<?php include __DIR__ . '/includes/header.php'; ?>

<style>
    .category-section {
        padding: 56px 40px;
        background: var(--color-bg);
    }

    .category-header {
        max-width: 560px;
        margin: 0 auto 40px;
        text-align: center;
    }

    .category-header h2 {
        margin: 0 0 10px;
        font-size: 32px;
        font-weight: 800;
        color: var(--color-primary);
    }

    .category-header p {
        margin: 0;
        color: var(--color-text-light);
        font-size: 15px;
        line-height: 1.6;
    }

    .category-grid {
        max-width: var(--container-width);
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 28px;
    }

    .category-card {
        display: flex;
        flex-direction: column;
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-soft);
        transition: border-color 0.25s ease;
    }

    .category-card:hover {
        border-color: var(--color-accent);
    }

    .category-card:hover .category-image {
        transform: scale(1.04);
    }

    .category-card:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 3px;
    }

    .category-image-wrap {
        aspect-ratio: 4 / 3;
        overflow: hidden;
        background: var(--color-primary-light);
    }

    .category-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    /* Shown instead of a broken <img> when a category has no image set */
    .category-image-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .category-image-placeholder svg {
        width: 44px;
        height: 44px;
        color: var(--color-accent);
    }

    .category-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        padding: 20px 22px 22px;
    }

    .category-content h3 {
        margin: 0 0 8px;
        color: var(--color-text);
        font-size: 18px;
        font-weight: 700;
    }

    .category-content p {
        margin: 0;
        flex: 1;
        color: var(--color-text-light);
        font-size: 14px;
        line-height: 1.6;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .category-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 16px;
        color: var(--color-primary);
        font-size: 14px;
        font-weight: 700;
    }

    .category-card:hover .category-link {
        color: var(--color-primary-dark);
    }

    .category-link svg {
        width: 15px;
        height: 15px;
        transition: transform 0.2s ease;
    }

    .category-card:hover .category-link svg {
        transform: translateX(3px);
    }

    @media (max-width: 500px) {
        .category-section {
            padding: 36px 20px;
        }

        .category-header h2 {
            font-size: 26px;
        }
    }
</style>

<section class="category-section">

    <div class="category-header">
        <h2>Shop by Category</h2>
        <p>Explore our range of Ayurvedic and herbal wellness products</p>
    </div>

    <div class="category-grid">

        <?php foreach ($categories as $category): ?>

            <a
                href="<?php echo BASE_URL; ?>products.php?category_slug=<?php echo urlencode($category['slug']); ?>"
                class="category-card"
            >
                <div class="category-image-wrap">
                    <?php if (!empty($category['image'])): ?>
                        <img
                            src="<?php echo htmlspecialchars($category['image']); ?>"
                            alt="<?php echo htmlspecialchars($category['name']); ?>"
                            class="category-image"
                            loading="lazy"
                        >
                    <?php else: ?>
                        <div class="category-image-placeholder" aria-hidden="true">
                            <svg viewBox="0 0 64 64" fill="currentColor">
                                <path d="M32 6c-6 8-10 16-10 24 0 6 4 10 10 10s10-4 10-10c0-8-4-16-10-24z" />
                                <path d="M12 24c8 0 14 6 16 14-8 2-16-2-20-8-1.5-2.4-1-4.6 4-6z" />
                                <path d="M52 24c-8 0-14 6-16 14 8 2 16-2 20-8 1.5-2.4 1-4.6-4-6z" />
                            </svg>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="category-content">

                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>

                    <?php if (!empty($category['description'])): ?>
                        <p><?php echo htmlspecialchars($category['description']); ?></p>
                    <?php endif; ?>

                    <span class="category-link">
                        Explore Category
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </span>

                </div>

            </a>

        <?php endforeach; ?>

    </div>

</section>

<?php include __DIR__ . '/includes/footer.php'; ?>