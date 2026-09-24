<?php

/**
 * detux/timeline.php
 *
 * Included from detux/index.php alongside hero.php / highlights.php /
 * information.php. No config/header/footer includes here — index.php
 * already handles those.
 *
 * Left: course-preview video visual (image from Banner Management,
 * position = 'detox_video_preview'; falls back to a dark-green botanical
 * panel if none is set). Middle: heading, intro and "Watch a preview" link.
 * Right: connected "transformation timeline", hardcoded below.
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

$timelineEyebrow = 'How Each Day Will Look Like';
$timelineHeading = 'Your Daily Wellness Journey';
$timelineIntro   = 'Daily guided videos so you always know exactly what to do, when to do it, and why it works.';
$stagesEyebrow   = 'Your Transformation Timeline';

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

if (!function_exists('detoxE')) {
    function detoxE($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
?>

<style>
    /* Design tokens: fall back to the approved brand palette.
       Change --dx-heading-font to your homepage heading font variable. */
    .detox-timeline {
        --dx-primary: var(--color-primary, #1f3a32);
        --dx-primary-dark: var(--color-primary-dark, #142a24);
        --dx-primary-light: var(--color-primary-light, #edf1e8);
        --dx-accent: var(--color-accent, #b28a32);
        --dx-bg: var(--color-bg, #f8f6ef);
        --dx-text: var(--color-text, #26342f);
        --dx-muted: var(--color-text-light, #6f776f);
        --dx-border: var(--color-border, #ded9c9);
        --dx-white: var(--color-white, #ffffff);
        --dx-heading-font: var(--font-heading, "Cormorant Garamond", "Playfair Display", Georgia, "Times New Roman", serif);
        --dx-gap: clamp(28px, 3.6vw, 56px);
    }

    .detox-timeline {
        position: relative;
        overflow: hidden;
        background: var(--dx-primary-light);
        padding: 40px 0;
    }

    .detox-timeline__sprite {
        position: absolute;
        width: 0;
        height: 0;
        overflow: hidden;
    }

    .detox-timeline__leaf {
        position: absolute;
        z-index: 0;
        color: var(--dx-primary);
        opacity: 0.14;
        pointer-events: none;
    }

    .detox-timeline__leaf--l {
        left: -14px;
        top: 20%;
        width: clamp(70px, 7vw, 110px);
        transform: rotate(-10deg);
    }

    .detox-timeline__leaf--r {
        right: -12px;
        bottom: 5%;
        width: clamp(70px, 6vw, 100px);
        transform: rotate(26deg);
    }

    .detox-timeline__grid {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr) minmax(0, 1.1fr);
        column-gap: var(--dx-gap);
        align-items: center;
    }

    /* ---------------------------------------------------------------
       Visual (organic blob + video)
       --------------------------------------------------------------- */
    .detox-timeline__visual {
        position: relative;
        width: 100%;
        max-width: 520px;
        margin: 0 auto;
        padding-left: clamp(14px, 2.2vw, 32px);
    }

    /* pale organic shape peeking out behind the image */
    .detox-timeline__visual::before {
        content: "";
        position: absolute;
        left: 0;
        bottom: -5%;
        width: 74%;
        height: 62%;
        border-radius: 52% 48% 56% 44% / 46% 54% 46% 54%;
        background: rgba(255, 255, 255, 0.55);
    }

    /* fine gold arc */
    .detox-timeline__visual::after {
        content: "";
        position: absolute;
        left: -2%;
        bottom: -9%;
        width: 60%;
        aspect-ratio: 1;
        border: 1px solid rgba(178, 138, 50, 0.6);
        border-top-color: transparent;
        border-right-color: transparent;
        border-radius: 50%;
        transform: rotate(6deg);
    }

    .detox-video {
        position: relative;
        z-index: 1;
        display: block;
        aspect-ratio: 1 / 1;
        overflow: hidden;
        border-radius: 44% 56% 48% 52% / 40% 46% 54% 60%;
        background: var(--dx-primary);
    }

    .detox-video img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.9s ease;
    }

    a.detox-video:hover img {
        transform: scale(1.04);
    }

    .detox-video__play {
        position: absolute;
        top: 50%;
        left: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        width: clamp(56px, 6vw, 72px);
        height: clamp(56px, 6vw, 72px);
        border-radius: 50%;
        background: var(--dx-white);
        color: var(--dx-primary-dark);
        box-shadow: 0 8px 24px rgba(20, 42, 36, 0.18);
        transform: translate(-50%, -50%);
        transition: transform 0.3s ease;
    }

    .detox-video__play svg {
        width: 40%;
        height: 40%;
        margin-left: 4%;
    }

    a.detox-video:hover .detox-video__play {
        transform: translate(-50%, -50%) scale(1.07);
    }

    a.detox-video:focus-visible,
    .detox-timeline__watch:focus-visible {
        outline: 3px solid var(--dx-accent);
        outline-offset: 4px;
    }

    /* No banner uploaded yet: intentional botanical panel */
    .detox-video--empty {
        display: flex;
        align-items: center;
        justify-content: center;
        background:
            radial-gradient(70% 60% at 25% 20%, rgba(178, 138, 50, 0.24), rgba(178, 138, 50, 0) 70%),
            linear-gradient(160deg, var(--dx-primary) 0%, var(--dx-primary-dark) 100%);
    }

    .detox-video--empty .detox-timeline__leaf {
        color: #f8f6ef;
        opacity: 0.1;
    }

    .detox-video--empty .detox-timeline__leaf--a {
        top: 10%;
        left: 12%;
        width: 30%;
        transform: rotate(-22deg);
    }

    .detox-video--empty .detox-timeline__leaf--b {
        bottom: 6%;
        right: 14%;
        width: 26%;
        transform: rotate(150deg);
    }

    .detox-video__caption {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 17%;
        padding: 0 12%;
        text-align: center;
        font-family: var(--dx-heading-font);
        font-size: clamp(1.05rem, 1.6vw, 1.35rem);
        font-weight: 700;
        line-height: 1.25;
        color: #f8f6ef;
    }

    /* ---------------------------------------------------------------
       Copy column
       --------------------------------------------------------------- */
    .detox-timeline__eyebrow {
        display: flex;
        align-items: center;
        gap: 14px;
        margin: 0;
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0.2em;
        line-height: 1.5;
        text-transform: uppercase;
        white-space: nowrap;
        color: var(--dx-muted);
    }

    .detox-timeline__eyebrow::after {
        content: "";
        flex: 0 1 clamp(28px, 4vw, 48px);
        min-width: 0;
        height: 1px;
        background: var(--dx-accent);
        opacity: 0.7;
    }

    .detox-timeline__heading {
        margin: 16px 0 0;
        font-family: var(--dx-heading-font);
        font-size: clamp(2.1rem, 3.5vw, 3.1rem);
        font-weight: 700;
        line-height: 1.08;
        letter-spacing: -0.01em;
        color: var(--dx-primary);
    }

    .detox-timeline__intro {
        margin: 18px 0 0;
        max-width: 40ch;
        font-size: clamp(0.98rem, 1.15vw, 1.05rem);
        line-height: 1.7;
        color: var(--dx-text);
        opacity: 0.85;
    }

    .detox-timeline__watch {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        margin-top: 26px;
        font-size: 15px;
        font-weight: 500;
        color: var(--dx-primary);
        text-decoration: none;
    }

    .detox-timeline__watch span:last-child {
        text-underline-offset: 4px;
    }

    .detox-timeline__watch:hover span:last-child {
        text-decoration: underline;
    }

    .detox-timeline__watch-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: var(--dx-accent);
        color: var(--dx-white);
        transition: transform 0.3s ease;
    }

    .detox-timeline__watch:hover .detox-timeline__watch-icon {
        transform: scale(1.08);
    }

    .detox-timeline__watch-icon svg {
        width: 11px;
        height: 11px;
        margin-left: 1px;
    }

    /* ---------------------------------------------------------------
       Timeline column
       --------------------------------------------------------------- */
    .detox-timeline__stages {
        align-self: stretch;
        padding-left: var(--dx-gap);
        border-left: 1px solid rgba(31, 58, 50, 0.15);
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .detox-stages {
        list-style: none;
        margin: 30px 0 0;
        padding: 0;
    }

    .detox-stage {
        --draw-hidden: scaleY(0);
        --draw-shown: scaleY(1);
        position: relative;
        padding: 0 0 clamp(22px, 2.6vw, 34px) 38px;
    }

    .detox-stage:last-child {
        padding-bottom: 0;
    }

    /* dot */
    .detox-stage::before {
        content: "";
        position: absolute;
        left: 0;
        top: 7px;
        width: 11px;
        height: 11px;
        border-radius: 50%;
        background: var(--dx-accent);
        box-shadow: 0 0 0 5px rgba(178, 138, 50, 0.16);
    }

    /* connector to the next dot */
    .detox-stage:not(:last-child)::after {
        content: "";
        position: absolute;
        left: 5px;
        top: 26px;
        bottom: -5px;
        width: 1px;
        background: rgba(178, 138, 50, 0.5);
        transform-origin: top;
    }

    .detox-stage__range {
        margin: 0;
        font-family: var(--dx-heading-font);
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.2;
        color: var(--dx-primary);
    }

    .detox-stage__title {
        margin: 4px 0 0;
        font-size: 0.98rem;
        font-weight: 600;
        line-height: 1.4;
        color: var(--dx-text);
    }

    .detox-stage__desc {
        margin: 6px 0 0;
        max-width: 46ch;
        font-size: 0.95rem;
        line-height: 1.65;
        color: var(--dx-muted);
    }

    /* Subtle reveal: armed by JS only, so content is never hidden without it */
    .detox-stages.is-armed .detox-stage {
        opacity: 0;
        transform: translateY(10px);
    }

    .detox-stages.is-armed .detox-stage:not(:last-child)::after {
        transform: var(--draw-hidden);
    }

    .detox-stages.is-visible .detox-stage {
        opacity: 1;
        transform: none;
        transition: opacity 0.6s ease calc(var(--i) * 150ms), transform 0.6s ease calc(var(--i) * 150ms);
    }

    .detox-stages.is-visible .detox-stage:not(:last-child)::after {
        transform: var(--draw-shown);
        transition: transform 0.6s ease calc(var(--i) * 150ms + 250ms);
    }

    /* ---------------------------------------------------------------
       Responsive
       --------------------------------------------------------------- */

    /* Tablet: image + copy side by side, timeline below */
    @media (max-width: 1099px) {
        .detox-timeline__grid {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            row-gap: 48px;
        }

        .detox-timeline__stages {
            grid-column: 1 / -1;
            align-self: auto;
            padding-left: 0;
            padding-top: 44px;
            border-left: 0;
            border-top: 1px solid rgba(31, 58, 50, 0.15);
        }

    }

    /* Tablet: timeline runs horizontally so the wide row isn't left empty */
    @media (min-width: 760px) and (max-width: 1099px) {
        .detox-stages {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            column-gap: 28px;
            row-gap: 36px;
        }

        .detox-stage,
        .detox-stage:last-child {
            --draw-hidden: scaleX(0);
            --draw-shown: scaleX(1);
            padding: 34px 0 0;
        }

        .detox-stage::before {
            top: 0;
        }

        .detox-stage:not(:last-child)::after {
            left: 24px;
            right: -18px;
            top: 5px;
            bottom: auto;
            width: auto;
            height: 1px;
            transform-origin: left;
        }

        .detox-stage:nth-child(4n)::after {
            display: none;
        }
    }

    @media (min-width: 760px) and (max-width: 899px) {
        .detox-stages {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .detox-stage:nth-child(2n)::after {
            display: none;
        }
    }

    /* Phone: single column */
    @media (max-width: 759px) {
        .detox-timeline__grid {
            grid-template-columns: minmax(0, 1fr);
            row-gap: 36px;
        }

        .detox-timeline__visual {
            max-width: 400px;
        }

        .detox-timeline__leaf--r {
            display: none;
        }

        .detox-timeline__stages {
            padding-top: 36px;
        }

        .detox-stage {
            padding-left: 34px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .detox-video img,
        .detox-video__play,
        .detox-timeline__watch-icon {
            transition: none;
        }

        a.detox-video:hover img {
            transform: none;
        }
    }
</style>

<section class="detox-timeline" aria-labelledby="detoxTimelineHeading">
    <svg class="detox-timeline__sprite" aria-hidden="true" focusable="false">
        <symbol id="detox-tl-sprig" viewBox="0 0 120 200">
            <path d="M60 198C54 140 66 80 60 8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
            <g fill="currentColor">
                <ellipse cx="86" cy="150" rx="26" ry="10" transform="rotate(-38 86 150)"/>
                <ellipse cx="34" cy="126" rx="26" ry="10" transform="rotate(34 34 126)"/>
                <ellipse cx="88" cy="100" rx="25" ry="9.5" transform="rotate(-34 88 100)"/>
                <ellipse cx="34" cy="76" rx="24" ry="9.5" transform="rotate(32 34 76)"/>
                <ellipse cx="84" cy="50" rx="21" ry="8.5" transform="rotate(-32 84 50)"/>
                <ellipse cx="40" cy="30" rx="18" ry="7.5" transform="rotate(30 40 30)"/>
                <ellipse cx="62" cy="9" rx="12" ry="6" transform="rotate(-80 62 9)"/>
            </g>
        </symbol>
    </svg>

    <svg class="detox-timeline__leaf detox-timeline__leaf--l" viewBox="0 0 120 200" aria-hidden="true"><use href="#detox-tl-sprig"/></svg>
    <svg class="detox-timeline__leaf detox-timeline__leaf--r" viewBox="0 0 120 200" aria-hidden="true"><use href="#detox-tl-sprig"/></svg>

    <div class="container detox-timeline__grid">

        <!-- Video visual -->
        <div class="detox-timeline__visual">
            <?php if ($videoPreview && $videoPreview['image']): ?>
                <a
                    class="detox-video detox-video--has-image"
                    href="<?= detoxE($videoPreview['url'] ?: '#') ?>"
                    aria-label="Watch the course preview video"
                    <?= $videoPreview['url'] ? 'target="_blank" rel="noopener"' : 'onclick="return false;"' ?>
                >
                    <img src="<?= detoxE($videoPreview['image']) ?>"
                         alt="<?= detoxE($videoPreview['alt']) ?>">
                    <span class="detox-video__play">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                    </span>
                </a>
            <?php else: ?>
                <div class="detox-video detox-video--empty">
                    <svg class="detox-timeline__leaf detox-timeline__leaf--a" viewBox="0 0 120 200" aria-hidden="true"><use href="#detox-tl-sprig"/></svg>
                    <svg class="detox-timeline__leaf detox-timeline__leaf--b" viewBox="0 0 120 200" aria-hidden="true"><use href="#detox-tl-sprig"/></svg>
                    <span class="detox-video__caption">Click to Preview<br>Course Video</span>
                    <span class="detox-video__play">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Heading + intro -->
        <div class="detox-timeline__copy">
            <p class="detox-timeline__eyebrow"><?= detoxE($timelineEyebrow) ?></p>
            <h2 class="detox-timeline__heading" id="detoxTimelineHeading"><?= detoxE($timelineHeading) ?></h2>
            <p class="detox-timeline__intro"><?= detoxE($timelineIntro) ?></p>

            <?php if ($videoPreview && $videoPreview['url']): ?>
                <a class="detox-timeline__watch"
                   href="<?= detoxE($videoPreview['url']) ?>"
                   target="_blank" rel="noopener">
                    <span class="detox-timeline__watch-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                    </span>
                    <span>Watch a preview</span>
                </a>
            <?php endif; ?>
        </div>

        <!-- Timeline -->
        <div class="detox-timeline__stages">
            <h3 class="detox-timeline__eyebrow"><?= detoxE($stagesEyebrow) ?></h3>

            <ol class="detox-stages" id="detoxStages">
                <?php foreach ($timelineStages as $stageIndex => $stage): ?>
                    <li class="detox-stage" style="--i: <?= (int) $stageIndex ?>">
                        <p class="detox-stage__range"><?= detoxE(str_replace('-', '–', $stage['range'])) ?></p>
                        <p class="detox-stage__title"><?= detoxE($stage['title']) ?></p>
                        <p class="detox-stage__desc"><?= detoxE($stage['desc']) ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>

    </div>
</section>

<script>
    (function () {
        var list = document.getElementById('detoxStages');

        if (!list || !('IntersectionObserver' in window)) {
            return;
        }
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        list.classList.add('is-armed');

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    list.classList.add('is-visible');
                    observer.disconnect();
                }
            });
        }, { threshold: 0.1 });

        observer.observe(list);
    })();
</script>