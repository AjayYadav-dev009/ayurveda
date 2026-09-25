<?php
$pageTitle = 'Add Blog Post';
$activeNav = 'blog';
?>
<?php include __DIR__ . '/../../function/blog.php'; ?>
<?php include __DIR__ . '/../../function/blog-category.php'; ?>
<?php include __DIR__ . '/../../function/helper.php'; ?>
<?php include __DIR__ . '/../../includes/auth.php'; ?>
<?php require_once __DIR__ . '/../../config/database.php'; ?>
<?php include __DIR__ . '/../include/header.php'; ?>

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
    .fadm { max-width: 860px; margin: 0 auto 60px; }
    .fadm * { box-sizing: border-box; }

    .fadm .topbar {
        display: flex; align-items: center; justify-content: space-between;
        gap: 16px; margin-bottom: 22px; flex-wrap: wrap;
    }
    .fadm .topbar h1 { margin: 0; font-size: 1.4rem; font-weight: 800; letter-spacing: -0.01em; color: var(--ink); }
    .fadm .topbar .subtitle { display: block; margin-top: 2px; font-size: 0.85rem; font-weight: 600; color: var(--muted); }

    .fadm .btn {
        display: inline-flex; align-items: center; gap: 7px; padding: 10px 18px; font-size: 0.88rem; font-weight: 700;
        text-decoration: none; border: 1px solid transparent; border-radius: 10px; cursor: pointer;
        transition: background-color 0.15s ease, transform 0.15s ease;
    }
    .fadm .btn svg { width: 15px; height: 15px; }
    .fadm .btn:hover { transform: translateY(-1px); }
    .fadm .btn-secondary { background: #fff; color: var(--ink); border-color: var(--line); }
    .fadm .btn-secondary:hover { background: var(--mist); }
    .fadm .btn-primary { background: var(--leaf); color: #fff; }
    .fadm .btn-primary:hover { background: var(--leaf-dark); }

    .fadm .notice-error {
        display: flex; align-items: flex-start; gap: 10px;
        margin-bottom: 18px; padding: 13px 16px;
        background: #fdeceb; border: 1px solid #f6c9c4; color: #9a2e25;
        border-radius: 12px; font-size: 0.9rem;
    }
    .fadm .notice-error svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }

    .fadm .card {
        background: #fff; border: 1px solid var(--line); border-radius: 16px;
        padding: 22px 24px; margin-bottom: 18px;
        box-shadow: 0 1px 2px rgba(23, 72, 61, 0.04);
    }

    .fadm .card h2 {
        display: flex; align-items: center; gap: 9px;
        font-size: 1rem; font-weight: 800; margin: 0 0 16px;
        padding-bottom: 12px; border-bottom: 1px solid var(--line); color: var(--ink);
    }
    .fadm .card h2 svg { width: 18px; height: 18px; color: var(--leaf); }

    .fadm .row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .fadm .field { margin-top: 16px; }
    .fadm .field:first-child { margin-top: 0; }
    .fadm .row .field { margin-top: 0; }

    .fadm label {
        display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: 700; color: var(--ink);
    }
    .fadm label small { font-weight: 500; color: var(--muted); }

    .fadm select,
    .fadm input[type="text"],
    .fadm input[type="datetime-local"],
    .fadm input[type="file"],
    .fadm textarea {
        width: 100%; padding: 10px 13px; font-size: 0.92rem; font-family: inherit;
        border: 1px solid var(--line); border-radius: 10px; background: #fff; color: var(--ink);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .fadm select:focus,
    .fadm input[type="text"]:focus,
    .fadm input[type="datetime-local"]:focus,
    .fadm textarea:focus {
        outline: none; border-color: var(--leaf); box-shadow: 0 0 0 3px var(--leaf-tint);
    }

    .fadm input[type="file"] {
        padding: 8px 10px; background: var(--mist); cursor: pointer;
    }

    .fadm textarea { min-height: 80px; resize: vertical; }
    .fadm .char-count { display: block; margin-top: 4px; font-size: 0.76rem; color: var(--muted); text-align: right; }

    .fadm .ck-editor__editable { min-height: 260px; border-radius: 0 0 10px 10px !important; }
    .fadm .ck.ck-toolbar { border-radius: 10px 10px 0 0 !important; }

    .fadm .form-actions {
        display: flex; align-items: center; justify-content: flex-end; gap: 10px; margin-top: 6px;
    }

    @media (max-width: 640px) {
        .fadm .row { grid-template-columns: 1fr; }
        .fadm .card { padding: 18px; }
    }
</style>

<div class="fadm">
    <div class="topbar">
        <div>
            <h1>Add Blog Post</h1>
            <span class="subtitle">Write a new wellness story or update</span>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
            Back to posts
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="notice-error">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5"/><path d="M12 16h.01"/></svg>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <form action="#" method="post" enctype="multipart/form-data">

        <div class="card">
            <h2>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Z"/><path d="M14 3v5h5"/><path d="M9 13h6M9 17h6"/></svg>
                Content
            </h2>

            <div class="field">
                <label for="title">Title</label>
                <input type="text" name="title" id="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
            </div>

            <div class="field">
                <label for="excerpt">Excerpt</label>
                <textarea name="excerpt" id="excerpt"><?php echo isset($_POST['excerpt']) ? htmlspecialchars($_POST['excerpt']) : ''; ?></textarea>
            </div>

            <div class="field">
                <label for="content">Content</label>
                <textarea name="content" id="content"><?php echo isset($_POST['content']) ? $_POST['content'] : ''; ?></textarea>
            </div>
        </div>

        <div class="card">
            <h2>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="1.6"/><path d="m21 15-4.5-4.5L6 21"/></svg>
                Media
            </h2>

            <div class="row">
                <div class="field">
                    <label for="hero_image">Hero Image <small>(wide banner, e.g. 1600&times;700)</small></label>
                    <input type="file" name="hero_image" id="hero_image" accept="image/*">
                </div>
                <div class="field">
                    <label for="image">Featured Image <small>(2:1, e.g. 1200&times;600)</small></label>
                    <input type="file" name="image" id="image" accept="image/*">
                </div>
            </div>
        </div>

        <div class="card">
            <h2>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 9 6 4-6 4"/><path d="M13 9h6"/><path d="M13 13h6"/></svg>
                Publishing
            </h2>

            <div class="row">
                <div class="field">
                    <label for="category_id">Category</label>
                    <select name="category_id" id="category_id">
                        <option value="">None</option>
                        <?php foreach ($blogCategories as $blogCategory): ?>
                            <option value="<?php echo $blogCategory['id']; ?>" <?php if (isset($_POST['category_id']) && $_POST['category_id'] == $blogCategory['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($blogCategory['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="status">Status</label>
                    <select name="status" id="status">
                        <option value="Draft" <?php if (!isset($_POST['status']) || $_POST['status'] == 'Draft') echo 'selected'; ?>>Draft</option>
                        <option value="Active" <?php if (isset($_POST['status']) && $_POST['status'] == 'Active') echo 'selected'; ?>>Active</option>
                        <option value="Inactive" <?php if (isset($_POST['status']) && $_POST['status'] == 'Inactive') echo 'selected'; ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="published_at">Published At <small>(leave blank to auto-set when first made Active)</small></label>
                <input type="datetime-local" name="published_at" id="published_at" value="<?php echo isset($_POST['published_at']) ? htmlspecialchars($_POST['published_at']) : ''; ?>">
            </div>
        </div>

        <div class="card">
            <h2>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                SEO
            </h2>

            <div class="field">
                <label for="meta_title">Meta Title <small>(defaults to title if left blank)</small></label>
                <input type="text" name="meta_title" id="meta_title" value="<?php echo isset($_POST['meta_title']) ? htmlspecialchars($_POST['meta_title']) : ''; ?>">
            </div>

            <div class="field">
                <label for="meta_description">Meta Description <small>(optional, max 200 chars)</small></label>
                <textarea name="meta_description" id="meta_description" maxlength="200"><?php echo isset($_POST['meta_description']) ? htmlspecialchars($_POST['meta_description']) : ''; ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <a href="index.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Add Blog Post
            </button>
        </div>
    </form>
</div>

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

<?php include __DIR__ . '/../include/footer.php'; ?>