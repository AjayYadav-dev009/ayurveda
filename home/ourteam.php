<?php

if (!function_exists('getActiveTeamMembers')) {
    require_once __DIR__ . '/../function/team.php';
}

try {
    $teamMembers = getActiveTeamMembers($conn, 12);
} catch (Exception $e) {
    // Fail closed: hide the section rather than show a broken slider.
    $teamMembers = [];
}

$teamCount = count($teamMembers);

// Fallback quote when a team member has no "quote" column/value.
$tmeDefaultQuote = 'True wellness comes from balance – within and around you.';

// Small inline SVG helpers (no image files required).
$tmeLeafSprig = '<svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <path d="M10 190C60 150 110 100 175 20" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
    <path d="M52 152c-22-4-34-22-36-44 22 2 38 16 36 44Z" fill="currentColor" opacity=".55"/>
    <path d="M74 128c-6-24 2-44 24-56 8 24 0 44-24 56Z" fill="currentColor" opacity=".7"/>
    <path d="M100 100c14-18 34-24 56-18-8 22-28 30-56 18Z" fill="currentColor" opacity=".5"/>
    <path d="M124 70c-4-22 6-40 28-48 6 22-2 38-28 48Z" fill="currentColor" opacity=".7"/>
    <path d="M40 176c-18 4-32-4-40-20 18-6 34 0 40 20Z" fill="currentColor" opacity=".45"/>
</svg>';

$tmeSprout = '<svg viewBox="0 0 32 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <path d="M16 22V12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
    <path d="M16 12C16 6 11 3 4 3c0 6 4 9 12 9Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
    <path d="M16 12c0-6 5-9 12-9 0 6-4 9-12 9Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
</svg>';
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,600;0,700;1,400;1,500&display=swap" rel="stylesheet">

