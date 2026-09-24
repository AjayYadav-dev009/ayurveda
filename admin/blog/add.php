<?php include __DIR__ . '/../../function/blog.php'; ?>
<?php include __DIR__ . '/../../function/blog-category.php'; ?>
<?php include __DIR__ . '/../../function/helper.php'; ?>
<?php include __DIR__ . '/../../includes/auth.php'; ?>
<?php require_once __DIR__ . '/../../config/database.php'; ?>

<?php
$blogCategories = getBlogCategories($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {

        $title = $_POST['title'] ?? null;
        $excerpt = $_POST['excerpt'] ?? null;
        $content = $_POST['content'] ?? null;
        $status = $_POST['status'] ?? null;
        $published_at = $_POST['published_at'] ?? null;
        $meta_title = $_POST['meta_title'] ?? null;
        $meta_description = $_POST['meta_description'] ?? null;
        $category_id = $_POST['category_id'] ?? null;

        // uploadBlogImage() validates the upload and returns a plain
        // filename string (or null if no file was chosen) — safe to pass
        // straight into addBlogPost().
        $image = uploadBlogImage($_FILES['image'] ?? null);
        // Second image: the wide banner shown at the top of the post page.
        $heroImage = uploadBlogImage($_FILES['hero_image'] ?? null);

        $blog_id = addBlogPost($conn, $title, $excerpt, $content, $image, $status, $published_at, $meta_title, $meta_description, $category_id, $heroImage);

        header("Location: index.php");
        exit;
    } catch (Exception $e) {

        $error = $e->getMessage();

        // Don't leave freshly uploaded files behind when the post wasn't saved.
        foreach ([$image ?? null, $heroImage ?? null] as $orphan) {
            if ($orphan) {
                @unlink(rtrim(BLOG_IMAGE_UPLOAD_DIR, '/') . '/' . $orphan);
            }
        }
    }
}
?>

<style>
    form {
        max-width: 720px;
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
    form input[type="datetime-local"],
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
    form input[type="datetime-local"]:focus,
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

    .notice-error {
        max-width: 720px;
        margin: 0 auto 14px;
        padding: 10px 14px;
        background: #f8d7da;
        border: 1px solid #f5c2c7;
        color: #842029;
        border-radius: 4px;
        font-family: sans-serif;
    }

    .ck-editor__editable {
        min-height: 250px;
    }
</style>

<?php if (!empty($error)): ?>
    <div class="notice-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form action="#" method="post" enctype="multipart/form-data">
    <label for="title">Title:</label>
    <input type="text" name="title" id="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>

    <label for="excerpt">Excerpt:</label>
    <textarea name="excerpt" id="excerpt"><?php echo isset($_POST['excerpt']) ? htmlspecialchars($_POST['excerpt']) : ''; ?></textarea>

    <label for="content">Content:</label>
    <textarea name="content" id="content"><?php echo isset($_POST['content']) ? $_POST['content'] : ''; ?></textarea>

    <label for="hero_image">Hero Image: <small>(banner at the top of the post, wide, e.g. 1600&times;700)</small></label>
    <input type="file" name="hero_image" id="hero_image" accept="image/*">

    <label for="image">Featured Image: <small>(shown above the article text, 2:1, e.g. 1200&times;600)</small></label>
    <input type="file" name="image" id="image" accept="image/*">

    <label for="category_id">Category:</label>
    <select name="category_id" id="category_id">
        <option value="">None</option>
        <?php foreach ($blogCategories as $blogCategory): ?>
            <option value="<?php echo $blogCategory['id']; ?>" <?php if (isset($_POST['category_id']) && $_POST['category_id'] == $blogCategory['id']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($blogCategory['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="status">Status:</label>
    <select name="status" id="status">
        <option value="Draft" <?php if (!isset($_POST['status']) || $_POST['status'] == 'Draft') echo 'selected'; ?>>Draft</option>
        <option value="Active" <?php if (isset($_POST['status']) && $_POST['status'] == 'Active') echo 'selected'; ?>>Active</option>
        <option value="Inactive" <?php if (isset($_POST['status']) && $_POST['status'] == 'Inactive') echo 'selected'; ?>>Inactive</option>
    </select>

    <label for="published_at">Published At: <small>(leave blank to auto-set when first made Active)</small></label>
    <input type="datetime-local" name="published_at" id="published_at" value="<?php echo isset($_POST['published_at']) ? htmlspecialchars($_POST['published_at']) : ''; ?>">

    <label for="meta_title">Meta Title: <small>(defaults to title if left blank)</small></label>
    <input type="text" name="meta_title" id="meta_title" value="<?php echo isset($_POST['meta_title']) ? htmlspecialchars($_POST['meta_title']) : ''; ?>">

    <label for="meta_description">Meta Description: <small>(optional, max 200 chars)</small></label>
    <textarea name="meta_description" id="meta_description" maxlength="200"><?php echo isset($_POST['meta_description']) ? htmlspecialchars($_POST['meta_description']) : ''; ?></textarea>

    <input type="submit" value="Add Blog Post">
</form>

<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    let blogContentEditor;
    ClassicEditor
        .create(document.querySelector('#content'))
        .then(editor => {
            blogContentEditor = editor;
        })
        .catch(error => {
            console.error(error);
        });

    document.querySelector('form').addEventListener('submit', function () {
        if (blogContentEditor) {
            blogContentEditor.updateSourceElement();
        }
    });
</script>