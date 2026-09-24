<?php
$consultImage = 'assets/images/consult-veda.png';

$consultUrl = BASE_URL . 'consult-veda/index.php';

$cvbSprig = '<svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
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
       Ayurvedic consultation CTA — namespaced "cvb".
       Wide dark-green panel (#17483d) with a faint dotted + botanical texture.
       Text on the left; on the right the practitioner sits on a soft organic
       sage shape and rises above the panel's top edge while the shape bleeds
       off the right edge, so the photo never reads as a pasted rectangle.
       The texture/shape live in .cvb__bg (which clips them); the person lives
       outside it so they're free to overlap the panel edge.
       ========================================================================== */

    .cvb {
        --cvb-bg: #17483d;
        --cvb-cream: #f4efe2;
        --cvb-gold: #d8b678;
        --cvb-sage: #91a96b;
        --cvb-serif: 'Lora', Georgia, 'Times New Roman', serif;

        padding: 64px 0 48px;
        background: var(--color-bg);
    }

    .cvb__panel {
        position: relative;
        display: grid;
        grid-template-columns: minmax(0, 1fr) 440px;
        align-items: center;
        min-height: 340px;
        border-radius: 20px;
        background: var(--cvb-bg);
        color: var(--cvb-cream);
        box-shadow: 0 18px 40px rgba(23, 72, 61, 0.22);
    }

    /* ---- Background layer (clipped to the rounded panel) ---- */

    .cvb__bg {
        position: absolute;
        inset: 0;
        border-radius: inherit;
        overflow: hidden;
        pointer-events: none;
        background:
            radial-gradient(ellipse 55% 90% at 82% 60%, rgba(145, 169, 107, 0.18), rgba(145, 169, 107, 0) 70%),
            radial-gradient(ellipse 45% 70% at 0% 100%, rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0) 70%);
    }

    /* dotted texture */
    .cvb__bg::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: radial-gradient(rgba(244, 239, 226, 0.13) 1.2px, transparent 1.4px);
        background-size: 22px 22px;
        -webkit-mask-image: linear-gradient(to right, #000 0%, #000 45%, transparent 85%);
        mask-image: linear-gradient(to right, #000 0%, #000 45%, transparent 85%);
    }

    .cvb__leaf {
        position: absolute;
        color: var(--cvb-sage);
        opacity: 0.2;
    }

    .cvb__leaf svg { width: 100%; height: 100%; display: block; }
    .cvb__leaf--tl { top: -22px; left: -18px; width: 170px; height: 170px; transform: rotate(-6deg); }
    .cvb__leaf--bl { bottom: -26px; left: 34%; width: 120px; height: 120px; transform: rotate(18deg); opacity: 0.12; }

    /* Organic sage shape behind the person (bleeds off the right edge) */
    .cvb__blob {
        position: absolute;
        right: -90px;
        bottom: -120px;
        width: 500px;
        height: 500px;
        border-radius: 58% 42% 52% 48% / 46% 56% 44% 54%;
        background: radial-gradient(circle at 38% 32%, rgba(145, 169, 107, 0.55), rgba(74, 122, 96, 0.35) 55%, rgba(74, 122, 96, 0.12) 100%);
    }

    .cvb__ring {
        position: absolute;
        right: -130px;
        bottom: -160px;
        width: 580px;
        height: 580px;
        border-radius: 50%;
        border: 1px dashed rgba(216, 182, 120, 0.4);
    }

    /* ---- Text ---- */

    .cvb__text {
        position: relative;
        z-index: 2;
        padding: 56px 0 56px 60px;
        max-width: 600px;
    }

    .cvb__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        margin: 0 0 18px;
        font-size: 12.5px;
        font-weight: 600;
        letter-spacing: 0.3em;
        text-transform: uppercase;
        color: var(--cvb-gold);
    }

    .cvb__eyebrow::before {
        content: "";
        width: 40px;
        height: 1px;
        background: var(--cvb-gold);
        opacity: 0.8;
    }

    .cvb__heading {
        margin: 0 0 18px;
        font-family: var(--cvb-serif);
        font-size: 42px;
        line-height: 1.16;
        font-weight: 700;
        color: var(--cvb-cream);
    }

    .cvb__desc {
        margin: 0 0 30px;
        max-width: 480px;
        font-size: 16.5px;
        line-height: 1.7;
        color: var(--cvb-cream);
        opacity: 0.86;
    }

    .cvb__cta {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 15px 30px;
        border-radius: 999px;
        background: var(--cvb-cream);
        color: var(--cvb-bg);
        font-size: 15.5px;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s ease, background 0.2s ease;
    }

    .cvb__cta svg { width: 18px; height: 18px; transition: transform 0.2s ease; }
    .cvb__cta:hover { background: #fff; transform: translateY(-2px); }
    .cvb__cta:hover svg { transform: translateX(3px); }
    .cvb__cta:focus-visible { outline: 2px solid var(--cvb-gold); outline-offset: 3px; }

    .cvb__note {
        margin: 16px 0 0;
        font-size: 13px;
        line-height: 1.5;
        color: var(--cvb-cream);
        opacity: 0.6;
    }

    /* ---- Person ---- */

    .cvb__photo {
        position: relative;
        z-index: 2;
        align-self: stretch;
        min-height: 340px;
    }

    /* rises 44px above the panel's top edge */
    .cvb__photo-inner {
        position: absolute;
        top: -44px;
        right: 34px;
        bottom: 0;
        width: 400px;
        max-width: 100%;
    }

    .cvb__photo img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: bottom center;
        pointer-events: none;
        user-select: none;
        filter: drop-shadow(0 18px 24px rgba(0, 0, 0, 0.35));
    }

    .cvb__fallback {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: flex-end;
        justify-content: center;
    }

    .cvb__fallback span {
        width: 62%;
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(244, 239, 226, 0.1);
        color: var(--cvb-cream);
        opacity: 0.8;
        margin-bottom: 40px;
    }

    .cvb__fallback svg { width: 50%; height: 50%; }

    @media (prefers-reduced-motion: reduce) {
        .cvb__cta, .cvb__cta svg { transition: none; }
    }

    /* ---- Responsive ---- */

    @media (max-width: 1000px) {
        .cvb__panel { grid-template-columns: minmax(0, 1fr) 340px; }
        .cvb__text { padding: 46px 0 46px 40px; }
        .cvb__heading { font-size: 34px; }
        .cvb__photo-inner { width: 320px; right: 20px; }
        .cvb__blob { width: 400px; height: 400px; right: -100px; bottom: -110px; }
        .cvb__ring { display: none; }
    }

    @media (max-width: 720px) {
        .cvb { padding: 32px 0 40px; }

        .cvb__panel {
            grid-template-columns: 1fr;
            min-height: 0;
        }

        .cvb__text {
            max-width: none;
            padding: 38px 26px 0;
            text-align: center;
        }

        .cvb__eyebrow { font-size: 11.5px; letter-spacing: 0.22em; }
        .cvb__eyebrow::before { width: 24px; }
        .cvb__heading { font-size: 28px; }
        .cvb__desc { margin-left: auto; margin-right: auto; font-size: 15.5px; }
        .cvb__cta { width: 100%; justify-content: center; }

        /* person sits at the bottom of the panel, no pop-out above the top */
        .cvb__photo { min-height: 270px; margin-top: 14px; }

        .cvb__photo-inner {
            top: 0;
            right: auto;
            left: 50%;
            width: min(300px, 80%);
            transform: translateX(-50%);
        }

        .cvb__blob {
            right: 50%;
            transform: translateX(50%);
            bottom: -150px;
            width: 380px;
            height: 380px;
        }

        .cvb__leaf--bl { display: none; }
        .cvb__bg::before {
            -webkit-mask-image: linear-gradient(to bottom, #000 0%, #000 50%, transparent 90%);
            mask-image: linear-gradient(to bottom, #000 0%, #000 50%, transparent 90%);
        }
    }
</style>

<section class="cvb" aria-labelledby="cvb-heading">
    <div class="container">
        <div class="cvb__panel">

            <div class="cvb__bg" aria-hidden="true">
                <span class="cvb__leaf cvb__leaf--tl"><?= $cvbSprig ?></span>
                <span class="cvb__leaf cvb__leaf--bl"><?= $cvbSprig ?></span>
                <span class="cvb__ring"></span>
                <span class="cvb__blob"></span>
            </div>

            <div class="cvb__text">
                <p class="cvb__eyebrow">Understand Your Wellness</p>
                <h2 class="cvb__heading" id="cvb-heading">Find Out The Root Cause Of Your Problems</h2>
                <p class="cvb__desc">Understand your wellness needs and discover a more personalized approach to everyday care.</p>

                <a href="<?= htmlspecialchars($consultUrl, ENT_QUOTES, 'UTF-8') ?>" class="cvb__cta">
                    Start Your Wellness Journey
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14M13 6l6 6-6 6"/>
                    </svg>
                </a>
                <p class="cvb__note">Speak with our Ayurvedic Vaidyas from the comfort of your home.</p>
            </div>

            <div class="cvb__photo">
                <div class="cvb__photo-inner">
                    <span class="cvb__fallback" aria-hidden="true">
                        <span>
                            <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="32" cy="20" r="12" fill="currentColor" />
                                <path d="M10 60c0-14 10-24 22-24s22 10 22 24" stroke="currentColor" stroke-width="4" stroke-linecap="round" fill="none" />
                            </svg>
                        </span>
                    </span>
                    <img
                        src="<?= htmlspecialchars(BASE_URL . ltrim($consultImage, '/'), ENT_QUOTES, 'UTF-8') ?>"
                        alt="Ayurvedic consultant"
                        loading="lazy"
                        onerror="this.style.display='none';">
                </div>
            </div>

        </div>
    </div>
</section>