<?php

/* -----------------------------------------------------------------------------
 * Data (unchanged logic): pull active promo videos from the database unless a
 * caller already supplied $promoVideos.
 * --------------------------------------------------------------------------- */
if (!isset($promoVideos) || !is_array($promoVideos)) {
    if (!function_exists('getFeaturedPromoVideos')) {
        require_once __DIR__ . '/../function/promotional-video.php';
    }
    try {
        $promoVideos = getFeaturedPromoVideos($conn, 12);
    } catch (Throwable $e) {
        error_log('Promo videos error: ' . $e->getMessage());
        $promoVideos = [];
    }
}

$promoCount = count($promoVideos);

/* -----------------------------------------------------------------------------
 * Presentation prep: split by orientation and group into "pages" of
 * 1 featured horizontal video + up to 2 vertical videos.
 * --------------------------------------------------------------------------- */
$pvEsc = function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$pvHorizontal = [];
$pvVertical = [];
foreach ($promoVideos as $pvItem) {
    $pvOrientation = strtolower(trim((string) ($pvItem['orientation'] ?? '')));
    if ($pvOrientation === 'vertical') {
        $pvVertical[] = $pvItem;
    } else {
        $pvHorizontal[] = $pvItem; // horizontal or unknown
    }
}

$pvPages = [];
while ($pvHorizontal || $pvVertical) {
    $pvFeatured = $pvHorizontal ? array_shift($pvHorizontal) : null;
    $pvPages[] = [
        'featured'  => $pvFeatured,
        'verticals' => array_splice($pvVertical, 0, $pvFeatured ? 2 : 3),
    ];
}

// Category badges are placeholders until a category column exists in the
// table; if a row ever has a 'category' value it is used instead.
$pvBadgesHorizontal = ['Brand Story', 'Product Story', 'Customer Stories'];
$pvBadgesVertical   = ['Behind The Scenes', 'Wellness Tips', 'Product Story'];
$pvHIndex = 0;
$pvVIndex = 0;

/**
 * Renders one video card. $variant is 'featured' or 'vertical'.
 */
$pvRenderCard = function (array $video, $variant, $badge) use ($pvEsc) {
    $isFeatured = $variant === 'featured';
    $title = trim((string) ($video['title'] ?? ''));
    $desc  = trim((string) ($video['description'] ?? ''));
    $embed = !empty($video['embed_src']) ? (string) $video['embed_src'] : '';

    $fileUrl = '';
    if ($embed === '' && !empty($video['video_url'])) {
        $fileUrl = BASE_URL . ltrim((string) $video['video_url'], '/');
    }

    $poster = '';
    $posterFallback = '';
    $posterIsYoutube = false;
    if (!empty($video['thumbnail'])) {
        $poster = BASE_URL . ltrim((string) $video['thumbnail'], '/');
    } elseif ($embed !== '' && preg_match('~youtube\.com/embed/([A-Za-z0-9_-]{11})~', $embed, $m)) {
        $posterIsYoutube = true;
        $poster = 'https://i.ytimg.com/vi/' . $m[1] . '/' . ($isFeatured ? 'maxresdefault' : 'hqdefault') . '.jpg';
        $posterFallback = 'https://i.ytimg.com/vi/' . $m[1] . '/hqdefault.jpg';
    }

    $posterClass = 'promotional-video-card__poster';
    if ($posterIsYoutube && !$isFeatured) {
        $posterClass .= ' promotional-video-card__poster--letterboxed';
    }

    $cardClass = 'promotional-video-card promotional-video-card--' . ($isFeatured ? 'featured' : 'vertical');
    $label = $title !== '' ? $title : 'video';
    ?>
    <article class="<?= $cardClass ?>" data-pv-card
             data-pv-embed="<?= $pvEsc($embed) ?>"
             data-pv-title="<?= $pvEsc($label) ?>">
        <div class="promotional-video-card__media" data-pv-media>
            <?php if ($embed === '' && $fileUrl !== ''): ?>
                <video class="promotional-video-card__video"
                       src="<?= $pvEsc($fileUrl) ?>#t=0.1"
                       <?= $poster !== '' ? 'poster="' . $pvEsc($poster) . '"' : '' ?>
                       preload="metadata"
                       playsinline></video>
            <?php elseif ($poster !== ''): ?>
                <img class="<?= $posterClass ?>" src="<?= $pvEsc($poster) ?>" alt="" loading="lazy"
                     <?= $posterFallback !== '' ? 'onerror="this.onerror=null;this.src=\'' . $pvEsc($posterFallback) . '\'"' : '' ?>>
            <?php endif; ?>

            <div class="promotional-video-card__overlay">
                <span class="promotional-video-card__badge"><?= $pvEsc($badge) ?></span>

                <button type="button" class="promotional-video-card__play" data-pv-play
                        aria-label="Play video: <?= $pvEsc($label) ?>">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8.5 5.6v12.8a.6.6 0 0 0 .9.5l10-6.4a.6.6 0 0 0 0-1L9.4 5.1a.6.6 0 0 0-.9.5z"/></svg>
                </button>

                <div class="promotional-video-card__content">
                    <?php if ($title !== ''): ?>
                        <h3 class="promotional-video-card__title"><?= $pvEsc($title) ?></h3>
                    <?php endif; ?>
                    <?php if ($desc !== ''): ?>
                        <p class="promotional-video-card__description"><?= $pvEsc($desc) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </article>
    <?php
};
?>

