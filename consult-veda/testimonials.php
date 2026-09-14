<?php

/**
 * "Real Stories. Shared by Real Customers." — consult-veda.
 *
 * Hardcoded content: ayurveda_db.sql has a `reviews` table, but it's
 * product ratings tied to product_id, not consultation testimonials —
 * there's nothing to query here. Same reasoning as health-conditions.php
 * and why-choose-us.php.
 *
 * Built on the shared slider engine (see assets/css/slider.css + the
 * initSlider() code in global.js) — same mechanism as hero-banner.php.
 *
 * Usage (from consult-veda/index.php, after $conn is available):
 *   include __DIR__ . '/testimonials.php';
 */

$testimonials = [
    [
        'name' => 'Sanjay Kulkarni',
        'email' => 'sanjay.k***@gmail.com',
        'date' => 'Aug 12',
        'color' => '#5c6b47',
        'body' => "After following the suggested routine and medicines, I felt more disciplined about my health. It gave me a proper sense of direction instead of randomly trying things.",
    ],
    [
        'name' => 'Karan Shah',
        'email' => 'karan.shah***@gmail.com',
        'date' => 'Aug 19',
        'color' => '#8a4b3c',
        'body' => "Earlier I used to check articles and get more confused. This consultation made it easier to understand my issue in a better way, and lifestyle changes now support me.",
    ],
    [
        'name' => 'Nihi Suri',
        'email' => 'nihi.suri***@gmail.com',
        'date' => 'Aug 25',
        'color' => '#a4356b',
        'body' => "The wellness counsellor and Vaidya both listened patiently. The process was smooth, and I felt someone is actually understanding my body instead of rushing me through.",
    ],
    [
        'name' => 'Divya Menon',
        'email' => 'divya.menon***@gmail.com',
        'date' => 'Sep 02',
        'color' => '#245c4f',
        'body' => "I was not sure if online Vaidya consultation will be as effective, but it was actually good. The doctor understood my concern and gave diet and medicine guidance properly.",
    ],
    [
        'name' => 'Rohit Bhatia',
        'email' => 'rohit.bhatia***@gmail.com',
        'date' => 'Sep 05',
        'color' => '#3c5b8a',
        'body' => "What stood out for me was how the Vaidya asked about my daily routine before suggesting anything. It didn't feel like a generic prescription, it felt personal.",
    ],
    [
        'name' => 'Priya Nair',
        'email' => 'priya.nair***@gmail.com',
        'date' => 'Sep 08',
        'color' => '#916b1e',
        'body' => "I appreciated the follow-up call after two weeks to check on progress. Most places just hand you a prescription and move on, this felt like actual care.",
    ],
    [
        'name' => 'Arjun Verma',
        'email' => 'arjun.verma***@gmail.com',
        'date' => 'Sep 10',
        'color' => '#4a4a6b',
        'body' => "Booking the slot was simple and the wait time was short. The consultation itself was thorough, not rushed at all despite being fully online.",
    ],
    [
        'name' => 'Meera Iyer',
        'email' => 'meera.iyer***@gmail.com',
        'date' => 'Sep 12',
        'color' => '#a65c2e',
        'body' => "Was skeptical about doing this over a video call, but the Vaidya explained everything clearly and answered all my questions without making me feel rushed.",
    ],
];
?>

<?php if (!defined('GLOBAL_SLIDER_CSS_LOADED')): ?>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/slider.css">
    <?php define('GLOBAL_SLIDER_CSS_LOADED', true); ?>
<?php endif; ?>

