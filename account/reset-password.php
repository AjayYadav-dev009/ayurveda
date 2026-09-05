<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/helper.php';

$token = $_GET['token'] ?? ($_POST['token'] ?? '');

$errors = [];
$success = false;

// Check the token up front so we can show a clear "link expired" message
// instead of a blank/broken form.
$reset = null;
try {
    $reset = getValidPasswordReset($conn, $token);
} catch (Exception $e) {
    $errors['general'] = 'Something went wrong. Please try again.';
}

if ($reset === null && empty($errors)) {
    $errors['general'] = 'This password reset link is invalid or has expired. Please request a new one.';
}

if ($reset !== null && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        try {
            resetUserPassword($conn, $token, $password);
            $success = true;
        } catch (InvalidArgumentException $e) {
            $errors['general'] = $e->getMessage();
        } catch (Exception $e) {
            $errors['general'] = 'Something went wrong while resetting your password. Please try again.';
        }
    }
}
?>

<div class="auth-page">
    <form class="auth-form" method="post" action="?token=<?= urlencode($token) ?>">
        <h1>Reset Password</h1>

        <?php if ($success): ?>
            <p class="form-success">Your password has been reset. You can now log in with your new password.</p>
            <p class="auth-links"><a href="login.php">Go to login</a></p>
        <?php else: ?>
            <?php if (!empty($errors['general'])): ?>
                <p class="form-error"><?= htmlspecialchars($errors['general']) ?></p>
            <?php endif; ?>

            <?php if ($reset !== null): ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <label for="password">New password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autofocus>
                <?php if (!empty($errors['password'])): ?>
                    <p class="field-error"><?= htmlspecialchars($errors['password']) ?></p>
                <?php endif; ?>

                <label for="confirm_password">Confirm new password</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    required>
                <?php if (!empty($errors['confirm_password'])): ?>
                    <p class="field-error"><?= htmlspecialchars($errors['confirm_password']) ?></p>
                <?php endif; ?>

                <button type="submit">Reset Password</button>
            <?php else: ?>
                <p class="auth-links"><a href="forgot-password.php">Request a new reset link</a></p>
            <?php endif; ?>
        <?php endif; ?>
    </form>
</div>