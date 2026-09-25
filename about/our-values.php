<?php
/**
 * "What Guides Us" values section for the homepage.
 * Static content — shares the same --color-* / --font-heading tokens as
 * about-ayurveda.php and our-story.php.
 */

$valuesEyebrow = 'Our Values';
$valuesTitle   = 'What Guides Us';
$valuesText    = 'Our approach to wellness is built on values that have stood the test of time.';

$valuesItems = [
    [
        'title' => 'Authenticity',
        'text'  => 'We follow classical Ayurvedic principles, without shortcuts.',
        'icon'  => '<path d="M6 19c9 0 13-6 13-14-8 0-13 4-13 12-1.3-.3-2.3-1-3-2" />',
    ],
    [
        'title' => 'Trust',
        'text'  => 'Your health and well-being are our top priority.',
        'icon'  => '<path d="M12 3.5 5.5 6v5.5c0 4.4 2.8 7.9 6.5 9 3.7-1.1 6.5-4.6 6.5-9V6L12 3.5Z" /><path d="m9.5 12 1.8 1.8L15 10" />',
    ],
    [
        'title' => 'Compassion',
        'text'  => 'We treat every individual with care, respect, and personal attention.',
        'icon'  => '<path d="M12 20s-7-4.35-7-9.8A4.2 4.2 0 0 1 12 7.4a4.2 4.2 0 0 1 7 2.8c0 5.45-7 9.8-7 9.8Z" />',
    ],
    [
        'title' => 'Sustainability',
        'text'  => 'We support holistic health for you and the planet.',
        'icon'  => '<path d="M12 4c0 3-2 4.5-4.5 4.5S3 7 3 7s2-3.5 5.5-3.5S12 4 12 4Z" /><path d="M12 4c0 3 2 4.5 4.5 4.5S21 7 21 7s-2-3.5-5.5-3.5S12 4 12 4Z" /><path d="M12 4c-1.5 2-1.5 5 0 7 1.5-2 1.5-5 0-7Z" /><path d="M12 11v9" />',
    ],
];

if (!function_exists('valuesE')) {
    function valuesE($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
?>

<style>
    /* ==========================================================================
       What Guides Us — values panel.
       Uses the same --color-* / --font-heading tokens as the other sections.
       ========================================================================== */

    .our-values {
        --values-primary: var(--color-primary, #1f3a32);
        --values-accent-text: #8a6a22;
        --values-panel-bg: #edf0e3;
        --values-text: var(--color-text, #2a352f);
        --values-muted: var(--color-text-light, #5f6a63);
        --values-heading-font: var(--font-heading, "Cormorant Garamond", "Playfair Display", Georgia, "Times New Roman", serif);
        background: var(--color-bg, #f3f1e6);
        padding: clamp(28px, 4vw, 48px) clamp(24px, 3vw, 48px);
    }

    .our-values__panel {
        max-width: 1240px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: minmax(0, 0.85fr) minmax(0, 2.15fr);
        gap: clamp(28px, 4vw, 56px);
        align-items: center;
        border-radius: 20px;
        background: var(--values-panel-bg);
        padding: clamp(28px, 3.5vw, 44px) clamp(28px, 4vw, 48px);
    }

    .our-values__intro {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .our-values__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: var(--values-accent-text);
    }

    .our-values__eyebrow svg {
        width: 12px;
        height: 12px;
        flex: 0 0 auto;
    }

    .our-values__title {
        margin: 12px 0 0;
        font-family: var(--values-heading-font);
        font-size: clamp(1.5rem, 2.2vw, 1.9rem);
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: -0.01em;
        color: var(--values-primary);
    }

    .our-values__text {
        margin: 10px 0 0;
        max-width: 28ch;
        font-size: 0.88rem;
        line-height: 1.6;
        color: var(--values-text);
        opacity: 0.85;
    }

    .our-values__list {
        margin: 0;
        padding: 0;
        list-style: none;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: clamp(16px, 2.5vw, 32px);
    }

    .our-values__icon {
        width: 30px;
        height: 30px;
        color: var(--values-primary);
    }

    .our-values__item-title {
        margin: 14px 0 0;
        font-size: 0.94rem;
        font-weight: 700;
        color: var(--values-primary);
    }

    .our-values__item-text {
        margin: 6px 0 0;
        font-size: 0.82rem;
        line-height: 1.55;
        color: var(--values-muted);
    }

    /* ---- Tablet / mobile: stack --------------------------------------------- */
    @media (max-width: 899px) {
        .our-values__panel {
            grid-template-columns: minmax(0, 1fr);
        }

        .our-values__list {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 480px) {
        .our-values__list {
            grid-template-columns: minmax(0, 1fr);
        }
    }
</style>

<section class="our-values" aria-label="Our values">
    <div class="our-values__panel">
        <div class="our-values__intro">
            <p class="our-values__eyebrow">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="1.5" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                <?= valuesE($valuesEyebrow) ?>
            </p>
            <h2 class="our-values__title"><?= valuesE($valuesTitle) ?></h2>
            <p class="our-values__text"><?= valuesE($valuesText) ?></p>
        </div>

        <ul class="our-values__list">
            <?php foreach ($valuesItems as $item): ?>
                <li class="our-values__item">
                    <svg class="our-values__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $item['icon'] ?></svg>
                    <p class="our-values__item-title"><?= valuesE($item['title']) ?></p>
                    <p class="our-values__item-text"><?= valuesE($item['text']) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
