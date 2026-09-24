<?php

/**
 * detux/consultation-cta.php
 *
 * Included from detux/index.php alongside the other detux sections.
 * No config/header/footer includes here — index.php already handles
 * those.
 *
 * Image comes from Banner Management, position =
 * 'detox_consultation_cta' (same workflow as the hero gallery). Falls
 * back to a botanical panel if none is set. Copy and button link are
 * hardcoded below.
 */

require_once __DIR__ . '/../function/banner.php';

$ctaBanners = getActiveBanners($conn, 'detox_consultation_cta');
$ctaImage = null;

if (!empty($ctaBanners)) {
    $banner = $ctaBanners[0];
    $ctaImage = [
        'url' => getBannerImageUrl($banner['image']),
        'alt' => $banner['title'] ?: 'Book a consultation with our Gut Health Expert',
    ];
}

$ctaEyebrow       = 'Personalised Consultation';
$ctaHeadingMain   = 'Not Sure Where To';
$ctaHeadingAccent = 'Start?';
$ctaHighlight     = "Maharishi Ayurveda's Gut Health Expert";
$ctaTextBefore    = 'Book a consultation with ';
$ctaTextAfter     = '. Get recommendations based on your Dosha and which course of action is right for you.';
$ctaButtonLabel   = 'Book Your Consultation Now';
$ctaButtonUrl     = '#consultation';

// Supporting points shown under the paragraph. Set to [] to hide the row.
$ctaFeatures = [
    [
        'icon'  => 'guidance',
        'title' => 'Expert Guidance',
        'desc'  => 'Get personalised advice from certified Ayurvedic practitioners.',
    ],
    [
        'icon'  => 'root',
        'title' => 'Root Cause Analysis',
        'desc'  => 'Understand your unique constitution and imbalance.',
    ],
    [
        'icon'  => 'tailored',
        'title' => 'Tailored Recommendations',
        'desc'  => 'Find the right programme for your needs.',
    ],
];

$ctaIcons = [
    'guidance' => '<circle cx="12" cy="7" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/><path d="M12 16.2v3.6M10.2 18h3.6"/>',
    'root'     => '<path d="M12 21v-9"/><path d="M12 12c0-4 3-6 7-6 0 4-3 6-7 6z"/><path d="M12 15.5c0-3-2-5-6-5 0 3 2 5 6 5z"/>',
    'tailored' => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4h6v3H9z"/><path d="m9 14.2 2 2 4-4"/>',
];

