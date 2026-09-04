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
$success = false;

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
    if ((int) $_SESSION['admin_id'] === (int) $id && $status !== 'Active') {
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

            $success = true;
            $admin = getAdminById($conn, $id);
            $name = $admin['name'];
            $email = $admin['email'];
            $role = $admin['role'];
            $status = $admin['status'];
        } catch (InvalidArgumentException $e) {
            $errors['email'] = $e->getMessage();
        } catch (Exception $e) {
            $errors['general'] = 'Something went wrong while saving the user.';
        }
    }
}
?>

<p><a href="view-one.php?id=<?= (int) $id ?>">&laquo; Back to user</a></p>

<?php if ($success): ?>
    <p style="color: #218838; font-weight: 600;">User updated successfully.</p>
<?php endif; ?>

<?php if (!empty($errors['general'])): ?>
    <p style="color: #c00; font-weight: 600;"><?= htmlspecialchars($errors['general']) ?></p>
<?php endif; ?>

<form method="post" action="">

    <label for="name">Name</label>
    <input
        type="text"
        id="name"
        name="name"
        value="<?= htmlspecialchars($name) ?>"
        required>
    <?php if (!empty($errors['name'])): ?>
        <p class="field-error"><?= htmlspecialchars($errors['name']) ?></p>
    <?php endif; ?>

    <label for="email">Email</label>
    <input
        type="email"
        id="email"
        name="email"
        value="<?= htmlspecialchars($email) ?>"
        required>
    <?php if (!empty($errors['email'])): ?>
        <p class="field-error"><?= htmlspecialchars($errors['email']) ?></p>
    <?php endif; ?>

    <label for="password">New Password (leave blank to keep current password)</label>
    <input
        type="password"
        id="password"
        name="password">
    <?php if (!empty($errors['password'])): ?>
        <p class="field-error"><?= htmlspecialchars($errors['password']) ?></p>
    <?php endif; ?>

    <label for="confirm_password">Confirm New Password</label>
    <input
        type="password"
        id="confirm_password"
        name="confirm_password">
    <?php if (!empty($errors['confirm_password'])): ?>
        <p class="field-error"><?= htmlspecialchars($errors['confirm_password']) ?></p>
    <?php endif; ?>

    <label for="role">Role</label>
    <select id="role" name="role">
        <option value="staff" <?= $role === 'staff' ? 'selected' : '' ?>>Staff</option>
        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
        <option value="super_admin" <?= $role === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
    </select>
    <?php if (!empty($errors['role'])): ?>
        <p class="field-error"><?= htmlspecialchars($errors['role']) ?></p>
    <?php endif; ?>

    <label for="status">Status</label>
    <select id="status" name="status">
        <option value="Active" <?= $status === 'Active' ? 'selected' : '' ?>>Active</option>
        <option value="Inactive" <?= $status === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
    </select>
    <?php if (!empty($errors['status'])): ?>
        <p class="field-error"><?= htmlspecialchars($errors['status']) ?></p>
    <?php endif; ?>

    <input type="submit" value="Save Changes">
</form>