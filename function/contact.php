<?php

/**
 * function/contact.php
 *
 * Helpers for the public Contact page.
 *
 * Reads contact details / dropdown options from the `settings` table using
 * getSetting() (only setting_key + setting_value are needed, so this works
 * with the current 4-column settings table AND with the extended one that
 * function/settings.php describes).
 *
 * Stores submissions in `contact_messages` (see sql/contact-setup.sql).
 *
 * Settings keys used:
 *   contact_phone, contact_phone_note, contact_email, contact_email_note,
 *   contact_address (one line per row), contact_topics (one option per line),
 *   contact_notify_email (empty = no email notifications), site_name (optional)
 *
 * Like function/customer.php, everything degrades safely: if something in
 * the database is missing, the page keeps working and the problem is logged
 * with error_log() instead of breaking the site.
 */

if (!defined('CONTACT_MAX_PER_IP_PER_HOUR')) {
    define('CONTACT_MAX_PER_IP_PER_HOUR', 5);
}
if (!defined('CONTACT_MESSAGE_MIN_LENGTH')) {
    define('CONTACT_MESSAGE_MIN_LENGTH', 10);
}
if (!defined('CONTACT_MESSAGE_MAX_LENGTH')) {
    define('CONTACT_MESSAGE_MAX_LENGTH', 3000);
}

/* -------------------------------------------------------------------------
 * Small utilities
 * ---------------------------------------------------------------------- */

if (!function_exists('contactStrLen')) {
    /** Character count that is safe for Hindi / accented text. */
    function contactStrLen($value)
    {
        return function_exists('mb_strlen') ? mb_strlen((string) $value, 'UTF-8') : strlen((string) $value);
    }
}

if (!function_exists('contactSplitLines')) {
    /** Split a multi-line setting into trimmed, non-empty lines. */
    function contactSplitLines($text)
    {
        $lines = preg_split('/\R/u', (string) $text);
        if ($lines === false) {
            return [];
        }
        $lines = array_map('trim', $lines);
        return array_values(array_filter($lines, static function ($line) {
            return $line !== '';
        }));
    }
}

if (!function_exists('contactClientIp')) {
    /**
     * The visitor's IP. Only REMOTE_ADDR is trusted (X-Forwarded-For can be
     * faked by anyone). If the site sits behind a proxy/CDN, adjust here.
     */
    function contactClientIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
    }
}

/* -------------------------------------------------------------------------
 * Reading settings
 * ---------------------------------------------------------------------- */

if (!function_exists('contactSetting')) {
    /** getSetting() wrapped so a database problem can never break the page. */
    function contactSetting($conn, $key, $default = '')
    {
        try {
            if (!function_exists('getSetting')) {
                require_once __DIR__ . '/settings.php';
            }
            return trim((string) getSetting($conn, $key, $default));
        } catch (Throwable $e) {
            error_log('contactSetting(' . $key . ') failed: ' . $e->getMessage());
            return (string) $default;
        }
    }
}

if (!function_exists('getContactDetails')) {
    /**
     * Contact details for the left-hand column. Any blank setting is
     * returned empty so the page can simply skip it.
     *
     * @return array{phone: array, email: array, address: array}
     */
    function getContactDetails($conn)
    {
        $phone = contactSetting($conn, 'contact_phone');
        $email = contactSetting($conn, 'contact_email');

        return [
            'phone' => [
                'value' => $phone,
                'note'  => contactSetting($conn, 'contact_phone_note'),
                'href'  => $phone !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : '',
            ],
            'email' => [
                'value' => $email,
                'note'  => contactSetting($conn, 'contact_email_note'),
                'href'  => ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) ? 'mailto:' . $email : '',
            ],
            'address' => [
                'lines' => contactSplitLines(contactSetting($conn, 'contact_address')),
            ],
        ];
    }
}

if (!function_exists('getContactTopics')) {
    /** Options for the "I'm interested in" dropdown (max 20, no duplicates). */
    function getContactTopics($conn)
    {
        $topics = [];
        foreach (contactSplitLines(contactSetting($conn, 'contact_topics')) as $line) {
            if (contactStrLen($line) <= 150 && !in_array($line, $topics, true)) {
                $topics[] = $line;
            }
        }
        return array_slice($topics, 0, 20);
    }
}

if (!function_exists('contactSafeUrl')) {
    /** Returns the URL only if it is a plain https:// link, otherwise ''. */
    function contactSafeUrl($url)
    {
        $url = trim((string) $url);
        return preg_match('~^https://[^\s"\'<>]+$~i', $url) ? $url : '';
    }
}

