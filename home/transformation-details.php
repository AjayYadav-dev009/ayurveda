<?php

/**
 * Public "Transformation Details" page.
 *
 * Reached as /transformation/{slug} via a rewrite rule (see the .htaccess
 * snippet in the accompanying notes) which maps to:
 *   transformation-details.php?slug={slug}
 * It also works directly as transformation-details.php?slug=... without
 * any rewrite, so this page functions either way.
 *
 * Adjust the three require paths below if this file doesn't sit at the
 * project root alongside includes/ and function/.
 */

require_once __DIR__ . '/function/transformation.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$transformation = $slug !== '' ? getTransformationBySlug($conn, $slug) : null;

// Unknown/inactive slug: send a real 404 rather than a blank page.
if (!$transformation) {
    http_response_code(404);
}

require_once __DIR__ . '/includes/header.php';

if (!$transformation): ?>

    <section class="trfd-empty">
        <div class="container">
            <h1>Transformation not found</h1>
            <p>This story may have been removed or the link is incorrect.</p>
            <a class="trfd-empty__link" href="/">Back to home</a>
        </div>
    </section>

<?php else:
    $beforeUrl = htmlspecialchars(BASE_URL . ltrim((string) ($transformation['before_image'] ?? ''), '/'), ENT_QUOTES, 'UTF-8');
    $afterUrl  = htmlspecialchars(BASE_URL . ltrim((string) ($transformation['after_image'] ?? ''), '/'), ENT_QUOTES, 'UTF-8');

    $name = trim((string) ($transformation['customer_name'] ?? ''));
    $safeName = htmlspecialchars($name !== '' ? $name : 'Customer', ENT_QUOTES, 'UTF-8');

    $description = trim((string) ($transformation['description'] ?? ''));
    $safeDescription = nl2br(htmlspecialchars($description, ENT_QUOTES, 'UTF-8'));

    $productName = trim((string) ($transformation['product_name'] ?? ''));
    $safeProductName = htmlspecialchars($productName, ENT_QUOTES, 'UTF-8');
    $productUrl = trim((string) ($transformation['product_url'] ?? ''));
    $safeProductUrl = $productUrl !== '' ? htmlspecialchars($productUrl, ENT_QUOTES, 'UTF-8') : '';

    $duration = trim((string) ($transformation['duration'] ?? ''));
    $safeDuration = htmlspecialchars($duration, ENT_QUOTES, 'UTF-8');

    $isVerified = !empty($transformation['is_verified']);

    $pageTitle = $safeName . ($safeProductName !== '' ? ' with ' . $safeProductName : '') . ' - Transformation Story';
