<?php require_once __DIR__ . '/../config/database.php'; ?>
<?php require_once __DIR__ . '/../function/blog.php'; ?>
<?php require_once __DIR__ . '/../function/blog-category.php'; ?>

<?php
$search = isset($_GET['search']) ? trim($_GET['search']) : null;
$activeCategorySlug = isset($_GET['category']) ? trim($_GET['category']) : null;

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

$perPage = 5; // 1 featured card + 4 grid cards per page, matching the design
$offset = ($page - 1) * $perPage;

$totalPosts = countPublishedBlogPosts($conn, $activeCategorySlug, $search);
$totalPages = max(1, (int) ceil($totalPosts / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$posts = getPublishedBlogPosts($conn, $perPage, $offset, $activeCategorySlug, $search);
$featuredPost = array_shift($posts); // remaining $posts feed the grid below it

$allPostsCount = countPublishedBlogPosts($conn);
$blogCategories = getBlogCategoriesWithPostCounts($conn);
$popularPosts = getRecentPublishedBlogPosts($conn, 4);

/**
 * Build a blog.php query string that preserves the current filters while
 * changing/adding the given params (e.g. page number).
 */
function blogQueryString(array $overrides = [])
{
    $params = array_filter([
        'search' => $_GET['search'] ?? null,
        'category' => $_GET['category'] ?? null,
        'page' => $_GET['page'] ?? null,
    ], fn($v) => $v !== null && $v !== '');

    $params = array_merge($params, $overrides);
    $params = array_filter($params, fn($v) => $v !== null && $v !== '');

    return http_build_query($params);
}

include __DIR__ . '/../includes/header.php';
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&display=swap');

    .blog-serif {
        font-family: 'Playfair Display', Georgia, serif;
    }

    /* ---------------- Hero ---------------- */

    .blog-hero {
        background: var(--color-primary-light);
        padding: 56px 0;
        overflow: hidden;
    }

    .blog-hero__inner {
        display: grid;
        grid-template-columns: 1.1fr 1fr;
        align-items: center;
        gap: 48px;
    }

    .blog-hero__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--color-accent);
    }

    .blog-hero__eyebrow::after {
        content: "";
        width: 34px;
        height: 2px;
        background: var(--color-accent);
    }

    .blog-hero__title {
        margin: 16px 0 18px;
        font-size: 44px;
        line-height: 1.15;
        font-weight: 700;
        color: var(--color-primary-dark);
    }

    .blog-hero__desc {
        max-width: 480px;
        margin-bottom: 26px;
        font-size: 15px;
        line-height: 1.7;
        color: var(--color-text-light);
    }

    .blog-hero__search {
        display: flex;
        align-items: center;
        gap: 10px;
        max-width: 420px;
        padding: 6px 8px 6px 18px;
        background: var(--color-white);
        border-radius: 999px;
        box-shadow: var(--shadow-soft);
    }

    .blog-hero__search svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
        color: var(--color-text-light);
    }

    .blog-hero__search input {
        flex: 1;
        border: 0;
        outline: none;
        font-size: 14px;
        font-family: inherit;
        background: transparent;
        color: var(--color-text);
    }

    .blog-hero__search button {
        padding: 10px 22px;
        border: 0;
        border-radius: 999px;
        background: var(--color-primary-dark);
        color: var(--color-white);
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
    }

    .blog-hero__image img {
        width: 100%;
        height: 340px;
        object-fit: cover;
        border-radius: var(--radius-lg) var(--radius-lg) 90px var(--radius-lg);
    }

    @media (max-width: 860px) {
        .blog-hero__inner {
            grid-template-columns: 1fr;
        }

        .blog-hero__title {
            font-size: 32px;
        }

        .blog-hero__image img {
            height: 220px;
        }
    }

    /* ---------------- Layout ---------------- */

    .blog-main {
        padding: 56px 0 72px;
    }

    .blog-main__layout {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 40px;
        align-items: start;
    }

    .blog-section-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--color-accent);
    }

    .blog-section-eyebrow::after {
        content: "";
        width: 26px;
        height: 2px;
        background: var(--color-accent);
    }

    .blog-section-title {
        margin: 10px 0 8px;
        font-size: 30px;
        font-weight: 700;
        color: var(--color-primary-dark);
    }

    .blog-section-desc {
        max-width: 560px;
        margin-bottom: 30px;
        font-size: 14px;
        line-height: 1.7;
        color: var(--color-text-light);
    }

    .blog-empty {
        padding: 40px 20px;
        text-align: center;
        color: var(--color-text-light);
        background: var(--color-white);
        border-radius: var(--radius-md);
        border: 1px solid var(--color-border);
    }

    /* ---- Card badges/meta shared by featured + grid cards ---- */

    .blog-badge {
        position: absolute;
        top: 14px;
        left: 14px;
        padding: 5px 12px;
        border-radius: 999px;
        background: var(--color-white);
        color: var(--color-primary-dark);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .blog-meta {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 10px;
        font-size: 12.5px;
        color: var(--color-text-light);
    }

    .blog-meta span {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .blog-read-more {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 12px;
        font-size: 13.5px;
        font-weight: 700;
        color: var(--color-primary-dark);
    }

    /* ---- Featured card ---- */

    .blog-featured {
        display: block;
        background: var(--color-white);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-soft);
        margin-bottom: 28px;
    }

    .blog-featured__image {
        position: relative;
    }

    .blog-featured__image img {
        width: 100%;
        height: 320px;
        object-fit: cover;
    }

    .blog-featured__body {
        padding: 26px 28px 30px;
    }

    .blog-featured__title {
        margin: 6px 0 10px;
        font-size: 24px;
        font-weight: 700;
        color: var(--color-primary-dark);
    }

    .blog-featured__excerpt {
        font-size: 14px;
        line-height: 1.7;
        color: var(--color-text-light);
    }

    /* ---- Grid ---- */

    .blog-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
        margin-bottom: 36px;
    }

    .blog-card {
        display: block;
        background: var(--color-white);
        border-radius: var(--radius-md);
        overflow: hidden;
        box-shadow: var(--shadow-soft);
    }

    .blog-card__image {
        position: relative;
    }

    .blog-card__image img {
        width: 100%;
        height: 170px;
        object-fit: cover;
    }

    .blog-card__body {
        padding: 18px 20px 22px;
    }

    .blog-card__title {
        margin: 4px 0 8px;
        font-size: 17px;
        font-weight: 700;
        color: var(--color-primary-dark);
        line-height: 1.35;
    }

    .blog-card__excerpt {
        font-size: 13.5px;
        line-height: 1.6;
        color: var(--color-text-light);
    }

    @media (max-width: 640px) {
        .blog-grid {
            grid-template-columns: 1fr;
        }
    }

    /* ---- Pagination ---- */

    .blog-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
    }

    .blog-pagination a,
    .blog-pagination span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        font-size: 13px;
        font-weight: 700;
        color: var(--color-text);
        background: var(--color-white);
        border: 1px solid var(--color-border);
    }

    .blog-pagination a:hover {
        border-color: var(--color-primary);
        color: var(--color-primary);
    }

    .blog-pagination .is-active {
        background: var(--color-primary-dark);
        border-color: var(--color-primary-dark);
        color: var(--color-white);
    }

    /* ---------------- Sidebar ---------------- */

    .blog-widget {
        background: var(--color-primary-light);
        border-radius: var(--radius-md);
        padding: 22px;
        margin-bottom: 24px;
    }

    .blog-widget__title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
        font-size: 16px;
        font-weight: 700;
        color: var(--color-primary-dark);
    }

    .blog-widget__list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .blog-widget__list a {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        border-radius: 999px;
        background: var(--color-white);
        font-size: 13.5px;
        font-weight: 600;
        color: var(--color-text);
    }

    .blog-widget__list a:hover,
    .blog-widget__list a.is-active {
        color: var(--color-primary);
    }

    .blog-widget__list a span {
        color: var(--color-text-light);
        font-weight: 700;
        font-size: 12px;
    }

    .blog-popular-item {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 14px;
    }

    .blog-popular-item:last-child {
        margin-bottom: 0;
    }

    .blog-popular-item img {
        width: 58px;
        height: 58px;
        object-fit: cover;
        border-radius: var(--radius-sm);
        flex-shrink: 0;
    }

    .blog-popular-item__title {
        font-size: 13px;
        font-weight: 700;
        line-height: 1.35;
        color: var(--color-primary-dark);
        margin-bottom: 4px;
    }

    .blog-popular-item__date {
        font-size: 11.5px;
        color: var(--color-text-light);
    }

    /* ---------------- Newsletter ---------------- */

    .blog-newsletter {
        background: var(--color-primary-light);
        padding: 0;
        overflow: hidden;
    }

    .blog-newsletter__inner {
        display: grid;
        grid-template-columns: 1fr 1.4fr;
        align-items: stretch;
    }

    .blog-newsletter__image img {
        width: 100%;
        height: 100%;
        min-height: 260px;
        object-fit: cover;
    }

    .blog-newsletter__content {
        padding: 48px;
    }

    .blog-newsletter__eyebrow {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--color-accent);
    }

    .blog-newsletter__title {
        margin: 10px 0 12px;
        font-size: 26px;
        font-weight: 700;
        color: var(--color-primary-dark);
    }

    .blog-newsletter__desc {
        max-width: 440px;
        margin-bottom: 22px;
        font-size: 14px;
        line-height: 1.7;
        color: var(--color-text-light);
    }

    .blog-newsletter__form {
        display: flex;
        gap: 10px;
        max-width: 460px;
    }

    .blog-newsletter__form input {
        flex: 1;
        padding: 13px 18px;
        border: 1px solid var(--color-border);
        border-radius: 999px;
        font-size: 14px;
        font-family: inherit;
    }

    .blog-newsletter__form button {
        padding: 13px 24px;
        border: 0;
        border-radius: 999px;
        background: var(--color-primary-dark);
        color: var(--color-white);
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
    }

    @media (max-width: 860px) {
        .blog-main__layout {
            grid-template-columns: 1fr;
        }

        .blog-newsletter__inner {
            grid-template-columns: 1fr;
        }

        .blog-newsletter__image img {
            min-height: 180px;
        }
    }
