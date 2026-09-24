<?php


if (!function_exists('getAllCategoriesWithProducts')) {
    require_once __DIR__ . '/function/category.php';
}

try {
    // Existing query: only categories that actually contain products.
    $shopCategories = getAllCategoriesWithProducts($conn);
} catch (Exception $e) {
    $shopCategories = [];
}

$shopCategoryCount = count($shopCategories);

// Line icons shown in the small circle on each card. Picked by keywords in the
// category name/slug (no IDs), otherwise rotated so cards still feel varied.
$sbcIcons = [
    'leaf'  => '<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M16 28V13"/><path d="M16 17c-5 0-8-3-8-8 5 0 8 3 8 8Z"/><path d="M16 13c0-5 3-8 8-8 0 5-3 8-8 8Z"/><path d="M16 23c-4 0-6-2-6-6 4 0 6 2 6 6Z"/><path d="M16 23c0-4 2-6 6-6 0 4-2 6-6 6Z"/></svg>',
    'face'  => '<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4c-5 0-8 4-8 9v6c0 4 2 7 4 9"/><path d="M16 4c5 0 8 4 8 9v6c0 4-2 7-4 9"/><path d="M12 12c3 0 5-2 6-4 1 2 2 4 3 4"/><path d="M13 15c0 2 1.5 4 3 4s3-2 3-4"/><path d="M11 26c2 1.5 8 1.5 10 0"/></svg>',
    'body'  => '<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5c1 5 0 8-2 11l2 11"/><path d="M23 5c-1 5 0 8 2 11l-2 11"/><path d="M9 16c4 2 10 2 14 0"/><path d="M11 11c3 1.5 7 1.5 10 0"/><path d="M12 22c2.5 1 5.5 1 8 0"/></svg>',
    'lotus' => '<svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M16 6c3 3 4.5 6 4.5 9S19 21 16 23c-3-2-4.5-5-4.5-8S13 9 16 6Z"/><path d="M16 23c-6 0-10-3-11-8 4 0 8 1.5 10 5"/><path d="M16 23c6 0 10-3 11-8-4 0-8 1.5-10 5"/><path d="M8 27h16"/></svg>',
];
$sbcIconKeys = array_keys($sbcIcons);

function sbcPickIcon($name, $slug, $index, $icons, $keys)
{
    $haystack = strtolower($name . ' ' . $slug);
    if (preg_match('/women|woman|female|ladies|her\b/', $haystack)) {
        return $icons['face'];
    }
    if (preg_match('/\bmen\b|men\'s|mens|male|\bhim\b/', $haystack)) {
        return $icons['leaf'];
    }
    if (preg_match('/weight|slim|fat|obes|diet|metabol/', $haystack)) {
        return $icons['body'];
    }
    if (preg_match('/general|immun|wellness|health|daily/', $haystack)) {
        return $icons['lotus'];
    }
    return $icons[$keys[$index % count($keys)]];
}

$sbcSprig = '<svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <path d="M10 190C60 150 110 100 175 20" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
    <path d="M52 152c-22-4-34-22-36-44 22 2 38 16 36 44Z" fill="currentColor" opacity=".55"/>
    <path d="M74 128c-6-24 2-44 24-56 8 24 0 44-24 56Z" fill="currentColor" opacity=".7"/>
    <path d="M100 100c14-18 34-24 56-18-8 22-28 30-56 18Z" fill="currentColor" opacity=".5"/>
    <path d="M124 70c-4-22 6-40 28-48 6 22-2 38-28 48Z" fill="currentColor" opacity=".7"/>
    <path d="M40 176c-18 4-32-4-40-20 18-6 34 0 40 20Z" fill="currentColor" opacity=".45"/>
</svg>';
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lora:wght@600;700&display=swap" rel="stylesheet">

