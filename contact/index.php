<?php
/**
 * contact/index.php
 *
 * Same structure as detux/index.php (config -> database -> header ->
 * sections -> footer), plus the form handling, which MUST run before
 * header.php because a successful submit redirects.
 * The session include mirrors login.php (needed for the CSRF token).
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/helper.php';
require_once __DIR__ . '/../function/csrf.php';
require_once __DIR__ . '/../function/settings.php';
require_once __DIR__ . '/../function/contact.php';

// Where this page lives (used for the redirect after a successful send).
$contactPageUrl = rtrim(BASE_URL, '/') . '/contact/index.php';

$topics = getContactTopics($conn);
$errors = [];
$old = ['name' => '', 'email' => '', 'phone' => '', 'topic' => '', 'message' => ''];

// Logged-in customers: pre-fill name and email (session keys from customer.php).
if (!empty($_SESSION['customer_id'])) {
    $old['name']  = (string) ($_SESSION['customer_name'] ?? '');
    $old['email'] = (string) ($_SESSION['customer_email'] ?? '');
}

// One-time "thank you" flag set by the redirect below.
$flashSuccess = !empty($_SESSION['contact_flash']);
unset($_SESSION['contact_flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors['form'] = 'Your session has expired. Please try again.';
        $old = array_merge($old, [
            'name'    => trim((string) ($_POST['name'] ?? '')),
            'email'   => trim((string) ($_POST['email'] ?? '')),
            'phone'   => trim((string) ($_POST['phone'] ?? '')),
            'topic'   => trim((string) ($_POST['topic'] ?? '')),
            'message' => (string) ($_POST['message'] ?? ''),
        ]);
    } elseif (!empty($_POST['website'])) {
        // Hidden "website" field: real visitors never see or fill it, bots do.
        // Pretend it worked, save nothing.
        $_SESSION['contact_flash'] = 1;
        redirect($contactPageUrl . '#contact-form');
    } else {
        $result = validateContactForm($_POST, $topics);
        $old    = $result['data'];
        $errors = $result['errors'];

        if (empty($errors)) {
            try {
                $ip = contactClientIp();

                if (isContactRateLimited($conn, $ip)) {
                    $errors['form'] = 'You have sent several messages recently. Please try again a little later.';
                } elseif (isDuplicateContactMessage($conn, $old['email'], $old['message'])) {
                    // Same message already received (double click / back button).
                    $_SESSION['contact_flash'] = 1;
                    redirect($contactPageUrl . '#contact-form');
                } else {
                    $userId    = !empty($_SESSION['customer_id']) ? (int) $_SESSION['customer_id'] : null;
                    $userAgent = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');

                    $messageId = createContactMessage($conn, $old, $ip, $userAgent, $userId);
                    contactSendNotification($conn, $old, $messageId, $ip);

                    // Redirect so refreshing the page can't re-send the form.
                    $_SESSION['contact_flash'] = 1;
                    redirect($contactPageUrl . '#contact-form');
                }
            } catch (Throwable $e) {
                error_log('Contact form error: ' . $e->getMessage());
                $errors['form'] = 'We could not send your message right now. Please try again in a few minutes.';
            }
        }
    }
}

$csrfToken = generateCSRFToken();

include __DIR__ . '/../includes/header.php';
?>

<main>
    <?php include __DIR__ . '/contact-section.php'; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
