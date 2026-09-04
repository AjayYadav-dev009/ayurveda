<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../function/admin-login.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    die('Invalid user id.');
}

$admin = getAdminById($conn, $id);
if ($admin === null) {
    die('User not found.');
}

$statusClass = $admin['status'] === 'Active' ? 'badge-active' : 'badge-inactive';
?>

<style>
    dl {
        font-family: sans-serif;
    }

    dt {
        font-weight: 700;
        margin-top: 10px;
    }

    dd {
        margin-left: 0;
        margin-bottom: 5px;
    }

    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
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

    .btn {
        display: inline-block;
        padding: 10px 20px;
        font-size: 1rem;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-edit {
        background-color: #ffc107;
        color: #212529;
    }

    .btn-delete {
        background-color: #dc3545;
    }

    .btn-back {
        background-color: #6c757d;
    }
</style>

<div style="margin-bottom: 15px;">
    <a href="index.php" class="btn btn-back">&laquo; Back to users</a>
    <a href="edit.php?id=<?php echo (int) $admin['id']; ?>" class="btn btn-edit">Edit</a>
    <form method="POST" action="delete.php" style="display:inline;" onsubmit="return confirm('Delete this user? This cannot be undone.');">
        <input type="hidden" name="id" value="<?php echo (int) $admin['id']; ?>">
        <button type="submit" class="btn btn-delete">Delete</button>
    </form>
</div>

<h2><?php echo htmlspecialchars($admin['name']); ?></h2>
<p>
    <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($admin['status']); ?></span>
</p>

<dl>
    <dt>Email</dt>
    <dd><?php echo htmlspecialchars($admin['email']); ?></dd>

    <dt>Role</dt>
    <dd><?php echo htmlspecialchars($admin['role']); ?></dd>

    <dt>Last login</dt>
    <dd><?php echo $admin['last_login_at'] !== null ? htmlspecialchars($admin['last_login_at']) : '&mdash;'; ?></dd>

    <dt>Created at</dt>
    <dd><?php echo htmlspecialchars($admin['created_at']); ?></dd>

    <dt>Updated at</dt>
    <dd><?php echo htmlspecialchars($admin['updated_at']); ?></dd>
</dl>