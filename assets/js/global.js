document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.mega-menu').forEach(function (megaMenu) {
        var sidebarItems = megaMenu.querySelectorAll('.mega-menu__sidebar-item');

        var activate = function (item) {
            var targetId = item.getAttribute('data-panel-target');
            var targetPanel = megaMenu.querySelector('#' + targetId);
            if (!targetPanel) {
                return;
            }

            sidebarItems.forEach(function (el) {
                el.classList.remove('is-active');
            });
            megaMenu.querySelectorAll('.mega-menu__panel').forEach(function (panel) {
                panel.classList.remove('is-active');
            });

            item.classList.add('is-active');
            targetPanel.classList.add('is-active');
        };

        sidebarItems.forEach(function (item) {
            item.addEventListener('mouseenter', function () {
                activate(item);
            });

            // Touch devices: first tap previews the panel instead of
            // navigating straight to the category link.
            item.addEventListener('click', function (event) {
                if (item.classList.contains('is-active')) {
                    return; // second tap on an already-active item follows the link
                }
                if (window.matchMedia('(hover: none)').matches) {
                    event.preventDefault();
                    activate(item);
                }
            });
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var DEFAULT_AUTOPLAY_INTERVAL_MS = 2500;

    function getVisibleCount(root) {
        var raw = getComputedStyle(root).getPropertyValue('--slider-visible');
        var value = parseFloat(raw);
        return value > 0 ? value : 1;
    }

    function cloneAsHidden(node) {
        var clone = node.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        var focusable = clone.querySelectorAll('a, button');
        for (var i = 0; i < focusable.length; i++) {
            focusable[i].setAttribute('tabindex', '-1');
        }
        return clone;
    }

    function initSlider(root) {
        var track = root.querySelector('[data-slider-track]');
        if (!track) {
            return;
        }

        var realSlides = Array.prototype.slice.call(track.children);
        var count = realSlides.length;
        if (count === 0) {
            return;
        }

        var dotsContainer = root.querySelector('[data-slider-dots]');
        var dots = dotsContainer ? Array.prototype.slice.call(dotsContainer.querySelectorAll('.slider__dot')) : [];

        // `visible` and `isBuilt` describe the clone structure currently in
        // the DOM. They only change inside build() — never read live from
        // CSS elsewhere — so position math and clone count can't drift apart
        // between two points in the same render.
        var visible = 0;
        var isBuilt = false;
        var currentClones = [];
        var currentIndex = 0;
        var timerId = null;
        var autoplayIntervalMs = parseInt(root.getAttribute('data-slider-interval'), 10) || DEFAULT_AUTOPLAY_INTERVAL_MS;
        var highlightCenter = root.hasAttribute('data-slider-highlight-center');

        function slideStepPercent() {
            return 100 / getVisibleCount(root);
        }

        function setPosition(withTransition) {
            track.style.transition = withTransition ? '' : 'none';
            track.style.transform = 'translateX(-' + (currentIndex * slideStepPercent()) + '%)';
            if (!withTransition) {
                // Force layout so the next transform change animates again.
                track.offsetHeight;
                track.style.transition = '';
            }
        }

        function updateDots() {
            if (!dots.length) {
                return;
            }
            var realIndex = isBuilt ? (((currentIndex - visible) % count) + count) % count : currentIndex;
            dots.forEach(function (dot, i) {
                var isActive = i === realIndex;
                dot.classList.toggle('is-active', isActive);
                dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
        }

        // Marks whichever slide currently sits in the middle of the visible
        // row as "is-center" (e.g. the middle card of 3), so it can be
        // styled as raised/highlighted. Re-run any time currentIndex moves,
        // including the invisible wrap-around snap, since that snap points
        // at a different (but visually identical) DOM node.
        function updateCenterHighlight() {
            if (!highlightCenter) {
                return;
            }
            var mid = Math.floor(visible / 2);
            var targetIndex = currentIndex + mid;
            Array.prototype.forEach.call(track.children, function (child, i) {
                child.classList.toggle('is-center', i === targetIndex);
            });
        }

        function goToTrackIndex(index) {
            currentIndex = index;
            setPosition(true);
            updateDots();
            updateCenterHighlight();
        }

        function next() {
            goToTrackIndex(currentIndex + 1);
        }

        function startAutoplay() {
            stopAutoplay();
            if (isBuilt) {
                timerId = window.setInterval(next, autoplayIntervalMs);
            }
        }

        function stopAutoplay() {
            if (timerId !== null) {
                window.clearInterval(timerId);
                timerId = null;
            }
        }

        function removeClones() {
            currentClones.forEach(function (node) {
                node.remove();
            });
            currentClones = [];
        }

        // (Re)builds the clone structure for a given visible count, resuming
        // on whichever real slide was showing before — so a breakpoint
        // change (resize, zoom, dev tools, device rotation) rebuilds the
        // loop instead of leaving stale clones that no longer match what
        // the CSS is currently rendering per slide.
        function build(newVisible, resumeRealIndex) {
            removeClones();

            if (count <= newVisible) {
                visible = newVisible;
                isBuilt = false;
                currentIndex = resumeRealIndex || 0;
                if (dotsContainer) {
                    dotsContainer.style.display = 'none';
                }
                setPosition(false);
                updateDots();
                updateCenterHighlight();
                stopAutoplay();
                return;
            }

            if (dotsContainer) {
                dotsContainer.style.display = '';
            }

            // Clone enough slides on each side to make the loop seamless:
            // [tail clones][real slides][head clones]
            var headClones = realSlides.slice(0, newVisible).map(cloneAsHidden);
            var tailClones = realSlides.slice(-newVisible).map(cloneAsHidden);

            tailClones.forEach(function (clone) {
                track.insertBefore(clone, track.firstChild);
            });
            headClones.forEach(function (clone) {
                track.appendChild(clone);
            });
            currentClones = tailClones.concat(headClones);

            visible = newVisible;
            isBuilt = true;
            currentIndex = newVisible + (resumeRealIndex || 0); // position of the first real slide within the full (cloned) track
            setPosition(false);
            updateDots();
            updateCenterHighlight();
        }

        // After sliding into the cloned region, snap back to the matching
        // real position with no transition — invisible to the user.
        track.addEventListener('transitionend', function (event) {
            if (event.target !== track || !isBuilt) {
                return;
            }
            if (currentIndex >= visible + count) {
                currentIndex -= count;
                setPosition(false);
                updateCenterHighlight();
            } else if (currentIndex < visible) {
                currentIndex += count;
                setPosition(false);
                updateCenterHighlight();
            }
        });

        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function () {
                goToTrackIndex((isBuilt ? visible : 0) + i);
                startAutoplay(); // restart the clock so it doesn't jump right after a manual pick
            });
        });

        if (!root.hasAttribute('data-slider-no-hover-pause')) {
            root.addEventListener('mouseenter', stopAutoplay);
            root.addEventListener('mouseleave', startAutoplay);
            root.addEventListener('touchstart', stopAutoplay, { passive: true });
            root.addEventListener('touchend', startAutoplay, { passive: true });
        }

        var resizeTimer = null;
        window.addEventListener('resize', function () {
            window.clearTimeout(resizeTimer);
            resizeTimer = window.setTimeout(function () {
                var newVisible = Math.max(1, Math.floor(getVisibleCount(root)));
                if (newVisible !== visible) {
                    // The visible-slide count actually changed (crossed a
                    // breakpoint) — the old clones no longer match, rebuild
                    // around whichever real slide is currently showing.
                    var realIndex = isBuilt ? (((currentIndex - visible) % count) + count) % count : currentIndex;
                    build(newVisible, realIndex);
                } else {
                    // Same slide count, just a pixel-width change — no
                    // rebuild needed, only reposition.
                    setPosition(false);
                }
            }, 150);
        });

        build(Math.max(1, Math.floor(getVisibleCount(root))), 0);
        startAutoplay();
    }

    document.querySelectorAll('[data-slider]').forEach(initSlider);
});