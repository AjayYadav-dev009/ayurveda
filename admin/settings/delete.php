<?php
/**
 * admin/settings/delete.php
 *
 * POST-only handler for the Delete button on index.php. Verifies the CSRF
 * token, deletes the setting (and its image file, if any), flashes the
 * result, and redirects back to the list. Outputs no HTML.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../function/settings.php';
require_once __DIR__ . '/../../function/csrf.php';
require_once __DIR__ . '/../../includes/auth.php';

// Deleting must never happen from a plain link / GET request.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['settings_flash'] = ['type' => 'error', 'message' => 'Your session expired. Please try again.'];
    header('Location: index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['settings_flash'] = ['type' => 'error', 'message' => 'Invalid setting.'];
    header('Location: index.php');
    exit;
}

try {
    $deleted = deleteSetting($conn, $id);
    $_SESSION['settings_flash'] = $deleted
        ? ['type' => 'success', 'message' => 'Setting deleted.']
        : ['type' => 'error',   'message' => 'Setting not found (it may already have been deleted).'];
} catch (Throwable $ex) {
    $_SESSION['settings_flash'] = ['type' => 'error', 'message' => 'Could not delete the setting. Please try again.'];
}

header('Location: index.php');
exit;