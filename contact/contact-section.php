<?php

/**
 * contact/contact-section.php
 *
 * Included from contact/index.php, which provides:
 *   $conn, $topics, $errors, $old, $csrfToken, $flashSuccess, $contactPageUrl
 * Contact details are read from the `settings` table (see sql/contact-setup.sql).
 */

$details = getContactDetails($conn);

$contactEyebrow = 'Contact Information';
$contactHeading = 'Get In Touch';
$contactIntro   = 'Reach out to us through any of the channels below. We’d love to hear from you.';
$formHeading    = 'Send Us a Message';

$locationEyebrow = 'Our Location';
$locationButton  = 'Get Directions';
$locationImgAlt  = 'Entrance to our wellness center';

if (!function_exists('detoxE')) {
    function detoxE($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

$hasDetails = $details['phone']['value'] !== ''
    || $details['email']['value'] !== ''
    || !empty($details['address']['lines']);

// Map + text + photo band shown under the form (see getContactLocation()).
$location = getContactLocation($conn, $details['address']['lines']);
?>

<style>
    /* Design tokens: fall back to the approved brand palette.
       Change --dx-heading-font to your homepage heading font variable. */
    .contact-page {
        --dx-primary: var(--color-primary, #1f3a32);
        --dx-primary-dark: var(--color-primary-dark, #142a24);
        --dx-primary-light: var(--color-primary-light, #edf1e8);
        --dx-accent: var(--color-accent, #b28a32);
        --dx-bg: var(--color-bg, #f8f6ef);
        --dx-text: var(--color-text, #26342f);
        --dx-muted: var(--color-text-light, #6f776f);
        --dx-border: var(--color-border, #ded9c9);
        --dx-white: var(--color-white, #ffffff);
        --dx-error: #b3261e;
        --dx-heading-font: var(--font-heading, "Cormorant Garamond", "Playfair Display", Georgia, "Times New Roman", serif);
    }

    .contact-page {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(60% 70% at 85% 10%, rgba(237, 241, 232, 0.9), rgba(237, 241, 232, 0) 70%),
            var(--dx-bg);
        padding: clamp(56px, 7vw, 100px) 0;
    }

    .contact-sprite {
        position: absolute;
        width: 0;
        height: 0;
        overflow: hidden;
    }

    .contact-leaf {
        position: absolute;
        z-index: 0;
        color: var(--dx-primary);
        opacity: 0.1;
        pointer-events: none;
    }

    .contact-leaf--l {
        left: -20px;
        top: 34%;
        width: clamp(90px, 9vw, 140px);
        transform: rotate(-16deg);
    }

    .contact-leaf--r {
        right: -18px;
        top: 44%;
        width: clamp(90px, 8vw, 130px);
        transform: rotate(22deg);
    }

    .contact-page .container {
        position: relative;
        z-index: 1;
    }

    .contact-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr);
        gap: clamp(32px, 5vw, 88px);
        align-items: start;
    }

    /* ---------------------------------------------------------------
       Left: details
       --------------------------------------------------------------- */
    .contact-eyebrow {
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

    .contact-eyebrow::after {
        content: "";
        flex: 0 1 clamp(28px, 4vw, 48px);
        min-width: 0;
        height: 1px;
        background: var(--dx-accent);
        opacity: 0.8;
    }

    .contact-heading {
        margin: 16px 0 0;
        font-family: var(--dx-heading-font);
        font-size: clamp(2.4rem, 4.4vw, 3.6rem);
        font-weight: 700;
        line-height: 1.06;
        letter-spacing: -0.01em;
        color: var(--dx-primary);
    }

    .contact-intro {
        margin: 18px 0 0;
        max-width: 42ch;
        font-size: clamp(1rem, 1.2vw, 1.08rem);
        line-height: 1.7;
        color: var(--dx-text);
        opacity: 0.82;
    }

    .contact-list {
        display: flex;
        flex-direction: column;
        gap: clamp(22px, 2.6vw, 32px);
        margin: clamp(32px, 4vw, 48px) 0 0;
        padding: 0;
        list-style: none;
    }

    .contact-item {
        display: grid;
        grid-template-columns: 68px minmax(0, 1fr);
        column-gap: 22px;
        align-items: center;
    }

    .contact-item__icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 68px;
        height: 68px;
        border-radius: 50%;
        background: rgba(31, 58, 50, 0.08);
        color: var(--dx-primary);
    }

    .contact-item__icon svg {
        width: 28px;
        height: 28px;
    }

    .contact-item__title {
        margin: 0;
        font-family: var(--dx-heading-font);
        font-size: 1.3rem;
        font-weight: 700;
        line-height: 1.2;
        color: var(--dx-primary);
    }

    .contact-item__line {
        margin: 6px 0 0;
        font-size: 1rem;
        line-height: 1.5;
        color: var(--dx-text);
    }

    .contact-item__line + .contact-item__line {
        margin-top: 2px;
    }

    .contact-item__line a {
        color: inherit;
        text-decoration: none;
    }

    .contact-item__line a:hover {
        color: var(--dx-primary);
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .contact-item__note {
        margin: 4px 0 0;
        font-size: 0.9rem;
        line-height: 1.5;
        color: var(--dx-muted);
    }

    /* ---------------------------------------------------------------
       Right: form
       --------------------------------------------------------------- */
    .contact-card {
        padding: clamp(24px, 3.4vw, 44px);
        border-radius: 24px;
        background: rgba(237, 241, 232, 0.85);
        box-shadow: inset 0 0 0 1px rgba(31, 58, 50, 0.06);
    }

    .contact-card__title {
        display: flex;
        align-items: center;
        gap: 14px;
        margin: 0;
        font-family: var(--dx-heading-font);
        font-size: clamp(1.5rem, 2.2vw, 1.9rem);
        font-weight: 700;
        line-height: 1.2;
        color: var(--dx-primary);
    }

    .contact-card__title svg {
        width: 26px;
        height: 26px;
        flex: 0 0 auto;
        color: var(--dx-primary);
    }

    .contact-form {
        display: grid;
        gap: 14px;
        margin-top: 24px;
    }

    .contact-field {
        min-width: 0;
    }

    .contact-sr {
        position: absolute;
        width: 1px;
        height: 1px;
        margin: -1px;
        padding: 0;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    /* Spam trap: kept off-screen, never shown to people */
    .contact-hp {
        position: absolute;
        left: -9999px;
        width: 1px;
        height: 1px;
        overflow: hidden;
    }

    .contact-input,
    .contact-select,
    .contact-textarea {
        display: block;
        width: 100%;
        padding: 15px 18px;
        border: 1px solid transparent;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.78);
        color: var(--dx-text);
        font-family: inherit;
        font-size: 1rem;
        line-height: 1.4;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }

    .contact-input::placeholder,
    .contact-textarea::placeholder {
        color: var(--dx-muted);
        opacity: 1;
    }

    .contact-select {
        appearance: none;
        -webkit-appearance: none;
        padding-right: 46px;
        cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%236f776f' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 16px center;
    }

    .contact-select.is-empty {
        color: var(--dx-muted);
    }

    .contact-select option {
        color: var(--dx-text);
    }

    .contact-textarea {
        min-height: 150px;
        resize: vertical;
    }

    .contact-input:focus,
    .contact-select:focus,
    .contact-textarea:focus {
        outline: none;
        border-color: var(--dx-primary);
        background-color: var(--dx-white);
        box-shadow: 0 0 0 3px rgba(31, 58, 50, 0.12);
    }

    .contact-input[aria-invalid="true"],
    .contact-select[aria-invalid="true"],
    .contact-textarea[aria-invalid="true"] {
        border-color: var(--dx-error);
    }

    .contact-error {
        margin: 6px 2px 0;
        font-size: 0.875rem;
        line-height: 1.4;
        color: var(--dx-error);
    }

    .contact-alert {
        margin: 20px 0 0;
        padding: 14px 16px;
        border-radius: 10px;
        font-size: 0.975rem;
        line-height: 1.55;
    }

    .contact-alert--error {
        color: #8a1c14;
        background: #fbeceb;
        border: 1px solid #f2c6c2;
    }

    .contact-alert--success {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        color: var(--dx-primary-dark);
        background: var(--dx-white);
        border: 1px solid rgba(31, 58, 50, 0.16);
    }

    .contact-alert--success svg {
        width: 22px;
        height: 22px;
        flex: 0 0 auto;
        margin-top: 1px;
        color: var(--dx-accent);
    }

    .contact-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        min-height: 56px;
        margin-top: 6px;
        padding: 14px 22px;
        border: 0;
        border-radius: 10px;
        background: var(--dx-primary);
        color: var(--dx-white);
        font-family: inherit;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.25s ease, transform 0.25s ease;
    }

    .contact-submit svg {
        width: 18px;
        height: 18px;
        flex: 0 0 auto;
        transition: transform 0.25s ease;
    }

    .contact-submit:hover {
        background: var(--dx-primary-dark);
    }

    .contact-submit:hover svg {
        transform: translateX(3px);
    }

    .contact-submit:disabled {
        opacity: 0.7;
        cursor: progress;
    }

    .contact-submit:focus-visible,
    .contact-item__line a:focus-visible {
        outline: 3px solid var(--dx-accent);
        outline-offset: 3px;
    }

    /* ---------------------------------------------------------------
       Location band: map | text | photo on a soft green panel that
       bleeds off the right edge of the screen
       --------------------------------------------------------------- */
    .contact-location {
        position: relative;
        display: grid;
        grid-template-columns: minmax(0, 1.55fr) minmax(0, 1fr) minmax(0, 0.8fr);
        gap: clamp(22px, 3.2vw, 44px);
        align-items: stretch;
        margin-top: clamp(48px, 6vw, 88px);
        padding: 14px 0;
    }

    .contact-location--no-image {
        grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
    }

    /* The panel starts behind the right part of the map and runs to the
       edge of the viewport (the section clips any overflow). */
    .contact-location::before {
        content: "";
        position: absolute;
        z-index: 0;
        top: 0;
        bottom: 0;
        left: 34%;
        right: calc(50% - 50vw);
        border-radius: 28px 0 0 28px;
        background: rgba(237, 241, 232, 0.85);
        box-shadow: inset 0 0 0 1px rgba(31, 58, 50, 0.05);
    }

    .contact-location__map,
    .contact-location__photo {
        position: relative;
        z-index: 1;
        min-height: 240px;
        border-radius: 18px;
        overflow: hidden;
        background: var(--dx-primary-light);
    }

    .contact-location__map iframe,
    .contact-location__photo img {
        position: absolute;
        inset: 0;
        display: block;
        width: 100%;
        height: 100%;
        border: 0;
    }

    /* Soften the map colours so it sits in the brand palette */
    .contact-location__map iframe {
        filter: grayscale(0.3) sepia(0.28) saturate(0.85) contrast(0.96);
    }

    .contact-location__photo img {
        object-fit: cover;
    }

    .contact-location__body {
        position: relative;
        z-index: 1;
        align-self: center;
    }

    .contact-location__title {
        margin: 14px 0 0;
        font-family: var(--dx-heading-font);
        font-size: clamp(1.5rem, 2.3vw, 2rem);
        font-weight: 700;
        line-height: 1.18;
        color: var(--dx-primary);
    }

    .contact-location__text {
        margin: 14px 0 0;
        max-width: 38ch;
        font-size: 0.98rem;
        line-height: 1.7;
        color: var(--dx-text);
        opacity: 0.82;
    }

    .contact-location__btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-top: 24px;
        padding: 14px 28px;
        border-radius: 999px;
        background: var(--dx-primary);
        color: var(--dx-white);
        font-size: 0.95rem;
        font-weight: 500;
        line-height: 1.2;
        text-decoration: none;
        transition: background-color 0.25s ease;
    }

    .contact-location__btn svg {
        width: 16px;
        height: 16px;
        flex: 0 0 auto;
        transition: transform 0.25s ease;
    }

    .contact-location__btn:hover {
        background: var(--dx-primary-dark);
    }

    .contact-location__btn:hover svg {
        transform: translateX(3px);
    }

    .contact-location__btn:focus-visible {
        outline: 3px solid var(--dx-accent);
        outline-offset: 3px;
    }

    /* ---------------------------------------------------------------
       Responsive
       --------------------------------------------------------------- */
    @media (max-width: 899px) {
        .contact-location,
        .contact-location--no-image {
            grid-template-columns: minmax(0, 1fr);
            gap: 24px;
            padding: 18px 0;
        }

        .contact-location::before {
            left: calc(50% - 50vw);
            border-radius: 0;
        }

        .contact-location__map,
        .contact-location__photo {
            min-height: 220px;
        }

        .contact-location__text {
            max-width: 56ch;
        }

        .contact-grid {
            grid-template-columns: minmax(0, 1fr);
            gap: 44px;
        }

        .contact-intro {
            max-width: 56ch;
        }

        .contact-leaf--r {
            display: none;
        }
    }

    @media (max-width: 479px) {
        .contact-item {
            grid-template-columns: 56px minmax(0, 1fr);
            column-gap: 16px;
        }

        .contact-item__icon {
            width: 56px;
            height: 56px;
        }

        .contact-item__icon svg {
            width: 24px;
            height: 24px;
        }

        .contact-card {
            border-radius: 20px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .contact-submit,
        .contact-submit svg,
        .contact-input,
        .contact-select,
        .contact-textarea {
            transition: none;
        }

        .contact-submit:hover svg {
            transform: none;
        }

        .contact-location__btn,
        .contact-location__btn svg {
            transition: none;
        }

        .contact-location__btn:hover svg {
            transform: none;
        }
    }
</style>

<section class="contact-page" aria-labelledby="contactHeading">
    <svg class="contact-sprite" aria-hidden="true" focusable="false">
        <symbol id="contact-sprig" viewBox="0 0 120 200">
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

    <svg class="contact-leaf contact-leaf--l" viewBox="0 0 120 200" aria-hidden="true"><use href="#contact-sprig"/></svg>
    <svg class="contact-leaf contact-leaf--r" viewBox="0 0 120 200" aria-hidden="true"><use href="#contact-sprig"/></svg>

    <div class="container contact-grid">

        <!-- Details (from the settings table) -->
        <div class="contact-info">
            <p class="contact-eyebrow"><?= detoxE($contactEyebrow) ?></p>
            <h1 class="contact-heading" id="contactHeading"><?= detoxE($contactHeading) ?></h1>
            <p class="contact-intro"><?= detoxE($contactIntro) ?></p>

            <?php if ($hasDetails): ?>
                <ul class="contact-list">

                    <?php if ($details['phone']['value'] !== ''): ?>
                        <li class="contact-item">
                            <span class="contact-item__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </span>
                            <div>
                                <h2 class="contact-item__title">Call Us</h2>
                                <p class="contact-item__line">
                                    <?php if ($details['phone']['href'] !== ''): ?>
                                        <a href="<?= detoxE($details['phone']['href']) ?>"><?= detoxE($details['phone']['value']) ?></a>
                                    <?php else: ?>
                                        <?= detoxE($details['phone']['value']) ?>
                                    <?php endif; ?>
                                </p>
                                <?php if ($details['phone']['note'] !== ''): ?>
                                    <p class="contact-item__note"><?= detoxE($details['phone']['note']) ?></p>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endif; ?>

                    <?php if ($details['email']['value'] !== ''): ?>
                        <li class="contact-item">
                            <span class="contact-item__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            </span>
                            <div>
                                <h2 class="contact-item__title">Email Us</h2>
                                <p class="contact-item__line">
                                    <?php if ($details['email']['href'] !== ''): ?>
                                        <a href="<?= detoxE($details['email']['href']) ?>"><?= detoxE($details['email']['value']) ?></a>
                                    <?php else: ?>
                                        <?= detoxE($details['email']['value']) ?>
                                    <?php endif; ?>
                                </p>
                                <?php if ($details['email']['note'] !== ''): ?>
                                    <p class="contact-item__note"><?= detoxE($details['email']['note']) ?></p>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endif; ?>

                    <?php if (!empty($details['address']['lines'])): ?>
                        <li class="contact-item">
                            <span class="contact-item__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            </span>
                            <div>
                                <h2 class="contact-item__title">Visit Us</h2>
                                <?php foreach ($details['address']['lines'] as $addressLine): ?>
                                    <p class="contact-item__line"><?= detoxE($addressLine) ?></p>
                                <?php endforeach; ?>
                            </div>
                        </li>
                    <?php endif; ?>

                </ul>
            <?php endif; ?>
        </div>

        <!-- Form -->
        <div class="contact-card" id="contact-form">
            <h2 class="contact-card__title">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M5 19c0-8 5-14 15-14 0 10-6 15-14 15z"/><path d="M5 19c4-5 7-8 11-10" fill="none" stroke="#edf1e8" stroke-width="1.4" stroke-linecap="round"/></svg>
                <?= detoxE($formHeading) ?>
            </h2>

            <?php if ($flashSuccess): ?>
                <div class="contact-alert contact-alert--success" role="status">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9.5"/><path d="m8 12.4 2.8 2.8L16.2 9.6"/></svg>
                    <span>Thank you for reaching out. Your message has been sent and we’ll get back to you soon.</span>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors['form'])): ?>
                <div class="contact-alert contact-alert--error" role="alert"><?= detoxE($errors['form']) ?></div>
            <?php endif; ?>

            <form class="contact-form" method="post" action="<?= detoxE($contactPageUrl) ?>#contact-form" id="contactForm">
                <input type="hidden" name="csrf_token" value="<?= detoxE($csrfToken) ?>">

                <div class="contact-hp" aria-hidden="true">
                    <label>Leave this field empty
                        <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
                    </label>
                </div>

                <div class="contact-field">
                    <label class="contact-sr" for="contactName">Your name</label>
                    <input class="contact-input" type="text" id="contactName" name="name"
                           placeholder="Your Name *" maxlength="150" autocomplete="name" required
                           value="<?= detoxE($old['name']) ?>"
                           <?php if (!empty($errors['name'])): ?>aria-invalid="true" aria-describedby="contactNameError"<?php endif; ?>>
                    <?php if (!empty($errors['name'])): ?>
                        <p class="contact-error" id="contactNameError"><?= detoxE($errors['name']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="contact-field">
                    <label class="contact-sr" for="contactEmail">Your email</label>
                    <input class="contact-input" type="email" id="contactEmail" name="email"
                           placeholder="Your Email *" maxlength="191" autocomplete="email" required
                           value="<?= detoxE($old['email']) ?>"
                           <?php if (!empty($errors['email'])): ?>aria-invalid="true" aria-describedby="contactEmailError"<?php endif; ?>>
                    <?php if (!empty($errors['email'])): ?>
                        <p class="contact-error" id="contactEmailError"><?= detoxE($errors['email']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="contact-field">
                    <label class="contact-sr" for="contactPhone">Phone number</label>
                    <input class="contact-input" type="tel" id="contactPhone" name="phone"
                           placeholder="Phone Number" maxlength="25" autocomplete="tel"
                           value="<?= detoxE($old['phone']) ?>"
                           <?php if (!empty($errors['phone'])): ?>aria-invalid="true" aria-describedby="contactPhoneError"<?php endif; ?>>
                    <?php if (!empty($errors['phone'])): ?>
                        <p class="contact-error" id="contactPhoneError"><?= detoxE($errors['phone']) ?></p>
                    <?php endif; ?>
                </div>

                <?php if (!empty($topics)): ?>
                    <div class="contact-field">
                        <label class="contact-sr" for="contactTopic">I’m interested in</label>
                        <select class="contact-select<?= $old['topic'] === '' ? ' is-empty' : '' ?>" id="contactTopic" name="topic"
                                <?php if (!empty($errors['topic'])): ?>aria-invalid="true" aria-describedby="contactTopicError"<?php endif; ?>>
                            <option value="">I’m interested in</option>
                            <?php foreach ($topics as $topicOption): ?>
                                <option value="<?= detoxE($topicOption) ?>"<?= $old['topic'] === $topicOption ? ' selected' : '' ?>><?= detoxE($topicOption) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors['topic'])): ?>
                            <p class="contact-error" id="contactTopicError"><?= detoxE($errors['topic']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="contact-field">
                    <label class="contact-sr" for="contactMessage">Your message</label>
                    <textarea class="contact-textarea" id="contactMessage" name="message"
                              placeholder="Your Message *" maxlength="<?= (int) CONTACT_MESSAGE_MAX_LENGTH ?>" required
                              <?php if (!empty($errors['message'])): ?>aria-invalid="true" aria-describedby="contactMessageError"<?php endif; ?>><?= detoxE($old['message']) ?></textarea>
                    <?php if (!empty($errors['message'])): ?>
                        <p class="contact-error" id="contactMessageError"><?= detoxE($errors['message']) ?></p>
                    <?php endif; ?>
                </div>

                <button type="submit" class="contact-submit" id="contactSubmit">
                    <span>Send Message</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>
            </form>
        </div>

    </div>

    <?php if ($location['embed'] !== ''): ?>
        <!-- Location: map | text | photo -->
        <div class="container">
            <section class="contact-location<?= $location['image'] === '' ? ' contact-location--no-image' : '' ?>" aria-labelledby="contactLocationHeading">

                <div class="contact-location__map">
                    <iframe src="<?= detoxE($location['embed']) ?>"
                            title="Map showing our location"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen></iframe>
                </div>

                <div class="contact-location__body">
                    <p class="contact-eyebrow"><?= detoxE($locationEyebrow) ?></p>
                    <h2 class="contact-location__title" id="contactLocationHeading"><?= detoxE($location['heading']) ?></h2>
                    <p class="contact-location__text"><?= detoxE($location['text']) ?></p>

                    <?php if ($location['directions'] !== ''): ?>
                        <a class="contact-location__btn" href="<?= detoxE($location['directions']) ?>" target="_blank" rel="noopener noreferrer">
                            <span><?= detoxE($locationButton) ?></span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                    <?php endif; ?>
                </div>

                <?php if ($location['image'] !== ''): ?>
                    <div class="contact-location__photo">
                        <img src="<?= detoxE($location['image']) ?>" alt="<?= detoxE($locationImgAlt) ?>" loading="lazy">
                    </div>
                <?php endif; ?>

            </section>
        </div>
    <?php endif; ?>
</section>

<script>
    (function () {
        var form = document.getElementById('contactForm');
        var submit = document.getElementById('contactSubmit');
        var topic = document.getElementById('contactTopic');

        // Dropdown shows placeholder colour until an option is picked
        if (topic) {
            topic.addEventListener('change', function () {
                topic.classList.toggle('is-empty', topic.value === '');
            });
        }

        // Stop double-clicks from sending twice
        if (form && submit) {
            form.addEventListener('submit', function () {
                submit.disabled = true;
                submit.querySelector('span').textContent = 'Sending…';
            });
        }
    })();
</script>