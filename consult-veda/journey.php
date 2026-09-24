<?php

/**
 * "Your Healing Journey" 4-step process strip for the consult-veda flow.
 *
 * Static content — a plain PHP array below rather than a DB query, same
 * pattern as condition.php. Each entry has a title, a short description and
 * an icon key so it can be swapped for a DB fetch later without touching
 * the markup or styles. Steps are numbered automatically from their order.
 *
 * Usage (from consult-veda/index.php, after $conn is available):
 *   include __DIR__ . '/healing-journey.php';
 */

$journeyEyebrow = 'Your Healing Journey';
$journeyHeading = 'Simple Steps to Better Health';

// Button under the steps. Set the label to '' to hide it.
$journeyCtaLabel = 'Book Your Slot Now';
$journeyCtaUrl   = '#book-slot';

$healingSteps = [
    ['label' => 'Book Consultation', 'text' => 'Share your concerns and health history.',            'icon' => 'calendar'],
    ['label' => 'Expert Analysis',   'text' => 'Get a detailed assessment from our Vaidya.',          'icon' => 'analysis'],
    ['label' => 'Personalised Plan', 'text' => 'Receive your custom care plan and recommendations.',  'icon' => 'leaf'],
    ['label' => 'Ongoing Support',   'text' => 'Track progress with follow-ups and guidance.',        'icon' => 'headset'],
];

// Icon artwork (24x24 outline paths). Trusted inline SVG markup, keyed by
// the `icon` value above. An unknown key falls back to 'leaf'.
$journeyIcons = [
    'calendar' => '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M4 10h16M9 3v4M15 3v4"/><path d="m9.5 15 2 2 3.5-3.6"/>',
    'analysis' => '<circle cx="10" cy="8" r="3.2"/><path d="M4 20c0-3.6 2.6-6 6-6 1.2 0 2.3.3 3.2.8"/><circle cx="17" cy="16" r="3"/><path d="m19.2 18.2 2 2"/>',
    'leaf'     => '<path d="M5 19c0-8 5-14 15-14 0 10-6 15-14 15z"/><path d="M5 19c4-5 7-8 11-10"/>',
    'headset'  => '<path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="3" y="13" width="4" height="6" rx="1.5"/><rect x="17" y="13" width="4" height="6" rx="1.5"/><path d="M19 19c0 1.5-2 2.5-5 2.5"/>',
];

