<?php

$pricingEyebrow = 'Our Services';
$pricingHeading = 'Choose the Right Support for You';
$pricingIntro   = 'Get expert guidance or a customised diet plan based on your unique needs.';

$pricingShowFeatures = true;

$pricingPlans = [
    [
        'eyebrow'     => 'Consultation',
        'title'       => 'Ayurvedic Consultation',
        'description' => 'Talk to our expert Vaidya and get a personalised health plan.',
        'price'       => '499.00',
        'unit'        => '30 mins',
        'features'    => [
            'Personalized Health Assessment',
            'Dosha Analysis & Imbalance ID',
            'Basic Dietary & Lifestyle Tips',
            'Q&A Session with Vaidya',
        ],
        'cta_label'   => 'Book Now',
        'cta_href'    => '#book-now',
        'icon'        => 'stethoscope',
        'image'       => 'assets/images/veda-consult.jpg',
    ],
    [
        'eyebrow'     => 'Diet Plan',
        'title'       => 'Personalised Diet Plan',
        'description' => 'Get a custom diet plan based on your dosha and health goals.',
        'price'       => '1999.00',
        'unit'        => '7 days',
        'features'    => [
            'Customized Meal Plan (7 Days)',
            'Dosha-Specific Food Recommendations',
            'Recipes & Shopping List',
            'Follow-up Check-in (15 min)',
        ],
        'cta_label'   => 'Get My Plan',
        'cta_href'    => '#get-plan',
        'icon'        => 'bowl',
        'image'       => 'assets/images/veda-diet.jpg',
    ],
];

// Placeholder artwork (24x24 outline paths) shown while a plan has no photo.
$pricingIcons = [
    'stethoscope' => '<path d="M6 3v6a4 4 0 0 0 8 0V3"/><path d="M10 13v2a5 5 0 0 0 10 0v-2"/><circle cx="20" cy="10" r="1.6"/><path d="M6 3H4M14 3h2"/>',
    'bowl'        => '<path d="M3.5 12h17a8.5 8.5 0 0 1-17 0z"/><path d="M9 8.5c0-1.4 1-1.8 1-3.2M13 8.5c0-1.4 1-1.8 1-3.2"/><path d="M8 21h8"/>',
];

