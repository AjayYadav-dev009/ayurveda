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

$errors = [];
$isSelf = (int) ($_SESSION['admin_id'] ?? 0) === $id;

$name = $admin['name'];
$email = $admin['email'];
$role = $admin['role'];
$status = $admin['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'staff';
    $status = $_POST['status'] ?? 'Active';

    $validRoles = ['super_admin', 'admin', 'staff'];
    $validStatuses = ['Active', 'Inactive'];

    if ($name === '') {
        $errors['name'] = 'Name is required.';
    }

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }

    // Password is optional here: leave both fields blank to keep the
    // existing password.
    if ($password !== '' || $confirmPassword !== '') {
        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }
    }

    if (!in_array($role, $validRoles, true)) {
        $errors['role'] = 'Select a valid role.';
    }

    if (!in_array($status, $validStatuses, true)) {
        $errors['status'] = 'Select a valid status.';
    }

    // Don't let an admin lock themselves out by deactivating their own account.
    if ($isSelf && $status !== 'Active') {
        $errors['status'] = 'You cannot deactivate your own account.';
    }

    if (empty($errors)) {
        try {
            updateAdmin($conn, $id, [
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'status' => $status,
                'password' => $password !== '' ? $password : null,
            ]);

            // The list page shows the "updated" message.
            header('Location: index.php?updated=1');
            exit;
        } catch (InvalidArgumentException $e) {
            $errors['email'] = $e->getMessage();
        } catch (Exception $e) {
            $errors['general'] = 'Something went wrong while saving the user.';
        }
    }
}

$pageTitle = 'Edit Admin';
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

    .ladm .form-card { max-width: 760px; padding: 26px; }
    .ladm .field { margin-bottom: 18px; }
    .ladm .field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 18px; }
    .ladm .field label { display: block; margin-bottom: 7px; font-size: 0.82rem; font-weight: 800; letter-spacing: 0.02em; color: var(--ink); }
    .ladm .field input[type="text"], .ladm .field input[type="email"], .ladm .field input[type="password"], .ladm .field select {
        width: 100%; padding: 10px 13px; font-size: 0.9rem; font-family: inherit; color: var(--ink); background: #fff;
        border: 1px solid var(--line); border-radius: 10px; transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .ladm .field input:focus, .ladm .field select:focus { outline: none; border-color: var(--leaf); box-shadow: 0 0 0 3px var(--leaf-tint); }
    .ladm .field.has-error input, .ladm .field.has-error select { border-color: #c23b32; }
    .ladm .field.has-error input:focus, .ladm .field.has-error select:focus { box-shadow: 0 0 0 3px #fdeceb; }
    .ladm .field-error { margin: 6px 0 0; font-size: 0.8rem; font-weight: 700; color: #c23b32; }
    .ladm .field-hint { margin: 6px 0 0; font-size: 0.8rem; font-weight: 600; color: var(--muted); }
    .ladm .form-actions { display: flex; gap: 10px; margin-top: 6px; padding-top: 20px; border-top: 1px solid var(--line); }
    @media (max-width: 720px) { .ladm .field-grid { grid-template-columns: 1fr; } .ladm .form-card { padding: 18px; } }
</style>

<div class="ladm">
    <div class="toolbar">
        <div>
            <h1>Edit Admin</h1>
            <span class="subtitle"><?= htmlspecialchars($admin['name']) ?> · <?= htmlspecialchars($admin['email']) ?></span>
        </div>
        <div class="toolbar-right">
            <a href="view-one.php?id=<?= (int) $id ?>" class="btn btn-view">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                View
            </a>
            <a href="index.php" class="btn btn-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                Back to users
            </a>
        </div>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <p class="alert alert-error"><?= htmlspecialchars($errors['general']) ?></p>
    <?php endif; ?>

    <form method="post" action="" class="card form-card" novalidate>

        <div class="field<?= !empty($errors['name']) ? ' has-error' : '' ?>">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
            <?php if (!empty($errors['name'])): ?>
                <p class="field-error"><?= htmlspecialchars($errors['name']) ?></p>
            <?php endif; ?>
        </div>

        <div class="field<?= !empty($errors['email']) ? ' has-error' : '' ?>">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
            <?php if (!empty($errors['email'])): ?>
                <p class="field-error"><?= htmlspecialchars($errors['email']) ?></p>
            <?php endif; ?>
        </div>

        <div class="field-grid">
            <div class="field<?= !empty($errors['password']) ? ' has-error' : '' ?>">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" autocomplete="new-password">
                <?php if (!empty($errors['password'])): ?>
                    <p class="field-error"><?= htmlspecialchars($errors['password']) ?></p>
                <?php else: ?>
                    <p class="field-hint">Leave blank to keep the current password.</p>
                <?php endif; ?>
            </div>

            <div class="field<?= !empty($errors['confirm_password']) ? ' has-error' : '' ?>">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password">
                <?php if (!empty($errors['confirm_password'])): ?>
                    <p class="field-error"><?= htmlspecialchars($errors['confirm_password']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="field-grid">
            <div class="field<?= !empty($errors['role']) ? ' has-error' : '' ?>">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="staff" <?= $role === 'staff' ? 'selected' : '' ?>>Staff</option>
                    <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="super_admin" <?= $role === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                </select>
                <?php if (!empty($errors['role'])): ?>
                    <p class="field-error"><?= htmlspecialchars($errors['role']) ?></p>
                <?php endif; ?>
            </div>

            <div class="field<?= !empty($errors['status']) ? ' has-error' : '' ?>">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="Active" <?= $status === 'Active' ? 'selected' : '' ?>>Active</option>
                    <option value="Inactive" <?= $status === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
                <?php if (!empty($errors['status'])): ?>
                    <p class="field-error"><?= htmlspecialchars($errors['status']) ?></p>
                <?php endif; ?>
                <?php if (!empty($isSelf) && empty($errors['status'])): ?>
                    <p class="field-hint">You can't deactivate your own account.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-add">Save Changes</button>
            <a href="index.php" class="btn btn-back">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>