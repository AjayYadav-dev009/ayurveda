<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/function/newsletter.php';
require_once __DIR__ . '/function/helper.php';

$redirectTo = $_POST['redirect_to'] ?? BASE_URL;
// Only ever redirect back into this site — never follow an external URL
// supplied by the form.
if (strpos($redirectTo, BASE_URL) !== 0) {
    $redirectTo = BASE_URL;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($redirectTo);
}

try {
    if (!validateFooterCaptcha($_POST['captcha'] ?? '')) {
        throw new InvalidArgumentException('That captcha didn\'t match — please try again.');
    }

    subscribeToNewsletter($conn, $_POST['email'] ?? '');

    $_SESSION['newsletter_flash'] = ['type' => 'success', 'message' => 'Thanks for subscribing!'];
} catch (InvalidArgumentException $e) {
    $_SESSION['newsletter_flash'] = ['type' => 'error', 'message' => $e->getMessage()];
} catch (Exception $e) {
    $_SESSION['newsletter_flash'] = ['type' => 'error', 'message' => 'Something went wrong. Please try again.'];
}

redirect($redirectTo . (strpos($redirectTo, '#') === false ? '#newsletter' : ''));