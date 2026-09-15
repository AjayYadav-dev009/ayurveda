<?php
/**
 * Static "Don't Know Your Dosha Yet?" homepage banner.
 *
 * Purely presentational — no database queries. Drop in wherever it belongs
 * on the homepage:
 *
 *   include __DIR__ . '/partials/dosha-banner.php';
 *
 * The only dynamic piece is the CTA link, which points at a quiz page via
 * BASE_URL — update DOSHA_QUIZ_URL below (or the href directly) once that
 * page exists.
 */
$doshaQuizUrl = (defined('BASE_URL') ? BASE_URL : '/') . 'dosha-quiz.php';
?>

<style>
    /* ==========================================================================
       Dosha banner — self-contained, namespaced "dq". Dark-green background
       uses the site's own --color-primary (it's already almost this exact
       shade). The warm gold/cream text, though, has no equivalent in
       global.css's token set — --dq-gold / --dq-cream below are local,
       one-off colors scoped to this section only, not new site-wide tokens.
       If this look gets reused elsewhere, promote them to global.css then.
       ========================================================================== */

    .dq {
        --dq-gold: #d8b678;
        --dq-cream: #f3ead9;
        --dq-line: rgba(216, 182, 120, 0.35);

        padding: 64px 0;
        background: var(--color-primary);
        color: var(--dq-cream);
    }

    .dq__header {
        text-align: center;
        max-width: 640px;
        margin: 0 auto 48px;
    }

    .dq__title {
        font-size: 30px;
        font-weight: 700;
        color: var(--dq-gold);
        margin: 0 0 8px;
    }

    .dq__subtitle {
        font-size: 15px;
        font-weight: 600;
        color: var(--dq-cream);
        margin: 0;
    }

    .dq__flourish {
        width: 46px;
        height: 2px;
        background: var(--dq-gold);
        border-radius: 2px;
        opacity: 0.7;
        margin: 18px auto 0;
    }

    .dq__body {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
        align-items: center;
        gap: 48px;
    }

    .dq__divider {
        align-self: stretch;
        width: 1px;
        background: linear-gradient(to bottom, transparent, var(--dq-line) 20%, var(--dq-line) 80%, transparent);
    }

    /* ---- Text column ---- */

    .dq__eyebrow {
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--dq-cream);
        margin: 0 0 16px;
    }

    .dq__text {
        font-size: 14px;
        line-height: 1.7;
        color: var(--dq-cream);
        opacity: 0.9;
        margin: 0 0 14px;
    }

    .dq__doshas {
        display: flex;
        gap: 32px;
        margin: 24px 0 28px;
    }

    .dq__dosha {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .dq__dosha-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: rgba(216, 182, 120, 0.09);
        border: 1px solid var(--dq-line);
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .dq__dosha:hover .dq__dosha-icon {
        background: rgba(216, 182, 120, 0.18);
        transform: translateY(-2px);
    }

    .dq__dosha-icon svg {
        width: 30px;
        height: 30px;
        color: var(--dq-gold);
    }

    .dq__dosha span {
        font-size: 12.5px;
        font-weight: 600;
        color: var(--dq-gold);
    }

    .dq__cta {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 15px 32px;
        border: 1px solid var(--dq-cream);
        border-radius: var(--radius-sm);
        color: var(--dq-cream);
        font-size: 12.5px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        transition: background 0.2s ease, color 0.2s ease;
    }

    .dq__cta svg {
        width: 15px;
        height: 15px;
        transition: transform 0.2s ease;
    }

    .dq__cta:hover {
        background: var(--dq-cream);
        color: var(--color-primary);
    }

    .dq__cta:hover svg {
        transform: translateX(3px);
    }

    /* ---- Illustration column: a framed medallion instead of a small
       doodle floating loose in a lot of empty space. ---- */

    .dq__art {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dq__art-ring {
        position: relative;
        width: 300px;
        height: 300px;
        max-width: 100%;
        border-radius: 50%;
        border: 1px solid var(--dq-line);
        background: radial-gradient(circle, rgba(216, 182, 120, 0.1) 0%, rgba(216, 182, 120, 0) 72%);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dq__art-ring::before {
        content: "";
        position: absolute;
        inset: 20px;
        border-radius: 50%;
        border: 1px dashed var(--dq-line);
    }

    .dq__art-main {
        position: relative;
        z-index: 1;
        width: 220px;
        height: 220px;
        color: var(--dq-gold);
    }

    .dq__art-accent {
        position: absolute;
        z-index: 1;
        width: 26px;
        height: 26px;
        color: var(--dq-gold);
        opacity: 0.85;
    }

    .dq__art-accent--tl {
        top: 14%;
        left: 6%;
    }

    .dq__art-accent--br {
        bottom: 10%;
        right: 4%;
    }

    @media (max-width: 860px) {
        .dq__body {
            grid-template-columns: 1fr;
            gap: 36px;
        }

        .dq__divider {
            width: 100%;
            height: 1px;
            background: linear-gradient(to right, transparent, var(--dq-line) 20%, var(--dq-line) 80%, transparent);
        }

        .dq__art {
            order: -1;
        }

        .dq__art-accent {
            display: none;
        }
    }

    @media (max-width: 480px) {
        .dq__title {
            font-size: 24px;
        }

        .dq__doshas {
            gap: 20px;
        }

        .dq__art-ring {
            width: 220px;
            height: 220px;
        }

        .dq__art-main {
            width: 160px;
            height: 160px;
        }
    }
</style>

<section class="dq">
    <div class="container">
        <div class="dq__header">
            <h2 class="dq__title">Don't Know Your Dosha Yet?</h2>
            <p class="dq__subtitle">Let's find out with a quiz</p>
            <span class="dq__flourish" aria-hidden="true"></span>
        </div>

        <div class="dq__body">
            <div class="dq__col dq__col--text">
                <p class="dq__eyebrow">Do you know your body type?</p>
                <p class="dq__text">According to Ayurveda, there are three main metabolic body types (physiologies), that form the basic framework of a person.</p>
                <p class="dq__text">These are the essential forces behind an individual's physical, mental, and emotional makeup. It is imperative to map your needs to your dosha type to get the best results. There are three basic doshas every individual inherits, either a combination or any one of these.</p>

                <div class="dq__doshas">
                    <div class="dq__dosha">
                        <span class="dq__dosha-icon">
                            <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">
                                <path d="M24 15c-5.5 0-9 3.3-9 7.5S18.3 30 22 30s6-2.2 6-5-1.8-4.5-4.5-4.5-4 1.5-4 3.3" />
                            </svg>
                        </span>
                        <span>Vata Dosha</span>
                    </div>
                    <div class="dq__dosha">
                        <span class="dq__dosha-icon">
                            <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">
                                <path d="M24 13c3 4.5 3 9 0 12.5-3-3.5-3-8 0-12.5z" />
                                <path d="M17.5 21c2 2.7 2 5.6 0 7.5" />
                                <path d="M30.5 21c-2 2.7-2 5.6 0 7.5" />
                            </svg>
                        </span>
                        <span>Pitta Dosha</span>
                    </div>
                    <div class="dq__dosha">
                        <span class="dq__dosha-icon">
                            <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">
                                <path d="M24 31v-8.5" />
                                <path d="M24 22.5c0-3.3-2.4-5.8-6-6.2 .3 3.8 2.6 6.2 6 6.2z" />
                                <path d="M24 26c0-2.8 2-5 5-5.3-.3 3.2-2.2 5.3-5 5.3z" />
                                <ellipse cx="24" cy="15.5" rx="2.1" ry="2.6" />
                            </svg>
                        </span>
                        <span>Kapha Dosha</span>
                    </div>
                </div>

                <a href="<?php echo htmlspecialchars($doshaQuizUrl, ENT_QUOTES, 'UTF-8'); ?>" class="dq__cta">
                    Discover Your Dosha
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            </div>

            <div class="dq__divider" aria-hidden="true"></div>

            <div class="dq__col dq__col--art">
                <div class="dq__art">
                    <div class="dq__art-ring">
                        <svg class="dq__art-accent dq__art-accent--tl" viewBox="0 0 40 40" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true">
                            <g transform="translate(20 20)">
                                <path d="M0 -14 L3.3 -4.5 13 -4.5 5.2 1.7 8 11.5 0 5.5 -8 11.5 -5.2 1.7 -13 -4.5 -3.3 -4.5 Z" />
                            </g>
                        </svg>

                        <svg class="dq__art-main" viewBox="0 0 220 220" fill="none" stroke="currentColor" stroke-width="1.3" aria-hidden="true">
                            <!-- stem + top leaf -->
                            <line x1="110" y1="10" x2="110" y2="34" stroke-dasharray="1 5" stroke-linecap="round" />
                            <path d="M110 34c-8 3-8 12 0 16 8-4 8-13 0-16z" />

                            <!-- canopy arcs -->
                            <path d="M55 95c0-30 24.6-54.5 55-54.5S165 65 165 95" />
                            <path d="M70 100c0-22 17.9-39.5 40-39.5s40 17.5 40 39.5" />

                            <!-- central lotus -->
                            <g transform="translate(110 118)">
                                <path d="M0 -26c6 8 6 18 0 26-6-8-6-18 0-26z" />
                                <path d="M-16 -14c8 5 12 14 10 24-9-2-16-10-16-19 0-2 2-4 6-5z" />
                                <path d="M16 -14c-8 5-12 14-10 24 9-2 16-10 16-19 0-2-2-4-6-5z" />
                                <path d="M-22 4c8 1 15 6 18 14-9 3-19 1-24-6-2-3-1-6 6-8z" />
                                <path d="M22 4c-8 1-15 6-18 14 9 3 19 1 24-6 2-3 1-6-6-8z" />
                                <circle cx="0" cy="6" r="3.2" fill="currentColor" stroke="none" />
                            </g>

                            <!-- left: spiral (vata) -->
                            <path d="M52 150c-7 0-11 4.4-11 9.7S45 169 50 169s7.7-3 7.7-6.7-2.5-6-6-6-4.7 1.8-4.7 4" />

                            <!-- right: sprout (kapha) -->
                            <path d="M168 168v-11" />
                            <path d="M168 157c0-4.3-3.1-7.6-7.8-8.1.4 5 3.4 8.1 7.8 8.1z" />
                            <path d="M168 161c0-3.7 2.6-6.5 6.5-6.9-.3 4.2-2.9 6.9-6.5 6.9z" />

                            <!-- base arc connecting them -->
                            <path d="M40 178c22 12 118 12 140 0" />
                        </svg>

                        <svg class="dq__art-accent dq__art-accent--br" viewBox="0 0 40 40" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true">
                            <g transform="translate(20 20)">
                                <path d="M0 -14 L3.3 -4.5 13 -4.5 5.2 1.7 8 11.5 0 5.5 -8 11.5 -5.2 1.7 -13 -4.5 -3.3 -4.5 Z" />
                            </g>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>