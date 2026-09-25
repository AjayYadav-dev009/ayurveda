<?php
$pageTitle = 'Edit Category';
$activeNav = 'categories';
?>
<?php include __DIR__ . '/../../function/category.php'; ?>
<?php include __DIR__ . '/../../function/helper.php'; ?>
<?php include __DIR__ . '/../../includes/auth.php'; ?>
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
        $status = $_POST['status'] ?? null;
        $sort_order = $_POST['sort_order'] ?? null;

        // uploadCategoryImage() validates the upload and returns a plain
        // filename string (or null if no new file was chosen). Never pass
        // $_FILES['image'] straight into updateCategory() — it's an array,
        // not the string the DB column/bind_param expects.
        $newImage = uploadCategoryImage($_FILES['image'] ?? null);

        // Keep the existing image on disk/DB unless the admin uploaded a
        // replacement.
        $image = $newImage ?? $category['image'];

        $slug = createSlug($name);
        $meta_title = createMetaTitle($name);
        $meta_description = createMetaDescription($description);

        updateCategory($conn, $id, $parent_id, $name, $slug, $meta_title, $meta_description, $description, $image, $status, $sort_order);

        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
        // Re-fill the form with the submitted values, not the stale pre-edit row.
        $category['parent_id'] = $parent_id;
        $category['name'] = $name;
        $category['description'] = $description;
        $category['status'] = $status;
        $category['sort_order'] = $sort_order;
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

    .fadm .status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 11px; border-radius: 99px; font-size: 0.78rem; font-weight: 700;
    }
    .fadm .status-pill::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .fadm .status-Active { background: var(--leaf-tint); color: var(--leaf-dark); }
    .fadm .status-Inactive { background: var(--mist); color: var(--muted); }

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

    .fadm .current-image-wrap { display: flex; align-items: center; gap: 10px; margin-top: 10px; }
    .fadm .current-image {
        width: 72px; height: 72px; object-fit: cover;
        border: 1px solid var(--line); border-radius: 10px;
        box-shadow: 0 1px 2px rgba(23, 72, 61, 0.05);
    }
    .fadm .current-image-label { font-size: 0.78rem; color: var(--muted); font-weight: 600; }

    .fadm .form-actions { display: flex; align-items: center; justify-content: flex-end; gap: 10px; margin-top: 6px; }

    @media (max-width: 560px) {
        .fadm .row { grid-template-columns: 1fr; }
        .fadm .card { padding: 18px; }
    }
</style>

<div class="fadm">
    <div class="topbar">
        <div>
            <h1>Edit Category</h1>
            <span class="subtitle">
                Category #<?= (int) $category['id'] ?>
                <span class="status-pill status-<?= htmlspecialchars($category['status']) ?>" style="margin-left:8px;"><?= htmlspecialchars($category['status']) ?></span>
            </span>
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
        <input type="hidden" name="id" value="<?= htmlspecialchars($category['id']); ?>">

        <div class="card">
            <h2>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 3 8l9 5 9-5-9-5Z"/><path d="m3 13 9 5 9-5"/></svg>
                Category Details
            </h2>

            <div class="field">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($category['name']); ?>" required>
            </div>

            <div class="row">
                <div class="field">
                    <label for="parent_id">Parent Category</label>
                    <select name="parent_id" id="parent_id">
                        <option value="">None</option>
                        <?php foreach (getCategories($conn) as $allCategory): ?>
                            <?php if ((int) $allCategory['id'] === (int) $category['id']) continue; // a category can't be its own parent ?>
                            <option value="<?= (int) $allCategory['id']; ?>" <?= (string) $allCategory['id'] === (string) $category['parent_id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($allCategory['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="status">Status</label>
                    <select name="status" id="status">
                        <option value="Active" <?= $category['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?= $category['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="field">
                <label for="description">Description</label>
                <textarea name="description" id="description"><?= htmlspecialchars($category['description']); ?></textarea>
            </div>

            <div class="row">
                <div class="field">
                    <label for="image">Image</label>
                    <input type="file" name="image" id="image" accept="image/*">
                    <?php if (!empty($category['image']) && function_exists('getCategoryImageUrl')): ?>
                        <div class="current-image-wrap">
                            <img src="<?= htmlspecialchars(getCategoryImageUrl($category['image'])); ?>" alt="Current category image" class="current-image">
                            <span class="current-image-label">Current image<br>Upload a new file to replace it</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="field">
                    <label for="sort_order">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order" value="<?= htmlspecialchars($category['sort_order']); ?>">
                </div>
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

<?php include __DIR__ . '/../include/footer.php'; ?>