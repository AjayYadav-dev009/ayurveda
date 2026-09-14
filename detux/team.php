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
?>

<style>
    .detox-team {
        padding: 8px 0 64px;
    }

    .detox-team__heading {
        text-align: center;
        font-size: 28px;
        color: var(--color-text);
    }

    .detox-team__heading span {
        color: var(--color-accent);
    }

    .detox-team__grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 24px;
        margin-top: 32px;
    }

    .detox-team__card {
        text-align: center;
    }

    .detox-team__photo {
        border-radius: var(--radius-md);
        overflow: hidden;
        aspect-ratio: 3 / 3.4;
        background: var(--color-primary-light);
    }

    .detox-team__photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .detox-team__photo-fallback {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--color-primary-dark);
        font-size: 32px;
        font-weight: 700;
    }

    .detox-team__name {
        margin-top: 16px;
        font-size: 16px;
        color: var(--color-text);
    }

    .detox-team__designation {
        margin-top: 4px;
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: 0.4px;
        color: var(--color-accent);
    }

    .detox-team__bio {
        margin-top: 10px;
        font-size: 13px;
        line-height: 1.6;
        color: var(--color-text-light);
    }

    @media (max-width: 860px) {
        .detox-team__grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>

<section class="detox-team">
    <div class="container">
        <h2 class="detox-team__heading">Your Ayurvedic <span>Team &amp; Support</span></h2>

        <?php if (empty($teamMembers)): ?>
            <p style="text-align:center; margin-top:24px; color:var(--color-text-light); font-size:14px;">
                No team members added yet — add rows to the <code>team_members</code> table to populate this section.
            </p>
        <?php else: ?>
            <div class="detox-team__grid">
                <?php foreach ($teamMembers as $member):
                    $photoUrl = getTeamMemberImageUrl($member['image'] ?? null);
                    $initial = strtoupper(mb_substr(trim((string) $member['name']), 0, 1));
                ?>
                    <div class="detox-team__card">
                        <div class="detox-team__photo">
                            <?php if ($photoUrl): ?>
                                <img src="<?= htmlspecialchars($photoUrl, ENT_QUOTES, 'UTF-8') ?>"
                                     alt="<?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?>">
                            <?php else: ?>
                                <div class="detox-team__photo-fallback"><?= htmlspecialchars($initial, ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="detox-team__name"><?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?></div>

                        <?php if (!empty($member['designation'])): ?>
                            <div class="detox-team__designation">
                                <?= htmlspecialchars(strtoupper($member['designation']), ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($member['bio'])): ?>
                            <p class="detox-team__bio"><?= htmlspecialchars($member['bio'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>