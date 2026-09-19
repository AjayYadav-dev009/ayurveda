<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$id = (int) ($_POST['order_id'] ?? 0);

if (!validateCSRFToken((string) ($_POST['csrf_token'] ?? ''))) {
    flash('err', 'Session expired, please try again.');
} else {
    [$ok, $msg] = updateOrderForAdmin($conn, $id, $_POST, $currentAdminId);
    flash($ok ? 'ok' : 'err', $msg);
}

// Go back where we came from: the detail page or the (filtered) list.
if (($_POST['return'] ?? '') === 'view') {
    redirect('view.php?id=' . $id);
}
$qs = (string) ($_POST['back'] ?? '');
$qs = preg_match('/^[A-Za-z0-9_=&%+.\-]*$/', $qs) ? $qs : '';
redirect('index.php' . ($qs !== '' ? '?' . $qs : ''));