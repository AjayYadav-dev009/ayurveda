<?php
$consultImage = 'assets/images/consult-vaidya.jpg';

$consultUrl = BASE_URL . 'consult.php';
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
        background: var(--color-accent);
        border-radius: var(--radius-lg);
        overflow: hidden;
        min-height: 260px;
    }

    .cvb__text {
        position: relative;
        z-index: 1;
        padding: 40px 44px;
        max-width: 520px;
    }

    .cvb__heading {
        margin: 0 0 14px;
        font-size: 28px;
        line-height: 1.25;
        font-weight: 700;
        color: var(--color-white);
    }

    .cvb__desc {
        margin: 0 0 24px;
        font-size: 14px;
        line-height: 1.6;
        color: var(--color-white);
        opacity: 0.9;
    }

    .cvb__cta {
        display: inline-block;
        padding: 13px 24px;
        background: var(--color-white);
        color: var(--color-primary-dark);
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        text-decoration: none;
        border-radius: var(--radius-sm);
        box-shadow: var(--shadow-soft);
        transition: transform 0.15s ease, background 0.15s ease;
    }

    .cvb__cta:hover {
        background: var(--color-primary-light);
        transform: translateY(-1px);
    }

    /* ---- Photo: bleeds above/below the panel on wide screens ---- */

    .cvb__photo-wrap {
        position: absolute;
        right: 48px;
        bottom: 0;
        z-index: 0;
        width: 240px;
        height: 118%;
        display: flex;
        align-items: flex-end;
        justify-content: center;
    }

    .cvb__photo-wrap img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: bottom;
        pointer-events: none;
        user-select: none;
    }

    .cvb__photo-fallback {
        width: 70%;
        height: 70%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--color-primary-light);
        opacity: 0.8;
    }

    .cvb__photo-fallback svg {
        width: 100%;
        height: 100%;
    }

    @media (max-width: 900px) {
        .cvb__photo-wrap {
            width: 190px;
            right: 24px;
        }

        .cvb__text {
            max-width: 60%;
            padding: 32px;
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
            padding: 28px 24px 0;
        }

        .cvb__heading {
            font-size: 22px;
        }

        .cvb__photo-wrap {
            position: static;
            width: 100%;
            height: 220px;
            margin-top: 20px;
        }
    }
</style>

<section class="cvb">
    <div class="container">
        <div class="cvb__panel">
            <div class="cvb__text">
                <h2 class="cvb__heading">Find Out The Root Cause Of Your Problems</h2>
                <p class="cvb__desc">
                    As per Ayurveda, no two individuals are alike. We offer personalised
                    treatment for each individual at all touch-points. Consult our expert
                    Vaidyas to get root cause-based personalised treatment from the comfort
                    of your home.
                </p>
                <a href="<?= htmlspecialchars($consultUrl, ENT_QUOTES, 'UTF-8') ?>" class="cvb__cta">Consult Vaidya</a>
            </div>

            <div class="cvb__photo-wrap">
                <span class="cvb__photo-fallback">
                    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="32" cy="20" r="12" fill="currentColor" />
                        <path d="M10 60c0-14 10-24 22-24s22 10 22 24" stroke="currentColor" stroke-width="4" stroke-linecap="round" fill="none" />
                    </svg>
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