<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/helper.php';
require_once __DIR__ . '/../function/csrf.php';

// Already logged in? No need to register again.
if (isCustomerLogin()) {
    redirect(BASE_URL . '/');
}

$errors = [];
$name = '';
$email = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Your session has expired. Please try again.';
    } else {

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($name === '') {
            $errors['name'] = 'Name is required.';
        }

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            try {
                registerUser($conn, $name, $email, $phone !== '' ? $phone : null, $password);

                // Log the new customer straight in rather than making them
                // fill in the login form again right after registering.
                $user = loginUser($conn, $email, $password);

                redirect(BASE_URL . 'account/index.php?welcome=1');
            } catch (InvalidArgumentException $e) {
                $errors['general'] = $e->getMessage();
            } catch (Exception $e) {
                $errors['general'] = 'Something went wrong while creating your account. Please try again.';
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
        margin: 30px auto;
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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            </div>
            <h1 class="auth-card__title">Create an Account</h1>
            <p class="auth-card__subtitle">Join us — it only takes a minute</p>
        </div>

        <div class="auth-card__body">
            <form class="auth-form" method="post" action="">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <?php if (!empty($errors['general'])): ?>
                    <p class="form-error"><?= htmlspecialchars($errors['general']) ?></p>
                <?php endif; ?>

                <label for="name">Full name</label>
                <div class="auth-input-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?= htmlspecialchars($name) ?>"
                        required
                        autofocus>
                </div>
                <?php if (!empty($errors['name'])): ?>
                    <p class="field-error"><?= htmlspecialchars($errors['name']) ?></p>
                <?php endif; ?>

                <label for="email">Email</label>
                <div class="auth-input-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars($email) ?>"
                        required>
                </div>
                <?php if (!empty($errors['email'])): ?>
                    <p class="field-error"><?= htmlspecialchars($errors['email']) ?></p>
                <?php endif; ?>

                <label for="phone">Phone (optional)</label>
                <div class="auth-input-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        value="<?= htmlspecialchars($phone) ?>">
                </div>

                <label for="password">Password</label>
                <div class="auth-input-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required>
                </div>
                <?php if (!empty($errors['password'])): ?>
                    <p class="field-error"><?= htmlspecialchars($errors['password']) ?></p>
                <?php endif; ?>

                <label for="confirm_password">Confirm password</label>
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

                <button type="submit">Create Account</button>

                <p class="auth-links">
                    Already have an account? <a href="login.php">Log in</a>
                </p>
            </form>
        </div>
    </div>
</div>