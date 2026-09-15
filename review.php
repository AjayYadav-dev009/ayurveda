<?php

/**
 * Homepage "Trusted By" testimonials section.
 *
 * Pulls real, admin-approved (status = 'Active') reviews — there's no
 * separate "testimonials" table, and there doesn't need to be: the
 * `reviews` table plus the admin moderation pages in admin/reviews/ already
 * give admins full control over which reviews exist, what state they're
 * in, and (by approving/rejecting) which ones are eligible to show up
 * here. This section just displays the best of what's approved.
 *
 * Slider mechanics come entirely from the shared engine in global.js/
 * global.css ([data-slider] / .slider__track / .slider__slide /
 * .slider__dots — the same one banners and Shop By Category use), so
 * there's no bespoke JS in this file at all. One dot per review, same
 * convention as those sections.
 *
 * Usage (from the homepage), after $conn is available:
 *
 *   require_once __DIR__ . '/function/review.php';
 *   include __DIR__ . '/partials/testimonials.php';
 */

if (!function_exists('getFeaturedReviews')) {
    require_once __DIR__ . '/function/review.php';
}

try {
    $testimonials = getFeaturedReviews($conn, 9);
} catch (Exception $e) {
    $testimonials = [];
}

$testimonialCount = count($testimonials);
?>

<style>
    /* ==========================================================================
       Testimonials — homepage section. Namespaced "tst".
       Built entirely on global.css's design tokens (colors, radii, shadow)
       and its shared .slider engine — no separate palette, no bespoke
       slider JS. The only section-local color is the star rating gold,
       which is a universal rating convention independent of brand color.
       ========================================================================== */

    .tst {
        --tst-star: #e0a72e;
        padding: 60px 0;
        background: var(--color-bg);
    }

    .tst__heading {
        text-align: center;
        font-size: 26px;
        font-weight: 600;
        line-height: 1.5;
        color: var(--color-text);
        margin: 0 0 36px;
    }

    .tst__heading strong {
        color: var(--color-primary);
        font-weight: 700;
    }

    /* Sizing only — the shared .slider/.slider__track/.slider__dot rules
       themselves live in global.css and are untouched. This just sets
       this section's --slider-visible/--slider-gap responsively. */
    .tst .slider {
        --slider-visible: 3;
        --slider-gap: 20px;
        padding: 18px 0 4px;
    }

    @media (max-width: 860px) {
        .tst .slider {
            --slider-visible: 2;
            padding: 0;
        }
    }

    @media (max-width: 560px) {
        .tst .slider {
            --slider-visible: 1.08;
            --slider-gap: 14px;
        }

        .tst__heading {
            font-size: 20px;
            margin-bottom: 24px;
        }
    }

    /* ---- Card ---- */

    .tst-card {
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 32px 24px;
        border-radius: var(--radius-lg);
        background: var(--color-white);
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-soft);
        transition: transform 0.35s ease, background 0.35s ease,
            box-shadow 0.35s ease, border-color 0.35s ease;
    }

    /* Middle card of the 3 currently on screen — raised, highlighted,
       and given a stronger shadow. Toggled by global.js when the slider
       opts in via [data-slider-highlight-center]; harmless without JS
       since the class simply never gets added. */
    .slider__slide.is-center .tst-card {
        transform: translateY(-12px);
        background: var(--color-primary-light);
        border-color: var(--color-primary);
        box-shadow: 0 22px 44px rgba(23, 72, 61, 0.22);
    }

    @media (max-width: 860px) {
        .slider__slide.is-center .tst-card {
            transform: none;
            background: var(--color-white);
            border-color: var(--color-border);
            box-shadow: var(--shadow-soft);
        }
    }

    .tst-card__avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--color-primary-light);
        color: var(--color-primary);
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 14px;
    }

    .tst-card__name {
        font-size: 15px;
        font-weight: 700;
        color: var(--color-text);
        margin: 0 0 8px;
    }

    .tst-card__stars {
        color: var(--tst-star);
        letter-spacing: 2px;
        margin-bottom: 14px;
        font-size: 13px;
    }

    .tst-card__text {
        font-size: 13.5px;
        line-height: 1.7;
        color: var(--color-text-light);
        margin: 0;

        display: -webkit-box;
        -webkit-line-clamp: 5;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<?php if ($testimonialCount > 0): ?>
    <section class="tst">
        <div class="container">
            <h2 class="tst__heading">
                Trusted By <strong>10 Lakh</strong> Customers<br>
                Across <strong>3600+</strong> Cities
            </h2>

            <div class="slider" data-slider data-slider-highlight-center>
                <div class="slider__track" data-slider-track>
                    <?php foreach ($testimonials as $review):
                        $rating = max(1, min(5, (int) $review['rating']));
                        $name = trim((string) ($review['customer_name'] ?? ''));
                        $initial = $name !== '' ? mb_strtoupper(mb_substr($name, 0, 1)) : '?';
                    ?>
                        <div class="slider__slide">
                            <div class="tst-card">
                                <span class="tst-card__avatar" aria-hidden="true"><?= htmlspecialchars($initial, ENT_QUOTES, 'UTF-8') ?></span>
                                <p class="tst-card__name"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="tst-card__stars"><?= str_repeat('&#9733;', $rating) . str_repeat('&#9734;', 5 - $rating) ?></p>
                                <p class="tst-card__text"><?= htmlspecialchars($review['review'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($testimonialCount > 1): ?>
                    <div class="slider__dots" data-slider-dots role="tablist" aria-label="Testimonials navigation">
                        <?php for ($i = 0; $i < $testimonialCount; $i++): ?>
                            <button type="button"
                                class="slider__dot<?= $i === 0 ? ' is-active' : '' ?>"
                                role="tab"
                                aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"
                                aria-label="Go to review <?= $i + 1 ?>"></button>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?>