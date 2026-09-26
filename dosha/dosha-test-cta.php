<?php

/**
 * dosha-test-cta.php
 *
 * "Take the Ayurvedic Dosha Test" CTA banner + the premium two-column modal
 * form it opens. Include this from any page (e.g. index.php) the same way
 * hero.php / our-story.php are included.
 *
 * Requires a session (for the CSRF token) — adjust the require_once path
 * below if this file lives at a different folder depth than includes/.
 *
 * Submits to ajax/dosha-submit.php via fetch(); see that file and
 * function/dosha.php for the validation/save logic, and
 * sql/dosha-test-setup.sql for the `dosha_leads` table this depends on.
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../function/csrf.php';

$doshaCsrfToken = generateCSRFToken();
$doshaSubmitUrl = (defined('BASE_URL') ? BASE_URL : '/') . 'ajax/dosha-submit.php';

$doshaGenderOptions = ['Female', 'Male', 'Other', 'Prefer not to say'];
$doshaGoalOptions   = [
    'Weight Management', 'Better Sleep', 'Stress Relief', 'Digestive Health',
    'Skin & Hair Health', 'Increased Energy', 'Chronic Pain Relief', 'General Wellness',
];

if (!function_exists('doshaE')) {
    function doshaE($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

include __DIR__ . '/../includes/header.php';
?>

<style>
    /* ==========================================================================
       Dosha Test CTA banner + modal.
       Uses the same --color-* / --font-heading tokens as the other sections.
       ========================================================================== */

    .dosha-cta {
        --dosha-primary: var(--color-primary, #1f3a32);
        --dosha-primary-dark: var(--color-primary-dark, #142a24);
        --dosha-primary-light: var(--color-primary-light, #edf1e8);
        --dosha-accent: var(--color-accent, #b28a32);
        --dosha-accent-strong: #94701f;
        --dosha-bg: var(--color-bg, #f8f6ef);
        --dosha-text: var(--color-text, #26342f);
        --dosha-muted: var(--color-text-light, #6f776f);
        --dosha-heading-font: var(--font-heading, "Cormorant Garamond", "Playfair Display", Georgia, "Times New Roman", serif);
        position: relative;
        overflow: hidden;
        background: var(--dosha-bg);
        padding: clamp(64px, 9vw, 112px) clamp(24px, 3vw, 48px);
        text-align: center;
    }

    .dosha-cta__leaf {
        position: absolute;
        z-index: 0;
        color: var(--dosha-primary);
        opacity: 0.1;
        pointer-events: none;
    }

    .dosha-cta__leaf--tl {
        top: -10px;
        left: -20px;
        width: clamp(90px, 9vw, 150px);
        transform: rotate(-20deg);
    }

    .dosha-cta__leaf--br {
        right: -16px;
        bottom: -20px;
        width: clamp(90px, 9vw, 150px);
        transform: rotate(24deg) scaleX(-1);
    }

    .dosha-cta__inner {
        position: relative;
        z-index: 1;
        max-width: 680px;
        margin: 0 auto;
    }

    .dosha-cta__mark {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
        color: var(--dosha-accent);
    }

    .dosha-cta__mark i {
        display: block;
        width: clamp(32px, 5vw, 56px);
        height: 1px;
        background: rgba(178, 138, 50, 0.4);
    }

    .dosha-cta__mark svg {
        width: 28px;
        height: 28px;
    }

    .dosha-cta__eyebrow {
        margin: 14px 0 0;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        color: var(--dosha-primary);
        opacity: 0.8;
    }

    .dosha-cta__title {
        margin: 10px 0 0;
        font-family: var(--dosha-heading-font);
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 700;
        line-height: 1.15;
        color: var(--dosha-primary);
    }

    .dosha-cta__text {
        margin: 16px auto 0;
        max-width: 44ch;
        font-size: clamp(0.95rem, 1.1vw, 1.05rem);
        line-height: 1.65;
        color: var(--dosha-text);
        opacity: 0.85;
    }

    .dosha-cta__btn {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        margin-top: 30px;
        padding: 16px 32px;
        border: 0;
        border-radius: 999px;
        background: var(--dosha-primary-dark);
        color: #ffffff;
        font-size: 0.92rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        cursor: pointer;
        transition: background-color 0.25s ease;
    }

    .dosha-cta__btn svg {
        width: 17px;
        height: 17px;
        flex: 0 0 auto;
    }

    .dosha-cta__btn:hover {
        background: #0e211c;
        color: #b28a32;
    }

    .dosha-cta__btn:focus-visible {
        outline: 3px solid var(--dosha-accent);
        outline-offset: 3px;
    }

    /* ==========================================================================
       Modal
       ========================================================================== */

    .dosha-modal {
        position: fixed;
        inset: 0;
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .dosha-modal.is-open {
        display: flex;
    }

    .dosha-modal__overlay {
        position: absolute;
        inset: 0;
        background: rgb(255 255 255 / 88%);
        backdrop-filter: blur(2px);
    }

    .dosha-modal__panel {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 920px;
        max-height: 90vh;
        overflow-y: auto;
        display: grid;
        grid-template-columns: minmax(0, 0.85fr) minmax(0, 1.15fr);
        border-radius: 22px;
        background: var(--dosha-bg);
        box-shadow: 0 30px 70px rgba(15, 30, 26, 0.35);
    }

    .dosha-modal__close {
        position: absolute;
        top: 16px;
        right: 16px;
        z-index: 2;
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 50%;
        background: rgba(31, 58, 50, 0.06);
        color: var(--dosha-primary);
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .dosha-modal__close:hover {
        background: rgba(31, 58, 50, 0.12);
    }

    .dosha-modal__close svg {
        width: 16px;
        height: 16px;
    }

    /* ---- Left info panel ------------------------------------------------------ */
    .dosha-modal__intro {
        position: relative;
        overflow: hidden;
        background: var(--dosha-primary-light);
        padding: clamp(28px, 3.5vw, 44px) clamp(24px, 3vw, 36px);
    }

    .dosha-modal__intro-leaf {
        position: absolute;
        left: -14px;
        bottom: -10px;
        width: 120px;
        color: var(--dosha-primary);
        opacity: 0.14;
        pointer-events: none;
    }

    .dosha-modal__logo {
        width: 26px;
        height: 26px;
        color: var(--dosha-accent);
    }

    .dosha-modal__eyebrow {
        margin: 10px 0 0;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: var(--dosha-muted);
    }

    .dosha-modal__heading {
        margin: 10px 0 0;
        font-family: var(--dosha-heading-font);
        font-size: clamp(1.5rem, 2.4vw, 1.9rem);
        font-weight: 700;
        line-height: 1.15;
        color: var(--dosha-primary);
    }

    .dosha-modal__intro-text {
        margin: 12px 0 0;
        max-width: 32ch;
        font-size: 0.88rem;
        line-height: 1.6;
        color: var(--dosha-text);
        opacity: 0.85;
    }

    .dosha-modal__benefits {
        position: relative;
        z-index: 1;
        margin: 24px 0 0;
        padding: 0;
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .dosha-modal__benefit {
        display: grid;
        grid-template-columns: 38px minmax(0, 1fr);
        gap: 12px;
        align-items: start;
    }

    .dosha-modal__benefit-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(31, 58, 50, 0.08);
        color: var(--dosha-primary);
    }

    .dosha-modal__benefit-icon svg {
        width: 18px;
        height: 18px;
    }

    .dosha-modal__benefit-title {
        margin: 0;
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--dosha-primary);
    }

    .dosha-modal__benefit-text {
        margin: 3px 0 0;
        font-size: 0.8rem;
        line-height: 1.5;
        color: var(--dosha-muted);
    }

    .dosha-modal__tagline {
        position: relative;
        z-index: 1;
        margin: 30px 0 0;
        font-family: var(--dosha-heading-font);
        font-style: italic;
        font-size: 1.15rem;
        color: var(--dosha-primary);
    }

    .dosha-modal__tagline-rule {
        display: block;
        width: 46px;
        height: 2px;
        margin-top: 8px;
        background: var(--dosha-accent);
    }

    /* ---- Right form panel ------------------------------------------------------ */
    .dosha-modal__form-panel {
        padding: clamp(28px, 3.5vw, 44px) clamp(24px, 3vw, 40px);
    }

    .dosha-modal__form-heading {
        margin: 0;
        font-family: var(--dosha-heading-font);
        font-size: clamp(1.4rem, 2.2vw, 1.7rem);
        font-weight: 700;
        color: var(--dosha-primary);
    }

    .dosha-modal__form-sub {
        margin: 6px 0 0;
        font-size: 0.88rem;
        color: var(--dosha-muted);
    }

    .dosha-modal__banner {
        display: none;
        margin: 16px 0 0;
        padding: 10px 14px;
        border-radius: 10px;
        background: #fbe9e7;
        color: #9c3b2e;
        font-size: 0.85rem;
    }

    .dosha-modal__banner.is-visible {
        display: block;
    }

    .dosha-modal__grid {
        margin: 20px 0 0;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .dosha-field {
        grid-column: span 1;
        display: flex;
        flex-direction: column;
    }

    .dosha-field--full {
        grid-column: 1 / -1;
    }

    .dosha-field label {
        margin: 0 0 6px;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--dosha-primary);
    }

    .dosha-field label .req {
        color: var(--dosha-accent-strong);
    }

    .dosha-field__control {
        position: relative;
    }

    .dosha-field__control svg {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        color: var(--dosha-muted);
        pointer-events: none;
    }

    .dosha-field input,
    .dosha-field select {
        width: 100%;
        padding: 11px 14px 11px 38px;
        border: 1px solid var(--color-border, #ded9c9);
        border-radius: 10px;
        background: #ffffff;
        font-size: 0.88rem;
        color: var(--dosha-text);
        font-family: inherit;
    }

    .dosha-field select {
        appearance: none;
        background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236f776f' stroke-width='2'><path d='M6 9l6 6 6-6'/></svg>");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 14px;
    }

    .dosha-field input::placeholder {
        color: #a8ac9f;
    }

    .dosha-field input:focus,
    .dosha-field select:focus {
        outline: none;
        border-color: var(--dosha-primary);
        box-shadow: 0 0 0 3px rgba(31, 58, 50, 0.1);
    }

    .dosha-field.has-error input,
    .dosha-field.has-error select {
        border-color: #c0392b;
    }

    .dosha-field__error {
        display: none;
        margin: 5px 0 0;
        font-size: 0.75rem;
        color: #c0392b;
    }

    .dosha-field.has-error .dosha-field__error {
        display: block;
    }

    /* Honeypot — visually hidden but present in the DOM/tab order-safe way. */
    .dosha-hp {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip: rect(0 0 0 0);
        white-space: nowrap;
    }

    .dosha-modal__submit {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 22px;
        padding: 15px 20px;
        border: 0;
        border-radius: 999px;
        background: #142a24;
        color: #ffffff;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.25s ease, opacity 0.2s ease;
    }

    .dosha-modal__submit svg {
        width: 16px;
        height: 16px;
        transition: transform 0.25s ease;
    }

    .dosha-modal__submit:hover {
        background: #0e211c;
        color: var(--dosha-accent-strong);
    }

    .dosha-modal__submit:hover svg {
        transform: translateX(3px);
    }

    .dosha-modal__submit:disabled {
        opacity: 0.65;
        cursor: default;
    }

    .dosha-modal__privacy {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin: 12px 0 0;
        font-size: 0.76rem;
        color: var(--dosha-muted);
    }

    .dosha-modal__privacy svg {
        width: 13px;
        height: 13px;
    }

    /* ---- Tablet / mobile -------------------------------------------------------- */
    @media (max-width: 819px) {
        .dosha-modal__panel {
            grid-template-columns: minmax(0, 1fr);
        }

        .dosha-modal__intro {
            padding-bottom: 28px;
        }
    }

    @media (max-width: 480px) {
        .dosha-modal__grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .dosha-field {
            grid-column: 1 / -1;
        }
    }
</style>

<section class="dosha-cta" aria-labelledby="doshaCtaHeading">
    <svg class="dosha-cta__leaf dosha-cta__leaf--tl" viewBox="0 0 120 200" aria-hidden="true">
        <path d="M60 198C54 140 66 80 60 8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
        <g fill="currentColor">
            <ellipse cx="86" cy="150" rx="26" ry="10" transform="rotate(-38 86 150)" />
            <ellipse cx="34" cy="126" rx="26" ry="10" transform="rotate(34 34 126)" />
            <ellipse cx="88" cy="100" rx="25" ry="9.5" transform="rotate(-34 88 100)" />
        </g>
    </svg>
    <svg class="dosha-cta__leaf dosha-cta__leaf--br" viewBox="0 0 120 200" aria-hidden="true">
        <path d="M60 198C54 140 66 80 60 8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
        <g fill="currentColor">
            <ellipse cx="86" cy="150" rx="26" ry="10" transform="rotate(-38 86 150)" />
            <ellipse cx="34" cy="126" rx="26" ry="10" transform="rotate(34 34 126)" />
            <ellipse cx="88" cy="100" rx="25" ry="9.5" transform="rotate(-34 88 100)" />
        </g>
    </svg>

    <div class="dosha-cta__inner">
        <div class="dosha-cta__mark" aria-hidden="true">
            <i></i>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 21c0-5 2-8 2-8s2 3 2 8" />
                <path d="M12 21c0-5-2-8-2-8s-2 3-2 8" />
                <path d="M12 13c-3 0-5-2-5-5 3 0 5 2 5 5Z" />
                <path d="M12 13c3 0 5-2 5-5-3 0-5 2-5 5Z" />
            </svg>
            <i></i>
        </div>
        <p class="dosha-cta__eyebrow">Discover Your Dosha</p>
        <h2 class="dosha-cta__title" id="doshaCtaHeading">Take the Ayurvedic<br>Dosha Test</h2>
        <p class="dosha-cta__text">Understand your unique mind-body constitution and unlock a healthier, more balanced you.</p>

        <button type="button" class="dosha-cta__btn" id="doshaOpenBtn" aria-haspopup="dialog" aria-controls="doshaModal">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M5 19c0-8 5-14 15-14 0 10-6 15-14 15z" />
                <path d="M5 19c4-5 7-8 11-10" />
            </svg>
            <span>Take the Dosha Test</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
        </button>
    </div>
</section>

<div class="dosha-modal" id="doshaModal" role="dialog" aria-modal="true" aria-labelledby="doshaModalHeading">
    <div class="dosha-modal__overlay" data-dosha-close></div>

    <div class="dosha-modal__panel">
        <button type="button" class="dosha-modal__close" data-dosha-close aria-label="Close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18" /></svg>
        </button>

        <div class="dosha-modal__intro">
            <svg class="dosha-modal__intro-leaf" viewBox="0 0 120 200" aria-hidden="true">
                <path d="M60 198C54 140 66 80 60 8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                <g fill="currentColor">
                    <ellipse cx="86" cy="150" rx="26" ry="10" transform="rotate(-38 86 150)" />
                    <ellipse cx="34" cy="126" rx="26" ry="10" transform="rotate(34 34 126)" />
                    <ellipse cx="88" cy="100" rx="25" ry="9.5" transform="rotate(-34 88 100)" />
                </g>
            </svg>

            <svg class="dosha-modal__logo" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 21c0-5 2-8 2-8s2 3 2 8" />
                <path d="M12 21c0-5-2-8-2-8s-2 3-2 8" />
                <path d="M12 13c-3 0-5-2-5-5 3 0 5 2 5 5Z" />
                <path d="M12 13c3 0 5-2 5-5-3 0-5 2-5 5Z" />
            </svg>
            <p class="dosha-modal__eyebrow">Ayurveda Dosha Test</p>
            <h2 class="dosha-modal__heading" id="doshaModalHeading">Know Your Unique Dosha</h2>
            <p class="dosha-modal__intro-text">Discover your body-mind balance with our simple Ayurvedic dosha test and get personalized wellness insights.</p>

            <ul class="dosha-modal__benefits">
                <li class="dosha-modal__benefit">
                    <span class="dosha-modal__benefit-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 19c9 0 13-6 13-14-8 0-13 4-13 12-1.3-.3-2.3-1-3-2" /></svg>
                    </span>
                    <span>
                        <p class="dosha-modal__benefit-title">Understand Your Constitution</p>
                        <p class="dosha-modal__benefit-text">Identify your Vata, Pitta or Kapha balance.</p>
                    </span>
                </li>
                <li class="dosha-modal__benefit">
                    <span class="dosha-modal__benefit-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21c0-5 2-8 2-8s2 3 2 8" /><path d="M12 21c0-5-2-8-2-8s-2 3-2 8" /><path d="M12 13c-3 0-5-2-5-5 3 0 5 2 5 5Z" /><path d="M12 13c3 0 5-2 5-5-3 0-5 2-5 5Z" /></svg>
                    </span>
                    <span>
                        <p class="dosha-modal__benefit-title">Personalized Guidance</p>
                        <p class="dosha-modal__benefit-text">Get tailored recommendations for better health.</p>
                    </span>
                </li>
                <li class="dosha-modal__benefit">
                    <span class="dosha-modal__benefit-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20s-7-4.35-7-9.8A4.2 4.2 0 0 1 12 7.4a4.2 4.2 0 0 1 7 2.8c0 5.45-7 9.8-7 9.8Z" /></svg>
                    </span>
                    <span>
                        <p class="dosha-modal__benefit-title">Live in Harmony</p>
                        <p class="dosha-modal__benefit-text">Align your lifestyle with your natural dosha.</p>
                    </span>
                </li>
            </ul>

            <p class="dosha-modal__tagline">
                Your Wellness<br>Starts Here
                <span class="dosha-modal__tagline-rule" aria-hidden="true"></span>
            </p>
        </div>

        <div class="dosha-modal__form-panel">
            <h3 class="dosha-modal__form-heading">Fill in Your Details</h3>
            <p class="dosha-modal__form-sub">Just a few simple questions to get started.</p>

            <div class="dosha-modal__banner" id="doshaFormBanner" role="alert"></div>

            <form id="doshaForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?= doshaE($doshaCsrfToken) ?>">
                <div class="dosha-hp" aria-hidden="true">
                    <label for="doshaHpCheck">Leave this field empty</label>
                    <input type="text" id="doshaHpCheck" name="hp_check" tabindex="-1" autocomplete="off">
                </div>

                <div class="dosha-modal__grid">
                    <div class="dosha-field" data-field="full_name">
                        <label for="doshaFullName">Full Name <span class="req">*</span></label>
                        <div class="dosha-field__control">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="3.5" /><path d="M5 20c1.5-4 4-6 7-6s5.5 2 7 6" /></svg>
                            <input type="text" id="doshaFullName" name="full_name" placeholder="Enter your name" required maxlength="150">
                        </div>
                        <p class="dosha-field__error"></p>
                    </div>

                    <div class="dosha-field" data-field="email">
                        <label for="doshaEmail">Email Address <span class="req">*</span></label>
                        <div class="dosha-field__control">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" /><path d="m4 7 8 6 8-6" /></svg>
                            <input type="email" id="doshaEmail" name="email" placeholder="Enter your email" required maxlength="191">
                        </div>
                        <p class="dosha-field__error"></p>
                    </div>

                    <div class="dosha-field" data-field="date_of_birth">
                        <label for="doshaDob">Date of Birth <span class="req">*</span></label>
                        <div class="dosha-field__control">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2" /><path d="M3 10h18M8 3v4M16 3v4" /></svg>
                            <input type="date" id="doshaDob" name="date_of_birth" required>
                        </div>
                        <p class="dosha-field__error"></p>
                    </div>

                    <div class="dosha-field" data-field="gender">
                        <label for="doshaGender">Gender <span class="req">*</span></label>
                        <div class="dosha-field__control">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="3.5" /><path d="M5 20c1.5-4 4-6 7-6s5.5 2 7 6" /></svg>
                            <select id="doshaGender" name="gender" required>
                                <option value="" disabled selected>Select gender</option>
                                <?php foreach ($doshaGenderOptions as $option): ?>
                                    <option value="<?= doshaE($option) ?>"><?= doshaE($option) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <p class="dosha-field__error"></p>
                    </div>

                    <div class="dosha-field dosha-field--full" data-field="mobile">
                        <label for="doshaMobile">Mobile Number <span class="req">*</span></label>
                        <div class="dosha-field__control">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h4l2 5-2.5 1.5a11 11 0 0 0 5 5L16 12l5 2v4a2 2 0 0 1-2 2C10.5 20 4 13.5 4 5a2 2 0 0 1 2-2Z" /></svg>
                            <input type="tel" id="doshaMobile" name="mobile" placeholder="Enter your mobile number" required maxlength="20">
                        </div>
                        <p class="dosha-field__error"></p>
                    </div>

                    <div class="dosha-field dosha-field--full" data-field="location">
                        <label for="doshaLocation">Current Location <span class="req">*</span></label>
                        <div class="dosha-field__control">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-6.5 7-11.5A7 7 0 0 0 5 9.5C5 14.5 12 21 12 21Z" /><circle cx="12" cy="9.5" r="2.3" /></svg>
                            <input type="text" id="doshaLocation" name="location" placeholder="Enter your city or state" required maxlength="150">
                        </div>
                        <p class="dosha-field__error"></p>
                    </div>

                    <div class="dosha-field dosha-field--full" data-field="wellness_goal">
                        <label for="doshaGoal">What are your wellness goals? (Optional)</label>
                        <div class="dosha-field__control">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 19c9 0 13-6 13-14-8 0-13 4-13 12-1.3-.3-2.3-1-3-2" /></svg>
                            <input type="text" id="doshaGoal" name="wellness_goal" list="doshaGoalList" placeholder="Select or type your goal" maxlength="150">
                            <datalist id="doshaGoalList">
                                <?php foreach ($doshaGoalOptions as $goal): ?>
                                    <option value="<?= doshaE($goal) ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <p class="dosha-field__error"></p>
                    </div>
                </div>

                <button type="submit" class="dosha-modal__submit" id="doshaSubmitBtn">
                    <span>Start My Dosha Test</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                </button>

                <p class="dosha-modal__privacy">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="10" width="16" height="10" rx="2" /><path d="M8 10V7a4 4 0 0 1 8 0v3" /></svg>
                    Your information is safe and secure
                </p>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    var SUBMIT_URL = <?= json_encode($doshaSubmitUrl) ?>;

    var modal     = document.getElementById('doshaModal');
    var openBtn   = document.getElementById('doshaOpenBtn');
    var form      = document.getElementById('doshaForm');
    var submitBtn = document.getElementById('doshaSubmitBtn');
    var banner    = document.getElementById('doshaFormBanner');
    var lastFocused = null;

    function openModal() {
        lastFocused = document.activeElement;
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        var firstField = document.getElementById('doshaFullName');
        if (firstField) { firstField.focus(); }
    }

    function closeModal() {
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
        if (lastFocused) { lastFocused.focus(); }
    }

    openBtn.addEventListener('click', openModal);

    modal.querySelectorAll('[data-dosha-close]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });

    function clearErrors() {
        banner.classList.remove('is-visible');
        banner.textContent = '';
        form.querySelectorAll('.dosha-field.has-error').forEach(function (field) {
            field.classList.remove('has-error');
            var msg = field.querySelector('.dosha-field__error');
            if (msg) { msg.textContent = ''; }
        });
    }

    function showErrors(errors) {
        Object.keys(errors || {}).forEach(function (key) {
            if (key === 'form') {
                banner.textContent = errors[key];
                banner.classList.add('is-visible');
                return;
            }
            var field = form.querySelector('.dosha-field[data-field="' + key + '"]');
            if (field) {
                field.classList.add('has-error');
                var msg = field.querySelector('.dosha-field__error');
                if (msg) { msg.textContent = errors[key]; }
            }
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();

        submitBtn.disabled = true;
        var originalLabel = submitBtn.querySelector('span').textContent;
        submitBtn.querySelector('span').textContent = 'Starting your test…';

        fetch(SUBMIT_URL, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (res) {
                return res.json().then(function (data) { return { status: res.status, data: data }; });
            })
            .then(function (result) {
                if (result.data && result.data.success) {
                    window.location.href = result.data.redirect;
                    return;
                }
                showErrors(result.data ? result.data.errors : { form: 'Something went wrong. Please try again.' });
                submitBtn.disabled = false;
                submitBtn.querySelector('span').textContent = originalLabel;
            })
            .catch(function () {
                showErrors({ form: 'Network error. Please check your connection and try again.' });
                submitBtn.disabled = false;
                submitBtn.querySelector('span').textContent = originalLabel;
            });
    });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>