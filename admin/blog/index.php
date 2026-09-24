<?php include __DIR__ . '/../../function/blog.php'; ?>
<?php require_once __DIR__ . '/../../config/database.php'; ?>
<?php include __DIR__ . '/../../includes/auth.php'; ?>

<?php
$posts = getBlogPosts($conn);
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

    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .status-Active {
        background-color: #d4edda;
        color: #155724;
    }

    .status-Draft {
        background-color: #fff3cd;
        color: #856404;
    }

    .status-Inactive {
        background-color: #f8d7da;
        color: #721c24;
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
        transition: background-color 0.2s ease;
    }

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
    <a href="add.php" class="btn btn-add" style="margin-bottom: 10px;">Add Blog Post</a>
</div>

<table>
    <thead>
        <tr>
            <th>Title</th>
            <th>Slug</th>
            <th>Category</th>
            <th>Image</th>
            <th>Status</th>
            <th>Published At</th>
            <th>Created At</th>
            <th>Updated At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($posts) === 0) : ?>
            <tr>
                <td colspan="9" style="text-align:center;">No blog post found.</td>
            </tr>
        <?php else : ?>
            <?php foreach ($posts as $post) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($post['title']); ?></td>
                    <td><?php echo htmlspecialchars($post['slug']); ?></td>
                    <td><?php echo htmlspecialchars($post['category_name'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($post['image'] ?? ''); ?></td>
                    <td><span class="status-badge status-<?php echo htmlspecialchars($post['status']); ?>"><?php echo htmlspecialchars($post['status']); ?></span></td>
                    <td><?php echo htmlspecialchars($post['published_at'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($post['created_at']); ?></td>
                    <td><?php echo htmlspecialchars($post['updated_at']); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-edit">Edit</a>
                        <a href="delete.php?id=<?php echo $post['id']; ?>" class="btn btn-delete">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif ?>
    </tbody>
</table>