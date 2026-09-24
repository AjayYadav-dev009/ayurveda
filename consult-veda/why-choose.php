<?php

/**
 * "Why Choose Our Online Vaidya Consultation?" — consult-veda.
 *
 * Static content for now, same reasoning as condition.php: no dedicated
 * table for this in the schema, so it's a plain PHP array below rather than
 * a DB query, shaped so a future fetch could drop in without touching the
 * markup (title + description + icon key).
 *
 * Usage (from consult-veda/index.php, after $conn is available):
 *   include __DIR__ . '/why-choose-us.php';
 */

$whyEyebrow = 'Why Choose Us';
$whyHeading = 'Why Choose Our Online Vaidya Consultation?';
$whyIntro   = 'Authentic Ayurvedic expertise, modern convenience, and personalised care — all from the comfort of your home.';

// Photo on the left (mortar, pestle and herbs). Full URL, or a path under
// BASE_URL such as 'assets/images/why-choose.jpg'. Leave '' to hide it.
$whyImage = 'assets/images/veda.jpg';

$whyChooseUs = [
    [
        'title'       => 'Certified Practitioners',
        'description' => 'Experienced and trusted Ayurvedic doctors.',
        'icon'        => 'leaf',
    ],
    [
        'title'       => 'Personalised Approach',
        'description' => 'Tailored to your unique constitution and needs.',
        'icon'        => 'sprout',
    ],
    [
        'title'       => 'Safe & Confidential',
        'description' => 'Your privacy and health are our priority.',
        'icon'        => 'shield',
    ],
    [
        'title'       => 'Convenient & Flexible',
        'description' => 'Consult from anywhere, anytime.',
        'icon'        => 'clock',
    ],
];

// Icon artwork (24x24 outline paths). Trusted inline SVG markup, keyed by
// the `icon` value above. An unknown key falls back to 'leaf'.
$whyIcons = [
    'leaf'   => '<path d="M5 19c0-8 5-14 15-14 0 10-6 15-14 15z"/><path d="M5 19c4-5 7-8 11-10"/>',
    'sprout' => '<path d="M12 21v-9"/><path d="M12 12c0-3.2-2.2-5.2-5.5-5.2 0 3.2 2.2 5.2 5.5 5.2z"/><path d="M12 14.5c0-3.2 2.2-5.2 5.5-5.2 0 3.2-2.2 5.2-5.5 5.2z"/><path d="M8 21h8"/>',
    'shield' => '<path d="M12 3 4.5 6v5.5c0 4.5 3.2 8.2 7.5 9.5 4.3-1.3 7.5-5 7.5-9.5V6L12 3z"/><path d="m8.8 12.2 2.3 2.3 4.1-4.4"/>',
    'clock'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
];

