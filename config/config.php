<?php

/**
 * Application environment.
 *
 * Set the APP_ENV environment variable to "production" on the live server
 * (e.g. in your hosting control panel, or via `SetEnv APP_ENV production`
 * in the Apache/vhost config). If it is not set at all -- which is the
 * case on a fresh local/XAMPP install -- this falls back to
 * "development" and nothing below changes from current behavior.
 */
if (!defined('APP_ENV')) {
    define('APP_ENV', getenv('APP_ENV') ?: 'development');
}

define("SITE_NAME", "Your Store");

/**
 * Base URL.
 *
 * Production must NOT use the localhost URL below. Set the BASE_URL
 * environment variable to the real production URL (including the
 * trailing slash), e.g. BASE_URL=https://www.example.com/
 *
 * If APP_ENV=production is set but BASE_URL is not, this fails fast (a
 * generic error, logged server-side) instead of silently falling back to
 * "localhost" or a placeholder domain in production.
 */
if (APP_ENV === 'production') {
    $productionBaseUrl = getenv('BASE_URL');
    if ($productionBaseUrl === false || trim($productionBaseUrl) === '') {
        // Fail fast instead of silently defining a placeholder/localhost
        // BASE_URL in production.
        error_log('BASE_URL environment variable is not set in production.');
        die("Site configuration error.");
    }
    define("BASE_URL", $productionBaseUrl);
} else {
    define("BASE_URL", "http://localhost/ayurveda/");
}

/**
 * Error handling.
 *
 * Production must never show PHP warnings/notices or database/SQL error
 * details to customers, but errors must still be logged so they can be
 * diagnosed. error_reporting(E_ALL) + log_errors=1 apply in every
 * environment; only whether errors are *displayed* changes.
 *
 * This does not introduce a new logging system -- log_errors=1 uses
 * PHP's/the server's existing error log (php.ini's error_log setting,
 * or the web server's default PHP error log if that isn't set).
 */
error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('display_errors', APP_ENV === 'production' ? '0' : '1');

/**
 * Session cookie hardening.
 *
 * These must be applied before session_start() is called. They are set
 * here because config.php is the project's central configuration file;
 * the file that actually calls session_start() was not part of the files
 * provided for this task, so please confirm config.php is included
 * before session_start() runs on every page (it should already be, since
 * SITE_NAME/BASE_URL need to be available everywhere) -- otherwise these
 * ini_set() calls will have no effect for that request.
 *
 * HttpOnly, strict-mode and SameSite=Lax are safe in every environment.
 * The Secure flag is only enabled in production because it would block
 * the session cookie entirely over plain HTTP during local development.
 */
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (APP_ENV === 'production') {
    ini_set('session.cookie_secure', '1');
}