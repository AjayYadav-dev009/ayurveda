<?php

/**
 * Homepage "Trusted By" testimonials section.
 *
 * Pulls real, admin-approved (status = 'Active') reviews — there's no
 * separate "testimonials" table, and there doesn't need to be: the
 * `reviews` table plus the admin moderation pages in admin/reviews/ already
 * give admins full control over which reviews exist, what state they're
 * in, and (by approving/rejecting) which ones are eligible to show up
 * here. This section just displays the best of what's approved.
 *
 * Usage (from the homepage), after $conn is available:
 *
 *   require_once __DIR__ . '/function/review.php';
 *   include __DIR__ . '/partials/testimonials.php';
 */

if (!function_exists('getFeaturedReviews')) {
    require_once __DIR__ . '/function/review.php';
}

try {
    $testimonials = getFeaturedReviews($conn, 9);
} catch (Exception $e) {
    $testimonials = [];
}

$testimonialCount = count($testimonials);
?>

<style>
    /* ==========================================================================
       Testimonials — homepage section. Self-contained, namespaced "tst".
       Warm cream/tan palette to match the reference — not in global.css's
       (green-based) token set, so --tst-* below are local, one-off colors
       scoped to this section only, same approach as dosha-banner.php.
       ========================================================================== */

    .tst {
        --tst-bg: #f7efe1;
        --tst-card: #ecdcb8;
        --tst-card-highlight: #a97a45;
        --tst-text: #2c2418;
        --tst-text-light: #6b5d47;
        --tst-star: #c9a227;
        --tst-accent: #a97a45;

        padding: 60px 0;
        background: var(--tst-bg);
    }

    .tst__heading {
        text-align: center;
        font-size: 26px;
        font-weight: 600;
        line-height: 1.5;
        color: var(--tst-text);
        margin: 0 0 36px;
    }

    .tst__heading strong {
        color: var(--tst-accent);
        font-weight: 700;
    }

    /* ---- Scroll viewport (same native-scroll approach as
       shopbycategory.php / product-highlights.php) ---- */

    .tst__viewport {
        display: flex;
        gap: 20px;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        scroll-behavior: smooth;
        cursor: grab;
        padding: 6px 6px 10px;

        --tst-visible: 3;

        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .tst__viewport::-webkit-scrollbar {
        display: none;
    }

    .tst__viewport.is-dragging {
        cursor: grabbing;
        scroll-snap-type: none;
        scroll-behavior: auto;
    }

    .tst__item {
        flex: 0 0 calc((100% - (var(--tst-visible) - 1) * 20px) / var(--tst-visible));
        min-width: 0;
        scroll-snap-align: start;
    }

    @media (max-width: 860px) {
        .tst__viewport {
            --tst-visible: 2;
        }
    }

    @media (max-width: 560px) {
        .tst__viewport {
            --tst-visible: 1.08;
        }

        .tst__heading {
            font-size: 20px;
            margin-bottom: 24px;
        }
    }

    /* ---- Card ---- */

    .tst__card {
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 28px 22px;
        border-radius: var(--radius-lg);
        background: var(--tst-card);
        color: var(--tst-text);
        -webkit-user-drag: none;
        user-select: none;
        transition: background 0.35s ease, color 0.35s ease;
    }

    .tst__card.is-center {
        background: var(--tst-card-highlight);
        color: #fbf3e6;
    }

    .tst__name {
        font-size: 15px;
        font-weight: 700;
        margin: 0 0 8px;
    }

    .tst__stars {
        color: var(--tst-star);
        letter-spacing: 2px;
        margin-bottom: 14px;
        font-size: 13px;
    }

    .tst__card.is-center .tst__stars {
        color: #f0d27a;
    }

    .tst__text {
        font-size: 13.5px;
        line-height: 1.7;
        color: var(--tst-text-light);
        margin: 0;

        display: -webkit-box;
        -webkit-line-clamp: 5;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .tst__card.is-center .tst__text {
        color: #f0e6d4;
    }

    /* ---- Dots ---- */

    .tst__dots {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 26px;
    }

    .tst__dots[hidden] {
        display: none;
    }

    .tst__dot {
        width: 9px;
        height: 9px;
        padding: 0;
        border: none;
        border-radius: 50%;
        background: var(--tst-card);
        transition: background 0.3s ease, transform 0.3s ease;
    }

    .tst__dot.is-active {
        background: var(--tst-accent);
        transform: scale(1.25);
    }

    .tst__dot:focus-visible {
        outline: 2px solid var(--tst-accent);
        outline-offset: 2px;
    }
</style>

<?php if ($testimonialCount > 0): ?>
    <section class="tst" data-tst>
        <div class="container">
            <h2 class="tst__heading">
                Trusted By <strong>10 Lakh</strong> Customers<br>
                Across <strong>3600+</strong> Cities
            </h2>

            <div class="tst__viewport" data-tst-viewport>
                <?php foreach ($testimonials as $review):
                    $rating = max(1, min(5, (int) $review['rating']));
                ?>
                    <div class="tst__item">
                        <div class="tst__card">
                            <p class="tst__name"><?= htmlspecialchars($review['customer_name'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="tst__stars"><?= str_repeat('&#9733;', $rating) . str_repeat('&#9734;', 5 - $rating) ?></p>
                            <p class="tst__text"><?= htmlspecialchars($review['review'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="tst__dots" data-tst-dots hidden></div>
        </div>
    </section>

    <script>
        (function () {
            // Drag + dots: same pattern as shopbycategory.php / product-
            // highlights.php, scoped to [data-tst]. Adds one thing those
            // don't need: continuously tracking which card is nearest the
            // viewport's own center and marking it .is-center, so the
            // highlighted (dark) card always matches whichever testimonial
            // is actually front-and-center — including mid-drag.
            document.querySelectorAll('[data-tst]').forEach(function (root) {
                var viewport = root.querySelector('[data-tst-viewport]');
                var dotsWrap = root.querySelector('[data-tst-dots]');
                var items = Array.prototype.slice.call(viewport.children);
                var cards = items.map(function (item) {
                    return item.querySelector('.tst__card');
                });
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

                function updateCenterHighlight() {
                    var viewportRect = viewport.getBoundingClientRect();
                    var viewportCenter = viewportRect.left + viewportRect.width / 2;

                    var closestIndex = 0;
                    var closestDistance = Infinity;
                    items.forEach(function (item, i) {
                        var rect = item.getBoundingClientRect();
                        var itemCenter = rect.left + rect.width / 2;
                        var distance = Math.abs(itemCenter - viewportCenter);
                        if (distance < closestDistance) {
                            closestDistance = distance;
                            closestIndex = i;
                        }
                    });

                    cards.forEach(function (card, i) {
                        if (card) {
                            card.classList.toggle('is-center', i === closestIndex);
                        }
                    });
                }

                function pageCount() {
                    var raw = getComputedStyle(viewport).getPropertyValue('--tst-visible');
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
                        dot.className = 'tst__dot';
                        dot.setAttribute('aria-label', 'Go to reviews page ' + (i + 1));
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
                    var dots = dotsWrap.querySelectorAll('.tst__dot');
                    dots.forEach(function (dot, i) {
                        dot.classList.toggle('is-active', i === active);
                    });
                }

                var scrollTimer = null;
                viewport.addEventListener('scroll', function () {
                    updateCenterHighlight();
                    window.clearTimeout(scrollTimer);
                    scrollTimer = window.setTimeout(updateActiveDot, 80);
                });

                var resizeTimer = null;
                window.addEventListener('resize', function () {
                    window.clearTimeout(resizeTimer);
                    resizeTimer = window.setTimeout(function () {
                        buildDots();
                        updateCenterHighlight();
                    }, 150);
                });

                buildDots();
                updateCenterHighlight();
            });
        })();
    </script>
<?php endif; ?>