<?php
/**
 * "Trusted by Thousands" impact/stats section for the homepage.
 * Static content — shares the same --color-* / --font-heading tokens as
 * the other homepage sections.
 */

$impactEyebrow = 'Our Impact';
$impactTitle   = 'Trusted by Thousands';
$impactText    = 'Over the years, we have helped thousands of individuals reclaim their health and balance through authentic Ayurvedic care and personalised guidance.';

$impactStats = [
    ['value' => '10+',     'label' => 'Years of Experience'],
    ['value' => '50,000+', 'label' => 'Happy Customers'],
    ['value' => '4.8/5',   'label' => 'Average Rating'],
    ['value' => '12+',     'label' => 'Expert Vaidyas'],
];

$impactCardText = 'Traditional Ayurveda for a Healthier Tomorrow';

// TODO: replace with the real photo once it's added to assets/images/.
$impactImage    = 'assets/images/our-impact.jpg';
$impactImageAlt = 'Traditional Ayurvedic bowls, herbs, and brass vessels arranged on a table';

if (!function_exists('impactE')) {
    function impactE($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
?>

<style>
    /* ==========================================================================
       Trusted by Thousands — impact/stats section.
       Uses the same --color-* / --font-heading tokens as the other sections.
       ========================================================================== */

    .our-impact {
        --impact-primary: var(--color-primary, #1f3a32);
        --impact-accent-text: #8a6a22;
        --impact-bg: var(--color-bg, #f3f1e6);
        --impact-text: var(--color-text, #2a352f);
        --impact-muted: var(--color-text-light, #5f6a63);
        --impact-heading-font: var(--font-heading, "Cormorant Garamond", "Playfair Display", Georgia, "Times New Roman", serif);
        background: var(--impact-bg);
    }

    .our-impact__grid {
        max-width: 1240px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr);
        align-items: center;
        gap: clamp(32px, 5vw, 64px);
        padding: clamp(40px, 6vw, 72px) clamp(24px, 3vw, 48px);
    }

    /* ---- Text column ----------------------------------------------------------- */
    .our-impact__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: var(--impact-accent-text);
    }

    .our-impact__eyebrow svg {
        width: 12px;
        height: 12px;
        flex: 0 0 auto;
    }

    .our-impact__title {
        margin: 12px 0 0;
        max-width: 11em;
        font-family: var(--impact-heading-font);
        font-size: clamp(1.7rem, 2.8vw, 2.4rem);
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: -0.01em;
        text-wrap: balance;
        color: var(--impact-primary);
    }

    .our-impact__text {
        margin: 14px 0 0;
        max-width: 42ch;
        font-size: 0.9rem;
        line-height: 1.65;
        color: var(--impact-text);
        opacity: 0.85;
    }

    .our-impact__stats {
        margin: 26px 0 0;
        padding: 0;
        list-style: none;
        display: flex;
        flex-wrap: wrap;
    }

    .our-impact__stat {
        padding: 0 clamp(16px, 2.2vw, 28px);
        border-left: 1px solid rgba(31, 58, 50, 0.15);
    }

    .our-impact__stat:first-child {
        padding-left: 0;
        border-left: 0;
    }

    .our-impact__stat-value {
        margin: 0;
        font-family: var(--impact-heading-font);
        font-size: clamp(1.3rem, 1.8vw, 1.6rem);
        font-weight: 700;
        color: var(--impact-primary);
    }

    .our-impact__stat-label {
        margin: 4px 0 0;
        font-size: 0.78rem;
        line-height: 1.4;
        color: var(--impact-muted);
        white-space: nowrap;
    }

    /* ---- Photo with floating card ---------------------------------------------- */
    .our-impact__media {
        position: relative;
    }

    .our-impact__photo {
        border-radius: 20px;
        overflow: hidden;
        aspect-ratio: 16 / 9.5;
        background: #d9d4bf;
    }

    .our-impact__image {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }

    .our-impact__card {
        position: absolute;
        top: clamp(-18px, -2vw, -10px);
        right: clamp(-18px, -2vw, -10px);
        z-index: 2;
        width: clamp(120px, 15vw, 148px);
        padding: 18px 16px;
        border-radius: 16px;
        background: var(--impact-bg);
        box-shadow: 0 10px 30px rgba(31, 58, 50, 0.14);
        text-align: center;
    }

    .our-impact__card-icon {
        width: 22px;
        height: 22px;
        margin: 0 auto;
        color: var(--impact-primary);
        opacity: 0.55;
    }

    .our-impact__card-text {
        margin: 10px 0 0;
        font-family: var(--impact-heading-font);
        font-size: 0.86rem;
        line-height: 1.4;
        color: var(--impact-primary);
    }

    /* ---- Tablet / mobile: stack -------------------------------------------- */
    @media (max-width: 899px) {
        .our-impact__grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .our-impact__title {
            max-width: 14em;
        }

        .our-impact__card {
            top: -14px;
            right: 16px;
        }
    }

    @media (max-width: 480px) {
        .our-impact__stats {
            gap: 14px 0;
        }

        .our-impact__stat {
            width: 50%;
            border-left: 0;
            padding: 10px 0;
            border-top: 1px solid rgba(31, 58, 50, 0.15);
        }

        .our-impact__stat:nth-child(1),
        .our-impact__stat:nth-child(2) {
            border-top: 0;
        }
    }
</style>

<section class="our-impact" aria-label="Our impact">
    <div class="our-impact__grid">
        <div class="our-impact__content">
            <p class="our-impact__eyebrow">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="1.5" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                <?= impactE($impactEyebrow) ?>
            </p>

            <h2 class="our-impact__title"><?= impactE($impactTitle) ?></h2>
            <p class="our-impact__text"><?= impactE($impactText) ?></p>

            <ul class="our-impact__stats">
                <?php foreach ($impactStats as $stat): ?>
                    <li class="our-impact__stat">
                        <p class="our-impact__stat-value"><?= impactE($stat['value']) ?></p>
                        <p class="our-impact__stat-label"><?= impactE($stat['label']) ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="our-impact__media">
            <div class="our-impact__photo">
                <img class="our-impact__image"
                    src="<?php echo BASE_URL; ?><?= impactE($impactImage) ?>"
                    alt="<?= impactE($impactImageAlt) ?>"
                    loading="lazy">
            </div>

            <div class="our-impact__card">
                <svg class="our-impact__card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 20.5s-7-4-7-9.8A4.2 4.2 0 0 1 12 8a4.2 4.2 0 0 1 7 2.7c0 5.8-7 9.8-7 9.8Z" />
                    <path d="M12 8c0-3 2-5 4.5-5.5C16.5-.2 13.8 1 12 3.5 10.2 1 7.5-.2 7.5 2.5 10 3 12 5 12 8Z" />
                </svg>
                <p class="our-impact__card-text"><?= impactE($impactCardText) ?></p>
            </div>
        </div>
    </div>
</section>
