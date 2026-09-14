<?php

/**
 * "Your Healing Journey" 4-step process strip for the consult-veda flow.
 *
 * Static content — a plain PHP array below rather than a DB query, same
 * pattern as health-conditions.php. Each entry has a number, label, and
 * icon key so it can be swapped for a DB fetch later without touching
 * the markup or styles.
 *
 * Usage (from consult-veda/index.php, after $conn is available):
 *   include __DIR__ . '/healing-journey.php';
 */

$healingSteps = [
    ['number' => 1, 'label' => 'Tell us about your issue(s)', 'icon' => 'person'],
    ['number' => 2, 'label' => 'Consult with Maharishi Expert Vaidya', 'icon' => 'stethoscope'],
    ['number' => 3, 'label' => 'Receive Personalized Ayurvedic Treatment Plan', 'icon' => 'plan'],
    ['number' => 4, 'label' => 'Dedicated Wellness Counsellor for Support', 'icon' => 'handshake'],
];
?>

<style>
    .journey-section {
        padding: 56px 40px;
        background: var(--color-white);
    }

    .journey-header {
        text-align: center;
        margin: 0 auto 44px;
    }

    .journey-header h2 {
        margin: 0 0 12px;
        font-size: 30px;
        font-weight: 800;
        color: var(--color-primary);
        font-family: var(--font-heading, inherit);
    }

    .journey-header .journey-divider {
        width: 48px;
        height: 2px;
        background: var(--color-border);
        margin: 0 auto;
    }

    .journey-steps {
        position: relative;
        max-width: var(--container-width);
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }

    .journey-steps::before {
        content: '';
        position: absolute;
        top: 32px;
        left: 12.5%;
        right: 12.5%;
        height: 1px;
        background: var(--color-accent);
        opacity: 0.5;
        z-index: 0;
    }

    .journey-step {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 16px;
        padding: 0 12px;
    }

    .journey-icon-wrap {
        position: relative;
        width: 64px;
        height: 64px;
    }

    .journey-icon {
        width: 64px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--color-primary-light);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-soft);
    }

    .journey-icon svg {
        width: 28px;
        height: 28px;
        fill: none;
        stroke: var(--color-primary);
        stroke-width: 1.6;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .journey-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: var(--color-accent);
        color: var(--color-white);
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .journey-step h3 {
        margin: 0;
        max-width: 170px;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.5;
        color: var(--color-primary);
    }

    .journey-cta {
        text-align: center;
        margin-top: 40px;
    }

    .journey-cta-btn {
        display: inline-block;
        padding: 14px 42px;
        background: var(--color-accent);
        color: var(--color-white);
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-decoration: none;
        border: none;
        border-radius: var(--radius-md, 6px);
        cursor: pointer;
        transition: opacity 0.25s ease, transform 0.25s ease;
    }

    .journey-cta-btn:hover {
        opacity: 0.9;
        transform: translateY(-2px);
    }

    @media (max-width: 900px) {
        .journey-steps {
            grid-template-columns: repeat(2, 1fr);
            gap: 36px 16px;
        }

        .journey-steps::before {
            display: none;
        }
    }

    @media (max-width: 480px) {
        .journey-section {
            padding: 36px 20px;
        }

        .journey-header h2 {
            font-size: 24px;
        }

        .journey-steps {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="journey-section">
    <div class="journey-header">
        <h2>Your Healing Journey</h2>
        <div class="journey-divider"></div>
    </div>

    <div class="journey-steps">
        <?php foreach ($healingSteps as $step): ?>
            <div class="journey-step">
                <div class="journey-icon-wrap">
                    <div class="journey-icon" aria-hidden="true">
                        <?php switch ($step['icon']):
                            case 'person': ?>
                                <svg viewBox="0 0 48 48"><circle cx="24" cy="16" r="7" /><path d="M10 40c0-8 6-13 14-13s14 5 14 13" /></svg>
                            <?php break;
                            case 'stethoscope': ?>
                                <svg viewBox="0 0 48 48"><path d="M14 8v10c0 5 4 9 9 9s9-4 9-9V8" /><path d="M23 27v5c0 5 4 9 9 9 3 0 6-2 7-5" /><circle cx="39" cy="31" r="3" /><path d="M14 8h-4M32 8h-4" /></svg>
                            <?php break;
                            case 'plan': ?>
                                <svg viewBox="0 0 48 48"><rect x="12" y="8" width="24" height="32" rx="2" /><path d="M18 8v-2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2" /><path d="M18 20h12M18 26h12M18 32h7" /></svg>
                            <?php break;
                            case 'handshake': ?>
                                <svg viewBox="0 0 48 48"><path d="M4 22l8-8 8 6-5 6" /><path d="M44 22l-8-8-8 6 5 6" /><path d="M15 20l6 6c1.5 1.5 4 1.5 5 0" /><path d="M20 26l5 5c1.5 1.5 4 1.5 5 0" /><path d="M25 20l7 7c1.5 1.5 4 1.5 5 0" /></svg>
                            <?php break;
                        endswitch; ?>
                    </div>
                    <span class="journey-badge"><?= (int) $step['number'] ?></span>
                </div>
                <h3><?= htmlspecialchars($step['label'], ENT_QUOTES, 'UTF-8') ?></h3>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="journey-cta">
        <a href="#book-slot" class="journey-cta-btn">Book Your Slot Now</a>
    </div>
</section>