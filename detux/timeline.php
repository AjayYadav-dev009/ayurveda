<?php

/**
 * detux/timeline.php
 *
 * Included from detux/index.php alongside hero.php / highlights.php /
 * information.php. No config/header/footer includes here — index.php
 * already handles those.
 *
 * Left: course-preview video thumbnail (image from Banner Management,
 * position = 'detox_video_preview'; falls back to a plain decorative
 * panel if none is set). Right: 2x2 "transformation timeline" grid,
 * hardcoded below.
 */

require_once __DIR__ . '/../function/banner.php';

$videoPreviewBanners = getActiveBanners($conn, 'detox_video_preview');
$videoPreview = null;

if (!empty($videoPreviewBanners)) {
    $banner = $videoPreviewBanners[0];
    $videoPreview = [
        'image' => getBannerImageUrl($banner['image']),
        'url'   => $banner['button_url'] ?: null,
        'alt'   => $banner['title'] ?: 'Course preview video',
    ];
}

$timelineIntro = 'Daily guided videos so you always know exactly what to do, when to do it, and why it works.';

$timelineStages = [
    [
        'range' => 'Days 1-3',
        'title' => 'The Reset Begins',
        'desc'  => 'The heaviness after meals begins to lift. Your gut starts processing food the way it was always meant to, without bloating or discomfort.',
    ],
    [
        'range' => 'Days 3-5',
        'title' => 'Energy and Clarity Boost',
        'desc'  => 'As Ama starts clearing, the foggy, sluggish feeling eases. You wake up a little lighter, your mind feels more present, and the persistent tiredness begins to lose its grip.',
    ],
    [
        'range' => 'Days 5-8',
        'title' => 'Your Skin and Mood Will Reflect Change',
        'desc'  => 'What is happening inside begins to show outside. Dullness softens, skin clears, and as digestion steadies, the restlessness quietly settles too.',
    ],
    [
        'range' => 'Days 8-10',
        'title' => 'The Full Reset',
        'desc'  => "Digestion working, toxins cleared, your body's natural fat-burning gets a clean start. You feel renewed, because what was blocking you is finally gone.",
    ],
];
?>

<style>
    .detox-timeline {
        padding: 8px 0 64px;
    }

    .detox-timeline__grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr);
        gap: 48px;
        align-items: start;
    }

    /* ---------------------------------------------------------------
       Left: video preview
       --------------------------------------------------------------- */
    .detox-timeline__left {
        text-align: center;
    }

    .detox-timeline__heading {
        font-size: 26px;
        color: var(--color-text);
    }

    .detox-timeline__heading span {
        color: var(--color-accent);
    }

    .detox-timeline__intro {
        margin: 12px auto 0;
        max-width: 42ch;
        font-size: 14px;
        line-height: 1.6;
        color: var(--color-text-light);
    }

    .detox-video {
        position: relative;
        display: block;
        margin-top: 28px;
        border-radius: var(--radius-lg);
        overflow: hidden;
        aspect-ratio: 4 / 3;
        background:
            radial-gradient(circle at center, transparent 0 38%, var(--color-primary-light) 38% 40%, transparent 40% 100%),
            radial-gradient(circle at center, transparent 0 58%, var(--color-primary-light) 58% 60%, transparent 60% 100%),
            var(--color-bg);
    }

    .detox-video img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .detox-video__overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .detox-video__play {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--color-white);
        box-shadow: var(--shadow-soft);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--color-primary-dark);
        flex: 0 0 auto;
    }

    .detox-video__play svg {
        width: 20px;
        height: 20px;
        margin-left: 2px;
    }

    .detox-video__caption {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 24px;
        font-size: 20px;
        font-weight: 700;
        color: var(--color-text);
    }

    .detox-video--has-image .detox-video__caption {
        display: none;
    }

    .detox-video--has-image .detox-video__overlay {
        background: rgba(0, 0, 0, 0.15);
    }

    /* ---------------------------------------------------------------
       Right: timeline grid
       --------------------------------------------------------------- */
    .detox-timeline__right-heading {
        font-size: 26px;
        color: var(--color-text);
    }

    .detox-timeline__right-heading span {
        color: var(--color-accent);
    }

    .detox-timeline__cards {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
        margin-top: 24px;
    }

    .detox-timeline__card {
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        padding: 20px;
    }

    .detox-timeline__range {
        font-size: 12px;
        font-weight: 600;
        color: var(--color-accent);
    }

    .detox-timeline__title {
        margin-top: 8px;
        font-size: 15px;
        color: var(--color-text);
    }

    .detox-timeline__desc {
        margin-top: 8px;
        font-size: 13px;
        line-height: 1.6;
        color: var(--color-text-light);
    }

    @media (max-width: 860px) {
        .detox-timeline__grid {
            grid-template-columns: 1fr;
            gap: 36px;
        }

        .detox-timeline__cards {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="detox-timeline">
    <div class="container detox-timeline__grid">

        <div class="detox-timeline__left">
            <h2 class="detox-timeline__heading">How Each Day <span>Will Look Like!</span></h2>
            <p class="detox-timeline__intro"><?= htmlspecialchars($timelineIntro, ENT_QUOTES, 'UTF-8') ?></p>

            <?php if ($videoPreview && $videoPreview['image']): ?>
                <a
                    class="detox-video detox-video--has-image"
                    href="<?= htmlspecialchars($videoPreview['url'] ?: '#', ENT_QUOTES, 'UTF-8') ?>"
                    <?= $videoPreview['url'] ? 'target="_blank" rel="noopener"' : 'onclick="return false;"' ?>
                >
                    <img src="<?= htmlspecialchars($videoPreview['image'], ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= htmlspecialchars($videoPreview['alt'], ENT_QUOTES, 'UTF-8') ?>">
                    <span class="detox-video__overlay">
                        <span class="detox-video__play">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                        </span>
                    </span>
                </a>
            <?php else: ?>
                <div class="detox-video">
                    <span class="detox-video__caption">Click to Preview<br>Course Video</span>
                    <span class="detox-video__overlay">
                        <span class="detox-video__play">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                        </span>
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <div class="detox-timeline__right">
            <h2 class="detox-timeline__right-heading">Your Transformation <span>Timeline</span></h2>

            <div class="detox-timeline__cards">
                <?php foreach ($timelineStages as $stage): ?>
                    <div class="detox-timeline__card">
                        <div class="detox-timeline__range"><?= htmlspecialchars(strtoupper($stage['range']), ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="detox-timeline__title"><?= htmlspecialchars($stage['title'], ENT_QUOTES, 'UTF-8') ?></div>
                        <p class="detox-timeline__desc"><?= htmlspecialchars($stage['desc'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</section>