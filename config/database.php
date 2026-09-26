<?php

    //$isProduction = (getenv('APP_ENV') ?: 'development') === 'production';

    //$envHost     = getenv('DB_HOST');
    //$envPort     = getenv('DB_PORT');
    //$envName     = getenv('DB_NAME');
    //$envUser     = getenv('DB_USER');
    //$envPassword = getenv('DB_PASSWORD');

    //if ($isProduction) {
        //if ($envHost === false || $envName === false || $envUser === false || $envPassword === false) {
            //error_log('Production database credentials are not fully set via environment variables (DB_HOST/DB_NAME/DB_USER/DB_PASSWORD).');
            //die("Database connection failed.");
        //}
        //$host     = $envHost;
        //$port     = $envPort !== false ? (int) $envPort : 3306;
        //$dbname   = $envName;
        //$username = $envUser;
        //$password = $envPassword;
    //} else {
        //$host     = $envHost !== false ? $envHost : "localhost";
       //$port     = $envPort !== false ? (int) $envPort : 3306;
        //$dbname   = $envName !== false ? $envName : "ayurveda_db";
        //$username = $envUser !== false ? $envUser : "root";
        //$password = $envPassword !== false ? $envPassword : "";
    //}

    //try {
        //$conn = new mysqli(
            //$host,
            //$username,
            //$password,
            //$dbname,
            //(int) $port
        //);
    //} catch (mysqli_sql_exception $e) {
        //error_log("Database connection failed: " . $e->getMessage());
        //die("Database connection failed.");
    //}

    //if ($conn->connect_error) {
        //error_log("Database connection failed: " . $conn->connect_error);
        //die("Database connection failed.");
    //}

    //$conn->set_charset("utf8mb4");

//$host     = "localhost";
//$port     = 3306;
//$dbname   = "ayurveda_db";
//$username = "root";
//$password = "";



/*
|--------------------------------------------------------------------------
| Database Configuration
|--------------------------------------------------------------------------
| Automatically switches between local XAMPP and production.
|
| Local:
|   Database: ayurveda_db
|   User: root
|   Password: empty
|
| Production:
|   Database: daurp0duction_ayurveda_db
|   User: daurp0duction_vedorishi
|   Password: your MilesWeb password
|--------------------------------------------------------------------------
*/


// Detect environment from the current hostname
$hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';

$isLocal = (
    $hostname === 'localhost' ||
    $hostname === '127.0.0.1' ||
    str_starts_with($hostname, 'localhost:')
);


// Local XAMPP database
if ($isLocal) {

    $host     = "localhost";
    $port     = 3306;
    $dbname   = "ayurveda_db";
    $username = "root";
    $password = "";


// MilesWeb production database
} else {

    $host     = "localhost";
    $port     = 3306;
    $dbname   = "daurp0duction_ayurveda_db";
    $username = "daurp0duction_vedorishi";
    $password = "vedorishi@@123";
}


/*
|--------------------------------------------------------------------------
| Database Connection
|--------------------------------------------------------------------------
*/

try {

    $conn = new mysqli(
        $host,
        $username,
        $password,
        $dbname,
        $port
    );

} catch (mysqli_sql_exception $e) {

    error_log(
        "Database connection failed: " . $e->getMessage()
    );

    die("Database connection failed.");
}


/*
|--------------------------------------------------------------------------
| Connection Error Check
|--------------------------------------------------------------------------
*/

if ($conn->connect_error) {

    error_log(
        "Database connection failed: " . $conn->connect_error
    );

    die("Database connection failed.");
}


/*
|--------------------------------------------------------------------------
| Character Set
|--------------------------------------------------------------------------
*/

$conn->set_charset("utf8mb4");