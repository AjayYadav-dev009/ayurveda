<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/helper.php';
require_once __DIR__ . '/../function/csrf.php';

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

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Your session has expired. Please reload the page and try again.';
    } else {

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
}

$csrfToken = generateCSRFToken();
?>

<style>
    :root {
        --color-primary: #245c4f;
        --color-primary-dark: #17483d;
        --color-primary-light: #eaf4f0;

        --color-accent: #91a96b;

        --color-white: #ffffff;
        --color-bg: #fafcfa;
        --color-text: #1d2925;
        --color-text-light: #69756f;

        --color-border: #dde7e2;

        --container-width: 1200px;

        --radius-sm: 6px;
        --radius-md: 12px;
        --radius-lg: 20px;

        --shadow-soft: 0 8px 30px rgba(25, 70, 58, 0.08);
    }

    .auth-page {
        max-width: 440px;
        margin: 60px auto;
        padding: 0 20px;
    }

    .auth-form {
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-soft);
        padding: 36px 32px;
    }

    .auth-form h1 {
        margin: 0 0 24px;
        font-size: 24px;
        font-weight: 800;
        color: var(--color-primary-dark);
        text-align: center;
    }

    .auth-form label {
        display: block;
        margin-bottom: 6px;
        font-size: 13px;
        font-weight: 600;
        color: var(--color-text);
    }

    .auth-form label:not(:first-of-type) {
        margin-top: 16px;
    }

    .auth-form input {
        width: 100%;
        padding: 11px 14px;
        font-size: 14px;
        font-family: inherit;
        color: var(--color-text);
        background: var(--color-bg);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-sm);
        box-sizing: border-box;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .auth-form input:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px var(--color-primary-light);
    }

    .field-error {
        margin: 6px 0 0;
        font-size: 12px;
        color: #b3261e;
    }

    .form-error {
        margin: 0 0 20px;
        padding: 12px 14px;
        font-size: 13px;
        color: #8a1c14;
        background: #fbeceb;
        border: 1px solid #f2c6c2;
        border-radius: var(--radius-sm);
    }

    .form-success {
        margin: 0 0 16px;
        padding: 12px 14px;
        font-size: 13px;
        color: var(--color-primary-dark);
        background: var(--color-primary-light);
        border: 1px solid var(--color-primary);
        border-radius: var(--radius-sm);
        line-height: 1.5;
    }

    .auth-form button[type="submit"] {
        width: 100%;
        margin-top: 24px;
        padding: 12px 16px;
        font-size: 15px;
        font-weight: 700;
        color: var(--color-white);
        background: var(--color-primary);
        border: none;
        border-radius: var(--radius-md);
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .auth-form button[type="submit"]:hover {
        background: var(--color-primary-dark);
    }

    .auth-links {
        margin: 16px 0 0;
        font-size: 13px;
        text-align: center;
        color: var(--color-text-light);
    }

    .auth-links a {
        color: var(--color-primary);
        font-weight: 600;
        text-decoration: none;
    }

    .auth-links a:hover {
        color: var(--color-primary-dark);
        text-decoration: underline;
    }
</style>

<div class="auth-page">
    <form class="auth-form" method="post" action="?token=<?= urlencode($token) ?>">
        <h1>Reset Password</h1>

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

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
                <p class="auth-links"><a href="forget-password.php">Request a new reset link</a></p>
            <?php endif; ?>
        <?php endif; ?>
    </form>
</div>