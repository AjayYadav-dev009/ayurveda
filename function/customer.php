<?php
require_once __DIR__ . '/../config/database.php';

/**
 * =============================================================================
 * Customer Authentication Functions
 * =============================================================================
 * Everything related to the storefront `users` table: registration, login,
 * logout, and the password_resets flow (forgot/reset password).
 *
 * Session keys used (kept separate from the admin session, which uses
 * admin_id / admin_name / admin_email, so a browser can be logged into the
 * admin panel and the storefront at the same time without collisions):
 *   $_SESSION['customer_id']
 *   $_SESSION['customer_name']
 *   $_SESSION['customer_email']
 *
 * Table: users
 *   id          bigint UNSIGNED PK
 *   name        varchar(150)
 *   email       varchar(191)  UNIQUE
 *   phone       varchar(20)   nullable
 *   password    varchar(255)  (password_hash() output)
 *   status      enum('Active','Inactive','Blocked')
 *   created_at, updated_at
 *
 * Table: password_resets
 *   id          bigint UNSIGNED PK
 *   user_id     bigint UNSIGNED  (FK -> users.id, ON DELETE CASCADE)
 *   token_hash  char(64)         (sha256 hex digest of the plaintext token)
 *   expires_at  datetime
 *   used_at     datetime nullable
 *   created_at  timestamp
 * =============================================================================
 *
 * NOTE: this file must be safe to require_once from any page regardless of
 * what else has already been loaded, so every function below is wrapped in
 * function_exists() per project convention.
 */

/** How long a password reset link stays valid. */
if (!defined('PASSWORD_RESET_TTL_MINUTES')) {
    define('PASSWORD_RESET_TTL_MINUTES', 60);
}

/**
 * Login rate limiting.
 * - Per email: protects one account from being brute-forced regardless of
 *   which IP the attempts come from.
 * - Per IP: protects against one source hammering many different emails
 *   (credential stuffing). Set higher than the per-email limit since a
 *   shared/NAT IP (an office, a mobile carrier) can have several genuine
 *   users failing a password here and there.
 * Both windows are the same rolling LOGIN_LOCKOUT_MINUTES.
 * See login_attempts table (add-login-attempts-table.sql).
 */
if (!defined('LOGIN_MAX_ATTEMPTS_PER_EMAIL')) {
    define('LOGIN_MAX_ATTEMPTS_PER_EMAIL', 5);
}
if (!defined('LOGIN_MAX_ATTEMPTS_PER_IP')) {
    define('LOGIN_MAX_ATTEMPTS_PER_IP', 20);
}
if (!defined('LOGIN_LOCKOUT_MINUTES')) {
    define('LOGIN_LOCKOUT_MINUTES', 15);
}

const USER_STATUSES = ['Active', 'Inactive', 'Blocked'];

if (!function_exists('registerUser')) {
    /**
     * Register a new customer account.
     *
     * @param mysqli $conn
     * @param string $name
     * @param string $email
     * @param string|null $phone
     * @param string $password Plaintext password (validated + hashed here).
     * @return int Newly created user id.
     * @throws InvalidArgumentException On bad input or a duplicate email.
     * @throws Exception On a database error.
     */
    function registerUser($conn, $name, $email, $phone, $password)
    {
        $name = trim($name);
        $email = trim($email);
        $phone = $phone !== null ? trim($phone) : null;
        if ($phone === '') {
            $phone = null;
        }

        if ($name === '') {
            throw new InvalidArgumentException('Name is required.');
        }

        if ($email === '') {
            throw new InvalidArgumentException('Email is required.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Enter a valid email address.');
        }

        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters.');
        }

        if (getUserByEmail($conn, $email)) {
            throw new InvalidArgumentException('An account with this email already exists.');
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO users (name, email, phone, password, status) VALUES (?, ?, ?, ?, 'Active')"
        );
        if (!$stmt) {
            throw new Exception('Error preparing statement: ' . mysqli_error($conn));
        }

        $stmt->bind_param('ssss', $name, $email, $phone, $hashedPassword);

        try {
            $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                throw new InvalidArgumentException('An account with this email already exists.');
            }
            throw new Exception('Error registering user: ' . mysqli_error($conn));
        }

        return (int) $conn->insert_id;
    }
}

if (!function_exists('getUserByEmail')) {
    /**
     * @param mysqli $conn
     * @param string $email
     * @return array|null
     * @throws Exception
     */
    function getUserByEmail($conn, $email)
    {
        $sql = "SELECT id, name, email, phone, password, status, created_at, updated_at
                FROM users WHERE email = ? LIMIT 1";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Error preparing statement: ' . mysqli_error($conn));
        }

        $stmt->bind_param('s', $email);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    }
}