if (!function_exists('journeyE')) {
    function journeyE($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
?>

<style>
    .journey-section {
        --jr-primary: var(--color-primary, #1f3a32);
        --jr-primary-dark: var(--color-primary-dark, #142a24);
        --jr-accent: var(--color-accent, #b28a32);
        --jr-accent-text: #82631a;
        --jr-accent-strong: #94701f;
        --jr-text: var(--color-text, #26342f);
        --jr-muted: #5c665f;
        --jr-node: var(--color-primary-light, #edf1e8);
        --jr-heading-font: var(--font-heading, "Cormorant Garamond", "Playfair Display", Georgia, "Times New Roman", serif);
        position: relative;
        overflow: hidden;
        padding: clamp(48px, 6vw, 80px) 24px;
        background: var(--color-bg, #f8f6ef);
    }

    .journey-sprite {
        position: absolute;
        width: 0;
        height: 0;
        overflow: hidden;
    }

    .journey-leaf {
        position: absolute;
        z-index: 0;
        left: -14px;
        top: 12%;
        width: clamp(70px, 7vw, 110px);
        color: var(--jr-primary);
        opacity: 0.1;
        transform: rotate(-18deg);
        pointer-events: none;
    }

    .journey-header {
        position: relative;
        z-index: 1;
        margin: 0 auto clamp(32px, 4vw, 52px);
        text-align: center;
    }

    .journey-eyebrow {
        margin: 0;
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--jr-accent-text);
    }

    .journey-heading {
        margin: 12px 0 0;
        font-family: var(--jr-heading-font);
        font-size: clamp(1.9rem, 3.1vw, 2.6rem);
        font-weight: 700;
        line-height: 1.12;
        letter-spacing: -0.01em;
        color: var(--jr-primary);
    }

    /* ---- Steps on a single thin line ---------------------------------------- */
    .journey-steps {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: clamp(20px, 3vw, 44px);
        max-width: var(--container-width, 1200px);
        margin: 0 auto;
        padding: 0;
        list-style: none;
    }

    /* the line runs behind the icon circles, through their centres */
    .journey-steps::before {
        content: "";
        position: absolute;
        top: 28px;
        left: 28px;
        right: 0;
        height: 1px;
        background: var(--jr-accent);
        opacity: 0.45;
    }

    .journey-step {
        position: relative;
    }

    .journey-icon {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--jr-node);
        box-shadow: inset 0 0 0 1px rgba(31, 58, 50, 0.08);
    }

    .journey-icon svg {
        width: 26px;
        height: 26px;
        fill: none;
        stroke: var(--jr-primary);
        stroke-width: 1.5;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .journey-step__number {
        display: block;
        margin: 20px 0 0;
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0.12em;
        color: var(--jr-accent-text);
    }

    .journey-step__title {
        margin: 4px 0 0;
        font-size: 1.05rem;
        font-weight: 700;
        line-height: 1.3;
        color: var(--jr-primary);
    }

    .journey-step__text {
        margin: 6px 0 0;
        max-width: 26ch;
        font-size: 0.88rem;
        line-height: 1.6;
        color: var(--jr-muted);
    }

    /* ---- Button ---------------------------------------------------------------- */
    .journey-cta {
        position: relative;
        z-index: 1;
        margin: clamp(32px, 4vw, 48px) 0 0;
        text-align: center;
    }

    .journey-cta__btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 28px;
        border-radius: 999px;
        background: var(--jr-accent-strong);
        color: #ffffff;
        font-size: 0.95rem;
        font-weight: 500;
        line-height: 1.2;
        text-decoration: none;
        transition: background-color 0.25s ease;
    }

    .journey-cta__btn svg {
        width: 16px;
        height: 16px;
        transition: transform 0.25s ease;
    }

    .journey-cta__btn:hover {
        background: #7a5d17;
    }

    .journey-cta__btn:hover svg {
        transform: translateX(3px);
    }

    .journey-cta__btn:focus-visible {
        outline: 3px solid var(--jr-primary);
        outline-offset: 3px;
    }

    /* ---- Tablet: two columns, no line ------------------------------------------ */
    @media (max-width: 899px) {
        .journey-steps {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 36px 28px;
        }

        .journey-steps::before {
            display: none;
        }
    }

    /* ---- Mobile: vertical timeline ----------------------------------------------- */
    @media (max-width: 559px) {
        .journey-section {
            padding-left: 20px;
            padding-right: 20px;
        }

        .journey-steps {
            grid-template-columns: minmax(0, 1fr);
            gap: 30px;
        }

        .journey-steps::before {
            display: block;
            top: 28px;
            bottom: 28px;
            left: 28px;
            right: auto;
            width: 1px;
            height: auto;
        }

        .journey-step {
            display: grid;
            grid-template-columns: 56px minmax(0, 1fr);
            column-gap: 18px;
            align-items: start;
        }

        .journey-icon {
            grid-row: span 3;
        }

        .journey-step__number {
            margin-top: 2px;
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .journey-cta__btn,
        .journey-cta__btn svg {
            transition: none;
        }

        .journey-cta__btn:hover svg {
            transform: none;
        }
    }
</style>

<section class="journey-section" aria-labelledby="journeyHeading">
    <svg class="journey-sprite" aria-hidden="true" focusable="false">
        <symbol id="journey-sprig" viewBox="0 0 120 200">
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
    <svg class="journey-leaf" viewBox="0 0 120 200" aria-hidden="true">
        <use href="#journey-sprig" />
    </svg>

    <div class="journey-header">
        <p class="journey-eyebrow"><?= journeyE($journeyEyebrow) ?></p>
        <h2 class="journey-heading" id="journeyHeading"><?= journeyE($journeyHeading) ?></h2>
    </div>

    <ol class="journey-steps">
        <?php foreach ($healingSteps as $i => $step):
            $iconSvg = $journeyIcons[$step['icon'] ?? 'leaf'] ?? $journeyIcons['leaf'];
            $text    = trim((string) ($step['text'] ?? ''));
        ?>
            <li class="journey-step">
                <div class="journey-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" focusable="false"><?= $iconSvg ?></svg>
                </div>
                <span class="journey-step__number" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span>
                <h3 class="journey-step__title"><?= journeyE($step['label']) ?></h3>
                <?php if ($text !== ''): ?>
                    <p class="journey-step__text"><?= journeyE($text) ?></p>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>

    <?php if ($journeyCtaLabel !== ''): ?>
        <p class="journey-cta">
            <a class="journey-cta__btn" href="<?= journeyE($journeyCtaUrl) ?>">
                <span><?= journeyE($journeyCtaLabel) ?></span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
            </a>
        </p>
    <?php endif; ?>
</section>