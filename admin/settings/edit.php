<?php
/**
 * admin/settings/edit.php?id=123
 *
 * Edit an existing setting. On success, flashes a message and returns to
 * index.php.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../function/settings.php';
require_once __DIR__ . '/../../function/csrf.php';
require_once __DIR__ . '/../../includes/auth.php';

$id = (int) ($_GET['id'] ?? 0);

try {
    $setting = $id > 0 ? getSettingById($conn, $id) : null;
} catch (Throwable $ex) {
    $setting = null;
}

if (!$setting) {
    $_SESSION['settings_flash'] = ['type' => 'error', 'message' => 'Setting not found.'];
    header('Location: index.php');
    exit;
}

$error         = null;
$form          = settingFormFromRow($setting);
$existingImage = $setting['setting_type'] === 'image' ? (string) $setting['setting_value'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = settingFormFromPost($_POST);

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Your session expired. Please try again.';
    } else {
        $newImage = null;
        try {
            if ($form['setting_type'] === 'image') {
                // null = no file chosen; updateSetting() then keeps the current image.
                $newImage = uploadSettingImage($_FILES['value_image'] ?? null);
                $form['setting_value'] = $newImage ?? '';
            }

            updateSetting($conn, $id, $form);

            $_SESSION['settings_flash'] = [
                'type'    => 'success',
                'message' => 'Setting "' . $form['label'] . '" was updated.',
            ];
            header('Location: index.php');
            exit;
        } catch (InvalidArgumentException $ex) {
            $error = $ex->getMessage();
        } catch (mysqli_sql_exception $ex) {
            $error = $ex->getCode() === 1062
                ? 'A setting with the key "' . $form['setting_key'] . '" already exists.'
                : 'A database error occurred. Please try again.';
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }

        // Saving failed, so discard the file we just uploaded.
        if ($newImage) {
            deleteSettingImageFile($newImage);
        }
    }
}

$csrfToken  = generateCSRFToken();
$mode       = 'edit';
$formAction = 'edit.php?id=' . $id;

$pageTitle = 'Edit Setting';
$activeNav = 'settings';

include __DIR__ . '/../include/header.php';
include __DIR__ . '/../../includes/setting-form.php';