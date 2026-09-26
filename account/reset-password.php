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

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --acc-side: #17483d;
        --acc-side-deep: #10382f;
        --acc-cream: #f4efe2;
        --acc-gold: #d8b678;
        --acc-gold-dark: #b3904f;

        --acc-white: #ffffff;
        --acc-bg: #fafcfa;
        --acc-text: #1d2925;
        --acc-text-light: #69756f;
        --acc-border: #dde7e2;

        --acc-radius-sm: 6px;
        --acc-radius-md: 12px;
        --acc-radius-lg: 20px;

        --acc-shadow: 0 10px 34px rgba(16, 56, 47, 0.12);
    }

    .auth-shell {
        max-width: 440px;
        margin: 60px auto;
        padding: 0 20px;
        font-family: 'Manrope', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .auth-card {
        background: var(--acc-white);
        border-radius: var(--acc-radius-lg);
        box-shadow: var(--acc-shadow);
        overflow: hidden;
    }

    .auth-card__banner {
        text-align: center;
        padding: 34px 32px 30px;
        color: #fff;
        background:
            radial-gradient(rgba(244, 239, 226, 0.07) 1px, transparent 1.2px) 0 0 / 22px 22px,
            linear-gradient(120deg, var(--acc-side) 0%, var(--acc-side-deep) 100%);
    }

    .auth-card__mark {
        display: grid;
        place-items: center;
        width: 52px;
        height: 52px;
        margin: 0 auto 14px;
        border-radius: 50%;
        background: rgba(216, 182, 120, 0.18);
        border: 1px solid rgba(216, 182, 120, 0.5);
        color: var(--acc-gold);
    }

    .auth-card__mark svg {
        width: 24px;
        height: 24px;
    }

    .auth-card__title {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.01em;
    }

    .auth-card__subtitle {
        margin: 6px 0 0;
        font-size: 13.5px;
        color: rgba(244, 239, 226, 0.75);
    }

    .auth-card__body {
        padding: 30px 32px 32px;
    }

    .auth-form label {
        display: block;
        margin-bottom: 6px;
        font-size: 13px;
        font-weight: 700;
        color: var(--acc-text);
    }

    .auth-form label:not(:first-of-type) {
        margin-top: 16px;
    }

    .auth-input-wrap {
        position: relative;
    }

    .auth-input-wrap svg {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        color: var(--acc-text-light);
        pointer-events: none;
    }

    .auth-form input {
        width: 100%;
        padding: 11px 14px 11px 40px;
        font-size: 14px;
        font-family: inherit;
        color: var(--acc-text);
        background: var(--acc-bg);
        border: 1px solid var(--acc-border);
        border-radius: var(--acc-radius-sm);
        box-sizing: border-box;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .auth-form input:focus {
        outline: none;
        border-color: var(--acc-side);
        box-shadow: 0 0 0 3px rgba(23, 72, 61, 0.12);
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
        border-radius: var(--acc-radius-sm);
    }

    .form-success {
        margin: 0 0 16px;
        padding: 12px 14px;
        font-size: 13px;
        font-weight: 600;
        color: var(--acc-side);
        background: rgba(47, 158, 110, 0.1);
        border: 1px solid rgba(47, 158, 110, 0.25);
        border-radius: var(--acc-radius-sm);
        line-height: 1.5;
    }

    .auth-links {
        margin: 16px 0 0;
        font-size: 13px;
        text-align: center;
        color: var(--acc-text-light);
    }

    .auth-links a {
        color: var(--acc-side);
        font-weight: 700;
        text-decoration: none;
    }

    .auth-links a:hover {
        color: var(--acc-gold-dark);
        text-decoration: underline;
    }

    .auth-form button[type="submit"] {
        width: 100%;
        margin-top: 24px;
        padding: 12px 16px;
        font-size: 15px;
        font-weight: 700;
        font-family: inherit;
        color: #fff;
        background: var(--acc-side);
        border: none;
        border-radius: var(--acc-radius-md);
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .auth-form button[type="submit"]:hover {
        background: var(--acc-side-deep);
    }
</style>

<div class="auth-shell">
    <div class="auth-card">
        <div class="auth-card__banner">
            <div class="auth-card__mark">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
            </div>
            <h1 class="auth-card__title">Reset Password</h1>
            <p class="auth-card__subtitle">Choose a new password below</p>
        </div>

        <div class="auth-card__body">
            <form class="auth-form" method="post" action="?token=<?= urlencode($token) ?>">
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
                        <div class="auth-input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                autofocus>
                        </div>
                        <?php if (!empty($errors['password'])): ?>
                            <p class="field-error"><?= htmlspecialchars($errors['password']) ?></p>
                        <?php endif; ?>

                        <label for="confirm_password">Confirm new password</label>
                        <div class="auth-input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                required>
                        </div>
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
    </div>
</div>