<?php
$usps = [
    [
        'label' => 'Scientifically Researched',
        'icon'  => '<path d="M20 6h16a2 2 0 0 1 2 2v28a2 2 0 0 1-2 2H20a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z"/><path d="M22 14h12M22 20h12M22 26h8"/><path d="M27 30c2 3 5 3 7 1"/>',
    ],
    [
        'label' => 'Clinically Tested',
        'icon'  => '<path d="M22 8h8M25 8v10l-7 12a3 3 0 0 0 3 4h12a3 3 0 0 0 3-4l-7-12V8"/><path d="M20 28h14"/><circle cx="24" cy="32" r="1.4" fill="currentColor" stroke="none"/><circle cx="29" cy="34" r="1.2" fill="currentColor" stroke="none"/>',
    ],
    [
        'label' => 'Free From Heavy Metals',
        'icon'  => '<circle cx="24" cy="24" r="3"/><circle cx="14" cy="16" r="2.6"/><circle cx="34" cy="16" r="2.6"/><circle cx="34" cy="32" r="2.6"/><circle cx="14" cy="32" r="2.6"/><path d="M16 17l6 5M32 17l-6 5M32 31l-6-5M16 31l6-5"/><path d="M11 11l26 26" stroke-width="2.4"/>',
    ],
    [
        'label' => 'Pesticide Free',
        'icon'  => '<circle cx="24" cy="24" r="3"/><circle cx="14" cy="16" r="2.6"/><circle cx="34" cy="16" r="2.6"/><circle cx="34" cy="32" r="2.6"/><circle cx="14" cy="32" r="2.6"/><path d="M16 17l6 5M32 17l-6 5M32 31l-6-5M16 31l6-5"/>',
    ],
    [
        'label' => 'Certified Organic By Ecocert',
        'icon'  => '<circle cx="24" cy="24" r="13"/><path d="M24 15c3 4 3 8 0 9-3-1-3-5 0-9z"/><path d="M24 15c-3 4-3 8 0 9M15 20c4-2 8-1 9 2-3 2-6.5 0.5-9-2z"/><path d="M15 28c4 2 8 1 9-2-3-2-6.5-.5-9 2z"/><path d="M33 20c-4-2-8-1-9 2 3 2 6.5.5 9-2z"/><path d="M33 28c-4 2-8 1-9-2 3-2 6.5-.5 9 2z"/>',
    ],
];
?>

<style>
    /* ==========================================================================
       Our USPs — static trust-badge row. Namespaced "usp". Single row at
       every breakpoint by design: on mobile the circles/type shrink rather
       than wrapping to a new line or stacking.
       ========================================================================== */

    .usp {
        padding: 44px 0 52px;
        background: var(--color-bg);
    }

    .usp__heading {
        text-align: center;
        font-size: 26px;
        font-weight: 700;
        color: var(--color-text);
        margin: 0 0 32px;
    }

    .usp__row {
        display: flex;
        flex-wrap: nowrap;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;

        /* Safety net if a very narrow screen still can't fit 7 items even
           at the smallest size below — scrolls instead of wrapping. */
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    .usp__row::-webkit-scrollbar {
        display: none;
    }

    .usp__item {
        flex: 1 1 0;
        min-width: 78px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .usp__circle {
        width: 84px;
        height: 84px;
        border-radius: 50%;
        border: 1px solid var(--color-border);
        background: var(--color-white);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        flex-shrink: 0;
    }

    .usp__circle svg {
        width: 40px;
        height: 40px;
        color: var(--color-primary);
        fill: none;
        stroke: currentColor;
        stroke-width: 1.6;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .usp__circle--seal svg {
        fill: currentColor;
        stroke: none;
    }

    .usp__label {
        font-size: 13px;
        font-weight: 600;
        line-height: 1.35;
        color: var(--color-text);
        max-width: 100px;
    }

    /* ---- Shrink, don't wrap ---- */

    @media (max-width: 900px) {
        .usp__circle {
            width: 68px;
            height: 68px;
            margin-bottom: 9px;
        }

        .usp__circle svg {
            width: 32px;
            height: 32px;
        }

        .usp__label {
            font-size: 11.5px;
            max-width: 84px;
        }

        .usp__heading {
            font-size: 22px;
            margin-bottom: 24px;
        }

        .usp__row {
            gap: 4px;
        }
    }

    @media (max-width: 560px) {
        .usp__circle {
            width: 52px;
            height: 52px;
            margin-bottom: 7px;
            border-width: 1px;
        }

        .usp__circle svg {
            width: 24px;
            height: 24px;
            stroke-width: 1.8;
        }

        .usp__label {
            font-size: 10px;
            max-width: 68px;
        }

        .usp__item {
            min-width: 58px;
        }

        .usp__heading {
            font-size: 19px;
            margin-bottom: 18px;
        }
    }
</style>

<section class="usp">
    <div class="container">
        <h2 class="usp__heading">Our USPs</h2>

        <div class="usp__row">
            <?php foreach ($usps as $item): ?>
                <div class="usp__item">
                    <div class="usp__circle">
                        <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                            <?= $item['icon'] ?>
                        </svg>
                    </div>
                    <span class="usp__label"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endforeach; ?>

            <div class="usp__item">
                <div class="usp__circle usp__circle--seal">
                    <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="24" cy="24" r="20" fill="none" stroke="currentColor" stroke-width="1.6" />
                        <circle cx="24" cy="24" r="16" fill="currentColor" opacity="0.12" />
                        <text x="24" y="27" text-anchor="middle" font-size="11" font-weight="700" fill="currentColor" font-family="inherit">GMP</text>
                    </svg>
                </div>
                <span class="usp__label">GMP Certified</span>
            </div>

            <div class="usp__item">
                <div class="usp__circle usp__circle--seal">
                    <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="24" cy="24" r="20" fill="none" stroke="currentColor" stroke-width="1.6" />
                        <circle cx="24" cy="24" r="16" fill="currentColor" opacity="0.12" />
                        <text x="24" y="22" text-anchor="middle" font-size="9" font-weight="700" fill="currentColor" font-family="inherit">ISO</text>
                        <text x="24" y="32" text-anchor="middle" font-size="6.5" font-weight="600" fill="currentColor" font-family="inherit">22000</text>
                    </svg>
                </div>
                <span class="usp__label">ISO 22000:2005</span>
            </div>
        </div>
    </div>
</section>