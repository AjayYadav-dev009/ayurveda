<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/helper.php';

// Already logged in? No need to register again.
if (isCustomerLogin()) {
    redirect(BASE_URL . 'account/index.php');
}

$errors = [];
$name = '';
$email = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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

    .register-page {
        max-width: 440px;
        margin: 10px auto;
        padding: 0 20px;
    }

    .register-form {
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-soft);
        padding: 36px 32px;
    }

    .register-form h1 {
        margin: 0 0 24px;
        font-size: 24px;
        font-weight: 800;
        color: var(--color-primary-dark);
        text-align: center;
    }

    .register-form label {
        display: block;
        margin-bottom: 6px;
        font-size: 16px;
        font-weight: 600;
        color: var(--color-text);
    }

    .register-form label:not(:first-of-type) {
        margin-top: 16px;
    }

    .register-form input {
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

    .register-form input:focus {
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

    .register-form button[type="submit"] {
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

    .register-form button[type="submit"]:hover {
        background: var(--color-primary-dark);
    }

    .register-links {
        margin: 16px 0 0;
        font-size: 13px;
        text-align: center;
        color: var(--color-text-light);
    }

    .register-links a {
        color: var(--color-primary);
        font-weight: 600;
        text-decoration: none;
    }

    .register-links a:hover {
        color: var(--color-primary-dark);
        text-decoration: underline;
    }
</style>

<div class="register-page">
    <form class="register-form" method="post" action="">
        <h1>Create an Account</h1>

        <?php if (!empty($errors['general'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['general']) ?></p>
        <?php endif; ?>

        <label for="name">Full name</label>
        <input
            type="text"
            id="name"
            name="name"
            value="<?= htmlspecialchars($name) ?>"
            required
            autofocus>
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

        <label for="phone">Phone (optional)</label>
        <input
            type="tel"
            id="phone"
            name="phone"
            value="<?= htmlspecialchars($phone) ?>">

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            required>
        <?php if (!empty($errors['password'])): ?>
            <p class="field-error"><?= htmlspecialchars($errors['password']) ?></p>
        <?php endif; ?>

        <label for="confirm_password">Confirm password</label>
        <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            required>
        <?php if (!empty($errors['confirm_password'])): ?>
            <p class="field-error"><?= htmlspecialchars($errors['confirm_password']) ?></p>
        <?php endif; ?>

        <button type="submit">Create Account</button>

        <p class="register-links">
            Already have an account? <a href="login.php">Log in</a>
        </p>
    </form>
</div>