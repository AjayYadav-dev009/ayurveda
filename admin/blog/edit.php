<?php
$pageTitle = 'Edit Blog Post';
$activeNav = 'blog';
?>
<?php include __DIR__ . '/../../function/blog.php'; ?>
<?php include __DIR__ . '/../../function/blog-category.php'; ?>
<?php include __DIR__ . '/../../function/helper.php'; ?>
<?php include __DIR__ . '/../../includes/auth.php'; ?>
<?php require_once __DIR__ . '/../../config/database.php'; ?>

<?php

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: index.php');
    exit;
}

$blogResult = getBlogPostById($conn, $id);
$blog = mysqli_fetch_assoc($blogResult);

if (!$blog) {
    header('Location: index.php');
    exit;
}

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
        // filename string (or null if no new file was chosen). Never pass
        // $_FILES['image'] straight into updateBlogPost() — it's an array,
        // not the string the DB column/bind_param expects.
        $newImage = uploadBlogImage($_FILES['image'] ?? null);

        // Keep the existing image on disk/DB unless the admin uploaded a
        // replacement.
        $image = $newImage ?? $blog['image'];

        // Same rule for the hero banner: keep the current one unless replaced.
        $newHeroImage = uploadBlogImage($_FILES['hero_image'] ?? null);
        $heroImage = $newHeroImage ?? ($blog['hero_image'] ?? null);

        updateBlogPost($conn, $id, $title, $excerpt, $content, $image, $status, $published_at, $meta_title, $meta_description, $category_id, $heroImage);

        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();

        // The post wasn't saved, so remove any files uploaded during this attempt.
        foreach ([$newImage ?? null, $newHeroImage ?? null] as $orphan) {
            if ($orphan) {
                @unlink(rtrim(BLOG_IMAGE_UPLOAD_DIR, '/') . '/' . $orphan);
            }
        }
        // Re-fetch so the form below still shows the saved values on error,
        // not the stale pre-edit row.
        $blog['title'] = $title;
        $blog['excerpt'] = $excerpt;
        $blog['content'] = $content;
        $blog['status'] = $status;
        $blog['meta_title'] = $meta_title;
        $blog['meta_description'] = $meta_description;
        $blog['category_id'] = $category_id;
    }
}

$publishedAtValue = '';
if (!empty($blog['published_at'])) {
    $publishedAtValue = str_replace(' ', 'T', substr($blog['published_at'], 0, 16));
}

?>
<?php include __DIR__ . '/../include/header.php'; ?>

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

    .fadm .status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 11px; border-radius: 99px; font-size: 0.78rem; font-weight: 700;
    }
    .fadm .status-pill::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .fadm .status-Active { background: var(--leaf-tint); color: var(--leaf-dark); }
    .fadm .status-Draft { background: #fdf1de; color: #93650f; }
    .fadm .status-Inactive { background: #fdeceb; color: #c23b32; }

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

    .fadm .current-image-wrap {
        display: flex; align-items: center; gap: 10px; margin-top: 10px;
    }

    .fadm .current-image {
        width: 96px; height: 72px; object-fit: cover;
        border: 1px solid var(--line); border-radius: 10px;
        box-shadow: 0 1px 2px rgba(23, 72, 61, 0.05);
    }

    .fadm .current-image-label {
        font-size: 0.78rem; color: var(--muted); font-weight: 600;
    }

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
            <h1>Edit Blog Post</h1>
            <span class="subtitle">
                Post #<?= (int) $blog['id'] ?>
                <span class="status-pill status-<?= htmlspecialchars($blog['status']) ?>" style="margin-left:8px;"><?= htmlspecialchars($blog['status']) ?></span>
            </span>
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
        <input type="hidden" name="id" value="<?= htmlspecialchars($blog['id']); ?>">

        <div class="card">
            <h2>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Z"/><path d="M14 3v5h5"/><path d="M9 13h6M9 17h6"/></svg>
                Content
            </h2>

            <div class="field">
                <label for="title">Title</label>
                <input type="text" name="title" id="title" value="<?= htmlspecialchars($blog['title']); ?>" required>
            </div>

            <div class="field">
                <label for="excerpt">Excerpt</label>
                <textarea name="excerpt" id="excerpt"><?= htmlspecialchars($blog['excerpt'] ?? ''); ?></textarea>
            </div>

            <div class="field">
                <label for="content">Content</label>
                <textarea name="content" id="content"><?= $blog['content'] ?? ''; ?></textarea>
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
                    <?php if (!empty($blog['hero_image'])): ?>
                        <div class="current-image-wrap">
                            <img src="<?= htmlspecialchars(getBlogImageUrl($blog['hero_image'])); ?>" alt="Current hero image" class="current-image">
                            <span class="current-image-label">Current image<br>Upload a new file to replace it</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="field">
                    <label for="image">Featured Image <small>(2:1, e.g. 1200&times;600)</small></label>
                    <input type="file" name="image" id="image" accept="image/*">
                    <?php if (!empty($blog['image'])): ?>
                        <div class="current-image-wrap">
                            <img src="<?= htmlspecialchars(getBlogImageUrl($blog['image'])); ?>" alt="Current featured image" class="current-image">
                            <span class="current-image-label">Current image<br>Upload a new file to replace it</span>
                        </div>
                    <?php endif; ?>
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
                            <option value="<?= $blogCategory['id']; ?>" <?= (string) $blog['category_id'] === (string) $blogCategory['id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($blogCategory['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="status">Status</label>
                    <select name="status" id="status">
                        <option value="Draft" <?= $blog['status'] === 'Draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="Active" <?= $blog['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?= $blog['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="published_at">Published At <small>(leave blank to auto-set when first made Active)</small></label>
                <input type="datetime-local" name="published_at" id="published_at" value="<?= htmlspecialchars($publishedAtValue); ?>">
            </div>
        </div>

        <div class="card">
            <h2>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                SEO
            </h2>

            <div class="field">
                <label for="meta_title">Meta Title <small>(defaults to title if left blank)</small></label>
                <input type="text" name="meta_title" id="meta_title" value="<?= htmlspecialchars($blog['meta_title'] ?? ''); ?>">
            </div>

            <div class="field">
                <label for="meta_description">Meta Description <small>(optional, max 200 chars)</small></label>
                <textarea name="meta_description" id="meta_description" maxlength="200"><?= htmlspecialchars($blog['meta_description'] ?? ''); ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <a href="index.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                Save
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