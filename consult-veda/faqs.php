<?php


$faqHeading = 'Frequently Asked Questions';
$faqIntro   = 'Everything you need to know about online Vaidya consultation';

$faqItems = [
    [
        'question' => 'How does the online Vaidya consultation work?',
        'answer'   => 'You book a slot, share your health concerns and history, and speak with an experienced Ayurvedic Vaidya online. The Vaidya assesses your concerns, then you receive a personalised plan with recommendations for diet, lifestyle and treatment.',
    ],
    [
        'question' => 'What can I expect in the 30-minute consultation?',
        'answer'   => 'A personalised health assessment, a dosha analysis to identify your imbalances, basic dietary and lifestyle tips, and time to ask the Vaidya your questions.',
    ],
    [
        'question' => 'Which health concerns can the Vaidyas help with?',
        'answer'   => 'Our Vaidyas consult on digestive issues, skin and hair care, hormonal balance, weight management, stress and anxiety, immunity, joint and muscle pain, and chronic conditions such as diabetes and hypertension. The focus is on finding and treating the root cause. In a medical emergency, please seek immediate care.',
    ],
    [
        'question' => 'What will I receive after my consultation?',
        'answer'   => "After the consultation you'll receive a valid prescription with all the recommendations. You can also avail free follow-up for up to 30 days.",
    ],
    [
        'question' => 'What is included in the 7-day personalised diet plan?',
        'answer'   => 'A customised 7-day meal plan built around your dosha, with dosha-specific food recommendations, recipes and a shopping list. It also includes a 15-minute follow-up check-in.',
    ],
    [
        'question' => 'Is my personal and health information kept confidential?',
        'answer'   => 'Yes. Your privacy is our priority, and your consultation is kept completely confidential.',
    ],
];

$faqCtaHeading = 'Still have questions?';
$faqCtaText    = "Not sure if online consultation is right for you? Book a slot with one of our Ayurvedic experts and get your questions answered.";
$faqCtaButtonLabel = 'Book a Consultation';
$faqCtaButtonUrl   = '#book-now';
?>

<style>
    .detox-faq2 {
        padding: 40px 0 64px;
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