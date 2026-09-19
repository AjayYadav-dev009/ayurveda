<?php

if (!defined('APP_ENV')) {
    define('APP_ENV', getenv('APP_ENV') ?: 'development');
}

define("SITE_NAME", "Your Store");

if (APP_ENV === 'production') {
    $productionBaseUrl = getenv('BASE_URL');
    if ($productionBaseUrl === false || trim($productionBaseUrl) === '') {
        
        error_log('BASE_URL environment variable is not set in production.');
        die("Site configuration error.");
    }
    define("BASE_URL", $productionBaseUrl);
} else {
    define("BASE_URL", "http://localhost/ayurveda/");
}

error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('display_errors', APP_ENV === 'production' ? '0' : '1');

ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (APP_ENV === 'production') {
    ini_set('session.cookie_secure', '1');
}