<style>
    /* ==========================================================================
       Meet Our Ayurvedic Experts — homepage slider. Namespaced "tme".
       Layout: heading → single wide card (photo | details | quote) with
       prev/next arrows → dots → trust strip. Decorative leaves are static
       and live outside the slides, so the slider JS never touches them.
       ========================================================================== */

    .tme {
        --tme-green-dark: #0f5132;
        --tme-green: #2f6b4f;
        --tme-green-soft: #dfe9e1;
        --tme-bg: #f6f8f3;
        --tme-card: #f0f4ee;
        --tme-line: #c9d6cb;
        --tme-text: #4a5750;
        --tme-serif: 'Lora', Georgia, 'Times New Roman', serif;

        position: relative;
        overflow: hidden;
        background: var(--tme-bg);
        padding-top: 30px;
    }

    /* ---- Decorative leaf sprigs (static) ---- */

    .tme__leaf {
        position: absolute;
        color: #9fbba6;
        opacity: 0.45;
        pointer-events: none;
        z-index: 0;
    }

    .tme__leaf svg {
        width: 100%;
        height: 100%;
        display: block;
    }

    .tme__leaf--tl {
        top: -10px;
        left: -14px;
        width: 170px;
        height: 170px;
        transform: rotate(-8deg);
    }

    .tme__leaf--tr {
        top: -6px;
        right: -14px;
        width: 150px;
        height: 150px;
        transform: scaleX(-1) rotate(-8deg);
    }

    .tme__leaf--bl {
        bottom: 4px;
        left: -10px;
        width: 90px;
        height: 100px;
        opacity: 0.4;
    }

    .tme__leaf--br {
        bottom: 4px;
        right: -10px;
        width: 90px;
        height: 100px;
        opacity: 0.4;
        transform: scaleX(-1);
    }

    /* ---- Heading block ---- */

    .tme__head {
        position: relative;
        z-index: 1;
        text-align: center;
        max-width: 700px;
        margin: 0 auto 30px;
        padding: 0 20px;
    }

    .tme__eyebrow {
        display: block;
        margin: 0 0 6px;
        font-size: 13px;
        font-weight: 500;
        letter-spacing: 0.24em;
        text-transform: uppercase;
        color: var(--tme-green);
    }

    .tme__heading {
        margin: 0 0 12px;
        font-family: var(--tme-serif);
        font-size: 40px;
        line-height: 1.15;
        font-weight: 700;
        color: var(--tme-green-dark);
    }

    .tme__subheading {
        margin: 0;
        font-size: 15px;
        line-height: 1.7;
        color: var(--tme-text);
    }

    .tme__ornament {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 14px;
        margin-top: 16px;
        color: var(--tme-green);
    }

    .tme__ornament::before,
    .tme__ornament::after {
        content: "";
        width: 42px;
        height: 1px;
        background: var(--tme-line);
    }

    .tme__ornament svg {
        width: 26px;
        height: 20px;
    }

    /* ---- Slider ---- */

    .tme__stage {
        position: relative;
        z-index: 1;
        max-width: 1260px;
        margin: 0 auto;
        padding: 0 60px;
    }

    /* soft leaf shape peeking from behind the card (left) */
    .tme__stage::before {
        content: "";
        position: absolute;
        left: 22px;
        top: 34px;
        width: 70px;
        height: 130px;
        background: #a9c2b0;
        opacity: 0.7;
        border-radius: 0 100% 0 100%;
        transform: rotate(-12deg);
        z-index: 0;
    }

    .tme__viewport {
        position: relative;
        z-index: 1;
    }

    .tme__slide {
        display: none;
    }

    .tme__slide.is-active {
        display: grid;
        grid-template-columns: 390px minmax(0, 1fr) 260px;
        align-items: center;
        gap: 32px;
        padding: 12px;
        background: var(--tme-card);
        border-radius: 22px;
        box-shadow: 0 10px 30px rgba(31, 74, 52, 0.08), 0 1px 3px rgba(31, 74, 52, 0.06);
        animation: tmeFade 0.45s ease;
    }

    @keyframes tmeFade {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .tme__slide.is-active {
            animation: none;
        }
    }

    /* Photo */

    .tme__photo-wrap {
        position: relative;
        height: 300px;
        border-radius: 16px;
        overflow: hidden;
        background: var(--tme-green-soft);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .tme__photo-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center top;
        display: block;
    }

    .tme__photo-fallback svg {
        width: 90px;
        height: 90px;
        color: var(--tme-green);
        opacity: 0.6;
    }

    .tme__badge {
        position: absolute;
        left: 20px;
        bottom: 16px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        max-width: calc(100% - 40px);
        padding: 9px 18px 9px 14px;
        border-radius: 999px;
        background: var(--tme-green-dark);
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.2;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
    }

    .tme__badge svg {
        width: 18px;
        height: 14px;
        flex: 0 0 auto;
    }

    /* Details */

    .tme__info {
        min-width: 0;
        padding: 4px 0;
    }

    .tme__name {
        margin: 0 0 8px;
        font-family: var(--tme-serif);
        font-size: 32px;
        line-height: 1.2;
        font-weight: 600;
        color: var(--tme-green-dark);
    }

    .tme__designation {
        margin: 0;
        font-size: 13px;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--tme-text);
    }

    .tme__rule {
        display: block;
        width: 92px;
        height: 2px;
        margin: 18px 0;
        background: var(--tme-line);
        border: 0;
    }

    .tme__bio {
        margin: 0 0 22px;
        max-width: 640px;
        font-size: 16px;
        line-height: 1.65;
        color: var(--tme-text);
        display: -webkit-box;
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .tme__points {
        display: flex;
        flex-wrap: wrap;
        gap: 0;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .tme__point {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0 22px;
        font-size: 14px;
        line-height: 1.35;
        color: var(--tme-text);
    }

    .tme__point+.tme__point {
        border-left: 1px solid var(--tme-line);
    }

    .tme__icon {
        flex: 0 0 auto;
        width: 48px;
        height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 1.5px solid #b9cbbd;
        color: var(--tme-green);
        background: rgba(255, 255, 255, 0.35);
    }

    .tme__icon svg {
        width: 24px;
        height: 24px;
    }

    /* Quote */

    .tme__quote {
        position: relative;
        align-self: stretch;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 0 24px 0 8px;
        margin: 0;
    }

    .tme__quote-mark {
        font-family: var(--tme-serif);
        font-size: 84px;
        line-height: 0.7;
        height: 40px;
        color: #c3d0c6;
        display: block;
    }

    .tme__quote-text {
        margin: 6px 0 20px;
        font-family: var(--tme-serif);
        font-style: italic;
        font-size: 19px;
        line-height: 1.6;
        color: var(--tme-green-dark);
    }

    .tme__quote .tme__ornament {
        margin-top: 0;
        justify-content: flex-start;
        padding-left: 0;
    }

    .tme__quote .tme__ornament::before {
        width: 28px;
    }

    .tme__quote .tme__ornament::after {
        width: 28px;
    }

    .tme__quote .tme__ornament svg {
        width: 22px;
        height: 16px;
    }

    /* Arrows */

    .tme__arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 3;
        width: 56px;
        height: 56px;
        padding: 0;
        border: 0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--tme-green-soft);
        color: var(--tme-green-dark);
        cursor: pointer;
        box-shadow: 0 3px 10px rgba(31, 74, 52, 0.12);
        transition: background 0.2s ease, color 0.2s ease;
    }

    .tme__arrow:hover {
        background: var(--tme-green-dark);
        color: #fff;
    }

    .tme__arrow:focus-visible {
        outline: 2px solid var(--tme-green-dark);
        outline-offset: 3px;
    }

    .tme__arrow svg {
        width: 22px;
        height: 22px;
    }

    .tme__arrow--prev {
        left: 0;
    }

    .tme__arrow--next {
        right: 0;
    }

    /* Dots */

    .tme__dots {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin: 26px 0 30px;
    }

    .tme__dots::before,
    .tme__dots::after {
        content: "";
        width: 34px;
        height: 1px;
        background: var(--tme-line);
    }

    .tme__dots::before {
        margin-right: 6px;
    }

    .tme__dots::after {
        margin-left: 6px;
    }

    .tme__dot {
        width: 12px;
        height: 12px;
        padding: 0;
        border: 0;
        border-radius: 50%;
        background: #b4c4b8;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .tme__dot.is-active {
        background: var(--tme-green-dark);
    }

    .tme__dot:focus-visible {
        outline: 2px solid var(--tme-green-dark);
        outline-offset: 3px;
    }

    /* Trust strip */

    .tme__trust {
        position: relative;
        z-index: 1;
        background: rgba(232, 240, 231, 0.55);
        padding: 22px 0 26px;
    }

    .tme__trust-list {
        max-width: 1180px;
        margin: 0 auto;
        padding: 0 40px;
        list-style: none;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
    }

    .tme__trust-item {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 14px;
        padding: 6px 16px;
    }

    .tme__trust-item+.tme__trust-item {
        border-left: 1px solid var(--tme-line);
    }

    .tme__trust-icon {
        flex: 0 0 auto;
        width: 58px;
        height: 58px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 1.5px solid #b9cbbd;
        color: var(--tme-green);
    }

    .tme__trust-icon svg {
        width: 28px;
        height: 28px;
    }

    .tme__trust-title {
        display: block;
        font-size: 16px;
        font-weight: 600;
        color: var(--tme-green-dark);
    }

    .tme__trust-sub {
        display: block;
        margin-top: 2px;
        font-size: 13px;
        color: var(--tme-text);
    }

    /* ---- Responsive ---- */

    @media (max-width: 1200px) {
        .tme__slide.is-active {
            grid-template-columns: 320px minmax(0, 1fr);
        }

        .tme__quote {
            display: none;
        }

        .tme__photo-wrap {
            height: 280px;
        }
    }

    @media (max-width: 900px) {
        .tme__stage {
            padding: 0 20px;
        }

        .tme__stage::before {
            display: none;
        }

        .tme__slide.is-active {
            grid-template-columns: 1fr;
            gap: 20px;
            padding: 10px 10px 24px;
        }

        .tme__photo-wrap {
            height: 300px;
        }

        .tme__info {
            padding: 0 14px;
        }

        .tme__points {
            gap: 14px;
        }

        .tme__point {
            padding: 0;
            border: 0 !important;
            width: 100%;
        }

        .tme__arrow {
            top: 150px;
            width: 42px;
            height: 42px;
        }

        .tme__arrow--prev {
            left: 26px;
        }

        .tme__arrow--next {
            right: 26px;
        }

        .tme__trust-list {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px 0;
            padding: 0 20px;
        }

        .tme__trust-item:nth-child(3) {
            border-left: 0;
        }
    }

    @media (max-width: 560px) {
        .tme__heading {
            font-size: 30px;
        }

        .tme__name {
            font-size: 26px;
        }

        .tme__photo-wrap {
            height: 260px;
        }

        .tme__trust-list {
            grid-template-columns: 1fr;
        }

        .tme__trust-item {
            justify-content: flex-start;
            border-left: 0 !important;
        }

        .tme__leaf--tl,
        .tme__leaf--tr {
            width: 110px;
            height: 110px;
        }

        .tme__leaf--bl,
        .tme__leaf--br {
            display: none;
        }
    }
