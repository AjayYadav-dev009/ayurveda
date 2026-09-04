<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../function/admin-login.php';

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';

include __DIR__ . '/include/header.php';
?>

<h1>Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?></h1>
<p>You're logged in as <?= htmlspecialchars($_SESSION['admin_email']) ?>.</p>

<?php include __DIR__ . '/include/footer.php'; ?>