<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../function/admin-login.php';

/**
 * Fetch all admins, most recently created first.
 *
 * @param mysqli $conn
 * @return array<int, array>
 * @throws Exception
 */
function getAllAdmins($conn)
{
    $sql = "SELECT id, name, email, role, status, last_login_at, created_at
            FROM admins
            ORDER BY created_at DESC";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Error fetching admins: " . mysqli_error($conn));
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

$admins = [];
$loadError = '';

try {
    $admins = getAllAdmins($conn);
} catch (Exception $e) {
    $loadError = 'Something went wrong while loading the users.';
}

$deleted = isset($_GET['deleted']) && $_GET['deleted'] === '1';
$created = isset($_GET['created']) && $_GET['created'] === '1';
$updated = isset($_GET['updated']) && $_GET['updated'] === '1';
?>

<style>
    .users-wrap {
        font-family: sans-serif;
    }

    .users-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 15px;
    }

    .users-header h2 {
        margin: 0;
    }

    .btn {
        display: inline-block;
        padding: 8px 16px;
        font-size: 0.9rem;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-add {
        background-color: #218838;
    }

    .btn-view {
        background-color: #6c757d;
    }

    .btn-edit {
        background-color: #ffc107;
        color: #212529;
    }

    .btn-delete {
        background-color: #dc3545;
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 0.8rem;
        margin-right: 4px;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        padding: 10px 15px;
        border-radius: 4px;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px 15px;
        border-radius: 4px;
        margin-bottom: 15px;
        font-weight: 600;
    }

    table.users-table {
        width: 100%;
        border-collapse: collapse;
    }

    .users-table th,
    .users-table td {
        text-align: left;
        padding: 10px 12px;
        border-bottom: 1px solid #e2e2e2;
        vertical-align: middle;
    }

    .users-table th {
        background-color: #f8f9fa;
        font-weight: 700;
    }

    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-active {
        background-color: #d4edda;
        color: #155724;
    }

    .badge-inactive {
        background-color: #f1f1f1;
        color: #555555;
    }

    .empty-state {
        padding: 30px;
        text-align: center;
        color: #777;
    }

    .actions-cell form {
        display: inline;
    }
</style>

<div class="users-wrap">
    <div class="users-header">
        <h2>Admin Users</h2>
        <a href="add.php" class="btn btn-add">+ Add New Admin</a>
    </div>

    <?php if ($deleted): ?>
        <p class="alert-success">User deleted successfully.</p>
    <?php endif; ?>

    <?php if ($created): ?>
        <p class="alert-success">Admin user created successfully.</p>
    <?php endif; ?>

    <?php if ($updated): ?>
        <p class="alert-success">User updated successfully.</p>
    <?php endif; ?>

    <?php if ($loadError !== ''): ?>
        <p class="alert-error"><?= htmlspecialchars($loadError) ?></p>
    <?php endif; ?>

    <?php if (empty($admins) && $loadError === ''): ?>
        <p class="empty-state">No admin users found. Click "Add New Admin" to create one.</p>
    <?php elseif (!empty($admins)): ?>
        <table class="users-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td><?= htmlspecialchars($admin['name']) ?></td>
                        <td><?= htmlspecialchars($admin['email']) ?></td>
                        <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $admin['role']))) ?></td>
                        <td>
                            <span class="badge <?= $admin['status'] === 'Active' ? 'badge-active' : 'badge-inactive' ?>">
                                <?= htmlspecialchars($admin['status']) ?>
                            </span>
                        </td>
                        <td><?= $admin['last_login_at'] !== null ? htmlspecialchars($admin['last_login_at']) : '&mdash;' ?></td>
                        <td><?= htmlspecialchars($admin['created_at']) ?></td>
                        <td class="actions-cell">
                            <a href="view-one.php?id=<?= (int) $admin['id'] ?>" class="btn btn-view btn-sm">View</a>
                            <a href="edit.php?id=<?= (int) $admin['id'] ?>" class="btn btn-edit btn-sm">Edit</a>
                            <?php if ((int) $admin['id'] !== (int) ($_SESSION['admin_id'] ?? 0)): ?>
                                <form method="POST" action="delete.php" onsubmit="return confirm('Delete this user? This cannot be undone.');">
                                    <input type="hidden" name="id" value="<?= (int) $admin['id'] ?>">
                                    <button type="submit" class="btn btn-delete btn-sm">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>