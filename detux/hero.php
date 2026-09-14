<?php

/**
 * detux/hero.php
 *
 * Included from detux/index.php, which already requires
 * config/config.php, config/database.php ($conn) and includes/header.php
 * before this file, and includes/footer.php after. Do NOT re-require
 * those here — that's what caused conflicts.
 *
 * Gallery images come from Banner Management (function/banner.php),
 * position = 'detox_gallery'. First active banner (lowest sort_order)
 * is the hero image, the rest fill the thumbnail strip.
 */

require_once __DIR__ . '/../function/banner.php';

// -----------------------------------------------------------------------
// Gallery images
// -----------------------------------------------------------------------
$detoxBanners = getActiveBanners($conn, 'detox_gallery');

$galleryImages = array_map(static function ($banner) {
    return [
        'url' => getBannerImageUrl($banner['image']),
        'alt' => $banner['title'] !== null && $banner['title'] !== ''
            ? $banner['title']
            : 'Complete Gut Detox',
    ];
}, $detoxBanners);

$heroImage       = $galleryImages[0] ?? null;
$thumbnailImages = array_slice($galleryImages, 1);

// -----------------------------------------------------------------------
// Page copy & pricing (hardcoded)
// -----------------------------------------------------------------------
$pageBadge    = '10-Day Programme';
$pageTitle    = 'Complete Gut Detox';
$pageSubtitle = 'Reset in just 10 days';

$rating      = 4.2;
$reviewCount = 40;

$description = 'A 10-day, do-at-home Ayurvedic Gut Reset that restores your digestive '
    . 'fire (Agni), clears Ama (toxins left behind by weak digestion), and rebuilds '
    . 'core vitality (Ojas). Guided daily routines, authentic herbal formulations, '
    . 'and three Vaidya consultations — all fit to your life and your body\'s '
    . 'specific needs.';

// Each item: label, optional detail line, "was" price, and "price"
// (null price = shown as "Free").
$inclusions = [
    [
        'label'  => '30 Days Ayurvedic Gut Wellness Kit',
        'detail' => 'Organic Gut Health, Livomap, Ama Cleanse, Pure Cleanse Infusion',
        'was'    => 5499,
        'price'  => 2599,
    ],
    [
        'label'  => '3 Doctor Consultations',
        'detail' => null,
        'was'    => 1900,
        'price'  => 1400,
    ],
    [
        'label'  => 'Dedicated Wellness Counsellor',
        'detail' => null,
        'was'    => 1999,
        'price'  => null,
    ],
    [
        'label'  => '10-Day Video Course',
        'detail' => null,
        'was'    => 999,
        'price'  => null,
    ],
    [
        'label'  => 'Signature MA Tea Cup',
        'detail' => null,
        'was'    => 999,
        'price'  => null,
    ],
    [
        'label'  => '10 Day Habit Building Planner',
        'detail' => null,
        'was'    => 799,
        'price'  => null,
    ],
];

$totalWas = array_sum(array_column($inclusions, 'was'));
$totalNow = 3999;
$youSaved = $totalWas - $totalNow;

/**
 * Render a star rating as filled / empty unicode stars.
 */
if (!function_exists('detoxRenderStars')) {
    function detoxRenderStars(float $rating, int $max = 5): string
    {
        $full = (int) floor($rating);
        $full = max(0, min($max, $full));
        $empty = $max - $full;

        return str_repeat('&#9733;', $full) . str_repeat('&#9734;', $empty);
    }
}
?>

