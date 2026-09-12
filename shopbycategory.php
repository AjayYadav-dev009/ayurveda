<?php

/**
 * Homepage "Shop By Category" section.
 *
 * Renders active, top-level categories as an image-led card slider.
 * Only category image + name + link come from the database — the
 * slider mechanics (position, autoplay, looping, dots) are handled by
 * assets/js/shop-by-category.js and never touch category data directly.
 *
 * Usage (from the homepage), after $conn is available:
 *
 *   require_once __DIR__ . '/function/category.php';
 *   include __DIR__ . '/partials/shop-by-category.php';
 *
 * Include assets/css/shop-by-category.css and
 * assets/js/shop-by-category.js on the same page (once).
 */

if (!function_exists('getTopLevelActiveCategories')) {
    require_once __DIR__ . '/function/category.php';
}

try {
    $shopCategories = getTopLevelActiveCategories($conn);
} catch (Exception $e) {
    // Fail closed: hide the section rather than show a broken slider.
    $shopCategories = [];
}

$shopCategoryCount = count($shopCategories);
?>

<style>
    /* ==========================================================================
   Shop By Category — homepage section.
   Built entirely on the existing global.css tokens (colors, radii, shadow,
   .container) — no new palette, no new container system.
   ========================================================================== */

    .shop-by-category {
        padding: 56px 0;
        background: var(--color-bg);
    }

    .shop-by-category__heading {
        text-align: center;
        font-size: 28px;
        font-weight: 700;
        color: var(--color-text);
        margin-bottom: 32px;
    }

    /* ---- Slider shell ---- */

    .category-slider {
        --cat-visible: 5;
        --cat-gap: 20px;
        position: relative;
        overflow: hidden;
    }

    .category-track {
        display: flex;
        gap: var(--cat-gap);
        transition: transform 0.6s ease-in-out;
        will-change: transform;
    }

    /* ---- Card sizing: driven entirely by --cat-visible, set per breakpoint ---- */

    .category-slide {
        flex: 0 0 calc((100% - (var(--cat-visible) - 1) * var(--cat-gap)) / var(--cat-visible));
        min-width: 0;
    }

    /* Clones injected by JS for the infinite loop should never be reachable
   by keyboard/screen reader — the real cards already cover every category. */
    .category-slide[aria-hidden="true"] {
        pointer-events: none;
    }

    /* ---- Card ---- */

    .category-card {
        display: flex;
        flex-direction: column;
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-soft);
        overflow: hidden;
        height: 100%;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .category-card:hover,
    .category-card:focus-visible {
        transform: translateY(-4px);
        box-shadow: 0 12px 34px rgba(25, 70, 58, 0.14);
    }

    .category-card:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 2px;
    }

    .category-card__image {
        display: block;
        width: 100%;
        aspect-ratio: 1 / 1;
        background: var(--color-primary-light);
    }

    .category-card__image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        /* category photography is uncropped source art, unlike the hero banners */
    }

    .category-card__name {
        display: block;
        text-align: center;
        padding: 10px 12px;
        margin: -18px 12px 12px;
        position: relative;
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        font-size: 14px;
        font-weight: 600;
        color: var(--color-text);
    }

    /* ---- Dots ---- */

    .category-slider-dots {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 24px;
    }

    .category-slider-dot {
        width: 9px;
        height: 9px;
        padding: 0;
        border: none;
        border-radius: 50%;
        background: var(--color-border);
        transition: background 0.3s ease, transform 0.3s ease;
    }

    .category-slider-dot.is-active {
        background: var(--color-primary);
        transform: scale(1.25);
    }

    .category-slider-dot:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 2px;
    }

    /* ---- Responsive visible-card counts ---- */

    @media (max-width: 1024px) {
        .category-slider {
            --cat-visible: 4;
        }
    }

    @media (max-width: 860px) {
        .category-slider {
            --cat-visible: 3;
        }
    }

    @media (max-width: 640px) {
        .category-slider {
            --cat-visible: 2;
            --cat-gap: 14px;
        }

        .shop-by-category__heading {
            font-size: 22px;
            margin-bottom: 22px;
        }
    }

    @media (max-width: 420px) {
        .category-slider {
            --cat-visible: 1.15;
        }

        /* slight peek of the next card hints it scrolls */
    }
</style>

