<?php

$conditionsEyebrow = 'Health Conditions';
$conditionsHeading = 'Health Conditions We Treat';
$conditionsIntro   = 'From common concerns to chronic conditions, our Ayurvedic experts address the root cause for long-term wellness.';

$healthConditions = [
    ['label' => 'Digestive Issues',    'text' => 'Acidity, bloating, IBS, constipation and more.', 'icon' => 'digestive'],
    ['label' => 'Skin & Hair Care',    'text' => 'Acne, eczema, hair fall, healthy glow.',         'icon' => 'leaf'],
    ['label' => 'Hormonal Balance',    'text' => 'PCOS, thyroid, menstrual health.',               'icon' => 'lotus'],
    ['label' => 'Weight Management',   'text' => 'Healthy weight, metabolism and lifestyle.',      'icon' => 'weight'],
    ['label' => 'Stress & Anxiety',    'text' => 'Better sleep, emotional balance and calmness.',  'icon' => 'lotus'],
    ['label' => 'Immunity Boost',      'text' => 'Stronger defense, seasonal wellness.',           'icon' => 'shield'],
    ['label' => 'Joint & Muscle Pain', 'text' => 'Arthritis, stiffness and mobility.',             'icon' => 'joint'],
    ['label' => 'Chronic Conditions',  'text' => 'Diabetes, hypertension and long-term care.',     'icon' => 'leaf'],
];

// Icon artwork (24x24 outline paths). Trusted inline SVG markup, keyed by
// the `icon` value used above. An unknown key falls back to 'leaf'.
$conditionIcons = [
    'digestive' => '<path d="M9 3v3.5c0 1.6-.6 2.6-2 3.6S4 12.8 4 15.5C4 19 7 21 10.5 21c3.5 0 6.5-1.5 7.5-4.5.6-1.8 0-3.5-1.5-4.5S14 10 14.5 8c.3-1.3 1.3-2.5 3-2.5"/><path d="M9 3H6.5"/>',
    'leaf'      => '<path d="M5 19c0-8 5-14 15-14 0 10-6 15-14 15z"/><path d="M5 19c4-5 7-8 11-10"/>',
    'lotus'     => '<path d="M12 4c2.6 2.6 3.4 5.8 0 10.5C8.6 9.8 9.4 6.6 12 4z"/><path d="M12 14.5c-1-3.2-4-4.6-8-4.1 0 4.2 3 7.1 8 7.1"/><path d="M12 14.5c1-3.2 4-4.6 8-4.1 0 4.2-3 7.1-8 7.1"/><path d="M7 20.5h10"/>',
    'weight'    => '<circle cx="12" cy="5" r="2.2"/><path d="M12 8.2V13"/><path d="M7.5 10l4.5-1.8 4.5 1.8"/><path d="m12 13-3 7.5M12 13l3 7.5"/>',
    'shield'    => '<path d="M12 3 4.5 6v5.5c0 4.5 3.2 8.2 7.5 9.5 4.3-1.3 7.5-5 7.5-9.5V6L12 3z"/><path d="m8.8 12.2 2.3 2.3 4.1-4.4"/>',
    'joint'     => '<path d="m8.2 8.2 7.6 7.6"/><circle cx="5.8" cy="8.4" r="2"/><circle cx="8.4" cy="5.8" r="2"/><circle cx="15.6" cy="18.2" r="2"/><circle cx="18.2" cy="15.6" r="2"/>',
];

