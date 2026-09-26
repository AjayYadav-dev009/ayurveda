<?php

/**
 * Static "Don't Know Your Dosha Yet?" homepage panel.
 *
 * Purely presentational — no database queries. Drop in wherever it belongs
 * on the homepage:
 *
 *   include __DIR__ . '/partials/dosha-banner.php';
 *
 * The only dynamic piece is the CTA link, which points at a quiz page via
 * BASE_URL — update the URL below once that page exists.
 */
$doshaQuizUrl = (defined('BASE_URL') ? BASE_URL : '/') . 'dosha/dosha-test-cta.php';

$dqSprig = '<svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <path d="M10 190C60 150 110 100 175 20" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
    <path d="M52 152c-22-4-34-22-36-44 22 2 38 16 36 44Z" stroke="currentColor" stroke-width="1.4"/>
    <path d="M74 128c-6-24 2-44 24-56 8 24 0 44-24 56Z" stroke="currentColor" stroke-width="1.4"/>
    <path d="M100 100c14-18 34-24 56-18-8 22-28 30-56 18Z" stroke="currentColor" stroke-width="1.4"/>
    <path d="M124 70c-4-22 6-40 28-48 6 22-2 38-28 48Z" stroke="currentColor" stroke-width="1.4"/>
    <path d="M40 176c-18 4-32-4-40-20 18-6 34 0 40 20Z" stroke="currentColor" stroke-width="1.4"/>
</svg>';
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lora:wght@600;700&display=swap" rel="stylesheet">

