<?php

    /**
     * Database credentials.
     *
     * Production must NOT use the root account or an empty password.
     * Set these via environment variables (e.g. in your host's control
     * panel, or in the Apache/vhost config with SetEnv).
     *
     * In production (APP_ENV=production), every one of these is
     * required -- if any is missing, the connection fails fast with a
     * generic message rather than silently falling back to the
     * root/localhost/empty-password development defaults below.
     * Outside production, missing variables fall back to the original
     * local/XAMPP defaults, so nothing changes for the existing
     * development setup.
     *
     * This checks getenv('APP_ENV') directly (not the APP_ENV constant
     * from config.php) because database.php can be loaded on its own,
     * without config.php, from some entry points.
     */
    $isProduction = (getenv('APP_ENV') ?: 'development') === 'production';

    $envHost     = getenv('DB_HOST');
    $envPort     = getenv('DB_PORT');
    $envName     = getenv('DB_NAME');
    $envUser     = getenv('DB_USER');
    $envPassword = getenv('DB_PASSWORD');

    if ($isProduction) {
        if ($envHost === false || $envName === false || $envUser === false || $envPassword === false) {
            error_log('Production database credentials are not fully set via environment variables (DB_HOST/DB_NAME/DB_USER/DB_PASSWORD).');
            die("Database connection failed.");
        }
        $host     = $envHost;
        $port     = $envPort !== false ? (int) $envPort : 3306;
        $dbname   = $envName;
        $username = $envUser;
        $password = $envPassword;
    } else {
        $host     = $envHost !== false ? $envHost : "localhost";
        $port     = $envPort !== false ? (int) $envPort : 3306;
        $dbname   = $envName !== false ? $envName : "ayurveda_db";
        $username = $envUser !== false ? $envUser : "root";
        $password = $envPassword !== false ? $envPassword : "";
    }

    /**
     * On PHP 8.1+, mysqli's default error-reporting mode throws a
     * mysqli_sql_exception on a failed connection instead of just setting
     * $conn->connect_error. Left uncaught, that exception (with
     * display_errors on) would print the connection details in a stack
     * trace to the customer. The try/catch below preserves the existing
     * "die with a generic message" behavior in that case too, and logs
     * the real error server-side either way.
     */
    try {
        $conn = new mysqli(
            $host,
            $username,
            $password,
            $dbname,
            (int) $port
        );
    } catch (mysqli_sql_exception $e) {
        error_log("Database connection failed: " . $e->getMessage());
        die("Database connection failed.");
    }

    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        die("Database connection failed.");
    }

    $conn->set_charset("utf8mb4");