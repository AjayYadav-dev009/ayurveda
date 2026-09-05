<?php include __DIR__ . '/../../function/category.php'; ?>
<?php require_once __DIR__ . '/../../config/database.php'; ?>
<?php include __DIR__ . '/../../includes/auth.php'; ?>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<?php
$categories = getCategories($conn);
?>

<style>
    table {
        border-collapse: collapse;
        width: 100%;
        font-family: sans-serif;
    }

    th,
    td {
        border: 2px solid #000000;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #dddcdc;
        font-weight: 900;
        color: #000000;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    /* Base (from before) */
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
        transition: background-color 0.2s ease;
    }

    /* Variants */
    .btn-add {
        background-color: #28a745;
    }

    .btn-add:hover {
        background-color: #218838;
    }

    .btn-edit {
        background-color: #ffc107;
        color: #212529;
        margin-bottom: 10px;
    }

    .btn-edit:hover {
        background-color: #e0a800;
    }

    .btn-delete {
        background-color: #dc3545;
    }

    .btn-delete:hover {
        background-color: #bb2d3b;
    }

    /* Focus & disabled (shared) */
    .btn:focus-visible {
        outline: 2px solid currentColor;
        outline-offset: 2px;
    }

    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>

<div>
    <a href="add.php" class="btn btn-add" style="margin-bottom: 10px;">Add Category</a>
</div>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Slug</th>
            <th>Description</th>
            <th>Image</th>
            <th>Status</th>
            <th>Sort Order</th>
            <th>Created At</th>
            <th>Updated At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($categories) === 0) : ?>
            <tr>
                <td colspan="9" style="text-align:center;">No category found.</td>
            </tr>
        <?php else : ?>
            <?php foreach ($categories as $category) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                    <td><?php echo htmlspecialchars($category['slug']); ?></td>
                    <td><?php echo htmlspecialchars($category['description']); ?></td>
                    <td><?php echo htmlspecialchars($category['image']); ?></td>
                    <td><?php echo htmlspecialchars($category['status']); ?></td>
                    <td><?php echo $category['sort_order']; ?></td>
                    <td><?php echo htmlspecialchars($category['created_at']); ?></td>
                    <td><?php echo htmlspecialchars($category['updated_at']); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $category['id']; ?>" class="btn btn-edit">Edit</a>
                        <form method="POST" action="delete.php" style="display:inline;">
                            <input type="hidden" name="id" value="<?= (int)$category['id']; ?>">
                            <button type="submit" class="btn btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif ?>
    </tbody>
</table>