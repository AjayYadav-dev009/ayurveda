<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/helper.php';

$errors = [];
$email = '';
$submitted = false;
$devResetLink = null; // See note near the bottom of this file.

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }

    if (empty($errors)) {
        try {
            $token = createPasswordResetRequest($conn, $email);

            // Deliberately identical whether or not the email is
            // registered — never reveal which addresses have accounts.
            $submitted = true;

            if ($token !== null) {
                $resetLink = BASE_URL . 'account/reset-password.php?token=' . urlencode($token);

                // ---------------------------------------------------------
                // TODO: send $resetLink to the customer by email instead of
                // displaying it. This project has no mailer wired up yet
                // (no PHPMailer/SMTP config), so for local development the
                // link is shown directly on the page below. Before this
                // goes anywhere near production, swap this block out for a
                // real email send and delete the $devResetLink display.
                // ---------------------------------------------------------
                $devResetLink = $resetLink;
            }
        } catch (Exception $e) {
            $errors['general'] = 'Something went wrong. Please try again.';
        }
    }
}
?>

<div class="auth-page">
    <form class="auth-form" method="post" action="">
        <h1>Forgot Password</h1>

        <?php if (!empty($errors['general'])): ?>
            <p class="form-error"><?= htmlspecialchars($errors['general']) ?></p>
        <?php endif; ?>

        <?php if ($submitted): ?>
            <p class="form-success">
                If an account exists for that email, we've sent a link to reset the password.
                It will expire in <?= (int) PASSWORD_RESET_TTL_MINUTES ?> minutes.
            </p>

            <?php if ($devResetLink !== null): ?>
                <p class="dev-note">
                    <strong>Dev mode:</strong> no mailer is configured yet, so here's the link directly —
                    <a href="<?= htmlspecialchars($devResetLink) ?>"><?= htmlspecialchars($devResetLink) ?></a>
                </p>
            <?php endif; ?>
        <?php else: ?>
            <p>Enter the email address on your account and we'll send you a link to reset your password.</p>

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

            <button type="submit">Send Reset Link</button>
        <?php endif; ?>

        <p class="auth-links">
            <a href="login.php">Back to log in</a>
        </p>
    </form>
</div>