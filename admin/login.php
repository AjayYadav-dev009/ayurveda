<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/admin-login.php';
require_once __DIR__ . '/../function/helper.php';

// Already logged in? Skip straight to the dashboard.
if (isAdminLogin()) {
    redirect(BASE_URL . 'admin/index.php');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    }

    // Authentication
    if (empty($errors)) {

        $admin = getAdminByEmail($conn, $email);

        if (!$admin) {

            $errors['login'] = 'Invalid email or password.';
        } elseif ((int) $admin['status'] !== 1) {

            $errors['login'] = 'Your account is inactive.';
        } elseif (!password_verify($password, $admin['password'])) {

            $errors['login'] = 'Invalid email or password.';
        } else {

            session_regenerate_id(true);

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];

            redirect(BASE_URL . 'admin/index.php');
        }
    }
}
?>

<div class="login-page">
    <form class="login-form" method="post" action="">
        <h1>Admin Login</h1>

        <?php if (!empty($errors['login'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['login']) ?></p>
        <?php endif; ?>

        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            value="<?= htmlspecialchars($email) ?>"
            required
            autofocus>
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

        <button type="submit">Log In</button>
    </form>
</div>