if (!function_exists('getUserById')) {
    /**
     * @param mysqli $conn
     * @param int $id
     * @return array|null
     * @throws Exception
     */
    function getUserById($conn, $id)
    {
        $sql = "SELECT id, name, email, phone, status, created_at, updated_at
                FROM users WHERE id = ? LIMIT 1";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Error preparing statement: ' . mysqli_error($conn));
        }

        $id = (int) $id;
        $stmt->bind_param('i', $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc() ?: null;
    }
}

if (!function_exists('verifyUserPassword')) {
    /**
     * Verify a plaintext password against a stored password_hash().
     *
     * @param string $password Plaintext password as submitted by the user.
     * @param string $hash Stored hash from users.password.
     * @return bool
     */
    function verifyUserPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
}

if (!function_exists('countRecentLoginAttempts')) {
    /**
     * Count failed login attempts for a given email or IP within the
     * rate-limiting window. Returns null (rather than throwing) if the
     * login_attempts table doesn't exist yet or the query otherwise fails,
     * so that a missing migration degrades to "rate limiting temporarily
     * off" instead of breaking login for everyone. The failure is still
     * logged server-side so it doesn't go unnoticed.
     *
     * @param mysqli $conn
     * @param string $column 'email' or 'ip_address'
     * @param string $value
     * @param string $windowStart 'Y-m-d H:i:s'
     * @return int|null
     */
    function countRecentLoginAttempts($conn, $column, $value, $windowStart)
    {
        if (!in_array($column, ['email', 'ip_address'], true)) {
            throw new InvalidArgumentException('Invalid column for countRecentLoginAttempts().');
        }

        try {
            $stmt = $conn->prepare(
                "SELECT COUNT(*) AS attempts FROM login_attempts
                 WHERE {$column} = ? AND success = 0 AND attempted_at >= ?"
            );
            if (!$stmt) {
                throw new Exception('Error preparing statement: ' . mysqli_error($conn));
            }
            $stmt->bind_param('ss', $value, $windowStart);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            return (int) ($row['attempts'] ?? 0);
        } catch (Exception $e) {
            error_log('Login rate limiting unavailable — has add-login-attempts-table.sql been run? ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('isLoginRateLimited')) {
    /**
     * @param mysqli $conn
     * @param string $email
     * @param string $ip
     * @return bool True if this email or IP has too many recent failed
     *              login attempts and should be blocked from trying again.
     */
    function isLoginRateLimited($conn, $email, $ip)
    {
        $windowStart = date('Y-m-d H:i:s', time() - LOGIN_LOCKOUT_MINUTES * 60);

        $emailAttempts = countRecentLoginAttempts($conn, 'email', $email, $windowStart);
        if ($emailAttempts !== null && $emailAttempts >= LOGIN_MAX_ATTEMPTS_PER_EMAIL) {
            return true;
        }

        $ipAttempts = countRecentLoginAttempts($conn, 'ip_address', $ip, $windowStart);
        if ($ipAttempts !== null && $ipAttempts >= LOGIN_MAX_ATTEMPTS_PER_IP) {
            return true;
        }

        return false;
    }
}

if (!function_exists('recordLoginAttempt')) {
    /**
     * Log one login attempt (success or failure) for rate-limiting
     * purposes. Silently no-ops (logging server-side) if the
     * login_attempts table doesn't exist yet — never breaks login.
     *
     * @param mysqli $conn
     * @param string $email
     * @param string $ip
     * @param bool $success
     */
    function recordLoginAttempt($conn, $email, $ip, $success)
    {
        try {
            $stmt = $conn->prepare(
                "INSERT INTO login_attempts (email, ip_address, success, attempted_at) VALUES (?, ?, ?, NOW())"
            );
            if (!$stmt) {
                throw new Exception('Error preparing statement: ' . mysqli_error($conn));
            }
            $successInt = $success ? 1 : 0;
            $stmt->bind_param('ssi', $email, $ip, $successInt);
            $stmt->execute();
        } catch (Exception $e) {
            error_log('Could not record login attempt — has add-login-attempts-table.sql been run? ' . $e->getMessage());
        }
    }
}

if (!function_exists('loginUser')) {
    /**
     * Attempt to log a customer in: looks the account up by email, checks its
     * status, verifies the password, and — only on full success — starts the
     * session for them (regenerating the session id to prevent session
     * fixation, exactly like the admin login does).
     *
     * @param mysqli $conn
     * @param string $email
     * @param string $password Plaintext password.
     * @return array The logged-in user's row (without the password hash).
     * @throws InvalidArgumentException On any authentication failure. The
     *         message is always generic ("Invalid email or password.") for
     *         bad credentials so a caller can't use this to enumerate which
     *         emails are registered — the only exception is a Blocked/Inactive
     *         account, which is intentionally reported so real customers know
     *         what's going on. Assumes a session is already active (see
     *         includes/session.php).
     * @throws Exception On a database error.
     */
    function loginUser($conn, $email, $password, $ipAddress = null)
    {
        $email = trim($email);
        $ipAddress = $ipAddress ?? ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

        if ($email === '' || $password === '') {
            throw new InvalidArgumentException('Invalid email or password.');
        }

        // Rate limiting: checked before touching the password at all, so a
        // locked-out attacker can't keep using this endpoint as a password
        // oracle. Message is the same regardless of whether the email
        // exists, so it can't be used to enumerate accounts either.
        if (isLoginRateLimited($conn, $email, $ipAddress)) {
            throw new InvalidArgumentException(
                'Too many login attempts. Please wait ' . LOGIN_LOCKOUT_MINUTES . ' minutes and try again.'
            );
        }

        $user = getUserByEmail($conn, $email);

        if (!$user || !verifyUserPassword($password, $user['password'])) {
            recordLoginAttempt($conn, $email, $ipAddress, false);
            throw new InvalidArgumentException('Invalid email or password.');
        }

        if ($user['status'] !== 'Active') {
            recordLoginAttempt($conn, $email, $ipAddress, false);
            throw new InvalidArgumentException(
                $user['status'] === 'Blocked'
                    ? 'This account has been blocked. Please contact support.'
                    : 'This account is inactive. Please contact support.'
            );
        }

        recordLoginAttempt($conn, $email, $ipAddress, true);

        session_regenerate_id(true);

        $_SESSION['customer_id'] = (int) $user['id'];
        $_SESSION['customer_name'] = $user['name'];
        $_SESSION['customer_email'] = $user['email'];

        unset($user['password']);
        return $user;
    }
}

if (!function_exists('logoutUser')) {
    /**
     * Log the current customer out: clears their session data and rotates the
     * session id so nothing from the authenticated session can be replayed.
     * Intentionally does NOT call session_destroy() — a guest cart may be
     * stored elsewhere in $_SESSION and must survive logout.
     */
    function logoutUser()
    {
        unset(
            $_SESSION['customer_id'],
            $_SESSION['customer_name'],
            $_SESSION['customer_email']
        );

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
}

if (!function_exists('isCustomerLogin')) {
    /**
     * @param mysqli|null $conn When omitted, this is a fast session-only
     *        check (unchanged behavior). When a connection is passed, the
     *        account's status is re-checked against the database, and a
     *        customer whose account was blocked/deactivated since they
     *        logged in is transparently logged out. Pass $conn from any
     *        page that guards real account access (order history, profile,
     *        etc.); the plain no-arg form remains fine for cheap checks
     *        like "am I already logged in" on the login/register pages.
     * @return bool True if a customer is currently logged in.
     */
    function isCustomerLogin($conn = null)
    {
        if (!isset($_SESSION['customer_id'])) {
            return false;
        }

        if ($conn === null) {
            return true;
        }

        try {
            $user = getUserById($conn, $_SESSION['customer_id']);
        } catch (Exception $e) {
            // Fail safe on a transient DB error rather than logging
            // everyone out because a status check couldn't run.
            return true;
        }

        if (!$user || $user['status'] !== 'Active') {
            logoutUser();
            return false;
        }

        return true;
    }
}

if (!function_exists('requireCustomerLogin')) {
    /**
     * Guard helper for pages that require a logged-in customer (account
     * dashboard, order history, profile, etc.). Not currently wired into
     * any page — those files weren't part of this phase — but provided so
     * future protected account pages have one shared guard to call instead
     * of re-implementing the isset($_SESSION['customer_id']) check.
     *
     * Usage at the top of a protected page (after requiring
     * includes/session.php, config/database.php and function/customer.php):
     *     requireCustomerLogin($conn);
     *
     * @param mysqli|null $conn Passed through to isCustomerLogin() for
     *        status revalidation; omit for a session-only check.
     * @param string|null $loginUrl Override the login page to redirect to.
     */
    function requireCustomerLogin($conn = null, $loginUrl = null)
    {
        if (isCustomerLogin($conn)) {
            return;
        }

        $loginUrl = $loginUrl ?? (defined('BASE_URL') ? BASE_URL . 'account/login.php' : 'login.php');

        if (isset($_SERVER['REQUEST_URI'])) {
            $loginUrl .= '?redirect=' . urlencode(ltrim($_SERVER['REQUEST_URI'], '/'));
        }

        redirect($loginUrl);
    }
}

if (!function_exists('createPasswordResetRequest')) {
    /**
     * Start (or resume) a password reset for the given email.
     *
     * Always behaves the same way whether or not the email is registered, so a
     * caller can show one generic "check your email" message either way and
     * never leak which addresses have accounts.
     *
     * @param mysqli $conn
     * @param string $email
     * @return string|null The plaintext token to embed in the reset link
     *                      (e.g. reset-password.php?token=...), or null if no
     *                      account exists for that email (nothing was created).
     * @throws Exception On a database error.
     */
    function createPasswordResetRequest($conn, $email)
    {
        $user = getUserByEmail($conn, trim($email));
        if (!$user) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + PASSWORD_RESET_TTL_MINUTES * 60);
        $userId = (int) $user['id'];

        // Invalidate any earlier outstanding reset requests for this user so
        // only the newest link works.
        $invalidate = $conn->prepare(
            "UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL"
        );
        if (!$invalidate) {
            throw new Exception('Error preparing statement: ' . mysqli_error($conn));
        }
        $invalidate->bind_param('i', $userId);
        $invalidate->execute();

        $stmt = $conn->prepare(
            "INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)"
        );
        if (!$stmt) {
            throw new Exception('Error preparing statement: ' . mysqli_error($conn));
        }
        $stmt->bind_param('iss', $userId, $tokenHash, $expiresAt);
        $stmt->execute();

        return $token;
    }
}

if (!function_exists('getValidPasswordReset')) {
    /**
     * Look up a password reset request by its plaintext token and confirm it's
     * still usable (exists, not expired, not already used).
     *
     * @param mysqli $conn
     * @param string $token Plaintext token from the reset link's query string.
     * @return array|null The password_resets row (plus user_id) if valid, else null.
     * @throws Exception On a database error.
     */
    function getValidPasswordReset($conn, $token)
    {
        if ($token === '' || $token === null) {
            return null;
        }

        $tokenHash = hash('sha256', $token);

        $sql = "SELECT id, user_id, expires_at, used_at
                FROM password_resets
                WHERE token_hash = ?
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Error preparing statement: ' . mysqli_error($conn));
        }
        $stmt->bind_param('s', $tokenHash);
        $stmt->execute();
        $reset = $stmt->get_result()->fetch_assoc();

        if (!$reset) {
            return null;
        }
        if ($reset['used_at'] !== null) {
            return null;
        }
        if (strtotime($reset['expires_at']) < time()) {
            return null;
        }

        return $reset;
    }
}

if (!function_exists('resetUserPassword')) {
    /**
     * Complete a password reset: validates the token again, updates the
     * user's password, and marks the token used so it can't be replayed.
     * Runs in a transaction so a failure partway through never leaves a
     * "used" token with an unchanged password (or vice versa).
     *
     * @param mysqli $conn
     * @param string $token Plaintext token from the reset link.
     * @param string $newPassword
     * @return bool
     * @throws InvalidArgumentException If the token is invalid/expired/used, or the new password is too short.
     * @throws Exception On a database error.
     */
    function resetUserPassword($conn, $token, $newPassword)
    {
        if (strlen($newPassword) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters.');
        }

        $reset = getValidPasswordReset($conn, $token);
        if (!$reset) {
            throw new InvalidArgumentException('This password reset link is invalid or has expired.');
        }

        $conn->begin_transaction();

        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $userId = (int) $reset['user_id'];

            $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            if (!$updateStmt) {
                throw new Exception('Error preparing statement: ' . mysqli_error($conn));
            }
            $updateStmt->bind_param('si', $hashedPassword, $userId);
            $updateStmt->execute();

            $markUsedStmt = $conn->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = ?");
            if (!$markUsedStmt) {
                throw new Exception('Error preparing statement: ' . mysqli_error($conn));
            }
            $resetId = (int) $reset['id'];
            $markUsedStmt->bind_param('i', $resetId);
            $markUsedStmt->execute();

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }

        return true;
    }
}