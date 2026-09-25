<?php

/**
 * dosha-test.php
 *
 * PLACEHOLDER landing page for a started dosha test session.
 *
 * This is intentionally minimal — the actual question flow (the real
 * "Dosha Test") wasn't part of this task and doesn't exist yet. This page
 * only proves the handoff works: it reads the session_token that
 * ajax/dosha-submit.php redirected to (or the dosha_session_token cookie,
 * for a returning visitor), looks up the lead via getDoshaLeadByToken(),
 * and greets them by name.
 *
 * Replace the body of this file with the real question-by-question flow
 * when that's ready. Keep reading the token the same way so it stays
 * connected to the lead captured by the modal.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../function/dosha.php';
require_once __DIR__ . '/../includes/header.php';

$token = trim((string) ($_GET['token'] ?? ($_COOKIE['dosha_session_token'] ?? '')));
$lead  = $token !== '' ? getDoshaLeadByToken($conn, $token) : null;
?>

<section style="max-width:640px;margin:0 auto;padding:96px 24px;text-align:center;">
    <?php if ($lead): ?>
        <p style="font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:#8a6a22;">Ayurveda Dosha Test</p>
        <h1 style="font-family:'Cormorant Garamond','Playfair Display',serif;color:#1f3a32;font-size:2.2rem;margin:12px 0 0;">
            Welcome, <?= htmlspecialchars(explode(' ', $lead['full_name'])[0], ENT_QUOTES, 'UTF-8') ?>
        </h1>
        <p style="margin:14px 0 0;color:#5f6a63;line-height:1.6;">
            Your details have been saved and your dosha test session has started.
            The question-by-question assessment goes here — replace this
            placeholder with the real flow when it's built.
        </p>
        <p style="margin:24px 0 0;font-size:12px;color:#9aa39a;">
            Session ref: <?= htmlspecialchars($lead['session_token'], ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php else: ?>
        <h1 style="font-family:'Cormorant Garamond','Playfair Display',serif;color:#1f3a32;font-size:2rem;margin:0;">
            We couldn't find that test session
        </h1>
        <p style="margin:14px 0 0;color:#5f6a63;line-height:1.6;">
            The link may have expired. Please start the dosha test again from the homepage.
        </p>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
