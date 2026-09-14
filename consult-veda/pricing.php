<?php

/**
 * Pricing / plan cards for the consult-veda flow.
 *
 * Static content — a plain PHP array below rather than a DB query, same
 * pattern as health-conditions.php and healing-journey.php. Each entry
 * already has the shape a `plans` DB row would (title, price, subtitle,
 * feature list, CTA), so swapping in a fetch later is a drop-in change.
 *
 * Usage (from consult-veda/index.php, after $conn is available):
 *   include __DIR__ . '/pricing-plans.php';
 */

$pricingPlans = [
    [
        'title' => '30-Minute Consultation',
        'price' => '499.00',
        'subtitle' => 'Initial assessment & guidance',
        'features' => [
            'Personalized Health Assessment',
            'Dosha Analysis & Imbalance ID',
            'Basic Dietary & Lifestyle Tips',
            'Q&A Session with Vaidya',
        ],
        'cta_label' => 'Book Now',
        'cta_href' => '#book-now',
    ],
    [
        'title' => 'Personalized 7-Day Diet Plan',
        'price' => '1999.00',
        'subtitle' => 'Tailored nutrition for your Dosha',
        'features' => [
            'Customized Meal Plan (7 Days)',
            'Dosha-Specific Food Recommendations',
            'Recipes & Shopping List',
            'Follow-up Check-in (15 min)',
        ],
        'cta_label' => 'Get My Plan',
        'cta_href' => '#get-plan',
    ],
];
?>

<style>
    .pricing-section {
        padding: 56px 40px;
        background: var(--color-bg);
    }

    .pricing-grid {
        max-width: var(--container-width);
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 28px;
    }

    @media (max-width: 768px) {
        .pricing-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .pricing-section {
            padding: 36px 20px;
        }
    }

    .pricing-card {
        display: flex;
        flex-direction: column;
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-soft);
        padding: 40px 36px;
        text-align: center;
        width: 450px;
        margin: 0 auto;
    }

    .pricing-title {
        margin: 0 0 20px;
        font-size: 22px;
        font-weight: 800;
        color: var(--color-primary);
    }

    .pricing-price {
        margin: 0 0 10px;
        font-size: 32px;
        font-weight: 700;
        color: var(--color-accent);
    }

    .pricing-price .pricing-currency {
        font-size: 22px;
        font-weight: 600;
        margin-right: 2px;
    }

    .pricing-subtitle {
        margin: 0 0 26px;
        font-size: 14px;
        color: var(--color-text-light);
    }

    .pricing-features {
        list-style: none;
        margin: 0 0 32px;
        padding: 0;
        text-align: left;
        display: flex;
        flex-direction: column;
        gap: 13px;
    }

    .pricing-feature {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 14px;
        line-height: 1.5;
        color: var(--color-feature-text, #3f72af);
    }

    .pricing-feature-icon {
        flex-shrink: 0;
        width: 18px;
        height: 18px;
        margin-top: 1px;
    }

    .pricing-feature-icon svg {
        width: 18px;
        height: 18px;
        fill: none;
        stroke: var(--color-success, #3fa34d);
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .pricing-cta-btn {
        margin-top: auto;
        display: block;
        width: 100%;
        padding: 14px 20px;
        background: var(--color-accent);
        color: var(--color-white);
        font-size: 14px;
        font-weight: 700;
        text-align: center;
        text-decoration: none;
        border: none;
        border-radius: var(--radius-md, 6px);
        cursor: pointer;
        transition: opacity 0.25s ease, transform 0.25s ease;
    }

    .pricing-cta-btn:hover {
        opacity: 0.9;
        transform: translateY(-2px);
    }
</style>

<section class="pricing-section">
    <div class="pricing-grid">
        <?php foreach ($pricingPlans as $plan): ?>
            <div class="pricing-card">
                <h3 class="pricing-title"><?= htmlspecialchars($plan['title'], ENT_QUOTES, 'UTF-8') ?></h3>

                <p class="pricing-price">
                    <span class="pricing-currency">&#8377;</span><?= htmlspecialchars($plan['price'], ENT_QUOTES, 'UTF-8') ?>
                </p>

                <p class="pricing-subtitle"><?= htmlspecialchars($plan['subtitle'], ENT_QUOTES, 'UTF-8') ?></p>

                <ul class="pricing-features">
                    <?php foreach ($plan['features'] as $feature): ?>
                        <li class="pricing-feature">
                            <span class="pricing-feature-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9.5" /><path d="M8 12.5l2.6 2.6L16.5 9" /></svg>
                            </span>
                            <span><?= htmlspecialchars($feature, ENT_QUOTES, 'UTF-8') ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <a href="<?= htmlspecialchars($plan['cta_href'], ENT_QUOTES, 'UTF-8') ?>" class="pricing-cta-btn">
                    <?= htmlspecialchars($plan['cta_label'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>