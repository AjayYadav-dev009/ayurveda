<?php

/**
 * "What Our Patients Say" — consult-veda.
 *
 * Hardcoded content: ayurveda_db.sql has a `reviews` table, but it's
 * product ratings tied to product_id, not consultation testimonials —
 * there's nothing to query here. Same reasoning as condition.php and
 * why-choose.php.
 *
 * Built on the shared slider engine (see assets/css/slider.css + the
 * initSlider() code in global.js) — same mechanism as hero.php.
 *
 * Each entry:
 *   name, body   required
 *   date         shown under the name when there is no `location`
 *   location     optional, e.g. 'New Delhi' (shown under the name)
 *   photo        optional, full URL or path under BASE_URL; without it the
 *                avatar is a coloured circle with the first letter
 *   color        avatar colour for the letter fallback
 *   email        kept for reference, not shown on the page
 *
 * Usage (from consult-veda/index.php, after $conn is available):
 *   include __DIR__ . '/testimonials.php';
 */

$testimonialsEyebrow = 'Real Stories';
$testimonialsHeading = 'What Our Patients Say';
$testimonialsIntro   = 'Trusted by thousands on their journey to better health.';

$testimonials = [
    [
        'name' => 'Sanjay Kulkarni',
        'email' => 'sanjay.k***@gmail.com',
        'date' => 'Aug 12',
        'color' => '#5c6b47',
        'body' => "After following the suggested routine and medicines, I felt more disciplined about my health. It gave me a proper sense of direction instead of randomly trying things.",
    ],
    [
        'name' => 'Karan Shah',
        'email' => 'karan.shah***@gmail.com',
        'date' => 'Aug 19',
        'color' => '#8a4b3c',
        'body' => "Earlier I used to check articles and get more confused. This consultation made it easier to understand my issue in a better way, and lifestyle changes now support me.",
    ],
    [
        'name' => 'Nihi Suri',
        'email' => 'nihi.suri***@gmail.com',
        'date' => 'Aug 25',
        'color' => '#a4356b',
        'body' => "The wellness counsellor and Vaidya both listened patiently. The process was smooth, and I felt someone is actually understanding my body instead of rushing me through.",
    ],
    [
        'name' => 'Divya Menon',
        'email' => 'divya.menon***@gmail.com',
        'date' => 'Sep 02',
        'color' => '#245c4f',
        'body' => "I was not sure if online Vaidya consultation will be as effective, but it was actually good. The doctor understood my concern and gave diet and medicine guidance properly.",
    ],
    [
        'name' => 'Rohit Bhatia',
        'email' => 'rohit.bhatia***@gmail.com',
        'date' => 'Sep 05',
        'color' => '#3c5b8a',
        'body' => "What stood out for me was how the Vaidya asked about my daily routine before suggesting anything. It didn't feel like a generic prescription, it felt personal.",
    ],
    [
        'name' => 'Priya Nair',
        'email' => 'priya.nair***@gmail.com',
        'date' => 'Sep 08',
        'color' => '#916b1e',
        'body' => "I appreciated the follow-up call after two weeks to check on progress. Most places just hand you a prescription and move on, this felt like actual care.",
    ],
    [
        'name' => 'Arjun Verma',
        'email' => 'arjun.verma***@gmail.com',
        'date' => 'Sep 10',
        'color' => '#4a4a6b',
        'body' => "Booking the slot was simple and the wait time was short. The consultation itself was thorough, not rushed at all despite being fully online.",
    ],
    [
        'name' => 'Meera Iyer',
        'email' => 'meera.iyer***@gmail.com',
        'date' => 'Sep 12',
        'color' => '#a65c2e',
        'body' => "Was skeptical about doing this over a video call, but the Vaidya explained everything clearly and answered all my questions without making me feel rushed.",
    ],
];

