<?php

/**
 * detux/consultation-cta.php
 *
 * Included from detux/index.php alongside the other detux sections.
 * No config/header/footer includes here — index.php already handles
 * those.
 *
 * Image comes from Banner Management, position =
 * 'detox_consultation_cta'. Falls back to a plain panel if none is set.
 * Copy and button link are hardcoded below.
 */

require_once __DIR__ . '/../function/banner.php';

$ctaBanners = getActiveBanners($conn, 'detox_consultation_cta');
$ctaImage = null;

if (!empty($ctaBanners)) {
    $banner = $ctaBanners[0];
    $ctaImage = [
        'url' => getBannerImageUrl($banner['image']),
        'alt' => $banner['title'] ?: 'Book a consultation with our Gut Health Expert',
    ];
}

$ctaHeadingLine1 = 'Not Sure';
$ctaHeadingLine2 = 'Where to Start?';
$ctaHighlight    = "Maharishi Ayurveda's Gut Health Expert";
$ctaTextBefore   = 'Book a consultation with ';
$ctaTextAfter    = '. Get recommendations based on your Dosha and which course of action is right for you.';
$ctaButtonLabel  = 'Book Your Consultation Now';
$ctaButtonUrl    = '#consultation';
?>

<style>
    .detox-cta {
        padding: 8px 0 64px;
    }

    .detox-cta__grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 48px;
        align-items: center;
    }

    .detox-cta__image {
        border-radius: var(--radius-lg);
        overflow: hidden;
        aspect-ratio: 4 / 3.4;
        background: var(--color-primary-light);
    }

    .detox-cta__image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .detox-cta__placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 24px;
        color: var(--color-text-light);
        font-size: 14px;
    }

    .detox-cta__heading {
        font-size: 30px;
        line-height: 1.2;
        color: var(--color-text);
    }

    .detox-cta__heading span {
        display: block;
        margin-top: 2px;
        color: var(--color-accent);
        font-style: italic;
    }

    .detox-cta__text {
        margin-top: 16px;
        max-width: 46ch;
        font-size: 14.5px;
        line-height: 1.7;
        color: var(--color-text-light);
    }

    .detox-cta__text strong {
        color: var(--color-primary-dark);
        font-weight: 600;
    }

    .detox-cta__button {
        display: inline-block;
        margin-top: 24px;
        padding: 14px 28px;
        border-radius: 999px;
        background: var(--color-accent);
        color: var(--color-white);
        font-size: 14px;
        font-weight: 600;
    }

    .detox-cta__button:hover {
        background: var(--color-primary-dark);
    }

    @media (max-width: 860px) {
        .detox-cta__grid {
            grid-template-columns: 1fr;
            gap: 28px;
        }

        .detox-cta__heading {
            font-size: 26px;
        }
    }
</style>

<section class="detox-cta">
    <div class="container detox-cta__grid">

        <div class="detox-cta__image">
            <?php if ($ctaImage): ?>
                <img src="<?= htmlspecialchars($ctaImage['url'], ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($ctaImage['alt'], ENT_QUOTES, 'UTF-8') ?>">
            <?php else: ?>
                <div class="detox-cta__placeholder">
                    No image yet — add one in Banner Management with
                    position <strong>detox_consultation_cta</strong>.
                </div>
            <?php endif; ?>
        </div>

        <div class="detox-cta__content">
            <h2 class="detox-cta__heading">
                <?= htmlspecialchars($ctaHeadingLine1, ENT_QUOTES, 'UTF-8') ?>
                <span><?= htmlspecialchars($ctaHeadingLine2, ENT_QUOTES, 'UTF-8') ?></span>
            </h2>

            <p class="detox-cta__text">
                <?= htmlspecialchars($ctaTextBefore, ENT_QUOTES, 'UTF-8') ?><strong><?= htmlspecialchars($ctaHighlight, ENT_QUOTES, 'UTF-8') ?></strong><?= htmlspecialchars($ctaTextAfter, ENT_QUOTES, 'UTF-8') ?>
            </p>

            <a class="detox-cta__button" href="<?= htmlspecialchars($ctaButtonUrl, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($ctaButtonLabel, ENT_QUOTES, 'UTF-8') ?>
            </a>
        </div>

    </div>
</section>