<style>
    .testimonials-section {
        padding: 56px 40px;
        background: var(--color-bg);
    }

    .testimonials-header {
        max-width: 620px;
        margin: 0 auto 40px;
        text-align: center;
    }

    .testimonials-header h2 {
        margin: 0 0 10px;
        font-size: 30px;
        font-weight: 800;
        color: var(--color-text);
    }

    .testimonials-header p {
        margin: 0;
        color: var(--color-text-light);
        font-size: 15px;
    }

    .testimonials-slider {
        --slider-visible: 4;
        --slider-gap: 20px;
        max-width: var(--container-width);
        margin: 0 auto;
    }

    @media (max-width: 1024px) {
        .testimonials-slider {
            --slider-visible: 3;
        }
    }

    @media (max-width: 760px) {
        .testimonials-slider {
            --slider-visible: 2;
        }
    }

    @media (max-width: 520px) {
        .testimonials-slider {
            --slider-visible: 1.1;
            --slider-gap: 14px;
        }
    }

    .mail-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-soft);
        overflow: hidden;
    }

    .mail-card__bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        border-bottom: 1px solid var(--color-border);
    }

    .mail-card__brand {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.03em;
        color: var(--color-primary);
    }

    .mail-card__brand svg {
        width: 15px;
        height: 15px;
        fill: none;
        stroke: var(--color-primary);
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .mail-card__tools {
        display: flex;
        gap: 8px;
    }

    .mail-card__tools svg {
        width: 13px;
        height: 13px;
        fill: none;
        stroke: var(--color-text-light);
        stroke-width: 1.6;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .mail-card__subject {
        padding: 10px 14px 0;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--color-text);
    }

    .mail-card__from {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-bottom: 1px solid var(--color-border);
    }

    .mail-card__avatar {
        flex-shrink: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--color-white);
        font-size: 12px;
        font-weight: 700;
    }

    .mail-card__from-text {
        flex: 1;
        min-width: 0;
    }

    .mail-card__from-name {
        font-size: 12.5px;
        font-weight: 700;
        color: var(--color-text);
    }

    .mail-card__from-email {
        font-size: 11px;
        color: var(--color-text-light);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .mail-card__date {
        flex-shrink: 0;
        font-size: 11px;
        color: var(--color-text-light);
    }

    .mail-card__body {
        flex: 1;
        padding: 14px;
        font-size: 12.5px;
        line-height: 1.65;
        color: var(--color-text);
    }

    .mail-card__body p {
        margin: 0 0 10px;
    }

    .mail-card__signoff {
        margin: 0;
        color: var(--color-text-light);
    }

    .mail-card__signoff strong {
        display: block;
        font-weight: 700;
    }
</style>

<section class="testimonials-section">
    <div class="testimonials-header">
        <h2>Real Stories. Shared by Real Customers.</h2>
        <p>Browse authentic customer experiences exactly as they were shared with us.</p>
    </div>

    <div class="slider testimonials-slider" data-slider>
        <div class="slider__track" data-slider-track>
            <?php foreach ($testimonials as $index => $t):
                $initial = mb_strtoupper(mb_substr($t['name'], 0, 1));
            ?>
                <div class="slider__slide" data-index="<?= (int) $index ?>">
                    <div class="mail-card">
                        <div class="mail-card__bar">
                            <div class="mail-card__brand">
                                <svg viewBox="0 0 24 24"><path d="M3 6l9 6 9-6" /><rect x="3" y="5" width="18" height="14" rx="2" /></svg>
                                MAIL
                            </div>
                            <div class="mail-card__tools" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M9 5l-7 7 7 7" /><path d="M2 12h13a6 6 0 010 12" /></svg>
                                <svg viewBox="0 0 24 24"><path d="M4 4v6h6" /><path d="M20 20a8 8 0 00-16-6" /></svg>
                                <svg viewBox="0 0 24 24"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14" /></svg>
                            </div>
                        </div>

                        <p class="mail-card__subject">My experience with online Vaidya consultation</p>

                        <div class="mail-card__from">
                            <div class="mail-card__avatar" style="background: <?= htmlspecialchars($t['color'], ENT_QUOTES, 'UTF-8') ?>;">
                                <?= htmlspecialchars($initial, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <div class="mail-card__from-text">
                                <div class="mail-card__from-name"><?= htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="mail-card__from-email"><?= htmlspecialchars($t['email'], ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                            <div class="mail-card__date"><?= htmlspecialchars($t['date'], ENT_QUOTES, 'UTF-8') ?></div>
                        </div>

                        <div class="mail-card__body">
                            <p>Hi Team,</p>
                            <p><?= htmlspecialchars($t['body'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="mail-card__signoff">
                                Warm regards,
                                <strong style="color: <?= htmlspecialchars($t['color'], ENT_QUOTES, 'UTF-8') ?>;"><?= htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($testimonials) > 1): ?>
            <div class="slider__dots" data-slider-dots role="tablist" aria-label="Testimonials navigation"></div>
        <?php endif; ?>
    </div>
</section>