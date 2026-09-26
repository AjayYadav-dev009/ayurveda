<?php

if (!function_exists('getTrendingProducts')) {
    require_once __DIR__ . '/function/product.php';
}

try {
    $trendingProducts = getTrendingProducts($conn, 100);
} catch (Exception $e) {
    // Fail closed: hide the section rather than show a broken carousel.
    $trendingProducts = [];
}

$trendingCount = count($trendingProducts);

// Wishlist state for the heart toggle on each card. Fetched in bulk
// (one query for every wishlisted product_id) rather than calling
// isInWishlist() once per card in the loop below.
if (!function_exists('isCustomerLogin')) {
    require_once __DIR__ . '/../includes/session.php';
    require_once __DIR__ . '/../function/customer.php';
}
if (!function_exists('generateCSRFToken')) {
    require_once __DIR__ . '/../function/csrf.php';
}
if (!function_exists('getWishlistProductIds')) {
    require_once __DIR__ . '/../function/wishlist.php';
}

$isCustomerLoggedIn = isCustomerLogin($conn);
$wishlistProductIds = [];
if ($isCustomerLoggedIn) {
    try {
        $wishlistProductIds = getWishlistProductIds($conn, $_SESSION['customer_id']);
    } catch (Exception $e) {
        error_log('Failed to load wishlist state: ' . $e->getMessage());
        $wishlistProductIds = [];
    }
}
$wishlistCsrfToken = generateCSRFToken();
?>

