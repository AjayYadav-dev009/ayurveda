<?php

if (!function_exists('getBestSellerProducts')) {
    require_once __DIR__ . '/function/product.php';
}

try {
    $bestSellerProducts = getBestSellerProducts($conn, 12);
} catch (Exception $e) {
    // Fail closed: hide the section rather than show a broken carousel.
    $bestSellerProducts = [];
}

$bestSellerCount = count($bestSellerProducts);
?>

<style>
    /* ==========================================================================
       Best Sellers — homepage section. Self-contained: no shared slider
       classes, no shared slider JS. Everything below is namespaced "bsp" so
       it can't collide with or be affected by other sections (mirrors
       producthighlight.php's "phl" section and shopbycategory.php's "sbc").
       ========================================================================== */

    .bsp {
        padding: 56px 0;
        background: var(--color-bg);
    }

    .bsp__heading {
        text-align: center;
        font-size: 30px;
        font-weight: 700;
        color: var(--color-text);
        margin-bottom: 36px;
    }

    /* ---- Scroll viewport (real overflow-x scroll, not a transform-driven
       track — see shopbycategory.php for why) ---- */

    .bsp__viewport {
        display: flex;
        gap: 24px;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        scroll-behavior: smooth;
        cursor: grab;
        padding-bottom: 4px;

        --bsp-visible: 4;

        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .bsp__viewport::-webkit-scrollbar {
        display: none;
    }

    .bsp__viewport.is-dragging {
        cursor: grabbing;
        scroll-snap-type: none;
        scroll-behavior: auto;
    }

    .bsp__item {
        flex: 0 0 calc((100% - (var(--bsp-visible) - 1) * 24px) / var(--bsp-visible));
        min-width: 0;
        scroll-snap-align: start;
    }

    @media (max-width: 1024px) {
        .bsp__viewport {
            --bsp-visible: 3;
        }
    }

    @media (max-width: 720px) {
        .bsp__viewport {
            --bsp-visible: 2;
            gap: 16px;
        }

        .bsp__heading {
            font-size: 22px;
            margin-bottom: 22px;
        }
    }

    @media (max-width: 460px) {
        .bsp__viewport {
            --bsp-visible: 1.1;
        }
    }

    /* ---- Card ---- */

    .bsp__card {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-soft);
        -webkit-user-drag: none;
        user-select: none;
    }

    .bsp__image-wrap {
        position: relative;
        aspect-ratio: 4 / 3;
        overflow: hidden;
        background: var(--color-primary-light);
    }

    .bsp__image-wrap img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        pointer-events: none;
    }

    .bsp__image-fallback {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .bsp__image-fallback svg {
        width: 20%;
        height: 20%;
        color: var(--color-accent);
        opacity: 0.7;
    }

    .bsp__badge {
        position: absolute;
        top: 12px;
        left: 12px;
        padding: 4px 10px;
        border-radius: var(--radius-sm);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    .bsp__badge--sale {
        background: var(--color-text);
        color: var(--color-white);
    }

    .bsp__badge--soldout {
        background: var(--color-white);
        color: var(--color-text-light);
        border: 1px solid var(--color-border);
    }

    .bsp__body {
        flex: 1;
        display: flex;
        flex-direction: column;
        padding: 18px 18px 20px;
    }

    .bsp__title-link {
        display: block;
    }

    .bsp__title {
        margin: 0 0 6px;
        font-size: 15.5px;
        font-weight: 700;
        line-height: 1.35;
        color: var(--color-text);

        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .bsp__title-link:hover .bsp__title {
        color: var(--color-primary);
    }

    .bsp__rating {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 8px;
        font-size: 12px;
        color: var(--color-text-light);
    }

    .bsp__rating svg {
        width: 12px;
        height: 12px;
        color: var(--color-border);
    }

    .bsp__rating svg.is-filled {
        color: var(--color-accent);
    }

    .bsp__desc {
        margin: 0 0 12px;
        color: var(--color-text-light);
        font-size: 13px;
        line-height: 1.55;

        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .bsp__pills {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 14px;
    }

    .bsp__pill {
        padding: 4px 10px;
        border-radius: var(--radius-sm);
        border: 1px solid var(--color-border);
        font-size: 11.5px;
        font-weight: 600;
        color: var(--color-text-light);
    }

    .bsp__price-row {
        display: flex;
        align-items: baseline;
        gap: 8px;
        margin-top: auto;
        margin-bottom: 12px;
    }

    .bsp__price {
        color: var(--color-text);
        font-size: 17px;
        font-weight: 800;
    }

    .bsp__price--full {
        color: var(--color-text-light);
        font-size: 13px;
        text-decoration: line-through;
    }

    .bsp__discount {
        color: var(--color-primary);
        font-size: 12.5px;
        font-weight: 700;
    }

    .bsp__cta {
        display: block;
        width: 100%;
        padding: 11px 16px;
        border: none;
        border-radius: var(--radius-md);
        background: var(--color-primary);
        color: var(--color-white);
        font-size: 13.5px;
        font-weight: 700;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .bsp__cta:hover {
        background: var(--color-primary-dark);
    }

    .bsp__cta:disabled {
        background: var(--color-border);
        color: var(--color-text-light);
        cursor: not-allowed;
    }

    /* ---- Page dots ---- */

    .bsp__dots {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 26px;
    }

    .bsp__dots[hidden] {
        display: none;
    }

    .bsp__dot {
        width: 9px;
        height: 9px;
        padding: 0;
        border: none;
        border-radius: 50%;
        background: var(--color-border);
        transition: background 0.3s ease, transform 0.3s ease;
    }

    .bsp__dot.is-active {
        background: var(--color-primary);
        transform: scale(1.25);
    }

    .bsp__dot:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 2px;
    }
</style>

<?php if ($bestSellerCount > 0): ?>
    <section class="bsp" data-bsp>
        <div class="container">
            <h2 class="bsp__heading">Best Sellers</h2>

            <div class="bsp__viewport" data-bsp-viewport>
                <?php foreach ($bestSellerProducts as $product):
                    $hasSale = $product['display_sale_price'] !== null && $product['display_sale_price'] < $product['display_price'];
                    $discountPercent = $hasSale
                        ? (int) round((1 - ($product['display_sale_price'] / $product['display_price'])) * 100)
                        : 0;
                    $isOutOfStock = !empty($product['is_out_of_stock']);
                    $productUrl = BASE_URL . 'product_details.php?slug=' . urlencode($product['slug']);
                    $avgRating = (float) $product['avg_rating'];
                    $reviewCount = (int) $product['review_count'];
                ?>
                    <div class="bsp__item">
                        <div class="bsp__card">
                            <a href="<?= htmlspecialchars($productUrl, ENT_QUOTES, 'UTF-8') ?>" class="bsp__image-wrap" tabindex="-1" aria-hidden="true">
                                <span class="bsp__image-fallback">
                                    <svg viewBox="0 0 64 64" fill="currentColor">
                                        <path d="M32 6c-6 8-10 16-10 24 0 6 4 10 10 10s10-4 10-10c0-8-4-16-10-24z" />
                                        <path d="M12 24c8 0 14 6 16 14-8 2-16-2-20-8-1.5-2.4-1-4.6 4-6z" />
                                        <path d="M52 24c-8 0-14 6-16 14 8 2 16-2 20-8 1.5-2.4 1-4.6-4-6z" />
                                    </svg>
                                </span>
                                <?php if (!empty($product['primary_image'])): ?>
                                    <img
                                        src="<?= htmlspecialchars(BASE_URL . ltrim($product['primary_image'], '/'), ENT_QUOTES, 'UTF-8') ?>"
                                        alt="<?= htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8') ?>"
                                        loading="lazy"
                                        draggable="false"
                                        onerror="this.style.display='none';">
                                <?php endif; ?>

                                <?php if ($isOutOfStock): ?>
                                    <span class="bsp__badge bsp__badge--soldout">Sold Out</span>
                                <?php elseif ($hasSale): ?>
                                    <span class="bsp__badge bsp__badge--sale">On Sale</span>
                                <?php endif; ?>
                            </a>

                            <div class="bsp__body">
                                <a href="<?= htmlspecialchars($productUrl, ENT_QUOTES, 'UTF-8') ?>" class="bsp__title-link">
                                    <h3 class="bsp__title"><?= htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                                </a>

                                <?php if ($reviewCount > 0): ?>
                                    <div class="bsp__rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <svg class="<?= $i <= round($avgRating) ? 'is-filled' : '' ?>" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10 1.5l2.6 5.7 6.2.6-4.7 4.2 1.4 6.1L10 15.1l-5.5 3 1.4-6.1-4.7-4.2 6.2-.6z" />
                                            </svg>
                                        <?php endfor; ?>
                                        <a href="<?= htmlspecialchars($productUrl, ENT_QUOTES, 'UTF-8') ?>"><?= $reviewCount ?> review<?= $reviewCount === 1 ? '' : 's' ?></a>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($product['short_description'])): ?>
                                    <p class="bsp__desc"><?= htmlspecialchars($product['short_description'], ENT_QUOTES, 'UTF-8') ?></p>
                                <?php endif; ?>

                                <?php if (!empty($product['variant_labels'])): ?>
                                    <div class="bsp__pills">
                                        <?php foreach ($product['variant_labels'] as $label): ?>
                                            <span class="bsp__pill"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="bsp__price-row">
                                    <?php if ($hasSale): ?>
                                        <span class="bsp__price">&#8377;<?= number_format($product['display_sale_price'], 0) ?></span>
                                        <span class="bsp__price--full">&#8377;<?= number_format($product['display_price'], 0) ?></span>
                                        <span class="bsp__discount"><?= $discountPercent ?>% off</span>
                                    <?php else: ?>
                                        <span class="bsp__price">&#8377;<?= number_format($product['display_price'], 0) ?></span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($isOutOfStock): ?>
                                    <button type="button" class="bsp__cta" disabled>Sold Out</button>
                                <?php else: ?>
                                    <form method="POST" action="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>cart/index.php">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                        <input type="hidden" name="variant_id" value="<?= $product['default_variant_id'] !== null ? (int) $product['default_variant_id'] : '' ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="bsp__cta">Add to Cart</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="bsp__dots" data-bsp-dots hidden></div>
        </div>
    </section>

    <script>
        (function () {
            // Identical drag/dots behavior to producthighlight.php's script,
            // scoped to [data-bsp] instead of [data-phl] — kept as a
            // separate copy (not a shared function) so this section stays
            // fully independent of the other homepage carousels.
            document.querySelectorAll('[data-bsp]').forEach(function (root) {
                var viewport = root.querySelector('[data-bsp-viewport]');
                var dotsWrap = root.querySelector('[data-bsp-dots]');
                var items = Array.prototype.slice.call(viewport.children);
                var count = items.length;

                if (count === 0) {
                    return;
                }

                var isDown = false;
                var dragged = false;
                var startX = 0;
                var startScroll = 0;

                viewport.addEventListener('mousedown', function (event) {
                    isDown = true;
                    dragged = false;
                    viewport.classList.add('is-dragging');
                    startX = event.pageX;
                    startScroll = viewport.scrollLeft;
                });

                window.addEventListener('mousemove', function (event) {
                    if (!isDown) {
                        return;
                    }
                    var delta = event.pageX - startX;
                    if (Math.abs(delta) > 4) {
                        dragged = true;
                    }
                    viewport.scrollLeft = startScroll - delta;
                });

                function endDrag() {
                    if (!isDown) {
                        return;
                    }
                    isDown = false;
                    viewport.classList.remove('is-dragging');
                    snapToNearest();
                }

                window.addEventListener('mouseup', endDrag);
                viewport.addEventListener('mouseleave', function () {
                    if (isDown) {
                        endDrag();
                    }
                });

                viewport.addEventListener('click', function (event) {
                    if (dragged) {
                        event.preventDefault();
                        dragged = false;
                    }
                }, true);

                function snapToNearest() {
                    var pageWidth = viewport.clientWidth;
                    var page = Math.round(viewport.scrollLeft / pageWidth);
                    viewport.scrollTo({ left: page * pageWidth, behavior: 'smooth' });
                }

                function pageCount() {
                    var raw = getComputedStyle(viewport).getPropertyValue('--bsp-visible');
                    var visible = Math.max(1, Math.floor(parseFloat(raw)) || 1);
                    return Math.max(1, Math.ceil(count / visible));
                }

                function buildDots() {
                    dotsWrap.innerHTML = '';
                    var pages = pageCount();
                    if (pages <= 1) {
                        dotsWrap.hidden = true;
                        return;
                    }
                    dotsWrap.hidden = false;
                    for (var i = 0; i < pages; i++) {
                        var dot = document.createElement('button');
                        dot.type = 'button';
                        dot.className = 'bsp__dot';
                        dot.setAttribute('aria-label', 'Go to products page ' + (i + 1));
                        (function (page) {
                            dot.addEventListener('click', function () {
                                viewport.scrollTo({ left: page * viewport.clientWidth, behavior: 'smooth' });
                            });
                        })(i);
                        dotsWrap.appendChild(dot);
                    }
                    updateActiveDot();
                }

                function updateActiveDot() {
                    var pageWidth = viewport.clientWidth || 1;
                    var active = Math.round(viewport.scrollLeft / pageWidth);
                    var dots = dotsWrap.querySelectorAll('.bsp__dot');
                    dots.forEach(function (dot, i) {
                        dot.classList.toggle('is-active', i === active);
                    });
                }

                var scrollTimer = null;
                viewport.addEventListener('scroll', function () {
                    window.clearTimeout(scrollTimer);
                    scrollTimer = window.setTimeout(updateActiveDot, 80);
                });

                var resizeTimer = null;
                window.addEventListener('resize', function () {
                    window.clearTimeout(resizeTimer);
                    resizeTimer = window.setTimeout(buildDots, 150);
                });

                buildDots();
            });
        })();
    </script>
<?php endif; ?>