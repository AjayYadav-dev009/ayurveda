<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../function/helper.php';
require_once __DIR__ . '/../../function/contact.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);

    if ($id > 0) {
        try {
            deleteContactMessage($conn, $id);
        } catch (Throwable $e) {
            error_log('Contact message delete failed: ' . $e->getMessage());
        }
    }
}

$returnQs = preg_replace('/[^\w=&%.\-]/', '', (string) ($_POST['return_qs'] ?? ''));

redirect('index.php' . ($returnQs !== '' ? '?' . $returnQs : ''));