<style>
    .detox-hero {
        padding: 48px 0 64px;
    }

    .detox-hero__grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 48px;
        align-items: start;
    }

    .detox-gallery__main {
        border-radius: var(--radius-lg);
        overflow: hidden;
        background: var(--color-primary-light);
        aspect-ratio: 4 / 5;
    }

    .detox-gallery__main img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .detox-gallery__placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 24px;
        color: var(--color-text-light);
        font-size: 14px;
    }

    .detox-gallery__thumbs {
        display: flex;
        gap: 12px;
        margin-top: 12px;
        overflow-x: auto;
        padding-bottom: 4px;
    }

    .detox-gallery__thumb {
        flex: 0 0 auto;
        width: 76px;
        height: 76px;
        padding: 0;
        border-radius: var(--radius-sm);
        border: 2px solid transparent;
        overflow: hidden;
        background: var(--color-primary-light);
    }

    .detox-gallery__thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .detox-gallery__thumb.is-active {
        border-color: var(--color-primary);
    }

    .detox-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 999px;
        background: var(--color-primary-light);
        color: var(--color-primary-dark);
        font-size: 13px;
        font-weight: 600;
    }

    .detox-title {
        margin-top: 16px;
        font-size: 34px;
        line-height: 1.15;
        color: var(--color-text);
    }

    .detox-subtitle {
        margin-top: 6px;
        font-size: 18px;
        color: var(--color-accent);
        font-weight: 600;
    }

    .detox-rating {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 14px;
        font-size: 14px;
        color: var(--color-text-light);
    }

    .detox-rating__stars {
        color: var(--color-accent);
        font-size: 16px;
        letter-spacing: 1px;
    }

    .detox-rating__value {
        color: var(--color-text);
        font-weight: 600;
    }

    .detox-rating__link {
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    .detox-description {
        margin-top: 18px;
        font-size: 15px;
        line-height: 1.6;
        color: var(--color-text-light);
        max-width: 60ch;
    }

    .detox-inclusions {
        margin-top: 24px;
        border-top: 1px solid var(--color-border);
    }

    .detox-inclusions__item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 0;
        border-bottom: 1px solid var(--color-border);
    }

    .detox-inclusions__number {
        flex: 0 0 auto;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--color-primary);
        color: var(--color-white);
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .detox-inclusions__text {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .detox-inclusions__label {
        font-size: 15px;
        font-weight: 600;
        color: var(--color-text);
    }

    .detox-inclusions__detail {
        font-size: 13px;
        color: var(--color-text-light);
    }

    .detox-inclusions__price {
        flex: 0 0 auto;
        display: flex;
        align-items: baseline;
        gap: 8px;
        white-space: nowrap;
    }

    .detox-inclusions__was {
        font-size: 13px;
        color: var(--color-text-light);
        text-decoration: line-through;
    }

    .detox-inclusions__now {
        font-size: 15px;
        font-weight: 600;
        color: var(--color-text);
    }

    .detox-inclusions__free {
        font-size: 15px;
        font-weight: 600;
        color: var(--color-accent);
    }

    .detox-total {
        margin-top: 20px;
        padding-top: 16px;
    }

    .detox-total__label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        font-size: 14px;
        font-weight: 600;
        color: var(--color-text);
    }

    .detox-total__saved {
        color: var(--color-accent);
        font-weight: 600;
    }

    .detox-total__price {
        display: flex;
        align-items: baseline;
        gap: 10px;
        margin-top: 6px;
    }

    .detox-total__was {
        font-size: 15px;
        color: var(--color-text-light);
        text-decoration: line-through;
    }

    .detox-total__now {
        font-size: 26px;
        font-weight: 700;
        color: var(--color-text);
    }

    .detox-total__mrp {
        margin-top: 2px;
        font-size: 12px;
        color: var(--color-text-light);
    }

    .detox-actions {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }

    .detox-btn {
        flex: 1 1 0;
        padding: 14px 20px;
        border-radius: var(--radius-sm);
        border: 1px solid transparent;
        font-size: 15px;
        font-weight: 600;
    }

    .detox-btn--primary {
        background: var(--color-primary);
        color: var(--color-white);
    }

    .detox-btn--primary:hover {
        background: var(--color-primary-dark);
    }

    .detox-btn--secondary {
        background: var(--color-white);
        color: var(--color-primary-dark);
        border-color: var(--color-primary);
    }

    .detox-btn--secondary:hover {
        background: var(--color-primary-light);
    }

    .detox-login-link {
        display: block;
        text-align: center;
        margin-top: 16px;
        font-size: 13px;
        color: var(--color-text-light);
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    @media (max-width: 860px) {
        .detox-hero__grid {
            grid-template-columns: 1fr;
            gap: 28px;
        }

        .detox-title {
            font-size: 26px;
        }
    }
</style>

<section class="detox-hero">
    <div class="container detox-hero__grid">

        <div class="detox-gallery">
            <div class="detox-gallery__main">
                <?php if ($heroImage): ?>
                    <img id="detoxMainImage"
                         src="<?= htmlspecialchars($heroImage['url'], ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= htmlspecialchars($heroImage['alt'], ENT_QUOTES, 'UTF-8') ?>">
                <?php else: ?>
                    <div class="detox-gallery__placeholder">
                        No gallery image yet — add one in Banner Management with
                        position <strong>detox_gallery</strong>.
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($thumbnailImages)): ?>
                <div class="detox-gallery__thumbs">
                    <?php foreach ($thumbnailImages as $thumbIndex => $image): ?>
                        <button
                            type="button"
                            class="detox-gallery__thumb"
                            data-full="<?= htmlspecialchars($image['url'], ENT_QUOTES, 'UTF-8') ?>"
                            data-alt="<?= htmlspecialchars($image['alt'], ENT_QUOTES, 'UTF-8') ?>"
                        >
                            <img src="<?= htmlspecialchars($image['url'], ENT_QUOTES, 'UTF-8') ?>"
                                 alt="<?= htmlspecialchars($image['alt'], ENT_QUOTES, 'UTF-8') ?>">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="detox-info">
            <span class="detox-badge"><?= htmlspecialchars($pageBadge, ENT_QUOTES, 'UTF-8') ?></span>

            <h1 class="detox-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="detox-subtitle"><?= htmlspecialchars($pageSubtitle, ENT_QUOTES, 'UTF-8') ?></p>

            <div class="detox-rating">
                <span class="detox-rating__stars"><?= detoxRenderStars($rating) ?></span>
                <span class="detox-rating__value"><?= number_format($rating, 1) ?></span>
                <a href="#reviews" class="detox-rating__link">See all <?= (int) $reviewCount ?> reviews</a>
            </div>

            <p class="detox-description"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>

            <ol class="detox-inclusions">
                <?php foreach ($inclusions as $incIndex => $item): ?>
                    <li class="detox-inclusions__item">
                        <span class="detox-inclusions__number"><?= $incIndex + 1 ?></span>
                        <span class="detox-inclusions__text">
                            <span class="detox-inclusions__label">
                                <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <?php if (!empty($item['detail'])): ?>
                                <span class="detox-inclusions__detail">
                                    <?= htmlspecialchars($item['detail'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            <?php endif; ?>
                        </span>
                        <span class="detox-inclusions__price">
                            <?php if (!empty($item['was'])): ?>
                                <span class="detox-inclusions__was">&#8377;<?= number_format($item['was']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($item['price'])): ?>
                                <span class="detox-inclusions__now">&#8377;<?= number_format($item['price']) ?></span>
                            <?php else: ?>
                                <span class="detox-inclusions__free">Free</span>
                            <?php endif; ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ol>

            <div class="detox-total">
                <div class="detox-total__label">
                    <span>Your Personalised Gut Detox Plan</span>
                    <span class="detox-total__saved">You saved &#8377;<?= number_format($youSaved) ?></span>
                </div>
                <div class="detox-total__price">
                    <span class="detox-total__was">&#8377;<?= number_format($totalWas) ?></span>
                    <span class="detox-total__now">&#8377;<?= number_format($totalNow) ?></span>
                </div>
                <div class="detox-total__mrp">MRP (incl. of all taxes)</div>
            </div>

            <div class="detox-actions">
                <button type="button" class="detox-btn detox-btn--primary">Buy Now</button>
                <button type="button" class="detox-btn detox-btn--secondary">Book a Consultation</button>
            </div>

            <a href="<?= htmlspecialchars(rtrim(BASE_URL, '/'), ENT_QUOTES, 'UTF-8') ?>/login.php" class="detox-login-link">
                Already purchased? Login here
            </a>
        </div>

    </div>
</section>

<script>
    (function () {
        var mainImage = document.getElementById('detoxMainImage');
        var thumbs = document.querySelectorAll('.detox-gallery__thumb');

        thumbs.forEach(function (thumb) {
            thumb.addEventListener('click', function () {
                if (!mainImage) {
                    return;
                }
                mainImage.src = thumb.getAttribute('data-full');
                mainImage.alt = thumb.getAttribute('data-alt') || '';

                thumbs.forEach(function (t) {
                    t.classList.remove('is-active');
                });
                thumb.classList.add('is-active');
            });
        });
    })();
</script>