<style>
    /* ==========================================================================
       Trending — homepage section. Self-contained: no shared slider
       classes, no shared slider JS. Everything below is namespaced "trd" so
       it can't collide with or be affected by other sections (mirrors
       producthighlight.php's "phl" section and shopbycategory.php's "sbc").
       ========================================================================== */

    .trd {
        padding: 56px 0;
        background: var(--color-bg);
    }

    .trd__heading {
        text-align: center;
        font-size: 30px;
        font-weight: 700;
        color: var(--color-text);
        margin-bottom: 36px;
    }

    /* ---- Scroll viewport (real overflow-x scroll, not a transform-driven
       track — see shopbycategory.php for why) ---- */

    .trd__viewport {
        display: flex;
        gap: 24px;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        scroll-behavior: smooth;
        cursor: grab;
        padding-bottom: 4px;

        --trd-visible: 4;

        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .trd__viewport::-webkit-scrollbar {
        display: none;
    }

    .trd__viewport.is-dragging {
        cursor: grabbing;
        scroll-snap-type: none;
        scroll-behavior: auto;
    }

    .trd__item {
        flex: 0 0 calc((100% - (var(--trd-visible) - 1) * 24px) / var(--trd-visible));
        min-width: 0;
        scroll-snap-align: start;
    }

    @media (max-width: 1024px) {
        .trd__viewport {
            --trd-visible: 3;
        }
    }

    @media (max-width: 720px) {
        .trd__viewport {
            --trd-visible: 2;
            gap: 16px;
        }

        .trd__heading {
            font-size: 22px;
            margin-bottom: 22px;
        }
    }

    @media (max-width: 460px) {
        .trd__viewport {
            --trd-visible: 1.1;
        }
    }

    /* ---- Card ---- */

    .trd__card {
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

    .trd__image-wrap {
        position: relative;
        aspect-ratio: 4 / 3;
        overflow: hidden;
        background: var(--color-primary-light);
    }

    .trd__image-wrap img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        pointer-events: none;
    }

    .trd__image-fallback {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .trd__image-fallback svg {
        width: 20%;
        height: 20%;
        color: var(--color-accent);
        opacity: 0.7;
    }

    .trd__image-link {
        position: absolute;
        inset: 0;
        display: block;
    }

    .trd__wishlist-btn {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 3;
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        border-radius: 50%;
        background: var(--color-white);
        box-shadow: var(--shadow-soft);
        color: var(--color-text-light);
        cursor: pointer;
        transition: color 0.2s ease, transform 0.15s ease;
    }

    .trd__wishlist-btn:hover {
        color: var(--color-accent);
        transform: scale(1.08);
    }

    .trd__wishlist-btn svg {
        width: 17px;
        height: 17px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        transition: fill 0.2s ease;
    }

    .trd__wishlist-btn.is-active {
        color: var(--color-accent);
    }

    .trd__wishlist-btn.is-active svg {
        fill: currentColor;
    }

    .trd__wishlist-btn.is-loading {
        opacity: 0.6;
        pointer-events: none;
    }

    .trd__badge {
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

    .trd__badge--sale {
        background: var(--color-text);
        color: var(--color-white);
    }

    .trd__badge--soldout {
        background: var(--color-white);
        color: var(--color-text-light);
        border: 1px solid var(--color-border);
    }

    .trd__body {
        flex: 1;
        display: flex;
        flex-direction: column;
        padding: 18px 18px 20px;
    }

    .trd__title-link {
        display: block;
    }

    .trd__title {
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

    .trd__title-link:hover .trd__title {
        color: var(--color-primary);
    }

    .trd__rating {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 8px;
        font-size: 12px;
        color: var(--color-text-light);
    }

    .trd__rating svg {
        width: 12px;
        height: 12px;
        color: var(--color-border);
    }

    .trd__rating svg.is-filled {
        color: var(--color-accent);
    }

    .trd__desc {
        margin: 0 0 12px;
        color: var(--color-text-light);
        font-size: 13px;
        line-height: 1.55;

        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .trd__pills {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 14px;
    }

    .trd__pill {
        padding: 4px 10px;
        border-radius: var(--radius-sm);
        border: 1px solid var(--color-border);
        font-size: 11.5px;
        font-weight: 600;
        color: var(--color-text-light);
    }

    .trd__price-row {
        display: flex;
        align-items: baseline;
        gap: 8px;
        margin-top: auto;
        margin-bottom: 12px;
    }

    .trd__price {
        color: var(--color-text);
        font-size: 17px;
        font-weight: 800;
    }

    .trd__price--full {
        color: var(--color-text-light);
        font-size: 13px;
        text-decoration: line-through;
    }

    .trd__discount {
        color: var(--color-primary);
        font-size: 12.5px;
        font-weight: 700;
    }

    .trd__cta {
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

    .trd__cta:hover {
        background: var(--color-primary-dark);
    }

    .trd__cta:disabled {
        background: var(--color-border);
        color: var(--color-text-light);
        cursor: not-allowed;
    }

    /* ---- Page dots ---- */

    .trd__dots {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 26px;
    }

    .trd__dots[hidden] {
        display: none;
    }

    .trd__dot {
        width: 9px;
        height: 9px;
        padding: 0;
        border: none;
        border-radius: 50%;
        background: var(--color-border);
        transition: background 0.3s ease, transform 0.3s ease;
    }

    .trd__dot.is-active {
        background: var(--color-primary);
        transform: scale(1.25);
    }

    .trd__dot:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 2px;
    }
</style>

<?php if ($trendingCount > 0): ?>
    <section class="trd" data-trd>
        <div class="container">
            <h2 class="trd__heading">Trending</h2>

            <div class="trd__viewport" data-trd-viewport>
                <?php foreach ($trendingProducts as $product):
                    $hasSale = $product['display_sale_price'] !== null && $product['display_sale_price'] < $product['display_price'];
                    $discountPercent = $hasSale
                        ? (int) round((1 - ($product['display_sale_price'] / $product['display_price'])) * 100)
                        : 0;
                    $isOutOfStock = !empty($product['is_out_of_stock']);
                    $productUrl = BASE_URL . 'products/product_details.php?slug=' . urlencode($product['slug']);
                    $avgRating = (float) $product['avg_rating'];
                    $reviewCount = (int) $product['review_count'];
                ?>
                    <div class="trd__item">
                        <div class="trd__card">
                            <div class="trd__image-wrap">
                                <a href="<?= htmlspecialchars($productUrl, ENT_QUOTES, 'UTF-8') ?>" class="trd__image-link" tabindex="-1" aria-hidden="true">
                                    <span class="trd__image-fallback">
                                    <svg viewBox="0 0 64 64" fill="currentColor">
                                        <path d="M32 6c-6 8-10 16-10 24 0 6 4 10 10 10s10-4 10-10c0-8-4-16-10-24z" />
                                        <path d="M12 24c8 0 14 6 16 14-8 2-16-2-20-8-1.5-2.4-1-4.6 4-6z" />
                                        <path d="M52 24c-8 0-14 6-16 14 8 2 16-2 20-8 1.5-2.4 1-4.6-4-6z" />
                                    </svg>
                                    </span>
                                    <?php if (!empty($product['primary_image'])): ?>
                                        <img
                                            src="<?= htmlspecialchars(getProductImageUrl($product['primary_image']), ENT_QUOTES, 'UTF-8') ?>"
                                            alt="<?= htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8') ?>"
                                            loading="lazy"
                                            draggable="false"
                                            onerror="this.style.display='none';">
                                    <?php endif; ?>

                                    <?php if ($isOutOfStock): ?>
                                        <span class="trd__badge trd__badge--soldout">Sold Out</span>
                                    <?php elseif ($hasSale): ?>
                                        <span class="trd__badge trd__badge--sale">On Sale</span>
                                    <?php endif; ?>
                                </a>

                                <?php $isWishlisted = in_array((int) $product['id'], $wishlistProductIds, true); ?>
                                <button
                                    type="button"
                                    class="trd__wishlist-btn<?= $isWishlisted ? ' is-active' : '' ?>"
                                    data-product-id="<?= (int) $product['id'] ?>"
                                    aria-pressed="<?= $isWishlisted ? 'true' : 'false' ?>"
                                    aria-label="<?= $isWishlisted ? 'Remove from wishlist' : 'Add to wishlist' ?>">
                                    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 21s-7.5-4.8-10.2-9.3C.3 8.9 1.4 5 5 4.1c2.2-.5 4.3.5 5.5 2.4l1.5 2.3 1.5-2.3C14.7 4.6 16.8 3.6 19 4.1c3.6.9 4.7 4.8 3.2 7.6C19.5 16.2 12 21 12 21z"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="trd__body">
                                <a href="<?= htmlspecialchars($productUrl, ENT_QUOTES, 'UTF-8') ?>" class="trd__title-link">
                                    <h3 class="trd__title"><?= htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                                </a>

                                <?php if ($reviewCount > 0): ?>
                                    <div class="trd__rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <svg class="<?= $i <= round($avgRating) ? 'is-filled' : '' ?>" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10 1.5l2.6 5.7 6.2.6-4.7 4.2 1.4 6.1L10 15.1l-5.5 3 1.4-6.1-4.7-4.2 6.2-.6z" />
                                            </svg>
                                        <?php endfor; ?>
                                        <a href="<?= htmlspecialchars($productUrl, ENT_QUOTES, 'UTF-8') ?>"><?= $reviewCount ?> review<?= $reviewCount === 1 ? '' : 's' ?></a>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($product['short_description'])): ?>
                                    <p class="trd__desc"><?= htmlspecialchars($product['short_description'], ENT_QUOTES, 'UTF-8') ?></p>
                                <?php endif; ?>

                                <?php if (!empty($product['variant_labels'])): ?>
                                    <div class="trd__pills">
                                        <?php foreach ($product['variant_labels'] as $label): ?>
                                            <span class="trd__pill"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="trd__price-row">
                                    <?php if ($hasSale): ?>
                                        <span class="trd__price">&#8377;<?= number_format($product['display_sale_price'], 0) ?></span>
                                        <span class="trd__price--full">&#8377;<?= number_format($product['display_price'], 0) ?></span>
                                        <span class="trd__discount"><?= $discountPercent ?>% off</span>
                                    <?php else: ?>
                                        <span class="trd__price">&#8377;<?= number_format($product['display_price'], 0) ?></span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($isOutOfStock): ?>
                                    <button type="button" class="trd__cta" disabled>Sold Out</button>
                                <?php else: ?>
                                    <form method="POST" action="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>cart/index.php">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                        <input type="hidden" name="variant_id" value="<?= $product['default_variant_id'] !== null ? (int) $product['default_variant_id'] : '' ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="trd__cta">Add to Cart</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="trd__dots" data-trd-dots hidden></div>
        </div>
    </section>

    <script>
        (function () {
            // Identical drag/dots behavior to producthighlight.php's script,
            // scoped to [data-trd] instead of [data-phl] — kept as a
            // separate copy (not a shared function) so this section stays
            // fully independent of the other homepage carousels.
            document.querySelectorAll('[data-trd]').forEach(function (root) {
                var viewport = root.querySelector('[data-trd-viewport]');
                var dotsWrap = root.querySelector('[data-trd-dots]');
                var items = Array.prototype.slice.call(viewport.children);
                var count = items.length;

                if (count === 0) {
                    return;
                }

                // --- Wishlist toggle ---
                var wishlistIsLoggedIn = <?php echo $isCustomerLoggedIn ? 'true' : 'false'; ?>;
                var wishlistCsrfToken = <?php echo json_encode($wishlistCsrfToken); ?>;
                var wishlistToggleUrl = <?php echo json_encode(BASE_URL . 'account/wishlist-toggle.php'); ?>;
                var wishlistLoginUrl = <?php echo json_encode(BASE_URL . 'account/login.php'); ?>;

                root.querySelectorAll('.trd__wishlist-btn').forEach(function (btn) {
                    btn.addEventListener('click', function (event) {
                        // The button sits next to the aria-hidden image link -
                        // stop the click from also triggering that link/drag layer.
                        event.preventDefault();
                        event.stopPropagation();

                        if (btn.classList.contains('is-loading')) return;

                        if (!wishlistIsLoggedIn) {
                            window.location.href = wishlistLoginUrl + '?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
                            return;
                        }

                        btn.classList.add('is-loading');

                        var fd = new FormData();
                        fd.append('product_id', btn.getAttribute('data-product-id'));
                        fd.append('csrf_token', wishlistCsrfToken);

                        fetch(wishlistToggleUrl, {
                                method: 'POST',
                                body: fd,
                                credentials: 'same-origin'
                            })
                            .then(function (res) {
                                return res.json();
                            })
                            .then(function (data) {
                                btn.classList.remove('is-loading');

                                if (data.ok) {
                                    btn.classList.toggle('is-active', data.in_wishlist);
                                    btn.setAttribute('aria-pressed', data.in_wishlist ? 'true' : 'false');
                                    btn.setAttribute('aria-label', data.in_wishlist ? 'Remove from wishlist' : 'Add to wishlist');
                                    document.dispatchEvent(new CustomEvent('wishlist:updated', {
                                        detail: {
                                            count: data.count
                                        }
                                    }));
                                } else if (data.error === 'login_required') {
                                    window.location.href = wishlistLoginUrl + '?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
                                }
                            })
                            .catch(function () {
                                btn.classList.remove('is-loading');
                            });
                    });
                });

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
                    var raw = getComputedStyle(viewport).getPropertyValue('--trd-visible');
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
                        dot.className = 'trd__dot';
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
                    var dots = dotsWrap.querySelectorAll('.trd__dot');
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