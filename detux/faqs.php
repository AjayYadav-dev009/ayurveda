<?php

/**
 * detux/faq-with-cta.php
 *
 * Alternative to faq.php: two-column layout — accordion list on the
 * left, a sticky "still have questions?" CTA card on the right,
 * instead of a single full-width list.
 *
 * Included from detux/index.php alongside the other detux sections.
 * No config/header/footer includes here — index.php already handles
 * those. Use this INSTEAD OF faq.php, not alongside it, unless you
 * actually want both.
 */

$faqHeading = 'Frequently Asked Questions';
$faqIntro   = 'Everything you need to know about the 10-day Complete Gut Detox';

$faqItems = [
    [
        'question' => 'What will I follow during the 10-Day Gut Reset?',
        'answer'   => 'You will follow a structured Ayurvedic daily routine — covering what to eat, which herbs to take, and simple wellness habits. Each day is guided, practical, and designed to build lasting digestive health.',
    ],
    [
        'question' => 'Will I be able to speak to an Ayurvedic expert during the reset?',
        'answer'   => 'Yes. Your plan includes consultations with a Maharishi Ayurveda Vaidya, including a check-in around Day 5 to review your progress and adjust your protocol if needed.',
    ],
    [
        'question' => 'How soon will I start seeing results?',
        'answer'   => 'Most people notice lighter digestion and steadier energy within the first 3-5 days, with fuller changes in skin, mood, and gut comfort building through Day 10.',
    ],
    [
        'question' => 'How much time does this take each day?',
        'answer'   => 'Around 20-30 minutes a day — enough to follow the guided video, take your herbal formulations, and complete that day\'s simple wellness habit.',
    ],
    [
        'question' => 'Are herbal supplements safe?',
        'answer'   => 'The kit uses authentic Maharishi Ayurveda formulations taken at guided doses. As with any supplement, speak with your doctor first if you\'re pregnant, nursing, or managing an existing medical condition.',
    ],
    [
        'question' => 'Do I need to know Ayurveda before starting?',
        'answer'   => 'No prior knowledge needed. Everything — diet guidelines, herbs, and daily habits — is explained step by step through your guided videos and Vaidya consultations.',
    ],
];

$faqCtaHeading = 'Still have questions?';
$faqCtaText    = "Our Ayurvedic team is happy to talk you through the programme before you commit — no obligation, just clarity on whether this reset is right for you.";
$faqCtaButtonLabel = 'Talk to an Expert';
$faqCtaButtonUrl   = rtrim(BASE_URL, '/') . '/contact/';
?>

<style>
    .detox-faq2 {
        padding: 8px 0 64px;
    }

    .detox-faq2__grid {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
        gap: 48px;
        align-items: start;
    }

    .detox-faq2__heading {
        font-size: 26px;
        color: var(--color-text);
    }

    .detox-faq2__intro {
        margin-top: 8px;
        font-size: 14px;
        color: var(--color-text-light);
    }

    .detox-faq2__list {
        margin-top: 24px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .detox-faq2__item {
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        background: var(--color-white);
        overflow: hidden;
    }

    .detox-faq2__question {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 18px;
        background: none;
        border: none;
        text-align: left;
        font-size: 14.5px;
        font-weight: 600;
        color: var(--color-text);
    }

    .detox-faq2__number {
        flex: 0 0 auto;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: var(--color-primary-light);
        color: var(--color-primary-dark);
        font-size: 12px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .detox-faq2__question-text {
        flex: 1 1 auto;
    }

    .detox-faq2__chevron {
        flex: 0 0 auto;
        width: 10px;
        height: 10px;
        border-right: 2px solid var(--color-text-light);
        border-bottom: 2px solid var(--color-text-light);
        transform: rotate(45deg);
        transition: transform 0.15s ease;
    }

    .detox-faq2__item.is-open .detox-faq2__chevron {
        transform: rotate(-135deg);
    }

    .detox-faq2__answer {
        display: none;
        padding: 0 18px 18px 58px;
        font-size: 13.5px;
        line-height: 1.7;
        color: var(--color-text-light);
    }

    .detox-faq2__item.is-open .detox-faq2__answer {
        display: block;
    }

    /* ---------------------------------------------------------------
       Side CTA card
       --------------------------------------------------------------- */
    .detox-faq2__cta {
        position: sticky;
        top: 24px;
        background: var(--color-primary-dark);
        color: var(--color-white);
        border-radius: var(--radius-lg);
        padding: 32px 28px;
    }

    .detox-faq2__cta-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .detox-faq2__cta-icon svg {
        width: 20px;
        height: 20px;
    }

    .detox-faq2__cta-heading {
        margin-top: 18px;
        font-size: 20px;
    }

    .detox-faq2__cta-text {
        margin-top: 10px;
        font-size: 13.5px;
        line-height: 1.7;
        color: rgba(255, 255, 255, 0.8);
    }

    .detox-faq2__cta-button {
        display: inline-block;
        margin-top: 22px;
        padding: 12px 22px;
        border-radius: 999px;
        background: var(--color-white);
        color: var(--color-primary-dark);
        font-size: 13.5px;
        font-weight: 600;
    }

    .detox-faq2__cta-button:hover {
        background: var(--color-accent);
        color: var(--color-white);
    }

    @media (max-width: 860px) {
        .detox-faq2__grid {
            grid-template-columns: 1fr;
            gap: 28px;
        }

        .detox-faq2__cta {
            position: static;
        }
    }
</style>

<section class="detox-faq2">
    <div class="container detox-faq2__grid">

        <div class="detox-faq2__main">
            <h2 class="detox-faq2__heading"><?= htmlspecialchars($faqHeading, ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="detox-faq2__intro"><?= htmlspecialchars($faqIntro, ENT_QUOTES, 'UTF-8') ?></p>

            <div class="detox-faq2__list">
                <?php foreach ($faqItems as $faqIndex => $faq): ?>
                    <div class="detox-faq2__item<?= $faqIndex === 0 ? ' is-open' : '' ?>">
                        <button type="button" class="detox-faq2__question">
                            <span class="detox-faq2__number"><?= $faqIndex + 1 ?></span>
                            <span class="detox-faq2__question-text"><?= htmlspecialchars($faq['question'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="detox-faq2__chevron" aria-hidden="true"></span>
                        </button>
                        <div class="detox-faq2__answer">
                            <?= htmlspecialchars($faq['answer'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="detox-faq2__cta">
            <div class="detox-faq2__cta-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 11.5a8.5 8.5 0 01-8.5 8.5c-1.3 0-2.5-.3-3.6-.8L3 20l1-5.7A8.5 8.5 0 1121 11.5z"/>
                </svg>
            </div>
            <h3 class="detox-faq2__cta-heading"><?= htmlspecialchars($faqCtaHeading, ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="detox-faq2__cta-text"><?= htmlspecialchars($faqCtaText, ENT_QUOTES, 'UTF-8') ?></p>
            <a class="detox-faq2__cta-button" href="<?= htmlspecialchars($faqCtaButtonUrl, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($faqCtaButtonLabel, ENT_QUOTES, 'UTF-8') ?>
            </a>
        </div>

    </div>
</section>

<script>
    (function () {
        var items = document.querySelectorAll('.detox-faq2__item');

        items.forEach(function (item) {
            var button = item.querySelector('.detox-faq2__question');
            button.addEventListener('click', function () {
                item.classList.toggle('is-open');
            });
        });
    })();
</script>