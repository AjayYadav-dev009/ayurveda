<?php

/**
 * detux/information.php
 *
 * Included from detux/index.php, after hero.php, inside the same
 * <main>. No config/header/footer includes here — index.php already
 * handles those.
 */

/**
 * Small inline icon set for the tabbed cards below.
 */
if (!function_exists('detoxIcon')) {
    function detoxIcon(string $name): string
    {
        $icons = [
            'users' => '<path d="M9 8a3 3 0 100 6 3 3 0 000-6z"/><path d="M2 20c1-3.5 3.5-5.5 7-5.5s6 2 7 5.5"/><circle cx="17" cy="9" r="2.5"/><path d="M16 14.2c2.4.2 4.2 1.9 5.2 4.5"/>',
            'sparkles' => '<path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5L12 3z"/><path d="M19 15l.7 2 2 .7-2 .7-.7 2-.7-2-2-.7 2-.7.7-2z"/>',
            'alert' => '<circle cx="12" cy="12" r="9"/><path d="M12 8v5"/><circle cx="12" cy="16.3" r="0.6" fill="currentColor" stroke="none"/>',
            'thermometer' => '<path d="M12 14.5V5a2 2 0 10-4 0v9.5a4 4 0 104 0z"/>',
            'droplet' => '<path d="M12 3s6 7 6 11a6 6 0 11-12 0c0-4 6-11 6-11z"/>',
            'zap' => '<path d="M13 3L4 14h6l-1 7 9-11h-6l1-7z"/>',
            'activity' => '<path d="M3 12h4l2-7 4 14 2-7h6"/>',
            'user' => '<circle cx="12" cy="8" r="3.5"/><path d="M5 20c1.5-4 4.5-6 7-6s5.5 2 7 6"/>',
            'target' => '<circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="4"/><circle cx="12" cy="12" r="0.6" fill="currentColor" stroke="none"/>',
            'check' => '<path d="M5 13l4 4L19 7"/>',
        ];

        $path = $icons[$name] ?? $icons['check'];

        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" '
            . 'stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
    }
}

// -----------------------------------------------------------------------
// "Is this for you" tabbed section (hardcoded)
// -----------------------------------------------------------------------
$gutTabs = [
    'for_you_if' => [
        'label' => 'This is for you if',
        'icon'  => 'users',
        'items' => [
            [
                'icon'  => 'thermometer',
                'title' => 'Weak Digestion',
                'desc'  => 'You feel bloated, acidic, or constipated after meals, and for those dealing with GERD this is where healing begins.',
            ],
            [
                'icon'  => 'droplet',
                'title' => 'Toxin Buildup',
                'desc'  => "You feel dull, foggy, and low on energy, and rest alone doesn't seem to fix it.",
            ],
            [
                'icon'  => 'zap',
                'title' => 'Stress-Induced Imbalance',
                'desc'  => 'You feel wired, restless, or exhausted, and your sleep and digestion are paying for it.',
            ],
            [
                'icon'  => 'activity',
                'title' => 'Aggravated Pitta',
                'desc'  => 'You notice breakouts, dull skin, or hair fall — meaning your gut is likely signalling an internal imbalance.',
            ],
            [
                'icon'  => 'user',
                'title' => "Low Energy Won't Go Away",
                'desc'  => "You recovered from illness or stress, but your body still feels like it hasn't caught up.",
            ],
            [
                'icon'  => 'target',
                'title' => 'Finding the Root Cause',
                'desc'  => 'You want your body to fix the root cause, not just suppress symptoms of your poor gut health.',
            ],
        ],
    ],
    'how_it_works' => [
        'label' => 'How to reset your gut in 10 days?',
        'icon'  => 'sparkles',
        'items' => [
            [
                'icon'  => 'check',
                'title' => 'Understanding Your Body',
                'desc'  => 'Your Vaidya assesses your dosha, imbalances, and Ama levels so every step that follows is built around your body.',
            ],
            [
                'icon'  => 'check',
                'title' => 'Diet Guidelines',
                'desc'  => 'You receive diet guidelines because in Ayurveda, the right food at the right time is treatment in itself.',
            ],
            [
                'icon'  => 'check',
                'title' => 'Ayurvedic Formulations',
                'desc'  => 'Authentic Maharishi Ayurveda formulations, taken each morning and evening as guided.',
            ],
            [
                'icon'  => 'check',
                'title' => 'Guided Daily Videos',
                'desc'  => 'Each day comes with a short video walking you through exactly what to do and why it works.',
            ],
            [
                'icon'  => 'check',
                'title' => 'One-on-One Vaidya Consultation',
                'desc'  => 'Around Day 5, your Vaidya reviews progress and adjusts what is needed.',
            ],
            [
                'icon'  => 'check',
                'title' => 'Post-Detox Plan',
                'desc'  => 'Diet, herbs, and habits to keep results going — your Vaidya closes the loop.',
            ],
        ],
    ],
    'not_for_you' => [
        'label' => 'Do not take this gut reset if you have:',
        'icon'  => 'alert',
        'items' => [
            [
                'icon'  => 'check',
                'title' => 'Sugar or BP Out of Control',
                'desc'  => 'If your diabetes or blood pressure is currently unmanaged, the reset needs to wait until levels are stable.',
            ],
            [
                'icon'  => 'check',
                'title' => 'A Heart Condition',
                'desc'  => 'Cardiac conditions require medical clearance first. Please speak to your doctor before starting.',
            ],
            [
                'icon'  => 'check',
                'title' => 'Breastfeeding Mothers',
                'desc'  => 'The herbs and detox process may interfere with milk supply and infant health. Please wait until you are done nursing.',
            ],
            [
                'icon'  => 'check',
                'title' => 'Seasonal Infection',
                'desc'  => 'An active infection or inflammation is a signal to heal first. A detox works best when the body is not already fighting something.',
            ],
            [
                'icon'  => 'check',
                'title' => 'Pregnant Women',
                'desc'  => 'Your nutritional needs are different at this stage. This gut reset is not designed for this phase of life.',
            ],
            [
                'icon'  => 'check',
                'title' => 'Under 12 Years of Age',
                'desc'  => 'This reset is built for adult bodies. It is not suitable for children below 12 years.',
            ],
        ],
    ],
];
?>