if (!function_exists('getContactLocation')) {
    /**
     * Data for the "Visit Our Wellness Center" band (map + text + photo).
     *
     * Everything is optional. With nothing configured, the map and the
     * directions link are built from the contact address, so the section
     * works as soon as `contact_address` is filled in. If there is neither
     * an address nor a map URL, 'embed' is '' and the page skips the band.
     *
     * Settings keys (all optional):
     *   contact_map_embed_url   full https:// iframe src (Google Maps "Embed a map")
     *   contact_directions_url  full https:// link opened by the button
     *   contact_location_image  photo on the right (full URL or path under BASE_URL)
     *   contact_location_heading, contact_location_text
     *
     * @param array $addressLines Lines from getContactDetails()['address']['lines'].
     * @return array{embed: string, directions: string, image: string, heading: string, text: string}
     */
    function getContactLocation($conn, array $addressLines = [])
    {
        $query = implode(', ', $addressLines);

        $embed = contactSafeUrl(contactSetting($conn, 'contact_map_embed_url'));
        if ($embed === '' && $query !== '') {
            $embed = 'https://maps.google.com/maps?q=' . rawurlencode($query) . '&output=embed';
        }

        $directions = contactSafeUrl(contactSetting($conn, 'contact_directions_url'));
        if ($directions === '' && $query !== '') {
            $directions = 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($query);
        }

        $image = contactSetting($conn, 'contact_location_image');
        if ($image !== '' && !preg_match('~^(https?:)?//~i', $image) && $image[0] !== '/') {
            $image = rtrim(defined('BASE_URL') ? (string) BASE_URL : '', '/') . '/' . ltrim($image, '/');
        }

        return [
            'embed'      => $embed,
            'directions' => $directions,
            'image'      => $image,
            'heading'    => contactSetting($conn, 'contact_location_heading', 'Visit Our Wellness Center'),
            'text'       => contactSetting(
                $conn,
                'contact_location_text',
                'Experience the authentic touch of Ayurveda at our centre in New Delhi. Our team of experts is here to guide you on your wellness journey.'
            ),
        ];
    }
}

/* -------------------------------------------------------------------------
 * Validation
 * ---------------------------------------------------------------------- */

if (!function_exists('validateContactForm')) {
    /**
     * Clean and validate the submitted form.
     *
     * @param array $input  Usually $_POST.
     * @param array $topics Allowed dropdown values (from getContactTopics()).
     * @return array{data: array, errors: array} `data` always holds the
     *         cleaned values (so the form can be re-filled); `errors` is
     *         keyed by field name and empty when everything is valid.
     */
    function validateContactForm(array $input, array $topics)
    {
        $str = static function ($key) use ($input) {
            return isset($input[$key]) && is_scalar($input[$key]) ? (string) $input[$key] : '';
        };

        $errors = [];

        // Name: collapse whitespace, drop control characters.
        $name = preg_replace('/[^\P{C}]+/u', ' ', $str('name'));
        if ($name === null) {
            $errors['name'] = 'Your name contains invalid characters.';
            $name = '';
        }
        $name = trim(preg_replace('/\s+/u', ' ', (string) $name));

        $email = trim($str('email'));
        $phone = trim($str('phone'));
        $topic = trim($str('topic'));

        // Message: normalise line breaks, drop control characters except \n and \t.
        $message = str_replace(["\r\n", "\r"], "\n", $str('message'));
        $cleanMessage = preg_replace('/[^\P{C}\n\t]+/u', '', $message);
        if ($cleanMessage === null) {
            $errors['message'] = 'Your message contains invalid characters.';
            $cleanMessage = '';
        }
        $message = trim($cleanMessage);

        if (!isset($errors['name'])) {
            if ($name === '') {
                $errors['name'] = 'Please enter your name.';
            } elseif (contactStrLen($name) < 2) {
                $errors['name'] = 'Please enter your full name.';
            } elseif (contactStrLen($name) > 150) {
                $errors['name'] = 'Your name is too long.';
            }
        }

        if ($email === '') {
            $errors['email'] = 'Please enter your email address.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || contactStrLen($email) > 191) {
            $errors['email'] = 'Enter a valid email address.';
        }

        // Same rule settings.php uses for phone numbers. Optional field.
        if ($phone !== '' && !preg_match('/^[0-9+\-\s().]{5,25}$/D', $phone)) {
            $errors['phone'] = 'Enter a valid phone number.';
        }

        // Only values that are in the current option list are accepted.
        if ($topic !== '' && !in_array($topic, $topics, true)) {
            $errors['topic'] = 'Please choose one of the listed options.';
        }

        if (!isset($errors['message'])) {
            $length = contactStrLen($message);
            if ($message === '') {
                $errors['message'] = 'Please write a message.';
            } elseif ($length < CONTACT_MESSAGE_MIN_LENGTH) {
                $errors['message'] = 'Please write at least ' . CONTACT_MESSAGE_MIN_LENGTH . ' characters.';
            } elseif ($length > CONTACT_MESSAGE_MAX_LENGTH) {
                $errors['message'] = 'Your message is too long (maximum ' . CONTACT_MESSAGE_MAX_LENGTH . ' characters).';
            }
        }

        return [
            'data' => [
                'name'    => $name,
                'email'   => $email,
                'phone'   => $phone,
                'topic'   => $topic,
                'message' => $message,
            ],
            'errors' => $errors,
        ];
    }
}

/* -------------------------------------------------------------------------
 * Abuse protection (same idea as the login rate limit in customer.php)
 * ---------------------------------------------------------------------- */

