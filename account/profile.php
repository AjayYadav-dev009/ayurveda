<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/helper.php';

if (!isCustomerLogin()) {
    redirect(BASE_URL . 'account/login.php?redirect=account/profile.php');
}

$user = getUserById($conn, $_SESSION['customer_id']);

if ($user === null) {
    logoutUser();
    redirect(BASE_URL . 'account/login.php');
}

$errors = [];
$success = false;

$form = [
    'name' => $user['name'],
    'email' => $user['email'],
    'phone' => $user['phone'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = [
        'name' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
    ];

    if ($form['name'] === '') {
        $errors['name'] = 'Name is required.';
    }

    if ($form['email'] === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }

    if (empty($errors)) {
        // -------------------------------------------------------------
        // PLACEHOLDER: no profile-update function exists yet. Once you
        // add one — e.g. updateCustomerProfile($conn, $userId, $data) in
        // function/customer.php — this branch picks it up automatically;
        // nothing else on this page needs to change.
        // -------------------------------------------------------------
        if (function_exists('updateCustomerProfile')) {
            try {
                updateCustomerProfile($conn, $_SESSION['customer_id'], $form);
                $user = getUserById($conn, $_SESSION['customer_id']);
                $_SESSION['customer_name'] = $user['name'];
                $success = true;
            } catch (InvalidArgumentException $e) {
                $errors['general'] = $e->getMessage();
            } catch (Exception $e) {
                $errors['general'] = 'Something went wrong while saving your profile.';
            }
        } else {
            $errors['general'] = "Profile editing isn't wired up yet — check back soon.";
        }
    }
}

$activeNav = 'profile';
require __DIR__ . '/account-sidebar.php';
?>

<style>
    .profile-card {
        max-width: 560px;
        background: var(--acc-white);
        border: 1px solid var(--acc-border);
        border-radius: var(--acc-radius-lg);
        box-shadow: var(--acc-shadow);
        overflow: hidden;
    }

    .profile-card__header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 22px 26px;
        color: #fff;
        background: linear-gradient(120deg, var(--acc-side) 0%, var(--acc-side-deep) 100%);
    }

    .profile-card__avatar {
        flex-shrink: 0;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--acc-gold);
        color: var(--acc-side);
        font-size: 18px;
        font-weight: 800;
    }

    .profile-card__header-name {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
    }

    .profile-card__header-sub {
        margin: 2px 0 0;
        font-size: 12.5px;
        color: rgba(244, 239, 226, 0.7);
    }

    .profile-form {
        padding: 26px;
    }

    .profile-form__group {
        margin-bottom: 18px;
    }

    .profile-form label {
        display: block;
        margin-bottom: 6px;
        font-size: 13px;
        font-weight: 700;
        color: var(--acc-text);
    }

    .profile-form__input-wrap {
        position: relative;
    }

    .profile-form__input-wrap svg {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        color: var(--acc-text-light);
        pointer-events: none;
    }

    .profile-form input {
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

    .profile-form input:focus {
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
        margin: 0 0 20px;
        padding: 12px 14px;
        font-size: 13px;
        font-weight: 600;
        color: var(--acc-side);
        background: rgba(47, 158, 110, 0.1);
        border: 1px solid rgba(47, 158, 110, 0.25);
        border-radius: var(--acc-radius-sm);
        line-height: 1.5;
    }

    .profile-form__actions {
        margin-top: 8px;
        padding-top: 18px;
        border-top: 1px solid var(--acc-border);
    }

    .profile-form button[type="submit"] {
        padding: 12px 26px;
        font-size: 14px;
        font-weight: 700;
        font-family: inherit;
        color: #fff;
        background: var(--acc-side);
        border: none;
        border-radius: var(--acc-radius-md);
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .profile-form button[type="submit"]:hover {
        background: var(--acc-side-deep);
    }
</style>

<h1>Profile</h1>

<?php if ($success): ?>
    <p class="form-success">Your profile has been updated.</p>
<?php endif; ?>

<?php if (!empty($errors['general'])): ?>
    <p class="form-error"><?= htmlspecialchars($errors['general']) ?></p>
<?php endif; ?>

<div class="profile-card">
    <div class="profile-card__header">
        <div class="profile-card__avatar"><?= htmlspecialchars($sidebarInitial) ?></div>
        <div>
            <p class="profile-card__header-name"><?= htmlspecialchars($user['name']) ?></p>
            <p class="profile-card__header-sub"><?= htmlspecialchars($user['email']) ?></p>
        </div>
    </div>

    <form class="profile-form" method="post" action="">
        <div class="profile-form__group">
            <label for="name">Full name</label>
            <div class="profile-form__input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($form['name']) ?>" required>
            </div>
            <?php if (!empty($errors['name'])): ?>
                <p class="field-error"><?= htmlspecialchars($errors['name']) ?></p>
            <?php endif; ?>
        </div>

        <div class="profile-form__group">
            <label for="email">Email</label>
            <div class="profile-form__input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($form['email']) ?>" required>
            </div>
            <?php if (!empty($errors['email'])): ?>
                <p class="field-error"><?= htmlspecialchars($errors['email']) ?></p>
            <?php endif; ?>
        </div>

        <div class="profile-form__group">
            <label for="phone">Phone</label>
            <div class="profile-form__input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .3 2 .6 2.9a2 2 0 0 1-.4 2.1L8.1 9.9a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.4c.9.3 1.9.5 2.9.6a2 2 0 0 1 1.7 2Z"/></svg>
                <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($form['phone']) ?>">
            </div>
        </div>

        <div class="profile-form__actions">
            <button type="submit">Save Changes</button>
        </div>
    </form>
</div>

</main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>