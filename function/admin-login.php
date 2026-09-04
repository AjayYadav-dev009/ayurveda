<?php require_once __DIR__ . '/../config/database.php'; ?>
<?php

/**
 * @param mysqli $conn
 * @param string $email
 * @throws Exception
 * @throws InvalidArgumentException
 */

function getAdminByEmail($conn, $email)
{
    $sql = "SELECT id, name, password, role, status, last_login_at FROM admins WHERE email = ? LIMIT 1";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    $stmt->bind_param('s', $email);

    $stmt->execute();

    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

function isAdminLogin()
{
    return isset($_SESSION['admin_id']);
}

function logoutAdmin()
{
    unset(
        $_SESSION['admin_id'],
        $_SESSION['admin_name'],
        $_SESSION['admin_email']
    );
}
