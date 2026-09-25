<?php
$pageTitle = 'Add Category';
$activeNav = 'categories';
?>
<?php include __DIR__ . '/../../function/category.php'; ?>
<?php include __DIR__ . '/../../function/helper.php'; ?>
<?php include __DIR__ . '/../../includes/auth.php'; ?>
<?php require_once __DIR__ . '/../../config/database.php'; ?>

<?php
$categories = getCategories($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {

        $parent_id = $_POST['parent_id'] ?? null;
        $name = $_POST['name'] ?? null;
        $description = $_POST['description'] ?? null;
        // uploadCategoryImage() validates the upload and returns a plain
        // filename string (or null if no file was chosen) — safe to pass
        // straight into addCategory().
        $image = uploadCategoryImage($_FILES['image'] ?? null);
        $status = $_POST['status'] ?? null;

        $sort_order = 0;

        $slug = createSlug($name);
        $meta_title = createMetaTitle($name);
        $meta_description = createMetaDescription($description);

        $category_id = addCategory($conn, $parent_id, $name, $slug, $meta_title, $meta_description, $description, $image, $status, $sort_order);

        header("Location: index.php");
        exit;
    } catch (Exception $e) {

        $error = $e->getMessage();
    }
}
?>
<?php include __DIR__ . '/../include/header.php'; ?>

<style>
    .fadm { max-width: 640px; margin: 0 auto 60px; }
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
        padding: 24px 26px; margin-bottom: 18px;
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

    .fadm label { display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: 700; color: var(--ink); }
    .fadm label small { font-weight: 500; color: var(--muted); }

    .fadm select,
    .fadm input[type="text"],
    .fadm input[type="number"],
    .fadm input[type="file"],
    .fadm textarea {
        width: 100%; padding: 10px 13px; font-size: 0.92rem; font-family: inherit;
        border: 1px solid var(--line); border-radius: 10px; background: #fff; color: var(--ink);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .fadm select:focus,
    .fadm input[type="text"]:focus,
    .fadm input[type="number"]:focus,
    .fadm textarea:focus {
        outline: none; border-color: var(--leaf); box-shadow: 0 0 0 3px var(--leaf-tint);
    }

    .fadm input[type="file"] { padding: 8px 10px; background: var(--mist); cursor: pointer; }

    .fadm textarea { min-height: 90px; resize: vertical; }

    .fadm .form-actions { display: flex; align-items: center; justify-content: flex-end; gap: 10px; margin-top: 6px; }

    @media (max-width: 560px) {
        .fadm .row { grid-template-columns: 1fr; }
        .fadm .card { padding: 18px; }
    }
</style>

<div class="fadm">
    <div class="topbar">
        <div>
            <h1>Add Category</h1>
            <span class="subtitle">Appears in Shop By Category once saved as Active</span>
        </div>
        <a href="index.php" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
            Back to categories
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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 3 8l9 5 9-5-9-5Z"/><path d="m3 13 9 5 9-5"/></svg>
                Category Details
            </h2>

            <div class="field">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
            </div>

            <div class="row">
                <div class="field">
                    <label for="parent_id">Parent Category</label>
                    <select name="parent_id" id="parent_id">
                        <option value="">None</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php if (isset($_POST['parent_id']) && $_POST['parent_id'] == $category['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="status">Status</label>
                    <select name="status" id="status">
                        <option value="Active" <?php if (isset($_POST['status']) && $_POST['status'] == 'Active') echo 'selected'; ?>>Active</option>
                        <option value="Inactive" <?php if (isset($_POST['status']) && $_POST['status'] == 'Inactive') echo 'selected'; ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="description">Description</label>
                <textarea name="description" id="description"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
            </div>

            <div class="field">
                <label for="image">Image</label>
                <input type="file" name="image" id="image" accept="image/*">
            </div>
        </div>

        <div class="form-actions">
            <a href="index.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Add Category
            </button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>