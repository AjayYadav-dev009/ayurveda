<?php

if (!function_exists('generateCSRFToken')) {
    /**
     * Return the current session's CSRF token, creating one if needed.
     * Call this when rendering a form and echo the result into a hidden
     * input named csrf_token.
     *
     * @return string
     */
    function generateCSRFToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('validateCSRFToken')) {
    /**
     * Validate a submitted CSRF token against the one stored in the
     * session. Safe against timing attacks (hash_equals) and against a
     * malformed/array submission (is_string guard — hash_equals() throws
     * a TypeError on PHP 8 if given a non-string).
     *
     * @param mixed $token Raw value from $_POST['csrf_token'].
     * @return bool
     */
    function validateCSRFToken($token)
    {
        return isset($_SESSION['csrf_token']) &&
               is_string($token) &&
               $token !== '' &&
               hash_equals($_SESSION['csrf_token'], $token);
    }
}