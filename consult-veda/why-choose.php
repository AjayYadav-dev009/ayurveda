<?php

/**
 * "Why [Store]'s Online Vaidya Consultation?" — consult-veda.
 *
 * Static content for now, same reasoning as health-conditions.php: no
 * dedicated table for this in the schema, so it's a plain PHP array
 * below rather than a DB query, shaped so a future fetch could drop in
 * without touching the markup.
 *
 * $storeName is a local placeholder — if config/config.php already
 * defines a site-name constant, swap the line below for that instead of
 * hardcoding it here.
 *
 * Usage (from consult-veda/index.php, after $conn is available):
 *   include __DIR__ . '/why-choose-us.php';
 */

$storeName = 'Ayurvedic Store';

$whyChooseUs = [
    [
        'title' => 'Root Cause Treatment',
        'description' => 'Address the root cause of imbalances for lasting well-being.',
        'icon' => 'leaf',
    ],
    [
        'title' => 'Certified Ayurvedic Doctors',
        'description' => 'A team of trained experienced vaidyas dedicated to your wellness.',
        'icon' => 'stethoscope',
    ],
    [
        'title' => 'Personalized Care',
        'description' => 'Every plan is customised to your dosha type, condition, and lifestyle.',
        'icon' => 'person',
    ],
    [
        'title' => 'Confidentiality Maintained',
        'description' => 'Your privacy is our priority, with complete end-to-end confidentiality.',
        'icon' => 'shield',
    ],
    [
        'title' => 'Get a Valid Prescription',
        'description' => "After consultation you'll be provided a valid prescription with all the recommendations.",
        'icon' => 'document',
    ],
    [
        'title' => 'Free Follow-up',
        'description' => 'Avail free follow-up upto 30 days.',
        'icon' => 'bell',
    ],
];
?>

<style>
    .why-us-section {
        padding: 56px 40px;
        background: var(--color-bg);
    }

    .why-us-header {
        max-width: 620px;
        margin: 0 auto 40px;
        text-align: center;
    }

    .why-us-header h2 {
        margin: 0 0 14px;
        font-size: 30px;
        font-weight: 800;
        color: var(--color-text);
        line-height: 1.35;
    }

    .why-us-header .why-us-rule {
        width: 44px;
        height: 3px;
        margin: 0 auto;
        background: var(--color-accent);
        border-radius: 2px;
    }

    .why-us-grid {
        max-width: var(--container-width);
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }

    @media (max-width: 900px) {
        .why-us-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 560px) {
        .why-us-grid {
            grid-template-columns: 1fr;
        }

        .why-us-section {
            padding: 36px 20px;
        }

        .why-us-header h2 {
            font-size: 24px;
        }
    }

    .why-us-card {
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-soft);
        padding: 26px 24px;
        transition: border-color 0.25s ease, transform 0.25s ease;
    }

    .why-us-card:hover {
        border-color: var(--color-accent);
        transform: translateY(-3px);
    }

    .why-us-card-top {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 12px;
    }

    .why-us-icon {
        width: 48px;
        height: 48px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--color-primary-light);
    }

    .why-us-icon svg {
        width: 24px;
        height: 24px;
        fill: none;
        stroke: var(--color-primary);
        stroke-width: 1.6;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .why-us-card h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: var(--color-text);
        line-height: 1.35;
    }

    .why-us-card p {
        margin: 0;
        font-size: 14px;
        line-height: 1.6;
        color: var(--color-text-light);
    }
</style>

<section class="why-us-section">
    <div class="why-us-header">
        <h2>Why <?= htmlspecialchars($storeName, ENT_QUOTES, 'UTF-8') ?>'s Online Vaidya Consultation?</h2>
        <div class="why-us-rule"></div>
    </div>

    <div class="why-us-grid">
        <?php foreach ($whyChooseUs as $item): ?>
            <div class="why-us-card">
                <div class="why-us-card-top">
                    <div class="why-us-icon" aria-hidden="true">
                        <?php switch ($item['icon']):
                            case 'leaf': ?>
                                <svg viewBox="0 0 24 24"><path d="M5 19c9 0 14-5 14-14 0 0-11-1-14 6-2 4-1 6 0 8z" /><path d="M5 19c2-5 5-8 9-10" /></svg>
                            <?php break;
                            case 'stethoscope': ?>
                                <svg viewBox="0 0 24 24"><path d="M6 3v6a4 4 0 008 0V3" /><path d="M10 13v2a5 5 0 0010 0v-2" /><circle cx="20" cy="10" r="1.6" /><path d="M6 3H4M14 3h2" /></svg>
                            <?php break;
                            case 'person': ?>
                                <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.6" /><path d="M5 20c1.5-4 4-6 7-6s5.5 2 7 6" /></svg>
                            <?php break;
                            case 'shield': ?>
                                <svg viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 5-3 8.5-7 10-4-1.5-7-5-7-10V6l7-3z" /><path d="M9 12l2 2 4-4" /></svg>
                            <?php break;
                            case 'document': ?>
                                <svg viewBox="0 0 24 24"><path d="M7 2h7l4 4v16H7z" /><path d="M14 2v4h4" /><path d="M10 13h6M10 17h6M10 9h2" /></svg>
                            <?php break;
                            case 'bell': ?>
                                <svg viewBox="0 0 24 24"><path d="M6 10a6 6 0 0112 0c0 4 1.5 5.5 2 6H4c.5-.5 2-2 2-6z" /><path d="M10 19a2 2 0 004 0" /></svg>
                            <?php break;
                        endswitch; ?>
                    </div>
                    <h3><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                </div>
                <p><?= htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>