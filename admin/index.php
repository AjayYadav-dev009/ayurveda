<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../function/admin-login.php';

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';

include __DIR__ . '/include/header.php';

/* ---------------------------------------------------------------------------
   Live counts for the stat cards. Each entry reads COUNT(*) from one table.
   If a table doesn't exist under that name, its card is simply left out —
   change the 'table' values below to match your database.
   ------------------------------------------------------------------------ */
$dashStats = [
    ['key' => 'products',   'label' => 'Products',      'table' => 'products'],
    ['key' => 'categories', 'label' => 'Categories',    'table' => 'categories'],
    ['key' => 'orders',     'label' => 'Orders',        'table' => 'orders'],
    ['key' => 'users',      'label' => 'Customers',     'table' => 'users'],
    ['key' => 'team',       'label' => 'Team members',  'table' => 'team_members'],
    ['key' => 'blog',       'label' => 'Blog posts',    'table' => 'blogs'],
];

if (!function_exists('dashCount')) {
    /** Returns the row count of $table, or null if it can't be read. */
    function dashCount($conn, $table)
    {
        if (!$conn || !preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            return null;
        }
        try {
            if ($conn instanceof PDO) {
                $stmt = $conn->query('SELECT COUNT(*) FROM `' . $table . '`');
                return $stmt ? (int) $stmt->fetchColumn() : null;
            }
            if ($conn instanceof mysqli) {
                $result = $conn->query('SELECT COUNT(*) FROM `' . $table . '`');
                if (!$result) {
                    return null;
                }
                $row = $result->fetch_row();
                return (int) $row[0];
            }
        } catch (Throwable $e) {
            return null;
        }
        return null;
    }
}

$dbConn = isset($conn) ? $conn : null;
$statCards = [];
foreach ($dashStats as $stat) {
    $count = dashCount($dbConn, $stat['table']);
    if ($count !== null && isset($adminNavIndex[$stat['key']])) {
        $statCards[] = $stat + ['count' => $count, 'href' => $adminNavIndex[$stat['key']]['href']];
    }
}

$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$firstName = trim(explode(' ', $adminName)[0] ?? '') ?: 'Admin';

$quickKeys = ['products', 'orders', 'banners', 'reviews', 'promo', 'transformations', 'team', 'blog', 'categories', 'settings'];
$tints = [
    ['#e3efe8', '#17483d'],
    ['#f7efdc', '#8a6a2b'],
    ['#e7f0f7', '#0f6fb0'],
    ['#eef2e2', '#5b7a2e'],
];
?>

