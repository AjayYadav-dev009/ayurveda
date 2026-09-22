<?php

/*
|--------------------------------------------------------------------------
| ONE-TIME ADMIN RECOVERY SCRIPT
|--------------------------------------------------------------------------
|
| Use this if an admin already exists but you don't know the credentials.
|
| IMPORTANT:
| 1. Change the email and password below.
| 2. Open this file once in your browser.
| 3. Login using those credentials.
| 4. DELETE THIS FILE immediately.
|
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/config/database.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection failed.');
}

$conn->set_charset('utf8mb4');


/*
|--------------------------------------------------------------------------
| NEW ADMIN LOGIN CREDENTIALS
|--------------------------------------------------------------------------
*/

$newName     = 'Admin';
$newEmail    = 'admin@123.com';
$newPassword = 'admin@123';


/*
|--------------------------------------------------------------------------
| HASH PASSWORD
|--------------------------------------------------------------------------
*/

$passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

if ($passwordHash === false) {
    die('Password hashing failed.');
}


/*
|--------------------------------------------------------------------------
| CHECK EXISTING ADMIN
|--------------------------------------------------------------------------
*/

$result = $conn->query("
    SELECT id
    FROM admins
    ORDER BY id ASC
    LIMIT 1
");

if (!$result) {
    die('Could not check admins table: ' . $conn->error);
}


/*
|--------------------------------------------------------------------------
| ADMIN EXISTS → RESET FIRST ADMIN
|--------------------------------------------------------------------------
*/

if ($result->num_rows > 0) {

    $admin = $result->fetch_assoc();
    $adminId = (int) $admin['id'];

    $stmt = $conn->prepare("
        UPDATE admins
        SET
            name = ?,
            email = ?,
            password = ?,
            role = 'super_admin',
            status = 'Active'
        WHERE id = ?
    ");

    if (!$stmt) {
        die('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param(
        'sssi',
        $newName,
        $newEmail,
        $passwordHash,
        $adminId
    );

    if (!$stmt->execute()) {
        die('Could not update admin: ' . $stmt->error);
    }

    $stmt->close();

    echo '<h2>Admin credentials reset successfully.</h2>';

}


/*
|--------------------------------------------------------------------------
| NO ADMIN EXISTS → CREATE FIRST ADMIN
|--------------------------------------------------------------------------
*/

else {

    $stmt = $conn->prepare("
        INSERT INTO admins
        (
            name,
            email,
            password,
            role,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?,
            'super_admin',
            'Active'
        )
    ");

    if (!$stmt) {
        die('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param(
        'sss',
        $newName,
        $newEmail,
        $passwordHash
    );

    if (!$stmt->execute()) {
        die('Could not create admin: ' . $stmt->error);
    }

    $stmt->close();

    echo '<h2>First admin created successfully.</h2>';
}


/*
|--------------------------------------------------------------------------
| SHOW LOGIN CREDENTIALS
|--------------------------------------------------------------------------
*/

echo '<hr>';

echo '<p><strong>Email:</strong> ' . htmlspecialchars($newEmail) . '</p>';

echo '<p><strong>Password:</strong> ' . htmlspecialchars($newPassword) . '</p>';

echo '<p style="color:red;">
    DELETE THIS FILE FROM THE SERVER NOW.
</p>';

?>