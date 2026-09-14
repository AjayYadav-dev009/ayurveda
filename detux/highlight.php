<?php

/**
 * detux/highlights.php
 *
 * Included from detux/index.php alongside hero.php / information.php.
 * No config/header/footer includes here — index.php already handles
 * those.
 *
 * A row of quick facts about the programme. All hardcoded — edit the
 * $programHighlights array below to change labels/values.
 */

if (!function_exists('detoxHighlightIcon')) {
    function detoxHighlightIcon(string $name): string
    {
        $icons = [
            // Vaidya-guided consultations — clipboard with notes
            'consult' => '<rect x="6" y="6" width="12" height="15" rx="2"/><path d="M9 4h6a1 1 0 011 1v1H8V5a1 1 0 011-1z"/><path d="M9 11.5h6M9 15h4"/>',
            // Daily time — stopwatch
            'time' => '<circle cx="12" cy="13.5" r="7.5"/><path d="M12 13.5V9.5"/><path d="M9.5 3h5"/><path d="M18.5 6.5l1-1"/>',
            // Duration — hourglass
            'duration' => '<path d="M7 3h10"/><path d="M7 21h10"/><path d="M8 3c0 4.2 3.2 5.3 4 6-0.8 0.7-4 1.8-4 6"/><path d="M16 3c0 4.2-3.2 5.3-4 6 0.8 0.7 4 1.8 4 6"/>',
            // Difficulty — person climbing steps
            'difficulty' => '<circle cx="17.5" cy="5.5" r="1.8"/><path d="M4 20h4v-4h4v-4h4v-3.5"/>',
            // Format — screen + kit box
            'format' => '<rect x="3" y="5" width="13" height="9" rx="1.2"/><path d="M2 17h15"/><rect x="16.5" y="9.5" width="5.5" height="5.5" rx="1"/>',
        ];

        $path = $icons[$name] ?? $icons['consult'];

        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" '
            . 'stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
    }
}

$programHighlights = [
    [
        'icon'  => 'consult',
        'label' => 'Vaidya Guided',
        'value' => '3 Consultations',
    ],
    [
        'icon'  => 'time',
        'label' => 'Daily Time',
        'value' => '20-30 Minutes',
    ],
    [
        'icon'  => 'duration',
        'label' => 'Duration',
        'value' => '10 Days',
    ],
    [
        'icon'  => 'difficulty',
        'label' => 'Difficulty Level',
        'value' => 'Beginner Friendly',
    ],
    [
        'icon'  => 'format',
        'label' => 'Format',
        'value' => 'Videos + Guidance + Kit',
    ],
];
?>

<style>
    .detox-highlights {
        padding: 8px 0 64px;
    }

    .detox-highlights__row {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 20px;
    }

    .detox-highlight {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .detox-highlight__icon {
        width: 100%;
        aspect-ratio: 1;
        max-width: 110px;
        border-radius: var(--radius-md);
        background: var(--color-primary-light);
        color: var(--color-primary-dark);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .detox-highlight__icon svg {
        width: 40%;
        height: 40%;
    }

    .detox-highlight__label {
        font-size: 14px;
        font-weight: 600;
        color: var(--color-text);
    }

    .detox-highlight__value {
        margin-top: 2px;
        font-size: 13px;
        color: var(--color-text-light);
    }

    @media (max-width: 860px) {
        .detox-highlights__row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>

<section class="detox-highlights">
    <div class="container">
        <div class="detox-highlights__row">
            <?php foreach ($programHighlights as $highlight): ?>
                <div class="detox-highlight">
                    <div class="detox-highlight__icon"><?= detoxHighlightIcon($highlight['icon']) ?></div>
                    <div class="detox-highlight__text">
                        <div class="detox-highlight__label"><?= htmlspecialchars($highlight['label'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="detox-highlight__value"><?= htmlspecialchars($highlight['value'], ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>