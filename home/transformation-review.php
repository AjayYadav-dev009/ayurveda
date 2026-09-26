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
        --trf-width: 340px;
        --trf-gap: 26px;
        --trf-radius: 22px;
        --trf-scale: 1.05;
        --trf-accent: var(--color-primary, #245c4f);
        --trf-shadow: 0 10px 24px rgba(20, 50, 40, 0.10);
        --trf-shadow-active: 0 26px 50px rgba(20, 50, 40, 0.18);

        position: relative;
        padding: 70px 0;
        overflow: hidden;
        max-width: 100%;
        background: radial-gradient(circle at 15% 20%, var(--color-primary-light) 0%, transparent 45%),
            radial-gradient(circle at 85% 80%, var(--color-primary-light) 0%, transparent 45%),
            var(--color-bg);
    }

    /* ---- Corner leaf decorations (purely CSS/SVG, no external assets) ---- */

    .trf__leaf {
        position: absolute;
        width: 130px;
        height: 130px;
        color: var(--color-accent);
        opacity: 0.35;
        pointer-events: none;
        z-index: 0;
    }

    .trf__leaf--tl {
        top: -10px;
        left: -20px;
        transform: rotate(-10deg);
    }

    .trf__leaf--br {
        bottom: -10px;
        right: -20px;
        transform: rotate(170deg);
    }

    @media (max-width: 720px) {
        .trf__leaf {
            width: 80px;
            height: 80px;
        }
    }

    /* ---- Head ---- */

    .trf__head {
        position: relative;
        z-index: 1;
        max-width: 640px;
        margin: 0 auto 46px;
        text-align: center;
    }

    .trf__brandmark {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 14px;
        margin-bottom: 14px;
    }

    .trf__brandline {
        width: 60px;
        height: 1px;
        background: var(--color-border);
    }

    .trf__brandicon {
        width: 20px;
        height: 20px;
        color: var(--trf-accent);
        flex: 0 0 auto;
    }

    .trf__eyebrow {
        display: block;
        margin-bottom: 14px;
        font-size: 12.5px;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--color-text-light);
    }

    .trf__eyebrow strong {
        color: var(--color-text);
        font-weight: 700;
    }

    .trf__heading {
        margin: 0 0 12px;
        font-size: 42px;
        font-weight: 700;
        letter-spacing: -0.01em;
        color: var(--trf-accent);
    }

    .trf__subheading {
        margin: 0 0 8px;
        font-size: 15px;
        line-height: 1.6;
        color: var(--color-text-light);
    }

    .trf__disclaimer {
        margin: 0;
        font-size: 11.5px;
        line-height: 1.5;
        color: var(--color-text-light);
        opacity: 0.75;
    }

    @media (max-width: 720px) {
        .trf__heading {
            font-size: 30px;
        }
    }

    /* ---- Stage: nav buttons + viewport side by side ---- */

    .trf__stage {
        position: relative;
        z-index: 1;
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
        scroll-snap-type: x mandatory;
        scroll-behavior: smooth;
        touch-action: pan-x;
        -webkit-overflow-scrolling: touch;
        padding: 18px 0 34px;

        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .trf__viewport::-webkit-scrollbar {
        display: none;
    }

    .trf__viewport:focus-visible {
        outline: 2px solid var(--trf-accent);
        outline-offset: 4px;
        border-radius: var(--radius-md);
    }

    .trf__track {
        display: flex;
        align-items: flex-start;
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
        border-radius: var(--trf-radius);
        box-shadow: var(--trf-shadow);
        padding: 14px 14px 22px;
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

    /* ---- Split before/after photo ---- */

    .trf__poster {
        display: flex;
        gap: 4px;
        height: 280px;
        border-radius: 14px;
        overflow: hidden;
        text-decoration: none;
        color: inherit;
    }

    .trf__photo {
        position: relative;
        flex: 1 1 50%;
        min-width: 0;
        overflow: hidden;
    }

    .trf__photo img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: top center;
    }

    .trf__badge {
        position: absolute;
        left: 10px;
        bottom: 10px;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.03em;
        white-space: nowrap;
        color: #fff;
    }

    .trf__badge--before {
        background: rgba(40, 46, 42, 0.72);
    }

    .trf__badge--after {
        background: var(--trf-accent);
    }

    /* ---- Card body ---- */

    .trf__body {
        display: flex;
        flex-direction: column;
        padding: 20px 8px 0;
    }

    .trf__nameRow {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px 10px;
        margin-bottom: 8px;
    }

    .trf__name {
        margin: 0;
        font-size: 19px;
        font-weight: 700;
        color: var(--trf-accent);
    }

    .trf__verified {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 999px;
        background: var(--color-primary-light);
        color: var(--trf-accent);
        font-size: 10.5px;
        font-weight: 700;
        white-space: nowrap;
    }

    .trf__verified svg {
        width: 11px;
        height: 11px;
    }

    .trf__desc {
        margin: 0 0 18px;
        font-size: 13.5px;
        line-height: 1.65;
        color: var(--color-text-light);

        display: -webkit-box;
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .trf__meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 14px;
        font-size: 12px;
    }

    .trf__product {
        font-weight: 600;
        color: var(--color-text);
        text-decoration: none;
    }

    a.trf__product:hover {
        color: var(--trf-accent);
        text-decoration: underline;
    }

    .trf__metaDot {
        color: var(--color-text-light);
    }

    .trf__duration {
        color: var(--color-text-light);
    }

    /* ---- Footer row: accent underline + circular arrow CTA ---- */

    .trf__footerRow {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .trf__underline {
        width: 34px;
        height: 3px;
        border-radius: 999px;
        background: var(--trf-accent);
    }

    .trf__cta {
        flex: 0 0 auto;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 1px solid var(--color-border);
        background: var(--color-white);
        color: var(--trf-accent);
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: background 0.15s ease, color 0.15s ease, transform 0.1s ease;
    }

    .trf__cta svg {
        width: 16px;
        height: 16px;
    }

    .trf__cta:hover {
        background: var(--trf-accent);
        color: #fff;
    }

    .trf__cta:active {
        transform: scale(0.94);
    }

    .trf__cta:focus-visible {
        outline: 2px solid var(--trf-accent);
        outline-offset: 2px;
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
        background: var(--trf-accent);
        border-color: var(--trf-accent);
        color: #fff;
    }

    .trf__nav:active {
        transform: scale(0.92);
    }

    .trf__nav:focus-visible {
        outline: 2px solid var(--trf-accent);
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
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 6px;
    }

    .trf__dot {
        width: 8px;
        height: 8px;
        padding: 0;
        border: none;
        border-radius: 999px;
        background: var(--color-border);
        cursor: pointer;
        transition: width 0.2s ease, background 0.2s ease;
    }

    .trf__dot:hover {
        background: var(--trf-accent);
    }

    .trf__dot:focus-visible {
        outline: 2px solid var(--trf-accent);
        outline-offset: 2px;
    }

    .trf__dot.is-active {
        width: 22px;
        background: var(--trf-accent);
    }

    /* ---- Responsive ---- */

    @media (max-width: 1080px) {
        .trf {
            --trf-width: 300px;
        }

        .trf__poster {
            height: 240px;
        }
    }

    /* Phones: one card, fully visible.
       The side arrow buttons used to squeeze the viewport so the card was
       wider than the space left for it (that's what cut it off). They are
       hidden here; swipe and the dots below do the navigating, and the
       card is sized from the actual stage width so it always fits. */

    @media (max-width: 720px) {
        .trf {
            --trf-width: min(calc(100vw - 56px), 340px);
            --trf-gap: 14px;
            --trf-scale: 1;
            padding: 52px 0 56px;
        }

        @supports (width: 1cqw) {
            .trf {
                --trf-width: min(calc(100cqw - 8px), 360px);
            }
        }

        .trf__stage {
            display: block;
            container-type: inline-size;
        }

        .trf__nav {
            display: none;
        }

        .trf__surface {
            padding: 12px 12px 20px;
        }

        .trf__poster {
            height: 270px;
        }

        .trf__body {
            padding: 18px 6px 0;
        }
    }

    @media (max-width: 380px) {
        .trf__poster {
            height: 240px;
        }
    }
</style>

<?php if ($transformationCount > 0): ?>
    <section class="trf" data-trf aria-label="Customer transformations">
        <svg class="trf__leaf trf__leaf--tl" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
            <path d="M50 90C20 80 10 50 20 20c25 5 45 25 40 55-15-5-25-20-25-40" />
            <path d="M50 90C50 60 60 35 85 20" />
        </svg>
        <svg class="trf__leaf trf__leaf--br" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
            <path d="M50 90C20 80 10 50 20 20c25 5 45 25 40 55-15-5-25-20-25-40" />
            <path d="M50 90C50 60 60 35 85 20" />
        </svg>

        <div class="container">
            <div class="trf__head">
                <div class="trf__brandmark">
                    <span class="trf__brandline" aria-hidden="true"></span>
                    <svg class="trf__brandicon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 3c5 2 7 6 7 11-5 1-9-1-11-5-2 3-2 7 0 10" />
                    </svg>
                    <span class="trf__brandline" aria-hidden="true"></span>
                </div>

                <span class="trf__eyebrow">REAL <strong>PEOPLE</strong> &bull; REAL JOURNEYS &bull; NATURAL <strong>SUPPORT</strong></span>
                <h2 class="trf__heading">Transformation Journeys</h2>
                <p class="trf__subheading">Discover how small, consistent changes can make a big difference in your health and well-being.</p>
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

                            // Where the circular CTA links to: a dedicated detail
                            // page/slug if one exists, otherwise the linked product.
                            $detailUrl = trim((string) ($t['detail_url'] ?? $t['url'] ?? ''));
                            $safeDetailUrl = htmlspecialchars($detailUrl !== '' ? $detailUrl : ($safeProductUrl !== '' ? $productUrl : '#'), ENT_QUOTES, 'UTF-8');

                            // Optional per-record label overrides; plain
                            // "Before"/"After" otherwise, matching the design.
                            $beforeLabel = trim((string) ($t['before_label'] ?? ''));
                            $safeBeforeLabel = htmlspecialchars($beforeLabel !== '' ? $beforeLabel : 'Before', ENT_QUOTES, 'UTF-8');
                            $afterLabel = trim((string) ($t['after_label'] ?? ''));
                            $safeAfterLabel = htmlspecialchars($afterLabel !== '' ? $afterLabel : 'After', ENT_QUOTES, 'UTF-8');

                            // The badge is gated strictly on this flag — never
                            // shown just because other fields are present.
                            $isVerified = !empty($t['is_verified']);
                        ?>
                            <div class="trf__card" data-trf-card role="group" aria-roledescription="slide" aria-label="Transformation story: <?= $safeName ?>">
                                <div class="trf__surface">
                                    <a class="trf__poster" href="<?= $safeDetailUrl ?>" aria-label="View <?= $safeName ?>'s transformation">
                                        <div class="trf__photo">
                                            <img src="<?= $beforeUrl ?>" alt="<?= $safeName ?> before" loading="lazy" />
                                            <span class="trf__badge trf__badge--before"><?= $safeBeforeLabel ?></span>
                                        </div>
                                        <div class="trf__photo">
                                            <img src="<?= $afterUrl ?>" alt="<?= $safeName ?> after" loading="lazy" />
                                            <span class="trf__badge trf__badge--after"><?= $safeAfterLabel ?></span>
                                        </div>
                                    </a>

                                    <div class="trf__body">
                                        <div class="trf__nameRow">
                                            <h3 class="trf__name"><?= $safeName ?></h3>
                                            <?php if ($isVerified): ?>
                                                <span class="trf__verified">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M20 6L9 17l-5-5" />
                                                    </svg>
                                                    Verified
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <?php //if ($safeDescription !== ''): ?>
                                            <!-- <p class="trf__desc"><?= $safeDescription ?></p> -->
                                        <?php //endif; ?>

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

                                        <div class="trf__footerRow">
                                            <span class="trf__underline" aria-hidden="true"></span>
                                            <a class="trf__cta" href="<?= $safeDetailUrl ?>" aria-label="View <?= $safeName ?>'s transformation">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M5 12h14M13 6l6 6-6 6" />
                                                </svg>
                                            </a>
                                        </div>
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
                var pad = Math.max(0, Math.round((viewport.clientWidth - cards[0].offsetWidth) / 2));
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