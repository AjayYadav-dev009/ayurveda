<?php

/**
 * Closing CTA banner for the consult-veda flow.
 *
 * Static content — copy, button and photo pulled out to variables so
 * they're easy to tweak without touching the markup below.
 *
 * Usage (from consult-veda/index.php, after $conn is available):
 *   include __DIR__ . '/cta-banner.php';
 */

$ctaEyebrow     = 'Get Expert Advice';
$ctaHeading     = 'Not Sure Where To Start?';
$ctaText        = 'Book a consultation with an Ayurvedic expert and understand whether this care is right for you.';
$ctaButtonLabel = 'Book a Consultation';
$ctaButtonHref  = '#book-now';

// Photo on the right (the Vaidya). Full URL, or a path under BASE_URL such
// as 'assets/images/cta-vaidya.jpg'. Leave '' to show the banner without it.
$ctaImage = '';

if ($ctaImage !== '' && !preg_match('~^(https?:)?//~i', $ctaImage) && $ctaImage[0] !== '/') {
    $ctaImage = rtrim(defined('BASE_URL') ? (string) BASE_URL : '', '/') . '/' . ltrim($ctaImage, '/');
}

if (!function_exists('ctaE')) {
    function ctaE($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
?>

<style>
    .cta-banner {
        --cta-gold: #c9a04a;
        --cta-gold-hover: #d8b25c;
        --cta-heading-font: var(--font-heading, "Cormorant Garamond", "Playfair Display", Georgia, "Times New Roman", serif);
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(60% 140% at 22% 50%, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0) 70%),
            var(--color-primary, #1f3a32);
        color: #ffffff;
    }

    .cta-banner-sprite {
        position: absolute;
        width: 0;
        height: 0;
        overflow: hidden;
    }

    .cta-banner-leaf {
        position: absolute;
        z-index: 0;
        color: #cfe3d0;
        opacity: 0.1;
        pointer-events: none;
    }

    .cta-banner-leaf--l {
        left: -14px;
        bottom: -16px;
        width: clamp(70px, 7vw, 110px);
        transform: rotate(24deg) scaleX(-1);
    }

    .cta-banner-leaf--r {
        right: -12px;
        top: 18%;
        width: clamp(70px, 7vw, 110px);
        transform: rotate(-22deg);
    }

    .cta-banner__inner {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
        gap: clamp(20px, 4vw, 64px);
        max-width: var(--container-width, 1200px);
        min-height: clamp(280px, 24vw, 380px);
        margin: 0 auto;
        padding-left: 24px;
    }

    .cta-banner--no-image .cta-banner__inner {
        grid-template-columns: minmax(0, 1fr);
        padding-right: 24px;
    }

    .cta-banner__content {
        align-self: center;
        padding: clamp(36px, 4.5vw, 56px) 0;
    }

    .cta-banner__eyebrow {
        margin: 0;
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--cta-gold);
    }

    .cta-banner__heading {
        margin: 12px 0 0;
        max-width: 9em;
        font-family: var(--cta-heading-font);
        font-size: clamp(2rem, 3.4vw, 3rem);
        font-weight: 700;
        line-height: 1.1;
        letter-spacing: -0.01em;
        text-wrap: balance;
        color: #ffffff;
    }

    .cta-banner__text {
        margin: 16px 0 0;
        max-width: 40ch;
        font-size: clamp(0.95rem, 1.1vw, 1.02rem);
        line-height: 1.65;
        color: rgba(255, 255, 255, 0.82);
    }

    .cta-banner-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-top: 26px;
        padding: 14px 28px;
        border-radius: 999px;
        background: var(--cta-gold);
        color: #14261f;
        font-size: 0.95rem;
        font-weight: 600;
        line-height: 1.2;
        text-decoration: none;
        transition: background-color 0.25s ease;
    }

    .cta-banner-btn svg {
        width: 16px;
        height: 16px;
        flex: 0 0 auto;
        transition: transform 0.25s ease;
    }

    .cta-banner-btn:hover {
        background: var(--cta-gold-hover);
    }

    .cta-banner-btn:hover svg {
        transform: translateX(3px);
    }

    .cta-banner-btn:focus-visible {
        outline: 3px solid #ffffff;
        outline-offset: 3px;
    }

    /* ---- Photo: full height of the band, curved on the left ---------------- */
    .cta-banner__media {
        position: relative;
        overflow: hidden;
        border-radius: 34% 24px 24px 34% / 50% 24px 24px 50%;
        background: #2b4a40;
    }

    .cta-banner__media img {
        position: absolute;
        inset: 0;
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center top;
    }

    /* ---- Tablet / mobile: photo below the text ------------------------------------ */
    @media (max-width: 799px) {
        .cta-banner__inner {
            grid-template-columns: minmax(0, 1fr);
            gap: 0;
            min-height: 0;
            padding-right: 24px;
        }

        .cta-banner__media {
            height: clamp(220px, 58vw, 340px);
            margin: 0 -24px;
            border-radius: 50% 50% 0 0 / 22% 22% 0 0;
        }

        .cta-banner__content {
            padding-bottom: 32px;
        }
    }

    @media (max-width: 479px) {
        .cta-banner-btn {
            width: 100%;
            justify-content: center;
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .cta-banner-btn,
        .cta-banner-btn svg {
            transition: none;
        }

        .cta-banner-btn:hover svg {
            transform: none;
        }
    }
</style>

<section class="cta-banner<?= $ctaImage === '' ? ' cta-banner--no-image' : '' ?>" aria-labelledby="ctaBannerHeading">
    <svg class="cta-banner-sprite" aria-hidden="true" focusable="false">
        <symbol id="cta-banner-sprig" viewBox="0 0 120 200">
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
    <svg class="cta-banner-leaf cta-banner-leaf--l" viewBox="0 0 120 200" aria-hidden="true">
        <use href="#cta-banner-sprig" />
    </svg>
    <svg class="cta-banner-leaf cta-banner-leaf--r" viewBox="0 0 120 200" aria-hidden="true">
        <use href="#cta-banner-sprig" />
    </svg>

    <div class="cta-banner__inner">
        <div class="cta-banner__content">
            <p class="cta-banner__eyebrow"><?= ctaE($ctaEyebrow) ?></p>
            <h2 class="cta-banner__heading" id="ctaBannerHeading"><?= ctaE($ctaHeading) ?></h2>
            <p class="cta-banner__text"><?= ctaE($ctaText) ?></p>
            <a href="<?= ctaE($ctaButtonHref) ?>" class="cta-banner-btn">
                <span><?= ctaE($ctaButtonLabel) ?></span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
            </a>
        </div>

        <?php if ($ctaImage !== ''): ?>
            <div class="cta-banner__media">
                <img src="<?= ctaE($ctaImage) ?>" alt="" loading="lazy">
            </div>
        <?php endif; ?>
    </div>
</section>