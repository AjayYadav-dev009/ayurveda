<?php

/**
 * "About Ayurveda" intro section for the homepage.
 * Static content — styled to match the site's existing design tokens
 * (see includes/hero.php's slider for the same --color-* variables).
 */

$aboutEyebrow    = 'About Ayurveda';
$aboutTitle      = 'Ancient Wisdom for Modern Living';
$aboutText       = 'We are more than a wellness brand — we are a team of Ayurvedic practitioners, healers, and wellness enthusiasts on a mission to bring the timeless wisdom of Ayurveda into your everyday life.';
$aboutTagline    = 'Rooted in tradition. Designed for you.';

// TODO: replace with the real photo once it's added to assets/images/.
$aboutImage      = 'assets/images/about-ayurveda.jpg';
$aboutImageAlt   = 'Traditional stone mortar and pestle surrounded by fresh Ayurvedic herbs';

if (!function_exists('aboutE')) {
    function aboutE($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
?>

<style>
    /* ==========================================================================
       About Ayurveda intro section.
       Uses the same --color-* / --font-heading tokens as the consultation
       hero slider, so it stays visually consistent with the rest of the site.
       ========================================================================== */

    .about-ayu {
        --ayu-primary: var(--color-primary, #1f3a32);
        --ayu-accent: var(--color-accent, #b28a32);
        --ayu-accent-text: #8a6a22;
        --ayu-bg: var(--color-bg, #f3f1e6);
        --ayu-text: var(--color-text, #2a352f);
        --ayu-muted: var(--color-text-light, #5f6a63);
        --ayu-heading-font: var(--font-heading, "Cormorant Garamond", "Playfair Display", Georgia, "Times New Roman", serif);
        position: relative;
        background: var(--ayu-bg);
        overflow: hidden;
    }

    .about-ayu__grid {
        position: relative;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr);
        align-items: stretch;
        min-height: clamp(360px, 32vw, 460px);
    }

    /* ---- Faint decorative leaves behind the text ---------------------------- */
    .about-ayu__leaf {
        position: absolute;
        z-index: 0;
        color: var(--ayu-primary);
        opacity: 0.1;
        pointer-events: none;
    }

    .about-ayu__leaf--top {
        left: -18px;
        top: -10px;
        width: clamp(70px, 7vw, 100px);
        transform: rotate(-18deg);
    }

    .about-ayu__leaf--bottom {
        left: -14px;
        bottom: -18px;
        width: clamp(64px, 6vw, 92px);
        transform: rotate(24deg) scaleX(-1);
    }

    /* ---- Text column --------------------------------------------------------- */
    .about-ayu__content {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: flex-start;
        padding:
            clamp(36px, 5vw, 64px) clamp(24px, 3vw, 48px) clamp(36px, 5vw, 64px) max(24px, calc((100vw - 1240px) / 2 + 24px));
    }

    .about-ayu__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: var(--ayu-accent-text);
    }

    .about-ayu__eyebrow svg {
        width: 12px;
        height: 12px;
        flex: 0 0 auto;
    }

    .about-ayu__title {
        margin: 14px 0 0;
        max-width: 10em;
        font-family: var(--ayu-heading-font);
        font-size: clamp(1.9rem, 3.2vw, 2.75rem);
        font-weight: 700;
        line-height: 1.18;
        letter-spacing: -0.01em;
        text-wrap: balance;
        color: var(--ayu-primary);
    }

    .about-ayu__text {
        margin: 16px 0 0;
        max-width: 42ch;
        font-size: clamp(0.9rem, 1vw, 0.98rem);
        line-height: 1.7;
        color: var(--ayu-text);
        opacity: 0.85;
    }

    .about-ayu__divider {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 22px;
    }

    .about-ayu__divider-line {
        width: 34px;
        height: 1px;
        background: var(--ayu-accent);
        flex: 0 0 auto;
    }

    .about-ayu__divider svg {
        width: 18px;
        height: 18px;
        flex: 0 0 auto;
        color: var(--ayu-accent);
    }

    .about-ayu__tagline {
        margin: 0;
        font-family: var(--ayu-heading-font);
        font-style: italic;
        font-size: 0.98rem;
        color: var(--ayu-accent-text);
    }

    /* ---- Photo, curved on the left edge -------------------------------------- */
    .about-ayu__media {
        position: relative;
        z-index: 1;
    }

    .about-ayu__photo {
        position: absolute;
        inset: 0;
        overflow: hidden;
        border-radius: clamp(60px, 8vw, 120px) 0 0 clamp(60px, 8vw, 120px);
        background: #d9d4bf;
    }

    .about-ayu__image {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }

    /* ---- Tablet / mobile: stack -------------------------------------------- */
    @media (max-width: 899px) {
        .about-ayu__grid {
            grid-template-columns: minmax(0, 1fr);
            min-height: 0;
        }

        .about-ayu__content {
            padding: 40px 24px 28px;
        }

        .about-ayu__title {
            max-width: 14em;
        }

        .about-ayu__media {
            height: clamp(220px, 60vw, 360px);
        }

        .about-ayu__photo {
            border-radius: 0;
        }
    }
</style>

<section class="about-ayu" aria-label="About Ayurveda">
    <svg class="about-ayu__leaf about-ayu__leaf--top" viewBox="0 0 120 200" aria-hidden="true">
        <path d="M60 198C54 140 66 80 60 8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
        <g fill="currentColor">
            <ellipse cx="86" cy="150" rx="26" ry="10" transform="rotate(-38 86 150)" />
            <ellipse cx="34" cy="126" rx="26" ry="10" transform="rotate(34 34 126)" />
            <ellipse cx="88" cy="100" rx="25" ry="9.5" transform="rotate(-34 88 100)" />
            <ellipse cx="34" cy="76" rx="24" ry="9.5" transform="rotate(32 34 76)" />
            <ellipse cx="84" cy="50" rx="21" ry="8.5" transform="rotate(-32 84 50)" />
        </g>
    </svg>
    <svg class="about-ayu__leaf about-ayu__leaf--bottom" viewBox="0 0 120 200" aria-hidden="true">
        <path d="M60 198C54 140 66 80 60 8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
        <g fill="currentColor">
            <ellipse cx="86" cy="150" rx="26" ry="10" transform="rotate(-38 86 150)" />
            <ellipse cx="34" cy="126" rx="26" ry="10" transform="rotate(34 34 126)" />
            <ellipse cx="88" cy="100" rx="25" ry="9.5" transform="rotate(-34 88 100)" />
        </g>
    </svg>

    <div class="about-ayu__grid">
        <div class="about-ayu__content">
            <p class="about-ayu__eyebrow">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="1.5" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                <?= aboutE($aboutEyebrow) ?>
            </p>

            <h2 class="about-ayu__title"><?= aboutE($aboutTitle) ?></h2>

            <p class="about-ayu__text"><?= aboutE($aboutText) ?></p>

            <div class="about-ayu__divider">
                <span class="about-ayu__divider-line" aria-hidden="true"></span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 20c-4-1-7-4-7-9 4 0 7 2 7 6 0-4 3-6 7-6 0 5-3 8-7 9Z" />
                </svg>
                <p class="about-ayu__tagline"><?= aboutE($aboutTagline) ?></p>
            </div>
        </div>

        <div class="about-ayu__media">
            <div class="about-ayu__photo">
                <img class="about-ayu__image"
                    src="<?php echo BASE_URL; ?><?= aboutE($aboutImage) ?>"
                    alt="<?= aboutE($aboutImageAlt) ?>"
                    loading="lazy">
            </div>
        </div>
    </div>
</section>