if (!function_exists('conditionE')) {
    function conditionE($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
?>

<style>
    .conditions-section {
        --cond-primary: var(--color-primary, #1f3a32);
        --cond-accent-text: #82631a;
        --cond-card: var(--color-primary-light, #edf1e8);
        --cond-text: var(--color-text, #26342f);
        --cond-muted: #5c665f;
        --cond-heading-font: var(--font-heading, "Cormorant Garamond", "Playfair Display", Georgia, "Times New Roman", serif);
        position: relative;
        overflow: hidden;
        padding: clamp(48px, 6vw, 88px) 0;
        background: var(--color-bg, #f8f6ef);
    }

    .conditions-sprite {
        position: absolute;
        width: 0;
        height: 0;
        overflow: hidden;
    }

    .conditions-leaf {
        position: absolute;
        z-index: 0;
        color: var(--cond-primary);
        opacity: 0.1;
        pointer-events: none;
    }

    .conditions-leaf--tr {
        top: -10px;
        right: -14px;
        width: clamp(80px, 8vw, 128px);
        transform: rotate(28deg);
    }

    .conditions-leaf--bl {
        bottom: -14px;
        left: -12px;
        width: clamp(80px, 8vw, 120px);
        transform: rotate(-24deg) scaleX(-1);
    }

    .conditions-inner {
        position: relative;
        z-index: 1;
        max-width: var(--container-width, 1200px);
        margin: 0 auto;
        padding: 0 24px;
    }

    /* ---- Header (left aligned) ---------------------------------------- */
    .conditions-eyebrow {
        margin: 0;
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--cond-accent-text);
    }

    .conditions-heading {
        margin: 12px 0 0;
        font-family: var(--cond-heading-font);
        font-size: clamp(2rem, 3.4vw, 2.9rem);
        font-weight: 700;
        line-height: 1.1;
        letter-spacing: -0.01em;
        color: var(--cond-primary);
    }

    .conditions-intro {
        margin: 14px 0 0;
        max-width: 46ch;
        font-size: clamp(0.95rem, 1.1vw, 1.02rem);
        line-height: 1.65;
        color: var(--cond-text);
        opacity: 0.85;
    }

    /* ---- Grid ------------------------------------------------------------ */
    .conditions-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: clamp(14px, 1.8vw, 24px);
        margin: clamp(28px, 3.6vw, 44px) 0 0;
        padding: 0;
        list-style: none;
    }

    .condition-card {
        padding: clamp(20px, 2vw, 28px);
        border-radius: 14px;
        background: rgba(237, 241, 232, 0.92);
        box-shadow: inset 0 0 0 1px rgba(31, 58, 50, 0.06);
    }

    .condition-icon {
        display: block;
        width: 34px;
        height: 34px;
        fill: none;
        stroke: var(--cond-primary);
        stroke-width: 1.4;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .condition-card__title {
        margin: 22px 0 0;
        font-family: var(--cond-heading-font);
        font-size: 1.1rem;
        font-weight: 700;
        line-height: 1.25;
        color: var(--cond-primary);
    }

    .condition-card__text {
        margin: 6px 0 0;
        font-size: 0.86rem;
        line-height: 1.55;
        color: var(--cond-muted);
    }

    @media (max-width: 1023px) {
        .conditions-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 479px) {
        .conditions-inner {
            padding: 0 20px;
        }

        .conditions-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .condition-card {
            display: grid;
            grid-template-columns: 34px minmax(0, 1fr);
            column-gap: 16px;
            align-items: start;
        }

        .condition-icon {
            grid-row: span 2;
        }

        .condition-card__title {
            margin-top: 0;
        }
    }
</style>

<section class="conditions-section" aria-labelledby="conditionsHeading">
    <svg class="conditions-sprite" aria-hidden="true" focusable="false">
        <symbol id="conditions-sprig" viewBox="0 0 120 200">
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
    <svg class="conditions-leaf conditions-leaf--tr" viewBox="0 0 120 200" aria-hidden="true">
        <use href="#conditions-sprig" />
    </svg>
    <svg class="conditions-leaf conditions-leaf--bl" viewBox="0 0 120 200" aria-hidden="true">
        <use href="#conditions-sprig" />
    </svg>

    <div class="conditions-inner">
        <p class="conditions-eyebrow"><?= conditionE($conditionsEyebrow) ?></p>
        <h2 class="conditions-heading" id="conditionsHeading"><?= conditionE($conditionsHeading) ?></h2>
        <p class="conditions-intro"><?= conditionE($conditionsIntro) ?></p>

        <ul class="conditions-grid">
            <?php foreach ($healthConditions as $condition):
                $iconKey = $condition['icon'] ?? 'leaf';
                $iconSvg = $conditionIcons[$iconKey] ?? $conditionIcons['leaf'];
                $text    = trim((string) ($condition['text'] ?? ''));
            ?>
                <li class="condition-card">
                    <svg class="condition-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><?= $iconSvg ?></svg>
                    <h3 class="condition-card__title"><?= conditionE($condition['label']) ?></h3>
                    <?php if ($text !== ''): ?>
                        <p class="condition-card__text"><?= conditionE($text) ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>