<?php

if (!function_exists('getActiveBanners')) {
    require_once __DIR__ . '/../function/banner.php';
}

try {
    // Pass a position value here (e.g. getActiveBanners($conn, 'homepage_hero'))
    // once banners are tagged by placement in the admin. Left null so this
    // works with the banner data that already exists today.
    $heroBanners = getActiveBanners($conn, null);
} catch (Exception $e) {
    // Fail closed: hide the hero rather than show a broken slider.
    $heroBanners = [];
}

$heroBannerCount = count($heroBanners);
?>

<?php if (!defined('GLOBAL_SLIDER_CSS_LOADED')): ?>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/slider.css">
    <?php define('GLOBAL_SLIDER_CSS_LOADED', true); ?>
<?php endif; ?>

<style>
    /* ==========================================================================
   Homepage hero banner slider — image-only.
   All headings/CTAs/icons are baked into the uploaded banner image itself;
   built on the shared slider mechanics (assets/css/slider.css) — this file
   only styles the image itself and the overlaid dots.
   ========================================================================== */

    .hero-slider {
        --slider-visible: 1;
        background: #f2f0eb;
        /* neutral placeholder tone while an image loads */
        line-height: 0;
        /* avoid a stray gap under inline <img> elements */
    }

    .hero-slider__link {
        display: block;
        width: 100%;
    }

    .hero-slider__image {
        width: 100%;
        height: auto;
        /* preserves the banner's own aspect ratio, no cropping */
        display: block;
    }

    /*
 * If every banner is designed to a single, consistent aspect ratio, enforce
 * it explicitly instead of relying on height:auto — set the real ratio the
 * design team uses, and prefer `object-fit: contain` (never `cover`) so
 * nothing baked into the image gets clipped:
 *
 * .hero-slider__image {
 *   aspect-ratio: 21 / 9;
 *   object-fit: contain;
 * }
 *
 * @media (max-width: 768px) {
 *   .hero-slider__image {
 *     aspect-ratio: 4 / 5;
 *   }
 * }
 */

    /* Dots overlay the image itself (white, semi-transparent) rather than
   sitting below it like the shared default — set via the CSS variables
   the shared .slider__dot rules read from. */
    .hero-slider__dots {
        --slider-dot-color: rgba(255, 255, 255, 0.55);
        --slider-dot-active-color: #ffffff;
        position: absolute;
        left: 50%;
        bottom: 16px;
        transform: translateX(-50%);
        margin-top: 0;
        z-index: 2;
    }

    @media (max-width: 480px) {
        .hero-slider__dots {
            bottom: 10px;
            gap: 6px;
        }

        .hero-slider__dot {
            width: 8px;
            height: 8px;
        }
    }
</style>

<?php if ($heroBannerCount > 0): ?>
    <section class="slider hero-slider" data-slider>
        <div class="slider__track" data-slider-track>
            <?php foreach ($heroBanners as $index => $banner):
                $imageUrl = getBannerImageUrl($banner['image'] ?? null);

                // Skip malformed rows with no image rather than render a broken slide.
                if ($imageUrl === null) {
                    continue;
                }

                $altText = trim((string) ($banner['title'] ?? ''));
                if ($altText === '') {
                    $altText = 'Promotional banner';
                }

                $destination = trim((string) ($banner['button_url'] ?? ''));
            ?>
                <div class="slider__slide" data-index="<?= (int) $index ?>">
                    <?php if ($destination !== ''): ?>
                        <a class="hero-slider__link"
                            href="<?= htmlspecialchars($destination, ENT_QUOTES, 'UTF-8') ?>">
                            <img class="hero-slider__image"
                                src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>"
                                alt="<?= htmlspecialchars($altText, ENT_QUOTES, 'UTF-8') ?>"
                                <?= $index === 0 ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"' ?>>
                        </a>
                    <?php else: ?>
                        <img class="hero-slider__image"
                            src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($altText, ENT_QUOTES, 'UTF-8') ?>"
                            <?= $index === 0 ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"' ?>>
                    <?php endif; ?>
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