<?php if ($promoCount > 0): ?>
<style>
    /* =========================================================================
       Promotional Videos — editorial media section
       All selectors are scoped to .promotional-videos / .promotional-video-card
       ========================================================================= */

    .promotional-videos {
        --pv-primary: var(--color-primary, #245c4f);
        --pv-primary-dark: var(--color-primary-dark, #17483d);
        --pv-primary-light: var(--color-primary-light, #eaf4f0);
        --pv-accent: var(--color-accent, #91a96b);
        --pv-text: var(--color-text, #1d2925);
        --pv-text-light: var(--color-text-light, #69756f);
        --pv-border: var(--color-border, #dde7e2);
        --pv-radius: 20px;
        --pv-gap: 22px;
        --pv-shadow: 0 15px 40px rgba(36, 92, 79, 0.10);
        --pv-shadow-hover: 0 20px 46px rgba(36, 92, 79, 0.17);

        position: relative;
        overflow: hidden;
        padding: 88px 0 72px;
        background: #fafcfa;
        color: var(--pv-text);
    }

    .promotional-videos > .container {
        position: relative;
        z-index: 1;
    }

    /* ---- Botanical decoration (kept faint and in the corners) ---- */

    .promotional-videos__decor {
        position: absolute;
        width: 150px;
        height: auto;
        color: var(--pv-accent);
        opacity: 0.32;
        pointer-events: none;
        z-index: 0;
    }

    .promotional-videos__decor--top {
        top: 24px;
        left: -34px;
        transform: rotate(-8deg);
    }

    .promotional-videos__decor--bottom {
        right: -34px;
        bottom: 96px;
        transform: scaleX(-1) rotate(-8deg);
    }

    /* ---- Header ---- */

    .promotional-videos__header {
        max-width: 640px;
        margin: 0 auto 52px;
        text-align: center;
    }

    .promotional-videos__leaf {
        display: block;
        width: 34px;
        height: 34px;
        margin: 0 auto 10px;
        color: var(--pv-primary);
    }

    .promotional-videos__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 14px;
        margin: 0 0 14px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.28em;
        text-transform: uppercase;
        color: var(--pv-primary);
    }

    .promotional-videos__eyebrow::before,
    .promotional-videos__eyebrow::after {
        content: "";
        width: 34px;
        height: 1px;
        background: var(--pv-accent);
    }

    .promotional-videos__title {
        margin: 0 0 14px;
        font-family: var(--font-heading, inherit);
        font-size: clamp(30px, 4.2vw, 46px);
        font-weight: 600;
        line-height: 1.15;
        letter-spacing: -0.01em;
        color: var(--pv-primary-dark);
    }

    .promotional-videos__description {
        margin: 0;
        font-size: 16px;
        line-height: 1.7;
        color: var(--pv-text-light);
    }

    /* ---- Gallery grid ---- */

    .promotional-videos__gallery {
        position: relative;
    }

    .promotional-videos__page {
        --pv-cols: minmax(0, 3.8fr) minmax(150px, 1.38fr) minmax(150px, 1.38fr);
        display: grid;
        grid-template-columns: var(--pv-cols);
        gap: var(--pv-gap);
        align-items: start;
        animation: pv-fade 0.45s ease both;
    }

    .promotional-videos__page[hidden] {
        display: none;
    }

    .promotional-videos__page--one-vertical {
        --pv-cols: minmax(0, 3.8fr) minmax(150px, 1.38fr);
    }

    .promotional-videos__page--solo {
        --pv-cols: minmax(0, 1fr);
        max-width: 880px;
        margin-inline: auto;
    }

    .promotional-videos__page--verticals {
        --pv-cols: repeat(3, minmax(150px, 260px));
        justify-content: center;
    }

    @keyframes pv-fade {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: none; }
    }

    /* ---- Video card ---- */

    .promotional-video-card {
        position: relative;
        width: 100%;
        border-radius: var(--pv-radius);
        overflow: hidden;
        background: linear-gradient(135deg, var(--pv-primary-dark), var(--pv-primary));
        box-shadow: var(--pv-shadow);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .promotional-video-card:hover,
    .promotional-video-card:focus-within {
        transform: translateY(-3px);
        box-shadow: var(--pv-shadow-hover);
    }

    .promotional-video-card--featured {
        aspect-ratio: 16 / 9;
    }

    .promotional-video-card--vertical {
        aspect-ratio: 9 / 14;
    }

    .promotional-video-card__media {
        position: absolute;
        inset: 0;
        cursor: pointer;
    }

    .promotional-video-card__poster,
    .promotional-video-card__video {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scale(var(--pv-base, 1));
        transition: transform 0.6s ease;
    }

    /* YouTube's 4:3 thumbnails of vertical clips carry black side bars;
       a slight scale hides them inside the 9:14 frame. */
    .promotional-video-card__poster--letterboxed {
        --pv-base: 1.2;
    }

    .promotional-video-card:hover .promotional-video-card__poster,
    .promotional-video-card:hover .promotional-video-card__video {
        transform: scale(calc(var(--pv-base, 1) * 1.03));
    }

    .promotional-video-card__frame {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        border: 0;
        background: #0b1512;
    }

    .promotional-video-card.is-playing .promotional-video-card__video {
        object-fit: contain;
        background: #0b1512;
        transform: none;
    }

    /* ---- Branded overlay ---- */

    .promotional-video-card__overlay {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: flex-start;
        padding: 20px;
        color: #fff;
        background:
            linear-gradient(to top, rgba(10, 32, 27, 0.82) 0%, rgba(10, 32, 27, 0.38) 38%, rgba(10, 32, 27, 0) 66%),
            linear-gradient(to bottom, rgba(10, 32, 27, 0.28) 0%, rgba(10, 32, 27, 0) 28%);
        transition: opacity 0.3s ease;
    }

    .promotional-video-card.is-playing .promotional-video-card__overlay {
        opacity: 0;
        pointer-events: none;
    }

    .promotional-video-card__badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 999px;
        background: rgba(36, 92, 79, 0.88);
        color: #fff;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.3;
        letter-spacing: 0.02em;
        -webkit-backdrop-filter: blur(6px);
        backdrop-filter: blur(6px);
    }

    .promotional-video-card__play {
        position: absolute;
        top: 50%;
        left: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 54px;
        height: 54px;
        padding: 0 0 0 3px;
        border: 0;
        border-radius: 50%;
        background: var(--pv-primary);
        color: #fff;
        cursor: pointer;
        box-shadow: 0 8px 22px rgba(10, 32, 27, 0.35), 0 0 0 7px rgba(255, 255, 255, 0.16);
        transform: translate(-50%, -50%);
        transition: transform 0.25s ease, background 0.2s ease;
    }

    .promotional-video-card--featured .promotional-video-card__play {
        width: 66px;
        height: 66px;
        box-shadow: 0 10px 26px rgba(10, 32, 27, 0.35), 0 0 0 9px rgba(255, 255, 255, 0.16);
    }

    .promotional-video-card__play svg {
        width: 46%;
        height: 46%;
    }

    .promotional-video-card:hover .promotional-video-card__play,
    .promotional-video-card__play:focus-visible {
        transform: translate(-50%, -50%) scale(1.08);
        background: var(--pv-primary-dark);
    }

    .promotional-video-card__play:focus-visible {
        outline: 2px solid #fff;
        outline-offset: 4px;
    }

    .promotional-video-card__content {
        width: 100%;
        margin-top: auto;
    }

    .promotional-video-card__title {
        margin: 0 0 6px;
        font-family: var(--font-heading, inherit);
        font-size: 17px;
        font-weight: 600;
        line-height: 1.3;
        color: #fff;
    }

    .promotional-video-card--featured .promotional-video-card__title {
        max-width: 560px;
        font-size: clamp(21px, 2.3vw, 30px);
        line-height: 1.2;
    }

    .promotional-video-card__description {
        margin: 0;
        font-size: 12.5px;
        line-height: 1.5;
        color: rgba(255, 255, 255, 0.88);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .promotional-video-card--featured .promotional-video-card__description {
        max-width: 460px;
        font-size: 14.5px;
    }

    /* ---- Navigation ---- */

    .promotional-videos__navigation {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 18px;
        margin-top: 34px;
    }

    .promotional-videos__nav-button {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border: 1px solid var(--pv-border);
        border-radius: 50%;
        background: #fff;
        color: var(--pv-primary);
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
    }

    .promotional-videos__nav-button svg {
        width: 16px;
        height: 16px;
    }

    .promotional-videos__nav-button:hover:not(:disabled) {
        background: var(--pv-primary);
        border-color: var(--pv-primary);
        color: #fff;
    }

    .promotional-videos__nav-button:focus-visible,
    .promotional-videos__dot:focus-visible {
        outline: 2px solid var(--pv-primary);
        outline-offset: 3px;
    }

    .promotional-videos__nav-button:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .promotional-videos__dots {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .promotional-videos__dot {
        width: 9px;
        height: 9px;
        padding: 0;
        border: 0;
        border-radius: 50%;
        background: #d3ddd8;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .promotional-videos__dot[aria-current="true"] {
        background: var(--pv-primary);
        transform: scale(1.15);
    }

    /* ---- Trust bar ---- */

    .promotional-videos__trust {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-top: 64px;
        padding-top: 40px;
        border-top: 1px solid var(--pv-border);
    }

    .promotional-videos__trust-item {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 14px;
        padding: 6px 18px;
    }

    .promotional-videos__trust-item + .promotional-videos__trust-item {
        border-left: 1px solid var(--pv-border);
    }

    .promotional-videos__trust-icon {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        border: 1.5px solid var(--pv-primary);
        border-radius: 50%;
        color: var(--pv-primary);
    }

    .promotional-videos__trust-icon svg {
        width: 22px;
        height: 22px;
    }

    .promotional-videos__trust-title {
        margin: 0 0 2px;
        font-size: 15px;
        font-weight: 600;
        color: var(--pv-primary-dark);
    }

    .promotional-videos__trust-text {
        margin: 0;
        font-size: 13px;
        line-height: 1.4;
        color: var(--pv-text-light);
    }

    /* ---- Tablet: featured on top, verticals side by side underneath ---- */

    @media (max-width: 960px) {
        .promotional-videos {
            padding: 68px 0 56px;
        }

        .promotional-videos__page,
        .promotional-videos__page--one-vertical,
        .promotional-videos__page--verticals {
            --pv-cols: repeat(2, minmax(0, 1fr));
        }

        .promotional-videos__page--solo {
            --pv-cols: minmax(0, 1fr);
        }

        .promotional-video-card--featured {
            grid-column: 1 / -1;
        }

        .promotional-video-card--vertical {
            justify-self: center;
            max-width: 300px;
        }

        .promotional-videos__trust {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            row-gap: 26px;
        }

        .promotional-videos__trust-item:nth-child(odd) {
            border-left: 0;
        }

        .promotional-videos__decor {
            width: 110px;
        }
    }

    /* ---- Mobile ---- */

    @media (max-width: 560px) {
        .promotional-videos {
            --pv-gap: 14px;
            --pv-radius: 18px;
            padding: 52px 0 44px;
        }

        .promotional-videos__header {
            margin-bottom: 34px;
        }

        .promotional-videos__eyebrow {
            font-size: 11px;
            letter-spacing: 0.2em;
        }

        .promotional-videos__eyebrow::before,
        .promotional-videos__eyebrow::after {
            width: 20px;
        }

        .promotional-videos__description {
            font-size: 14.5px;
        }

        .promotional-video-card__overlay {
            padding: 14px;
        }

        .promotional-video-card__badge {
            padding: 4px 10px;
            font-size: 11px;
        }

        .promotional-video-card__play {
            width: 46px;
            height: 46px;
        }

        .promotional-video-card--featured .promotional-video-card__play {
            width: 56px;
            height: 56px;
        }

        .promotional-video-card--vertical .promotional-video-card__title {
            font-size: 14px;
        }

        .promotional-video-card--vertical .promotional-video-card__description {
            display: none;
        }

        .promotional-videos__trust {
            grid-template-columns: minmax(0, 1fr);
            margin-top: 44px;
            padding-top: 28px;
        }

        .promotional-videos__trust-item {
            justify-content: flex-start;
            padding: 0 6px;
        }

        .promotional-videos__trust-item + .promotional-videos__trust-item {
            border-left: 0;
        }

        .promotional-videos__decor {
            width: 80px;
            opacity: 0.25;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .promotional-videos__page,
        .promotional-video-card,
        .promotional-video-card__poster,
        .promotional-video-card__video,
        .promotional-video-card__play {
            animation: none;
            transition: none;
        }
    }
</style>

<section class="promotional-videos" data-pv aria-labelledby="promotional-videos-title">

    <!-- Decorative botanical branches -->
    <svg class="promotional-videos__decor promotional-videos__decor--top" viewBox="0 0 160 200" fill="currentColor" aria-hidden="true">
        <path d="M10 200C40 140 70 90 130 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <path transform="translate(32 152) rotate(-130) scale(1.1)" d="M0 0C10-12 28-12 40 0C28 12 10 12 0 0Z"/>
        <path transform="translate(46 128) rotate(-20) scale(1.1)" d="M0 0C10-12 28-12 40 0C28 12 10 12 0 0Z"/>
        <path transform="translate(70 96) rotate(-140) scale(1.05)" d="M0 0C10-12 28-12 40 0C28 12 10 12 0 0Z"/>
        <path transform="translate(88 74) rotate(-30) scale(1)" d="M0 0C10-12 28-12 40 0C28 12 10 12 0 0Z"/>
        <path transform="translate(112 44) rotate(-150) scale(0.9)" d="M0 0C10-12 28-12 40 0C28 12 10 12 0 0Z"/>
        <path transform="translate(128 22) rotate(-50) scale(0.8)" d="M0 0C10-12 28-12 40 0C28 12 10 12 0 0Z"/>
    </svg>
    <svg class="promotional-videos__decor promotional-videos__decor--bottom" viewBox="0 0 160 200" fill="currentColor" aria-hidden="true">
        <path d="M10 200C40 140 70 90 130 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <path transform="translate(32 152) rotate(-130) scale(1.1)" d="M0 0C10-12 28-12 40 0C28 12 10 12 0 0Z"/>
        <path transform="translate(46 128) rotate(-20) scale(1.1)" d="M0 0C10-12 28-12 40 0C28 12 10 12 0 0Z"/>
        <path transform="translate(70 96) rotate(-140) scale(1.05)" d="M0 0C10-12 28-12 40 0C28 12 10 12 0 0Z"/>
        <path transform="translate(88 74) rotate(-30) scale(1)" d="M0 0C10-12 28-12 40 0C28 12 10 12 0 0Z"/>
        <path transform="translate(112 44) rotate(-150) scale(0.9)" d="M0 0C10-12 28-12 40 0C28 12 10 12 0 0Z"/>
    </svg>

    <div class="container">

        <header class="promotional-videos__header">
            <svg class="promotional-videos__leaf" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true">
                <path d="M16 4c-4 3-6 7-6 11 0 3 2 6 6 8 4-2 6-5 6-8 0-4-2-8-6-11z"/>
                <path d="M9 12c-4 1-6 4-6 7 0 3 3 6 8 6-1-2-2-4-2-6 0-2 0-5 0-7z" opacity=".75"/>
                <path d="M23 12c4 1 6 4 6 7 0 3-3 6-8 6 1-2 2-4 2-6 0-2 0-5 0-7z" opacity=".75"/>
                <path d="M16 23v6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" fill="none"/>
            </svg>
            <p class="promotional-videos__eyebrow">Vedorishi Ayurveda</p>
            <h2 class="promotional-videos__title" id="promotional-videos-title">Stories From Our Brand</h2>
            <p class="promotional-videos__description">Discover our products, wellness stories, and the people behind Vedorishi Ayurveda.</p>
        </header>

        <div class="promotional-videos__gallery">
            <?php foreach ($pvPages as $pvPageIndex => $pvPage):
                $pvPageClass = 'promotional-videos__page';
                if (!$pvPage['featured']) {
                    $pvPageClass .= ' promotional-videos__page--verticals';
                } elseif (count($pvPage['verticals']) === 0) {
                    $pvPageClass .= ' promotional-videos__page--solo';
                } elseif (count($pvPage['verticals']) === 1) {
                    $pvPageClass .= ' promotional-videos__page--one-vertical';
                }
            ?>
                <div class="<?= $pvPageClass ?>" data-pv-page <?= $pvPageIndex > 0 ? 'hidden' : '' ?>>
                    <?php
                    if ($pvPage['featured']) {
                        $pvBadge = trim((string) ($pvPage['featured']['category'] ?? ''));
                        if ($pvBadge === '') {
                            $pvBadge = $pvBadgesHorizontal[$pvHIndex % count($pvBadgesHorizontal)];
                        }
                        $pvHIndex++;
                        $pvRenderCard($pvPage['featured'], 'featured', $pvBadge);
                    }
                    foreach ($pvPage['verticals'] as $pvVideo) {
                        $pvBadge = trim((string) ($pvVideo['category'] ?? ''));
                        if ($pvBadge === '') {
                            $pvBadge = $pvBadgesVertical[$pvVIndex % count($pvBadgesVertical)];
                        }
                        $pvVIndex++;
                        $pvRenderCard($pvVideo, 'vertical', $pvBadge);
                    }
                    ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($pvPages) > 1): ?>
            <nav class="promotional-videos__navigation" aria-label="Promotional videos pages">
                <button type="button" class="promotional-videos__nav-button" data-pv-prev aria-label="Previous videos">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                </button>
                <div class="promotional-videos__dots">
                    <?php foreach ($pvPages as $pvDotIndex => $pvUnused): ?>
                        <button type="button" class="promotional-videos__dot" data-pv-dot
                                aria-label="Show videos <?= $pvDotIndex + 1 ?> of <?= count($pvPages) ?>"
                                <?= $pvDotIndex === 0 ? 'aria-current="true"' : '' ?>></button>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="promotional-videos__nav-button" data-pv-next aria-label="Next videos">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                </button>
            </nav>
        <?php endif; ?>

        <div class="promotional-videos__trust">
            <div class="promotional-videos__trust-item">
                <span class="promotional-videos__trust-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 19c0-8 5-13 14-14 0 9-5 14-13 14z"/><path d="M5 19c3-4 6-7 10-9"/></svg>
                </span>
                <div>
                    <p class="promotional-videos__trust-title">100% Natural</p>
                    <p class="promotional-videos__trust-text">Pure, safe &amp; effective</p>
                </div>
            </div>
            <div class="promotional-videos__trust-item">
                <span class="promotional-videos__trust-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 3h6"/><path d="M10 3v6l-5 9a2 2 0 0 0 1.8 3h10.4A2 2 0 0 0 19 18l-5-9V3"/><path d="M7.5 15h9"/></svg>
                </span>
                <div>
                    <p class="promotional-videos__trust-title">No Harmful Chemicals</p>
                    <p class="promotional-videos__trust-text">Just the goodness of nature</p>
                </div>
            </div>
            <div class="promotional-videos__trust-item">
                <span class="promotional-videos__trust-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 3v5c0 5-3 8-7 10-4-2-7-5-7-10V6z"/><path d="M9 12l2 2 4-4"/></svg>
                </span>
                <div>
                    <p class="promotional-videos__trust-title">Trusted by Thousands</p>
                    <p class="promotional-videos__trust-text">Across India and beyond</p>
                </div>
            </div>
            <div class="promotional-videos__trust-item">
                <span class="promotional-videos__trust-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21v-8"/><path d="M12 13c0-4-3-6-7-6 0 4 3 6 7 6z"/><path d="M12 15c0-3 2-5 6-5 0 3-2 5-6 5z"/></svg>
                </span>
                <div>
                    <p class="promotional-videos__trust-title">Ayurvedic Wisdom</p>
                    <p class="promotional-videos__trust-text">Rooted in traditional wellness</p>
                </div>
            </div>
        </div>

    </div>
</section>

<script>
    (function () {
        'use strict';

        document.querySelectorAll('[data-pv]').forEach(function (root) {
            var pages = [].slice.call(root.querySelectorAll('[data-pv-page]'));
            var dots = [].slice.call(root.querySelectorAll('[data-pv-dot]'));
            var prevBtn = root.querySelector('[data-pv-prev]');
            var nextBtn = root.querySelector('[data-pv-next]');
            var current = 0;

            /* ---- Playback ---- */

            function resetCard(card) {
                var frame = card.querySelector('iframe');
                if (frame) frame.remove();

                var video = card.querySelector('video');
                if (video) {
                    video.pause();
                    video.controls = false;
                    try { video.currentTime = 0.1; } catch (e) {}
                }
                card.classList.remove('is-playing');
            }

            function stopAll(except) {
                root.querySelectorAll('[data-pv-card].is-playing').forEach(function (card) {
                    if (card !== except) resetCard(card);
                });
            }

            function playCard(card) {
                stopAll(card);

                var embed = card.getAttribute('data-pv-embed');
                var media = card.querySelector('[data-pv-media]');

                if (embed) {
                    if (card.querySelector('iframe')) return;
                    var frame = document.createElement('iframe');
                    frame.className = 'promotional-video-card__frame';
                    frame.src = embed + (embed.indexOf('?') > -1 ? '&' : '?') + 'autoplay=1';
                    frame.title = card.getAttribute('data-pv-title') || 'Video';
                    frame.allow = 'accelerometer; autoplay; encrypted-media; picture-in-picture; fullscreen';
                    frame.allowFullscreen = true;
                    frame.setAttribute('frameborder', '0');
                    media.appendChild(frame);
                } else {
                    var video = card.querySelector('video');
                    if (!video) return;
                    video.controls = true;
                    var attempt = video.play();
                    if (attempt && typeof attempt.catch === 'function') attempt.catch(function () {});
                    video.addEventListener('ended', function () { resetCard(card); }, { once: true });
                }

                card.classList.add('is-playing');
            }

            root.querySelectorAll('[data-pv-card]').forEach(function (card) {
                var media = card.querySelector('[data-pv-media]');
                if (!media) return;
                media.addEventListener('click', function () {
                    if (!card.classList.contains('is-playing')) playCard(card);
                });
            });

            /* ---- Pagination ---- */

            function showPage(index) {
                if (index < 0 || index >= pages.length || index === current) return;
                stopAll(null);
                pages[current].hidden = true;
                current = index;
                pages[current].hidden = false;
                syncNav();
            }

            function syncNav() {
                dots.forEach(function (dot, i) {
                    if (i === current) dot.setAttribute('aria-current', 'true');
                    else dot.removeAttribute('aria-current');
                });
                if (prevBtn) prevBtn.disabled = current === 0;
                if (nextBtn) nextBtn.disabled = current === pages.length - 1;
            }

            if (prevBtn) prevBtn.addEventListener('click', function () { showPage(current - 1); });
            if (nextBtn) nextBtn.addEventListener('click', function () { showPage(current + 1); });
            dots.forEach(function (dot, i) {
                dot.addEventListener('click', function () { showPage(i); });
            });

            // Swipe support for touch screens.
            var gallery = root.querySelector('.promotional-videos__gallery');
            var touchX = null;
            if (gallery && pages.length > 1) {
                gallery.addEventListener('touchstart', function (e) {
                    touchX = e.touches[0].clientX;
                }, { passive: true });
                gallery.addEventListener('touchend', function (e) {
                    if (touchX === null) return;
                    var delta = e.changedTouches[0].clientX - touchX;
                    touchX = null;
                    if (Math.abs(delta) > 60) showPage(current + (delta < 0 ? 1 : -1));
                }, { passive: true });
            }

            syncNav();
        });
    })();
</script>
<?php endif; ?>