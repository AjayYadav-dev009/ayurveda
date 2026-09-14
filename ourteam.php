<?php

if (!function_exists('getActiveTeamMembers')) {
    require_once __DIR__ . '/function/team.php';
}

try {
    $teamMembers = getActiveTeamMembers($conn, 12);
} catch (Exception $e) {
    // Fail closed: hide the section rather than show a broken slider.
    $teamMembers = [];
}

$teamCount = count($teamMembers);
?>

<style>
    /* ==========================================================================
       Our Team Of Ayurvedic Experts — homepage slider. Namespaced "tme" so
       it can't collide with the product-carousel sections (phl / bsp /
       trd / ssp) or the other static sections (cvb / usp).

       IMPORTANT: .tme__decor-left / .tme__decor-right (the leaf + spoon)
       live OUTSIDE .tme__slide — they render once and never get touched
       by the slide-switching JS below, unlike the photo/name/bio which
       swap per team member.
       ========================================================================== */

    .tme {
        position: relative;
        padding: 52px 0 60px;
        background: var(--color-bg);
        overflow: hidden;
    }

    .tme__heading {
        text-align: center;
        font-size: 28px;
        font-weight: 700;
        color: var(--color-text);
        margin: 0 0 40px;
    }

    /* ---- Static decorative images (never swapped by the slider) ---- */

    .tme__decor {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 14px;
        z-index: 0;
        pointer-events: none;
    }

    .tme__decor-left {
        left: 4%;
    }

    .tme__decor-right {
        right: 4%;
    }

    .tme__decor-line {
        width: 1px;
        height: 160px;
        border-left: 1px dashed var(--color-accent);
        opacity: 0.6;
    }

    .tme__decor-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--color-accent);
        opacity: 0.7;
    }

    .tme__decor img {
        width: 64px;
        height: auto;
        object-fit: contain;
    }

    @media (max-width: 1100px) {
        .tme__decor {
            display: none;
        }
    }

    /* ---- Slider ---- */

    .tme__viewport {
        position: relative;
        z-index: 1;
        max-width: 980px;
        margin: 0 auto;
    }

    .tme__slide {
        display: none;
        align-items: center;
        gap: 44px;
    }

    .tme__slide.is-active {
        display: flex;
    }

    .tme__photo-wrap {
        flex: 0 0 260px;
        width: 260px;
        height: 260px;
        border-radius: var(--radius-lg);
        overflow: hidden;
        background: var(--color-primary-light);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .tme__photo-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .tme__photo-fallback svg {
        width: 34%;
        height: 34%;
        color: var(--color-accent);
        opacity: 0.7;
    }

    .tme__info {
        flex: 1;
        min-width: 0;
    }

    .tme__name {
        margin: 0 0 6px;
        font-size: 22px;
        font-weight: 700;
        color: var(--color-accent);
    }

    .tme__designation {
        margin: 0 0 14px;
        font-size: 13px;
        font-weight: 700;
        color: var(--color-text);
    }

    .tme__bio {
        margin: 0;
        font-size: 14px;
        line-height: 1.65;
        color: var(--color-text-light);
    }

    @media (max-width: 720px) {
        .tme__slide {
            flex-direction: column;
            text-align: center;
            gap: 22px;
        }

        .tme__photo-wrap {
            flex: 0 0 200px;
            width: 200px;
            height: 200px;
        }

        .tme__heading {
            font-size: 22px;
            margin-bottom: 28px;
        }
    }

    /* ---- Dots (active dot is an elongated pill, matching the reference) ---- */

    .tme__dots {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 34px;
        position: relative;
        z-index: 1;
    }

    .tme__dot {
        width: 9px;
        height: 9px;
        padding: 0;
        border: none;
        border-radius: 999px;
        background: var(--color-border);
        transition: width 0.25s ease, background 0.25s ease;
    }

    .tme__dot.is-active {
        width: 26px;
        background: var(--color-accent);
    }

    .tme__dot:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 2px;
    }