<style>
    /* ==========================================================================
       Dosha education panel — namespaced "dq". A large rounded dark-green
       panel sitting on the page background (a soft transition into the
       footer). Colors are local to this section:
         #17483d panel · cream text · #d8b678 gold · #91a96b sage
       ========================================================================== */

    .dq {
        --dq-bg: #17483d;
        --dq-cream: #f4efe2;
        --dq-gold: #d8b678;
        --dq-sage: #91a96b;
        --dq-line: rgba(216, 182, 120, 0.4);
        --dq-serif: 'Lora', Georgia, 'Times New Roman', serif;

        padding: 36px 0 64px;
        background: var(--color-bg);
    }

    .dq__panel {
        position: relative;
        overflow: hidden;
        border-radius: 30px;
        background:
            radial-gradient(ellipse 60% 70% at 78% 50%, rgba(145, 169, 107, 0.16), rgba(145, 169, 107, 0) 70%),
            radial-gradient(ellipse 50% 60% at 8% 100%, rgba(0, 0, 0, 0.18), rgba(0, 0, 0, 0) 70%),
            var(--dq-bg);
        color: var(--dq-cream);
        box-shadow: 0 18px 40px rgba(23, 72, 61, 0.22);
    }

    /* Very faint botanical pattern */
    .dq__panel::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140' viewBox='0 0 140 140' fill='none' stroke='%2391a96b' stroke-width='1.2'%3E%3Cpath d='M20 40c0-14 8-22 20-24 2 14-6 24-20 24Z'/%3E%3Cpath d='M20 40l14-14'/%3E%3Cpath d='M92 30c10-8 22-6 28 4-10 8-22 6-28-4Z'/%3E%3Cpath d='M92 30l22 2'/%3E%3Cpath d='M60 110c0-14 8-22 20-24 2 14-6 24-20 24Z'/%3E%3Cpath d='M60 110l14-14'/%3E%3Cpath d='M14 116c8-6 16-4 20 4-8 6-16 4-20-4Z'/%3E%3Ccircle cx='118' cy='96' r='2'/%3E%3Ccircle cx='70' cy='54' r='1.5'/%3E%3C/svg%3E");
        background-size: 140px 140px;
        opacity: 0.07;
        pointer-events: none;
    }

    .dq__leaf {
        position: absolute;
        color: var(--dq-sage);
        opacity: 0.16;
        pointer-events: none;
    }

    .dq__leaf svg {
        width: 100%;
        height: 100%;
        display: block;
    }

    .dq__leaf--tr {
        top: -14px;
        right: -18px;
        width: 190px;
        height: 190px;
        transform: scaleX(-1) rotate(-6deg);
    }

    .dq__leaf--bl {
        bottom: -20px;
        left: -16px;
        width: 170px;
        height: 170px;
        transform: rotate(-2deg);
    }

    .dq__body {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        align-items: center;
        gap: 40px;
        padding: 56px 64px;
    }

    /* ---- Text column ---- */

    .dq__mark {
        display: block;
        width: 30px;
        height: 26px;
        margin: 0 0 8px 108px;
    }

    .dq__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 14px;
        margin: 0 0 20px;
        font-size: 12.5px;
        font-weight: 500;
        letter-spacing: 0.34em;
        text-transform: uppercase;
        color: var(--dq-cream);
    }

    .dq__eyebrow::before,
    .dq__eyebrow::after {
        content: "";
        width: 44px;
        height: 1px;
        background: var(--dq-gold);
        opacity: 0.7;
    }

    .dq__title {
        margin: 0 0 18px;
        font-family: var(--dq-serif);
        font-size: 46px;
        line-height: 1.12;
        font-weight: 700;
        color: var(--dq-cream);
    }

    .dq__text {
        margin: 0 0 28px;
        max-width: 470px;
        font-size: 16.5px;
        line-height: 1.7;
        color: var(--dq-cream);
        opacity: 0.88;
    }

    .dq__cta {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 15px 30px;
        border-radius: 999px;
        background: #eaf4f0;
        color: #17483d;
        font-size: 15.5px;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18);
        transition: transform 0.2s ease, background 0.2s ease;
    }

    .dq__cta svg {
        width: 18px;
        height: 18px;
        transition: transform 0.2s ease;
    }

    .dq__cta:hover {
        background: #fff;
        transform: translateY(-2px);
    }

    .dq__cta:hover svg {
        transform: translateX(3px);
    }

    .dq__cta:focus-visible {
        outline: 2px solid var(--dq-gold);
        outline-offset: 3px;
    }

    .dq__note {
        margin: 16px 0 0;
        font-size: 12.5px;
        line-height: 1.5;
        color: var(--dq-cream);
        opacity: 0.6;
    }

    /* ---- Circular visual ---- */

    .dq__visual {
        position: relative;
        width: 100%;
        max-width: 500px;
        aspect-ratio: 1 / 1;
        margin: 0 auto;
    }

    .dq__art {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        color: var(--dq-gold);
    }

    .dq__node {
        position: absolute;
        width: 18%;
        transform: translate(-50%, -50%);
    }

    .dq__node--vata {
        left: 50%;
        top: 19%;
    }

    .dq__node--pitta {
        left: 21.4%;
        top: 68.5%;
    }

    .dq__node--kapha {
        left: 78.6%;
        top: 68.5%;
    }

    .dq__badge {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        aspect-ratio: 1;
        border-radius: 50%;
        border: 1.5px solid var(--dq-gold);
        box-shadow: 0 0 0 5px rgba(216, 182, 120, 0.1), 0 8px 18px rgba(0, 0, 0, 0.28);
        color: var(--dq-cream);
    }

    .dq__badge svg {
        width: 50%;
        height: 50%;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.4;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .dq__node--vata .dq__badge {
        background: radial-gradient(circle at 30% 25%, #2f7a68, #1c5a4c);
    }

    .dq__node--pitta .dq__badge {
        background: radial-gradient(circle at 30% 25%, #a4772f, #6d4b1a);
    }

    .dq__node--kapha .dq__badge {
        background: radial-gradient(circle at 30% 25%, #4d8a45, #2e5f2b);
    }

    .dq__label {
        position: absolute;
        top: 100%;
        left: 50%;
        width: 230%;
        transform: translateX(-50%);
        margin-top: 10px;
        text-align: center;
    }

    .dq__label-name {
        display: block;
        font-family: var(--dq-serif);
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        color: var(--dq-gold);
    }

    .dq__label-tags {
        display: block;
        margin-top: 4px;
        font-size: 12.5px;
        line-height: 1.5;
        color: var(--dq-cream);
        opacity: 0.85;
    }

    .dq__label-tags span {
        white-space: nowrap;
    }

    .dq__label-tags span+span::before {
        content: " \2022 ";
        opacity: 0.7;
    }

    /* ---- Responsive ---- */

    @media (max-width: 980px) {
        .dq__body {
            padding: 44px 36px;
            gap: 28px;
        }

        .dq__title {
            font-size: 38px;
        }

        .dq__mark {
            margin-left: 86px;
        }
    }

    @media (max-width: 820px) {
        .dq {
            padding: 24px 0 48px;
        }

        .dq__panel {
            border-radius: 24px;
        }

        .dq__body {
            grid-template-columns: 1fr;
            padding: 40px 24px 34px;
            gap: 34px;
            text-align: center;
        }

        .dq__mark {
            margin: 0 auto 8px;
        }

        .dq__eyebrow {
            letter-spacing: 0.26em;
            font-size: 11.5px;
        }

        .dq__eyebrow::before,
        .dq__eyebrow::after {
            width: 26px;
        }

        .dq__title {
            font-size: 32px;
        }

        .dq__text {
            margin-left: auto;
            margin-right: auto;
            font-size: 15.5px;
        }

        .dq__visual {
            max-width: 400px;
        }

        .dq__label-name {
            font-size: 13.5px;
            letter-spacing: 0.16em;
        }

        .dq__label-tags {
            font-size: 11px;
        }
    }

    @media (max-width: 480px) {
        .dq__title {
            font-size: 27px;
        }

        .dq__label-tags {
            display: none;
        }

        .dq__label {
            margin-top: 6px;
        }

        .dq__label-name {
            font-size: 12px;
            letter-spacing: 0.12em;
        }

        .dq__leaf--tr {
            width: 120px;
            height: 120px;
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .dq__cta,
        .dq__cta svg {
            transition: none;
        }
    }
</style>

<section class="dq" aria-labelledby="dq-title">
    <div class="container">
        <div class="dq__panel">
            <span class="dq__leaf dq__leaf--tr"><?= $dqSprig ?></span>
            <span class="dq__leaf dq__leaf--bl"><?= $dqSprig ?></span>

            <div class="dq__body">
                <div class="dq__col">
                    <svg class="dq__mark" viewBox="0 0 40 34" fill="none" aria-hidden="true">
                        <path d="M20 32V16" stroke="#d8b678" stroke-width="1.8" stroke-linecap="round" />
                        <path d="M20 18C20 9 14 4 5 4c0 9 5 14 15 14Z" stroke="#d8b678" stroke-width="1.6" stroke-linejoin="round" />
                        <path d="M20 18c0-9 6-14 15-14 0 9-5 14-15 14Z" stroke="#d8b678" stroke-width="1.6" stroke-linejoin="round" />
                    </svg>
                    <p class="dq__eyebrow">Understand Ayurveda</p>
                    <h2 class="dq__title" id="dq-title">Don't Know Your Dosha Yet?</h2>
                    <p class="dq__text">Discover the Ayurvedic principles that can help you better understand your natural constitution and daily wellness.</p>

                    <a href="<?= htmlspecialchars($doshaQuizUrl, ENT_QUOTES, 'UTF-8') ?>" class="dq__cta">
                        Discover Your Dosha
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14M13 6l6 6-6 6" />
                        </svg>
                    </a>
                    <p class="dq__note">For general wellness education only. Not a medical diagnosis.</p>
                </div>

                <div class="dq__visual" role="group" aria-label="The three doshas: Vata, Pitta and Kapha">
                    <svg class="dq__art" viewBox="0 0 100 100" fill="none" aria-hidden="true">
                        <defs>
                            <radialGradient id="dqGlow" cx="50%" cy="50%" r="50%">
                                <stop offset="0" stop-color="#d8b678" stop-opacity="0.28" />
                                <stop offset="1" stop-color="#d8b678" stop-opacity="0" />
                            </radialGradient>
                        </defs>

                        <!-- outer dotted ring + main orbit -->
                        <circle cx="50" cy="52" r="46" stroke="#91a96b" stroke-opacity="0.45" stroke-width="0.3" stroke-dasharray="0.4 1.6" stroke-linecap="round" />
                        <circle cx="50" cy="52" r="33" stroke="currentColor" stroke-opacity="0.6" stroke-width="0.35" />

                        <!-- central medallion -->
                        <circle cx="50" cy="52" r="20" fill="url(#dqGlow)" />
                        <circle cx="50" cy="52" r="17" stroke="currentColor" stroke-opacity="0.65" stroke-width="0.35" />
                        <circle cx="50" cy="52" r="19.2" stroke="currentColor" stroke-opacity="0.5" stroke-width="0.5" stroke-dasharray="0.3 1.9" stroke-linecap="round" />
                        <circle cx="50" cy="52" r="14" stroke="#91a96b" stroke-opacity="0.6" stroke-width="0.25" />

                        <!-- lotus -->
                        <g transform="translate(50 52)" stroke="currentColor" stroke-width="0.45" stroke-linejoin="round" stroke-linecap="round">
                            <g transform="translate(0 4)">
                                <path d="M0 -12c2.8 3.2 2.8 7.5 0 12-2.8-4.5-2.8-8.8 0-12Z" />
                                <path transform="rotate(-32 0 0)" d="M0 -10.500c2.5 3 2.5 6.8 0 10.5-2.5-3.7-2.5-7.5 0-10.500Z" />
                                <path transform="rotate(32 0 0)" d="M0 -10.500c2.5 3 2.5 6.8 0 10.5-2.5-3.7-2.5-7.5 0-10.500Z" />
                                <path transform="rotate(-62 0 0)" d="M0 -8.500c2.1 2.5 2.1 5.6 0 8.5-2.1-2.9-2.1-6 0-8.500Z" />
                                <path transform="rotate(62 0 0)" d="M0 -8.500c2.1 2.5 2.1 5.6 0 8.5-2.1-2.9-2.1-6 0-8.500Z" />
                                <path d="M-9 1.500c5 2.5 13 2.5 18 0" stroke-opacity="0.7" />
                            </g>
                            <circle cx="0" cy="-10.8" r="0.9" fill="currentColor" stroke="none" />
                        </g>

                        <!-- small sprout at the bottom of the orbit -->
                        <circle cx="50" cy="85" r="4" fill="#17483d" />
                        <g stroke="currentColor" stroke-width="0.4" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M50 87.500V83" />
                            <path d="M50 84.500c-2.3 0-3.5-1.3-3.5-3.3 2.3 0 3.5 1.3 3.5 3.300Z" />
                            <path d="M50 83c0-2.3 1.3-3.7 3.5-3.7 0 2.3-1.3 3.7-3.5 3.700Z" />
                        </g>
                    </svg>

                    <div class="dq__node dq__node--vata">
                        <span class="dq__badge" aria-hidden="true">
                            <svg viewBox="0 0 32 32">
                                <path d="M4 12h15a3.5 3.5 0 1 0-3.5-3.5" />
                                <path d="M4 17.500h21a3.5 3.5 0 1 1-3.5 3.5" />
                                <path d="M4 23h10" />
                            </svg>
                        </span>
                        <div class="dq__label">
                            <span class="dq__label-name">Vata</span>
                            <span class="dq__label-tags"><span>Movement</span><span>Creativity</span><span>Lightness</span></span>
                        </div>
                    </div>

                    <div class="dq__node dq__node--pitta">
                        <span class="dq__badge" aria-hidden="true">
                            <svg viewBox="0 0 32 32">
                                <path d="M16 4c1 4.5 6.5 6.5 6.5 12.500a6.5 6.5 0 0 1-13 0c0-3 1.5-4.8 3.2-6.5 0 2 .9 3.2 2.3 3.5-.8-3.5-.5-6.5 1-9.500Z" />
                                <path d="M16 27a3.2 3.2 0 0 1-3.2-3.200c0-2 2-3.3 3.2-5.3 1.2 2 3.2 3.3 3.2 5.300A3.2 3.2 0 0 1 16 27Z" />
                            </svg>
                        </span>
                        <div class="dq__label">
                            <span class="dq__label-name">Pitta</span>
                            <span class="dq__label-tags"><span>Digestion</span><span>Focus</span><span>Transformation</span></span>
                        </div>
                    </div>

                    <div class="dq__node dq__node--kapha">
                        <span class="dq__badge" aria-hidden="true">
                            <svg viewBox="0 0 32 32">
                                <path d="M16 28V15" />
                                <path d="M16 19.500c-5.5 0-8.5-3-8.5-8.5 5.5 0 8.5 3 8.5 8.500Z" />
                                <path d="M16 15.500c0-5.5 3-8.5 8.5-8.5 0 5.5-3 8.5-8.5 8.500Z" />
                            </svg>
                        </span>
                        <div class="dq__label">
                            <span class="dq__label-name">Kapha</span>
                            <span class="dq__label-tags"><span>Stability</span><span>Strength</span><span>Nourishment</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>