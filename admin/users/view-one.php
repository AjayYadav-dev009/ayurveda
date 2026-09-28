<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../function/admin-login.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    die('Invalid user id.');
}

$admin = getAdminById($conn, $id);
if ($admin === null) {
    die('User not found.');
}

$isActive = strcasecmp((string) $admin['status'], 'Active') === 0;
$isSelf = (int) $admin['id'] === (int) ($_SESSION['admin_id'] ?? 0);
$initial = strtoupper(function_exists('mb_substr') ? mb_substr(trim($admin['name']) ?: 'A', 0, 1) : substr(trim($admin['name']) ?: 'A', 0, 1));

$pageTitle = 'View Admin';
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

    .ladm .profile { max-width: 760px; }
    .ladm .profile-head { display: flex; align-items: center; gap: 16px; padding: 22px 26px; border-bottom: 1px solid var(--line); background: var(--mist); }
    .ladm .profile-avatar { display: grid; place-items: center; width: 56px; height: 56px; flex-shrink: 0; border-radius: 50%; background: var(--adm-side); color: var(--adm-gold); font-size: 1.3rem; font-weight: 800; }
    .ladm .profile-name { margin: 0 0 5px; font-size: 1.15rem; font-weight: 800; color: var(--ink); }
    .ladm .detail-grid { display: grid; grid-template-columns: 1fr 1fr; }
    .ladm .detail { padding: 18px 26px; border-bottom: 1px solid var(--line); }
    .ladm .detail:nth-child(odd) { border-right: 1px solid var(--line); }
    .ladm .detail:nth-last-child(-n+2) { border-bottom: none; }
    .ladm .detail:last-child:nth-child(odd) { grid-column: 1 / -1; border-right: none; }
    .ladm .detail-label { display: block; margin-bottom: 5px; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; color: var(--muted); }
    .ladm .detail-value { display: block; font-size: 0.92rem; font-weight: 600; color: var(--ink); word-break: break-word; }
    @media (max-width: 720px) {
        .ladm .detail-grid { grid-template-columns: 1fr; }
        .ladm .detail:nth-child(odd) { border-right: none; }
        .ladm .detail:nth-last-child(2) { border-bottom: 1px solid var(--line); }
    }
</style>

<div class="ladm">
    <div class="toolbar">
        <div>
            <h1>Admin Details</h1>
            <span class="subtitle">Account information for this admin user</span>
        </div>
        <div class="toolbar-right">
            <a href="index.php" class="btn btn-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                Back to users
            </a>
            <a href="edit.php?id=<?= (int) $admin['id'] ?>" class="btn btn-edit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"/><path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                Edit
            </a>
            <?php if (!$isSelf): ?>
                <form method="POST" action="delete.php" onsubmit="return confirm('Delete this user? This cannot be undone.');">
                    <input type="hidden" name="id" value="<?= (int) $admin['id'] ?>">
                    <button type="submit" class="btn btn-delete">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                        Delete
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="card profile">
        <div class="profile-head">
            <span class="profile-avatar" aria-hidden="true"><?= htmlspecialchars($initial) ?></span>
            <div>
                <h2 class="profile-name"><?= htmlspecialchars($admin['name']) ?></h2>
                <span class="pill <?= $isActive ? 'pill-active' : 'pill-inactive' ?>"><?= htmlspecialchars($admin['status']) ?></span>
            </div>
        </div>

        <div class="detail-grid">
            <div class="detail">
                <span class="detail-label">Email</span>
                <span class="detail-value"><?= htmlspecialchars($admin['email']) ?></span>
            </div>
            <div class="detail">
                <span class="detail-label">Role</span>
                <span class="detail-value"><span class="role-chip"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $admin['role']))) ?></span></span>
            </div>
            <div class="detail">
                <span class="detail-label">Last login</span>
                <span class="detail-value"><?= $admin['last_login_at'] !== null ? htmlspecialchars($admin['last_login_at']) : '&mdash;' ?></span>
            </div>
            <div class="detail">
                <span class="detail-label">Created at</span>
                <span class="detail-value"><?= htmlspecialchars($admin['created_at']) ?></span>
            </div>
            <div class="detail">
                <span class="detail-label">Updated at</span>
                <span class="detail-value"><?= htmlspecialchars($admin['updated_at']) ?></span>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>