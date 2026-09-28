<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../function/admin-login.php';

/**
 * Fetch all admins, most recently created first.
 *
 * @param mysqli $conn
 * @return array<int, array>
 * @throws Exception
 */
function getAllAdmins($conn)
{
    $sql = "SELECT id, name, email, role, status, last_login_at, created_at
            FROM admins
            ORDER BY created_at DESC";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Error fetching admins: " . mysqli_error($conn));
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

$admins = [];
$loadError = '';

try {
    $admins = getAllAdmins($conn);
} catch (Exception $e) {
    $loadError = 'Something went wrong while loading the users.';
}

$adminCount = count($admins);
$currentAdminId = (int) ($_SESSION['admin_id'] ?? 0);

$deleted = isset($_GET['deleted']) && $_GET['deleted'] === '1';
$created = isset($_GET['created']) && $_GET['created'] === '1';
$updated = isset($_GET['updated']) && $_GET['updated'] === '1';

$pageTitle = 'Admin Users';
$activeNav = 'users';

include __DIR__ . '/../include/header.php';
?>

<style>
    .ladm { max-width: 1160px; margin: 0 auto; }
    .ladm * { box-sizing: border-box; }

    .ladm .toolbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px; flex-wrap: wrap; }
    .ladm .toolbar h1 { margin: 0; font-size: 1.5rem; font-weight: 800; letter-spacing: -0.01em; color: var(--ink); }
    .ladm .toolbar .subtitle { display: block; margin-top: 2px; font-size: 0.85rem; font-weight: 600; color: var(--muted); }
    .ladm .toolbar-right { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .ladm .toolbar-right form { display: inline; margin: 0; }

    .ladm .btn { display: inline-flex; align-items: center; gap: 7px; padding: 10px 18px; font-size: 0.88rem; font-weight: 700; font-family: inherit; text-align: center; text-decoration: none; color: #fff; border: none; border-radius: 10px; cursor: pointer; transition: background-color 0.15s ease, transform 0.15s ease; }
    .ladm .btn svg { width: 16px; height: 16px; }
    .ladm .btn:hover { transform: translateY(-1px); }
    .ladm .btn:focus-visible { outline: 2px solid currentColor; outline-offset: 2px; }
    .ladm .btn-add { background-color: var(--leaf); }
    .ladm .btn-add:hover { background-color: var(--leaf-dark); }
    .ladm .btn-edit { background: var(--sky-tint); color: var(--sky); }
    .ladm .btn-edit:hover { background: #dcecf7; }
    .ladm .btn-view { background: var(--mist); color: var(--muted); }
    .ladm .btn-view:hover { background: #e6eef4; }
    .ladm .btn-delete { background: #fdeceb; color: #c23b32; }
    .ladm .btn-delete:hover { background: #fadedb; }
    .ladm .btn-back { background: #fff; color: var(--ink); border: 1px solid var(--line); }
    .ladm .btn-back:hover { background: var(--mist); }
    .ladm .btn-icon { padding: 8px; }

    .ladm .card { background: #fff; border: 1px solid var(--line); border-radius: 16px; overflow: hidden; box-shadow: 0 1px 2px rgba(23, 72, 61, 0.04); }

    .ladm .alert { padding: 11px 16px; margin-bottom: 16px; border-radius: 12px; font-size: 0.88rem; font-weight: 700; }
    .ladm .alert-success { background: var(--leaf-tint); color: var(--leaf-dark); }
    .ladm .alert-error { background: #fdeceb; color: #a8322a; }

    .ladm .pill { display: inline-flex; align-items: center; gap: 5px; padding: 4px 11px; border-radius: 99px; font-size: 0.78rem; font-weight: 700; }
    .ladm .pill::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .ladm .pill-active { background: var(--leaf-tint); color: var(--leaf-dark); }
    .ladm .pill-inactive { background: var(--mist); color: var(--muted); }

    .ladm .role-chip { display: inline-block; padding: 3px 10px; border-radius: 7px; background: var(--sky-tint); color: var(--sky); font-weight: 700; font-size: 0.8rem; white-space: nowrap; }

    .ladm .search { position: relative; display: flex; align-items: center; }
    .ladm .search svg { position: absolute; left: 12px; width: 16px; height: 16px; color: var(--muted); pointer-events: none; }
    .ladm .search input { width: 230px; padding: 9px 12px 9px 34px; font-size: 0.88rem; font-family: inherit; border: 1px solid var(--line); border-radius: 10px; background: #fff; color: var(--ink); }
    .ladm .search input:focus { outline: none; border-color: var(--leaf); box-shadow: 0 0 0 3px var(--leaf-tint); }

    .ladm table { border-collapse: collapse; width: 100%; font-size: 0.9rem; }
    .ladm th, .ladm td { padding: 13px 16px; text-align: left; vertical-align: middle; border-bottom: 1px solid var(--line); }
    .ladm thead th { background: var(--mist); font-size: 0.74rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; color: var(--muted); }
    .ladm tbody tr:last-child td { border-bottom: none; }
    .ladm tbody tr:hover { background: #fafcf9; }

    .ladm .user-cell { display: flex; align-items: center; gap: 12px; }
    .ladm .u-avatar { display: grid; place-items: center; width: 38px; height: 38px; flex-shrink: 0; border-radius: 50%; background: var(--leaf-tint); color: var(--leaf-dark); font-size: 0.9rem; font-weight: 800; }
    .ladm .u-name { font-weight: 700; color: var(--ink); }
    .ladm .u-you { margin-left: 6px; padding: 2px 8px; border-radius: 99px; background: var(--mist); color: var(--muted); font-size: 0.7rem; font-weight: 700; vertical-align: middle; }
    .ladm .u-email { color: var(--muted); }
    .ladm .muted-date { color: var(--muted); font-size: 0.85rem; white-space: nowrap; }
    .ladm .actions-cell { display: flex; gap: 6px; flex-wrap: wrap; }
    .ladm .actions-cell form { display: inline; margin: 0; }

    .ladm .empty { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 60px 20px; text-align: center; color: var(--muted); }
    .ladm .empty svg { width: 40px; height: 40px; color: #c9d8cd; }
    .ladm .empty strong { color: var(--ink); font-size: 0.98rem; }

    @media (max-width: 720px) {
        .ladm .toolbar-right { width: 100%; }
        .ladm .search { flex: 1; }
        .ladm .search input { width: 100%; }
        .ladm .card { overflow-x: auto; }
    }
</style>

<div class="ladm">
    <div class="toolbar">
        <div>
            <h1>Admin Users</h1>
            <span class="subtitle"><?= $adminCount ?> admin user<?= $adminCount === 1 ? '' : 's' ?> · can sign in to this panel</span>
        </div>
        <div class="toolbar-right">
            <label class="search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="search" id="userSearch" placeholder="Search users…" aria-label="Search users">
            </label>
            <a href="add.php" class="btn btn-add">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Add New Admin
            </a>
        </div>
    </div>

    <?php if ($deleted): ?>
        <p class="alert alert-success">User deleted successfully.</p>
    <?php endif; ?>
    <?php if ($created): ?>
        <p class="alert alert-success">Admin user created successfully.</p>
    <?php endif; ?>
    <?php if ($updated): ?>
        <p class="alert alert-success">User updated successfully.</p>
    <?php endif; ?>
    <?php if ($loadError !== ''): ?>
        <p class="alert alert-error"><?= htmlspecialchars($loadError) ?></p>
    <?php endif; ?>

    <div class="card">
        <?php if ($adminCount === 0 && $loadError === ''): ?>
            <div class="empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <strong>No admin users yet</strong>
                <span>Add your first admin to give someone access to this panel.</span>
                <a href="add.php" class="btn btn-add" style="margin-top:6px;">Add New Admin</a>
            </div>
        <?php elseif ($adminCount > 0): ?>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userRows">
                        <?php foreach ($admins as $admin):
                            $isActive = strcasecmp((string) $admin['status'], 'Active') === 0;
                            $isSelf = (int) $admin['id'] === $currentAdminId;
                            $initial = strtoupper(function_exists('mb_substr') ? mb_substr(trim($admin['name']) ?: 'A', 0, 1) : substr(trim($admin['name']) ?: 'A', 0, 1));
                        ?>
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <span class="u-avatar" aria-hidden="true"><?= htmlspecialchars($initial) ?></span>
                                        <span class="u-name"><?= htmlspecialchars($admin['name']) ?><?php if ($isSelf): ?><span class="u-you">You</span><?php endif; ?></span>
                                    </div>
                                </td>
                                <td class="u-email"><?= htmlspecialchars($admin['email']) ?></td>
                                <td><span class="role-chip"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $admin['role']))) ?></span></td>
                                <td><span class="pill <?= $isActive ? 'pill-active' : 'pill-inactive' ?>"><?= htmlspecialchars($admin['status']) ?></span></td>
                                <td class="muted-date"><?= $admin['last_login_at'] !== null ? htmlspecialchars($admin['last_login_at']) : '&mdash;' ?></td>
                                <td class="muted-date"><?= htmlspecialchars($admin['created_at']) ?></td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="view-one.php?id=<?= (int) $admin['id'] ?>" class="btn btn-view btn-icon" title="View" aria-label="View <?= htmlspecialchars($admin['name']) ?>">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <a href="edit.php?id=<?= (int) $admin['id'] ?>" class="btn btn-edit btn-icon" title="Edit" aria-label="Edit <?= htmlspecialchars($admin['name']) ?>">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"/><path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                                        </a>
                                        <?php if (!$isSelf): ?>
                                            <form method="POST" action="delete.php" onsubmit="return confirm('Delete this user? This cannot be undone.');">
                                                <input type="hidden" name="id" value="<?= (int) $admin['id'] ?>">
                                                <button type="submit" class="btn btn-delete btn-icon" title="Delete" aria-label="Delete <?= htmlspecialchars($admin['name']) ?>">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                                </button>
                                            </form>
                                        <?php endif; ?>
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

<script>
    (function () {
        var input = document.getElementById('userSearch');
        var rows = document.querySelectorAll('#userRows tr');
        if (!input || !rows.length) return;
        input.addEventListener('input', function () {
            var q = input.value.trim().toLowerCase();
            rows.forEach(function (row) {
                row.style.display = row.textContent.toLowerCase().indexOf(q) === -1 ? 'none' : '';
            });
        });
    })();
</script>

<?php include __DIR__ . '/../include/footer.php'; ?>