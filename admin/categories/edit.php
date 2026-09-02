<?php include __DIR__ . '/../../function/category.php'; ?>
<?php include __DIR__ . '/../../function/helper.php'; ?>
<?php require_once __DIR__ . '/../../config/database.php'; ?>

<?php

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: index.php');
    exit;
}

$category = getCategoryById($conn, $id);

$categoryResult = getCategoryById($conn, $id);
$category = mysqli_fetch_assoc($categoryResult);

if (!$category) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $parent_id = $_POST['parent_id'] ?? null;
        $name = $_POST['name'] ?? null;
        $description = $_POST['description'] ?? null;
        $image = $_FILES['image'] ?? null;
        $status = $_POST['status'] ?? null;
        $sort_order = $_POST['sort_order'] ?? null;

        $slug = createSlug($name);
        $meta_title = createMetaTitle($name);
        $meta_description = createMetaDescription($description);

        updateCategory($conn, $id, $parent_id, $name, $slug, $meta_title, $meta_description, $description, $image, $status, $sort_order);

        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

?>

<style>
    form {
        max-width: 520px;
        margin: 0 auto;
        font-family: sans-serif;
        font-size: 0.95rem;
    }

    form label {
        display: block;
        margin-top: 14px;
        margin-bottom: 5px;
        font-weight: 600;
        color: #333;
    }

    form select,
    form input[type="text"],
    form textarea {
        width: 100%;
        padding: 9px 12px;
        font-size: 0.95rem;
        font-family: inherit;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    form select:focus,
    form input[type="text"]:focus,
    form textarea:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
    }

    form textarea {
        min-height: 80px;
        resize: vertical;
    }

    form input[type="submit"] {
        margin-top: 20px;
        padding: 10px 24px;
        font-size: 1rem;
        font-weight: 600;
        color: #fff;
        background-color: #28a745;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    form input[type="submit"]:hover {
        background-color: #218838;
    }

    form input[type="submit"]:focus-visible {
        outline: 2px solid #28a745;
        outline-offset: 2px;
    }
</style>

<form action="#" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $category['id']; ?>">
    <label for="parent_id">Parent Category:</label>
    <select name="parent_id" id="parent_id">
        <option value="">None</option>
        <?php foreach (getCategories($conn) as $allCategory): ?>
            <option value="<?= $allCategory['id']; ?>" <?= $allCategory['id'] === $allCategory['parent_id'] ? 'selected' : ''; ?>><?= $allCategory['name']; ?></option>
        <?php endforeach; ?>
    </select>
    <label for="name">Name:</label>
    <input type="text" name="name" id="name" value="<?= $category['name']; ?>">
    <label for="description">Description:</label>
    <textarea name="description" id="description"><?= $category['description']; ?></textarea>
    <label for="image">Image:</label>
    <input type="file" name="image" id="image">
    <label for="status">Status:</label>
    <select name="status" id="status">
        <option value="Active" <?= $category['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
        <option value="Inactive" <?= $category['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
    </select>
    <label for="sort_order">Sort Order:</label>
    <input type="number" name="sort_order" id="sort_order" value="<?= $category['sort_order']; ?>">
    <input type="submit" value="Save">
</form>