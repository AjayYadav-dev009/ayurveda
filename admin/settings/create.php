<?php
/**
 * admin/settings/create.php
 *
 * Add a new site-wide setting. On success, flashes a message and returns
 * to index.php.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../function/settings.php';
require_once __DIR__ . '/../../function/csrf.php';
require_once __DIR__ . '/../../includes/auth.php';

$error = null;

// Defaults for a blank form.
$form = settingFormFromPost([]);
$form['setting_type']  = 'text';
$form['setting_group'] = 'General';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = settingFormFromPost($_POST);

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Your session expired. Please try again.';
    } else {
        $newImage = null;
        try {
            if ($form['setting_type'] === 'image') {
                $newImage = uploadSettingImage($_FILES['value_image'] ?? null);
                $form['setting_value'] = $newImage ?? '';
            }

            addSetting($conn, $form);

            $_SESSION['settings_flash'] = [
                'type'    => 'success',
                'message' => 'Setting "' . $form['label'] . '" was created.',
            ];
            header('Location: index.php');
            exit;
        } catch (InvalidArgumentException $ex) {
            $error = $ex->getMessage();
        } catch (mysqli_sql_exception $ex) {
            // 1062 = duplicate entry (two admins created the same key at once)
            $error = $ex->getCode() === 1062
                ? 'A setting with the key "' . $form['setting_key'] . '" already exists.'
                : 'A database error occurred. Please try again.';
        } catch (RuntimeException $ex) {
            $error = $ex->getMessage();
        }

        // Don't leave an orphaned upload behind when saving failed.
        if ($newImage) {
            deleteSettingImageFile($newImage);
        }
    }
}

$csrfToken     = generateCSRFToken();
$mode          = 'create';
$formAction    = 'create.php';
$existingImage = '';

$pageTitle = 'Add Setting';
$activeNav = 'settings';

include __DIR__ . '/../include/header.php';
include __DIR__ . '/../../includes/setting-form.php';