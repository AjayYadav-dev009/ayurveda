<?php


if (!function_exists('getAllCategoriesWithProducts')) {
    require_once __DIR__ . '/function/category.php';
}

try {
    $shopCategories = getAllCategoriesWithProducts($conn);
} catch (Exception $e) {
    $shopCategories = [];
}

$shopCategoryCount = count($shopCategories);
?>

<style>

    .sbc {
        padding: 32px 0;
        background: var(--color-bg);
    }

    .sbc__heading {
        text-align: center;
        font-size: 36px;
        font-weight: 800;
        color: var(--color-text);
        margin-bottom: 36px;
    }

    .sbc__viewport {
        display: flex;
        gap: var(--sbc-gap);
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        scroll-behavior: smooth;
        cursor: grab;
        padding-bottom: 4px;

        --sbc-visible: 5;
        --sbc-gap: 24px;

        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .sbc__viewport::-webkit-scrollbar {
        display: none;
    }

    .sbc__viewport.is-dragging {
        cursor: grabbing;
        scroll-snap-type: none;
        scroll-behavior: auto;
    }

    /* Fixed: this used to subtract a hardcoded -50px that had no relation
       to the real gap, which made every card wider than the space actually
       left for it — that's what was crushing the cards together with no
       visible gap. Now it's driven by the same --sbc-gap used above, so
       card width and spacing can never drift apart at any breakpoint. */
    .sbc__item {
        flex: 0 0 calc((100% - (var(--sbc-visible) - 1) * var(--sbc-gap)) / var(--sbc-visible));
        min-width: 0;
        scroll-snap-align: start;
    }

    @media (max-width: 1024px) {
        .sbc__viewport {
            --sbc-visible: 4;
        }
    }

    @media (max-width: 860px) {
        .sbc__viewport {
            --sbc-visible: 3;
        }
    }

    @media (max-width: 640px) {
        .sbc__viewport {
            --sbc-visible: 2;
            --sbc-gap: 16px;
        }

        .sbc__heading {
            font-size: 22px;
            margin-bottom: 22px;
        }
    }

    @media (max-width: 420px) {
        .sbc__viewport {
            --sbc-visible: 1.15;
        }
    }

    .sbc__card {
        position: relative;
        display: block;
        aspect-ratio: 1/1;
        width: 100%;
        border-radius: var(--radius-md);
        overflow: hidden;
        background: linear-gradient(160deg, var(--color-primary-light) 0%, var(--color-white) 100%);
        box-shadow: var(--shadow-soft);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        -webkit-user-drag: none;
        user-select: none;
    }

    .sbc__card:hover,
    .sbc__card:focus-visible {
        transform: translateY(-4px);
        box-shadow: 0 12px 34px rgba(25, 70, 58, 0.16);
    }

    .sbc__card:hover .sbc__card-image {
        transform: scale(1.06);
    }

    .sbc__card:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 2px;
    }

    .sbc__card-fallback {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sbc__card-fallback span {
        width: 56%;
        height: 56%;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.55);
    }

    .sbc__card-fallback svg {
        width: 42%;
        height: 42%;
        color: var(--color-accent);
    }

    /* Wrapper so the image and its hover-zoom don't also stretch/move the
       scrim or label sitting on top of it. */
    .sbc__card-image {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        pointer-events: none;
        transition: transform 0.4s ease;
    }

    /* Soft scrim behind the name label, independent of whatever the
       uploaded photo looks like — keeps the label readable whether the
       category image is light or dark. */
    .sbc__card-scrim {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 46%;
        background: linear-gradient(to top, rgba(15, 30, 24, 0.45), rgba(15, 30, 24, 0));
        pointer-events: none;
    }

    .sbc__name {
        position: absolute;
        left: 12px;
        right: 12px;
        bottom: 12px;
        text-align: center;
        padding: 11px 14px;
        background: var(--color-white);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-soft);
        font-size: 14.5px;
        font-weight: 700;
        color: var(--color-text);
    }

    /* ---- Page dots ---- */

    .sbc__dots {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 26px;
    }

    .sbc__dots[hidden] {
        display: none;
    }

    .sbc__dot {
        width: 9px;
        height: 9px;
        padding: 0;
        border: none;
        border-radius: 50%;
        background: var(--color-border);
        transition: background 0.3s ease, transform 0.3s ease;
    }

    .sbc__dot.is-active {
        background: var(--color-primary);
        transform: scale(1.25);
    }

    .sbc__dot:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 2px;
    }
</style>

