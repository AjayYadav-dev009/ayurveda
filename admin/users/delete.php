<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../function/admin-login.php';

// Delete is POST-only; anything else just goes back to the list.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$error = '';

if ($id <= 0) {
    $error = 'Invalid user id.';
} elseif ((int) ($_SESSION['admin_id'] ?? 0) === $id) {
    // Don't let an admin delete their own account while logged in as it.
    $error = 'You cannot delete your own account while logged in as it.';
} elseif (getAdminById($conn, $id) === null) {
    $error = 'User not found.';
} else {
    try {
        deleteAdmin($conn, $id);
        header('Location: index.php?deleted=1');
        exit;
    } catch (Exception $e) {
        $error = 'Something went wrong while deleting the user.';
    }
}

// Only reached on failure: show the error inside the admin layout.
$pageTitle = 'Delete Admin';
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
</style>

<div class="ladm">
    <div class="toolbar">
        <div>
            <h1>Delete Admin</h1>
            <span class="subtitle">The user could not be deleted</span>
        </div>
        <div class="toolbar-right">
            <a href="index.php" class="btn btn-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                Back to users
            </a>
        </div>
    </div>

    <p class="alert alert-error"><?= htmlspecialchars($error) ?></p>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>