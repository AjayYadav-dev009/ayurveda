<?php

require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/admin-login.php';
require_once __DIR__ . '/../function/helper.php';

// Already logged in? Skip straight to the dashboard.
if (isAdminLogin()) {
    redirect(BASE_URL . 'admin/index.php');
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

        $admin = getAdminByEmail($conn, $email);

        if (!$admin) {

            $errors['login'] = 'Invalid email or password.';
        } elseif ($admin['status'] !== 'Active') {

            $errors['login'] = 'Your account is inactive.';
        } elseif (!password_verify($password, $admin['password'])) {

            $errors['login'] = 'Invalid email or password.';
        } else {

            session_regenerate_id(true);

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];

            redirect(BASE_URL . 'admin/index.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login · Ayurveda Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Same tokens as the admin shell, so this page feels like the
               front door of the same panel rather than a different app. */
            --leaf: #2f9e6e;
            --leaf-dark: #22794f;
            --leaf-tint: #e7f6ee;
            --ink: #1c2b3a;
            --sky: #0f6fb0;
            --sky-tint: #eaf4fb;
            --paper: #ffffff;
            --mist: #f4f8fb;
            --line: #e1e9f0;
            --muted: #64798c;
            --danger: #c8412f;
            --danger-tint: #fbebe8;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            margin: 0;
            font-family: 'Manrope', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--ink);
            background: var(--mist);
        }

        .login-page {
            min-height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at 12% 15%, rgba(47, 158, 110, 0.14), transparent 45%),
                radial-gradient(circle at 88% 85%, rgba(15, 111, 176, 0.12), transparent 45%),
                var(--mist);
        }

        .login-page::before,
        .login-page::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }

        .login-page::before {
            width: 420px;
            height: 420px;
            top: -160px;
            left: -140px;
            background: radial-gradient(circle, rgba(47, 158, 110, 0.16), rgba(47, 158, 110, 0) 70%);
        }

        .login-page::after {
            width: 380px;
            height: 380px;
            bottom: -160px;
            right: -120px;
            background: radial-gradient(circle, rgba(15, 111, 176, 0.14), rgba(15, 111, 176, 0) 70%);
        }

        .login-shell {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 400px;
        }

        .login-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            margin-bottom: 22px;
            text-align: center;
        }

        .login-brand-mark {
            display: grid;
            place-items: center;
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--leaf) 0%, var(--sky) 100%);
            color: #fff;
            box-shadow: 0 10px 24px rgba(15, 111, 176, 0.22);
        }

        .login-brand-mark svg {
            width: 28px;
            height: 28px;
        }

        .login-brand-name {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.01em;
        }

        .login-brand-sub {
            font-size: 13px;
            color: var(--muted);
        }

        .login-form {
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 30px 30px 26px;
            box-shadow: 0 18px 40px rgba(28, 43, 58, 0.08);
        }

        .login-form h1 {
            margin: 0 0 4px;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.01em;
        }

        .login-form .login-hint {
            margin: 0 0 22px;
            font-size: 13.5px;
            color: var(--muted);
        }

        .form-error {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            margin: 0 0 18px;
            padding: 11px 14px;
            border-radius: 11px;
            background: var(--danger-tint);
            color: var(--danger);
            font-size: 13.5px;
            font-weight: 600;
        }

        .form-error svg {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .field {
            margin-bottom: 16px;
        }

        .field label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 700;
            color: var(--ink);
        }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrap svg.field-icon {
            position: absolute;
            left: 13px;
            width: 17px;
            height: 17px;
            color: var(--muted);
            pointer-events: none;
        }

        .field input {
            width: 100%;
            padding: 11px 13px 11px 38px;
            font-size: 14.5px;
            font-family: inherit;
            color: var(--ink);
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 11px;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .field input:focus {
            outline: none;
            border-color: var(--sky);
            box-shadow: 0 0 0 3px var(--sky-tint);
        }

        .field.has-error input {
            border-color: #eeb5ac;
        }

        .field.has-error input:focus {
            border-color: var(--danger);
            box-shadow: 0 0 0 3px var(--danger-tint);
        }

        .field-error {
            margin: 6px 0 0;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--danger);
        }

        .toggle-password {
            position: absolute;
            right: 6px;
            display: grid;
            place-items: center;
            width: 32px;
            height: 32px;
            border: none;
            background: none;
            color: var(--muted);
            border-radius: 8px;
            cursor: pointer;
        }

        .toggle-password:hover {
            background: var(--mist);
            color: var(--ink);
        }

        .toggle-password svg {
            width: 18px;
            height: 18px;
        }

        button[type="submit"] {
            width: 100%;
            margin-top: 6px;
            padding: 12px 18px;
            font-size: 15px;
            font-weight: 700;
            font-family: inherit;
            color: #fff;
            background: var(--leaf);
            border: none;
            border-radius: 11px;
            cursor: pointer;
            transition: background 0.15s ease, transform 0.15s ease;
        }

        button[type="submit"]:hover {
            background: var(--leaf-dark);
            transform: translateY(-1px);
        }

        button[type="submit"]:focus-visible {
            outline: 2px solid var(--leaf-dark);
            outline-offset: 2px;
        }

        .login-footnote {
            margin-top: 18px;
            text-align: center;
            font-size: 12.5px;
            color: var(--muted);
        }

        @media (max-width: 420px) {
            .login-form {
                padding: 24px 20px 22px;
            }
        }
    </style>
