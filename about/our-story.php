<?php

/**
 * "Our Story" section for the homepage.
 * Static content — shares the same --color-* / --font-heading tokens as
 * about-ayurveda.php and the consultation hero slider.
 */

$storyEyebrow  = 'Our Story';
$storyTitle    = 'A Journey Rooted in Ayurveda';
$storyPara1    = 'Ayurveda was born from a deep understanding of nature, life, and balance. For centuries, it has guided people towards better health, inner peace, and a higher quality of life.';
$storyPara2    = 'At Ayurveda, we carry this legacy forward — combining ancient knowledge with modern expertise to create personalised wellness solutions for today\'s world.';
$storyBtnLabel = 'Our Mission';
$storyBtnUrl   = '#';

// TODO: replace with the real photo once it's added to assets/images/.
$storyImage    = 'assets/images/our-story.jpg';
$storyImageAlt = 'Woman sitting on a garden porch, holding a warm drink and looking out at greenery';

if (!function_exists('storyE')) {
    function storyE($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
?>

<style>
    /* ==========================================================================
       Our Story section.
       Uses the same --color-* / --font-heading tokens as about-ayurveda.php.
       ========================================================================== */

    .our-story {
        --story-primary: var(--color-primary, #1f3a32);
        --story-accent: var(--color-accent, #b28a32);
        --story-accent-strong: #94701f;
        --story-accent-text: #8a6a22;
        --story-bg: var(--color-bg, #f3f1e6);
        --story-text: var(--color-text, #2a352f);
        --story-muted: var(--color-text-light, #5f6a63);
        --story-heading-font: var(--font-heading, "Cormorant Garamond", "Playfair Display", Georgia, "Times New Roman", serif);
        position: relative;
        background: var(--story-bg);
        overflow: hidden;
    }

    .our-story__grid {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr);
        align-items: center;
        gap: clamp(32px, 5vw, 64px);
        max-width: 1240px;
        margin: 0 auto;
        padding: clamp(40px, 6vw, 72px) clamp(24px, 3vw, 48px);
    }

    /* ---- Faint decorative sprig, bottom right of the section ---------------- */
    .our-story__leaf {
        position: absolute;
        z-index: 0;
        right: -10px;
        bottom: -20px;
        width: clamp(80px, 8vw, 120px);
        color: var(--story-primary);
        opacity: 0.1;
        pointer-events: none;
        transform: rotate(8deg);
    }

    /* ---- Photo card ----------------------------------------------------------- */
    .our-story__media {
        border-radius: 20px;
        overflow: hidden;
        aspect-ratio: 4 / 3;
        background: #d9d4bf;
    }

    .our-story__image {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }

    /* ---- Text column ----------------------------------------------------------- */
    .our-story__content {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .our-story__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: var(--story-accent-text);
    }

    .our-story__eyebrow svg {
        width: 12px;
        height: 12px;
        flex: 0 0 auto;
    }

    .our-story__eyebrow-line {
        width: 34px;
        height: 1px;
        background: var(--story-accent);
        flex: 0 0 auto;
    }

    .our-story__title {
        margin: 14px 0 0;
        max-width: 11em;
        font-family: var(--story-heading-font);
        font-size: clamp(1.9rem, 3.2vw, 2.75rem);
        font-weight: 700;
        line-height: 1.18;
        letter-spacing: -0.01em;
        text-wrap: balance;
        color: var(--story-primary);
    }

    .our-story__text {
        margin: 18px 0 0;
        max-width: 46ch;
        font-size: clamp(0.9rem, 1vw, 0.98rem);
        line-height: 1.7;
        color: var(--story-text);
        opacity: 0.85;
    }

    .our-story__text+.our-story__text {
        margin-top: 14px;
    }

    .our-story__btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-top: 26px;
        padding: 13px 26px;
        border-radius: 999px;
        background: var(--story-accent-strong);
        color: #ffffff;
        font-size: 0.9rem;
        font-weight: 500;
        line-height: 1.2;
        text-decoration: none;
        transition: background-color 0.25s ease;
    }

    .our-story__btn svg {
        width: 15px;
        height: 15px;
        flex: 0 0 auto;
        transition: transform 0.25s ease;
    }

    .our-story__btn:hover {
        background: #7a5d17;
    }

    .our-story__btn:hover svg {
        transform: translateX(3px);
    }

    .our-story__btn:focus-visible {
        outline: 3px solid var(--story-primary);
        outline-offset: 3px;
    }

    /* ---- Tablet / mobile: stack ------------------------------------------------ */
    @media (max-width: 899px) {
        .our-story__grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .our-story__title {
            max-width: 14em;
        }
    }

    @media (max-width: 559px) {
        .our-story__btn {
            width: 100%;
            justify-content: center;
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .our-story__btn,
        .our-story__btn svg {
            transition: none;
        }

        .our-story__btn:hover svg {
            transform: none;
        }
    }
</style>

<section class="our-story" aria-label="Our story">
    <svg class="our-story__leaf" viewBox="0 0 120 200" aria-hidden="true">
        <path d="M60 198C54 140 66 80 60 8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
        <g fill="currentColor">
            <ellipse cx="86" cy="150" rx="26" ry="10" transform="rotate(-38 86 150)" />
            <ellipse cx="34" cy="126" rx="26" ry="10" transform="rotate(34 34 126)" />
            <ellipse cx="88" cy="100" rx="25" ry="9.5" transform="rotate(-34 88 100)" />
            <ellipse cx="34" cy="76" rx="24" ry="9.5" transform="rotate(32 34 76)" />
            <ellipse cx="84" cy="50" rx="21" ry="8.5" transform="rotate(-32 84 50)" />
        </g>
    </svg>

    <div class="our-story__grid">
        <div class="our-story__media">
            <img class="our-story__image"
                src="<?php echo BASE_URL; ?><?= storyE($storyImage) ?>"
                alt="<?= storyE($storyImageAlt) ?>"
                loading="lazy">
        </div>

        <div class="our-story__content">
            <p class="our-story__eyebrow">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="1.5" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                <?= storyE($storyEyebrow) ?>
                <span class="our-story__eyebrow-line" aria-hidden="true"></span>
            </p>

            <h2 class="our-story__title"><?= storyE($storyTitle) ?></h2>

            <p class="our-story__text"><?= storyE($storyPara1) ?></p>
            <p class="our-story__text"><?= storyE($storyPara2) ?></p>

            <a class="our-story__btn" href="<?= storyE($storyBtnUrl) ?>">
                <span><?= storyE($storyBtnLabel) ?></span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
            </a>
        </div>
    </div>
</section>