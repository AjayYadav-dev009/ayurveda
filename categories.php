
<?php 
require_once __DIR__ . '/config/config.php'; 
require_once __DIR__ . '/config/database.php'; 
require_once __DIR__ . '/function/category.php'; 
 
$categories = getCategories($conn); 
?> 

<style>
    .category-section {
        padding: 40px;
        background: #f8faf7;
    }

    .category-header {
        text-align: center;
        margin-bottom: 35px;
    }

    .category-header h2 {
        margin: 0 0 8px;
        font-size: 32px;
        color: #234d32;
        font-weight: 700;
    }

    .category-header p {
        margin: 0;
        color: #6b7280;
        font-size: 15px;
    }

    .category-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
    }

    .category-card {
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #e5ebe5;
        box-shadow: 0 4px 14px rgba(35, 77, 50, 0.08);
        transition: all 0.3s ease;
    }

    .category-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 28px rgba(35, 77, 50, 0.15);
    }

    .category-image {
        width: 100%;
        height: 210px;
        object-fit: cover;
        display: block;
    }

    .category-content {
        padding: 20px;
    }

    .category-content h3 {
        margin: 0 0 10px;
        color: #234d32;
        font-size: 20px;
        font-weight: 700;
    }

    .category-content p {
        margin: 0;
        color: #6b7280;
        font-size: 14px;
        line-height: 1.6;
    }

    .category-link {
        display: inline-block;
        margin-top: 16px;
        color: #3f7d4f;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
    }

    .category-link:hover {
        color: #234d32;
    }

    @media (max-width: 1100px) {
        .category-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 800px) {
        .category-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .category-section {
            padding: 25px;
        }
    }

    @media (max-width: 500px) {
        .category-grid {
            grid-template-columns: 1fr;
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

            <div class="category-card">

                <img 
                    src="<?php echo htmlspecialchars($category['image']); ?>" 
                    alt="<?php echo htmlspecialchars($category['name']); ?>"
                    class="category-image"
                >

                <div class="category-content">

                    <h3>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </h3>

                    <p>
                        <?php echo htmlspecialchars($category['description']); ?>
                    </p>

                    <a 
                        href="<?php echo BASE_URL; ?>categories-product.php?category_slug=<?php echo $category['slug']; ?>"
                        class="category-link"
                    >
                        Explore Category →
                    </a>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</section>
