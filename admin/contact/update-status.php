<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../function/helper.php';
require_once __DIR__ . '/../../function/contact.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int) ($_POST['id'] ?? 0);
    $status = (string) ($_POST['status'] ?? '');

    if ($id > 0 && in_array($status, CONTACT_MESSAGE_STATUSES(), true)) {
        try {
            updateContactMessageStatus($conn, $id, $status);
        } catch (Throwable $e) {
            error_log('Contact message status update failed: ' . $e->getMessage());
        }
    }
}

// Only ever redirect back to one of this folder's own pages.
$allowedPages = ['index.php', 'view.php'];
$returnPage   = in_array($_POST['return_page'] ?? '', $allowedPages, true) ? $_POST['return_page'] : 'index.php';
$returnQs     = preg_replace('/[^\w=&%.\-]/', '', (string) ($_POST['return_qs'] ?? ''));

redirect($returnPage . ($returnQs !== '' ? '?' . $returnQs : ''));
