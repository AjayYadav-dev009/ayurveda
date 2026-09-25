<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../function/settings.php';
require_once __DIR__ . '/../../function/csrf.php';
require_once __DIR__ . '/../../function/helper.php';
require_once __DIR__ . '/../../includes/auth.php';

$search = trim($_GET['search'] ?? '');
$group  = trim($_GET['group'] ?? 'all');
$status = trim($_GET['status'] ?? 'all');

try {
    $settings = getSettings($conn, [
        'search' => $search,
        'group'  => $group,
        'status' => $status,
    ]);
    $allGroups = getSettingGroups($conn);
} catch (Exception $e) {
    $settings = [];
    $allGroups = [];
    $_SESSION['settings_flash'] = ['type' => 'error', 'message' => 'Could not load settings: ' . $e->getMessage()];
}

$flash = $_SESSION['settings_flash'] ?? null;
unset($_SESSION['settings_flash']);

$csrfToken = generateCSRFToken();
$hasAnySettings = !empty($settings);
$isFiltered = ($search !== '' || $group !== 'all' || $status !== 'all');

$pageTitle = 'Settings';
$activeNav = 'settings';

include __DIR__ . '/../include/header.php';

?>
<style>
    /* This page shares the admin shell's --leaf / --ink / --line / etc.
       tokens from header.php, so it no longer redeclares :root or body
       here — that duplicate block was overriding nothing useful and just
       drifting out of sync with the shell. Everything below is scoped
       to .sadm so it can't leak into the sidebar/topbar. */

    .sadm { max-width: 1200px; margin: 0 auto; }
    .sadm * { box-sizing: border-box; }

    .sadm .toolbar {
        display: flex; align-items: center; justify-content: space-between;
        gap: 16px; margin-bottom: 20px; flex-wrap: wrap;
    }
    .sadm .toolbar h1 { margin: 0; font-size: 1.5rem; font-weight: 800; letter-spacing: -0.01em; color: var(--ink); }
    .sadm .toolbar .subtitle { display: block; margin-top: 2px; font-size: 0.85rem; font-weight: 600; color: var(--muted); }

    .sadm .btn {
        display: inline-flex; align-items: center; gap: 7px; padding: 9px 16px; font-size: 0.86rem; font-weight: 700;
        text-decoration: none; border: 1px solid transparent; border-radius: 10px; cursor: pointer;
        transition: background-color 0.15s ease, transform 0.15s ease;
    }
    .sadm .btn svg { width: 15px; height: 15px; }
    .sadm .btn:hover { transform: translateY(-1px); }
    .sadm .btn:focus-visible { outline: 2px solid currentColor; outline-offset: 2px; }

    .sadm .btn-primary { background: var(--leaf); color: #fff; }
    .sadm .btn-primary:hover { background: var(--leaf-dark); }
    .sadm .btn-secondary { background: #fff; color: var(--ink); border-color: var(--line); }
    .sadm .btn-secondary:hover { background: var(--mist); }
    .sadm .btn-danger { background: #fdeceb; color: #c23b32; border-color: transparent; }
    .sadm .btn-danger:hover { background: #fadedb; }
    .sadm .btn-sm { padding: 7px 12px; font-size: 0.8rem; }

    .sadm .flash {
        display: flex; align-items: flex-start; gap: 10px;
        padding: 13px 16px; margin-bottom: 16px;
        border-radius: 12px; font-size: 0.9rem; font-weight: 600;
    }
    .sadm .flash svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }
    .sadm .flash-success { background: var(--leaf-tint); color: var(--leaf-dark); }
    .sadm .flash-error { background: #fdeceb; color: #9a2e25; }

    .sadm .card {
        background: #fff; border: 1px solid var(--line); border-radius: 16px;
        padding: 20px; box-shadow: 0 1px 2px rgba(23, 72, 61, 0.04);
    }

    .sadm .filter-bar { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 18px; }

    .sadm .search { position: relative; flex: 1; min-width: 220px; display: flex; align-items: center; }
    .sadm .search svg { position: absolute; left: 12px; width: 16px; height: 16px; color: var(--muted); pointer-events: none; }
    .sadm .search input {
        width: 100%; padding: 9px 12px 9px 34px; font-size: 0.88rem; font-family: inherit;
        border: 1px solid var(--line); border-radius: 10px; background: #fff; color: var(--ink);
    }

    .sadm .filter-bar select {
        padding: 9px 30px 9px 12px; font-size: 0.88rem; font-family: inherit;
        border: 1px solid var(--line); border-radius: 10px; background: #fff; color: var(--ink);
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='9'%3E%3Cpath d='M1 1l6 6 6-6' stroke='%2364798c' stroke-width='1.6' fill='none' fill-rule='evenodd'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 11px center;
    }

    .sadm .filter-bar input:focus,
    .sadm .filter-bar select:focus {
        outline: none; border-color: var(--leaf); box-shadow: 0 0 0 3px var(--leaf-tint);
    }

    .sadm table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
    .sadm th, .sadm td { text-align: left; padding: 12px 10px; border-bottom: 1px solid var(--line); vertical-align: middle; }
    .sadm th { color: var(--muted); font-weight: 800; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; }
    .sadm tbody tr:last-child td { border-bottom: none; }
    .sadm tbody tr:hover { background: #fafcf9; }

    .sadm .setting-label { font-weight: 700; color: var(--ink); }
    .sadm .setting-key { color: var(--muted); font-size: 0.78rem; font-family: ui-monospace, "SFMono-Regular", Menlo, monospace; }

    .sadm .value-preview {
        max-width: 220px; color: var(--muted); overflow: hidden;
        text-overflow: ellipsis; white-space: nowrap; display: block; margin-top: 2px;
    }

    .sadm .group-badge {
        display: inline-block; padding: 3px 10px; border-radius: 999px;
        font-size: 0.76rem; font-weight: 700; background: var(--sky-tint); color: var(--sky);
    }

    .sadm .type-badge {
        display: inline-block; padding: 2px 9px; border-radius: 7px;
        font-size: 0.72rem; font-weight: 700; background: var(--mist); color: var(--muted); border: 1px solid var(--line);
    }

    .sadm .status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 11px; border-radius: 999px; font-size: 0.78rem; font-weight: 700;
    }
    .sadm .status-pill::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .sadm .status-active { background: var(--leaf-tint); color: var(--leaf-dark); }
    .sadm .status-inactive { background: var(--mist); color: var(--muted); }

    .sadm .sort-chip {
        display: inline-block; min-width: 24px; padding: 3px 8px; text-align: center;
        border-radius: 7px; background: var(--sky-tint); color: var(--sky); font-weight: 700; font-size: 0.78rem;
    }

    .sadm .row-actions { display: flex; gap: 6px; flex-wrap: wrap; }
    .sadm form.inline { display: inline; }

    .sadm .empty-state { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 56px 20px; text-align: center; color: var(--muted); }
    .sadm .empty-state svg { width: 40px; height: 40px; color: #c9d8cd; }
    .sadm .empty-state h3 { margin: 0; font-size: 0.98rem; font-weight: 800; color: var(--ink); }
    .sadm .empty-state p { margin: 0; font-size: 0.86rem; }

    @media (max-width: 900px) {
        .sadm td:nth-child(2), .sadm th:nth-child(2) { display: none; }
    }

    @media (max-width: 720px) {
        .sadm .card { overflow-x: auto; }
    }
</style>

<div class="sadm">
    <div class="toolbar">
        <div>
            <h1>Settings</h1>
            <span class="subtitle">Manage website-wide configuration</span>
        </div>
        <a class="btn btn-primary" href="create.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Add Setting
        </a>
    </div>

    <?php if ($flash): ?>
        <div class="flash flash-<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?>">
            <?php if ($flash['type'] === 'success'): ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
            <?php else: ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5"/><path d="M12 16h.01"/></svg>
            <?php endif; ?>
            <span><?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <div class="card">
        <form method="get" action="index.php" class="filter-bar">
            <label class="search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" name="search" placeholder="Search settings…"
                    value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
            </label>

            <select name="group" onchange="this.form.submit()">
                <option value="all" <?= $group === 'all' ? 'selected' : '' ?>>All Groups</option>
                <?php foreach ($allGroups as $g): ?>
                    <option value="<?= htmlspecialchars($g, ENT_QUOTES, 'UTF-8') ?>" <?= $group === $g ? 'selected' : '' ?>>
                        <?= htmlspecialchars($g, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="status" onchange="this.form.submit()">
                <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Statuses</option>
                <?php foreach (SETTING_STATUS_OPTIONS() as $value => $label): ?>
                    <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= $status === $value ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-secondary">Filter</button>
            <?php if ($isFiltered): ?>
                <a class="btn btn-secondary" href="index.php">Reset</a>
            <?php endif; ?>
        </form>

        <?php if (!$hasAnySettings && !$isFiltered): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h9M17 6h3M4 12h3M11 12h9M4 18h11M19 18h1"/><circle cx="15" cy="6" r="2"/><circle cx="9" cy="12" r="2"/><circle cx="17" cy="18" r="2"/></svg>
                <h3>No settings found</h3>
                <p>Create your first site setting to start managing your website configuration.</p>
                <a class="btn btn-primary" href="create.php" style="margin-top:4px;">+ Add Setting</a>
            </div>
        <?php elseif (!$hasAnySettings): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                <h3>No settings match your filters</h3>
                <p>Try a different search term, group, or status.</p>
                <a class="btn btn-secondary" href="index.php" style="margin-top:4px;">Reset filters</a>
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Setting</th>
                            <th>Key</th>
                            <th>Group</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Order</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($settings as $setting):
                            $isActive = $setting['status'] === 'Active';
                            $rawValue = (string) ($setting['setting_value'] ?? '');
                            if ($setting['setting_type'] === 'boolean') {
                                $preview = $rawValue === '1' ? 'On' : 'Off';
                            } elseif ($setting['setting_type'] === 'image') {
                                $preview = $rawValue !== '' ? $rawValue : '—';
                            } elseif (mb_strlen($rawValue) > 40) {
                                $preview = mb_substr($rawValue, 0, 40) . '…';
                            } else {
                                $preview = $rawValue !== '' ? $rawValue : '—';
                            }
                        ?>
                            <tr>
                                <td>
                                    <span class="setting-label"><?= htmlspecialchars($setting['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="value-preview"><?= htmlspecialchars($preview, ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td><span class="setting-key"><?= htmlspecialchars($setting['setting_key'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td><span class="group-badge"><?= htmlspecialchars($setting['setting_group'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td><span class="type-badge"><?= htmlspecialchars(SETTING_TYPES()[$setting['setting_type']] ?? $setting['setting_type'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td>
                                    <span class="status-pill <?= $isActive ? 'status-active' : 'status-inactive' ?>">
                                        <?= $isActive ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td><span class="sort-chip"><?= (int) $setting['sort_order'] ?></span></td>
                                <td>
                                    <div class="row-actions">
                                        <a class="btn btn-secondary btn-sm" href="edit.php?id=<?= (int) $setting['id'] ?>" title="Edit">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"/><path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                                        </a>
                                        <form class="inline" method="post" action="delete.php" onsubmit="return confirm('Are you sure you want to delete this setting?');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="id" value="<?= (int) $setting['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>