</style>

<?php if ($teamCount > 0): ?>
    <section class="tme" data-tme aria-labelledby="tme-heading">
        <span class="tme__leaf tme__leaf--tl"><?= $tmeLeafSprig ?></span>
        <span class="tme__leaf tme__leaf--tr"><?= $tmeLeafSprig ?></span>
        <span class="tme__leaf tme__leaf--bl"><?= $tmeLeafSprig ?></span>
        <span class="tme__leaf tme__leaf--br"><?= $tmeLeafSprig ?></span>

        <div class="tme__head">
            <span class="tme__eyebrow">Our Experts</span>
            <h2 class="tme__heading" id="tme-heading">Meet Our Ayurvedic Experts</h2>
            <p class="tme__subheading">Guided by tradition, backed by knowledge. Our team of qualified Ayurvedic experts brings you authentic wellness, rooted in nature and science.</p>
            <div class="tme__ornament" aria-hidden="true"><?= $tmeSprout ?></div>
        </div>

        <div class="tme__stage">
            <?php if ($teamCount > 1): ?>
                <button type="button" class="tme__arrow tme__arrow--prev" data-tme-prev aria-label="Previous expert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 5l-7 7 7 7" />
                    </svg>
                </button>
                <button type="button" class="tme__arrow tme__arrow--next" data-tme-next aria-label="Next expert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            <?php endif; ?>

            <div class="tme__viewport" aria-live="polite">
                <?php foreach ($teamMembers as $index => $member):
                    $imageUrl = getTeamMemberImageUrl($member['image'] ?? null);
                    $designation = !empty($member['designation']) ? $member['designation'] : 'Ayurveda Expert';
                    $quote = !empty($member['quote']) ? $member['quote'] : $tmeDefaultQuote;
                ?>
                    <article class="tme__slide <?= $index === 0 ? 'is-active' : '' ?>" data-tme-slide>
                        <div class="tme__photo-wrap">
                            <?php if ($imageUrl): ?>
                                <img src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                            <?php else: ?>
                                <span class="tme__photo-fallback">
                                    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="32" cy="22" r="12" fill="currentColor" />
                                        <path d="M10 58c0-13 10-22 22-22s22 9 22 22" stroke="currentColor" stroke-width="4" stroke-linecap="round" fill="none" />
                                    </svg>
                                </span>
                            <?php endif; ?>
                            <span class="tme__badge">
                                <?= $tmeSprout ?>
                                <?= htmlspecialchars($designation, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>

                        <div class="tme__info">
                            <h3 class="tme__name"><?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="tme__designation"><?= htmlspecialchars($designation, ENT_QUOTES, 'UTF-8') ?></p>
                            <hr class="tme__rule">
                            <?php if (!empty($member['bio'])): ?>
                                <p class="tme__bio"><?= htmlspecialchars($member['bio'], ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>

                            <ul class="tme__points">
                                <li class="tme__point">
                                    <span class="tme__icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M5 19c0-8 5-14 15-14 0 10-6 15-14 15" />
                                            <path d="M5 19c3-5 6-8 10-10" />
                                        </svg>
                                    </span>
                                    <span>Personalized<br>Wellness Plans</span>
                                </li>
                                <li class="tme__point">
                                    <span class="tme__icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 12h18c0 4.5-3.5 8-9 8s-9-3.5-9-8Z" />
                                            <path d="M8 12c0-2 1.5-3 4-3s4 1 4 3" />
                                            <path d="M12 9V5m0 0c1.5 0 3 .5 4 1.5" />
                                        </svg>
                                    </span>
                                    <span>Traditional<br>Ayurvedic Expertise</span>
                                </li>
                                <li class="tme__point">
                                    <span class="tme__icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.5A4 4 0 0 1 19 10c0 5.6-7 10-7 10Z" />
                                        </svg>
                                    </span>
                                    <span>Holistic &amp;<br>Balanced Living</span>
                                </li>
                            </ul>
                        </div>

                        <blockquote class="tme__quote">
                            <span class="tme__quote-mark" aria-hidden="true">&ldquo;</span>
                            <p class="tme__quote-text">&ldquo;<?= htmlspecialchars($quote, ENT_QUOTES, 'UTF-8') ?>&rdquo;</p>
                            <span class="tme__ornament" aria-hidden="true"><?= $tmeSprout ?></span>
                        </blockquote>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($teamCount > 1): ?>
            <div class="tme__dots" data-tme-dots>
                <?php foreach ($teamMembers as $index => $member): ?>
                    <button type="button" class="tme__dot <?= $index === 0 ? 'is-active' : '' ?>" data-tme-dot="<?= $index ?>" aria-label="Show <?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?>"></button>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="height:30px"></div>
        <?php endif; ?>

        <div class="tme__trust">
            <ul class="tme__trust-list">
                <li class="tme__trust-item">
                    <span class="tme__trust-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 19c0-8 5-14 15-14 0 10-6 15-14 15" />
                            <path d="M5 19c3-5 6-8 10-10" />
                        </svg>
                    </span>
                    <span><span class="tme__trust-title">Authentic Ayurveda</span><span class="tme__trust-sub">Time-tested wisdom</span></span>
                </li>
                <li class="tme__trust-item">
                    <span class="tme__trust-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3l7 3v5c0 5-3 8.5-7 10-4-1.5-7-5-7-10V6l7-3Z" />
                            <path d="M9 12l2 2 4-4" />
                        </svg>
                    </span>
                    <span><span class="tme__trust-title">Quality Assured</span><span class="tme__trust-sub">Pure &amp; safe products</span></span>
                </li>
                <li class="tme__trust-item">
                    <span class="tme__trust-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="3" />
                            <circle cx="5.5" cy="10" r="2" />
                            <circle cx="18.5" cy="10" r="2" />
                            <path d="M6.5 20c0-3.500 2.5-6 5.5-6s5.5 2.5 5.5 6" />
                            <path d="M2 18c0-2 1.5-3.500 3.500-3.500M22 18c0-2-1.5-3.500-3.500-3.500" />
                        </svg>
                    </span>
                    <span><span class="tme__trust-title">Trusted by Thousands</span><span class="tme__trust-sub">Across India and beyond</span></span>
                </li>
                <li class="tme__trust-item">
                    <span class="tme__trust-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5c2 2 3 4 3 6.5S14 16 12 17c-2-1-3-3-3-5.5S10 7 12 5Z" />
                            <path d="M12 17c-4 0-7-2-8-6 3 0 5.5 1 7 3" />
                            <path d="M12 17c4 0 7-2 8-6-3 0-5.5 1-7 3" />
                            <path d="M6 20h12" />
                        </svg>
                    </span>
                    <span><span class="tme__trust-title">Holistic Wellness</span><span class="tme__trust-sub">For a healthier you</span></span>
                </li>
            </ul>
        </div>
    </section>

    <?php if ($teamCount > 1): ?>
        <script>
            (function() {
                // Scoped to [data-tme] — only swaps .is-active on slides/dots.
                // Leaves and the trust strip are static and never touched.
                document.querySelectorAll('[data-tme]').forEach(function(root) {
                    var slides = Array.prototype.slice.call(root.querySelectorAll('[data-tme-slide]'));
                    var dots = Array.prototype.slice.call(root.querySelectorAll('[data-tme-dot]'));
                    var prev = root.querySelector('[data-tme-prev]');
                    var next = root.querySelector('[data-tme-next]');

                    if (slides.length <= 1) {
                        return;
                    }

                    var current = 0;
                    var autoTimer = null;
                    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                    function show(index) {
                        current = (index + slides.length) % slides.length;
                        slides.forEach(function(slide, i) {
                            slide.classList.toggle('is-active', i === current);
                        });
                        dots.forEach(function(dot, i) {
                            dot.classList.toggle('is-active', i === current);
                        });
                    }

                    function stopAutoplay() {
                        window.clearInterval(autoTimer);
                        autoTimer = null;
                    }

                    function startAutoplay() {
                        if (reduceMotion) {
                            return;
                        }
                        stopAutoplay();
                        autoTimer = window.setInterval(function() {
                            show(current + 1);
                        }, 7000);
                    }

                    dots.forEach(function(dot, i) {
                        dot.addEventListener('click', function() {
                            show(i);
                            startAutoplay();
                        });
                    });
                    if (prev) {
                        prev.addEventListener('click', function() {
                            show(current - 1);
                            startAutoplay();
                        });
                    }
                    if (next) {
                        next.addEventListener('click', function() {
                            show(current + 1);
                            startAutoplay();
                        });
                    }

                    root.addEventListener('mouseenter', stopAutoplay);
                    root.addEventListener('mouseleave', startAutoplay);

                    startAutoplay();
                });
            })();
        </script>
    <?php endif; ?>
<?php endif; ?>