if (!function_exists('whyE')) {
    function whyE($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if ($whyImage !== '' && !preg_match('~^(https?:)?//~i', $whyImage) && $whyImage[0] !== '/') {
    $whyImage = rtrim(defined('BASE_URL') ? (string) BASE_URL : '', '/') . '/' . ltrim($whyImage, '/');
}
?>

<style>
    .why-us {
        --why-primary: var(--color-primary, #1f3a32);
        --why-accent-text: #82631a;
        --why-text: var(--color-text, #26342f);
        --why-muted: #566059;
        --why-heading-font: var(--font-heading, "Cormorant Garamond", "Playfair Display", Georgia, "Times New Roman", serif);
        position: relative;
        overflow: hidden;
        display: grid;
        grid-template-columns: minmax(0, 32%) minmax(0, 1fr);
        min-height: clamp(380px, 32vw, 520px);
        background: var(--color-primary-light, #edf1e8);
    }

    .why-us--no-image {
        grid-template-columns: minmax(0, 1fr);
    }

    .why-us-sprite {
        position: absolute;
        width: 0;
        height: 0;
        overflow: hidden;
    }

    .why-us-leaf {
        position: absolute;
        z-index: 0;
        right: -14px;
        top: 38%;
        width: clamp(80px, 8vw, 130px);
        color: var(--why-primary);
        opacity: 0.12;
        transform: rotate(24deg);
        pointer-events: none;
    }

    /* ---- Photo, fading into the panel on its right edge ------------------- */
    .why-us__media {
        position: relative;
    }

    .why-us__media img {
        position: absolute;
        inset: 0;
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        -webkit-mask-image: linear-gradient(90deg, #000 55%, transparent 100%);
        mask-image: linear-gradient(90deg, #000 55%, transparent 100%);
    }

    /* ---- Content -------------------------------------------------------- */
    .why-us__content {
        position: relative;
        z-index: 1;
        align-self: center;
        max-width: 48rem;
        padding: clamp(40px, 5vw, 72px) 24px clamp(40px, 5vw, 72px) clamp(16px, 3vw, 48px);
    }

    .why-us--no-image .why-us__content {
        max-width: var(--container-width, 1200px);
        width: 100%;
        margin: 0 auto;
        padding-left: 24px;
    }

    .why-us__eyebrow {
        margin: 0;
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--why-accent-text);
    }

    .why-us__heading {
        margin: 12px 0 0;
        max-width: 13em;
        font-family: var(--why-heading-font);
        font-size: clamp(2rem, 3.3vw, 2.9rem);
        font-weight: 700;
        line-height: 1.12;
        letter-spacing: -0.01em;
        text-wrap: balance;
        color: var(--why-primary);
    }

    .why-us__intro {
        margin: 16px 0 0;
        max-width: 46ch;
        font-size: clamp(0.95rem, 1.1vw, 1.02rem);
        line-height: 1.65;
        color: var(--why-text);
        opacity: 0.85;
    }

    .why-us__list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: clamp(22px, 2.6vw, 34px) clamp(24px, 4vw, 56px);
        margin: clamp(28px, 3.4vw, 40px) 0 0;
        padding: 0;
        list-style: none;
    }

    .why-us__item {
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr);
        column-gap: 16px;
        align-items: start;
    }

    .why-us__icon {
        grid-row: span 2;
        display: block;
        width: 34px;
        height: 34px;
        fill: none;
        stroke: var(--why-primary);
        stroke-width: 1.4;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .why-us__title {
        margin: 0;
        font-size: 0.98rem;
        font-weight: 700;
        line-height: 1.3;
        color: var(--why-primary);
    }

    .why-us__text {
        margin: 4px 0 0;
        font-size: 0.86rem;
        line-height: 1.55;
        color: var(--why-muted);
    }

    /* ---- Tablet / mobile: photo becomes a top band ------------------------ */
    @media (max-width: 899px) {
        .why-us {
            grid-template-columns: minmax(0, 1fr);
            min-height: 0;
        }

        .why-us__media {
            height: clamp(180px, 46vw, 300px);
        }

        .why-us__media img {
            -webkit-mask-image: linear-gradient(180deg, #000 55%, transparent 100%);
            mask-image: linear-gradient(180deg, #000 55%, transparent 100%);
        }

        .why-us__content,
        .why-us--no-image .why-us__content {
            max-width: none;
            padding: 8px 24px 44px;
        }

        .why-us--no-image .why-us__content {
            padding-top: 44px;
        }
    }

    @media (max-width: 559px) {
        .why-us__list {
            grid-template-columns: minmax(0, 1fr);
        }
    }
</style>

<section class="why-us<?= $whyImage === '' ? ' why-us--no-image' : '' ?>" aria-labelledby="whyUsHeading">
    <svg class="why-us-sprite" aria-hidden="true" focusable="false">
        <symbol id="why-us-sprig" viewBox="0 0 120 200">
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
    </svg>
    <svg class="why-us-leaf" viewBox="0 0 120 200" aria-hidden="true">
        <use href="#why-us-sprig" />
    </svg>

    <?php if ($whyImage !== ''): ?>
        <div class="why-us__media">
            <img src="<?= whyE($whyImage) ?>" alt="" loading="lazy">
        </div>
    <?php endif; ?>

    <div class="why-us__content">
        <p class="why-us__eyebrow"><?= whyE($whyEyebrow) ?></p>
        <h2 class="why-us__heading" id="whyUsHeading"><?= whyE($whyHeading) ?></h2>
        <p class="why-us__intro"><?= whyE($whyIntro) ?></p>

        <ul class="why-us__list">
            <?php foreach ($whyChooseUs as $item):
                $iconSvg = $whyIcons[$item['icon'] ?? 'leaf'] ?? $whyIcons['leaf'];
            ?>
                <li class="why-us__item">
                    <svg class="why-us__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><?= $iconSvg ?></svg>
                    <h3 class="why-us__title"><?= whyE($item['title']) ?></h3>
                    <p class="why-us__text"><?= whyE($item['description']) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>