<?php if ($shopCategoryCount > 0): ?>
    <section class="shop-by-category">
        <div class="container">
            <h2 class="shop-by-category__heading">Shop By Category</h2>

            <div class="category-slider" data-category-slider>
                <div class="category-track" data-category-track>
                    <?php foreach ($shopCategories as $category):
                        $imageUrl = getCategoryImageUrlOrFallback($category['image'] ?? null);
                        $name = trim((string) ($category['name'] ?? ''));
                        $url = getCategoryUrl($category['slug'] ?? '');
                    ?>
                        <div class="category-slide">
                            <a class="category-card" href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>">
                                <span class="category-card__image">
                                    <img src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>"
                                        alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                                        loading="lazy">
                                </span>
                                <span class="category-card__name">
                                    <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ($shopCategoryCount > 1): ?>
                <div class="category-slider-dots" data-category-dots role="tablist" aria-label="Category navigation">
                    <?php for ($i = 0; $i < $shopCategoryCount; $i++): ?>
                        <button type="button"
                            class="category-slider-dot<?= $i === 0 ? ' is-active' : '' ?>"
                            data-goto="<?= $i ?>"
                            role="tab"
                            aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"
                            aria-label="Go to category slide <?= $i + 1 ?>"></button>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<script>
    /**
     * Shop By Category — vanilla JS slider.
     *
     * Handles only interaction: current position, automatic movement, dot
     * navigation, infinite looping, and pause/resume. No category names,
     * images, URLs, or ids are read or written here — that all comes from
     * the markup rendered by partials/shop-by-category.php.
     */
    (function() {
        'use strict';

        var AUTOPLAY_INTERVAL_MS = 5000;

        function getVisibleCount(track) {
            var raw = getComputedStyle(track.parentElement).getPropertyValue('--cat-visible');
            var value = parseFloat(raw);
            return value > 0 ? value : 1;
        }

        function initCategorySlider(root) {
            var track = root.querySelector('[data-category-track]');
            if (!track) {
                return;
            }

            var realSlides = Array.prototype.slice.call(track.children);
            var count = realSlides.length;
            if (count === 0) {
                return;
            }

            var sectionEl = root.closest('.shop-by-category') || root.parentElement;
            var dotsContainer = sectionEl ? sectionEl.querySelector('[data-category-dots]') : null;
            var dots = dotsContainer ? Array.prototype.slice.call(dotsContainer.querySelectorAll('.category-slider-dot')) : [];

            var visible = Math.floor(getVisibleCount(track));
            if (visible < 1) {
                visible = 1;
            }

            // Section 21: not enough categories to fill one screen — just show
            // them, no clones, no autoplay, no dots needed.
            //
            // Note: this check runs once on load. If the viewport is later
            // resized across a breakpoint (e.g. desktop -> mobile) after
            // landing in this branch, the slider won't retroactively engage
            // until the page reloads. Kept intentionally simple per the
            // "practical, not overengineered" brief; a full fix would mean
            // tearing down/rebuilding the slider on every resize.
            if (count <= visible) {
                if (dotsContainer) {
                    dotsContainer.style.display = 'none';
                }
                return;
            }

            // Clone enough slides on each side to make the loop seamless:
            // [tail clones][real slides][head clones]
            var headClones = realSlides.slice(0, visible).map(function(node) {
                return cloneAsHidden(node);
            });
            var tailClones = realSlides.slice(-visible).map(function(node) {
                return cloneAsHidden(node);
            });

            tailClones.forEach(function(clone) {
                track.insertBefore(clone, track.firstChild);
            });
            headClones.forEach(function(clone) {
                track.appendChild(clone);
            });

            var currentIndex = visible; // position of the first real slide within the full (cloned) track
            var timerId = null;

            function slideStepPercent() {
                return 100 / getVisibleCount(track);
            }

            function setPosition(withTransition) {
                track.style.transition = withTransition ? '' : 'none';
                track.style.transform = 'translateX(-' + (currentIndex * slideStepPercent()) + '%)';
                if (!withTransition) {
                    // Force layout so the next transform change animates again.
                    // eslint-disable-next-line no-unused-expressions
                    track.offsetHeight;
                    track.style.transition = '';
                }
            }

            function updateDots() {
                if (!dots.length) {
                    return;
                }
                var realIndex = ((currentIndex - visible) % count + count) % count;
                dots.forEach(function(dot, i) {
                    var isActive = i === realIndex;
                    dot.classList.toggle('is-active', isActive);
                    dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });
            }

            function goToTrackIndex(index) {
                currentIndex = index;
                setPosition(true);
                updateDots();
            }

            function next() {
                goToTrackIndex(currentIndex + 1);
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

            // After sliding into the cloned region, snap back to the matching
            // real position with no transition — invisible to the user.
            track.addEventListener('transitionend', function(event) {
                if (event.target !== track) {
                    return;
                }
                if (currentIndex >= visible + count) {
                    currentIndex -= count;
                    setPosition(false);
                } else if (currentIndex < visible) {
                    currentIndex += count;
                    setPosition(false);
                }
            });

            dots.forEach(function(dot, i) {
                dot.addEventListener('click', function() {
                    goToTrackIndex(visible + i);
                    startAutoplay();
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

            var resizeTimer = null;
            window.addEventListener('resize', function() {
                // The visible-card count can change at a new breakpoint; just
                // reposition without animating rather than re-cloning.
                window.clearTimeout(resizeTimer);
                resizeTimer = window.setTimeout(function() {
                    setPosition(false);
                }, 150);
            });

            setPosition(false);
            updateDots();
            startAutoplay();
        }

        function cloneAsHidden(node) {
            var clone = node.cloneNode(true);
            clone.setAttribute('aria-hidden', 'true');
            var focusable = clone.querySelectorAll('a, button');
            for (var i = 0; i < focusable.length; i++) {
                focusable[i].setAttribute('tabindex', '-1');
            }
            return clone;
        }

        function init() {
            document.querySelectorAll('[data-category-slider]').forEach(initCategorySlider);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
</script>