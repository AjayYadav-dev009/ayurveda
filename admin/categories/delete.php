<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../function/category.php';
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
$impact = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // The hidden field below is what was missing: without it, $force
    // was always false and a category with any impact could never
    // actually be deleted, no matter how many times you clicked.
    $force = !empty($_POST['confirm']);

    try {
        deleteCategory($conn, $id, $force);
        header('Location: index.php');
        exit;
    } catch (CategoryDeletionImpactException $e) {
        // Still blocked -- show the same breakdown again with a button
        // to confirm, instead of dead-ending on plain text.
        $impact = [
            'subcategories' => $e->subcategories,
            'productsToUncategorize' => $e->productsToUncategorize,
            'productsToUnlink' => $e->productsToUnlink,
        ];
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
} else {
    // GET must never delete anything -- just preview the impact so the
    // page has something to show before the user commits to anything.
    try {
        $preview = previewCategoryDeletion($conn, $id);
        if ($preview['hasImpact']) {
            $impact = $preview;
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

$category = mysqli_fetch_assoc(getCategoryById($conn, $id));
if (!$category) {
    header('Location: index.php');
    exit;
}
?>

<h1>Delete category: <?= clean($category['name']) ?></h1>

<?php if (!empty($errors)): ?>
    <div class="notice notice-error">
        <?php foreach ($errors as $error): ?>
            <p><?= clean($error) ?></p>
        <?php endforeach; ?>
    </div>
    <p><a href="index.php">&larr; Back to categories</a></p>

<?php elseif ($impact): ?>
    <div class="impact-box">
        <p>Deleting "<strong><?= clean($category['name']) ?></strong>" will also affect:</p>
        <ul>
            <?php foreach ($impact['subcategories'] as $sub): ?>
                <li><span class="tag tag-delete">delete</span> Subcategory "<?= clean($sub['name']) ?>"</li>
            <?php endforeach; ?>
            <?php foreach ($impact['productsToUncategorize'] as $p): ?>
                <li><span class="tag tag-move">move</span> Product "<?= clean($p['title']) ?>" &rarr; Uncategorised</li>
            <?php endforeach; ?>
            <?php foreach ($impact['productsToUnlink'] as $p): ?>
                <li><span class="tag tag-unlink">unlink</span> Product "<?= clean($p['title']) ?>" (kept in its other categories)</li>
            <?php endforeach; ?>
        </ul>
    </div>

    <form method="post" action="delete.php?id=<?= $id ?>">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="confirm" value="1">
        <button type="submit" class="btn btn-danger">Yes, delete it anyway</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>

<?php else: ?>
    <p>Deleting "<strong><?= clean($category['name']) ?></strong>" is safe: it has no subcategories or linked products.</p>
    <form method="post" action="delete.php?id=<?= $id ?>">
        <input type="hidden" name="id" value="<?= $id ?>">
        <button type="submit" class="btn btn-danger">Delete category</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
<?php endif; ?>
