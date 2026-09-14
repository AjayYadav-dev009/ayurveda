<?php

/**
 * "Which Ayurveda type are you?" hero / quiz-CTA section for the
 * consult-veda flow.
 *
 * Static content — heading fragments, paragraph, and CTA copy pulled into
 * variables so they're easy to tweak without touching the markup.
 *
 * NOTE ON THE LEFT-SIDE ARTWORK: the reference design uses Maharishi
 * Ayurveda's own illustrated sage watermark behind the logo. That's a
 * specific brand asset, not something generated here — this file draws a
 * simple decorative lotus/line pattern in its place. If you have the real
 * illustration or logo as an image file, swap it in at the
 * `.hero-art-figure` spot (see comment in the markup below) instead of
 * the inline SVG.
 *
 * Usage (from consult-veda/index.php, after $conn is available):
 *   include __DIR__ . '/hero-quiz.php';
 */

$heroKicker = 'Find Balance Naturally';
$heroSubheading = 'Which Ayurveda type are you?';
$heroParagraph = 'Every human being carries these three doshas within him — in a balanced and individual combination. This basic balance is called Prakriti. It is already present at birth and remains unchanged throughout life. It is the inner nature, the balance that everyone strives for and in which one feels healthy and happy.';
$heroCtaLabel = 'Take The Quiz';
$heroCtaHref = '#quiz';
$heroCtaCaption = 'Estimated time to complete 15 mins';

$logoTop = 'Maharishi';
$logoBottom = 'ayurveda';
?>

<style>
    .hero-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        min-height: 540px;
        background: var(--color-primary);
        overflow: hidden;
    }

    .hero-art {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--color-primary);
        overflow: hidden;
    }

    /* Decorative background pattern — stand-in for the brand's own
       illustrated watermark. Replace with a real <img> if you have the
       asset (see note in the file header). */
    .hero-art-pattern {
        position: absolute;
        inset: 0;
        opacity: 0.12;
        background-image:
            radial-gradient(circle at 30% 30%, transparent 0, transparent 55%, var(--color-accent) 55.5%, var(--color-accent) 56%, transparent 56.5%),
            radial-gradient(circle at 70% 65%, transparent 0, transparent 40%, var(--color-accent) 40.5%, var(--color-accent) 41%, transparent 41.5%);
        background-size: 100% 100%;
    }

    .hero-art-figure {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 14px;
    }

    .hero-logo-icon svg {
        width: 76px;
        height: 76px;
        fill: none;
        stroke: var(--color-accent);
        stroke-width: 1.4;
    }

    .hero-logo-text {
        display: flex;
        flex-direction: column;
        align-items: center;
        line-height: 1.1;
    }

    .hero-logo-text .logo-top {
        font-size: 30px;
        font-weight: 800;
        letter-spacing: 2px;
        color: var(--color-accent);
        text-transform: uppercase;
    }

    .hero-logo-text .logo-bottom {
        font-size: 20px;
        font-style: italic;
        color: var(--color-accent);
        position: relative;
        padding: 0 14px;
    }

    .hero-logo-text .logo-bottom::before,
    .hero-logo-text .logo-bottom::after {
        content: '';
        position: absolute;
        top: 50%;
        width: 10px;
        height: 1px;
        background: var(--color-accent);
    }

    .hero-logo-text .logo-bottom::before {
        left: -6px;
    }

    .hero-logo-text .logo-bottom::after {
        right: -6px;
    }

    .hero-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 60px 64px;
        text-align: center;
    }

    .hero-kicker {
        margin: 0 0 6px;
        font-size: 19px;
        color: var(--color-accent);
    }

    .hero-heading {
        margin: 0 0 28px;
        font-size: 30px;
        line-height: 1.35;
        color: var(--color-white);
        font-weight: 500;
    }

    .hero-heading em {
        font-style: italic;
    }

    .hero-heading strong {
        font-weight: 800;
    }

    .hero-subheading {
        margin: 0 0 18px;
        font-size: 21px;
        font-weight: 700;
        color: var(--color-white);
    }

    .hero-paragraph {
        margin: 0 auto 32px;
        max-width: 460px;
        font-size: 15px;
        line-height: 1.8;
        color: rgba(255, 255, 255, 0.85);
    }

    .hero-cta-btn {
        display: inline-block;
        margin: 0 auto;
        padding: 15px 40px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.35);
        border-radius: var(--radius-md, 6px);
        color: var(--color-white);
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.25s ease, transform 0.25s ease;
    }

    .hero-cta-btn:hover {
        background: rgba(255, 255, 255, 0.16);
        transform: translateY(-2px);
    }

    .hero-cta-caption {
        margin: 14px 0 0;
        font-size: 12px;
        color: var(--color-accent);
    }

    @media (max-width: 900px) {
        .hero-section {
            grid-template-columns: 1fr;
        }

        .hero-art {
            min-height: 320px;
        }

        .hero-content {
            padding: 48px 32px;
        }
    }

    @media (max-width: 480px) {
        .hero-heading {
            font-size: 24px;
        }

        .hero-content {
            padding: 40px 22px;
        }
    }
</style>

<section class="hero-section">
    <div class="hero-art">
        <div class="hero-art-pattern" aria-hidden="true"></div>

        <!-- Swap this block for a real <img src="..." alt="Maharishi Ayurveda"> if you have the brand illustration/logo file -->
        <div class="hero-art-figure">
            <div class="hero-logo-icon" aria-hidden="true">
                <svg viewBox="0 0 64 64">
                    <path d="M32 44c-9 0-14-7-14-16 6 2 10 6 14 12 4-6 8-10 14-12 0 9-5 16-14 16z" />
                    <path d="M32 44c0-12 4-20 10-26" />
                    <path d="M32 44c0-12-4-20-10-26" />
                    <circle cx="32" cy="14" r="4" />
                </svg>
            </div>
            <div class="hero-logo-text">
                <span class="logo-top"><?= htmlspecialchars($logoTop, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="logo-bottom"><?= htmlspecialchars($logoBottom, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
    </div>

    <div class="hero-content">
        <p class="hero-kicker"><?= htmlspecialchars($heroKicker, ENT_QUOTES, 'UTF-8') ?></p>

        <h2 class="hero-heading">
            Start Your <em>Personalised</em><br>
            Path To <strong>Health &amp; Wellness</strong>
        </h2>

        <h3 class="hero-subheading"><?= htmlspecialchars($heroSubheading, ENT_QUOTES, 'UTF-8') ?></h3>

        <p class="hero-paragraph"><?= htmlspecialchars($heroParagraph, ENT_QUOTES, 'UTF-8') ?></p>

        <a href="<?= htmlspecialchars($heroCtaHref, ENT_QUOTES, 'UTF-8') ?>" class="hero-cta-btn">
            <?= htmlspecialchars($heroCtaLabel, ENT_QUOTES, 'UTF-8') ?>
        </a>
        <p class="hero-cta-caption"><?= htmlspecialchars($heroCtaCaption, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
</section>