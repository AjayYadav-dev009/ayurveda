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
 */

/** How long a password reset link stays valid. */
if (!defined('PASSWORD_RESET_TTL_MINUTES')) {
    define('PASSWORD_RESET_TTL_MINUTES', 60);
}

const USER_STATUSES = ['Active', 'Inactive', 'Blocked'];

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
 *         what's going on. If it hasn't been called explicitly, session_start()
 *         is assumed to already be active (see includes/session.php).
 * @throws Exception On a database error.
 */
function loginUser($conn, $email, $password)
{
    $email = trim($email);

    if ($email === '' || $password === '') {
        throw new InvalidArgumentException('Invalid email or password.');
    }

    $user = getUserByEmail($conn, $email);

    if (!$user || !verifyUserPassword($password, $user['password'])) {
        throw new InvalidArgumentException('Invalid email or password.');
    }

    if ($user['status'] !== 'Active') {
        throw new InvalidArgumentException(
            $user['status'] === 'Blocked'
                ? 'This account has been blocked. Please contact support.'
                : 'This account is inactive. Please contact support.'
        );
    }

    session_regenerate_id(true);

    $_SESSION['customer_id'] = (int) $user['id'];
    $_SESSION['customer_name'] = $user['name'];
    $_SESSION['customer_email'] = $user['email'];

    unset($user['password']);
    return $user;
}

/**
 * Log the current customer out: clears their session data and rotates the
 * session id so nothing from the authenticated session can be replayed.
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

/**
 * @return bool True if a customer is currently logged in.
 */
function isCustomerLogin()
{
    return isset($_SESSION['customer_id']);
}

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
