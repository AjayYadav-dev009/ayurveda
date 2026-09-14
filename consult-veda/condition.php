<?php

/**
 * "Health Conditions We Treat" grid for the consult-veda flow.
 *
 * Static content for now — there's no conditions table in the current
 * schema, so this is a plain PHP array below rather than a DB query. If a
 * `conditions` table gets added later, swap $healthConditions for a fetch
 * and everything else (markup, styles) stays the same, since each entry
 * already has exactly the shape a DB row would (label + icon key).
 *
 * Usage (from consult-veda/index.php, after $conn is available):
 *   include __DIR__ . '/health-conditions.php';
 */

$healthConditions = [
    ['label' => 'Digestive Health', 'icon' => 'digestive'],
    ['label' => 'Joint Problems', 'icon' => 'joint'],
    ['label' => 'Women Wellness', 'icon' => 'women'],
    ['label' => 'Diabetes & Kidney Health', 'icon' => 'kidney'],
    ['label' => 'Respiratory Issues', 'icon' => 'respiratory'],
    ['label' => 'Skin & Hair', 'icon' => 'skin'],
    ['label' => 'Heart Health', 'icon' => 'heart'],
    ['label' => 'Liver Issues', 'icon' => 'liver'],
];
?>

<style>
    .conditions-section {
        padding: 56px 40px;
        background: var(--color-bg);
    }

    .conditions-header {
        max-width: 560px;
        margin: 0 auto 40px;
        text-align: center;
    }

    .conditions-header h2 {
        margin: 0 0 10px;
        font-size: 32px;
        font-weight: 800;
        color: var(--color-primary);
    }

    .conditions-header p {
        margin: 0;
        color: var(--color-text-light);
        font-size: 15px;
        line-height: 1.6;
    }

    .conditions-grid {
        max-width: var(--container-width);
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
    }

    @media (max-width: 900px) {
        .conditions-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 480px) {
        .conditions-grid {
            grid-template-columns: 1fr;
        }

        .conditions-section {
            padding: 36px 20px;
        }

        .conditions-header h2 {
            font-size: 26px;
        }
    }

    .condition-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 14px;
        padding: 28px 20px;
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-soft);
        transition: border-color 0.25s ease, transform 0.25s ease;
    }

    .condition-card:hover {
        border-color: var(--color-accent);
        transform: translateY(-3px);
    }

    .condition-icon {
        width: 72px;
        height: 72px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--color-primary-light);
    }

    .condition-icon svg {
        width: 34px;
        height: 34px;
        fill: none;
        stroke: var(--color-primary);
        stroke-width: 1.6;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .condition-card h3 {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
        color: var(--color-text);
    }
</style>

<section class="conditions-section">
    <div class="conditions-header">
        <h2>Health Conditions We Treat</h2>
        <p>Our Vaidyas focus on identifying and treating the root cause of your health concerns.</p>
    </div>

    <div class="conditions-grid">
        <?php foreach ($healthConditions as $condition): ?>
            <div class="condition-card">
                <div class="condition-icon" aria-hidden="true">
                    <?php switch ($condition['icon']):
                        case 'digestive': ?>
                            <svg viewBox="0 0 48 48"><path d="M18 8c-4 0-7 3-7 7 0 5 4 6 4 11 0 5-4 6-4 11 0 4 3 7 7 7" /><path d="M22 8c6 0 9 5 9 10s-4 7-4 12 4 7 4 11c0 4-3 7-7 7" /><circle cx="30" cy="14" r="2.4" /></svg>
                        <?php break;
                        case 'joint': ?>
                            <svg viewBox="0 0 48 48"><path d="M14 10c2 6 2 11 0 16" /><path d="M14 26c6-2 11-2 16 0" /><path d="M30 26c2 6 2 11 0 16" /><circle cx="14" cy="18" r="3.5" /><circle cx="30" cy="34" r="3.5" /><path d="M8 8l6 4M14 6v6" /></svg>
                        <?php break;
                        case 'women': ?>
                            <svg viewBox="0 0 48 48"><circle cx="24" cy="17" r="10" /><path d="M24 27v13" /><path d="M18 34h12" /></svg>
                        <?php break;
                        case 'kidney': ?>
                            <svg viewBox="0 0 48 48"><path d="M20 10c-6 0-10 5-10 12s4 14 10 14c4 0 5-3 5-6s-2-5-2-9 3-5 3-9c0-1.3-1-2-2-2-1.6 0-2.6 1.2-4 0z" /><path d="M32 16c2.4 2 4 5 4 9 0 6-3 12-8 13" /><path d="M32 33c1 2 1 4 0 6" /></svg>
                        <?php break;
                        case 'respiratory': ?>
                            <svg viewBox="0 0 48 48"><path d="M24 8v14" /><path d="M24 22c-2-6-6-8-10-8-3 0-5 2-5 6 0 8 5 15 11 15 2.6 0 4-2 4-5" /><path d="M24 22c2-6 6-8 10-8 3 0 5 2 5 6 0 8-5 15-11 15-2.6 0-4-2-4-5" /><path d="M18 12l3 4M30 12l-3 4" /></svg>
                        <?php break;
                        case 'skin': ?>
                            <svg viewBox="0 0 48 48"><path d="M12 30c2-8 3-14 3-18" /><path d="M18 30c2-9 3-15 3-20" /><path d="M24 30c2-10 3-16 3-22" /><path d="M30 30c2-9 3-15 3-20" /><path d="M36 30c2-8 3-14 3-18" /><path d="M10 34h28" /></svg>
                        <?php break;
                        case 'heart': ?>
                            <svg viewBox="0 0 48 48"><path d="M24 38C12 30 6 23 6 16.5 6 11.7 9.8 8 14.5 8c3 0 5.7 1.6 7.5 4.2C23.8 9.6 26.5 8 29.5 8 34.2 8 38 11.7 38 16.5 38 23 32 30 24 38z" /><path d="M13 20h5l2.5-5 3 9 2.5-5H31" /></svg>
                        <?php break;
                        case 'liver': ?>
                            <svg viewBox="0 0 48 48"><path d="M9 22c0-6 5-11 12-11 3 0 5 1.4 7 1.4 2.6 0 4.5-2 8-2 6 0 12 5 12 12 0 8-7 14-19 14-11 0-20-5.6-20-14.4z" /><path d="M16 24c2-2 4-2 6 0s4 2 6 0 4-2 6 0" /></svg>
                        <?php break;
                    endswitch; ?>
                </div>
                <h3><?= htmlspecialchars($condition['label'], ENT_QUOTES, 'UTF-8') ?></h3>
            </div>
        <?php endforeach; ?>
    </div>
</section>