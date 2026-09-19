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
    :root {
        --leaf: #2f9e6e;
        --leaf-dark: #22794f;
        --leaf-tint: #e7f6ee;
        --ink: #1c2b3a;
        --sky: #0f6fb0;
        --sky-tint: #eaf4fb;
        --paper: #ffffff;
        --mist: #f4f8fb;
        --line: #e1e9f0;
        --muted: #64798c;
        --danger: #c8412f;
        --danger-tint: #fbebe8;
    }

    body {
        font-family: 'Manrope', -apple-system, BlinkMacSystemFont, sans-serif;
        background: var(--mist);
        margin: 0;
        color: var(--ink);
    }

    .admin-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 24px 20px 60px;
    }

    h1 {
        font-size: 21px;
        font-weight: 800;
        letter-spacing: -0.01em;
        margin: 0 0 2px;
        color: var(--ink);
    }

    .page-subtitle {
        color: var(--muted);
        font-size: 13px;
        margin: 0 0 20px;
    }

    .card {
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: 22px;
        margin-bottom: 20px;
        box-shadow: 0 1px 2px rgba(28, 43, 58, 0.04);
    }

    .flash {
        padding: 11px 15px;
        border-radius: 10px;
        margin-bottom: 16px;
        font-size: 14px;
        font-weight: 600;
        border-left: 3px solid transparent;
    }

    .flash-success {
        background: var(--leaf-tint);
        color: var(--leaf-dark);
        border-left-color: var(--leaf);
    }

    .flash-error {
        background: var(--danger-tint);
        color: var(--danger);
        border-left-color: var(--danger);
    }

    .btn {
        display: inline-block;
        padding: 8px 14px;
        border-radius: 8px;
        border: 1px solid transparent;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }

    .btn-primary {
        background: var(--leaf);
        color: #fff;
    }

    .btn-primary:hover {
        background: var(--leaf-dark);
    }

    .btn-secondary {
        background: #fff;
        color: var(--sky);
        border-color: var(--line);
    }

    .btn-secondary:hover {
        background: var(--sky-tint);
        border-color: var(--sky);
    }

    .btn-danger {
        background: #fff;
        color: var(--danger);
        border-color: #f0cdc6;
    }

    .btn-danger:hover {
        background: var(--danger-tint);
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        text-align: left;
        padding: 11px 9px;
        border-bottom: 1px solid var(--line);
        font-size: 13px;
        vertical-align: middle;
    }

    th {
        color: var(--muted);
        font-weight: 700;
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    tbody tr:hover {
        background: var(--sky-tint);
    }

    .setting-label {
        font-weight: 700;
    }

    .setting-key {
        color: var(--muted);
        font-size: 12px;
        font-family: 'SFMono-Regular', Consolas, monospace;
    }

    .value-preview {
        max-width: 220px;
        color: var(--muted);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: block;
    }

    .group-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        background: var(--sky-tint);
        color: var(--sky);
    }

    .type-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 700;
        background: var(--mist);
        color: var(--muted);
        border: 1px solid var(--line);
    }

    .status-pill {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .status-active {
        background: var(--leaf-tint);
        color: var(--leaf-dark);
    }

    .status-inactive {
        background: var(--mist);
        color: var(--muted);
    }

    .row-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    form.inline {
        display: inline;
    }

    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 4px;
    }

    .muted {
        color: var(--muted);
        font-size: 12px;
    }

    .filter-bar {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .filter-bar input[type=text],
    .filter-bar select {
        padding: 8px 10px;
        border: 1px solid var(--line);
        border-radius: 8px;
        font-size: 13px;
        font-family: inherit;
        color: var(--ink);
        background: #fff;
    }

    .filter-bar input[type=text] {
        flex: 1;
        min-width: 200px;
    }

    .filter-bar input:focus,
    .filter-bar select:focus {
        outline: none;
        border-color: var(--sky);
        box-shadow: 0 0 0 3px var(--sky-tint);
    }

    .empty-state {
        text-align: center;
        padding: 46px 20px;
    }

    .empty-state h3 {
        margin: 0 0 6px;
        font-size: 16px;
        color: var(--ink);
    }

    .empty-state p {
        color: var(--muted);
        font-size: 13px;
        margin: 0 0 18px;
    }
</style>

<div class="admin-wrap">
    <div class="top-bar">
        <div>
            <h1>Settings</h1>
            <p class="page-subtitle">Manage website-wide configuration</p>
        </div>
        <a class="btn btn-primary" href="create.php">+ Add Setting</a>
    </div>

    <?php if ($flash): ?>
        <div class="flash flash-<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <form method="get" action="index.php" class="filter-bar">
            <input type="text" name="search" placeholder="Search settings..."
                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">

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
                <h3>No settings found.</h3>
                <p>Create your first site setting to start managing<br>your website configuration.</p>
                <a class="btn btn-primary" href="create.php">+ Add Setting</a>
            </div>
        <?php elseif (!$hasAnySettings): ?>
            <div class="empty-state">
                <h3>No settings match your filters.</h3>
                <p>Try a different search term, group, or status.</p>
                <a class="btn btn-secondary" href="index.php">Reset filters</a>
            </div>
        <?php else: ?>
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
                                <span class="setting-label"><?= htmlspecialchars($setting['label'], ENT_QUOTES, 'UTF-8') ?></span><br>
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
                            <td><?= (int) $setting['sort_order'] ?></td>
                            <td>
                                <div class="row-actions">
                                    <a class="btn btn-secondary btn-sm" href="edit.php?id=<?= (int) $setting['id'] ?>">Edit</a>
                                    <form class="inline" method="post" action="delete.php" onsubmit="return confirm('Are you sure you want to delete this setting?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="id" value="<?= (int) $setting['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>