?>

    <style>
        .trfd {
            --trfd-accent: var(--color-primary, #245c4f);
            padding: 60px 0 90px;
        }

        .trfd__back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 28px;
            font-size: 14px;
            font-weight: 600;
            color: var(--color-text-light);
            text-decoration: none;
        }

        .trfd__back:hover {
            color: var(--trfd-accent);
        }

        .trfd__back svg {
            width: 16px;
            height: 16px;
        }

        .trfd__layout {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 48px;
            align-items: start;
        }

        @media (max-width: 900px) {
            .trfd__layout {
                grid-template-columns: 1fr;
                gap: 32px;
            }
        }

        /* ---- Before/after photos ---- */

        .trfd__poster {
            display: flex;
            gap: 6px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-soft);
        }

        .trfd__photo {
            position: relative;
            flex: 1 1 50%;
        }

        .trfd__photo img {
            display: block;
            width: 100%;
            height: 100%;
            aspect-ratio: 3 / 4;
            object-fit: cover;
        }

        .trfd__badge {
            position: absolute;
            left: 12px;
            bottom: 12px;
            padding: 5px 12px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            color: #fff;
            background: rgba(20, 30, 25, 0.6);
            backdrop-filter: blur(3px);
            border-radius: 999px;
        }

        .trfd__badge--after {
            background: var(--trfd-accent);
        }

        /* ---- Story column ---- */

        .trfd__nameRow {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 6px;
        }

        .trfd__name {
            margin: 0;
            font-size: 32px;
            font-weight: 700;
            color: var(--color-text);
        }

        .trfd__verified {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--trfd-accent);
            background: var(--color-primary-light, rgba(36, 92, 79, 0.1));
            border-radius: 999px;
        }

        .trfd__verified svg {
            width: 13px;
            height: 13px;
        }

        .trfd__meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin-bottom: 26px;
            font-size: 15px;
            color: var(--color-text-light);
        }

        .trfd__product {
            font-weight: 600;
            color: var(--trfd-accent);
            text-decoration: none;
        }

        .trfd__product:hover {
            text-decoration: underline;
        }

        .trfd__metaDot {
            opacity: 0.5;
        }

        .trfd__story {
            font-size: 16px;
            line-height: 1.75;
            color: var(--color-text);
            margin-bottom: 32px;
        }

        .trfd__story p {
            margin: 0 0 16px;
        }

        .trfd__cta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 28px;
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            background: var(--trfd-accent);
            border-radius: var(--radius-md, 10px);
            text-decoration: none;
            transition: opacity 0.15s ease;
        }

        .trfd__cta:hover {
            opacity: 0.9;
        }

        .trfd__disclaimer {
            margin-top: 28px;
            font-size: 12px;
            line-height: 1.6;
            color: var(--color-text-light);
            opacity: 0.8;
        }

        /* ---- Not found state ---- */

        .trfd-empty {
            padding: 100px 0;
            text-align: center;
        }

        .trfd-empty__link {
            color: var(--color-primary, #245c4f);
            font-weight: 600;
        }
    </style>

    <section class="trfd">
        <div class="container">
            <a class="trfd__back" href="/">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M19 12H5M11 18l-6-6 6-6" />
                </svg>
                All transformations
            </a>

            <div class="trfd__layout">
                <div class="trfd__poster">
                    <div class="trfd__photo">
                        <img src="<?= $beforeUrl ?>" alt="<?= $safeName ?> before" />
                        <span class="trfd__badge trfd__badge--before">Before</span>
                    </div>
                    <div class="trfd__photo">
                        <img src="<?= $afterUrl ?>" alt="<?= $safeName ?> after" />
                        <span class="trfd__badge trfd__badge--after">After</span>
                    </div>
                </div>

                <div class="trfd__body">
                    <div class="trfd__nameRow">
                        <h1 class="trfd__name"><?= $safeName ?></h1>
                        <?php if ($isVerified): ?>
                            <span class="trfd__verified">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M20 6L9 17l-5-5" />
                                </svg>
                                Verified
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($safeProductName !== '' || $safeDuration !== ''): ?>
                        <div class="trfd__meta">
                            <?php if ($safeProductName !== ''): ?>
                                <?php if ($safeProductUrl !== ''): ?>
                                    <a class="trfd__product" href="<?= $safeProductUrl ?>"><?= $safeProductName ?></a>
                                <?php else: ?>
                                    <span class="trfd__product"><?= $safeProductName ?></span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($safeProductName !== '' && $safeDuration !== ''): ?>
                                <span class="trfd__metaDot" aria-hidden="true">&bull;</span>
                            <?php endif; ?>

                            <?php if ($safeDuration !== ''): ?>
                                <span class="trfd__duration"><?= $safeDuration ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($safeDescription !== ''): ?>
                        <div class="trfd__story">
                            <p><?= $safeDescription ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($safeProductUrl !== ''): ?>
                        <a class="trfd__cta" href="<?= $safeProductUrl ?>">
                            Shop <?= $safeProductName ?>
                        </a>
                    <?php endif; ?>

                    <p class="trfd__disclaimer">Results are individual to each customer and can vary based on body type, routine, and consistency. Shared with the customer's permission.</p>
                </div>
            </div>
        </div>
    </section>

<?php endif;

require_once __DIR__ . '/includes/footer.php';
