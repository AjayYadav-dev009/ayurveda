<?php

//if (!defined('APP_ENV')) {
    //define('APP_ENV', getenv('APP_ENV') ?: 'development');
//}

//define("SITE_NAME", "Your Store");

//if (APP_ENV === 'production') {
    //$productionBaseUrl = getenv('BASE_URL');
    //if ($productionBaseUrl === false || trim($productionBaseUrl) === '') {
        
        //error_log('BASE_URL environment variable is not set in production.');
        //die("Site configuration error.");
    //}
    //define("BASE_URL", $productionBaseUrl);
//} else {
    //define("BASE_URL", "http://localhost/ayurveda/");
//}

//error_reporting(E_ALL);
//ini_set('log_errors', '1');
//ini_set('display_errors', APP_ENV === 'production' ? '0' : '1');

//ini_set('session.use_strict_mode', '1');
//ini_set('session.cookie_httponly', '1');
//ini_set('session.cookie_samesite', 'Lax');
//if (APP_ENV === 'production') {
    //ini_set('session.cookie_secure', '1');
//}

/*
|--------------------------------------------------------------------------
| Application Environment
|--------------------------------------------------------------------------
| Automatically detects whether the site is running locally or live.
*/

$hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';

if (
    $hostname === 'localhost' ||
    $hostname === '127.0.0.1' ||
    str_starts_with($hostname, 'localhost:')
) {
    define('APP_ENV', 'development');
} else {
    define('APP_ENV', 'production');
}


/*
|--------------------------------------------------------------------------
| Site Information
|--------------------------------------------------------------------------
*/

define('SITE_NAME', 'Vedorishi');


/*
|--------------------------------------------------------------------------
| Base URL
|--------------------------------------------------------------------------
*/

if (APP_ENV === 'production') {

    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_SERVER['SERVER_PORT'] ?? '') == 443)
    );

    define(
        'BASE_URL',
        ($isHttps ? 'https://' : 'http://') . 'vedorishi.ayurveda.daurproductions.com/'
    );

} else {

    define(
        'BASE_URL',
        'http://localhost/ayurveda/'
    );
}


/*
|--------------------------------------------------------------------------
| Error Reporting
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);

ini_set('log_errors', '1');

if (APP_ENV === 'production') {
    ini_set('display_errors', '0');
} else {
    ini_set('display_errors', '1');
}


/*
|--------------------------------------------------------------------------
| Session Security
|--------------------------------------------------------------------------
*/

ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');

if (APP_ENV === 'production') {
    ini_set('session.cookie_secure', '0');
}