</head>

<body>

    <div class="login-page">
        <div class="login-shell">

            <div class="login-brand">
                <span class="login-brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 4 13V7a1 1 0 0 1 1-1h1a7 7 0 0 1 7 7v7Z"/><path d="M11 20v-7a7 7 0 0 1 7-7h1a1 1 0 0 1 1 1v1a7 7 0 0 1-7 7"/></svg>
                </span>
                <span class="login-brand-name">Ayurveda Admin</span>
                <span class="login-brand-sub">Store control panel</span>
            </div>

            <form class="login-form" method="post" action="">
                <h1>Admin Login</h1>
                <p class="login-hint">Sign in to manage products, orders and homepage content.</p>

                <?php if (!empty($errors['login'])): ?>
                    <p class="form-error">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5"/><path d="M12 16h.01"/></svg>
                        <span><?= htmlspecialchars($errors['login']) ?></span>
                    </p>
                <?php endif; ?>

                <div class="field<?= !empty($errors['email']) ? ' has-error' : '' ?>">
                    <label for="email">Email</label>
                    <div class="input-wrap">
                        <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?= htmlspecialchars($email) ?>"
                            required
                            autofocus>
                    </div>
                    <?php if (!empty($errors['email'])): ?>
                        <p class="field-error"><?= htmlspecialchars($errors['email']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field<?= !empty($errors['password']) ? ' has-error' : '' ?>">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            style="padding-right:40px;"
                            required>
                        <button type="button" class="toggle-password" data-toggle-password aria-label="Show password" aria-controls="password" aria-pressed="false">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <?php if (!empty($errors['password'])): ?>
                        <p class="field-error"><?= htmlspecialchars($errors['password']) ?></p>
                    <?php endif; ?>
                </div>

                <button type="submit">Log In</button>
            </form>

            <p class="login-footnote">Access is limited to authorised store administrators.</p>
        </div>
    </div>

    <script>
        (function () {
            var btn = document.querySelector('[data-toggle-password]');
            var input = document.getElementById('password');
            if (!btn || !input) return;

            var eyeOpen = btn.innerHTML;
            var eyeClosed = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 10.6a3 3 0 0 0 4.24 4.24"/><path d="M9.9 5.1A11 11 0 0 1 12 5c7 0 11 7 11 7a13.2 13.2 0 0 1-3.1 3.8M6.3 6.3A13.6 13.6 0 0 0 1 12s4 7 11 7a11 11 0 0 0 4.4-.9"/></svg>';

            btn.addEventListener('click', function () {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.innerHTML = show ? eyeClosed : eyeOpen;
                btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            });
        })();
    </script>

</body>

</html>