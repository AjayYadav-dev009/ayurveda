<?php

/**
 * Closing CTA banner for the consult-veda flow.
 *
 * Static content — heading + button copy/link pulled out to variables so
 * they're easy to tweak without touching the markup below.
 *
 * Usage (from consult-veda/index.php, after $conn is available):
 *   include __DIR__ . '/cta-banner.php';
 */

$ctaHeading = 'Your Path to Balance Starts Today';
$ctaButtonLabel = 'Book Your Consultation Now';
$ctaButtonHref = '#book-now';
?>

<style>
    .cta-banner {
        padding: 40px 40px;
        background: var(--color-primary);
        text-align: center;
    }

    .cta-banner h2 {
        margin: 0 0 22px;
        font-size: 26px;
        font-weight: 800;
        color: var(--color-white);
    }

    .cta-banner-btn {
        display: inline-block;
        padding: 13px 32px;
        background: var(--color-accent);
        color: var(--color-white);
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        border: none;
        border-radius: var(--radius-md, 6px);
        cursor: pointer;
        transition: opacity 0.25s ease, transform 0.25s ease;
    }

    .cta-banner-btn:hover {
        opacity: 0.9;
        transform: translateY(-2px);
    }

    @media (max-width: 480px) {
        .cta-banner {
            padding: 32px 20px;
        }

        .cta-banner h2 {
            font-size: 21px;
        }
    }
</style>

<section class="cta-banner">
    <h2><?= htmlspecialchars($ctaHeading, ENT_QUOTES, 'UTF-8') ?></h2>
    <a href="<?= htmlspecialchars($ctaButtonHref, ENT_QUOTES, 'UTF-8') ?>" class="cta-banner-btn">
        <?= htmlspecialchars($ctaButtonLabel, ENT_QUOTES, 'UTF-8') ?>
    </a>
</section>