</style>

<?php if ($teamCount > 0): ?>
    <section class="tme" data-tme>
        <div class="tme__decor tme__decor-left" aria-hidden="true">
            <span class="tme__decor-dot"></span>
            <img src="<?= htmlspecialchars(BASE_URL . 'assets/images/team-decor-leaf.png', ENT_QUOTES, 'UTF-8') ?>" alt="" onerror="this.style.display='none';">
            <span class="tme__decor-line"></span>
            <span class="tme__decor-dot"></span>
        </div>

        <div class="container">
            <h2 class="tme__heading">Our Team Of Ayurvedic Experts</h2>

            <div class="tme__viewport">
                <?php foreach ($teamMembers as $index => $member):
                    $imageUrl = getTeamMemberImageUrl($member['image'] ?? null);
                ?>
                    <div class="tme__slide <?= $index === 0 ? 'is-active' : '' ?>" data-tme-slide>
                        <div class="tme__photo-wrap">
                            <?php if ($imageUrl): ?>
                                <img src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                            <?php else: ?>
                                <span class="tme__photo-fallback">
                                    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="32" cy="22" r="12" fill="currentColor" />
                                        <path d="M10 58c0-13 10-22 22-22s22 9 22 22" stroke="currentColor" stroke-width="4" stroke-linecap="round" fill="none" />
                                    </svg>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="tme__info">
                            <h3 class="tme__name"><?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <?php if (!empty($member['designation'])): ?>
                                <p class="tme__designation"><?= htmlspecialchars($member['designation'], ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>
                            <?php if (!empty($member['bio'])): ?>
                                <p class="tme__bio"><?= htmlspecialchars($member['bio'], ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($teamCount > 1): ?>
                <div class="tme__dots" data-tme-dots>
                    <?php foreach ($teamMembers as $index => $member): ?>
                        <button type="button" class="tme__dot <?= $index === 0 ? 'is-active' : '' ?>" data-tme-dot="<?= $index ?>" aria-label="Show <?= htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8') ?>"></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="tme__decor tme__decor-right" aria-hidden="true">
            <span class="tme__decor-dot"></span>
            <img src="<?= htmlspecialchars(BASE_URL . 'assets/images/team-decor-spoon.png', ENT_QUOTES, 'UTF-8') ?>" alt="" onerror="this.style.display='none';">
            <span class="tme__decor-line"></span>
            <span class="tme__decor-dot"></span>
        </div>
    </section>

    <?php if ($teamCount > 1): ?>
        <script>
            (function () {
                // Scoped to [data-tme] — swaps .tme__slide/.tme__dot active
                // classes only. Never touches .tme__decor-left/-right, so
                // the leaf and spoon images stay put regardless of which
                // team member is showing.
                document.querySelectorAll('[data-tme]').forEach(function (root) {
                    var slides = Array.prototype.slice.call(root.querySelectorAll('[data-tme-slide]'));
                    var dots = Array.prototype.slice.call(root.querySelectorAll('[data-tme-dot]'));

                    if (slides.length <= 1) {
                        return;
                    }

                    var current = 0;
                    var autoTimer = null;

                    function show(index) {
                        current = (index + slides.length) % slides.length;
                        slides.forEach(function (slide, i) {
                            slide.classList.toggle('is-active', i === current);
                        });
                        dots.forEach(function (dot, i) {
                            dot.classList.toggle('is-active', i === current);
                        });
                    }

                    dots.forEach(function (dot, i) {
                        dot.addEventListener('click', function () {
                            show(i);
                            restartAutoplay();
                        });
                    });

                    function restartAutoplay() {
                        window.clearInterval(autoTimer);
                        autoTimer = window.setInterval(function () {
                            show(current + 1);
                        }, 7000);
                    }

                    restartAutoplay();
                });
            })();
        </script>
    <?php endif; ?>
<?php endif; ?>