<style>
    /* ==========================================================================
       Shop By Category — curated collection cards. Namespaced "sbc".
       4 large cards per row on desktop, 3 on tablet, 2 on mobile; incomplete
       last rows stay centered. Each card: photo on top, a rounded "sheet"
       overlapping the photo with icon, name, description and a CTA row.
       ========================================================================== */

    .sbc {
        --sbc-cols: 4;
        --sbc-gap: 30px;
        --sbc-green-dark: #0f5132;
        --sbc-green: var(--color-accent, #2f6b4f);
        --sbc-line: #dfe7dd;
        --sbc-text: #5a675f;
        --sbc-serif: 'Lora', Georgia, 'Times New Roman', serif;

        position: relative;
        overflow: hidden;
        padding: 40px 0 72px;
        background: var(--color-bg);
    }

    .sbc__leaf {
        position: absolute;
        color: #9fbba6;
        opacity: 0.4;
        pointer-events: none;
        z-index: 0;
    }

    .sbc__leaf svg { width: 100%; height: 100%; display: block; }
    .sbc__leaf--tl { top: 10px; left: -20px; width: 150px; height: 150px; transform: rotate(-4deg); }
    .sbc__leaf--br { bottom: 10px; right: -24px; width: 130px; height: 130px; transform: scaleX(-1) rotate(160deg); }

    .sbc .container { position: relative; z-index: 1; }

    /* ---- Header ---- */

    .sbc__head {
        text-align: center;
        max-width: 700px;
        margin: 0 auto 48px;
    }

    .sbc__mark {
        display: block;
        width: 40px;
        height: 34px;
        margin: 0 auto 8px;
        color: var(--sbc-green-dark);
    }

    .sbc__eyebrow {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin: 0 0 16px;
        font-size: 14px;
        font-weight: 500;
        letter-spacing: 0.4em;
        text-transform: uppercase;
        color: var(--sbc-green);
    }

    .sbc__eyebrow::before,
    .sbc__eyebrow::after {
        content: "";
        width: 56px;
        height: 1px;
        background: #b9c9bc;
    }

    .sbc__heading {
        margin: 0 0 14px;
        font-family: var(--sbc-serif);
        font-size: 46px;
        line-height: 1.15;
        font-weight: 700;
        color: var(--sbc-green-dark);
    }

    .sbc__subheading {
        margin: 0 auto;
        max-width: 560px;
        font-size: 17px;
        line-height: 1.65;
        color: var(--sbc-text);
    }

    /* ---- Grid ---- */

    .sbc__grid {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: var(--sbc-gap);
        max-width: 1380px;
        margin: 0 auto;
    }

    .sbc__item {
        flex: 0 0 calc((100% - (var(--sbc-cols) - 1) * var(--sbc-gap)) / var(--sbc-cols));
        min-width: 0;
    }

    /* ---- Card ---- */

    .sbc__card {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: #fbfcf8;
        border: 1px solid transparent;
        border-radius: 22px;
        overflow: hidden;
        text-decoration: none;
        color: inherit;
        box-shadow: 0 6px 22px rgba(25, 70, 58, 0.09);
        transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
    }

    .sbc__card:hover,
    .sbc__card:focus-visible {
        transform: translateY(-3px);
        border-color: var(--sbc-green);
        box-shadow: 0 14px 32px rgba(25, 70, 58, 0.14);
    }

    .sbc__card:focus-visible {
        outline: 2px solid var(--sbc-green);
        outline-offset: 3px;
    }

    .sbc__media {
        position: relative;
        aspect-ratio: 1.3 / 1;
        overflow: hidden;
        background: linear-gradient(160deg, var(--color-primary-light, #e6efe8) 0%, #fff 100%);
    }

    .sbc__image {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.45s ease;
    }

    .sbc__card:hover .sbc__image,
    .sbc__card:focus-visible .sbc__image {
        transform: scale(1.04);
    }

    .sbc__fallback {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sbc__fallback span {
        width: 32%;
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.65);
    }

    .sbc__fallback svg { width: 52%; height: 52%; color: var(--sbc-green); opacity: 0.8; }

    /* Sheet that overlaps the bottom of the photo */
    .sbc__body {
        position: relative;
        display: flex;
        flex-direction: column;
        flex: 1;
        margin-top: -26px;
        padding: 24px 28px 26px;
        background: #fbfcf8;
        border-radius: 26px 26px 0 0;
    }

    .sbc__icon {
        width: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 1.5px solid #b9cbbd;
        background: #f1f5ef;
        color: var(--sbc-green);
    }

    .sbc__icon svg { width: 28px; height: 28px; }

    .sbc__name {
        margin: 18px 0 8px;
        font-family: var(--sbc-serif);
        font-size: 25px;
        line-height: 1.25;
        font-weight: 600;
        color: var(--sbc-green-dark);
    }

    .sbc__desc {
        margin: 0;
        max-width: 260px;
        font-size: 15.5px;
        line-height: 1.6;
        color: var(--sbc-text);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .sbc__foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: auto;
        padding-top: 24px;
    }

    .sbc__cta {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 15px;
        font-weight: 600;
        color: var(--sbc-green-dark);
    }

    .sbc__cta svg { width: 16px; height: 16px; transition: transform 0.25s ease; }

    .sbc__go {
        flex: 0 0 auto;
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--sbc-green-dark);
        color: #fff;
        box-shadow: 0 3px 8px rgba(15, 81, 50, 0.25);
    }

    .sbc__go svg { width: 18px; height: 18px; transition: transform 0.25s ease; }

    .sbc__card:hover .sbc__cta svg,
    .sbc__card:hover .sbc__go svg,
    .sbc__card:focus-visible .sbc__cta svg,
    .sbc__card:focus-visible .sbc__go svg {
        transform: translateX(3px);
    }

    @media (prefers-reduced-motion: reduce) {
        .sbc__card, .sbc__image, .sbc__cta svg, .sbc__go svg { transition: none; }
    }

    /* ---- Responsive ---- */

    @media (max-width: 1200px) {
        .sbc { --sbc-cols: 3; --sbc-gap: 26px; }
        .sbc__heading { font-size: 40px; }
    }

    @media (max-width: 820px) {
        .sbc { --sbc-cols: 2; --sbc-gap: 16px; padding: 32px 0 52px; }
        .sbc__heading { font-size: 32px; }
        .sbc__subheading { font-size: 15.5px; }
        .sbc__head { margin-bottom: 32px; }
        .sbc__eyebrow { letter-spacing: 0.28em; font-size: 12.5px; }
        .sbc__eyebrow::before, .sbc__eyebrow::after { width: 30px; }
        .sbc__body { padding: 20px 18px 20px; margin-top: -22px; border-radius: 22px 22px 0 0; }
        .sbc__icon { width: 44px; height: 44px; }
        .sbc__icon svg { width: 24px; height: 24px; }
        .sbc__name { font-size: 20px; margin-top: 14px; }
        .sbc__desc { font-size: 14px; }
        .sbc__foot { padding-top: 18px; }
        .sbc__cta { font-size: 14px; }
        .sbc__go { width: 38px; height: 38px; }
    }

    @media (max-width: 480px) {
        .sbc__heading { font-size: 27px; }
        .sbc__desc { display: none; }
        .sbc__cta-long { display: none; }
        .sbc__leaf--tl { width: 100px; height: 100px; }
        .sbc__leaf--br { display: none; }
    }
</style>

<?php if ($shopCategoryCount > 0): ?>
    <section class="sbc" data-sbc aria-labelledby="sbc-heading">
        <span class="sbc__leaf sbc__leaf--tl"><?= $sbcSprig ?></span>
        <span class="sbc__leaf sbc__leaf--br"><?= $sbcSprig ?></span>

        <div class="container">
            <div class="sbc__head">
                <svg class="sbc__mark" viewBox="0 0 40 34" fill="none" aria-hidden="true">
                    <path d="M20 32V16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M20 18C20 9 14 4 5 4c0 9 5 14 15 14Z" fill="#6f8f45"/>
                    <path d="M20 18c0-9 6-14 15-14 0 9-5 14-15 14Z" fill="currentColor"/>
                    <path d="M20 12c0-4 1-7 0-10 2 3 3 6 0 10Z" fill="#6f8f45"/>
                </svg>
                <p class="sbc__eyebrow">Shop By Category</p>
                <h2 class="sbc__heading" id="sbc-heading">Explore Wellness for Every You</h2>
                <p class="sbc__subheading">Discover our carefully curated categories, designed to support your unique wellness journey.</p>
            </div>

            <div class="sbc__grid">
                <?php foreach ($shopCategories as $index => $category):
                    $imageUrl = getCategoryImageUrl($category['image'] ?? null);
                    $name = trim((string) ($category['name'] ?? ''));
                    $slug = (string) ($category['slug'] ?? '');
                    $url = getCategoryUrl($slug);

                    // Optional short description: uses whichever field exists, otherwise omitted.
                    $description = trim(strip_tags((string) ($category['short_description'] ?? $category['description'] ?? '')));
                    $iconSvg = sbcPickIcon($name, $slug, (int) $index, $sbcIcons, $sbcIconKeys);
                ?>
                    <div class="sbc__item">
                        <a class="sbc__card" href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>">
                            <div class="sbc__media">
                                <span class="sbc__fallback" aria-hidden="true">
                                    <span>
                                        <svg viewBox="0 0 64 64" fill="currentColor">
                                            <path d="M32 6c-6 8-10 16-10 24 0 6 4 10 10 10s10-4 10-10c0-8-4-16-10-24z" />
                                            <path d="M12 24c8 0 14 6 16 14-8 2-16-2-20-8-1.5-2.4-1-4.6 4-6z" />
                                            <path d="M52 24c-8 0-14 6-16 14 8 2 16-2 20-8 1.5-2.4 1-4.6-4-6z" />
                                        </svg>
                                    </span>
                                </span>
                                <?php if ($imageUrl !== null): ?>
                                    <img
                                        class="sbc__image"
                                        src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>"
                                        alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                                        loading="lazy"
                                        onerror="this.style.display='none';">
                                <?php endif; ?>
                            </div>

                            <div class="sbc__body">
                                <span class="sbc__icon" aria-hidden="true"><?= $iconSvg ?></span>
                                <h3 class="sbc__name"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h3>
                                <?php if ($description !== ''): ?>
                                    <p class="sbc__desc"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>
                                <?php endif; ?>

                                <div class="sbc__foot">
                                    <span class="sbc__cta">
                                        <span>Explore<span class="sbc__cta-long"> Collection</span></span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                    </span>
                                    <span class="sbc__go" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>