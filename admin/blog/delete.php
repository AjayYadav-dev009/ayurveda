<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../function/blog-category.php';
require_once __DIR__ . '/../../includes/auth.php';

function clean($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$id || !is_numeric($id)) {
    header('Location: index.php');
    exit;
}
$id = (int) $id;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        deleteBlogCategory($conn, $id);
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

$category = mysqli_fetch_assoc(getBlogCategoryById($conn, $id));
if (!$category) {
    header('Location: index.php');
    exit;
}

$postCount = countPostsInBlogCategory($conn, $id);
?>

<style>
    body {
        font-family: sans-serif;
    }

    .notice-error {
        max-width: 520px;
        padding: 10px 14px;
        background: #f8d7da;
        border: 1px solid #f5c2c7;
        color: #842029;
        border-radius: 4px;
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

    .btn-danger {
        background-color: #dc3545;
    }

    .btn-secondary {
        background-color: #6c757d;
    }
</style>

<h1>Delete blog category: <?= clean($category['name']) ?></h1>

<?php if (!empty($errors)): ?>
    <div class="notice-error">
        <?php foreach ($errors as $error): ?>
            <p><?= clean($error) ?></p>
        <?php endforeach; ?>
    </div>
    <p><a href="index.php">&larr; Back to blog categories</a></p>
<?php else: ?>
    <?php if ($postCount > 0): ?>
        <p><?= $postCount ?> blog post<?= $postCount === 1 ? '' : 's' ?> currently use this category and will become uncategorized (not deleted) if you continue.</p>
    <?php else: ?>
        <p>No blog posts use this category — safe to delete.</p>
    <?php endif; ?>
    <form method="post" action="delete.php?id=<?= $id ?>">
        <input type="hidden" name="id" value="<?= $id ?>">
        <button type="submit" class="btn btn-danger">Delete category</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
<?php endif; ?>