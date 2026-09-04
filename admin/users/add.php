<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../function/admin-login.php';

$errors = [];
$success = false;

$name = '';
$email = '';
$role = 'staff';
$status = 'Active';

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
    } elseif (getAdminByEmail($conn, $email)) {
        $errors['email'] = 'An admin with this email already exists.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (!in_array($role, $validRoles, true)) {
        $errors['role'] = 'Select a valid role.';
    }

    if (!in_array($status, $validStatuses, true)) {
        $errors['status'] = 'Select a valid status.';
    }

    if (empty($errors)) {

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO admins (name, email, password, role, status)
             VALUES (?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            $errors['general'] = 'Something went wrong while saving the admin.';
        } else {

            $stmt->bind_param('sssss', $name, $email, $hashedPassword, $role, $status);

            try {
                $stmt->execute();
                $success = true;

                // Reset the form after a successful save.
                $name = '';
                $email = '';
                $role = 'staff';
                $status = 'Active';
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() === 1062) {
                    $errors['email'] = 'An admin with this email already exists.';
                } else {
                    $errors['general'] = 'Something went wrong while saving the admin.';
                }
            }
        }
    }
}
?>

<?php if ($success): ?>
    <p style="color: #218838; font-weight: 600;">Admin user created successfully.</p>
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

    <label for="password">Password</label>
    <input
        type="password"
        id="password"
        name="password"
        required>
    <?php if (!empty($errors['password'])): ?>
        <p class="field-error"><?= htmlspecialchars($errors['password']) ?></p>
    <?php endif; ?>

    <label for="confirm_password">Confirm Password</label>
    <input
        type="password"
        id="confirm_password"
        name="confirm_password"
        required>
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

    <input type="submit" value="Create Admin">
</form>