<?php

/**
 * function/dosha.php
 *
 * Helpers for the "Take the Ayurvedic Dosha Test" CTA + modal.
 *
 * Stores submissions in `dosha_leads` (see sql/dosha-test-setup.sql — that
 * migration must be run once before this file is used).
 *
 * Mirrors function/contact.php's shape on purpose: same style of
 * validate -> rate-limit -> save, same function_exists() guards, same
 * "never break the page over a DB hiccup" attitude via error_log().
 */

if (!defined('DOSHA_MAX_PER_IP_PER_HOUR')) {
    define('DOSHA_MAX_PER_IP_PER_HOUR', 5);
}

/** Allowed values for the Gender field — keep the <select> in the form in sync with this. */
const DOSHA_GENDER_OPTIONS = ['Female', 'Male', 'Other', 'Prefer not to say'];

/* -------------------------------------------------------------------------
 * Small utilities
 * ---------------------------------------------------------------------- */

if (!function_exists('doshaStrLen')) {
    /** Character count that is safe for Hindi / accented text. */
    function doshaStrLen($value)
    {
        return function_exists('mb_strlen') ? mb_strlen((string) $value, 'UTF-8') : strlen((string) $value);
    }
}

if (!function_exists('doshaClientIp')) {
    /** Only REMOTE_ADDR is trusted (X-Forwarded-For can be faked by anyone). */
    function doshaClientIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
    }
}

if (!function_exists('doshaGenerateSessionToken')) {
    /** A UUID v4, used to link a lead to its (future) test session/result without requiring an account. */
    function doshaGenerateSessionToken()
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40); // version 4
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80); // variant

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

/* -------------------------------------------------------------------------
 * Validation
 * ---------------------------------------------------------------------- */

if (!function_exists('validateDoshaForm')) {
    /**
     * Clean and validate the submitted form.
     *
     * @param array $input Usually $_POST.
     * @return array{data: array, errors: array} `data` always holds the
     *         cleaned values (so the form can be re-filled); `errors` is
     *         keyed by field name and empty when everything is valid.
     */
    function validateDoshaForm(array $input)
    {
        $str = static function ($key) use ($input) {
            return isset($input[$key]) && is_scalar($input[$key]) ? trim((string) $input[$key]) : '';
        };

        $errors = [];

        // Name: collapse whitespace, drop control characters (same rule as contact.php).
        $name = preg_replace('/[^\P{C}]+/u', ' ', $str('full_name'));
        $name = $name === null ? '' : trim(preg_replace('/\s+/u', ' ', $name));

        $email    = $str('email');
        $dobInput = $str('date_of_birth'); // expected DD/MM/YYYY from the date picker
        $gender   = $str('gender');
        $mobile   = $str('mobile');
        $location = $str('location');
        $goal     = $str('wellness_goal');

        // Honeypot: a real visitor never fills this hidden field in.
        // Named away from "website"/"url"/etc. because browser/password-manager
        // autofill can silently fill fields with those common names even
        // though the field is visually hidden, causing real submissions to
        // be mistaken for bots and rejected.
        if ($str('hp_check') !== '') {
            $errors['form'] = 'Submission rejected.';
        }

        if ($name === '') {
            $errors['full_name'] = 'Please enter your full name.';
        } elseif (doshaStrLen($name) < 2) {
            $errors['full_name'] = 'Please enter your full name.';
        } elseif (doshaStrLen($name) > 150) {
            $errors['full_name'] = 'Your name is too long.';
        }

        if ($email === '') {
            $errors['email'] = 'Please enter your email address.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || doshaStrLen($email) > 191) {
            $errors['email'] = 'Enter a valid email address.';
        }

        $dob = null;
        if ($dobInput === '') {
            $errors['date_of_birth'] = 'Please enter your date of birth.';
        } else {
            // Accept DD/MM/YYYY (what the form shows) or YYYY-MM-DD (native <input type="date">).
            $parsed = DateTime::createFromFormat('d/m/Y', $dobInput) ?: DateTime::createFromFormat('Y-m-d', $dobInput);
            if (!$parsed) {
                $errors['date_of_birth'] = 'Enter a valid date as DD/MM/YYYY.';
            } else {
                $today = new DateTime('today');
                $age   = $parsed->diff($today)->y;
                if ($parsed > $today) {
                    $errors['date_of_birth'] = 'Date of birth cannot be in the future.';
                } elseif ($age < 12 || $age > 110) {
                    $errors['date_of_birth'] = 'Please enter a valid date of birth.';
                } else {
                    $dob = $parsed->format('Y-m-d');
                }
            }
        }

        if ($gender === '') {
            $errors['gender'] = 'Please select your gender.';
        } elseif (!in_array($gender, DOSHA_GENDER_OPTIONS, true)) {
            $errors['gender'] = 'Please choose one of the listed options.';
        }

        if ($mobile === '') {
            $errors['mobile'] = 'Please enter your mobile number.';
        } elseif (!preg_match('/^[0-9+\-\s().]{7,20}$/D', $mobile)) {
            $errors['mobile'] = 'Enter a valid mobile number.';
        }

        if ($location === '') {
            $errors['location'] = 'Please enter your city or state.';
        } elseif (doshaStrLen($location) > 150) {
            $errors['location'] = 'That location is too long.';
        }

        if ($goal !== '' && doshaStrLen($goal) > 150) {
            $errors['wellness_goal'] = 'That goal is too long.';
        }

        return [
            'data' => [
                'full_name'     => $name,
                'email'         => $email,
                'date_of_birth' => $dob, // 'Y-m-d' or null if invalid
                'gender'        => $gender,
                'mobile'        => $mobile,
                'location'      => $location,
                'wellness_goal' => $goal !== '' ? $goal : null,
            ],
            'errors' => $errors,
        ];
    }
}

