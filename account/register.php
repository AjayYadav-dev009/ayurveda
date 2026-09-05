<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
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

<div class="auth-page">
    <form class="auth-form" method="post" action="">
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

        <p class="auth-links">
            Already have an account? <a href="login.php">Log in</a>
        </p>
    </form>
</div>