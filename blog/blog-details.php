<?php

/**
 * Storefront: single blog post page  (blog-details.php?slug=post-slug)
 *
 * Assumes this file lives in the project root, next to config/, function/
 * and includes/. Adjust the paths below if yours differs.
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/function/blog.php';

// ---- Site-specific settings (edit these) -------------------------------
const BD_LIST_URL     = 'blog.php';                          // blog listing page
const BD_HOME_URL     = 'index.php';
const BD_HERO_IMAGE   = '/assets/images/blog-hero.jpg';      // decorative hero art (right side)
const BD_NEWSLETTER   = 'newsletter-subscribe.php';          // form action for the subscribe box
const BD_AUTHOR = [                                          // schema has no author column yet
    'name'  => 'Dr. Radhika Tiwari',
    'role'  => 'Ayurveda Practitioner',
    'bio'   => 'Dr. Radhika Tiwari has over 12 years of experience in Ayurvedic practice, specialising in digestive health and lifestyle management.',
    'photo' => '',                                           // e.g. '/assets/images/authors/radhika.jpg'
];
// -------------------------------------------------------------------------

$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$postUrl = fn($slug) => 'blog-details.php?slug=' . rawurlencode($slug);
$fmtDate = fn($d) => $d ? date('M j, Y', strtotime($d)) : '';

$slug = trim((string) ($_GET['slug'] ?? ''));
$post = $slug !== '' ? getPublishedBlogPostBySlug($conn, $slug) : null;

if (!$post) {
    http_response_code(404);
    $pageTitle = 'Post not found';
} else {
    $pageTitle       = $post['meta_title'] ?: $post['title'];
    $metaDescription = $post['meta_description'] ?: strip_tags((string) $post['excerpt']);
    $ogImage         = getBlogImageUrl($post['image']);

    $body     = prepareBlogContent($post['content']);
    $readTime = estimateBlogReadTime($post['content']);
    $adjacent = getAdjacentPublishedBlogPosts($conn, $post);
    $related  = getRelatedPublishedBlogPosts($conn, $post, 4);
    $author   = BD_AUTHOR;
    $initials = implode('', array_map(fn($w) => mb_substr($w, 0, 1), array_slice(preg_split('/\s+/', preg_replace('/^Dr\.?\s+/i', '', $author['name'])), 0, 2)));
}

$headerFile = __DIR__ . '/includes/header.php';
$footerFile = __DIR__ . '/includes/footer.php';
$hasChrome  = is_file($headerFile);

if ($hasChrome) {
    include $headerFile;
} else { ?>
    <!doctype html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= $e($pageTitle) ?></title>
        <?php if (!empty($metaDescription)): ?>
            <meta name="description" content="<?= $e($metaDescription) ?>"><?php endif; ?>
        <meta property="og:title" content="<?= $e($pageTitle) ?>">
        <?php if (!empty($ogImage)): ?>
            <meta property="og:image" content="<?= $e($ogImage) ?>"><?php endif; ?>
    </head>

    <body>
    <?php } ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">

    <style>
        .bd {
            --bd-bg: #f6f3ec;
            --bd-ink: #1c3a30;
            --bd-text: #45524c;
            --bd-muted: #7d8780;
            --bd-gold: #b98a2e;
            --bd-sage: #dde4d6;
            --bd-sage-soft: #ebeee4;
            --bd-line: #e3ded1;
            --bd-dark: #163429;
            background: var(--bd-bg);
            color: var(--bd-text);
            font-family: 'DM Sans', system-ui, sans-serif;
            line-height: 1.7;
            scroll-behavior: smooth;
        }

        .bd *,
        .bd *::before,
        .bd *::after {
            box-sizing: border-box;
        }

        .bd a {
            color: inherit;
        }

        .bd :focus-visible {
            outline: 2px solid var(--bd-gold);
            outline-offset: 3px;
        }

        .bd-wrap {
            width: min(1180px, 100% - 48px);
            margin-inline: auto;
        }

        .bd-serif {
            font-family: 'DM Serif Display', Georgia, serif;
            font-weight: 400;
            color: var(--bd-ink);
        }

        .bd svg {
            width: 1em;
            height: 1em;
            flex: none;
        }

        /* Hero */
        .bd-hero {
            position: relative;
            overflow: hidden;
            min-height: 300px;
            display: flex;
            align-items: center;
            padding: 32px 0;
            background: linear-gradient(135deg, #e9eddf, #f6f3ec 60%);
        }

        .bd-hero::after {
            content: '';
            position: absolute;
            inset: 0 0 0 46%;
            background: url('<?= $e(BD_HERO_IMAGE) ?>') center / cover, linear-gradient(135deg, #b7c8ab, #6f8f6a);
            -webkit-mask-image: linear-gradient(to right, transparent, #000 38%);
            mask-image: linear-gradient(to right, transparent, #000 38%);
        }

        .bd-hero .bd-wrap {
            position: relative;
            z-index: 1;
        }

        .bd-hero-copy {
            max-width: 520px;
        }

        .bd-crumbs {
            font-size: .72rem;
            color: var(--bd-muted);
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .bd-crumbs a {
            color: var(--bd-ink);
        }

        .bd-pill {
            display: inline-block;
            padding: 4px 14px;
            border: 1px solid #d9c9a0;
            border-radius: 999px;
            background: #f3ecd8;
            color: #6b5a2c;
            font-size: .7rem;
            letter-spacing: .06em;
            text-transform: uppercase;
            text-decoration: none;
        }

        .bd-hero h1 {
            font-size: clamp(2rem, 4.4vw, 3.1rem);
            line-height: 1.12;
            margin: 16px 0 14px;
        }

        .bd-lede {
            font-size: 1.02rem;
            margin: 0 0 22px;
            max-width: 440px;
        }

        .bd-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 26px;
            font-size: .84rem;
            align-items: center;
        }

        .bd-meta span,
        .bd-meta button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .bd-meta svg {
            font-size: 1.15rem;
            color: var(--bd-ink);
        }

        .bd-share {
            background: none;
            border: 0;
            padding: 0;
            font: inherit;
            color: inherit;
            cursor: pointer;
        }

        /* Layout */
        .bd-main {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 360px;
            gap: 64px;
            padding: 44px 0 56px;
        }

        .bd-feature {
            width: 100%;
            aspect-ratio: 2 / 1;
            object-fit: cover;
            border-radius: 10px;
            display: block;
            margin-bottom: 28px;
        }

        /* Article body (CKEditor output) */
        .bd-body {
            counter-reset: step;
            font-size: .98rem;
        }

        .bd-body>p:first-child {
            font-size: 1.05rem;
        }

        .bd-body p,
        .bd-body ul,
        .bd-body ol {
            margin: 0 0 16px;
        }

        .bd-body h2 {
            counter-increment: step;
            position: relative;
            margin: 34px 0 6px;
            padding-left: 66px;
            font: 400 1.55rem/1.25 'DM Serif Display', Georgia, serif;
            color: var(--bd-ink);
            scroll-margin-top: 90px;
        }

        .bd-body h2::before {
            content: counter(step);
            position: absolute;
            left: 0;
            top: -8px;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--bd-sage-soft);
            display: grid;
            place-items: center;
            font-size: 1.15rem;
        }

        .bd-body h2+p,
        .bd-body h2+ul,
        .bd-body h2+ol {
            padding-left: 66px;
            font-size: .92rem;
        }

        .bd-body h3 {
            font: 400 1.25rem/1.3 'DM Serif Display', Georgia, serif;
            color: var(--bd-ink);
            margin: 26px 0 8px;
        }

        .bd-body img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .bd-body a {
            color: var(--bd-gold);
        }

        .bd-body blockquote {
            position: relative;
            margin: 34px 0;
            padding: 26px 30px 26px 96px;
            background: var(--bd-sage-soft);
            border-radius: 8px;
        }

        .bd-body blockquote::before {
            content: '';
            position: absolute;
            left: 30px;
            top: 50%;
            translate: 0 -50%;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--bd-sage) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%231c3a30'%3E%3Cpath d='M20 3C10 3 4 8 4 15c0 1.6.4 3 1 4.2C6.500 14 10 10.500 15 9 10.500 11 8 14.500 7 19.500 8 20.500 9.500 21 11 21c6 0 9-5 9-18z'/%3E%3C/svg%3E") center / 22px no-repeat;
        }

        .bd-body blockquote p {
            margin: 0;
            font: italic 1.02rem/1.6 'DM Serif Display', Georgia, serif;
            color: var(--bd-ink);
        }

        /* Prev / next */
        .bd-adjacent {
            display: grid;
            grid-template-columns: 1fr 1fr;
            margin-top: 44px;
            border: 1px solid var(--bd-line);
            border-radius: 8px;
        }

        .bd-adjacent a {
            padding: 16px 20px;
            text-decoration: none;
            font-size: .82rem;
        }

        .bd-adjacent a+a,
        .bd-adjacent span+a {
            border-left: 1px solid var(--bd-line);
            text-align: right;
        }

        .bd-adjacent small {
            display: block;
            color: var(--bd-muted);
            font-size: .8rem;
        }

        .bd-adjacent strong {
            color: var(--bd-ink);
            font-weight: 500;
        }

        .bd-adjacent a:hover strong {
            color: var(--bd-gold);
        }

        /* Sidebar */
        .bd-side>*+* {
            margin-top: 28px;
        }

        .bd-card {
            border: 1px solid var(--bd-line);
            border-radius: 10px;
            padding: 24px;
        }

        .bd-author {
            display: grid;
            grid-template-columns: 76px 1fr;
            gap: 20px;
        }

        .bd-avatar {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            object-fit: cover;
            background: var(--bd-sage);
            display: grid;
            place-items: center;
            font: 400 1.5rem 'DM Serif Display', serif;
            color: var(--bd-ink);
        }

        .bd-author p {
            margin: 0;
            font-size: .78rem;
            line-height: 1.6;
        }

        .bd-author .bd-label {
            font-size: .88rem;
            color: var(--bd-muted);
        }

        .bd-author .bd-name {
            font: 400 1.05rem 'DM Serif Display', serif;
            color: var(--bd-ink);
        }

        .bd-author .bd-role {
            font-size: .68rem;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--bd-muted);
            margin-bottom: 12px;
        }

        .bd-author a {
            display: inline-block;
            margin-top: 12px;
            font-size: .78rem;
            font-weight: 500;
            color: var(--bd-ink);
        }

        .bd-toc {
            background: var(--bd-sage-soft);
            border-color: transparent;
        }

        .bd-toc h2,
        .bd-related h2 {
            font-size: 1.15rem;
            margin: 0 0 16px;
        }

        .bd-toc ol {
            list-style: none;
            margin: 0;
            padding: 0;
            counter-reset: toc;
            display: grid;
            gap: 10px;
        }

        .bd-toc a {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            font-size: .8rem;
            counter-increment: toc;
        }

        .bd-toc a::before {
            content: counter(toc);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #b9c9b3;
            color: var(--bd-ink);
            display: grid;
            place-items: center;
            font-size: .8rem;
            flex: none;
        }

        .bd-toc a:hover {
            color: var(--bd-gold);
        }

        .bd-related h2 {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .bd-related h2::after {
            content: '';
            width: 44px;
            height: 2px;
            background: var(--bd-gold);
        }

        .bd-related ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 22px;
        }

        .bd-related a {
            display: grid;
            grid-template-columns: 88px 1fr;
            gap: 16px;
            align-items: center;
            text-decoration: none;
        }

        .bd-thumb {
            width: 88px;
            height: 64px;
            border-radius: 6px;
            object-fit: cover;
            background: var(--bd-sage);
            display: block;
        }

        .bd-related strong {
            display: block;
            color: var(--bd-ink);
            font-size: .86rem;
            font-weight: 600;
            line-height: 1.35;
        }

        .bd-related a:hover strong {
            color: var(--bd-gold);
        }

        .bd-related small {
            color: var(--bd-muted);
            font-size: .72rem;
        }

        /* Newsletter */
        .bd-news {
            background: var(--bd-dark);
            color: #d5dfd8;
            padding: 40px 0;
        }

        .bd-news .bd-wrap {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            align-items: center;
        }

        .bd-news h2 {
            color: #fff;
            font-size: 1.75rem;
            margin: 0 0 8px;
        }

        .bd-news p {
            margin: 0;
            font-size: .85rem;
            max-width: 340px;
        }

        .bd-news small {
            color: #d1a94f;
            font-size: .68rem;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .bd-news form {
            display: flex;
            gap: 12px;
        }

        .bd-news label {
            flex: 1;
            position: relative;
        }

        .bd-news input[type=email] {
            width: 100%;
            height: 44px;
            border: 0;
            border-radius: 6px;
            padding: 0 14px;
            font: inherit;
            font-size: .84rem;
            background: #f6f3ec;
        }

        .bd-news button {
            height: 44px;
            padding: 0 26px;
            border: 0;
            border-radius: 6px;
            background: var(--bd-gold);
            color: #fff;
            font: 600 .86rem 'DM Sans', sans-serif;
            cursor: pointer;
        }

        .bd-news button:hover {
            background: #a37824;
        }

        .bd-empty {
            padding: 96px 0;
            text-align: center;
        }

        @media (max-width: 960px) {
            .bd-main {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .bd-hero::after {
                inset: 0;
                opacity: .25;
                -webkit-mask-image: none;
                mask-image: none;
            }

            .bd-news .bd-wrap {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 560px) {
            .bd-body h2 {
                padding-left: 54px;
                font-size: 1.3rem;
            }

            .bd-body h2::before {
                width: 38px;
                height: 38px;
            }

            .bd-body h2+p,
            .bd-body h2+ul,
            .bd-body h2+ol {
                padding-left: 54px;
            }

            .bd-body blockquote {
                padding: 82px 22px 22px;
            }

            .bd-body blockquote::before {
                top: 24px;
                left: 22px;
                translate: none;
            }

            .bd-news form {
                flex-direction: column;
            }

            .bd-adjacent {
                grid-template-columns: 1fr;
            }

            .bd-adjacent a+a,
            .bd-adjacent span+a {
                border-left: 0;
                border-top: 1px solid var(--bd-line);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .bd {
                scroll-behavior: auto;
            }
        }
    </style>

    <div class="bd">
        <?php if (!$post): ?>
            <div class="bd-wrap bd-empty">
                <h1 class="bd-serif">This post isn't available</h1>
                <p>It may have been moved or unpublished.</p>
                <p><a href="<?= $e(BD_LIST_URL) ?>">Browse all articles</a></p>
            </div>
        <?php else: ?>

            <header class="bd-hero">
                <div class="bd-wrap">
                    <div class="bd-hero-copy">
                        <nav class="bd-crumbs" aria-label="Breadcrumb">
                            <a href="<?= $e(BD_HOME_URL) ?>">Home</a> &rsaquo;
                            <a href="<?= $e(BD_LIST_URL) ?>">Blog</a> &rsaquo;
                            <?php if ($post['category_name']): ?>
                                <a href="<?= $e(BD_LIST_URL . '?category=' . rawurlencode($post['category_slug'])) ?>"><?= $e($post['category_name']) ?></a> &rsaquo;
                            <?php endif; ?>
                            <span aria-current="page"><?= $e($post['title']) ?></span>
                        </nav>

                        <?php if ($post['category_name']): ?>
                            <a class="bd-pill" href="<?= $e(BD_LIST_URL . '?category=' . rawurlencode($post['category_slug'])) ?>"><?= $e($post['category_name']) ?></a>
                        <?php endif; ?>

                        <h1 class="bd-serif"><?= $e($post['title']) ?></h1>
                        <?php if (trim((string) $post['excerpt']) !== ''): ?>
                            <p class="bd-lede"><?= $e($post['excerpt']) ?></p>
                        <?php endif; ?>

                        <div class="bd-meta">
                            <span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                    <rect x="3.500" y="5" width="17" height="15.500" rx="2" />
                                    <path d="M3.500 10h17M8 3v4M16 3v4" />
                                </svg>
                                <time datetime="<?= $e(date('Y-m-d', strtotime($post['published_at']))) ?>"><?= $e($fmtDate($post['published_at'])) ?></time>
                            </span>
                            <span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                    <circle cx="12" cy="12" r="8.500" />
                                    <path d="M12 7.500V12l3 2" />
                                </svg>
                                <?= (int) $readTime ?> min read
                            </span>
                            <button type="button" class="bd-share" id="bd-share">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                    <circle cx="6" cy="12" r="2.500" />
                                    <circle cx="17.500" cy="6" r="2.500" />
                                    <circle cx="17.500" cy="18" r="2.500" />
                                    <path d="M8.300 10.800l7-3.600M8.300 13.200l7 3.600" />
                                </svg>
                                <span id="bd-share-label">Share</span>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <div class="bd-wrap bd-main">
                <article>
                    <?php if (!empty($post['image'])): ?>
                        <img class="bd-feature" src="<?= $e(getBlogImageUrl($post['image'])) ?>" alt="<?= $e($post['title']) ?>">
                    <?php endif; ?>

                    <div class="bd-body"><?= $body['html'] /* trusted admin-authored CKEditor HTML, same as edit.php */ ?></div>

                    <?php if ($adjacent['prev'] || $adjacent['next']): ?>
                        <nav class="bd-adjacent" aria-label="More posts">
                            <?php if ($adjacent['prev']): ?>
                                <a href="<?= $e($postUrl($adjacent['prev']['slug'])) ?>" rel="prev">
                                    <small>&larr; Previous post</small>
                                    <strong><?= $e($adjacent['prev']['title']) ?></strong>
                                </a>
                            <?php else: ?><span></span><?php endif; ?>
                            <?php if ($adjacent['next']): ?>
                                <a href="<?= $e($postUrl($adjacent['next']['slug'])) ?>" rel="next">
                                    <small>Next post &rarr;</small>
                                    <strong><?= $e($adjacent['next']['title']) ?></strong>
                                </a>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>
                </article>

                <aside class="bd-side">
                    <section class="bd-card bd-author" aria-label="Author">
                        <?php if ($author['photo']): ?>
                            <img class="bd-avatar" src="<?= $e($author['photo']) ?>" alt="<?= $e($author['name']) ?>">
                        <?php else: ?>
                            <div class="bd-avatar" aria-hidden="true"><?= $e($initials) ?></div>
                        <?php endif; ?>
                        <div>
                            <p class="bd-label">Written by</p>
                            <p class="bd-name"><?= $e($author['name']) ?></p>
                            <p class="bd-role"><?= $e($author['role']) ?></p>
                            <p><?= $e($author['bio']) ?></p>
                            <a href="<?= $e(BD_LIST_URL) ?>">View all posts &rarr;</a>
                        </div>
                    </section>

                    <?php if (count($body['toc']) > 1): ?>
                        <nav class="bd-card bd-toc" aria-label="Table of contents">
                            <h2 class="bd-serif">Table of contents</h2>
                            <ol>
                                <?php foreach ($body['toc'] as $item): ?>
                                    <li><a href="#<?= $e($item['id']) ?>"><?= $e($item['text']) ?></a></li>
                                <?php endforeach; ?>
                            </ol>
                        </nav>
                    <?php endif; ?>

                    <?php if ($related): ?>
                        <section class="bd-related" aria-label="Related posts">
                            <h2 class="bd-serif">Related posts</h2>
                            <ul>
                                <?php foreach ($related as $item): ?>
                                    <li>
                                        <a href="<?= $e($postUrl($item['slug'])) ?>">
                                            <?php if (!empty($item['image'])): ?>
                                                <img class="bd-thumb" src="<?= $e(getBlogImageUrl($item['image'])) ?>" alt="" loading="lazy">
                                            <?php else: ?>
                                                <span class="bd-thumb" aria-hidden="true"></span>
                                            <?php endif; ?>
                                            <span>
                                                <strong><?= $e($item['title']) ?></strong>
                                                <small><?= $e($fmtDate($item['published_at'])) ?> &middot; <?= (int) estimateBlogReadTime($item['content']) ?> min read</small>
                                            </span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </section>
                    <?php endif; ?>
                </aside>
            </div>

            <section class="bd-news" aria-labelledby="bd-news-title">
                <div class="bd-wrap">
                    <div>
                        <small>Your wellness matters</small>
                        <h2 class="bd-serif" id="bd-news-title">Subscribe to our newsletter</h2>
                        <p>Get the latest articles, wellness tips, and special offers delivered to your inbox.</p>
                    </div>
                    <form action="<?= $e(BD_NEWSLETTER) ?>" method="post">
                        <label>
                            <span class="sr-only" style="position:absolute;left:-9999px">Email address</span>
                            <input type="email" name="email" placeholder="Enter your email address" required>
                        </label>
                        <button type="submit">Subscribe</button>
                    </form>
                </div>
            </section>

            <script>
                (function() {
                    var btn = document.getElementById('bd-share');
                    var label = document.getElementById('bd-share-label');
                    if (!btn) return;
                    btn.addEventListener('click', function() {
                        var data = {
                            title: document.title,
                            url: location.href
                        };
                        if (navigator.share) {
                            navigator.share(data).catch(function() {});
                        } else if (navigator.clipboard) {
                            navigator.clipboard.writeText(data.url).then(function() {
                                label.textContent = 'Link copied';
                                setTimeout(function() {
                                    label.textContent = 'Share';
                                }, 2000);
                            });
                        }
                    });
                })();
            </script>
        <?php endif; ?>
    </div>

    <?php
    if ($hasChrome && is_file($footerFile)) {
        include $footerFile;
    } elseif (!$hasChrome) {
        echo "</body>\n</html>";
    }
