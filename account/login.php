<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/helper.php';

// Already logged in? Skip straight to the account area.
if (isCustomerLogin()) {
    redirect(BASE_URL . 'account/index.php');
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
        try {
            loginUser($conn, $email, $password);

            $redirectTo = $_GET['redirect'] ?? 'account/index.php';
            redirect(BASE_URL . ltrim($redirectTo, '/'));
        } catch (InvalidArgumentException $e) {
            $errors['login'] = $e->getMessage();
        } catch (Exception $e) {
            $errors['login'] = 'Something went wrong while logging you in. Please try again.';
        }
    }
}
?>

<div class="auth-page">
    <form class="auth-form" method="post" action="">
        <h1>Log In</h1>

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

        <p class="auth-links">
            <a href="forget-password.php">Forgot your password?</a>
        </p>

        <button type="submit">Log In</button>

        <p class="auth-links">
            Don't have an account? <a href="register.php">Create one</a>
        </p>
    </form>
</div>