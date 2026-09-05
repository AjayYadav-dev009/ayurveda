<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/helper.php';

// Not logged in? Send them to log in first, and bring them back here after.
if (!isCustomerLogin()) {
    redirect(BASE_URL . 'account/login.php?redirect=account/index.php');
}

$user = getUserById($conn, $_SESSION['customer_id']);

// Session points at an account that no longer exists (or was deleted) —
// log the stale session out rather than showing a broken page.
if ($user === null) {
    logoutUser();
    redirect(BASE_URL . 'account/login.php');
}

$welcome = isset($_GET['welcome']);
?>

<div class="account-page">
    <h1>My Account</h1>

    <?php if ($welcome): ?>
        <p class="form-success">Welcome, <?= htmlspecialchars($user['name']) ?>! Your account has been created.</p>
    <?php endif; ?>

    <dl>
        <dt>Name</dt>
        <dd><?= htmlspecialchars($user['name']) ?></dd>

        <dt>Email</dt>
        <dd><?= htmlspecialchars($user['email']) ?></dd>

        <dt>Phone</dt>
        <dd><?= $user['phone'] !== null ? htmlspecialchars($user['phone']) : '&mdash;' ?></dd>
    </dl>

    <form method="post" action="logout.php">
        <button type="submit">Log Out</button>
    </form>
</div>