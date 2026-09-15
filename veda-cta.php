<?php
$consultImage = 'assets/images/consult-veda.png';

$consultUrl = BASE_URL . 'consult-veda/index.php';
?>

<style>

    .cvb {
        padding: 40px 0;
        background: var(--color-bg);
    }

    .cvb__panel {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        background: linear-gradient(135deg, var(--color-accent) 0%, var(--color-primary) 100%);
        border-radius: var(--radius-lg);
        overflow: hidden;
        min-height: 320px;
        box-shadow: var(--shadow-soft);
    }

    /* ---- Decoration layer 1: faint dot-grid texture across the whole panel ---- */

    .cvb__panel::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: radial-gradient(rgba(255, 255, 255, 0.16) 1.5px, transparent 1.5px);
        background-size: 22px 22px;
        pointer-events: none;
    }

    /* ---- Decoration layer 2: large faint leaf watermark, top-left ---- */

    .cvb__leaf-mark {
        position: absolute;
        top: -30px;
        left: -30px;
        width: 220px;
        height: 220px;
        color: var(--color-white);
        opacity: 0.08;
        pointer-events: none;
    }

    /* ---- Decoration layer 3: soft glow behind the photo ---- */

    .cvb__glow {
        position: absolute;
        right: 10px;
        bottom: -60px;
        width: 340px;
        height: 340px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.4) 0%, rgba(255, 255, 255, 0) 72%);
        pointer-events: none;
    }

    .cvb__text {
        position: relative;
        z-index: 2;
        padding: 44px 0 44px 48px;
        max-width: 480px;
    }

    .cvb__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 16px;
        padding: 7px 15px;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.32);
        border-radius: 999px;
        color: var(--color-white);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .cvb__eyebrow svg {
        width: 13px;
        height: 13px;
        flex-shrink: 0;
    }

    .cvb__heading {
        margin: 0 0 16px;
        font-size: 36px;
        line-height: 1.2;
        font-weight: 800;
        letter-spacing: -0.01em;
        color: var(--color-white);
    }

    .cvb__desc {
        margin: 0 0 30px;
        font-size: 15px;
        line-height: 1.75;
        color: var(--color-white);
        opacity: 0.92;
    }

    .cvb__cta {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 16px 30px;
        background: var(--color-white);
        color: var(--color-primary-dark);
        font-size: 13.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        text-decoration: none;
        border-radius: 999px;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.18);
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .cvb__cta svg {
        width: 16px;
        height: 16px;
        transition: transform 0.2s ease;
    }

    .cvb__cta:hover {
        background: var(--color-primary-light);
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(0, 0, 0, 0.22);
    }

    .cvb__cta:hover svg {
        transform: translateX(3px);
    }

    .cvb__cta:focus-visible {
        outline: 2px solid var(--color-white);
        outline-offset: 3px;
    }

    /* ---- Photo: anchored to the panel's bottom edge, no more odd overflow ---- */

    .cvb__photo-wrap {
        position: relative;
        z-index: 2;
        flex-shrink: 0;
        align-self: flex-end;
        width: 350px;
        height: 350px;
    }

    .cvb__photo-wrap img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: bottom;
        pointer-events: none;
        user-select: none;
        filter: drop-shadow(0 16px 22px rgba(23, 72, 61, 0.35));
    }

    .cvb__photo-fallback {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cvb__photo-fallback span {
        width: 78%;
        height: 78%;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.14);
        color: var(--color-white);
        opacity: 0.85;
    }

    .cvb__photo-fallback svg {
        width: 46%;
        height: 46%;
    }

    @media (max-width: 900px) {
        .cvb__glow {
            width: 260px;
            height: 260px;
        }

        .cvb__photo-wrap {
            width: 210px;
            height: 280px;
        }

        .cvb__text {
            max-width: 56%;
            padding: 36px 0 36px 36px;
        }

        .cvb__heading {
            font-size: 28px;
        }
    }

    @media (max-width: 640px) {
        .cvb__panel {
            flex-direction: column;
            align-items: stretch;
            min-height: 0;
        }

        .cvb__text {
            max-width: none;
            padding: 32px 28px 0;
        }

        .cvb__heading {
            font-size: 24px;
        }

        .cvb__cta {
            width: 100%;
            justify-content: center;
        }

        .cvb__photo-wrap {
            align-self: center;
            width: 200px;
            height: 220px;
            margin-top: 24px;
        }

        .cvb__glow {
            right: 50%;
            transform: translateX(50%);
            bottom: -80px;
        }
    }
</style>

<section class="cvb">
    <div class="container">
        <div class="cvb__panel">

            <svg class="cvb__leaf-mark" viewBox="0 0 100 100" fill="none" aria-hidden="true">
                <path d="M50 8C30 8 12 26 12 50c0 24 18 42 38 42 4-24 4-60 0-84z" fill="currentColor" />
            </svg>

            <span class="cvb__glow" aria-hidden="true"></span>

            <div class="cvb__text">
                <span class="cvb__eyebrow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22c6-2 9-7 9-13 0-3-1-5-2-6-4 0-9 2-11 6-2-3-2-6-1-9-5 2-8 6-8 12 0 6 4 10 8 11" />
                    </svg>
                    Ayurvedic Consultation
                </span>

                <h2 class="cvb__heading">Find Out The Root Cause Of Your Problems</h2>
                <p class="cvb__desc">
                    As per Ayurveda, no two individuals are alike. We offer personalised
                    treatment for each individual at all touch-points. Consult our expert
                    Vaidyas to get root cause-based personalised treatment from the comfort
                    of your home.
                </p>
                <a href="<?= htmlspecialchars($consultUrl, ENT_QUOTES, 'UTF-8') ?>" class="cvb__cta">
                    Consult Vaidya
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            </div>

            <div class="cvb__photo-wrap">
                <span class="cvb__photo-fallback">
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
</section>