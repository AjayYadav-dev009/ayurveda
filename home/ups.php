<?php
// Brand values — static content, no database or certification claims.
$usps = [
    [
        'label' => 'Authentic Ayurveda',
        'desc'  => 'Time-tested wellness wisdom',
        'icon'  => '<path d="M16 28V14"/><path d="M16 18c-5 0-8-3-8-8 5 0 8 3 8 8Z"/><path d="M16 14c0-5 3-8 8-8 0 5-3 8-8 8Z"/><path d="M16 24c-3.500 0-5.500-2-5.500-5.500 3.500 0 5.500 2 5.500 5.500Z"/>',
    ],
    [
        'label' => 'Quality Assured',
        'desc'  => 'Carefully selected ingredients',
        'icon'  => '<path d="M16 5l9 3.5v7c0 6-3.8 10.5-9 12.5-5.2-2-9-6.5-9-12.5v-7L16 5Z"/><path d="M12 16l3 3 5-6"/>',
    ],
    [
        'label' => 'Trusted Experience',
        'desc'  => 'Built around customer needs',
        'icon'  => '<circle cx="16" cy="11" r="3.5"/><circle cx="7.5" cy="14" r="2.5"/><circle cx="24.5" cy="14" r="2.5"/><path d="M9.5 26c0-4 3-7 6.5-7s6.5 3 6.5 7"/><path d="M3 24c0-2.5 1.8-4.5 4.5-4.5M29 24c0-2.5-1.8-4.5-4.5-4.5"/>',
    ],
    [
        'label' => 'Holistic Wellness',
        'desc'  => 'A mindful approach to everyday care',
        'icon'  => '<path d="M16 6c2.5 2.5 3.8 5 3.8 7.800S18.5 19 16 21c-2.5-2-3.8-4.400-3.8-7.200S13.5 8.5 16 6Z"/><path d="M16 21c-5.5 0-9-2.5-10-7 3.5 0 7 1.200 9 4"/><path d="M16 21c5.5 0 9-2.5 10-7-3.5 0-7 1.200-9 4"/><path d="M9 26h14"/>',
    ],
];

$uspSprig = '<svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <path d="M10 190C60 150 110 100 175 20" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
    <path d="M52 152c-22-4-34-22-36-44 22 2 38 16 36 44Z" fill="currentColor" opacity=".55"/>
    <path d="M74 128c-6-24 2-44 24-56 8 24 0 44-24 56Z" fill="currentColor" opacity=".7"/>
    <path d="M100 100c14-18 34-24 56-18-8 22-28 30-56 18Z" fill="currentColor" opacity=".5"/>
    <path d="M124 70c-4-22 6-40 28-48 6 22-2 38-28 48Z" fill="currentColor" opacity=".7"/>
    <path d="M40 176c-18 4-32-4-40-20 18-6 34 0 40 20Z" fill="currentColor" opacity=".45"/>
</svg>';
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lora:wght@600;700&display=swap" rel="stylesheet">