/* -------------------------------------------------------------------------
 * Abuse protection (same idea as isContactRateLimited() in contact.php)
 * ---------------------------------------------------------------------- */

if (!function_exists('isDoshaRateLimited')) {
    /**
     * True if this IP already started DOSHA_MAX_PER_IP_PER_HOUR tests in the
     * last hour. Returns false (and logs) if the check itself fails, so a
     * missing migration degrades to "rate limiting temporarily off" rather
     * than breaking the form for everyone.
     */
    function isDoshaRateLimited($conn, $ip)
    {
        if ($ip === '') {
            return false;
        }

        try {
            $stmt = $conn->prepare(
                'SELECT COUNT(*) AS total FROM dosha_leads
                 WHERE ip_address = ? AND created_at >= (NOW() - INTERVAL 1 HOUR)'
            );
            if (!$stmt) {
                throw new RuntimeException('prepare failed: ' . $conn->error);
            }
            $stmt->bind_param('s', $ip);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return (int) ($row['total'] ?? 0) >= DOSHA_MAX_PER_IP_PER_HOUR;
        } catch (Throwable $e) {
            error_log('Dosha rate limiting unavailable — has dosha-test-setup.sql been run? ' . $e->getMessage());
            return false;
        }
    }
}

/* -------------------------------------------------------------------------
 * Saving + lookup
 * ---------------------------------------------------------------------- */

