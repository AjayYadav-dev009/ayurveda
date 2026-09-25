<?php
$pageTitle = 'Categories';
$activeNav = 'categories';
?>
<?php include __DIR__ . '/../../function/category.php'; ?>
<?php require_once __DIR__ . '/../../config/database.php'; ?>
<?php include __DIR__ . '/../../includes/auth.php'; ?>
<?php include __DIR__ . '/../include/header.php'; ?>

<?php
$categories = getCategories($conn);
$categoryCount = mysqli_num_rows($categories);
?>

<style>
    .ladm { max-width: 1160px; margin: 0 auto; }
    .ladm * { box-sizing: border-box; }

    .ladm .toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .ladm .toolbar h1 { margin: 0; font-size: 1.5rem; font-weight: 800; letter-spacing: -0.01em; color: var(--ink); }
    .ladm .toolbar .subtitle { display: block; margin-top: 2px; font-size: 0.85rem; font-weight: 600; color: var(--muted); }

    .ladm .toolbar-right { display: flex; align-items: center; gap: 10px; }

    .ladm .search {
        position: relative;
        display: flex;
        align-items: center;
    }

    .ladm .search svg {
        position: absolute;
        left: 12px;
        width: 16px;
        height: 16px;
        color: var(--muted);
        pointer-events: none;
    }

    .ladm .search input {
        width: 230px;
        padding: 9px 12px 9px 34px;
        font-size: 0.88rem;
        font-family: inherit;
        border: 1px solid var(--line);
        border-radius: 10px;
        background: #fff;
        color: var(--ink);
    }

    .ladm .search input:focus {
        outline: none;
        border-color: var(--leaf);
        box-shadow: 0 0 0 3px var(--leaf-tint);
    }

    .ladm .btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 18px;
        font-size: 0.88rem;
        font-weight: 700;
        text-align: center;
        text-decoration: none;
        color: #fff;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: background-color 0.15s ease, transform 0.15s ease;
    }

    .ladm .btn svg { width: 16px; height: 16px; }
    .ladm .btn:hover { transform: translateY(-1px); }
    .ladm .btn:focus-visible { outline: 2px solid currentColor; outline-offset: 2px; }
    .ladm .btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

    .ladm .btn-add { background-color: var(--leaf); }
    .ladm .btn-add:hover { background-color: var(--leaf-dark); }

    .ladm .btn-edit { background: var(--sky-tint); color: var(--sky); }
    .ladm .btn-edit:hover { background: #dcecf7; }

    .ladm .btn-delete { background: #fdeceb; color: #c23b32; }
    .ladm .btn-delete:hover { background: #fadedb; }

    .ladm .btn-icon { padding: 8px; }

    .ladm .card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(23, 72, 61, 0.04);
    }

    .ladm table { border-collapse: collapse; width: 100%; font-size: 0.9rem; }

    .ladm th, .ladm td {
        padding: 13px 16px;
        text-align: left;
        vertical-align: middle;
        border-bottom: 1px solid var(--line);
    }

    .ladm thead th {
        background: var(--mist);
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--muted);
        border-bottom: 1px solid var(--line);
    }

    .ladm tbody tr:last-child td { border-bottom: none; }
    .ladm tbody tr:hover { background: #fafcf9; }

    .ladm .cat-name { font-weight: 700; color: var(--ink); }
    .ladm .cat-slug { font-family: ui-monospace, "SFMono-Regular", Menlo, monospace; font-size: 0.82rem; color: var(--muted); }
    .ladm .cat-desc { max-width: 280px; color: var(--muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .ladm .thumb {
        width: 42px; height: 42px; border-radius: 9px; object-fit: cover; border: 1px solid var(--line); display: block;
    }

    .ladm .no-thumb {
        width: 42px; height: 42px; border-radius: 9px; display: flex; align-items: center; justify-content: center;
        background: var(--mist); color: var(--muted);
    }

    .ladm .no-thumb svg { width: 18px; height: 18px; }

    .ladm .pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 11px; border-radius: 99px; font-size: 0.78rem; font-weight: 700;
    }

    .ladm .pill::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

    .ladm .pill-active { background: var(--leaf-tint); color: var(--leaf-dark); }
    .ladm .pill-inactive { background: var(--mist); color: var(--muted); }

    .ladm .sort-chip {
        display: inline-block; min-width: 26px; padding: 3px 8px; text-align: center;
        border-radius: 7px; background: var(--sky-tint); color: var(--sky); font-weight: 700; font-size: 0.8rem;
    }

    .ladm .muted-date { color: var(--muted); font-size: 0.85rem; white-space: nowrap; }

    .ladm .actions-cell { display: flex; gap: 6px; flex-wrap: wrap; }

    .ladm .empty {
        display: flex; flex-direction: column; align-items: center; gap: 10px;
        padding: 60px 20px; text-align: center; color: var(--muted);
    }

    .ladm .empty svg { width: 40px; height: 40px; color: #c9d8cd; }
    .ladm .empty strong { color: var(--ink); font-size: 0.98rem; }

    @media (max-width: 720px) {
        .ladm .toolbar-right { width: 100%; }
        .ladm .search { flex: 1; }
        .ladm .search input { width: 100%; }
        .ladm .cat-desc { display: none; }
        .ladm .card { overflow-x: auto; }
    }
</style>

<div class="ladm">
    <div class="toolbar">
        <div>
            <h1>Categories</h1>
            <span class="subtitle"><?= $categoryCount ?> categor<?= $categoryCount === 1 ? 'y' : 'ies' ?> · shown in Shop By Category</span>
        </div>
        <div class="toolbar-right">
            <label class="search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="search" id="catSearch" placeholder="Search categories…" aria-label="Search categories">
            </label>
            <a href="add.php" class="btn btn-add">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Add Category
            </a>
        </div>
    </div>

    <div class="card">
        <?php if ($categoryCount === 0) : ?>
            <div class="empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 3 8l9 5 9-5-9-5Z"/><path d="m3 13 9 5 9-5"/></svg>
                <strong>No categories yet</strong>
                <span>Add your first category to start building Shop By Category.</span>
                <a href="add.php" class="btn btn-add" style="margin-top:6px;">Add Category</a>
            </div>
        <?php else : ?>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Sort</th>
                            <th>Created</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="catRows">
                        <?php foreach ($categories as $category) :
                            $imgPath = trim((string) ($category['image'] ?? ''));
                            $isActive = strcasecmp((string) $category['status'], 'Active') === 0;
                        ?>
                            <tr>
                                <td>
                                    <?php if ($imgPath !== '' && function_exists('getCategoryImageUrl')) : ?>
                                        <img class="thumb" src="<?= htmlspecialchars(getCategoryImageUrl($imgPath)) ?>" alt="">
                                    <?php else : ?>
                                        <span class="no-thumb" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="1.6"/><path d="m21 15-4.5-4.5L6 21"/></svg>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="cat-name"><?= htmlspecialchars($category['name']) ?></td>
                                <td class="cat-slug"><?= htmlspecialchars($category['slug']) ?></td>
                                <td class="cat-desc" title="<?= htmlspecialchars((string) $category['description']) ?>"><?= htmlspecialchars((string) $category['description']) ?></td>
                                <td><span class="pill <?= $isActive ? 'pill-active' : 'pill-inactive' ?>"><?= htmlspecialchars($category['status']) ?></span></td>
                                <td><span class="sort-chip"><?= (int) $category['sort_order'] ?></span></td>
                                <td class="muted-date"><?= htmlspecialchars($category['created_at']) ?></td>
                                <td class="muted-date"><?= htmlspecialchars($category['updated_at']) ?></td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="edit.php?id=<?= (int) $category['id'] ?>" class="btn btn-edit btn-icon" title="Edit" aria-label="Edit <?= htmlspecialchars($category['name']) ?>">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"/><path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                                        </a>
                                        <form method="POST" action="delete.php" style="display:inline;" onsubmit="return confirm('Delete this category?');">
                                            <input type="hidden" name="id" value="<?= (int) $category['id'] ?>">
                                            <button type="submit" class="btn btn-delete btn-icon" title="Delete" aria-label="Delete <?= htmlspecialchars($category['name']) ?>">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    (function () {
        var input = document.getElementById('catSearch');
        var rows = document.querySelectorAll('#catRows tr');
        if (!input || !rows.length) return;
        input.addEventListener('input', function () {
            var q = input.value.trim().toLowerCase();
            rows.forEach(function (row) {
                row.style.display = row.textContent.toLowerCase().indexOf(q) === -1 ? 'none' : '';
            });
        });
    })();
</script>

<?php include __DIR__ . '/../include/footer.php'; ?>