<style>
    /* ==========================================================================
       Why Vedorishi — brand values. Namespaced "usp".
       Four columns with subtle vertical dividers on desktop; 2x2 on mobile.
       Palette: #245c4f (deep green), #91a96b (sage accent), #eaf4f0 (mist).
       ========================================================================== */

    .usp {
        --usp-green: #245c4f;
        --usp-sage: #91a96b;
        --usp-mist: #eaf4f0;
        --usp-text: #5a675f;
        --usp-serif: 'Lora', Georgia, 'Times New Roman', serif;

        position: relative;
        overflow: hidden;
        padding: 64px 0 72px;
        background: var(--color-bg);
    }

    .usp__leaf {
        position: absolute;
        color: var(--usp-sage);
        opacity: 0.28;
        pointer-events: none;
    }

    .usp__leaf svg { width: 100%; height: 100%; display: block; }
    .usp__leaf--tl { top: 8px; left: -18px; width: 130px; height: 130px; transform: rotate(-4deg); }
    .usp__leaf--br { bottom: 6px; right: -22px; width: 110px; height: 110px; transform: scaleX(-1) rotate(160deg); }

    .usp .container { position: relative; z-index: 1; }

    /* ---- Header ---- */

    .usp__head {
        text-align: center;
        max-width: 700px;
        margin: 0 auto 46px;
    }

    .usp__mark {
        display: block;
        width: 36px;
        height: 30px;
        margin: 0 auto 8px;
    }

    .usp__eyebrow {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 14px;
        margin: 0 0 14px;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.3em;
        text-transform: uppercase;
        color: var(--usp-green);
    }

    .usp__eyebrow::before,
    .usp__eyebrow::after {
        content: "";
        width: 60px;
        height: 1px;
        background: var(--usp-sage);
    }

    .usp__heading {
        margin: 0 0 14px;
        font-family: var(--usp-serif);
        font-size: 42px;
        line-height: 1.15;
        font-weight: 700;
        color: var(--usp-green);
    }

    .usp__subheading {
        margin: 0 auto;
        max-width: 560px;
        font-size: 16.5px;
        line-height: 1.65;
        color: var(--usp-text);
    }

    /* ---- Values ---- */

    .usp__grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        max-width: 1160px;
        margin: 0 auto;
    }

    .usp__item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 8px 28px;
    }

    .usp__item + .usp__item {
        border-left: 1px solid rgba(36, 92, 79, 0.14);
    }

    .usp__circle {
        width: 68px;
        height: 68px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 1.5px solid var(--usp-sage);
        background: var(--usp-mist);
        color: var(--usp-green);
        margin-bottom: 20px;
    }

    .usp__circle svg {
        width: 32px;
        height: 32px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.4;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .usp__title {
        margin: 0 0 8px;
        font-family: var(--usp-serif);
        font-size: 22px;
        line-height: 1.25;
        font-weight: 600;
        color: var(--usp-green);
    }

    .usp__desc {
        margin: 0;
        max-width: 220px;
        font-size: 15px;
        line-height: 1.6;
        color: var(--usp-text);
    }

    /* ---- Responsive ---- */

    @media (max-width: 900px) {
        .usp { padding: 48px 0 54px; }
        .usp__heading { font-size: 32px; }
        .usp__subheading { font-size: 15.5px; }
        .usp__head { margin-bottom: 34px; }
        .usp__eyebrow { letter-spacing: 0.22em; font-size: 12px; }
        .usp__eyebrow::before, .usp__eyebrow::after { width: 32px; }

        /* 2x2 */
        .usp__grid { grid-template-columns: repeat(2, 1fr); row-gap: 34px; }
        .usp__item { padding: 4px 16px; }
        .usp__item + .usp__item { border-left: 0; }
        .usp__item:nth-child(even) { border-left: 1px solid rgba(36, 92, 79, 0.14); }

        .usp__circle { width: 58px; height: 58px; margin-bottom: 14px; }
        .usp__circle svg { width: 28px; height: 28px; }
        .usp__title { font-size: 18px; }
        .usp__desc { font-size: 13.5px; }
    }

    @media (max-width: 420px) {
        .usp__heading { font-size: 27px; }
        .usp__item { padding: 4px 10px; }
        .usp__title { font-size: 16.5px; }
        .usp__desc { font-size: 13px; }
        .usp__leaf--br { display: none; }
    }
</style>

<section class="usp" aria-labelledby="usp-heading">
    <span class="usp__leaf usp__leaf--tl"><?= $uspSprig ?></span>
    <span class="usp__leaf usp__leaf--br"><?= $uspSprig ?></span>

    <div class="container">
        <div class="usp__head">
            <svg class="usp__mark" viewBox="0 0 40 34" fill="none" aria-hidden="true">
                <path d="M20 32V16" stroke="#245c4f" stroke-width="2" stroke-linecap="round"/>
                <path d="M20 18C20 9 14 4 5 4c0 9 5 14 15 14Z" fill="#91a96b"/>
                <path d="M20 18c0-9 6-14 15-14 0 9-5 14-15 14Z" fill="#245c4f"/>
            </svg>
            <p class="usp__eyebrow">Why Vedorishi</p>
            <h2 class="usp__heading" id="usp-heading">Wellness Rooted In Trust</h2>
            <p class="usp__subheading">Our values go beyond products. We are committed to pure ingredients, authentic Ayurveda and your long-term well-being.</p>
        </div>

        <div class="usp__grid">
            <?php foreach ($usps as $item): ?>
                <div class="usp__item">
                    <div class="usp__circle" aria-hidden="true">
                        <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                            <?= $item['icon'] ?>
                        </svg>
                    </div>
                    <h3 class="usp__title"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="usp__desc"><?= htmlspecialchars($item['desc'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>