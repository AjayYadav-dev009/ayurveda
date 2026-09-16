<?php

if (!isset($transformations) || !is_array($transformations)) {
    if (!function_exists('getFeaturedTransformations')) {
        require_once __DIR__ . '/../function/transformation.php';
    }
    try {
        $transformations = getFeaturedTransformations($conn, 12);
    } catch (Exception $e) {
        $transformations = [];
    }
}

$transformationCount = count($transformations);
?>

<style>

    .trf {
        --trf-width: 220px;
        --trf-gap: 20px;
        --trf-radius: var(--radius-lg);
        --trf-radius-sm: var(--radius-md);
        --trf-scale: 1.04;
        --trf-shadow: 0 8px 20px rgba(23, 72, 61, 0.10);
        --trf-shadow-active: 0 22px 40px rgba(23, 72, 61, 0.22);

        padding: 60px 0;
        background: var(--color-bg);
        overflow-x: hidden;
        max-width: 100%;
    }

    .trf__head {
        max-width: 640px;
        margin: 0 auto 30px;
        text-align: center;
    }

    .trf__eyebrow {
        display: inline-block;
        margin-bottom: 10px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--color-primary);
    }

    .trf__heading {
        margin: 0 0 10px;
        font-size: 30px;
        font-weight: 700;
        color: var(--color-text);
    }

    .trf__subheading {
        margin: 0 0 10px;
        font-size: 14.5px;
        line-height: 1.6;
        color: var(--color-text-light);
    }

    .trf__disclaimer {
        margin: 0;
        font-size: 12px;
        line-height: 1.5;
        color: var(--color-text-light);
        opacity: 0.85;
    }

    /* ---- Stage: nav buttons + viewport side by side ---- */

    .trf__stage {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .trf__viewport {
        flex: 1;
        min-width: 0;
        max-width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        overscroll-behavior-x: contain;
        /* stops drag/swipe momentum from
        "chaining" into the page once the carousel hits its start/end —
        without this, dragging past an edge can rubber-band/scroll the
        whole page instead of just stopping at the last card */
        scroll-snap-type: x mandatory;
        scroll-behavior: smooth;
        touch-action: pan-x;
        cursor: grab;
        -webkit-overflow-scrolling: touch;
        padding: 14px 0 30px;

        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .trf__viewport::-webkit-scrollbar {
        display: none;
    }

    .trf__viewport:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 4px;
        border-radius: var(--radius-md);
    }

    .trf__viewport.is-dragging {
        cursor: grabbing;
        scroll-snap-type: none;
        scroll-behavior: auto;
    }


    .trf__track {
        display: flex;
        gap: var(--trf-gap);
        padding-inline: calc((100% - var(--trf-width)) / 2);
        max-width: 100%;
    }

    .trf__card {
        flex: 0 0 var(--trf-width);
        width: var(--trf-width);
        scroll-snap-align: center;
    }

    .trf__surface {
        display: flex;
        flex-direction: column;
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--trf-radius);
        box-shadow: var(--trf-shadow);
        overflow: hidden;
        transform: scale(1);
        transition: transform 0.35s ease, box-shadow 0.35s ease;
        -webkit-user-drag: none;
        user-select: none;
    }

    .trf__card.is-active .trf__surface {
        transform: scale(var(--trf-scale));
        box-shadow: var(--trf-shadow-active);
        z-index: 2;
    }

    /* ---- Before/After image pair: one bordered block so the two images
   read as a single connected pair, not two separate photos. ---- */

    .trf__images {
        position: relative;
        display: flex;
        align-items: stretch;
        background: var(--color-primary-light);
    }

    .trf__image {
        position: relative;
        flex: 1 1 50%;
        aspect-ratio: 4 / 5;
        overflow: hidden;
    }

    .trf__image img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .trf__surface:hover .trf__image img {
        transform: scale(1.04);
    }

    .trf__label {
        position: absolute;
        top: 8px;
        padding: 3px 9px;
        border-radius: 999px;
        background: rgba(11, 15, 13, 0.55);
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        pointer-events: none;
    }

    .trf__label--before {
        left: 8px;
    }

    .trf__label--after {
        right: 8px;
    }

    /* ---- Divider / connector between the two images, so the pair reads
   as one transformation rather than two unrelated photos. ---- */

    .trf__divider {
        flex: 0 0 auto;
        align-self: center;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        margin-inline: -13px;
        border-radius: 50%;
        background: var(--color-white);
        color: var(--color-primary);
        box-shadow: 0 2px 6px rgba(23, 72, 61, 0.25);
        z-index: 1;
    }

    .trf__divider svg {
        width: 14px;
        height: 14px;
    }

    /* ---- Card body ---- */

    .trf__body {
        padding: 14px 16px 16px;
    }

    .trf__nameRow {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px 10px;
        margin-bottom: 6px;
    }

    .trf__name {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        color: var(--color-text);
    }

    .trf__verified {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 999px;
        background: var(--color-primary-light);
        color: var(--color-primary);
        font-size: 10.5px;
        font-weight: 700;
        white-space: nowrap;
    }

    .trf__verified svg {
        width: 11px;
        height: 11px;
    }

    .trf__desc {
        margin: 0 0 10px;
        font-size: 13px;
        line-height: 1.55;
        color: var(--color-text-light);

        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .trf__meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        font-size: 12px;
    }

    .trf__product {
        font-weight: 600;
        color: var(--color-text);
        text-decoration: none;
    }

    a.trf__product:hover {
        color: var(--color-primary);
        text-decoration: underline;
    }

    .trf__metaDot {
        color: var(--color-text-light);
    }

    .trf__duration {
        color: var(--color-text-light);
    }

    /* ---- Nav buttons ---- */

    .trf__nav {
        flex: 0 0 auto;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        border: 1px solid var(--color-border);
        background: var(--color-white);
        color: var(--color-text);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: var(--shadow-soft);
        transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease, transform 0.1s ease;
    }

    .trf__nav svg {
        width: 18px;
        height: 18px;
    }

    .trf__nav:hover {
        background: var(--color-primary);
        border-color: var(--color-primary);
        color: var(--color-white);
    }

    .trf__nav:active {
        transform: scale(0.92);
    }

    .trf__nav:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 2px;
    }

    .trf__nav:disabled {
        opacity: 0.35;
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }

    /* ---- Pagination dots ---- */

    .trf__dots {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 6px;
    }

    .trf__dot {
        width: 7px;
        height: 7px;
        padding: 0;
        border: none;
        border-radius: 999px;
        background: var(--color-border);
        cursor: pointer;
        transition: width 0.2s ease, background 0.2s ease;
    }

    .trf__dot:hover {
        background: var(--color-primary-light);
    }

    .trf__dot:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 2px;
    }

    .trf__dot.is-active {
        width: 20px;
        background: var(--color-primary);
    }

    /* ---- Responsive: ~5 desktop / ~3 tablet / ~1(+peek) mobile ---- */

    @media (max-width: 1080px) {
        .trf {
            --trf-width: 250px;
        }
    }

    @media (max-width: 720px) {
        .trf {
            --trf-width: min(72vw, 300px);
            --trf-gap: 14px;
        }

        .trf__heading {
            font-size: 22px;
        }

        .trf__stage {
            gap: 8px;
        }

        .trf__nav {
            width: 38px;
            height: 38px;
        }
    }

    @media (max-width: 420px) {
        .trf {
            --trf-width: min(80vw, 300px);
        }

        .trf__nav {
            width: 34px;
            height: 34px;
        }
    }