if (!function_exists('testimonialE')) {
    function testimonialE($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

/** Full URL stays as is; a relative path is put under BASE_URL. */
if (!function_exists('testimonialPhotoUrl')) {
    function testimonialPhotoUrl($path): string
    {
        $path = trim((string) $path);
        if ($path === '' || preg_match('~^(https?:)?//~i', $path) || $path[0] === '/') {
            return $path;
        }
        return rtrim(defined('BASE_URL') ? (string) BASE_URL : '', '/') . '/' . ltrim($path, '/');
    }
}
?>

<?php //if (!defined('GLOBAL_SLIDER_CSS_LOADED')): ?>
    <!-- <link rel="stylesheet" href="<?php //echo BASE_URL; ?>assets/css/slider.css"> -->
    <?php //define('GLOBAL_SLIDER_CSS_LOADED', true); ?>
<?php //endif; ?>

<style>
    .testimonials-section {
        --tm-primary: var(--color-primary, #1f3a32);
        --tm-accent: var(--color-accent, #b28a32);
        --tm-accent-text: #82631a;
        --tm-text: var(--color-text, #26342f);
        --tm-muted: #5c665f;
        --tm-heading-font: var(--font-heading, "Cormorant Garamond", "Playfair Display", Georgia, "Times New Roman", serif);
        position: relative;
        overflow: hidden;
        padding: clamp(48px, 6vw, 88px) 24px;
        background: var(--color-bg, #f8f6ef);
    }

    .testimonials-sprite {
        position: absolute;
        width: 0;
        height: 0;
        overflow: hidden;
    }

    .testimonials-leaf {
        position: absolute;
        z-index: 0;
        color: var(--tm-primary);
        opacity: 0.1;
        pointer-events: none;
    }

    .testimonials-leaf--tl {
        top: 6px;
        left: -14px;
        width: clamp(70px, 7vw, 110px);
        transform: rotate(-20deg);
    }

    .testimonials-leaf--br {
        right: -12px;
        bottom: -14px;
        width: clamp(70px, 7vw, 110px);
        transform: rotate(-24deg) scaleX(-1);
    }

    /* ---- Header (centred) ---------------------------------------------- */
    .testimonials-header {
        position: relative;
        z-index: 1;
        max-width: 620px;
        margin: 0 auto clamp(28px, 3.6vw, 44px);
        text-align: center;
    }

    .testimonials-eyebrow {
        margin: 0;
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--tm-accent-text);
    }

    .testimonials-heading {
        margin: 12px 0 0;
        font-family: var(--tm-heading-font);
        font-size: clamp(2rem, 3.3vw, 2.75rem);
        font-weight: 700;
        line-height: 1.12;
        letter-spacing: -0.01em;
        color: var(--tm-primary);
    }

    .testimonials-intro {
        margin: 12px 0 0;
        font-size: 0.95rem;
        line-height: 1.6;
        color: var(--tm-muted);
    }

    /* ---- Slider ---------------------------------------------------------- */
    .testimonials-slider {
        --slider-visible: 3;
        --slider-gap: 24px;
        position: relative;
        z-index: 1;
        max-width: var(--container-width, 1200px);
        margin: 0 auto;
    }

    @media (max-width: 1024px) {
        .testimonials-slider {
            --slider-visible: 2;
        }
    }

    @media (max-width: 640px) {
        .testimonials-slider {
            --slider-visible: 1.1;
            --slider-gap: 14px;
        }
    }

    /* Outlined gold dots; the active one is filled dark green */
    .testimonials-slider .slider__dots {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 28px;
    }

    .testimonials-slider .slider__dot {
        box-sizing: border-box;
        width: 10px;
        height: 10px;
        padding: 0;
        border: 1.5px solid var(--tm-accent);
        border-radius: 50%;
        background: transparent;
    }

    .testimonials-slider .slider__dot.is-active {
        border-color: var(--tm-primary);
        background: var(--tm-primary);
    }

    /* ---- Card -------------------------------------------------------------- */
    .testimonial-card {
        position: relative;
        display: flex;
        flex-direction: column;
        height: 100%;
        margin: 0;
        padding: clamp(22px, 2.2vw, 30px);
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.62);
        box-shadow: inset 0 0 0 1px rgba(31, 58, 50, 0.07);
        overflow: hidden;
    }

    .testimonial-card__leaf {
        position: absolute;
        top: 14px;
        right: 10px;
        width: 44px;
        color: var(--tm-primary);
        opacity: 0.12;
        transform: rotate(28deg);
        pointer-events: none;
    }

    .testimonial-card__avatar {
        position: relative;
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        border-radius: 50%;
        overflow: hidden;
        color: #ffffff;
        font-size: 1.05rem;
        font-weight: 700;
    }

    .testimonial-card__avatar img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .testimonial-card__quote {
        flex: 1;
        margin: 18px 0 0;
        font-size: 0.95rem;
        line-height: 1.7;
        color: var(--tm-text);
    }

    .testimonial-card__quote p {
        margin: 0;
    }

    .testimonial-card__by {
        margin-top: 20px;
    }

    .testimonial-card__name {
        display: block;
        font-size: 0.92rem;
        font-weight: 700;
        line-height: 1.3;
        color: var(--tm-text);
    }

    .testimonial-card__meta {
        display: block;
        margin-top: 2px;
        font-size: 0.82rem;
        line-height: 1.4;
        color: var(--tm-muted);
    }
</style>

<section class="testimonials-section" aria-labelledby="testimonialsHeading">
    <svg class="testimonials-sprite" aria-hidden="true" focusable="false">
        <symbol id="testimonials-sprig" viewBox="0 0 120 200">
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
    <svg class="testimonials-leaf testimonials-leaf--tl" viewBox="0 0 120 200" aria-hidden="true">
        <use href="#testimonials-sprig" />
    </svg>
    <svg class="testimonials-leaf testimonials-leaf--br" viewBox="0 0 120 200" aria-hidden="true">
        <use href="#testimonials-sprig" />
    </svg>

    <div class="testimonials-header">
        <p class="testimonials-eyebrow"><?= testimonialE($testimonialsEyebrow) ?></p>
        <h2 class="testimonials-heading" id="testimonialsHeading"><?= testimonialE($testimonialsHeading) ?></h2>
        <p class="testimonials-intro"><?= testimonialE($testimonialsIntro) ?></p>
    </div>

    <div class="slider testimonials-slider" data-slider>
        <div class="slider__track" data-slider-track>
            <?php foreach ($testimonials as $index => $t):
                $name  = (string) ($t['name'] ?? '');
                $photo = testimonialPhotoUrl($t['photo'] ?? '');
                $meta  = trim((string) ($t['location'] ?? '')) !== ''
                    ? trim((string) $t['location'])
                    : trim((string) ($t['date'] ?? ''));
                $color = preg_match('/^#[0-9a-f]{3,8}$/i', (string) ($t['color'] ?? '')) ? $t['color'] : '#5c6b47';
            ?>
                <div class="slider__slide" data-index="<?= (int) $index ?>">
                    <figure class="testimonial-card">
                        <svg class="testimonial-card__leaf" viewBox="0 0 120 200" aria-hidden="true">
                            <use href="#testimonials-sprig" />
                        </svg>

                        <div class="testimonial-card__avatar" style="background: <?= testimonialE($color) ?>;" aria-hidden="true">
                            <?php if ($photo !== ''): ?>
                                <img src="<?= testimonialE($photo) ?>" alt="" loading="lazy">
                            <?php else: ?>
                                <?= testimonialE(mb_strtoupper(mb_substr($name, 0, 1))) ?>
                            <?php endif; ?>
                        </div>

                        <blockquote class="testimonial-card__quote">
                            <p>“<?= testimonialE($t['body'] ?? '') ?>”</p>
                        </blockquote>

                        <figcaption class="testimonial-card__by">
                            <span class="testimonial-card__name"><?= testimonialE($name) ?></span>
                            <?php if ($meta !== ''): ?>
                                <span class="testimonial-card__meta"><?= testimonialE($meta) ?></span>
                            <?php endif; ?>
                        </figcaption>
                    </figure>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($testimonials) > 1): ?>
            <div class="slider__dots" data-slider-dots role="tablist" aria-label="Testimonials navigation"></div>
        <?php endif; ?>
    </div>
</section>