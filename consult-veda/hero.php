<?php

if (!function_exists('getActiveBanners')) {
    require_once __DIR__ . '/../function/banner.php';
}

try {
    $heroBanners = getActiveBanners($conn, 'consult_veda_hero');
} catch (Exception $e) {
    // Fail closed: hide the hero rather than show a broken slider.
    $heroBanners = [];
}

// ---------------------------------------------------------------------------
// Copy used when a banner row leaves a field empty. Edit freely.
// ---------------------------------------------------------------------------
$heroEyebrow       = 'Ayurvedic Consultation';
$heroDefaultTitle  = 'Personalised Ayurvedic Care, Rooted in You';
$heroDefaultText   = 'Share your health concerns with our experienced Ayurvedic practitioners and get a personalised plan for lasting wellness — naturally.';
$heroDefaultButton = 'Book a Consultation';

// Trust points under the button. `icon` is trusted inline SVG markup.
$heroFeatures = [
    [
        'title' => 'Expert Guidance',
        'text'  => 'From certified Ayurvedic practitioners',
        'icon'  => '<path d="M5 19c0-8 5-14 15-14 0 10-6 15-14 15z"/><path d="M5 19c4-5 7-8 11-10"/>',
    ],
    [
        'title' => 'Trusted & Safe',
        'text'  => '100% authentic Ayurvedic practices',
        'icon'  => '<path d="M12 3 4.5 6v5.5c0 4.5 3.2 8.2 7.5 9.5 4.3-1.3 7.5-5 7.5-9.5V6L12 3Z"/><path d="m8.8 12.2 2.3 2.3 4.1-4.4"/>',
    ],
    [
        'title' => 'Holistic Healing',
        'text'  => 'For your mind, body and spirit',
        'icon'  => '<path d="M12 20.5s-7.5-4.6-7.5-10.2A4.3 4.3 0 0 1 12 7.6a4.3 4.3 0 0 1 7.5 2.7c0 5.6-7.5 10.2-7.5 10.2Z"/>',
    ],
];