</style>

<?php if ($transformationCount > 0): ?>
    <section class="trf" data-trf aria-label="Customer transformations">
        <div class="container">
            <div class="trf__head">
                <span class="trf__eyebrow">Real Stories. Real Journeys.</span>
                <h2 class="trf__heading">Customer Transformations</h2>
                <p class="trf__subheading">Experiences shared by real customers, in their own words.</p>
                <p class="trf__disclaimer">Results are individual to each customer and can vary based on body type, routine, and consistency. Shared with the customer's permission.</p>
            </div>

            <div class="trf__stage">
                <button type="button" class="trf__nav trf__nav--prev" data-trf-prev aria-label="Previous transformation">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6" />
                    </svg>
                </button>

                <div class="trf__viewport" data-trf-viewport tabindex="0" role="region" aria-roledescription="carousel" aria-label="Customer transformations carousel — use the arrow buttons or left/right arrow keys to browse">
                    <div class="trf__track" data-trf-track>
                        <?php foreach ($transformations as $t):
                            $beforeUrl = htmlspecialchars(BASE_URL . ltrim((string) ($t['before_image'] ?? ''), '/'), ENT_QUOTES, 'UTF-8');
                            $afterUrl  = htmlspecialchars(BASE_URL . ltrim((string) ($t['after_image'] ?? ''), '/'), ENT_QUOTES, 'UTF-8');

                            $name = trim((string) ($t['customer_name'] ?? ''));
                            $safeName = htmlspecialchars($name !== '' ? $name : 'Customer', ENT_QUOTES, 'UTF-8');

                            $description = trim((string) ($t['description'] ?? ''));
                            $safeDescription = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');

                            $productName = trim((string) ($t['product_name'] ?? ''));
                            $safeProductName = htmlspecialchars($productName, ENT_QUOTES, 'UTF-8');
                            $productUrl = trim((string) ($t['product_url'] ?? ''));
                            $safeProductUrl = $productUrl !== '' ? htmlspecialchars($productUrl, ENT_QUOTES, 'UTF-8') : '';

                            $duration = trim((string) ($t['duration'] ?? ''));
                            $safeDuration = htmlspecialchars($duration, ENT_QUOTES, 'UTF-8');

                            // The badge is gated strictly on this flag — never
                            // shown just because other fields are present.
                            $isVerified = !empty($t['is_verified']);
                        ?>
                            <div class="trf__card" data-trf-card role="group" aria-roledescription="slide" aria-label="Transformation story: <?= $safeName ?>">
                                <div class="trf__surface">
                                    <div class="trf__images">
                                        <div class="trf__image trf__image--before">
                                            <img src="<?= $beforeUrl ?>" alt="<?= $safeName ?> before" loading="lazy" />
                                            <span class="trf__label trf__label--before">Before</span>
                                        </div>

                                        <span class="trf__divider" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M5 12h13" />
                                                <path d="M13 6l6 6-6 6" />
                                            </svg>
                                        </span>

                                        <div class="trf__image trf__image--after">
                                            <img src="<?= $afterUrl ?>" alt="<?= $safeName ?> after" loading="lazy" />
                                            <span class="trf__label trf__label--after">After</span>
                                        </div>
                                    </div>

                                    <div class="trf__body">
                                        <div class="trf__nameRow">
                                            <h3 class="trf__name"><?= $safeName ?></h3>
                                            <?php if ($isVerified): ?>
                                                <span class="trf__verified">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M20 6L9 17l-5-5" />
                                                    </svg>
                                                    Verified Customer
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ($safeDescription !== ''): ?>
                                            <p class="trf__desc">&ldquo;<?= $safeDescription ?>&rdquo;</p>
                                        <?php endif; ?>

                                        <?php if ($safeProductName !== '' || $safeDuration !== ''): ?>
                                            <div class="trf__meta">
                                                <?php if ($safeProductName !== ''): ?>
                                                    <?php if ($safeProductUrl !== ''): ?>
                                                        <a class="trf__product" href="<?= $safeProductUrl ?>"><?= $safeProductName ?></a>
                                                    <?php else: ?>
                                                        <span class="trf__product"><?= $safeProductName ?></span>
                                                    <?php endif; ?>
                                                <?php endif; ?>

                                                <?php if ($safeProductName !== '' && $safeDuration !== ''): ?>
                                                    <span class="trf__metaDot" aria-hidden="true">&bull;</span>
                                                <?php endif; ?>

                                                <?php if ($safeDuration !== ''): ?>
                                                    <span class="trf__duration"><?= $safeDuration ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="button" class="trf__nav trf__nav--next" data-trf-next aria-label="Next transformation">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 6l6 6-6 6" />
                    </svg>
                </button>
            </div>

            <?php if ($transformationCount > 1): ?>
                <div class="trf__dots" data-trf-dots role="tablist" aria-label="Choose a transformation to view">
                    <?php for ($i = 0; $i < $transformationCount; $i++): ?>
                        <button type="button" class="trf__dot" data-trf-dot role="tab" aria-label="Go to transformation <?= $i + 1 ?>"></button>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<script>
    (function() {
        'use strict';

        document.querySelectorAll('[data-trf]').forEach(function(root) {
            var viewport = root.querySelector('[data-trf-viewport]');
            var track = root.querySelector('[data-trf-track]');
            var prevBtn = root.querySelector('[data-trf-prev]');
            var nextBtn = root.querySelector('[data-trf-next]');
            var dotsWrap = root.querySelector('[data-trf-dots]');
            var dots = dotsWrap ? Array.prototype.slice.call(dotsWrap.querySelectorAll('[data-trf-dot]')) : [];
            var cards = track ? Array.prototype.slice.call(track.children) : [];

            if (!viewport || !track || cards.length === 0) {
                return;
            }

            function setActiveIndex(index) {
                cards.forEach(function(card, i) {
                    card.classList.toggle('is-active', i === index);
                });

                if (dots.length) {
                    dots.forEach(function(dot, i) {
                        var isActive = i === index;
                        dot.classList.toggle('is-active', isActive);
                        dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
                    });
                }

                if (prevBtn) prevBtn.disabled = index === 0;
                if (nextBtn) nextBtn.disabled = index === cards.length - 1;
            }

            function updateActiveCard() {
                var viewportRect = viewport.getBoundingClientRect();
                var viewportCenter = viewportRect.left + viewportRect.width / 2;

                var closestIndex = 0;
                var closestDist = Infinity;
                cards.forEach(function(card, index) {
                    var r = card.getBoundingClientRect();
                    var cardCenter = r.left + r.width / 2;
                    var dist = Math.abs(cardCenter - viewportCenter);
                    if (dist < closestDist) {
                        closestDist = dist;
                        closestIndex = index;
                    }
                });

                setActiveIndex(closestIndex);
            }

            function centerCard(card, smooth) {
                var target = card.offsetLeft + card.offsetWidth / 2 - viewport.clientWidth / 2;
                viewport.scrollTo({
                    left: target,
                    behavior: smooth ? 'smooth' : 'auto'
                });
            }

            function updatePadding() {
                var pad = Math.max(20, Math.round((viewport.clientWidth - cards[0].offsetWidth) / 2));
                track.style.paddingInline = pad + 'px';
            }

            function step(dir) {
                var active = track.querySelector('.trf__card.is-active') || cards[0];
                var idx = cards.indexOf(active);
                var target = cards[Math.min(cards.length - 1, Math.max(0, idx + dir))];
                centerCard(target, true);
            }

            if (prevBtn) prevBtn.addEventListener('click', function() {
                step(-1);
            });
            if (nextBtn) nextBtn.addEventListener('click', function() {
                step(1);
            });

            // ---- Keyboard: left/right arrows when the carousel has focus ----
            viewport.addEventListener('keydown', function(event) {
                if (event.key === 'ArrowRight') {
                    event.preventDefault();
                    step(1);
                } else if (event.key === 'ArrowLeft') {
                    event.preventDefault();
                    step(-1);
                }
            });

            // ---- Pagination dots ----
            dots.forEach(function(dot, index) {
                dot.addEventListener('click', function() {
                    var target = cards[index];
                    if (target) centerCard(target, true);
                });
            });

            // ---- Mouse drag (touch/swipe is native — overflow-x + touch-action
            // handle that without any JS at all) ----
            var isDown = false;
            var dragged = false;
            var startX = 0;
            var startScroll = 0;

            viewport.addEventListener('mousedown', function(event) {
                isDown = true;
                dragged = false;
                viewport.classList.add('is-dragging');
                startX = event.pageX;
                startScroll = viewport.scrollLeft;
            });

            window.addEventListener('mousemove', function(event) {
                if (!isDown) return;
                var delta = event.pageX - startX;
                if (Math.abs(delta) > 4) dragged = true;
                viewport.scrollLeft = startScroll - delta;
            });

            function endDrag() {
                if (!isDown) return;
                isDown = false;
                viewport.classList.remove('is-dragging');
                // Let native scroll-snap glide to the nearest card, then sync state.
                window.setTimeout(updateActiveCard, 60);
            }

            window.addEventListener('mouseup', endDrag);
            viewport.addEventListener('mouseleave', function() {
                if (isDown) endDrag();
            });

            // Dragging a card shouldn't trigger a click-through on links inside
            // it (e.g. the product link) right after releasing.
            track.addEventListener('click', function(event) {
                if (dragged) {
                    event.preventDefault();
                    dragged = false;
                }
            });

            // ---- Scroll -> keep active-card state in sync, live while
            // dragging/swiping and once more after it settles. ----
            var scrollSettleTimer = null;
            viewport.addEventListener('scroll', function() {
                updateActiveCard();
                window.clearTimeout(scrollSettleTimer);
                scrollSettleTimer = window.setTimeout(updateActiveCard, 120);
            }, {
                passive: true
            });

            // ---- Resize / breakpoint change: --trf-width changes at each
            // breakpoint, which can leave the old scroll position off-center.
            // Re-center whichever card was active, instantly (no animation). ----
            var resizeTimer = null;
            window.addEventListener('resize', function() {
                window.clearTimeout(resizeTimer);
                resizeTimer = window.setTimeout(function() {
                    updatePadding();
                    var active = track.querySelector('.trf__card.is-active') || cards[0];
                    centerCard(active, false);
                    updateActiveCard();
                }, 150);
            });

            var initialIndex = Math.floor((cards.length - 1) / 2);

            function init() {
                updatePadding();
                centerCard(cards[initialIndex], false);
                setActiveIndex(initialIndex);
                window.setTimeout(updateActiveCard, 100);
            }
            window.requestAnimationFrame(function() {
                window.requestAnimationFrame(init);
            });
        });
    })();
</script>