<?php
require_once __DIR__ . '/../function/banner.php';

// -----------------------------------------------------------------------
// Gallery images
// -----------------------------------------------------------------------
$detoxBanners = getActiveBanners($conn, 'detux_hero');

$galleryImages = array_map(static function ($banner) {
    return [
        'url' => getBannerImageUrl($banner['image']),
        'alt' => $banner['title'] !== null && $banner['title'] !== ''
            ? $banner['title']
            : 'Complete Gut Detox',
    ];
}, $detoxBanners);

$heroImage       = $galleryImages[0] ?? null;
$thumbnailImages = array_slice($galleryImages, 1); // kept for backwards compatibility

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

// "What's Included" section copy
$showInclusions   = true;
$includedEyebrow  = 'Everything in your plan';
$includedHeading  = 'What’s Included';

// Each item: label, optional detail line, "blurb" (one-line fallback
// description), icon key, "was" price, and "price" (null price = "Free").
$inclusions = [
    [
        'label'  => '30 Days Ayurvedic Gut Wellness Kit',
        'detail' => 'Organic Gut Health, Livomap, Ama Cleanse, Pure Cleanse Infusion',
        'blurb'  => 'Curated Ayurvedic herbs for gentle cleansing.',
        'icon'   => 'kit',
        'was'    => 5499,
        'price'  => 2599,
    ],
    [
        'label'  => '3 Doctor Consultations',
        'detail' => null,
        'blurb'  => 'One-to-one guidance from Ayurvedic doctors.',
        'icon'   => 'doctor',
        'was'    => 1900,
        'price'  => 1400,
    ],
    [
        'label'  => 'Dedicated Wellness Counsellor',
        'detail' => null,
        'blurb'  => 'Personal support throughout your 10 days.',
        'icon'   => 'counsellor',
        'was'    => 1999,
        'price'  => null,
    ],
    [
        'label'  => '10-Day Video Course',
        'detail' => null,
        'blurb'  => 'Step-by-step guidance for every day.',
        'icon'   => 'video',
        'was'    => 999,
        'price'  => null,
    ],
    [
        'label'  => 'Signature MA Tea Cup',
        'detail' => null,
        'blurb'  => 'Your companion for the daily herbal infusion.',
        'icon'   => 'cup',
        'was'    => 999,
        'price'  => null,
    ],
    [
        'label'  => '10 Day Habit Building Planner',
        'detail' => null,
        'blurb'  => 'Track your routines and build lasting habits.',
        'icon'   => 'planner',
        'was'    => 799,
        'price'  => null,
    ],
];

$totalWas = array_sum(array_column($inclusions, 'was'));
$totalNow = 3999;
$youSaved = $totalWas - $totalNow;

// Inner SVG markup (24x24 viewBox, stroke icons) for the inclusion items.
$detoxIcons = [
    'kit'        => '<path d="M3 11h18a9 9 0 0 1-9 9 9 9 0 0 1-9-9z"/><path d="M8.5 20.5h7"/><path d="M16.5 2.5 11.5 11"/><path d="m15 2 3 1.6"/>',
    'doctor'     => '<circle cx="12" cy="7" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/><path d="M12 16.2v3.6M10.2 18h3.6"/>',
    'counsellor' => '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.2-4.7A8 8 0 1 1 21 12z"/><path d="M12 15s-3.2-1.9-3.2-4.1a1.8 1.8 0 0 1 3.2-1.1 1.8 1.8 0 0 1 3.2 1.1C15.2 13.1 12 15 12 15z"/>',
    'video'      => '<rect x="2.5" y="3.5" width="19" height="13" rx="2"/><path d="M8 21h8M12 16.5V21"/><path d="m10.2 7.6 4.4 2.4-4.4 2.4z"/>',
    'cup'        => '<path d="M3.5 9h13v5a5 5 0 0 1-5 5h-3a5 5 0 0 1-5-5z"/><path d="M16.5 10.5h1a2.8 2.8 0 0 1 0 5.6h-1.3"/><path d="M7 3v2.5M10 3v2.5M13 3v2.5"/>',
    'planner'    => '<rect x="3" y="4.5" width="18" height="16.5" rx="2"/><path d="M8 2.5v4M16 2.5v4M3 10h18"/><path d="m9 15.3 2 2 4-4"/>',
];

