<?php

if (!isset($promoVideos) || !is_array($promoVideos)) {
    if (!function_exists('getFeaturedPromoVideos')) {
        require_once __DIR__ . '/../function/promotional-video.php';
    }
    try {
        $promoVideos = getFeaturedPromoVideos($conn, 12);
    } catch (Exception $e) {
        $promoVideos = [];
    }
}

$promoCount = count($promoVideos);
?>

<style>

    .prv {
        --prv-height: 320px;
        --prv-gap: 20px;
        --prv-radius: var(--radius-lg);
        --prv-scale: 1.05;
        --prv-shadow: 0 10px 24px rgba(23, 72, 61, 0.12);
        --prv-shadow-active: 0 26px 48px rgba(23, 72, 61, 0.28);

        padding: 60px 0;
        background: var(--color-bg);
    }

    .prv__head {
        max-width: 640px;
        margin: 0 auto 36px;
        text-align: center;
    }

    .prv__heading {
        margin: 0 0 10px;
        font-size: 30px;
        font-weight: 700;
        color: var(--color-text);
    }

    .prv__subheading {
        margin: 0;
        font-size: 14.5px;
        line-height: 1.6;
        color: var(--color-text-light);
    }

    .prv__stage {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .prv__viewport {
        flex: 1;
        min-width: 0;
        overflow-x: auto;
        overflow-y: hidden;
        scroll-snap-type: x mandatory;
        scroll-behavior: smooth;
        touch-action: pan-x;
        cursor: grab;
        -webkit-overflow-scrolling: touch;
        padding: 14px 0 30px;

        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .prv__viewport::-webkit-scrollbar {
        display: none;
    }

    .prv__viewport:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 4px;
        border-radius: var(--radius-md);
    }

    .prv__viewport.is-dragging {
        cursor: grabbing;
        scroll-snap-type: none;
        scroll-behavior: auto;
    }

    /* ---- Track: side padding is set by JS to half the widest card's
       width, so even the widest horizontal card can be scrolled to
       dead-center at either end (a fixed CSS calc() can't do this once
       card widths vary, since it doesn't know the widest card). ---- */

    .prv__track {
        display: flex;
        align-items: flex-end;
        gap: var(--prv-gap);
        padding-inline: 40px; /* JS overrides this once cards are measured */
    }

    /* ---- Card: HEIGHT is fixed; WIDTH comes from aspect-ratio, which
       is what makes mixed orientation possible in one row. ---- */

    .prv__card {
        flex: 0 0 auto;
        scroll-snap-align: center;
    }

    .prv__media {
        position: relative;
        height: var(--prv-height);
        border-radius: var(--prv-radius);
        overflow: hidden;
        background: var(--color-primary-light);
        box-shadow: var(--prv-shadow);
        transform: scale(1);
        transition: transform 0.35s ease, box-shadow 0.35s ease, width 0.25s ease;
        -webkit-user-drag: none;
        user-select: none;
    }

    /* Default (and while orientation is still being auto-detected):
       assume vertical, since that's the more common short-form shape
       and keeps first paint from overflowing sideways. */
    .prv__card[data-orientation="vertical"] .prv__media,
    .prv__card[data-orientation="auto"] .prv__media {
        aspect-ratio: 9 / 16;
    }

    .prv__card[data-orientation="horizontal"] .prv__media {
        aspect-ratio: 16 / 9;
    }

    .prv__card.is-active .prv__media {
        transform: scale(var(--prv-scale));
        box-shadow: var(--prv-shadow-active);
        z-index: 2;
    }

    .prv__video {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        background: #0b0f0d;
        pointer-events: none;
    }

    .prv__title {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        margin: 0;
        padding: 32px 14px 12px;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0));
        color: #fff;
        font-size: 12.5px;
        font-weight: 600;
        line-height: 1.35;

        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* ---- Small badge marking horizontal videos, purely so people
       scanning the row can tell at a glance it's a "watch" video vs a
       reel — remove this block if you don't want it. ---- */

    .prv__badge {
        position: absolute;
        top: 10px;
        left: 10px;
        padding: 3px 8px;
        border-radius: 999px;
        background: rgba(11, 15, 13, 0.55);
        color: #fff;
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        pointer-events: none;
    }

    .prv__card[data-orientation="vertical"] .prv__badge,
    .prv__card[data-orientation="auto"] .prv__badge {
        display: none;
    }

    /* ---- Play / mute controls (same pattern as reels.php) ---- */

    .prv__control {
        position: absolute;
        top: 10px;
        width: 32px;
        height: 32px;
        border: none;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(11, 15, 13, 0.5);
        color: #fff;
        cursor: pointer;
        transition: background 0.15s ease, transform 0.15s ease;
    }

    .prv__control:hover {
        background: rgba(11, 15, 13, 0.72);
    }

    .prv__control:active {
        transform: scale(0.92);
    }

    .prv__control:focus-visible {
        outline: 2px solid #fff;
        outline-offset: 2px;
    }

    .prv__control svg {
        width: 15px;
        height: 15px;
    }

    .prv__control--play {
        left: 10px;
    }

    .prv__control--mute {
        right: 10px;
    }

    .prv__control [hidden] {
        display: none;
    }

    /* ---- Nav buttons ---- */

    .prv__nav {
        flex: 0 0 auto;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        border: 1px solid var(--color-border);
        background: var(--color-white);
        color: var(--color-text);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: var(--shadow-soft);
        transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease, transform 0.1s ease;
    }

    .prv__nav svg {
        width: 18px;
        height: 18px;
    }

    .prv__nav:hover {
        background: var(--color-primary);
        border-color: var(--color-primary);
        color: var(--color-white);
    }

    .prv__nav:active {
        transform: scale(0.92);
    }

    .prv__nav:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 2px;
    }

    .prv__nav:disabled {
        opacity: 0.35;
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }

    /* ---- Responsive: card HEIGHT shrinks; widths follow automatically
       via aspect-ratio, for both orientations at once. ---- */

    @media (max-width: 1080px) {
        .prv {
            --prv-height: 280px;
        }
    }

    @media (max-width: 720px) {
        .prv {
            --prv-height: 220px;
            --prv-gap: 14px;
        }

        .prv__heading {
            font-size: 22px;
        }

        .prv__stage {
            gap: 8px;
        }

        .prv__nav {
            width: 38px;
            height: 38px;
        }
    }

    @media (max-width: 420px) {
        .prv {
            --prv-height: 190px;
        }

        .prv__nav {
            width: 34px;
            height: 34px;
        }
    }
