<?php

/**
 * detux/team.php
 *
 * Included from detux/index.php alongside the other detux sections.
 * No config/header/footer includes here — index.php already handles
 * those.
 *
 * Pulls from the existing `team_members` table via function/team.php
 * (new file — no schema change, no new table). Add/edit team members
 * directly in that table for now; nothing here is hardcoded.
 */

require_once __DIR__ . '/../function/team.php';

$teamMembers = getActiveTeamMembers($conn);

// Section copy
$teamEyebrow = 'Meet your practitioners';
$teamHeading = 'Your Ayurvedic Team & Support';
$teamIntro   = 'Experienced practitioners who guide you through every day of the programme.';

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
    .detox-team {
        --dx-primary: var(--color-primary, #1f3a32);
        --dx-primary-dark: var(--color-primary-dark, #142a24);
        --dx-primary-light: var(--color-primary-light, #edf1e8);
        --dx-accent: var(--color-accent, #b28a32);
        --dx-bg: var(--color-bg, #f8f6ef);
        --dx-text: var(--color-text, #26342f);
        --dx-muted: var(--color-text-light, #6f776f);
        --dx-border: var(--color-border, #ded9c9);
        --dx-heading-font: var(--font-heading, "Cormorant Garamond", "Playfair Display", Georgia, "Times New Roman", serif);
    }

    .detox-team {
        position: relative;
        overflow: hidden;
        background: var(--dx-bg);
        padding: clamp(64px, 8vw, 104px) 0;
    }

    .detox-team__sprite {
        position: absolute;
        width: 0;
        height: 0;
        overflow: hidden;
    }

    .detox-team__leaf {
        position: absolute;
        z-index: 0;
        color: var(--dx-primary);
        opacity: 0.08;
        pointer-events: none;
    }

    .detox-team__leaf--tl {
        top: 4%;
        left: -22px;
        width: clamp(90px, 9vw, 140px);
        transform: rotate(-24deg);
    }

    .detox-team__leaf--br {
        right: -18px;
        bottom: 6%;
        width: clamp(90px, 8vw, 130px);
        transform: rotate(28deg);
    }

    .detox-team .container {
        position: relative;
        z-index: 1;
    }

    /* ---------------------------------------------------------------
       Heading
       --------------------------------------------------------------- */
    .detox-team__head {
        text-align: center;
    }

    .detox-team__mark {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
        color: var(--dx-primary);
    }

    .detox-team__mark i {
        display: block;
        width: clamp(28px, 4vw, 44px);
        height: 1px;
        background: rgba(31, 58, 50, 0.25);
    }

    .detox-team__mark svg {
        width: 22px;
        height: 22px;
    }

    .detox-team__eyebrow {
        margin: 12px 0 0;
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        color: var(--dx-muted);
    }

    .detox-team__heading {
        margin: 6px 0 0;
        font-family: var(--dx-heading-font);
        font-size: clamp(2rem, 3.6vw, 3rem);
        font-weight: 700;
        line-height: 1.1;
        color: var(--dx-primary);
    }

    .detox-team__intro {
        margin: 14px auto 0;
        max-width: 48ch;
        font-size: clamp(1rem, 1.2vw, 1.06rem);
        line-height: 1.65;
        color: var(--dx-text);
        opacity: 0.8;
    }

    /* ---------------------------------------------------------------
       Profiles
       --------------------------------------------------------------- */
    .detox-team__grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: clamp(24px, 2.6vw, 40px);
        margin: clamp(36px, 5vw, 60px) 0 0;
        padding: 0;
        list-style: none;
    }

    .detox-team__card {
        text-align: center;
    }

    /* Arched portrait: a quiet nod to Ayurvedic clinic architecture */
    .detox-team__photo {
        position: relative;
        aspect-ratio: 3 / 3.6;
        overflow: hidden;
        border-radius: 50% 50% 22px 22px / 40% 40% 22px 22px;
        background: var(--dx-primary-light);
    }

    .detox-team__photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: 50% 14%;
        display: block;
        transition: transform 0.9s ease;
    }

    .detox-team__card:hover .detox-team__photo img {
        transform: scale(1.04);
    }

    .detox-team__photo-fallback {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(160deg, var(--dx-primary-light) 0%, #dfe6d6 100%);
        color: var(--dx-primary);
        font-family: var(--dx-heading-font);
        font-size: clamp(2.6rem, 5vw, 3.6rem);
        font-weight: 700;
    }

    .detox-team__name {
        margin: 22px 0 0;
        font-family: var(--dx-heading-font);
        font-size: clamp(1.3rem, 1.7vw, 1.5rem);
        font-weight: 700;
        line-height: 1.2;
        color: var(--dx-primary);
    }

    .detox-team__designation {
        margin: 8px 0 0;
        font-size: 12.5px;
        font-weight: 600;
        letter-spacing: 0.14em;
        line-height: 1.5;
        text-transform: uppercase;
        color: var(--dx-primary);
        opacity: 0.75;
    }

    .detox-team__rule {
        display: block;
        width: 28px;
        height: 1px;
        margin: 14px auto 0;
        background: var(--dx-accent);
    }

    .detox-team__bio {
        margin: 14px auto 0;
        max-width: 34ch;
        font-size: 0.95rem;
        line-height: 1.65;
        color: var(--dx-text);
        opacity: 0.8;
    }

    .detox-team__empty {
        margin: 32px 0 0;
        text-align: center;
        font-size: 14px;
        color: var(--dx-muted);
    }

    /* ---------------------------------------------------------------
       Responsive
       --------------------------------------------------------------- */

    /* Tablet: two per row */
    @media (max-width: 999px) {
        .detox-team__grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            row-gap: 48px;
            max-width: 640px;
            margin-left: auto;
            margin-right: auto;
        }
    }

    /* Phone: swipeable row, next profile peeks in */
    @media (max-width: 599px) {
        .detox-team__grid {
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

        .detox-team__grid::-webkit-scrollbar {
            display: none;
        }

        .detox-team__card {
            flex: 0 0 76%;
            scroll-snap-align: start;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .detox-team__photo img {
            transition: none;
        }

        .detox-team__card:hover .detox-team__photo img {
            transform: none;
        }
    }
</style>

<section class="detox-team" aria-labelledby="detoxTeamHeading">
    <svg class="detox-team__sprite" aria-hidden="true" focusable="false">
        <symbol id="detox-team-sprig" viewBox="0 0 120 200">
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
        <symbol id="detox-team-leaf-mark" viewBox="0 0 24 24">
            <path d="M5 19c0-8 5-14 15-14 0 10-6 15-14 15z" />
            <path d="M5 19c4-5 7-8 11-10" />
        </symbol>
    </svg>

    <svg class="detox-team__leaf detox-team__leaf--tl" viewBox="0 0 120 200" aria-hidden="true">
        <use href="#detox-team-sprig" />
    </svg>
    <svg class="detox-team__leaf detox-team__leaf--br" viewBox="0 0 120 200" aria-hidden="true">
        <use href="#detox-team-sprig" />
    </svg>

    <div class="container">
        <div class="detox-team__head">
            <div class="detox-team__mark" aria-hidden="true">
                <i></i>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <use href="#detox-team-leaf-mark" />
                </svg>
                <i></i>
            </div>
            <p class="detox-team__eyebrow"><?= detoxE($teamEyebrow) ?></p>
            <h2 class="detox-team__heading" id="detoxTeamHeading"><?= detoxE($teamHeading) ?></h2>
            <p class="detox-team__intro"><?= detoxE($teamIntro) ?></p>
        </div>

        <?php if (empty($teamMembers)): ?>
            <p class="detox-team__empty">
                No team members added yet — add rows to the <code>team_members</code> table to populate this section.
            </p>
        <?php else: ?>
            <ul class="detox-team__grid">
                <?php foreach ($teamMembers as $member):
                    $photoUrl = getTeamMemberImageUrl($member['image'] ?? null);
                    $nameTrim = trim((string) $member['name']);
                    $initial  = function_exists('mb_substr')
                        ? mb_strtoupper(mb_substr($nameTrim, 0, 1))
                        : strtoupper(substr($nameTrim, 0, 1));
                ?>
                    <li class="detox-team__card">
                        <div class="detox-team__photo">
                            <?php if ($photoUrl): ?>
                                <img src="<?= detoxE($photoUrl) ?>"
                                    alt="<?= detoxE($member['name']) ?>"
                                    loading="lazy">
                            <?php else: ?>
                                <div class="detox-team__photo-fallback" aria-hidden="true"><?= detoxE($initial) ?></div>
                            <?php endif; ?>
                        </div>

                        <h3 class="detox-team__name"><?= detoxE($member['name']) ?></h3>

                        <?php if (!empty($member['designation'])): ?>
                            <p class="detox-team__designation"><?= detoxE($member['designation']) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($member['bio'])): ?>
                            <span class="detox-team__rule" aria-hidden="true"></span>
                            <p class="detox-team__bio"><?= detoxE($member['bio']) ?></p>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>