/**
 * Render a star rating as filled / empty unicode stars.
 * (Kept because other detox sections may still call it.)
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

/**
 * Short HTML-escape helper.
 */
if (!function_exists('detoxE')) {
    function detoxE($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

$ratingFill = max(0, min(100, ($rating / 5) * 100));
?>

<style>
    /* ------------------------------------------------------------------
       Design tokens (fall back to the approved brand palette if the
       site-wide variables are not defined).
       -> If the homepage heading font has a different variable name,
          change --dx-heading-font here (one place).
       -> If your .container max-width is not 1240px, change
          --dx-container so the hero copy lines up with the header.
       ------------------------------------------------------------------ */
    .detox-hero,
    .detox-included {
        --dx-primary: var(--color-primary, #1f3a32);
        --dx-primary-dark: var(--color-primary-dark, #142a24);
        --dx-primary-light: var(--color-primary-light, #edf1e8);
        --dx-accent: var(--color-accent, #b28a32);
        --dx-bg: var(--color-bg, #f8f6ef);
        --dx-text: var(--color-text, #26342f);
        --dx-muted: var(--color-text-light, #6f776f);
        --dx-border: var(--color-border, #ded9c9);
        --dx-white: var(--color-white, #ffffff);
        --dx-radius: var(--radius-sm, 10px);
        --dx-heading-font: var(--font-heading, "Cormorant Garamond", "Playfair Display", Georgia, "Times New Roman", serif);
        --dx-container: 1240px;
    }

    .detox-sprite {
        position: absolute;
        width: 0;
        height: 0;
        overflow: hidden;
    }

    /* Decorative botanical sprigs */
    .detox-leaf {
        position: absolute;
        z-index: 0;
        color: var(--dx-primary);
        opacity: 0.09;
        pointer-events: none;
    }

    /* ==================================================================
       1. HERO
       ================================================================== */
    .detox-hero {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(60% 70% at 92% 8%, rgba(237, 241, 232, 0.95), rgba(237, 241, 232, 0) 70%),
            var(--dx-bg);
    }

    .detox-hero__grid {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1.06fr) minmax(0, 1fr);
        align-items: stretch;
    }

    .detox-leaf--hero {
        top: -14px;
        right: -26px;
        width: clamp(110px, 11vw, 170px);
        transform: rotate(18deg);
    }

    /* ---- Gallery (bleeds to the left edge of the viewport) ---- */
    .detox-gallery {
        position: relative;
        min-height: clamp(520px, 42vw, 660px);
    }

    .detox-gallery__main {
        position: absolute;
        inset: 0;
        overflow: hidden;
        background: var(--dx-primary-light);
        border-bottom-right-radius: clamp(96px, 15vw, 230px) clamp(72px, 11.5vw, 175px);
    }

    .detox-gallery__main img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
        transition: opacity 0.35s ease;
    }

    .detox-gallery__main img.is-changing {
        opacity: 0;
    }

    /* Intentional empty state (no banner uploaded yet) */
    .detox-gallery__placeholder {
        position: relative;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: flex-end;
        justify-content: flex-start;
        padding: clamp(20px, 3vw, 36px);
        background:
            radial-gradient(70% 60% at 25% 20%, rgba(178, 138, 50, 0.22), rgba(178, 138, 50, 0) 70%),
            linear-gradient(160deg, var(--dx-primary) 0%, var(--dx-primary-dark) 100%);
        color: rgba(248, 246, 239, 0.75);
        font-size: 13px;
        line-height: 1.5;
        overflow: hidden;
    }

    .detox-gallery__placeholder .detox-leaf {
        color: #f8f6ef;
        opacity: 0.1;
    }

    .detox-gallery__placeholder .detox-leaf--a {
        top: 8%;
        left: 10%;
        width: clamp(120px, 16vw, 220px);
        transform: rotate(-24deg);
    }

    .detox-gallery__placeholder .detox-leaf--b {
        top: 22%;
        right: 22%;
        width: clamp(90px, 12vw, 170px);
        transform: rotate(28deg);
    }

    .detox-gallery__placeholder p {
        position: relative;
        max-width: 34ch;
        margin: 0;
    }

    .detox-gallery__thumbs {
        position: absolute;
        z-index: 2;
        left: clamp(16px, 2vw, 28px);
        bottom: clamp(16px, 2vw, 28px);
        display: flex;
        gap: 10px;
        max-width: calc(100% - 40px);
        padding: 4px;
        overflow-x: auto;
        scrollbar-width: none;
    }

    .detox-gallery__thumbs::-webkit-scrollbar {
        display: none;
    }

    .detox-gallery__thumb {
        flex: 0 0 auto;
        width: 58px;
        height: 58px;
        padding: 0;
        cursor: pointer;
        border-radius: 12px;
        border: 2px solid rgba(255, 255, 255, 0.75);
        overflow: hidden;
        background: var(--dx-primary-light);
        opacity: 0.85;
        transition: opacity 0.25s ease, border-color 0.25s ease, transform 0.25s ease;
    }

    .detox-gallery__thumb:hover {
        opacity: 1;
        transform: translateY(-2px);
    }

    .detox-gallery__thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .detox-gallery__thumb.is-active {
        opacity: 1;
        border-color: var(--dx-accent);
    }

    .detox-gallery__thumb:focus-visible,
    .detox-btn:focus-visible,
    .detox-login-link:focus-visible,
    .detox-rating__link:focus-visible {
        outline: 3px solid var(--dx-accent);
        outline-offset: 3px;
    }

    /* ---- Product info ---- */
    .detox-info {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
        padding:
            clamp(40px, 5vw, 72px) max(24px, calc((100vw - var(--dx-container)) / 2)) clamp(32px, 4vw, 56px) clamp(32px, 4.5vw, 72px);
        animation: detoxRise 0.8s ease both;
    }

    @keyframes detoxRise {
        from {
            opacity: 0;
            transform: translateY(14px);
        }

        to {
            opacity: 1;
            transform: none;
        }
    }

    .detox-badge {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 999px;
        background: var(--dx-primary-light);
        box-shadow: inset 0 0 0 1px rgba(31, 58, 50, 0.07);
        color: var(--dx-primary-dark);
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        line-height: 1;
    }

    .detox-title {
        margin: 20px 0 0;
        font-family: var(--dx-heading-font);
        font-size: clamp(2.5rem, 4.4vw, 3.9rem);
        font-weight: 700;
        line-height: 1.05;
        letter-spacing: -0.01em;
        color: var(--dx-primary);
    }

    .detox-subtitle {
        margin: 14px 0 0;
        font-size: clamp(1.1rem, 1.6vw, 1.35rem);
        line-height: 1.4;
        font-weight: 400;
        color: var(--dx-text);
    }

    .detox-description {
        margin: 20px 0 0;
        max-width: 58ch;
        font-size: clamp(0.98rem, 1.15vw, 1.05rem);
        line-height: 1.7;
        color: var(--dx-text);
        opacity: 0.85;
    }

    .detox-rating {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px 14px;
        margin-top: 22px;
        font-size: 16px;
        color: var(--dx-muted);
    }

    .detox-rating__stars {
        font-size: 19px;
        line-height: 1;
        letter-spacing: 2px;
        background: linear-gradient(90deg, var(--dx-accent) var(--fill), #dcd3b8 var(--fill));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        -webkit-text-fill-color: transparent;
    }

    .detox-rating__value {
        font-weight: 600;
        color: var(--dx-text);
    }

    .detox-rating__divider {
        width: 1px;
        height: 18px;
        background: var(--dx-border);
    }

    .detox-rating__link {
        color: var(--dx-muted);
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .detox-rating__link:hover {
        color: var(--dx-primary);
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    /* ---- Price ---- */
    .detox-price {
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
        gap: 16px 28px;
        margin-top: 30px;
    }

    .detox-price__main {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .detox-price__plan {
        margin: 0 0 6px;
        font-size: 13px;
        color: var(--dx-muted);
    }

    .detox-price__was {
        font-size: 15px;
        color: var(--dx-muted);
        text-decoration: line-through;
    }

    .detox-price__now {
        font-family: var(--dx-heading-font);
        font-size: clamp(2.1rem, 3vw, 2.6rem);
        font-weight: 700;
        line-height: 1.1;
        color: var(--dx-primary);
    }

    .detox-price__note {
        font-size: 12px;
        color: var(--dx-muted);
    }

    .detox-price__save {
        display: flex;
        align-items: center;
        padding-left: 28px;
        border-left: 1px solid var(--dx-border);
        font-size: 19px;
        font-weight: 600;
        color: var(--dx-accent);
    }

    /* ---- CTAs ---- */
    .detox-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        width: 100%;
        margin-top: 30px;
    }

    .detox-btn {
        flex: 1 1 200px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 56px;
        padding: 14px 22px;
        border-radius: var(--dx-radius);
        border: 1.5px solid transparent;
        font-family: inherit;
        font-size: 16px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        transition: background-color 0.25s ease, transform 0.25s ease, color 0.25s ease;
    }

    .detox-btn svg {
        width: 20px;
        height: 20px;
        flex: 0 0 auto;
    }

    .detox-btn--primary {
        background: var(--dx-primary);
        color: var(--dx-white);
    }

    .detox-btn--primary:hover {
        background: var(--dx-primary-dark);
        transform: translateY(-1px);
    }

    .detox-btn--secondary {
        background: var(--dx-white);
        color: var(--dx-primary-dark);
        border-color: var(--dx-primary);
    }

    .detox-btn--secondary:hover {
        background: var(--dx-primary-light);
        transform: translateY(-1px);
    }

    .detox-login-link {
        margin-top: 20px;
        font-size: 14px;
        color: var(--dx-muted);
        text-decoration: underline;
        text-underline-offset: 3px;
        transition: color 0.2s ease;
    }

    .detox-login-link:hover {
        color: var(--dx-primary);
    }

    /* ==================================================================
       2. WHAT'S INCLUDED
       ================================================================== */
    .detox-included {
        position: relative;
        overflow: hidden;
        background: var(--dx-primary-light);
        padding: clamp(48px, 6vw, 80px) 0 clamp(56px, 7vw, 96px);
    }

    .detox-included .container {
        position: relative;
        z-index: 1;
    }

    .detox-leaf--inc-l {
        left: -24px;
        bottom: 6%;
        width: clamp(90px, 9vw, 140px);
        transform: rotate(-32deg);
    }

    .detox-leaf--inc-r {
        right: -20px;
        bottom: -10px;
        width: clamp(90px, 8vw, 130px);
        transform: rotate(24deg);
    }

    .detox-included__head {
        text-align: center;
    }

    .detox-included__mark {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
        color: var(--dx-primary);
    }

    .detox-included__mark i {
        display: block;
        width: clamp(28px, 4vw, 44px);
        height: 1px;
        background: rgba(31, 58, 50, 0.25);
    }

    .detox-included__mark svg {
        width: 22px;
        height: 22px;
    }

    .detox-included__eyebrow {
        margin: 12px 0 0;
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        color: var(--dx-muted);
    }

    .detox-included__heading {
        margin: 6px 0 0;
        font-family: var(--dx-heading-font);
        font-size: clamp(2rem, 3.6vw, 3rem);
        font-weight: 700;
        line-height: 1.1;
        color: var(--dx-primary);
    }

    .detox-included__grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        row-gap: 44px;
        margin: clamp(32px, 4vw, 52px) 0 0;
        padding: 0;
        list-style: none;
    }

    .detox-included__item {
        padding: 0 clamp(12px, 1.4vw, 22px);
        text-align: center;
        border-left: 1px solid rgba(31, 58, 50, 0.13);
    }

    .detox-included__item:first-child {
        border-left: 0;
    }

    .detox-included__icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 72px;
        height: 72px;
        margin: 0 auto;
        border-radius: 50%;
        background: rgba(31, 58, 50, 0.08);
        color: var(--dx-primary);
    }

    .detox-included__icon svg {
        width: 30px;
        height: 30px;
    }

    .detox-included__title {
        margin: 20px 0 0;
        font-family: var(--dx-heading-font);
        font-size: 1.15rem;
        font-weight: 700;
        line-height: 1.25;
        color: var(--dx-primary);
    }

    .detox-included__desc {
        margin: 8px 0 0;
        font-size: 0.95rem;
        line-height: 1.55;
        color: var(--dx-muted);
    }

    .detox-included__value {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 2px 8px;
        margin-top: 12px;
        font-size: 0.875rem;
    }

    .detox-included__value s {
        color: var(--dx-muted);
    }

    .detox-included__value strong {
        font-weight: 600;
        color: var(--dx-primary);
    }

    /* ==================================================================
       RESPONSIVE
       ================================================================== */

    /* Tablet landscape / small laptop: inclusions in 2 rows of 3 */
    @media (max-width: 1199px) {
        .detox-included__grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .detox-included__item:nth-child(3n + 1) {
            border-left: 0;
        }
    }

    /* Tablet portrait: image on top, content below */
    @media (max-width: 899px) {
        .detox-hero__grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .detox-leaf--hero {
            display: none;
        }

        .detox-gallery {
            min-height: 0;
            aspect-ratio: 5 / 4.2;
            max-height: 560px;
            width: 100%;
        }

        .detox-gallery__main {
            border-bottom-right-radius: clamp(72px, 20vw, 140px) clamp(56px, 15vw, 104px);
        }

        .detox-info {
            padding: 32px clamp(20px, 5vw, 48px) 40px;
        }

        .detox-description {
            max-width: 64ch;
        }
    }

    /* Large phones: inclusions 2-up */
    @media (max-width: 699px) {
        .detox-included__grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            row-gap: 36px;
        }

        .detox-included__item:nth-child(3n + 1) {
            border-left: 1px solid rgba(31, 58, 50, 0.13);
        }

        .detox-included__item:nth-child(2n + 1) {
            border-left: 0;
        }
    }

    /* Phones */
    @media (max-width: 479px) {
        .detox-gallery {
            aspect-ratio: 1 / 1;
        }

        .detox-gallery__thumb {
            width: 48px;
            height: 48px;
            border-radius: 10px;
        }

        .detox-title {
            margin-top: 16px;
        }

        .detox-price {
            gap: 14px 20px;
        }

        .detox-price__save {
            padding-left: 20px;
            font-size: 18px;
        }

        .detox-btn {
            flex: 1 1 100%;
        }

        /* Inclusions become a clean vertical list: icon left, text right */
        .detox-included__grid {
            grid-template-columns: minmax(0, 1fr);
            row-gap: 0;
        }

        .detox-included__item,
        .detox-included__item:nth-child(3n + 1),
        .detox-included__item:nth-child(2n + 1) {
            display: grid;
            grid-template-columns: 56px minmax(0, 1fr);
            column-gap: 16px;
            align-items: start;
            padding: 20px 0;
            text-align: left;
            border-left: 0;
            border-top: 1px solid rgba(31, 58, 50, 0.13);
        }

        .detox-included__item:first-child {
            border-top: 0;
            padding-top: 0;
        }

        .detox-included__icon {
            grid-row: 1 / span 3;
            width: 56px;
            height: 56px;
            margin: 0;
        }

        .detox-included__icon svg {
            width: 24px;
            height: 24px;
        }

        .detox-included__title {
            margin-top: 0;
        }

        .detox-included__value {
            justify-content: flex-start;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .detox-info {
            animation: none;
        }

        .detox-btn,
        .detox-gallery__thumb,
        .detox-gallery__main img {
            transition: none;
        }

        .detox-btn:hover,
        .detox-gallery__thumb:hover {
            transform: none;
        }
    }
</style>

<!-- Shared SVG sprite (botanical sprig + leaf mark) -->
<svg class="detox-sprite" aria-hidden="true" focusable="false">
    <symbol id="detox-sprig" viewBox="0 0 120 200">
        <path d="M60 198C54 140 66 80 60 8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
        <g fill="currentColor">
            <ellipse cx="86" cy="150" rx="26" ry="10" transform="rotate(-38 86 150)" />
            <ellipse cx="34" cy="126" rx="26" ry="10" transform="rotate(34 34 126)" />
            <ellipse cx="88" cy="100" rx="25" ry="9.5" transform="rotate(-34 88 100)" />
            <ellipse cx="34" cy="76" rx="24" ry="9.5" transform="rotate(32 34 76)" />
            <ellipse cx="84" cy="50" rx="21" ry="8.5" transform="rotate(-32 84 50)" />
            <ellipse cx="40" cy="30" rx="18" ry="7.5" transform="rotate(30 40 30)" />
            <ellipse cx="62" cy="9" rx="12" ry="6" transform="rotate(-80 62 9)" />
        </g>
    </symbol>
    <symbol id="detox-leaf-mark" viewBox="0 0 24 24">
        <path d="M5 19c0-8 5-14 15-14 0 10-6 15-14 15z" />
        <path d="M5 19c4-5 7-8 11-10" />
    </symbol>
</svg>

<!-- ================================================================== -->
<!-- HERO                                                               -->
<!-- ================================================================== -->
<section class="detox-hero" aria-labelledby="detoxTitle">
    <svg class="detox-leaf detox-leaf--hero" viewBox="0 0 120 200" aria-hidden="true">
        <use href="#detox-sprig" />
    </svg>

    <div class="detox-hero__grid">

        <div class="detox-gallery">
            <div class="detox-gallery__main">
                <?php if ($heroImage): ?>
                    <img id="detoxMainImage"
                        src="<?= detoxE($heroImage['url']) ?>"
                        alt="<?= detoxE($heroImage['alt']) ?>">
                <?php else: ?>
                    <div class="detox-gallery__placeholder">
                        <svg class="detox-leaf detox-leaf--a" viewBox="0 0 120 200" aria-hidden="true">
                            <use href="#detox-sprig" />
                        </svg>
                        <svg class="detox-leaf detox-leaf--b" viewBox="0 0 120 200" aria-hidden="true">
                            <use href="#detox-sprig" />
                        </svg>
                        <p>
                            No gallery image yet — add one in Banner Management with
                            position <strong>detox_gallery</strong>.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (count($galleryImages) > 1): ?>
                <div class="detox-gallery__thumbs" role="group" aria-label="Product gallery">
                    <?php foreach ($galleryImages as $thumbIndex => $image): ?>
                        <button
                            type="button"
                            class="detox-gallery__thumb<?= $thumbIndex === 0 ? ' is-active' : '' ?>"
                            aria-label="Show image <?= (int) ($thumbIndex + 1) ?>"
                            aria-pressed="<?= $thumbIndex === 0 ? 'true' : 'false' ?>"
                            data-full="<?= detoxE($image['url']) ?>"
                            data-alt="<?= detoxE($image['alt']) ?>">
                            <img src="<?= detoxE($image['url']) ?>"
                                alt="" loading="lazy">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="detox-info">
            <span class="detox-badge"><?= detoxE($pageBadge) ?></span>

            <h1 class="detox-title" id="detoxTitle"><?= detoxE($pageTitle) ?></h1>
            <p class="detox-subtitle"><?= detoxE($pageSubtitle) ?></p>

            <p class="detox-description"><?= detoxE($description) ?></p>

            <div class="detox-rating">
                <span class="detox-rating__stars"
                    style="--fill: <?= number_format($ratingFill, 1, '.', '') ?>%"
                    role="img"
                    aria-label="Rated <?= number_format($rating, 1) ?> out of 5">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                <span class="detox-rating__value"><?= number_format($rating, 1) ?></span>
                <span class="detox-rating__divider" aria-hidden="true"></span>
                <a href="#reviews" class="detox-rating__link">See all <?= (int) $reviewCount ?> reviews</a>
            </div>

            <div class="detox-price">
                <div class="detox-price__main">
                    <p class="detox-price__plan">Your Personalised Gut Detox Plan</p>
                    <span class="detox-price__was">MRP &#8377;<?= number_format($totalWas) ?></span>
                    <span class="detox-price__now">&#8377;<?= number_format($totalNow) ?></span>
                    <span class="detox-price__note">Inclusive of all taxes</span>
                </div>
                <div class="detox-price__save">You save &#8377;<?= number_format($youSaved) ?></div>
            </div>

            <div class="detox-actions">
                <button type="button" class="detox-btn detox-btn--primary">
                    Buy Now
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                </button>
                <a href="<?= detoxE(rtrim(BASE_URL, '/')) ?>/contact/" class="detox-btn detox-btn--secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="4.5" width="18" height="16.5" rx="2" />
                        <path d="M8 2.5v4M16 2.5v4M3 10h18" />
                    </svg>
                    Book a Consultation
                </a>
            </div>

            <a href="<?= detoxE(rtrim(BASE_URL, '/')) ?>/login.php" class="detox-login-link">
                Already purchased? Login here
            </a>
        </div>

    </div>
</section>

<?php if ($showInclusions): ?>
    <!-- ================================================================== -->
    <!-- WHAT'S INCLUDED                                                    -->
    <!-- ================================================================== -->
    <section class="detox-included" aria-labelledby="detoxIncludedHeading">
        <svg class="detox-leaf detox-leaf--inc-l" viewBox="0 0 120 200" aria-hidden="true">
            <use href="#detox-sprig" />
        </svg>
        <svg class="detox-leaf detox-leaf--inc-r" viewBox="0 0 120 200" aria-hidden="true">
            <use href="#detox-sprig" />
        </svg>

        <div class="container">
            <div class="detox-included__head">
                <div class="detox-included__mark" aria-hidden="true">
                    <i></i>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <use href="#detox-leaf-mark" />
                    </svg>
                    <i></i>
                </div>
                <p class="detox-included__eyebrow"><?= detoxE($includedEyebrow) ?></p>
                <h2 class="detox-included__heading" id="detoxIncludedHeading"><?= detoxE($includedHeading) ?></h2>
            </div>

            <ul class="detox-included__grid">
                <?php foreach ($inclusions as $item): ?>
                    <li class="detox-included__item">
                        <span class="detox-included__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <?= $detoxIcons[$item['icon']] ?? $detoxIcons['kit'] ?>
                            </svg>
                        </span>
                        <h3 class="detox-included__title"><?= detoxE($item['label']) ?></h3>
                        <p class="detox-included__desc">
                            <?= detoxE(!empty($item['detail']) ? $item['detail'] : $item['blurb']) ?>
                        </p>
                        <p class="detox-included__value">
                            <?php if (!empty($item['was'])): ?>
                                <s>&#8377;<?= number_format($item['was']) ?></s>
                            <?php endif; ?>
                            <?php if (!empty($item['price'])): ?>
                                <strong>&#8377;<?= number_format($item['price']) ?></strong>
                            <?php else: ?>
                                <strong>Free</strong>
                            <?php endif; ?>
                        </p>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
<?php endif; ?>

<script>
    (function() {
        var mainImage = document.getElementById('detoxMainImage');
        var thumbs = document.querySelectorAll('.detox-gallery__thumb');

        if (!mainImage || !thumbs.length) {
            return;
        }

        thumbs.forEach(function(thumb) {
            thumb.addEventListener('click', function() {
                if (thumb.classList.contains('is-active')) {
                    return;
                }

                var url = thumb.getAttribute('data-full');
                var alt = thumb.getAttribute('data-alt') || '';
                var loader = new Image();

                mainImage.classList.add('is-changing');

                loader.onload = loader.onerror = function() {
                    mainImage.src = url;
                    mainImage.alt = alt;
                    mainImage.classList.remove('is-changing');
                };
                loader.src = url;

                thumbs.forEach(function(t) {
                    t.classList.remove('is-active');
                    t.setAttribute('aria-pressed', 'false');
                });
                thumb.classList.add('is-active');
                thumb.setAttribute('aria-pressed', 'true');
            });
        });
    })();
</script>