</style>

<?php if ($promoCount > 0): ?>
    <section class="prv" data-prv aria-label="Promotional videos">
        <div class="container">
            <div class="prv__head">
                <h2 class="prv__heading">Promotional Videos</h2>
                <p class="prv__subheading">Campaigns, launches, and stories from us — swipe through and press play.</p>
            </div>

            <div class="prv__stage">
                <button type="button" class="prv__nav prv__nav--prev" data-prv-prev aria-label="Previous video">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6" />
                    </svg>
                </button>

                <div class="prv__viewport" data-prv-viewport tabindex="0" role="region" aria-roledescription="carousel" aria-label="Promotional videos carousel — use the arrow buttons or left/right arrow keys to browse">
                    <div class="prv__track" data-prv-track>
                        <?php foreach ($promoVideos as $video):
                            $videoUrl = htmlspecialchars(BASE_URL . ltrim((string) ($video['video_url'] ?? ''), '/'), ENT_QUOTES, 'UTF-8');
                            $hasThumb = !empty($video['thumbnail']);
                            $posterUrl = $hasThumb ? htmlspecialchars(BASE_URL . ltrim((string) $video['thumbnail'], '/'), ENT_QUOTES, 'UTF-8') : '';
                            $title = trim((string) ($video['title'] ?? ''));
                            $safeTitle = htmlspecialchars($title !== '' ? $title : 'Video', ENT_QUOTES, 'UTF-8');

                            $orientation = strtolower(trim((string) ($video['orientation'] ?? '')));
                            if ($orientation !== 'vertical' && $orientation !== 'horizontal') {
                                $orientation = 'auto';
                            }
                        ?>
                            <div class="prv__card" data-prv-card data-orientation="<?= $orientation ?>">
                                <div class="prv__media">
                                    <video
                                        class="prv__video"
                                        data-prv-video
                                        src="<?= $videoUrl ?>"
                                        <?= $posterUrl !== '' ? 'poster="' . $posterUrl . '"' : '' ?>
                                        muted
                                        loop
                                        playsinline
                                        preload="metadata"></video>

                                    <span class="prv__badge">Watch</span>

                                    <button type="button" class="prv__control prv__control--play" data-prv-play aria-label="Play <?= $safeTitle ?>">
                                        <svg class="prv-icon-play" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M8 5.5v13l11-6.5z" />
                                        </svg>
                                        <svg class="prv-icon-pause" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" hidden>
                                            <path d="M7 5h4v14H7zM13 5h4v14h-4z" />
                                        </svg>
                                    </button>

                                    <button type="button" class="prv__control prv__control--mute" data-prv-mute aria-label="Unmute <?= $safeTitle ?>">
                                        <svg class="prv-icon-muted" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M4 9v6h4l5 5V4L8 9H4z" />
                                            <path d="M16.5 12l3.5 3.5M20 12l-3.5 3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" fill="none" />
                                        </svg>
                                        <svg class="prv-icon-unmuted" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" hidden>
                                            <path d="M4 9v6h4l5 5V4L8 9H4z" />
                                            <path d="M16 8.5a5 5 0 0 1 0 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" fill="none" />
                                            <path d="M18.5 6a8.5 8.5 0 0 1 0 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" fill="none" />
                                        </svg>
                                    </button>

                                    <?php if ($title !== ''): ?>
                                        <p class="prv__title"><?= $safeTitle ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="button" class="prv__nav prv__nav--next" data-prv-next aria-label="Next video">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 6l6 6-6 6" />
                    </svg>
                </button>
            </div>
        </div>
    </section>

    <script>
        (function () {
            'use strict';

            document.querySelectorAll('[data-prv]').forEach(function (root) {
                var viewport = root.querySelector('[data-prv-viewport]');
                var track = root.querySelector('[data-prv-track]');
                var prevBtn = root.querySelector('[data-prv-prev]');
                var nextBtn = root.querySelector('[data-prv-next]');
                var cards = track ? Array.prototype.slice.call(track.children) : [];

                if (!viewport || !track || cards.length === 0) {
                    return;
                }

                function setIcon(btn, playingClass, pausedClass, isFirstState) {
                    var a = btn.querySelector(playingClass);
                    var b = btn.querySelector(pausedClass);
                    if (a) a.hidden = !isFirstState;
                    if (b) b.hidden = isFirstState;
                }

                function setPlayIcon(card, isPlaying) {
                    var btn = card.querySelector('[data-prv-play]');
                    if (!btn) return;
                    setIcon(btn, '.prv-icon-play', '.prv-icon-pause', !isPlaying);
                    btn.setAttribute('aria-label', isPlaying ? 'Pause' : 'Play');
                }

                function applyDetectedOrientation(card, video) {
                    if (!video.videoWidth || !video.videoHeight) return;
                    var detected = video.videoWidth >= video.videoHeight ? 'horizontal' : 'vertical';
                    if (card.getAttribute('data-orientation') !== detected) {
                        card.setAttribute('data-orientation', detected);
                        updatePadding();

                        var active = track.querySelector('.prv__card.is-active') || cards[0];
                        centerCard(active, false);
                        updateActiveCard();
                    }
                }

                function updateActiveCard() {
                    var viewportRect = viewport.getBoundingClientRect();
                    var viewportCenter = viewportRect.left + viewportRect.width / 2;

                    var closest = null;
                    var closestDist = Infinity;
                    cards.forEach(function (card) {
                        var r = card.getBoundingClientRect();
                        var cardCenter = r.left + r.width / 2;
                        var dist = Math.abs(cardCenter - viewportCenter);
                        if (dist < closestDist) {
                            closestDist = dist;
                            closest = card;
                        }
                    });

                    cards.forEach(function (card) {
                        var isActive = card === closest;
                        card.classList.toggle('is-active', isActive);

                        var video = card.querySelector('[data-prv-video]');
                        if (!video) return;

                        if (isActive) {
                            var playAttempt = video.play();
                            if (playAttempt && typeof playAttempt.catch === 'function') {
                                playAttempt.catch(function () {
                                    setPlayIcon(card, false);
                                });
                            }
                            setPlayIcon(card, !video.paused);
                        } else if (!video.paused || video.currentTime > 0) {
                            video.pause();
                            video.currentTime = 0;
                            setPlayIcon(card, false);
                        }
                    });

                    if (prevBtn) prevBtn.disabled = closest === cards[0];
                    if (nextBtn) nextBtn.disabled = closest === cards[cards.length - 1];
                }

                function centerCard(card, smooth) {
                    var target = card.offsetLeft + card.offsetWidth / 2 - viewport.clientWidth / 2;
                    viewport.scrollTo({ left: target, behavior: smooth ? 'smooth' : 'auto' });
                }

                function step(dir) {
                    var active = track.querySelector('.prv__card.is-active') || cards[0];
                    var idx = cards.indexOf(active);
                    var target = cards[Math.min(cards.length - 1, Math.max(0, idx + dir))];
                    centerCard(target, true);
                }

                if (prevBtn) prevBtn.addEventListener('click', function () { step(-1); });
                if (nextBtn) nextBtn.addEventListener('click', function () { step(1); });

                viewport.addEventListener('keydown', function (event) {
                    if (event.key === 'ArrowRight') {
                        event.preventDefault();
                        step(1);
                    } else if (event.key === 'ArrowLeft') {
                        event.preventDefault();
                        step(-1);
                    }
                });

                function updatePadding() {
                    var maxWidth = 0;
                    cards.forEach(function (card) {
                        maxWidth = Math.max(maxWidth, card.offsetWidth);
                    });
                    var pad = Math.ceil(maxWidth / 2) + 12;
                    track.style.paddingInline = pad + 'px';
                }

                // ---- Mouse drag ----
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
                    if (!isDown) return;
                    var delta = event.pageX - startX;
                    if (Math.abs(delta) > 4) dragged = true;
                    viewport.scrollLeft = startScroll - delta;
                });

                function endDrag() {
                    if (!isDown) return;
                    isDown = false;
                    viewport.classList.remove('is-dragging');
                    window.setTimeout(updateActiveCard, 60);
                }

                window.addEventListener('mouseup', endDrag);
                viewport.addEventListener('mouseleave', function () {
                    if (isDown) endDrag();
                });

                // ---- Per-card play / mute controls + orientation detection ----
                cards.forEach(function (card) {
                    var media = card.querySelector('.prv__media');
                    var video = card.querySelector('[data-prv-video]');
                    var playBtn = card.querySelector('[data-prv-play]');
                    var muteBtn = card.querySelector('[data-prv-mute]');
                    if (!media || !video) return;

                    video.addEventListener('loadedmetadata', function () {
                        applyDetectedOrientation(card, video);
                    });
                    // Metadata may already be cached/available by the time
                    // this script runs (e.g. fast connection, small file).
                    if (video.readyState >= 1) {
                        applyDetectedOrientation(card, video);
                    }

                    if (playBtn) {
                        playBtn.addEventListener('click', function (event) {
                            event.stopPropagation();
                            if (video.paused) {
                                var attempt = video.play();
                                if (attempt && typeof attempt.catch === 'function') attempt.catch(function () {});
                                setPlayIcon(card, true);
                            } else {
                                video.pause();
                                setPlayIcon(card, false);
                            }
                        });
                    }

                    if (muteBtn) {
                        muteBtn.addEventListener('click', function (event) {
                            event.stopPropagation();
                            video.muted = !video.muted;
                            setIcon(muteBtn, '.prv-icon-muted', '.prv-icon-unmuted', video.muted);
                            muteBtn.setAttribute('aria-label', video.muted ? 'Unmute' : 'Mute');
                        });
                    }

                    media.addEventListener('click', function (event) {
                        if (dragged) {
                            dragged = false;
                            return;
                        }
                        if (event.target.closest('[data-prv-play]') || event.target.closest('[data-prv-mute]')) {
                            return;
                        }
                        if (video.paused) {
                            var attempt = video.play();
                            if (attempt && typeof attempt.catch === 'function') attempt.catch(function () {});
                            setPlayIcon(card, true);
                        } else {
                            video.pause();
                            setPlayIcon(card, false);
                        }
                    });
                });

                var scrollSettleTimer = null;
                viewport.addEventListener('scroll', function () {
                    updateActiveCard();
                    window.clearTimeout(scrollSettleTimer);
                    scrollSettleTimer = window.setTimeout(updateActiveCard, 120);
                }, { passive: true });

                var resizeTimer = null;
                window.addEventListener('resize', function () {
                    window.clearTimeout(resizeTimer);
                    resizeTimer = window.setTimeout(function () {
                        updatePadding();
                        var active = track.querySelector('.prv__card.is-active') || cards[0];
                        centerCard(active, false);
                        updateActiveCard();
                    }, 150);
                });

                var initialIndex = Math.floor((cards.length - 1) / 2);
                window.requestAnimationFrame(function () {
                    updatePadding();
                    centerCard(cards[initialIndex], false);
                    updateActiveCard();
                });
            });
        })();
    </script>
<?php endif; ?>