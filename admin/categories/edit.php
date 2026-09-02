<?php include __DIR__ . '/../../function/category.php'; ?>
<?php include __DIR__ . '/../../function/helper.php'; ?>
<?php require_once __DIR__ . '/../../config/database.php'; ?>

<?php
$categories = getCategories($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    try {

        $parent_id = $_POST['parent_id'] ?? null;
        $name = $_POST['name'] ?? null;
        $description = $_POST['description'] ?? null;
        // $image = $_FILES['image'] ?? null;
        $image = null;
        $status = $_POST['status'] ?? null;

        $sort_order = 0;

        $slug = createSlug($name);
        $meta_title = createMetaTitle($name);
        $meta_description = createMetaDescription($description);

        $category_id = addCategory($conn, $parent_id, $name, $slug, $meta_title, $meta_description, $description, $image, $status, $sort_order);
    } catch (Exception $e) {

        echo "Error: " . htmlspecialchars($e->getMessage());
    }
}
?>

<?php foreach ($categories as $category): ?>

    <form action="#" method="post" enctype="multipart/form-data">
        <label for="parent_id">Parent Category:</label>
        <select name="parent_id" id="parent_id">
            <option value="">None</option>
            <option value="<?php echo $category['id']; ?>" <?php if (isset($_POST['parent_id']) && $_POST['parent_id'] == $category['id']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($category['name']); ?>
            </option>
        </select>
        <label for="name">Name:</label>
        <input type="input" name="name">
        <label for="description">Description:</label>
        <textarea name="description" id="description"></textarea>
    </form>

<?php endforeach; ?>