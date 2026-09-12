<?php

/**
 * Homepage hero banner slider — image only.
 *
 * All visual content (headings, CTAs, icons, product/service info) lives
 * INSIDE the uploaded banner image. This template only ever outputs:
 *   - <img> tags
 *   - an optional wrapping <a> when button_url is set
 *   - dot navigation controls
 * No text, heading, or button markup is generated from banner data.
 *
 * Usage (from the homepage, e.g. index.php), after $conn is available:
 *
 *   require_once __DIR__ . '/function/banner.php';
 *   include __DIR__ . '/partials/hero-slider.php';
 *
 * Include assets/css/hero-slider.css and assets/js/hero-slider.js on the
 * same page (once) for styling and the auto-rotate/dot behavior.
 */

if (!function_exists('getActiveBanners')) {
    require_once __DIR__ . '/function/banner.php';
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

<style>
    /* ==========================================================================
   Homepage hero banner slider — image-only.
   All headings/CTAs/icons are baked into the uploaded banner image itself;
   this file only handles layout, sizing, transitions, and dot controls.
   ========================================================================== */

    .hero-slider {
        position: relative;
        width: 100%;
        overflow: hidden;
        background: #f2f0eb;
        /* neutral placeholder tone while an image loads */
        line-height: 0;
        /* avoid a stray gap under inline <img> elements */
    }

    .hero-slider__track {
        display: flex;
        width: 100%;
        transition: transform 0.6s ease-in-out;
        will-change: transform;
    }

    /* Section 19: a single banner just displays — no sliding animation. */
    .hero-slider[data-single="true"] .hero-slider__track {
        transition: none;
    }

    .hero-slider__slide {
        flex: 0 0 100%;
        min-width: 100%;
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

    .hero-slider__dots {
        position: absolute;
        left: 50%;
        bottom: 16px;
        transform: translateX(-50%);
        display: flex;
        gap: 8px;
        z-index: 2;
    }

    .hero-slider__dot {
        width: 10px;
        height: 10px;
        padding: 0;
        border: none;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.55);
        cursor: pointer;
        transition: background 0.3s ease, transform 0.3s ease;
    }

    .hero-slider__dot.is-active {
        background: #ffffff;
        transform: scale(1.2);
    }

    .hero-slider__dot:focus-visible {
        outline: 2px solid #ffffff;
        outline-offset: 2px;
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

<?php
/**
 * Homepage hero banner slider — image only.
 *
 * All visual content (headings, CTAs, icons, product/service info) lives
 * INSIDE the uploaded banner image. This template only ever outputs:
 *   - <img> tags
 *   - an optional wrapping <a> when button_url is set
 *   - dot navigation controls
 * No text, heading, or button markup is generated from banner data.
 *
 * Usage (from the homepage, e.g. index.php), after $conn is available:
 *
 *   require_once __DIR__ . '/function/banner.php';
 *   include __DIR__ . '/partials/hero-slider.php';
 *
 * Include assets/css/hero-slider.css and assets/js/hero-slider.js on the
 * same page (once) for styling and the auto-rotate/dot behavior.
 */

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
<?php if ($heroBannerCount > 0): ?>
    <section class="hero-slider" data-hero-slider<?= $heroBannerCount === 1 ? ' data-single="true"' : '' ?>>
        <div class="hero-slider__track">
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
                <div class="hero-slider__slide" data-index="<?= (int) $index ?>">
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
            <div class="hero-slider__dots" role="tablist" aria-label="Banner navigation">
                <?php for ($i = 0; $i < $heroBannerCount; $i++): ?>
                    <button type="button"
                        class="hero-slider__dot<?= $i === 0 ? ' is-active' : '' ?>"
                        data-goto="<?= $i ?>"
                        role="tab"
                        aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"
                        aria-label="Go to slide <?= $i + 1 ?>"></button>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<script>
    /**
     * Homepage hero banner slider — vanilla JS.
     *
     * Handles: current slide, automatic infinite rotation, dot navigation,
     * and pause/resume on interaction. No banner-specific data (images, URLs,
     * counts) is hardcoded here — everything is read from the markup rendered
     * by partials/hero-slider.php, so adding/removing/reordering banners in
     * the admin needs zero changes to this file.
     */
    (function() {
        'use strict';

        var AUTOPLAY_INTERVAL_MS = 5000;

        function initHeroSlider(root) {
            var track = root.querySelector('.hero-slider__track');
            if (!track) {
                return;
            }

            var slideCount = track.children.length;

            // Section 19: a single banner just displays — no autoplay, no dots.
            if (slideCount <= 1) {
                return;
            }

            var dotsContainer = root.querySelector('.hero-slider__dots');
            var dots = dotsContainer ? dotsContainer.querySelectorAll('.hero-slider__dot') : [];

            var currentIndex = 0;
            var timerId = null;

            function render() {
                track.style.transform = 'translateX(-' + (currentIndex * 100) + '%)';
                for (var i = 0; i < dots.length; i++) {
                    var isActive = i === currentIndex;
                    dots[i].classList.toggle('is-active', isActive);
                    dots[i].setAttribute('aria-selected', isActive ? 'true' : 'false');
                }
            }

            function goTo(index) {
                // Wrap around in both directions so it loops forever.
                currentIndex = ((index % slideCount) + slideCount) % slideCount;
                render();
            }

            function next() {
                goTo(currentIndex + 1);
            }

            function startAutoplay() {
                stopAutoplay();
                timerId = window.setInterval(next, AUTOPLAY_INTERVAL_MS);
            }

            function stopAutoplay() {
                if (timerId !== null) {
                    window.clearInterval(timerId);
                    timerId = null;
                }
            }

            dots.forEach(function(dot, index) {
                dot.addEventListener('click', function() {
                    goTo(index);
                    startAutoplay(); // restart the clock so it doesn't jump right after a manual pick
                });
            });

            root.addEventListener('mouseenter', stopAutoplay);
            root.addEventListener('mouseleave', startAutoplay);
            root.addEventListener('touchstart', stopAutoplay, {
                passive: true
            });
            root.addEventListener('touchend', startAutoplay, {
                passive: true
            });

            render();
            startAutoplay();
        }

        function init() {
            document.querySelectorAll('[data-hero-slider]').forEach(initHeroSlider);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
</script>