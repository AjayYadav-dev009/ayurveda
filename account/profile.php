<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
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
    .profile-form {
        max-width: 480px;
    }

    .profile-form label {
        display: block;
        margin-top: 16px;
        margin-bottom: 6px;
        font-size: 13px;
        font-weight: 600;
        color: var(--color-text);
    }

    .profile-form label:first-of-type {
        margin-top: 0;
    }

    .profile-form input {
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

    .profile-form input:focus {
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
        margin: 0 0 20px;
        padding: 12px 14px;
        font-size: 13px;
        color: var(--color-primary-dark);
        background: var(--color-primary-light);
        border: 1px solid var(--color-primary);
        border-radius: var(--radius-sm);
        line-height: 1.5;
    }

    .profile-form button[type="submit"] {
        margin-top: 24px;
        padding: 12px 24px;
        font-size: 14px;
        font-weight: 700;
        font-family: inherit;
        color: var(--color-white);
        background: var(--color-primary);
        border: none;
        border-radius: var(--radius-md);
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .profile-form button[type="submit"]:hover {
        background: var(--color-primary-dark);
    }
</style>

<h1>Profile</h1>

<?php if ($success): ?>
    <p class="form-success">Your profile has been updated.</p>
<?php endif; ?>

<?php if (!empty($errors['general'])): ?>
    <p class="form-error"><?= htmlspecialchars($errors['general']) ?></p>
<?php endif; ?>

<form class="profile-form" method="post" action="">
    <label for="name">Full name</label>
    <input type="text" id="name" name="name" value="<?= htmlspecialchars($form['name']) ?>" required>
    <?php if (!empty($errors['name'])): ?>
        <p class="field-error"><?= htmlspecialchars($errors['name']) ?></p>
    <?php endif; ?>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($form['email']) ?>" required>
    <?php if (!empty($errors['email'])): ?>
        <p class="field-error"><?= htmlspecialchars($errors['email']) ?></p>
    <?php endif; ?>

    <label for="phone">Phone</label>
    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($form['phone']) ?>">

    <button type="submit">Save Changes</button>
</form>

</main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>