<style>
    .dash {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        gap: 28px;
    }

    /* ---- Welcome hero ---- */

    .dash-hero {
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        padding: 34px 38px;
        border-radius: 20px;
        color: var(--adm-cream);
        background:
            radial-gradient(rgba(244, 239, 226, 0.09) 1.2px, transparent 1.4px) 0 0 / 22px 22px,
            radial-gradient(ellipse 50% 90% at 92% 40%, rgba(145, 169, 107, 0.3), rgba(145, 169, 107, 0) 70%),
            linear-gradient(135deg, #17483d 0%, #10382f 100%);
        box-shadow: 0 14px 34px rgba(23, 72, 61, 0.2);
    }

    .dash-hero::after {
        content: "";
        position: absolute;
        right: -70px;
        top: -90px;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        border: 1px dashed rgba(216, 182, 120, 0.4);
        pointer-events: none;
    }

    .dash-hero__date {
        margin: 0 0 8px;
        font-size: 12.5px;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: var(--adm-gold);
    }

    .dash-hero__title {
        margin: 0 0 8px;
        font-size: 30px;
        line-height: 1.2;
        font-weight: 800;
        letter-spacing: -0.015em;
        color: #fff;
    }

    .dash-hero__sub {
        margin: 0;
        max-width: 480px;
        font-size: 15px;
        line-height: 1.6;
        color: rgba(244, 239, 226, 0.8);
    }

    .dash-hero__actions {
        position: relative;
        z-index: 1;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        flex-shrink: 0;
    }

    .dash-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 20px;
        border-radius: 999px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        transition: transform 0.15s ease, background 0.15s ease;
    }

    .dash-btn svg {
        width: 16px;
        height: 16px;
    }

    .dash-btn:hover {
        transform: translateY(-2px);
    }

    .dash-btn--solid {
        background: var(--adm-cream);
        color: var(--adm-side);
    }

    .dash-btn--solid:hover {
        background: #fff;
    }

    .dash-btn--ghost {
        border: 1px solid rgba(244, 239, 226, 0.4);
        color: var(--adm-cream);
    }

    .dash-btn--ghost:hover {
        background: rgba(244, 239, 226, 0.1);
    }

    /* ---- Section headings ---- */

    .dash-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
        margin: 0 0 14px;
    }

    .dash-head h2 {
        margin: 0;
        font-size: 17px;
        font-weight: 800;
        letter-spacing: -0.01em;
        color: var(--ink);
    }

    .dash-head span {
        font-size: 13px;
        color: var(--muted);
    }

    /* ---- Stat cards ---- */

    .dash-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 16px;
    }

    .dash-stat {
        display: flex;
        flex-direction: column;
        gap: 16px;
        padding: 20px;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 16px;
        color: inherit;
        text-decoration: none;
        box-shadow: 0 1px 2px rgba(23, 72, 61, 0.04);
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }

    .dash-stat:hover {
        transform: translateY(-3px);
        border-color: #c9d8cd;
        box-shadow: 0 12px 26px rgba(23, 72, 61, 0.1);
    }

    .dash-stat:focus-visible,
    .dash-tile:focus-visible {
        outline: 2px solid var(--adm-side);
        outline-offset: 3px;
    }

    .dash-icon {
        display: grid;
        place-items: center;
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        border-radius: 12px;
    }

    .dash-icon svg {
        width: 22px;
        height: 22px;
    }

    .dash-stat__value {
        display: block;
        font-size: 32px;
        line-height: 1;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--ink);
    }

    .dash-stat__label {
        display: block;
        margin-top: 6px;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--muted);
    }

    .dash-stat__link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: auto;
        font-size: 13px;
        font-weight: 700;
        color: var(--adm-side);
    }

    .dash-stat__link svg,
    .dash-tile__arrow svg {
        width: 15px;
        height: 15px;
        transition: transform 0.18s ease;
    }

    .dash-stat:hover .dash-stat__link svg,
    .dash-tile:hover .dash-tile__arrow svg {
        transform: translateX(3px);
    }

    /* ---- Body: quick actions + side panel ---- */

    .dash-cols {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 320px;
        gap: 24px;
        align-items: start;
    }

    .dash-tiles {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
        gap: 14px;
    }

    .dash-tile {
        position: relative;
        display: flex;
        gap: 14px;
        padding: 18px;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 16px;
        color: inherit;
        text-decoration: none;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }

    .dash-tile:hover {
        transform: translateY(-3px);
        border-color: #c9d8cd;
        box-shadow: 0 12px 26px rgba(23, 72, 61, 0.1);
    }

    .dash-tile__title {
        display: block;
        margin-bottom: 4px;
        font-size: 14.5px;
        font-weight: 800;
        color: var(--ink);
    }

    .dash-tile__desc {
        display: block;
        font-size: 12.8px;
        line-height: 1.5;
        color: var(--muted);
    }

    .dash-tile__arrow {
        position: absolute;
        top: 16px;
        right: 16px;
        color: #a9bcae;
    }

    .dash-tile:hover .dash-tile__arrow {
        color: var(--adm-side);
    }

    .dash-panel {
        padding: 22px;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 16px;
    }

    .dash-panel + .dash-panel {
        margin-top: 16px;
    }

    .dash-panel h3 {
        margin: 0 0 14px;
        font-size: 15px;
        font-weight: 800;
    }

    .dash-account {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 16px;
        margin-bottom: 14px;
        border-bottom: 1px solid var(--line);
    }

    .dash-account .avatar {
        width: 44px;
        height: 44px;
        font-size: 17px;
        background: var(--adm-side);
        color: var(--adm-gold);
    }

    .dash-account strong,
    .dash-account small {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dash-account strong {
        font-size: 14.5px;
    }

    .dash-account small {
        margin-top: 2px;
        font-size: 12.5px;
        color: var(--muted);
    }

    .dash-links {
        display: grid;
        gap: 4px;
    }

    .dash-links a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        margin: 0 -12px;
        border-radius: 10px;
        color: var(--ink);
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
    }

    .dash-links a:hover {
        background: #eef4ef;
        color: var(--adm-side);
    }

    .dash-links svg {
        width: 17px;
        height: 17px;
        color: var(--muted);
    }

    .dash-tip {
        margin: 0;
        font-size: 13px;
        line-height: 1.6;
        color: var(--muted);
    }

    .dash-empty {
        padding: 18px 20px;
        border: 1px dashed #c9d8cd;
        border-radius: 14px;
        background: #fafcf9;
        font-size: 13.5px;
        line-height: 1.6;
        color: var(--muted);
    }

    .dash-empty code {
        padding: 2px 6px;
        border-radius: 6px;
        background: #eef4ef;
        font-size: 12.5px;
        color: var(--adm-side);
    }

    @media (max-width: 1000px) {
        .dash-cols {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .dash {
            gap: 22px;
        }

        .dash-hero {
            flex-direction: column;
            align-items: flex-start;
            padding: 26px 22px;
        }

        .dash-hero__title {
            font-size: 24px;
        }

        .dash-stats {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .dash-stat {
            padding: 16px;
            gap: 12px;
        }

        .dash-stat__value {
            font-size: 26px;
        }

        .dash-tiles {
            grid-template-columns: 1fr;
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .dash-stat,
        .dash-tile,
        .dash-btn,
        .dash-stat__link svg,
        .dash-tile__arrow svg {
            transition: none;
        }
    }
</style>

<?php $arrowSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>'; ?>

<div class="dash">

    <section class="dash-hero">
        <div>
            <p class="dash-hero__date"><?= htmlspecialchars(date('l, j F Y')) ?></p>
            <h1 class="dash-hero__title"><?= htmlspecialchars($greeting) ?>, <?= htmlspecialchars($firstName) ?></h1>
            <p class="dash-hero__sub">Here's a quick look at your store. Jump into any section below to update products, homepage content and orders.</p>
        </div>
        <div class="dash-hero__actions">
            <a class="dash-btn dash-btn--solid" href="<?= $adminUrl('products/index.php') ?>">
                <?= $adminIcon('products') ?> Manage products
            </a>
            <a class="dash-btn dash-btn--ghost" href="<?= htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                View store
            </a>
        </div>
    </section>

    <section aria-labelledby="dash-overview">
        <div class="dash-head">
            <h2 id="dash-overview">Overview</h2>
            <span>Live counts from your store</span>
        </div>

        <?php if ($statCards): ?>
            <div class="dash-stats">
                <?php foreach ($statCards as $i => $card):
                    [$tintBg, $tintFg] = $tints[$i % count($tints)];
                ?>
                    <a class="dash-stat" href="<?= $adminUrl($card['href']) ?>">
                        <span class="dash-icon" style="background: <?= $tintBg ?>; color: <?= $tintFg ?>;"><?= $adminIcon($card['key']) ?></span>
                        <span>
                            <span class="dash-stat__value"><?= number_format($card['count']) ?></span>
                            <span class="dash-stat__label"><?= htmlspecialchars($card['label']) ?></span>
                        </span>
                        <span class="dash-stat__link">Manage <?= $arrowSvg ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="dash-empty">
                Live counts will appear here once the table names in <code>$dashStats</code> (top of <code>admin/index.php</code>) match your database.
            </div>
        <?php endif; ?>
    </section>

    <div class="dash-cols">
        <section aria-labelledby="dash-quick">
            <div class="dash-head">
                <h2 id="dash-quick">Quick actions</h2>
                <span>Jump to a section</span>
            </div>

            <div class="dash-tiles">
                <?php foreach ($quickKeys as $i => $key):
                    if (!isset($adminNavIndex[$key])) {
                        continue;
                    }
                    $item = $adminNavIndex[$key];
                    [$tintBg, $tintFg] = $tints[$i % count($tints)];
                ?>
                    <a class="dash-tile" href="<?= $adminUrl($item['href']) ?>">
                        <span class="dash-icon" style="background: <?= $tintBg ?>; color: <?= $tintFg ?>;"><?= $adminIcon($key) ?></span>
                        <span>
                            <span class="dash-tile__title"><?= htmlspecialchars($item['label']) ?></span>
                            <span class="dash-tile__desc"><?= htmlspecialchars($item['desc']) ?></span>
                        </span>
                        <span class="dash-tile__arrow"><?= $arrowSvg ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <aside>
            <div class="dash-panel">
                <div class="dash-account">
                    <span class="avatar" aria-hidden="true"><?= htmlspecialchars($adminInitial) ?></span>
                    <span style="min-width:0">
                        <strong><?= htmlspecialchars($adminName) ?></strong>
                        <?php if ($adminEmail !== ''): ?>
                            <small><?= htmlspecialchars($adminEmail) ?></small>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="dash-links">
                    <a href="<?= htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l1.5-5h15L21 9"/><path d="M3 9a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/><path d="M5 12v8h14v-8"/></svg>
                        Open storefront
                    </a>
                    <a href="<?= $logoutUrl ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                        Sign out
                    </a>
                </div>
            </div>

            <div class="dash-panel">
                <h3>Handy to know</h3>
                <p class="dash-tip">Use the button at the top-left to collapse the sidebar to icons when you need more room. On phones it opens as a slide-in menu.</p>
            </div>
        </aside>
    </div>

</div>

<?php include __DIR__ . '/include/footer.php'; ?>