<?php

/**
 * about/team.php
 *
 * Included from the About page alongside the other about-* sections.
 * No config/header/footer includes here — the parent page already
 * handles those.
 *
 * Pulls from the existing `team_members` table via function/team.php
 * (same helper used by detux/team.php — no schema change, no new table,
 * no new query). Add/edit team members directly in that table for now;
 * nothing here is hardcoded.
 */

require_once __DIR__ . '/../function/team.php';

$aboutTeamMembers = getActiveTeamMembers($conn);

// Section copy
$aboutTeamEyebrow = 'Our Team';
$aboutTeamHeading = 'The People Behind Your Wellness';
$aboutTeamIntro   = 'A team of certified Ayurvedic practitioners, healers, and wellness experts committed to your health journey.';

if (!function_exists('aboutTeamE')) {
    function aboutTeamE($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
?>

<style>
    /* Design tokens: fall back to the approved brand palette.
       Change --at-heading-font to your homepage heading font variable. */
    .about-team {
        --at-primary: var(--color-primary, #1f3a32);
        --at-primary-dark: var(--color-primary-dark, #142a24);
        --at-primary-light: var(--color-primary-light, #edf1e8);
        --at-accent: var(--color-accent, #b28a32);
        --at-bg: var(--color-bg, #f8f6ef);
        --at-text: var(--color-text, #26342f);
        --at-muted: var(--color-text-light, #6f776f);
        --at-border: var(--color-border, #ded9c9);
        --at-heading-font: var(--font-heading, "Cormorant Garamond", "Playfair Display", Georgia, "Times New Roman", serif);
    }

    .about-team {
        position: relative;
        overflow: hidden;
        background: var(--at-bg);
        padding: clamp(64px, 8vw, 104px) 0;
    }

    .about-team__sprite {
        position: absolute;
        width: 0;
        height: 0;
        overflow: hidden;
    }

    .about-team__leaf {
        position: absolute;
        z-index: 0;
        color: var(--at-primary);
        opacity: 0.08;
        pointer-events: none;
    }

    .about-team__leaf--tl {
        top: 4%;
        left: -22px;
        width: clamp(90px, 9vw, 140px);
        transform: rotate(-24deg);
    }

    .about-team__leaf--br {
        right: -18px;
        bottom: 6%;
        width: clamp(90px, 8vw, 130px);
        transform: rotate(28deg);
    }

    .about-team .container {
        position: relative;
        z-index: 1;
    }

    /* ---------------------------------------------------------------
       Heading
       --------------------------------------------------------------- */
    .about-team__head {
        text-align: center;
    }

    .about-team__mark {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
        color: var(--at-primary);
    }

    .about-team__mark i {
        display: block;
        width: clamp(28px, 4vw, 44px);
        height: 1px;
        background: rgba(31, 58, 50, 0.25);
    }

    .about-team__mark svg {
        width: 22px;
        height: 22px;
    }

    .about-team__eyebrow {
        margin: 12px 0 0;
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        color: var(--at-muted);
    }

    .about-team__heading {
        margin: 6px 0 0;
        font-family: var(--at-heading-font);
        font-size: clamp(2rem, 3.6vw, 3rem);
        font-weight: 700;
        line-height: 1.1;
        color: var(--at-primary);
    }

    .about-team__intro {
        margin: 14px auto 0;
        max-width: 48ch;
        font-size: clamp(1rem, 1.2vw, 1.06rem);
        line-height: 1.65;
        color: var(--at-text);
        opacity: 0.8;
    }

    /* ---------------------------------------------------------------
       Profiles
       --------------------------------------------------------------- */
    .about-team__grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: clamp(24px, 2.6vw, 40px);
        margin: clamp(36px, 5vw, 60px) 0 0;
        padding: 0;
        list-style: none;
    }

    .about-team__card {
        text-align: center;
    }

    /* Arched portrait: a quiet nod to Ayurvedic clinic architecture */
    .about-team__photo {
        position: relative;
        aspect-ratio: 3 / 3.6;
        overflow: hidden;
        border-radius: 50% 50% 22px 22px / 40% 40% 22px 22px;
        background: var(--at-primary-light);
    }

    .about-team__photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: 50% 14%;
        display: block;
        transition: transform 0.9s ease;
    }

    .about-team__card:hover .about-team__photo img {
        transform: scale(1.04);
    }

    .about-team__photo-fallback {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(160deg, var(--at-primary-light) 0%, #dfe6d6 100%);
        color: var(--at-primary);
        font-family: var(--at-heading-font);
        font-size: clamp(2.6rem, 5vw, 3.6rem);
        font-weight: 700;
    }

    .about-team__name {
        margin: 22px 0 0;
        font-family: var(--at-heading-font);
        font-size: clamp(1.3rem, 1.7vw, 1.5rem);
        font-weight: 700;
        line-height: 1.2;
        color: var(--at-primary);
    }

    .about-team__designation {
        margin: 8px 0 0;
        font-size: 12.5px;
        font-weight: 600;
        letter-spacing: 0.14em;
        line-height: 1.5;
        text-transform: uppercase;
        color: var(--at-primary);
        opacity: 0.75;
    }

    .about-team__rule {
        display: block;
        width: 28px;
        height: 1px;
        margin: 14px auto 0;
        background: var(--at-accent);
    }

    .about-team__bio {
        margin: 14px auto 0;
        max-width: 34ch;
        font-size: 0.95rem;
        line-height: 1.65;
        color: var(--at-text);
        opacity: 0.8;
    }

    .about-team__empty {
        margin: 32px 0 0;
        text-align: center;
        font-size: 14px;
        color: var(--at-muted);
    }

    /* ---------------------------------------------------------------
       Responsive
       --------------------------------------------------------------- */

    /* Tablet: two per row */
    @media (max-width: 999px) {
        .about-team__grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            row-gap: 48px;
            max-width: 640px;
            margin-left: auto;
            margin-right: auto;
        }
    }

    /* Phone: swipeable row, next profile peeks in */
    @media (max-width: 599px) {
        .about-team__grid {
            display: flex;
            gap: 18px;
            width: 100vw;
            max-width: none;
            margin-left: calc(50% - 50vw);
            margin-right: 0;
            padding: 0 24px 8px;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-padding-inline: 24px;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }

        .about-team__grid::-webkit-scrollbar {
            display: none;
        }

        .about-team__card {
            flex: 0 0 76%;
            scroll-snap-align: start;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .about-team__photo img {
            transition: none;
        }

        .about-team__card:hover .about-team__photo img {
            transform: none;
        }
    }
</style>

<section class="about-team" aria-labelledby="aboutTeamHeading">
    <svg class="about-team__sprite" aria-hidden="true" focusable="false">
        <symbol id="about-team-sprig" viewBox="0 0 120 200">
            <path d="M60 198C54 140 66 80 60 8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
            <g fill="currentColor">
                <ellipse cx="86" cy="150" rx="26" ry="10" transform="rotate(-38 86 150)" />
                <ellipse cx="34" cy="126" rx="26" ry="10" transform="rotate(34 34 126)" />
                <ellipse cx="88" cy="100" rx="25" ry="9.5" transform="rotate(-34 88 100)" />
                <ellipse cx="34" cy="76" rx="24" ry="9.5" transform="rotate(32 34 76)" />
                <ellipse cx="84" cy="50" rx="21" ry="8.5" transform="rotate(-32 84 50)" />
                <ellipse cx="40" cy="30" rx="18" ry="7.5" transform="rotate(30 40 30)" />
                <ellipse cx="62" cy="9" rx="12" ry="6" transform="rotate(-80 62 9)" />
            </g>
        </symbol>
        <symbol id="about-team-leaf-mark" viewBox="0 0 24 24">
            <path d="M5 19c0-8 5-14 15-14 0 10-6 15-14 15z" />
            <path d="M5 19c4-5 7-8 11-10" />
        </symbol>
    </svg>

    <svg class="about-team__leaf about-team__leaf--tl" viewBox="0 0 120 200" aria-hidden="true">
        <use href="#about-team-sprig" />
    </svg>
    <svg class="about-team__leaf about-team__leaf--br" viewBox="0 0 120 200" aria-hidden="true">
        <use href="#about-team-sprig" />
    </svg>

    <div class="container">
        <div class="about-team__head">
            <div class="about-team__mark" aria-hidden="true">
                <i></i>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <use href="#about-team-leaf-mark" />
                </svg>
                <i></i>
            </div>
            <p class="about-team__eyebrow"><?= aboutTeamE($aboutTeamEyebrow) ?></p>
            <h2 class="about-team__heading" id="aboutTeamHeading"><?= aboutTeamE($aboutTeamHeading) ?></h2>
            <p class="about-team__intro"><?= aboutTeamE($aboutTeamIntro) ?></p>
        </div>

        <?php if (empty($aboutTeamMembers)): ?>
            <p class="about-team__empty">
                No team members added yet — add rows to the <code>team_members</code> table to populate this section.
            </p>
        <?php else: ?>
            <ul class="about-team__grid">
                <?php foreach ($aboutTeamMembers as $member):
                    $photoUrl = getTeamMemberImageUrl($member['image'] ?? null);
                    $nameTrim = trim((string) $member['name']);
                    $initial  = function_exists('mb_substr')
                        ? mb_strtoupper(mb_substr($nameTrim, 0, 1))
                        : strtoupper(substr($nameTrim, 0, 1));
                ?>
                    <li class="about-team__card">
                        <div class="about-team__photo">
                            <?php if ($photoUrl): ?>
                                <img src="<?= aboutTeamE($photoUrl) ?>"
                                    alt="<?= aboutTeamE($member['name']) ?>"
                                    loading="lazy">
                            <?php else: ?>
                                <div class="about-team__photo-fallback" aria-hidden="true"><?= aboutTeamE($initial) ?></div>
                            <?php endif; ?>
                        </div>

                        <h3 class="about-team__name"><?= aboutTeamE($member['name']) ?></h3>

                        <?php if (!empty($member['designation'])): ?>
                            <p class="about-team__designation"><?= aboutTeamE($member['designation']) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($member['bio'])): ?>
                            <span class="about-team__rule" aria-hidden="true"></span>
                            <p class="about-team__bio"><?= aboutTeamE($member['bio']) ?></p>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>