if (!function_exists('pricingE')) {
    function pricingE($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

/** '499.00' -> '499', '1999.00' -> '1,999', '499.50' -> '499.50'; anything else unchanged. */
if (!function_exists('pricingFormatPrice')) {
    function pricingFormatPrice($price): string
    {
        $price = trim((string) $price);
        if (!is_numeric($price)) {
            return $price;
        }
        $amount = (float) $price;
        return number_format($amount, floor($amount) === $amount ? 0 : 2);
    }
}

/** Full URL stays as is; a relative path is put under BASE_URL. */
if (!function_exists('pricingImageUrl')) {
    function pricingImageUrl($path): string
    {
        $path = trim((string) $path);
        if ($path === '' || preg_match('~^(https?:)?//~i', $path) || $path[0] === '/') {
            return $path;
        }
        return rtrim(defined('BASE_URL') ? (string) BASE_URL : '', '/') . '/' . ltrim($path, '/');
    }
}
?>

<style>
    .pricing-section {
        --pr-primary: var(--color-primary, #1f3a32);
        --pr-accent-text: #82631a;
        --pr-accent-strong: #94701f;
        --pr-text: var(--color-text, #26342f);
        --pr-muted: #5c665f;
        --pr-heading-font: var(--font-heading, "Cormorant Garamond", "Playfair Display", Georgia, "Times New Roman", serif);
        padding: clamp(48px, 6vw, 80px) 24px;
        background: var(--color-primary-light, #edf1e8);
    }

    .pricing-inner {
        max-width: var(--container-width, 1200px);
        margin: 0 auto;
    }

    /* ---- Header (left aligned) ----------------------------------------------- */
    .pricing-eyebrow {
        margin: 0;
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--pr-accent-text);
    }

    .pricing-heading {
        margin: 12px 0 0;
        font-family: var(--pr-heading-font);
        font-size: clamp(1.9rem, 3.1vw, 2.6rem);
        font-weight: 700;
        line-height: 1.12;
        letter-spacing: -0.01em;
        color: var(--pr-primary);
    }

    .pricing-intro {
        margin: 12px 0 0;
        max-width: 52ch;
        font-size: 0.95rem;
        line-height: 1.6;
        color: var(--pr-muted);
    }

    /* ---- Cards ------------------------------------------------------------------ */
    .pricing-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: clamp(16px, 2vw, 28px);
        margin: clamp(26px, 3.4vw, 40px) 0 0;
        padding: 0;
        list-style: none;
    }

    .pricing-card {
        display: grid;
        grid-template-columns: minmax(0, 0.85fr) minmax(0, 1.15fr);
        gap: clamp(16px, 2vw, 26px);
        padding: 14px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.62);
        box-shadow: inset 0 0 0 1px rgba(31, 58, 50, 0.07);
    }

    .pricing-card__media {
        position: relative;
        min-height: 220px;
        border-radius: 12px;
        overflow: hidden;
        background: linear-gradient(160deg, #e3e9da, #d5dfc9);
    }

    .pricing-card__media img {
        position: absolute;
        inset: 0;
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pricing-card__placeholder {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 64px;
        height: 64px;
        transform: translate(-50%, -50%);
        fill: none;
        stroke: var(--pr-primary);
        stroke-width: 1.2;
        stroke-linecap: round;
        stroke-linejoin: round;
        opacity: 0.55;
    }

    .pricing-card__body {
        display: flex;
        flex-direction: column;
        min-width: 0;
        padding: 10px 10px 8px 0;
    }

    .pricing-card__eyebrow {
        margin: 0;
        font-size: 11px;
        font-weight: 500;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: var(--pr-accent-text);
    }

    .pricing-card__title {
        margin: 8px 0 0;
        font-family: var(--pr-heading-font);
        font-size: clamp(1.25rem, 1.8vw, 1.5rem);
        font-weight: 700;
        line-height: 1.2;
        color: var(--pr-primary);
    }

    .pricing-card__text {
        margin: 10px 0 0;
        max-width: 38ch;
        font-size: 0.9rem;
        line-height: 1.6;
        color: var(--pr-muted);
    }

    .pricing-card__features {
        display: grid;
        gap: 7px 16px;
        margin: 16px 0 0;
        padding: 0;
        list-style: none;
    }

    .pricing-card__feature {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: 0.84rem;
        line-height: 1.45;
        color: var(--pr-text);
    }

    .pricing-card__feature svg {
        flex: 0 0 auto;
        width: 16px;
        height: 16px;
        margin-top: 1px;
        fill: none;
        stroke: var(--pr-primary);
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .pricing-card__footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 14px 24px;
        margin-top: auto;
        padding-top: 20px;
    }

    .pricing-card__price {
        margin: 0;
        font-family: var(--pr-heading-font);
        font-size: 1.7rem;
        font-weight: 700;
        line-height: 1;
        color: var(--pr-primary);
    }

    .pricing-card__unit {
        margin-left: 6px;
        font-family: inherit;
        font-size: 0.85rem;
        font-weight: 400;
        color: var(--pr-muted);
    }

    .pricing-card__btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 24px;
        border-radius: 999px;
        background: var(--pr-accent-strong);
        color: #ffffff;
        font-size: 0.92rem;
        font-weight: 500;
        line-height: 1.2;
        text-decoration: none;
        transition: background-color 0.25s ease;
    }

    .pricing-card__btn svg {
        width: 16px;
        height: 16px;
        flex: 0 0 auto;
        transition: transform 0.25s ease;
    }

    .pricing-card__btn:hover {
        background: #7a5d17;
    }

    .pricing-card__btn:hover svg {
        transform: translateX(3px);
    }

    .pricing-card__btn:focus-visible {
        outline: 3px solid var(--pr-primary);
        outline-offset: 3px;
    }

    /* ---- Tablet: one card per row ------------------------------------------------- */
    @media (max-width: 899px) {
        .pricing-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    /* ---- Mobile: photo on top ----------------------------------------------------- */
    @media (max-width: 559px) {
        .pricing-section {
            padding-left: 20px;
            padding-right: 20px;
        }

        .pricing-card {
            grid-template-columns: minmax(0, 1fr);
            gap: 8px;
        }

        .pricing-card__media {
            min-height: 0;
            height: 200px;
        }

        .pricing-card__body {
            padding: 12px 8px 8px;
        }

        .pricing-card__btn {
            width: 100%;
            justify-content: center;
        }

        .pricing-card__footer {
            flex-direction: column;
            align-items: stretch;
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .pricing-card__btn,
        .pricing-card__btn svg {
            transition: none;
        }

        .pricing-card__btn:hover svg {
            transform: none;
        }
    }
</style>

<section class="pricing-section" aria-labelledby="pricingHeading">
    <div class="pricing-inner">
        <p class="pricing-eyebrow"><?= pricingE($pricingEyebrow) ?></p>
        <h2 class="pricing-heading" id="pricingHeading"><?= pricingE($pricingHeading) ?></h2>
        <p class="pricing-intro"><?= pricingE($pricingIntro) ?></p>

        <ul class="pricing-grid">
            <?php foreach ($pricingPlans as $plan):
                $image    = pricingImageUrl($plan['image'] ?? '');
                $iconSvg  = $pricingIcons[$plan['icon'] ?? 'stethoscope'] ?? $pricingIcons['stethoscope'];
                $features = $plan['features'] ?? [];
                $unit     = trim((string) ($plan['unit'] ?? ''));
            ?>
                <li class="pricing-card">
                    <div class="pricing-card__media" aria-hidden="true">
                        <?php if ($image !== ''): ?>
                            <img src="<?= pricingE($image) ?>" alt="" loading="lazy">
                        <?php else: ?>
                            <svg class="pricing-card__placeholder" viewBox="0 0 24 24" focusable="false"><?= $iconSvg ?></svg>
                        <?php endif; ?>
                    </div>

                    <div class="pricing-card__body">
                        <?php if (!empty($plan['eyebrow'])): ?>
                            <p class="pricing-card__eyebrow"><?= pricingE($plan['eyebrow']) ?></p>
                        <?php endif; ?>
                        <h3 class="pricing-card__title"><?= pricingE($plan['title']) ?></h3>
                        <p class="pricing-card__text"><?= pricingE($plan['description'] ?? '') ?></p>

                        <?php if ($pricingShowFeatures && !empty($features)): ?>
                            <ul class="pricing-card__features">
                                <?php foreach ($features as $feature): ?>
                                    <li class="pricing-card__feature">
                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                            <path d="m5 12.5 4.2 4.2L19 7.5" />
                                        </svg>
                                        <span><?= pricingE($feature) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <div class="pricing-card__footer">
                            <p class="pricing-card__price">
                                <span>&#8377;</span><?= pricingE(pricingFormatPrice($plan['price'])) ?>
                                <?php if ($unit !== ''): ?>
                                    <span class="pricing-card__unit">/ <?= pricingE($unit) ?></span>
                                <?php endif; ?>
                            </p>

                            <a class="pricing-card__btn" href="<?= pricingE($plan['cta_href']) ?>">
                                <span><?= pricingE($plan['cta_label']) ?></span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 12h14M13 6l6 6-6 6" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>