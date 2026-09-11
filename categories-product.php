<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/function/product.php';

$slug = $_GET['category_slug'] ?? '';

$products = getProductsByCategorySlug($conn, $slug);
?>

<style>
    /* ================================
   Products Section
    ================================ */

    .products-section {
        width: 100%;
        padding: 60px 20px;
        background: #ffffff;
    }

    .products-grid {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;

        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 28px;
    }


    /* ================================
   Product Card
    ================================ */

    .product-card {
        display: flex;
        flex-direction: column;

        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;

        overflow: hidden;

        box-shadow: 0 8px 25px rgba(15, 23, 42, 0.06);

        transition:
            transform 0.3s ease,
            box-shadow 0.3s ease,
            border-color 0.3s ease;
    }

    .product-card:hover {
        transform: translateY(-6px);
        border-color: #86efac;
        box-shadow: 0 16px 35px rgba(15, 23, 42, 0.10);
    }


    /* ================================
   Product Image
    ================================ */

    .product-image {
        width: 100%;
        height: 250px;

        background: #f8faf8;

        display: flex;
        align-items: center;
        justify-content: center;

        overflow: hidden;
    }

    .product-image img {
        width: 100%;
        height: 100%;

        object-fit: cover;

        display: block;

        transition: transform 0.4s ease;
    }

    .product-card:hover .product-image img {
        transform: scale(1.05);
    }


    /* ================================
   Product Content
    ================================ */

    .product-content {
        display: flex;
        flex-direction: column;

        padding: 22px;

        flex: 1;
    }

    .product-content h3 {
        margin: 0 0 10px;

        color: #17251b;

        font-size: 20px;
        font-weight: 700;
        line-height: 1.35;
    }

    .product-content p {
        margin: 0 0 18px;

        color: #647067;

        font-size: 14px;
        line-height: 1.6;

        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }


    /* ================================
   Variants
    ================================ */

    .product-variants {
        display: flex;
        flex-direction: column;

        gap: 10px;

        margin-top: auto;
        margin-bottom: 20px;
    }

    .variant {
        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 15px;

        padding: 11px 13px;

        background: #f7faf7;

        border: 1px solid #e4ebe5;
        border-radius: 10px;
    }

    .variant-name {
        color: #344239;

        font-size: 14px;
        font-weight: 600;
    }

    .variant-price {
        color: #16803c;

        font-size: 15px;
        font-weight: 700;

        white-space: nowrap;
    }

    .variant-price del {
        margin-left: 6px;

        color: #9ca3af;

        font-size: 12px;
        font-weight: 400;
    }


    /* ================================
   Product Price
    ================================ */

    .product-price {
        display: flex;
        align-items: center;

        gap: 9px;

        margin-top: auto;
        margin-bottom: 20px;
    }

    .sale-price {
        color: #16803c;

        font-size: 21px;
        font-weight: 800;
    }

    .regular-price del {
        color: #9ca3af;

        font-size: 14px;
    }


    /* ================================
   View Product Button
    ================================ */

    .product-button {
        width: 100%;

        display: flex;
        align-items: center;
        justify-content: center;

        min-height: 46px;

        padding: 12px 18px;

        background: #198754;
        color: #ffffff;

        border-radius: 10px;

        text-decoration: none;

        font-size: 14px;
        font-weight: 700;

        transition:
            background 0.25s ease,
            transform 0.25s ease;
    }

    .product-button:hover {
        background: #146c43;
        transform: translateY(-1px);
    }


    /* ================================
   No Products
    ================================ */

    .products-grid>p {
        grid-column: 1 / -1;

        margin: 20px 0;

        padding: 40px 20px;

        text-align: center;

        color: #6b7280;

        background: #f8faf8;

        border: 1px dashed #d1d5db;
        border-radius: 14px;

        font-size: 15px;
    }


    /* ================================
   Tablet
    ================================ */

    @media (max-width: 992px) {

        .products-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
        }

        .product-image {
            height: 230px;
        }
    }


    /* ================================
   Mobile
    ================================ */

    @media (max-width: 640px) {

        .products-section {
            padding: 40px 15px;
        }

        .products-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .product-image {
            height: 260px;
        }

        .product-content {
            padding: 18px;
        }

        .product-content h3 {
            font-size: 18px;
        }

        .variant {
            padding: 10px 12px;
        }
    }
</style>

<section class="products-section">
    <div class="products-grid">


        <?php if ($products && mysqli_num_rows($products) > 0): ?>

            <?php while ($product = mysqli_fetch_assoc($products)): ?>

                <?php
                $productData = getProductWithRelations($conn, $product['id']);
                $variants = $productData['variants'];
                ?>

                <div class="product-card">

                    <div class="product-image">

                        <?php if (!empty($product['primary_image'])): ?>

                            <img
                                src="<?php echo BASE_URL . htmlspecialchars($product['primary_image']); ?>"
                                alt="<?php echo htmlspecialchars($product['title']); ?>">

                        <?php else: ?>

                            <img
                                src="<?php echo BASE_URL; ?>uploads/default.png"
                                alt="Product image unavailable">

                        <?php endif; ?>

                    </div>

                    <div class="product-content">

                        <h3>
                            <?php echo htmlspecialchars($product['title']); ?>
                        </h3>

                        <?php if (!empty($product['short_description'])): ?>

                            <p>
                                <?php echo htmlspecialchars($product['short_description']); ?>
                            </p>

                        <?php endif; ?>


                        <?php if (!empty($variants)): ?>

                            <div class="product-variants">

                                <?php foreach ($variants as $variant): ?>

                                    <div class="variant">

                                        <span class="variant-name">
                                            <?php echo htmlspecialchars($variant['variant_name']); ?>
                                        </span>

                                        <span class="variant-price">

                                            <?php if (
                                                $variant['sale_price'] !== null &&
                                                $variant['sale_price'] !== ''
                                            ): ?>

                                                ₹<?php echo number_format(
                                                        (float) $variant['sale_price'],
                                                        2
                                                    ); ?>

                                                <del>
                                                    ₹<?php echo number_format(
                                                            (float) $variant['price'],
                                                            2
                                                        ); ?>
                                                </del>

                                            <?php else: ?>

                                                ₹<?php echo number_format(
                                                        (float) $variant['price'],
                                                        2
                                                    ); ?>

                                            <?php endif; ?>

                                        </span>

                                    </div>

                                <?php endforeach; ?>

                            </div>

                        <?php else: ?>

                            <div class="product-price">

                                <?php if (
                                    $product['base_sale_price'] !== null &&
                                    $product['base_sale_price'] !== ''
                                ): ?>

                                    <span class="sale-price">
                                        ₹<?php echo number_format(
                                                (float) $product['base_sale_price'],
                                                2
                                            ); ?>
                                    </span>

                                    <span class="regular-price">
                                        <del>
                                            ₹<?php echo number_format(
                                                    (float) $product['base_price'],
                                                    2
                                                ); ?>
                                        </del>
                                    </span>

                                <?php else: ?>

                                    <span class="sale-price">
                                        ₹<?php echo number_format(
                                                (float) $product['base_price'],
                                                2
                                            ); ?>
                                    </span>

                                <?php endif; ?>

                            </div>

                        <?php endif; ?>


                        <a
                            href="<?php echo BASE_URL; ?>product.php?slug=<?php echo urlencode($product['slug']); ?>"
                            class="product-button">
                            View Product
                        </a>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p>No products found in this category.</p>

        <?php endif; ?>

    </div>


</section>