<style>
    .detox-why {
        padding: 8px 0 64px;
    }

    .detox-tabs__bar {
        display: flex;
        gap: 4px;
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: 999px;
        padding: 6px;
        box-shadow: var(--shadow-soft);
        max-width: 100%;
        overflow-x: auto;
    }

    .detox-tab {
        flex: 1 1 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        white-space: nowrap;
        padding: 12px 20px;
        border-radius: 999px;
        border: none;
        background: transparent;
        color: var(--color-text-light);
        font-size: 14px;
        font-weight: 600;
    }

    .detox-tab svg {
        width: 16px;
        height: 16px;
        flex: 0 0 auto;
    }

    .detox-tab.is-active {
        background: var(--color-primary-dark);
        color: var(--color-white);
    }

    .detox-tabpanel {
        display: none;
        margin-top: 28px;
    }

    .detox-tabpanel.is-active {
        display: block;
    }

    .detox-cards {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 20px;
    }

    .detox-card {
        position: relative;
        overflow: hidden;
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        padding: 24px;
    }

    .detox-card__icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--color-primary-light);
        color: var(--color-primary-dark);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .detox-card__icon svg {
        width: 18px;
        height: 18px;
    }

    .detox-card__title {
        margin-top: 16px;
        font-size: 16px;
        color: var(--color-text);
    }

    .detox-card__desc {
        margin-top: 8px;
        font-size: 13.5px;
        line-height: 1.6;
        color: var(--color-text-light);
        max-width: 34ch;
    }

    .detox-card__number {
        position: absolute;
        right: 16px;
        bottom: 8px;
        font-size: 40px;
        font-weight: 700;
        color: var(--color-primary-light);
        line-height: 1;
    }

    @media (max-width: 860px) {
        .detox-cards {
            grid-template-columns: 1fr;
        }

        .detox-tabs__bar {
            border-radius: var(--radius-md);
        }

        .detox-tab {
            justify-content: flex-start;
        }
    }
</style>

<section class="detox-why">
    <div class="container">

        <div class="detox-tabs__bar" role="tablist">
            <?php foreach ($gutTabs as $tabKey => $tab): ?>
                <button
                    type="button"
                    class="detox-tab<?= $tabKey === 'for_you_if' ? ' is-active' : '' ?>"
                    data-tab-target="<?= htmlspecialchars($tabKey, ENT_QUOTES, 'UTF-8') ?>"
                    role="tab"
                >
                    <?= detoxIcon($tab['icon']) ?>
                    <span><?= htmlspecialchars($tab['label'], ENT_QUOTES, 'UTF-8') ?></span>
                </button>
            <?php endforeach; ?>
        </div>

        <?php foreach ($gutTabs as $tabKey => $tab): ?>
            <div class="detox-tabpanel<?= $tabKey === 'for_you_if' ? ' is-active' : '' ?>" data-tabpanel="<?= htmlspecialchars($tabKey, ENT_QUOTES, 'UTF-8') ?>">
                <div class="detox-cards">
                    <?php foreach ($tab['items'] as $cardIndex => $item): ?>
                        <div class="detox-card">
                            <div class="detox-card__icon"><?= detoxIcon($item['icon']) ?></div>
                            <h3 class="detox-card__title"><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="detox-card__desc"><?= htmlspecialchars($item['desc'], ENT_QUOTES, 'UTF-8') ?></p>
                            <span class="detox-card__number"><?= str_pad((string) ($cardIndex + 1), 2, '0', STR_PAD_LEFT) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</section>

<script>
    (function () {
        var tabs = document.querySelectorAll('.detox-tab');
        var panels = document.querySelectorAll('.detox-tabpanel');

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var target = tab.getAttribute('data-tab-target');

                tabs.forEach(function (t) {
                    t.classList.remove('is-active');
                });
                tab.classList.add('is-active');

                panels.forEach(function (panel) {
                    panel.classList.toggle('is-active', panel.getAttribute('data-tabpanel') === target);
                });
            });
        });
    })();
</script>