<?php if ($shopCategoryCount > 0): ?>
    <section class="sbc" data-sbc>
        <div class="container">
            <h2 class="sbc__heading">Shop By Category</h2>

            <div class="sbc__viewport" data-sbc-viewport>
                <?php foreach ($shopCategories as $category):
                    $imageUrl = getCategoryImageUrl($category['image'] ?? null);
                    $name = trim((string) ($category['name'] ?? ''));
                    $url = getCategoryUrl($category['slug'] ?? '');
                ?>
                    <div class="sbc__item">
                        <a class="sbc__card" href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>" draggable="false">
                            <span class="sbc__card-fallback" aria-hidden="true">
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
                                    class="sbc__card-image"
                                    src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>"
                                    alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                                    loading="lazy"
                                    draggable="false"
                                    onerror="this.style.display='none';">
                                <span class="sbc__card-scrim" aria-hidden="true"></span>
                            <?php endif; ?>
                            <span class="sbc__name"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></span>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="sbc__dots" data-sbc-dots hidden></div>
        </div>
    </section>

    <script>
        (function () {
            // Self-contained drag + dots script for this section only.
            // Scoped to [data-sbc] — no shared slider engine involved.
            document.querySelectorAll('[data-sbc]').forEach(function (root) {
                var viewport = root.querySelector('[data-sbc-viewport]');
                var dotsWrap = root.querySelector('[data-sbc-dots]');
                var items = Array.prototype.slice.call(viewport.children);
                var count = items.length;

                if (count === 0) {
                    return;
                }

                // ---- Click-and-drag scrolling for desktop mouse users.
                // Touch and trackpad already scroll this natively; this
                // just adds the same for a plain mouse.
                var isDown = false;
                var dragged = false;
                var startX = 0;
                var startScroll = 0;

                viewport.addEventListener('mousedown', function (event) {
                    isDown = true;
                    dragged = false;
                    viewport.classList.add('is-dragging');
                    startX = event.pageX;
                    startScroll = viewport.scrollLeft;
                });

                window.addEventListener('mousemove', function (event) {
                    if (!isDown) {
                        return;
                    }
                    var delta = event.pageX - startX;
                    if (Math.abs(delta) > 4) {
                        dragged = true;
                    }
                    viewport.scrollLeft = startScroll - delta;
                });

                function endDrag() {
                    if (!isDown) {
                        return;
                    }
                    isDown = false;
                    viewport.classList.remove('is-dragging');
                    snapToNearest();
                }

                window.addEventListener('mouseup', endDrag);
                viewport.addEventListener('mouseleave', function () {
                    if (isDown) {
                        endDrag();
                    }
                });

                // A drag that actually moved the row shouldn't also follow
                // the card's link on release.
                viewport.addEventListener('click', function (event) {
                    if (dragged) {
                        event.preventDefault();
                        dragged = false;
                    }
                }, true);

                function snapToNearest() {
                    var pageWidth = viewport.clientWidth;
                    var page = Math.round(viewport.scrollLeft / pageWidth);
                    viewport.scrollTo({ left: page * pageWidth, behavior: 'smooth' });
                }

                // ---- Dots: one per screenful, jump on click, reflect
                // scroll position as the user drags/scrolls.
                function pageCount() {
                    var raw = getComputedStyle(viewport).getPropertyValue('--sbc-visible');
                    var visible = Math.max(1, Math.floor(parseFloat(raw)) || 1);
                    return Math.max(1, Math.ceil(count / visible));
                }

                function buildDots() {
                    dotsWrap.innerHTML = '';
                    var pages = pageCount();
                    if (pages <= 1) {
                        dotsWrap.hidden = true;
                        return;
                    }
                    dotsWrap.hidden = false;
                    for (var i = 0; i < pages; i++) {
                        var dot = document.createElement('button');
                        dot.type = 'button';
                        dot.className = 'sbc__dot';
                        dot.setAttribute('aria-label', 'Go to categories page ' + (i + 1));
                        (function (page) {
                            dot.addEventListener('click', function () {
                                viewport.scrollTo({ left: page * viewport.clientWidth, behavior: 'smooth' });
                            });
                        })(i);
                        dotsWrap.appendChild(dot);
                    }
                    updateActiveDot();
                }

                function updateActiveDot() {
                    var pageWidth = viewport.clientWidth || 1;
                    var active = Math.round(viewport.scrollLeft / pageWidth);
                    var dots = dotsWrap.querySelectorAll('.sbc__dot');
                    dots.forEach(function (dot, i) {
                        dot.classList.toggle('is-active', i === active);
                    });
                }

                var scrollTimer = null;
                viewport.addEventListener('scroll', function () {
                    window.clearTimeout(scrollTimer);
                    scrollTimer = window.setTimeout(updateActiveDot, 80);
                });

                var resizeTimer = null;
                window.addEventListener('resize', function () {
                    window.clearTimeout(resizeTimer);
                    resizeTimer = window.setTimeout(buildDots, 150);
                });

                buildDots();
            });
        })();
    </script>
<?php endif; ?>