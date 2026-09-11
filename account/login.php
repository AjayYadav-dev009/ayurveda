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

    .login-page {
        max-width: 440px;
        margin: 60px auto;
        padding: 0 20px;
    }

    .login-form {
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-soft);
        padding: 36px 32px;
    }

    .login-form h1 {
        margin: 0 0 24px;
        font-size: 24px;
        font-weight: 800;
        color: var(--color-primary-dark);
        text-align: center;
    }

    .login-form p {
        margin: 0 0 20px;
        font-size: 14px;
        color: var(--color-text-light);
        line-height: 1.5;
    }

    .login-form label {
        display: block;
        margin-bottom: 6px;
        font-size: 16px;
        font-weight: 600;
        color: var(--color-text);
    }

    .login-form label:not(:first-of-type) {
        margin-top: 16px;
    }

    .login-form input {
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

    .login-form input:focus {
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

    .dev-note {
        margin: 0 0 16px;
        padding: 12px 14px;
        font-size: 12px;
        color: var(--color-text);
        background: var(--color-bg);
        border: 1px dashed var(--color-accent);
        border-radius: var(--radius-sm);
        line-height: 1.5;
        word-break: break-all;
    }

    .dev-note a {
        color: var(--color-primary);
    }

    .login-form button[type="submit"] {
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

    .login-form button[type="submit"]:hover {
        background: var(--color-primary-dark);
    }

    .forget-pass {
        margin-top: 12px !important;
    }

    .login-links {
        margin: 16px 0 0;
        font-size: 13px;
        text-align: center;
        color: var(--color-text-light);
    }

    .login-links a {
        color: var(--color-primary);
        font-weight: 600;
        text-decoration: none;
    }

    .login-links a:hover {
        color: var(--color-primary-dark);
        text-decoration: underline;
    }
</style>

<div class="login-page">
    <form class="login-form" method="post" action="">
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

        <p class="login-links forget-pass">
            <a href="forget-password.php">Forgot your password?</a>
        </p>

        <button type="submit">Log In</button>

        <p class="login-links">
            Don't have an account? <a href="register.php">Create one</a>
        </p>
    </form>
</div>