if (!function_exists('detoxE')) {
    function detoxE($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
?>

<style>
    /* Design tokens: fall back to the approved brand palette.
       Change --dx-heading-font to your homepage heading font variable,
       and --dx-container to match your .container max-width. */
    .detox-cta {
        --dx-primary: var(--color-primary, #1f3a32);
        --dx-primary-dark: var(--color-primary-dark, #142a24);
        --dx-primary-light: var(--color-primary-light, #edf1e8);
        --dx-accent: var(--color-accent, #b28a32);
        --dx-bg: var(--color-bg, #f8f6ef);
        --dx-heading-font: var(--font-heading, "Cormorant Garamond", "Playfair Display", Georgia, "Times New Roman", serif);
        --dx-container: 1240px;
    }

    .detox-cta {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(60% 90% at 15% 0%, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0) 70%),
            linear-gradient(135deg, var(--dx-primary) 0%, var(--dx-primary-dark) 100%);
        color: var(--dx-bg);
    }

    .detox-cta__sprite {
        position: absolute;
        width: 0;
        height: 0;
        overflow: hidden;
    }

    .detox-cta__leaf {
        position: absolute;
        z-index: 0;
        color: #f8f6ef;
        opacity: 0.08;
        pointer-events: none;
    }

    .detox-cta__leaf--tl {
        top: -18px;
        left: -20px;
        width: clamp(90px, 10vw, 150px);
        transform: rotate(-150deg);
    }

    .detox-cta__leaf--bl {
        bottom: -30px;
        left: 34%;
        width: clamp(70px, 7vw, 110px);
        transform: rotate(28deg);
    }

    .detox-cta__grid {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
        align-items: stretch;
        min-height: clamp(520px, 40vw, 600px);
    }

    /* ---------------------------------------------------------------
       Copy
       --------------------------------------------------------------- */
    .detox-cta__content {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
        max-width: 100%;
        padding:
            clamp(56px, 6vw, 88px) clamp(28px, 3vw, 48px) clamp(56px, 6vw, 88px) max(24px, calc((100vw - var(--dx-container)) / 2 + 24px));
    }

    .detox-cta__eyebrow {
        display: flex;
        align-items: center;
        gap: 14px;
        margin: 0;
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0.2em;
        line-height: 1.5;
        text-transform: uppercase;
        white-space: nowrap;
        color: rgba(248, 246, 239, 0.72);
    }

    .detox-cta__eyebrow::after {
        content: "";
        flex: 0 1 clamp(28px, 4vw, 48px);
        min-width: 0;
        height: 1px;
        background: var(--dx-accent);
    }

    .detox-cta__heading {
        margin: 18px 0 0;
        max-width: 15ch;
        font-family: var(--dx-heading-font);
        font-size: clamp(2.4rem, 4.3vw, 3.7rem);
        font-weight: 700;
        line-height: 1.06;
        letter-spacing: -0.01em;
        color: #f8f6ef;
    }

    .detox-cta__heading em {
        font-style: italic;
        color: var(--dx-accent);
    }

    .detox-cta__text {
        margin: 20px 0 0;
        max-width: 46ch;
        font-size: clamp(1rem, 1.2vw, 1.06rem);
        line-height: 1.7;
        color: rgba(248, 246, 239, 0.82);
    }

    .detox-cta__text strong {
        font-weight: 600;
        color: #f8f6ef;
    }

    /* Supporting points */
    .detox-cta__features {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 28px;
        width: 100%;
        max-width: 620px;
        margin: 34px 0 0;
        padding: 30px 0 0;
        list-style: none;
        border-top: 1px solid rgba(248, 246, 239, 0.14);
    }

    .detox-cta__feature svg {
        display: block;
        width: 28px;
        height: 28px;
        color: var(--dx-accent);
    }

    .detox-cta__feature h3 {
        margin: 14px 0 0;
        font-size: 15.5px;
        font-weight: 600;
        line-height: 1.35;
        color: #f8f6ef;
    }

    .detox-cta__feature p {
        margin: 6px 0 0;
        font-size: 14px;
        line-height: 1.55;
        color: rgba(248, 246, 239, 0.7);
    }

    /* Button */
    .detox-cta__button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-top: 34px;
        padding: 16px 30px;
        border-radius: 999px;
        background: var(--dx-accent);
        color: var(--dx-primary-dark);
        font-size: 15.5px;
        font-weight: 600;
        text-decoration: none;
        transition: background-color 0.25s ease, transform 0.25s ease;
    }

    .detox-cta__button svg {
        width: 18px;
        height: 18px;
        flex: 0 0 auto;
        transition: transform 0.25s ease;
    }

    .detox-cta__button:hover {
        background: #f8f6ef;
        transform: translateY(-1px);
    }

    .detox-cta__button:hover svg {
        transform: translateX(3px);
    }

    .detox-cta__button:focus-visible {
        outline: 3px solid #f8f6ef;
        outline-offset: 3px;
    }

    /* ---------------------------------------------------------------
       Image (curved seam on desktop, curved bottom edge on mobile)
       --------------------------------------------------------------- */
    .detox-cta__image {
        position: relative;
        min-height: 100%;
        clip-path: url(#detoxCtaClipDesktop);
        background: linear-gradient(160deg, #2b4a40 0%, var(--dx-primary-dark) 100%);
    }

    .detox-cta__image img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: 60% 30%;
        display: block;
    }

    /* soft blend into the green at the seam */
    .detox-cta__image::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(20, 42, 36, 0.4) 0%, rgba(20, 42, 36, 0) 28%);
        pointer-events: none;
    }

    .detox-cta__placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: flex-end;
        padding: clamp(20px, 3vw, 36px);
        padding-left: 22%;
        font-size: 13px;
        line-height: 1.5;
        color: rgba(248, 246, 239, 0.7);
        overflow: hidden;
    }

    .detox-cta__placeholder p {
        position: relative;
        max-width: 34ch;
        margin: 0;
    }

    .detox-cta__placeholder .detox-cta__leaf {
        opacity: 0.1;
    }

    .detox-cta__placeholder .detox-cta__leaf--a {
        top: 12%;
        right: 12%;
        width: clamp(110px, 14vw, 200px);
        transform: rotate(24deg);
    }

    .detox-cta__placeholder .detox-cta__leaf--b {
        top: 40%;
        left: 30%;
        width: clamp(80px, 9vw, 130px);
        transform: rotate(-20deg);
    }

    /* ---------------------------------------------------------------
       Responsive
       --------------------------------------------------------------- */

    /* Narrow desktop / tablet landscape: features become a tidy list */
    @media (min-width: 900px) and (max-width: 1199px) {
        .detox-cta__features {
            grid-template-columns: minmax(0, 1fr);
            gap: 22px;
        }

        .detox-cta__feature {
            display: grid;
            grid-template-columns: 28px minmax(0, 1fr);
            column-gap: 16px;
            align-items: start;
        }

        .detox-cta__feature svg {
            grid-row: 1 / span 2;
            margin-top: 2px;
        }

        .detox-cta__feature h3 {
            margin-top: 0;
        }
    }

    /* Tablet portrait / phone: image on top, copy below */
    @media (max-width: 899px) {
        .detox-cta__grid {
            grid-template-columns: minmax(0, 1fr);
            min-height: 0;
        }

        .detox-cta__image {
            order: -1;
            min-height: 0;
            aspect-ratio: 4 / 3.2;
            max-height: 460px;
            width: 100%;
            clip-path: url(#detoxCtaClipMobile);
        }

        .detox-cta__image img {
            object-position: 50% 22%;
        }

        .detox-cta__image::after {
            background: linear-gradient(180deg, rgba(20, 42, 36, 0) 60%, rgba(20, 42, 36, 0.35) 100%);
        }

        .detox-cta__placeholder {
            padding-left: clamp(20px, 3vw, 36px);
        }

        .detox-cta__content {
            padding: 12px clamp(20px, 5vw, 48px) 56px;
        }

        .detox-cta__leaf--bl {
            display: none;
        }
    }

    @media (max-width: 599px) {
        .detox-cta__image {
            aspect-ratio: 1 / 0.95;
        }

        .detox-cta__heading {
            max-width: none;
        }

        .detox-cta__features {
            grid-template-columns: minmax(0, 1fr);
            gap: 22px;
        }

        .detox-cta__feature {
            display: grid;
            grid-template-columns: 28px minmax(0, 1fr);
            column-gap: 16px;
            align-items: start;
        }

        .detox-cta__feature svg {
            grid-row: 1 / span 2;
            margin-top: 2px;
        }

        .detox-cta__feature h3 {
            margin-top: 0;
        }

        .detox-cta__button {
            width: 100%;
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .detox-cta__button,
        .detox-cta__button svg {
            transition: none;
        }

        .detox-cta__button:hover,
        .detox-cta__button:hover svg {
            transform: none;
        }
    }
</style>

<section class="detox-cta" aria-labelledby="detoxCtaHeading">
    <!-- Sprig + clip shapes (objectBoundingBox units, so they scale with the image) -->
    <svg class="detox-cta__sprite" aria-hidden="true" focusable="false">
        <defs>
            <clipPath id="detoxCtaClipDesktop" clipPathUnits="objectBoundingBox">
                <path d="M0.14 0 L1 0 L1 1 L0.12 1 C0.16 0.82 0.03 0.70 0.02 0.52 C0.01 0.34 0.13 0.20 0.14 0 Z" />
            </clipPath>
            <clipPath id="detoxCtaClipMobile" clipPathUnits="objectBoundingBox">
                <path d="M0 0 L1 0 L1 0.93 C0.8 1 0.55 0.9 0.3 0.96 C0.18 0.99 0.08 0.96 0 0.93 Z" />
            </clipPath>
        </defs>
        <symbol id="detox-cta-sprig" viewBox="0 0 120 200">
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
    </svg>

    <svg class="detox-cta__leaf detox-cta__leaf--tl" viewBox="0 0 120 200" aria-hidden="true">
        <use href="#detox-cta-sprig" />
    </svg>
    <svg class="detox-cta__leaf detox-cta__leaf--bl" viewBox="0 0 120 200" aria-hidden="true">
        <use href="#detox-cta-sprig" />
    </svg>

    <div class="detox-cta__grid">

        <div class="detox-cta__content">
            <p class="detox-cta__eyebrow"><?= detoxE($ctaEyebrow) ?></p>

            <h2 class="detox-cta__heading" id="detoxCtaHeading">
                <?= detoxE($ctaHeadingMain) ?> <em><?= detoxE($ctaHeadingAccent) ?></em>
            </h2>

            <p class="detox-cta__text">
                <?= detoxE($ctaTextBefore) ?><strong><?= detoxE($ctaHighlight) ?></strong><?= detoxE($ctaTextAfter) ?>
            </p>

            <?php if (!empty($ctaFeatures)): ?>
                <ul class="detox-cta__features">
                    <?php foreach ($ctaFeatures as $feature): ?>
                        <li class="detox-cta__feature">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <?= $ctaIcons[$feature['icon']] ?? $ctaIcons['guidance'] ?>
                            </svg>
                            <h3><?= detoxE($feature['title']) ?></h3>
                            <p><?= detoxE($feature['desc']) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <a class="detox-cta__button" href="<?= detoxE($ctaButtonUrl) ?>">
                <?= detoxE($ctaButtonLabel) ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
            </a>
        </div>

        <div class="detox-cta__image">
            <?php if ($ctaImage): ?>
                <img src="<?= detoxE($ctaImage['url']) ?>"
                    alt="<?= detoxE($ctaImage['alt']) ?>">
            <?php else: ?>
                <div class="detox-cta__placeholder">
                    <svg class="detox-cta__leaf detox-cta__leaf--a" viewBox="0 0 120 200" aria-hidden="true">
                        <use href="#detox-cta-sprig" />
                    </svg>
                    <svg class="detox-cta__leaf detox-cta__leaf--b" viewBox="0 0 120 200" aria-hidden="true">
                        <use href="#detox-cta-sprig" />
                    </svg>
                    <p>
                        No image yet — add one in Banner Management with
                        position <strong>detox_consultation_cta</strong>.
                    </p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>