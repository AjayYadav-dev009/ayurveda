<?php

/**
 * function/newsletter.php
 *
 * Backs the footer newsletter signup: a lightweight session-stored
 * captcha (no GD/image dependency — rendered as distorted text via CSS
 * in footer.php) plus the actual subscribe insert.
 */

/**
 * Generates a new captcha string, stores it in the session for later
 * validation, and returns it so footer.php can render it. Call this once
 * per page load (footer.php does), not per form submit.
 *
 * @return string
 */
function generateFooterCaptcha()
{
    $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789'; // no 0/O/1/l/I
    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    $_SESSION['footer_captcha'] = $code;
    return $code;
}

/**
 * Validates a submitted captcha answer against the one stashed in the
 * session by generateFooterCaptcha(), then clears it — one-time use,
 * whether or not it matched, so a captured value can't be replayed.
 *
 * @param string $input
 * @return bool
 */
function validateFooterCaptcha($input)
{
    $expected = $_SESSION['footer_captcha'] ?? null;
    unset($_SESSION['footer_captcha']);

    if ($expected === null) {
        return false;
    }

    return is_string($input) && strcasecmp(trim($input), $expected) === 0;
}

/**
 * Records a newsletter signup. Re-subscribing an existing (even
 * previously unsubscribed) email is treated as a no-op success rather
 * than an error — the visitor doesn't need to know the difference.
 *
 * @param mysqli $conn
 * @param string $email
 * @throws InvalidArgumentException
 * @throws Exception
 */
function subscribeToNewsletter($conn, $email)
{
    $email = trim((string) $email);
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Please enter a valid email address.');
    }

    $sql = "INSERT INTO newsletter_subscribers (email, status)
            VALUES (?, 1)
            ON DUPLICATE KEY UPDATE status = 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 's', $email);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error saving subscription: ' . mysqli_error($conn));
    }
}