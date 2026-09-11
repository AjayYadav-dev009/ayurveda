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

    .reset-page {
        max-width: 440px;
        margin: 60px auto;
        padding: 0 20px;
    }

    .reset-form {
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-soft);
        padding: 36px 32px;
    }

    .reset-form h1 {
        margin: 0 0 24px;
        font-size: 24px;
        font-weight: 800;
        color: var(--color-primary-dark);
        text-align: center;
    }

    .reset-form p {
        margin: 0 0 20px;
        font-size: 14px;
        color: var(--color-text-light);
        line-height: 1.5;
    }

    .reset-form label {
        display: block;
        margin-bottom: 6px;
        font-size: 13px;
        font-weight: 600;
        color: var(--color-text);
    }

    .reset-form input {
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

    .reset-form input:focus {
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

    .reset-form button[type="submit"] {
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

    .reset-form button[type="submit"]:hover {
        background: var(--color-primary-dark);
    }

    .reset-links {
        margin: 16px 0 0;
        font-size: 13px;
        text-align: center;
        color: var(--color-text-light);
    }

    .reset-links a {
        color: var(--color-primary);
        font-weight: 600;
        text-decoration: none;
    }

    .reset-links a:hover {
        color: var(--color-primary-dark);
        text-decoration: underline;
    }
</style>

<div class="reset-page">
    <form class="reset-form" method="post" action="">
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

        <p class="reset-links">
            <a href="login.php">Back to log in</a>
        </p>
    </form>
</div>