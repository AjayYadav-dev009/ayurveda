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
    $sql = "SELECT id, name, email, password, role, status, last_login_at FROM admins WHERE email = ? LIMIT 1";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    $stmt->bind_param('s', $email);

    $stmt->execute();

    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

/**
 * @param mysqli $conn
 * @param int $id
 * @throws Exception
 */
function getAdminById($conn, $id)
{
    $sql = "SELECT id, name, email, role, status, last_login_at, created_at, updated_at FROM admins WHERE id = ? LIMIT 1";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    $stmt->bind_param('i', $id);

    $stmt->execute();

    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

/**
 * Updates an admin's core fields. Pass 'password' as null to leave the
 * existing password untouched.
 *
 * @param mysqli $conn
 * @param int $id
 * @param array $data ['name' => string, 'email' => string, 'role' => string, 'status' => string, 'password' => ?string]
 * @throws InvalidArgumentException
 * @throws Exception
 */
function updateAdmin($conn, $id, array $data)
{
    $existing = getAdminByEmail($conn, $data['email']);
    if ($existing && (int) $existing['id'] !== (int) $id) {
        throw new InvalidArgumentException('An admin with this email already exists.');
    }

    if (!empty($data['password'])) {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "UPDATE admins SET name = ?, email = ?, password = ?, role = ?, status = ? WHERE id = ?"
        );

        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }

        $stmt->bind_param(
            'sssssi',
            $data['name'],
            $data['email'],
            $hashedPassword,
            $data['role'],
            $data['status'],
            $id
        );
    } else {
        $stmt = $conn->prepare(
            "UPDATE admins SET name = ?, email = ?, role = ?, status = ? WHERE id = ?"
        );

        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }

        $stmt->bind_param(
            'ssssi',
            $data['name'],
            $data['email'],
            $data['role'],
            $data['status'],
            $id
        );
    }

    $stmt->execute();
}

/**
 * @param mysqli $conn
 * @param int $id
 * @throws Exception
 */
function deleteAdmin($conn, $id)
{
    $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");

    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    $stmt->bind_param('i', $id);

    $stmt->execute();
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