if (!function_exists('createDoshaLead')) {
    /**
     * Save one lead and start its test session. $data must come from
     * validateDoshaForm().
     *
     * @return array{id: int, session_token: string}
     * @throws RuntimeException if the insert fails.
     */
    function createDoshaLead($conn, array $data, $ip = '', $userAgent = '', $userId = null)
    {
        $sessionToken = doshaGenerateSessionToken();
        $ip           = $ip !== '' ? $ip : null;
        $userAgent    = $userAgent !== '' ? substr($userAgent, 0, 255) : null;
        $userId       = $userId !== null ? (int) $userId : null;

        $stmt = $conn->prepare(
            'INSERT INTO dosha_leads
                (user_id, session_token, full_name, email, date_of_birth, gender, mobile, location, wellness_goal, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        if (!$stmt) {
            throw new RuntimeException('Could not prepare dosha lead insert: ' . $conn->error);
        }

        $stmt->bind_param(
            'issssssssss',
            $userId,
            $sessionToken,
            $data['full_name'],
            $data['email'],
            $data['date_of_birth'],
            $data['gender'],
            $data['mobile'],
            $data['location'],
            $data['wellness_goal'],
            $ip,
            $userAgent
        );

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Could not save dosha lead: ' . $error);
        }

        $id = (int) $stmt->insert_id;
        $stmt->close();

        return ['id' => $id, 'session_token' => $sessionToken];
    }
}

if (!function_exists('getDoshaLeadByToken')) {
    /**
     * Look up a lead by its session token (e.g. to greet the visitor by
     * name on the test/result page, or to resume an in-progress test).
     *
     * @return array|null
     */
    function getDoshaLeadByToken($conn, $token)
    {
        $token = trim((string) $token);
        if ($token === '') {
            return null;
        }

        $stmt = $conn->prepare(
            'SELECT id, user_id, session_token, full_name, email, date_of_birth, gender, mobile, location,
                    wellness_goal, status, dosha_result, created_at
             FROM dosha_leads WHERE session_token = ? LIMIT 1'
        );
        if (!$stmt) {
            throw new RuntimeException('Could not prepare dosha lead lookup: ' . $conn->error);
        }
        $stmt->bind_param('s', $token);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    }
}


/* -----------------------------------------------------------------------
 * Admin: listing, viewing, status changes and deletion for the dosha
 * leads inbox (admin/dosha/index.php, view.php, update-status.php, delete.php)
 * -------------------------------------------------------------------- */

if (!function_exists('DOSHA_LEAD_STATUSES')) {
    /** Allowed values of dosha_leads.status, in display order. */
    function DOSHA_LEAD_STATUSES()
    {
        return ['started', 'completed'];
    }
}

if (!function_exists('getDoshaLeads')) {
    /**
     * Admin listing, newest first, with optional filters.
     *
     * @param array $filters {
     *     @var string $search Matches full_name / email / mobile / location (LIKE, case-insensitive).
     *     @var string $status One of DOSHA_LEAD_STATUSES(); ignored if not a valid value.
     *     @var string $date   Calendar day in Y-m-d format; matches DATE(created_at).
     * }
     * @return array[] Rows from dosha_leads.
     * @throws RuntimeException if the query cannot be prepared.
     */
    function getDoshaLeads($conn, array $filters = [])
    {
        $where  = [];
        $types  = '';
        $params = [];

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $like = '%' . strtr($search, ['\\' => '\\\\', '%' => '\\%', '_' => '\\_']) . '%';
            $where[] = '(full_name LIKE ? OR email LIKE ? OR mobile LIKE ? OR location LIKE ?)';
            $types  .= 'ssss';
            array_push($params, $like, $like, $like, $like);
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '' && in_array($status, DOSHA_LEAD_STATUSES(), true)) {
            $where[]  = 'status = ?';
            $types   .= 's';
            $params[] = $status;
        }

        $date = trim((string) ($filters['date'] ?? ''));
        if ($date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date)) {
            $where[]  = 'DATE(created_at) = ?';
            $types   .= 's';
            $params[] = $date;
        }

        $sql = 'SELECT * FROM dosha_leads';
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY created_at DESC';

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException('Database query failed: ' . $conn->error);
        }
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }
}

if (!function_exists('getDoshaLeadCounts')) {
    /** @return array{total:int,started:int,completed:int} */
    function getDoshaLeadCounts($conn)
    {
        $counts = ['total' => 0, 'started' => 0, 'completed' => 0];

        $result = $conn->query('SELECT status, COUNT(*) AS total FROM dosha_leads GROUP BY status');
        if ($result === false) {
            throw new RuntimeException('Database query failed: ' . $conn->error);
        }

        while ($row = $result->fetch_assoc()) {
            if (isset($counts[$row['status']])) {
                $counts[$row['status']] = (int) $row['total'];
            }
            $counts['total'] += (int) $row['total'];
        }

        return $counts;
    }
}

if (!function_exists('getDoshaLeadById')) {
    /** Numeric-id lookup for the admin panel (see getDoshaLeadByToken() for the public token lookup). */
    function getDoshaLeadById($conn, $id)
    {
        $id = (int) $id;

        $stmt = $conn->prepare('SELECT * FROM dosha_leads WHERE id = ? LIMIT 1');
        if ($stmt === false) {
            throw new RuntimeException('Database query failed: ' . $conn->error);
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }
}

if (!function_exists('updateDoshaLeadStatus')) {
    /**
     * @throws InvalidArgumentException if $status is not a recognised value.
     * @throws RuntimeException if the update fails.
     */
    function updateDoshaLeadStatus($conn, $id, $status)
    {
        if (!in_array($status, DOSHA_LEAD_STATUSES(), true)) {
            throw new InvalidArgumentException('Invalid dosha lead status.');
        }

        $id = (int) $id;

        $stmt = $conn->prepare('UPDATE dosha_leads SET status = ? WHERE id = ?');
        if ($stmt === false) {
            throw new RuntimeException('Database query failed: ' . $conn->error);
        }
        $stmt->bind_param('si', $status, $id);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Could not update lead status: ' . $error);
        }
        $stmt->close();

        return true;
    }
}

if (!function_exists('deleteDoshaLead')) {
    /** @throws RuntimeException if the delete fails. */
    function deleteDoshaLead($conn, $id)
    {
        $id = (int) $id;

        $stmt = $conn->prepare('DELETE FROM dosha_leads WHERE id = ?');
        if ($stmt === false) {
            throw new RuntimeException('Database query failed: ' . $conn->error);
        }
        $stmt->bind_param('i', $id);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Could not delete lead: ' . $error);
        }
        $affected = $stmt->affected_rows;
        $stmt->close();

        return $affected > 0;
    }
}