</style>

<section class="blog-hero">
    <div class="container blog-hero__inner">
        <div class="blog-hero__content">
            <span class="blog-hero__eyebrow">Our Blog</span>
            <h1 class="blog-hero__title blog-serif">Wisdom for a Healthier You</h1>
            <p class="blog-hero__desc">Explore expert insights, Ayurvedic tips, and practical guides to help you live a balanced, healthy and mindful life.</p>
            <form class="blog-hero__search" method="get" action="<?= BASE_URL ?>blog/blog.php">
                <svg viewBox="0 0 512 512" fill="currentColor" aria-hidden="true">
                    <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376C296.3 401.1 253.9 416 208 416 93.1 416 0 322.9 0 208S93.1 0 208 0 416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z" />
                </svg>
                <input type="text" name="search" placeholder="Search articles..." value="<?= htmlspecialchars($search ?? '') ?>">
                <button type="submit">Search</button>
            </form>
        </div>
        <div class="blog-hero__image">
            <img src="<?= BASE_URL ?>assets/images/blog-hero.jpg" alt="Ayurvedic herbs and mortar &amp; pestle" onerror="this.src='<?= BASE_URL ?>assets/images/placeholder.png'">
        </div>
    </div>
</section>

<section class="blog-main">
    <div class="container blog-main__layout">
        <div class="blog-main__content">
            <span class="blog-section-eyebrow">Latest Articles</span>
            <h2 class="blog-section-title blog-serif">Featured Articles</h2>
            <p class="blog-section-desc">Discover our latest blog posts, written by Ayurvedic experts to help you make informed choices for your well-being.</p>

            <?php if (!$featuredPost && empty($posts)): ?>
                <div class="blog-empty">
                    <?php if ($search || $activeCategorySlug): ?>
                        No articles match your filters. <a href="<?= BASE_URL ?>blog/blog.php">View all posts</a>.
                    <?php else: ?>
                        No blog posts have been published yet.
                    <?php endif; ?>
                </div>
            <?php else: ?>

                <?php if ($featuredPost): ?>
                    <a href="<?= BASE_URL ?>blog/blog-details.php?slug=<?= urlencode($featuredPost['slug']) ?>" class="blog-featured">
                        <div class="blog-featured__image">
                            <?php if (!empty($featuredPost['category_name'])): ?>
                                <span class="blog-badge"><?= htmlspecialchars(strtoupper($featuredPost['category_name'])) ?></span>
                            <?php endif; ?>
                            <img src="<?= htmlspecialchars(getBlogImageUrl($featuredPost['image']) ?? BASE_URL . 'assets/images/placeholder.png') ?>" alt="<?= htmlspecialchars($featuredPost['title']) ?>">
                        </div>
                        <div class="blog-featured__body">
                            <div class="blog-meta">
                                <span><?= date('M j, Y', strtotime($featuredPost['published_at'])) ?></span>
                                <span>&middot; <?= estimateBlogReadTime($featuredPost['content']) ?> min read</span>
                            </div>
                            <h3 class="blog-featured__title"><?= htmlspecialchars($featuredPost['title']) ?></h3>
                            <p class="blog-featured__excerpt"><?= htmlspecialchars($featuredPost['excerpt'] ?? '') ?></p>
                            <span class="blog-read-more">Read More &rarr;</span>
                        </div>
                    </a>
                <?php endif; ?>

                <?php if (!empty($posts)): ?>
                    <div class="blog-grid">
                        <?php foreach ($posts as $post): ?>
                            <a href="<?= BASE_URL ?>blog/blog-details.php?slug=<?= urlencode($post['slug']) ?>" class="blog-card">
                                <div class="blog-card__image">
                                    <?php if (!empty($post['category_name'])): ?>
                                        <span class="blog-badge"><?= htmlspecialchars(strtoupper($post['category_name'])) ?></span>
                                    <?php endif; ?>
                                    <img src="<?= htmlspecialchars(getBlogImageUrl($post['image']) ?? BASE_URL . 'assets/images/placeholder.png') ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                                </div>
                                <div class="blog-card__body">
                                    <div class="blog-meta">
                                        <span><?= date('M j, Y', strtotime($post['published_at'])) ?></span>
                                        <span>&middot; <?= estimateBlogReadTime($post['content']) ?> min read</span>
                                    </div>
                                    <h3 class="blog-card__title"><?= htmlspecialchars($post['title']) ?></h3>
                                    <p class="blog-card__excerpt"><?= htmlspecialchars(mb_strimwidth($post['excerpt'] ?? '', 0, 110, '...')) ?></p>
                                    <span class="blog-read-more">Read More &rarr;</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($totalPages > 1): ?>
                    <div class="blog-pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?= blogQueryString(['page' => $page - 1]) ?>" aria-label="Previous page">&lsaquo;</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i === $page): ?>
                                <span class="is-active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?<?= blogQueryString(['page' => $i]) ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?<?= blogQueryString(['page' => $page + 1]) ?>" aria-label="Next page">&rsaquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>

        <aside class="blog-main__sidebar">
            <div class="blog-widget">
                <h3 class="blog-widget__title">&#127807; Categories</h3>
                <div class="blog-widget__list">
                    <a href="<?= BASE_URL ?>blog/blog.php" class="<?= !$activeCategorySlug ? 'is-active' : '' ?>">
                        All Posts <span><?= $allPostsCount ?></span>
                    </a>
                    <?php foreach ($blogCategories as $blogCategory): ?>
                        <a href="?<?= blogQueryString(['category' => $blogCategory['slug'], 'page' => null]) ?>" class="<?= $activeCategorySlug === $blogCategory['slug'] ? 'is-active' : '' ?>">
                            <?= htmlspecialchars($blogCategory['name']) ?> <span><?= $blogCategory['post_count'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (!empty($popularPosts)): ?>
                <div class="blog-widget">
                    <h3 class="blog-widget__title">&#9733; Popular Posts</h3>
                    <?php foreach ($popularPosts as $popularPost): ?>
                        <a href="<?= BASE_URL ?>blog/blog-details.php?slug=<?= urlencode($popularPost['slug']) ?>" class="blog-popular-item">
                            <img src="<?= htmlspecialchars(getBlogImageUrl($popularPost['image']) ?? BASE_URL . 'assets/images/placeholder.png') ?>" alt="<?= htmlspecialchars($popularPost['title']) ?>">
                            <div>
                                <div class="blog-popular-item__title"><?= htmlspecialchars($popularPost['title']) ?></div>
                                <div class="blog-popular-item__date"><?= date('M j, Y', strtotime($popularPost['published_at'])) ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>