if (!function_exists('heroE')) {
    function heroE($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

// Keep only banners that have a usable image, so the dots always match the
// number of slides that are really rendered.
$heroSlides = [];
foreach ($heroBanners as $banner) {
    $imageUrl = getBannerImageUrl($banner['image'] ?? null);
    if ($imageUrl === null) {
        continue;
    }

    $pick = static function (array $keys, string $default = '') use ($banner): string {
        foreach ($keys as $key) {
            $value = trim((string) ($banner[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }
        return $default;
    };

    $heroSlides[] = [
        'image'  => $imageUrl,
        'title'  => $pick(['title'], $heroDefaultTitle),
        'text'   => $pick(['description', 'subtitle'], $heroDefaultText),
        'button' => $pick(['button_text'], $heroDefaultButton),
        'url'    => $pick(['button_url']),
    ];
}

$heroBannerCount = count($heroSlides);
?>

<?php //if (!defined('GLOBAL_SLIDER_CSS_LOADED')): ?>
    <!-- <link rel="stylesheet" href="<?php //echo BASE_URL; ?>assets/css/slider.css"> -->
    <?php //define('GLOBAL_SLIDER_CSS_LOADED', true); ?>
<?php //endif; ?>

<style>
    /* ==========================================================================
       Consult Veda hero slider.
       Built on the shared slider mechanics (assets/css/slider.css); this file
       only styles the slide layout and the overlaid dots.
       ========================================================================== */

    .hero-slider {
        --slider-visible: 1;
        --hero-primary: var(--color-primary, #1f3a32);
        --hero-primary-dark: var(--color-primary-dark, #142a24);
        --hero-accent: var(--color-accent, #b28a32);
        --hero-accent-strong: #94701f;
        /* darker gold so white button text stays readable */
        --hero-accent-text: #82631a;
        --hero-bg: var(--color-bg, #f8f6ef);
        --hero-text: var(--color-text, #26342f);
        --hero-muted: var(--color-text-light, #5f6a63);
        --hero-heading-font: var(--font-heading, "Cormorant Garamond", "Playfair Display", Georgia, "Times New Roman", serif);
        position: relative;
        background:
            radial-gradient(55% 80% at 78% 30%, rgba(237, 241, 232, 0.85), rgba(237, 241, 232, 0) 70%),
            var(--hero-bg);
    }

    .hero-sprite {
        position: absolute;
        width: 0;
        height: 0;
        overflow: hidden;
    }

    /* ---- Slide layout: text | photo ---------------------------------------- */
    .hero-slide {
        position: relative;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr);
        min-height: clamp(440px, 39vw, 600px);
        overflow: hidden;
        line-height: 1.5;
    }

    .hero-leaf {
        position: absolute;
        z-index: 0;
        color: var(--hero-primary);
        opacity: 0.12;
        pointer-events: none;
    }

    .hero-leaf--top {
        left: -16px;
        top: 12%;
        width: clamp(64px, 6vw, 96px);
        transform: rotate(-14deg);
    }

    .hero-leaf--bottom {
        left: -12px;
        bottom: -14px;
        width: clamp(60px, 5.5vw, 88px);
        transform: rotate(22deg) scaleX(-1);
    }

    .hero-slide__content {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
        padding:
            clamp(40px, 5vw, 72px) clamp(24px, 3vw, 48px) clamp(40px, 5vw, 72px) max(24px, calc((100vw - 1240px) / 2 + 24px));
    }

    .hero-slide__eyebrow {
        margin: 0;
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--hero-accent-text);
    }

    .hero-slide__title {
        margin: 16px 0 0;
        max-width: 9.5em;
        font-family: var(--hero-heading-font);
        font-size: clamp(2.1rem, 3.7vw, 3.5rem);
        font-weight: 700;
        line-height: 1.1;
        letter-spacing: -0.01em;
        text-wrap: balance;
        color: var(--hero-primary);
    }

    .hero-slide__text {
        margin: 18px 0 0;
        max-width: 40ch;
        font-size: clamp(0.95rem, 1.1vw, 1.05rem);
        line-height: 1.7;
        color: var(--hero-text);
        opacity: 0.85;
    }

    .hero-slide__btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-top: 28px;
        padding: 14px 28px;
        border-radius: 999px;
        background: var(--hero-accent-strong);
        color: #ffffff;
        font-size: 0.95rem;
        font-weight: 500;
        line-height: 1.2;
        text-decoration: none;
        transition: background-color 0.25s ease;
    }

    .hero-slide__btn svg {
        width: 16px;
        height: 16px;
        flex: 0 0 auto;
        transition: transform 0.25s ease;
    }

    .hero-slide__btn:hover {
        background: #7a5d17;
    }

    .hero-slide__btn:hover svg {
        transform: translateX(3px);
    }

    .hero-slide__btn:focus-visible {
        outline: 3px solid var(--hero-primary);
        outline-offset: 3px;
    }

    /* ---- Trust points ------------------------------------------------------ */
    .hero-features {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: clamp(16px, 2.4vw, 36px);
        width: 100%;
        max-width: 36rem;
        margin: clamp(28px, 3.4vw, 44px) 0 0;
        padding: 0;
        list-style: none;
    }

    .hero-feature__icon {
        display: block;
        width: 30px;
        height: 30px;
        color: var(--hero-primary);
    }

    .hero-feature__title {
        margin: 10px 0 0;
        font-size: 0.92rem;
        font-weight: 700;
        line-height: 1.3;
        color: var(--hero-primary);
    }

    .hero-feature__text {
        margin: 3px 0 0;
        font-size: 0.82rem;
        line-height: 1.45;
        color: var(--hero-muted);
    }

    /* ---- Photo with a curved left edge -------------------------------------- */
    .hero-slide__media {
        position: relative;
        z-index: 1;
    }

    /* thin gold outline that follows the curve, offset to the left */
    .hero-slide__media::before {
        content: "";
        position: absolute;
        top: 0;
        bottom: 0;
        left: -16px;
        right: 0;
        border: 1px solid rgba(178, 138, 50, 0.45);
        border-right: 0;
        border-radius: 34% 0 0 34% / 50% 0 0 50%;
        pointer-events: none;
    }

    .hero-slide__photo {
        position: absolute;
        inset: 0;
        overflow: hidden;
        border-radius: 34% 0 0 34% / 50% 0 0 50%;
        background: #e6e9df;
    }

    .hero-slide__image {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }

    /* ---- Dots: sit on the photo, bottom right ------------------------------ */
    .hero-slider__dots {
        --slider-dot-color: rgba(255, 255, 255, 0.6);
        --slider-dot-active-color: #ffffff;
        position: absolute;
        left: auto;
        right: clamp(16px, 3vw, 40px);
        bottom: 18px;
        transform: none;
        margin-top: 0;
        z-index: 3;
        display: flex;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(20, 42, 36, 0.42);
    }

    /* ---- Tablet / mobile: text on top, photo below --------------------------- */
    @media (max-width: 899px) {
        .hero-slide {
            grid-template-columns: minmax(0, 1fr);
            min-height: 0;
        }

        .hero-slide__content {
            padding: 40px 24px 32px;
        }

        .hero-slide__title {
            max-width: 12em;
        }

        .hero-slide__media {
            height: clamp(240px, 62vw, 380px);
        }

        .hero-slide__media::before {
            display: none;
        }

        .hero-slide__photo {
            border-radius: 50% 50% 0 0 / 26% 26% 0 0;
        }

        .hero-slider__dots {
            right: 50%;
            transform: translateX(50%);
            bottom: 14px;
        }
    }

    @media (max-width: 559px) {
        .hero-features {
            grid-template-columns: minmax(0, 1fr);
            gap: 16px;
        }

        .hero-feature {
            display: grid;
            grid-template-columns: 30px minmax(0, 1fr);
            column-gap: 14px;
            align-items: start;
        }

        .hero-feature__icon {
            grid-row: span 2;
        }

        .hero-feature__title {
            margin-top: 0;
        }

        .hero-slide__btn {
            width: 100%;
            justify-content: center;
        }

        .hero-slider__dot {
            width: 8px;
            height: 8px;
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .hero-slide__btn,
        .hero-slide__btn svg {
            transition: none;
        }

        .hero-slide__btn:hover svg {
            transform: none;
        }
    }
</style>

<?php if ($heroBannerCount > 0): ?>
    <section class="slider hero-slider" data-slider aria-roledescription="carousel" aria-label="Ayurvedic consultation">
        <svg class="hero-sprite" aria-hidden="true" focusable="false">
            <symbol id="hero-sprig" viewBox="0 0 120 200">
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

        <div class="slider__track" data-slider-track>
            <?php foreach ($heroSlides as $i => $slide): ?>
                <div class="slider__slide" data-index="<?= (int) $i ?>">
                    <div class="hero-slide">
                        <svg class="hero-leaf hero-leaf--top" viewBox="0 0 120 200" aria-hidden="true">
                            <use href="#hero-sprig" />
                        </svg>
                        <svg class="hero-leaf hero-leaf--bottom" viewBox="0 0 120 200" aria-hidden="true">
                            <use href="#hero-sprig" />
                        </svg>

                        <div class="hero-slide__content">
                            <p class="hero-slide__eyebrow"><?= heroE($heroEyebrow) ?></p>
                            <h2 class="hero-slide__title"><?= heroE($slide['title']) ?></h2>
                            <p class="hero-slide__text"><?= heroE($slide['text']) ?></p>

                            <?php if ($slide['url'] !== ''): ?>
                                <a class="hero-slide__btn" href="<?= heroE($slide['url']) ?>">
                                    <span><?= heroE($slide['button']) ?></span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M5 12h14M13 6l6 6-6 6" />
                                    </svg>
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($heroFeatures)): ?>
                                <ul class="hero-features">
                                    <?php foreach ($heroFeatures as $feature): ?>
                                        <li class="hero-feature">
                                            <svg class="hero-feature__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $feature['icon'] ?></svg>
                                            <p class="hero-feature__title"><?= heroE($feature['title']) ?></p>
                                            <p class="hero-feature__text"><?= heroE($feature['text']) ?></p>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>

                        <div class="hero-slide__media">
                            <div class="hero-slide__photo">
                                <img class="hero-slide__image"
                                    src="<?= heroE($slide['image']) ?>"
                                    alt=""
                                    <?= $i === 0 ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"' ?>>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($heroBannerCount > 1): ?>
            <div class="slider__dots hero-slider__dots" data-slider-dots role="tablist" aria-label="Banner navigation">
                <?php for ($i = 0; $i < $heroBannerCount; $i++): ?>
                    <button type="button"
                        class="slider__dot<?= $i === 0 ? ' is-active' : '' ?>"
                        data-goto="<?= $i ?>"
                        role="tab"
                        aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"
                        aria-label="Go to slide <?= $i + 1 ?>"></button>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>