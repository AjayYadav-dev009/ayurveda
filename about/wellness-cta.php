<?php
/**
 * "Your Wellness Journey Begins Here" closing CTA section for the homepage.
 * Static content — shares the same --color-* / --font-heading tokens as the
 * other homepage sections, but reverses to the dark palette for contrast.
 */

$ctaEyebrow    = "Let's Get Started";
$ctaTitle      = 'Your Wellness Journey Begins Here';
$ctaText       = 'Take the first step towards better health. Book a consultation with our Ayurvedic experts today.';
$ctaBtnLabel   = 'Book a Consultation';
$ctaBtnUrl     = '#';

// TODO: replace with the real photo once it's added to assets/images/.
$ctaImage      = 'assets/images/wellness-cta.jpg';
$ctaImageAlt   = 'Brass mortar and pestle with fresh herbs on a wooden table';

if (!function_exists('ctaE')) {
    function ctaE($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
?>

<style>
    /* ==========================================================================
       Closing CTA — dark panel.
       Uses the same --color-* / --font-heading tokens as the other sections,
       switched to light-on-dark for contrast as a closing section.
       ========================================================================== */

    .wellness-cta {
        --cta-bg: var(--color-primary-dark, #142a24);
        --cta-accent: var(--color-accent, #b28a32);
        --cta-accent-strong: #d1a94a;
        --cta-heading-font: var(--font-heading, "Cormorant Garamond", "Playfair Display", Georgia, "Times New Roman", serif);
        position: relative;
        background: var(--cta-bg);
        overflow: hidden;
    }

    .wellness-cta__leaf {
        position: absolute;
        z-index: 0;
        top: 50%;
        right: clamp(120px, 20vw, 300px);
        width: clamp(220px, 26vw, 340px);
        height: clamp(220px, 26vw, 340px);
        transform: translateY(-50%);
        color: #ffffff;
        opacity: 0.08;
        pointer-events: none;
    }

    .wellness-cta__grid {
        position: relative;
        z-index: 1;
        max-width: 1240px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
        align-items: center;
        gap: clamp(28px, 5vw, 56px);
        padding: clamp(40px, 6vw, 72px) clamp(24px, 3vw, 48px);
    }

    .wellness-cta__eyebrow {
        margin: 0;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: var(--cta-accent-strong);
    }

    .wellness-cta__title {
        margin: 14px 0 0;
        max-width: 11em;
        font-family: var(--cta-heading-font);
        font-size: clamp(1.9rem, 3.2vw, 2.6rem);
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: -0.01em;
        text-wrap: balance;
        color: #ffffff;
    }

    .wellness-cta__text {
        margin: 16px 0 0;
        max-width: 42ch;
        font-size: 0.92rem;
        line-height: 1.65;
        color: #ffffff;
        opacity: 0.75;
    }

    .wellness-cta__btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-top: 26px;
        padding: 14px 28px;
        border-radius: 999px;
        background: var(--cta-accent);
        color: #ffffff;
        font-size: 0.95rem;
        font-weight: 500;
        line-height: 1.2;
        text-decoration: none;
        transition: background-color 0.25s ease;
    }

    .wellness-cta__btn svg {
        width: 16px;
        height: 16px;
        flex: 0 0 auto;
        transition: transform 0.25s ease;
    }

    .wellness-cta__btn:hover {
        background: var(--cta-accent-strong);
    }

    .wellness-cta__btn:hover svg {
        transform: translateX(3px);
    }

    .wellness-cta__btn:focus-visible {
        outline: 3px solid #ffffff;
        outline-offset: 3px;
    }

    /* ---- Circular photo -------------------------------------------------------- */
    .wellness-cta__media {
        position: relative;
        justify-self: center;
        width: clamp(200px, 22vw, 280px);
        aspect-ratio: 1 / 1;
    }

    .wellness-cta__ring {
        position: absolute;
        inset: -18px;
        border-radius: 50%;
        border: 1px dashed rgba(255, 255, 255, 0.3);
        pointer-events: none;
    }

    .wellness-cta__photo {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        overflow: hidden;
        background: #2a4740;
    }

    .wellness-cta__image {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }

    /* ---- Tablet / mobile: stack ------------------------------------------------ */
    @media (max-width: 899px) {
        .wellness-cta__grid {
            grid-template-columns: minmax(0, 1fr);
            text-align: center;
        }

        .wellness-cta__title {
            max-width: none;
        }

        .wellness-cta__text {
            max-width: none;
            margin-left: auto;
            margin-right: auto;
        }

        .wellness-cta__media {
            order: -1;
        }
    }

    @media (max-width: 480px) {
        .wellness-cta__btn {
            width: 100%;
            justify-content: center;
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .wellness-cta__btn,
        .wellness-cta__btn svg {
            transition: none;
        }

        .wellness-cta__btn:hover svg {
            transform: none;
        }
    }
</style>

<section class="wellness-cta" aria-label="Book a consultation">
    <svg class="wellness-cta__leaf" viewBox="0 0 120 200" aria-hidden="true">
        <path d="M60 198C54 140 66 80 60 8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
        <g fill="currentColor">
            <ellipse cx="86" cy="150" rx="26" ry="10" transform="rotate(-38 86 150)" />
            <ellipse cx="34" cy="126" rx="26" ry="10" transform="rotate(34 34 126)" />
            <ellipse cx="88" cy="100" rx="25" ry="9.5" transform="rotate(-34 88 100)" />
            <ellipse cx="34" cy="76" rx="24" ry="9.5" transform="rotate(32 34 76)" />
            <ellipse cx="84" cy="50" rx="21" ry="8.5" transform="rotate(-32 84 50)" />
            <ellipse cx="40" cy="30" rx="18" ry="7.5" transform="rotate(30 40 30)" />
        </g>
    </svg>

    <div class="wellness-cta__grid">
        <div class="wellness-cta__content">
            <p class="wellness-cta__eyebrow"><?= ctaE($ctaEyebrow) ?></p>
            <h2 class="wellness-cta__title"><?= ctaE($ctaTitle) ?></h2>
            <p class="wellness-cta__text"><?= ctaE($ctaText) ?></p>

            <a class="wellness-cta__btn" href="<?= ctaE($ctaBtnUrl) ?>">
                <span><?= ctaE($ctaBtnLabel) ?></span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
            </a>
        </div>

        <div class="wellness-cta__media">
            <div class="wellness-cta__ring" aria-hidden="true"></div>
            <div class="wellness-cta__photo">
                <img class="wellness-cta__image"
                    src="<?php echo BASE_URL; ?><?= ctaE($ctaImage) ?>"
                    alt="<?= ctaE($ctaImageAlt) ?>"
                    loading="lazy">
            </div>
        </div>
    </div>
</section>