if (!function_exists('isContactRateLimited')) {
    /**
     * True if this IP already sent CONTACT_MAX_PER_IP_PER_HOUR messages in
     * the last hour. Uses the database clock, so it matches created_at.
     * Returns false (and logs) if the check itself fails.
     */
    function isContactRateLimited($conn, $ip)
    {
        if ($ip === '') {
            return false;
        }

        try {
            $stmt = $conn->prepare(
                'SELECT COUNT(*) AS total FROM contact_messages
                 WHERE ip_address = ? AND created_at >= (NOW() - INTERVAL 1 HOUR)'
            );
            if (!$stmt) {
                throw new RuntimeException('prepare failed: ' . $conn->error);
            }
            $stmt->bind_param('s', $ip);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return (int) ($row['total'] ?? 0) >= CONTACT_MAX_PER_IP_PER_HOUR;
        } catch (Throwable $e) {
            error_log('Contact rate limiting unavailable — has contact-setup.sql been run? ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('isDuplicateContactMessage')) {
    /**
     * True if the same email already sent the exact same message in the last
     * 10 minutes (double click, browser back button, etc.).
     */
    function isDuplicateContactMessage($conn, $email, $message)
    {
        try {
            $stmt = $conn->prepare(
                'SELECT id FROM contact_messages
                 WHERE email = ? AND message = ? AND created_at >= (NOW() - INTERVAL 10 MINUTE)
                 LIMIT 1'
            );
            if (!$stmt) {
                throw new RuntimeException('prepare failed: ' . $conn->error);
            }
            $stmt->bind_param('ss', $email, $message);
            $stmt->execute();
            $stmt->store_result();
            $found = $stmt->num_rows > 0;
            $stmt->close();
            return $found;
        } catch (Throwable $e) {
            error_log('Contact duplicate check failed: ' . $e->getMessage());
            return false;
        }
    }
}

/* -------------------------------------------------------------------------
 * Saving + notification
 * ---------------------------------------------------------------------- */

if (!function_exists('createContactMessage')) {
    /**
     * Save one message. $data must come from validateContactForm().
     *
     * @return int New row id.
     * @throws RuntimeException if the insert fails.
     */
    function createContactMessage($conn, array $data, $ip = '', $userAgent = '', $userId = null)
    {
        $phone     = $data['phone'] !== '' ? $data['phone'] : null;
        $topic     = $data['topic'] !== '' ? $data['topic'] : null;
        $ip        = $ip !== '' ? $ip : null;
        $userAgent = $userAgent !== '' ? substr($userAgent, 0, 255) : null;
        $userId    = $userId !== null ? (int) $userId : null;

        $stmt = $conn->prepare(
            'INSERT INTO contact_messages (user_id, name, email, phone, topic, message, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        if (!$stmt) {
            throw new RuntimeException('Could not prepare contact insert: ' . $conn->error);
        }

        $stmt->bind_param(
            'isssssss',
            $userId,
            $data['name'],
            $data['email'],
            $phone,
            $topic,
            $data['message'],
            $ip,
            $userAgent
        );

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Could not save contact message: ' . $error);
        }

        $id = (int) $stmt->insert_id;
        $stmt->close();
        return $id;
    }
}

if (!function_exists('contactSendNotification')) {
    /**
     * Optionally email the site owner about a new message.
     * Does nothing unless the `contact_notify_email` setting holds a valid
     * address. Never throws: a mail problem must not lose the message,
     * which is already saved in the database.
     *
     * @return bool True if PHP handed the email to the mail system.
     */
    function contactSendNotification($conn, array $data, $messageId, $ip = '')
    {
        try {
            $to = contactSetting($conn, 'contact_notify_email');
            if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
                return false;
            }

            // Strip line breaks so nothing can be injected into mail headers.
            $siteName = trim(preg_replace('/[\r\n]+/', ' ', contactSetting($conn, 'site_name', 'Website')));
            $subject  = '[' . ($siteName !== '' ? $siteName : 'Website') . '] New contact message #' . (int) $messageId;

            $body  = "You have a new message from the website contact form.\n\n";
            $body .= 'Name:    ' . $data['name'] . "\n";
            $body .= 'Email:   ' . $data['email'] . "\n";
            $body .= 'Phone:   ' . ($data['phone'] !== '' ? $data['phone'] : '-') . "\n";
            $body .= 'Topic:   ' . ($data['topic'] !== '' ? $data['topic'] : '-') . "\n";
            $body .= 'IP:      ' . ($ip !== '' ? $ip : '-') . "\n\n";
            $body .= "Message:\n" . $data['message'] . "\n";

            // The visitor's email was validated with FILTER_VALIDATE_EMAIL,
            // which rejects line breaks, so it is safe in Reply-To.
            $headers = [
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
                'From: ' . $to,
                'Reply-To: ' . $data['email'],
            ];

            $sent = @mail($to, $subject, $body, implode("\r\n", $headers));
            if (!$sent) {
                error_log('Contact notification email could not be sent for message #' . (int) $messageId);
            }
            return (bool) $sent;
        } catch (Throwable $e) {
            error_log('Contact notification failed: ' . $e->getMessage());
            return false;
        }
    }
}