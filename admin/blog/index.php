<?php
$pageTitle = 'Blog Management';
$activeNav = 'blog';
?>
<?php include __DIR__ . '/../../function/blog.php'; ?>
<?php require_once __DIR__ . '/../../config/database.php'; ?>
<?php include __DIR__ . '/../../includes/auth.php'; ?>
<?php include __DIR__ . '/../include/header.php'; ?>

<?php
$posts = getBlogPosts($conn);
$postCount = mysqli_num_rows($posts);
?>

<style>
    .ladm { max-width: 1180px; margin: 0 auto; }
    .ladm * { box-sizing: border-box; }

    .ladm .toolbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px; flex-wrap: wrap; }
    .ladm .toolbar h1 { margin: 0; font-size: 1.5rem; font-weight: 800; letter-spacing: -0.01em; color: var(--ink); }
    .ladm .toolbar .subtitle { display: block; margin-top: 2px; font-size: 0.85rem; font-weight: 600; color: var(--muted); }
    .ladm .toolbar-right { display: flex; align-items: center; gap: 10px; }

    .ladm .search { position: relative; display: flex; align-items: center; }
    .ladm .search svg { position: absolute; left: 12px; width: 16px; height: 16px; color: var(--muted); pointer-events: none; }
    .ladm .search input {
        width: 230px; padding: 9px 12px 9px 34px; font-size: 0.88rem; font-family: inherit;
        border: 1px solid var(--line); border-radius: 10px; background: #fff; color: var(--ink);
    }
    .ladm .search input:focus { outline: none; border-color: var(--leaf); box-shadow: 0 0 0 3px var(--leaf-tint); }

    .ladm .btn {
        display: inline-flex; align-items: center; gap: 7px; padding: 10px 18px; font-size: 0.88rem; font-weight: 700;
        text-align: center; text-decoration: none; color: #fff; border: none; border-radius: 10px; cursor: pointer;
        transition: background-color 0.15s ease, transform 0.15s ease;
    }
    .ladm .btn svg { width: 15px; height: 15px; }
    .ladm .btn:hover { transform: translateY(-1px); }
    .ladm .btn:focus-visible { outline: 2px solid currentColor; outline-offset: 2px; }

    .ladm .btn-add { background-color: var(--leaf); }
    .ladm .btn-add:hover { background-color: var(--leaf-dark); }
    .ladm .btn-edit { background: #fdf1de; color: #93650f; }
    .ladm .btn-edit:hover { background: #fbe6bf; }
    .ladm .btn-delete { background: #fdeceb; color: #c23b32; }
    .ladm .btn-delete:hover { background: #fadedb; }
    .ladm .btn-sm { padding: 7px 12px; font-size: 0.8rem; }

    .ladm .card { background: #fff; border: 1px solid var(--line); border-radius: 16px; overflow: hidden; box-shadow: 0 1px 2px rgba(23, 72, 61, 0.04); }

    .ladm table { border-collapse: collapse; width: 100%; font-size: 0.9rem; }
    .ladm th, .ladm td { padding: 12px 16px; text-align: left; vertical-align: middle; border-bottom: 1px solid var(--line); }
    .ladm thead th { background: var(--mist); font-size: 0.74rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; color: var(--muted); }
    .ladm tbody tr:last-child td { border-bottom: none; }
    .ladm tbody tr:hover { background: #fafcf9; }

    .ladm .post-title { font-weight: 700; color: var(--ink); }
    .ladm .post-slug { font-family: ui-monospace, "SFMono-Regular", Menlo, monospace; font-size: 0.82rem; color: var(--muted); }
    .ladm .post-image { color: var(--muted); font-size: 0.82rem; max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .ladm .status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 11px; border-radius: 99px; font-size: 0.78rem; font-weight: 700; }
    .ladm .status-badge::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .ladm .status-Active { background: var(--leaf-tint); color: var(--leaf-dark); }
    .ladm .status-Draft { background: #fdf1de; color: #93650f; }
    .ladm .status-Inactive { background: #fdeceb; color: #c23b32; }

    .ladm .muted-date { color: var(--muted); font-size: 0.85rem; white-space: nowrap; }
    .ladm .actions-cell { display: flex; gap: 6px; flex-wrap: wrap; }

    .ladm .empty { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 60px 20px; text-align: center; color: var(--muted); }
    .ladm .empty svg { width: 40px; height: 40px; color: #c9d8cd; }
    .ladm .empty strong { color: var(--ink); font-size: 0.98rem; }

    @media (max-width: 900px) {
        .ladm .post-image, .ladm td:nth-child(4) { display: none; }
    }

    @media (max-width: 720px) {
        .ladm .toolbar-right { width: 100%; }
        .ladm .search { flex: 1; }
        .ladm .search input { width: 100%; }
        .ladm .card { overflow-x: auto; }
    }
</style>

<div class="ladm">
    <div class="toolbar">
        <div>
            <h1>Blog Management</h1>
            <span class="subtitle"><?= $postCount ?> post<?= $postCount === 1 ? '' : 's' ?> total</span>
        </div>
        <div class="toolbar-right">
            <label class="search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="search" id="blogSearch" placeholder="Search posts…" aria-label="Search blog posts">
            </label>
            <a href="add.php" class="btn btn-add">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Add Blog Post
            </a>
        </div>
    </div>

    <div class="card">
        <?php if ($postCount === 0) : ?>
            <div class="empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Z"/><path d="M14 3v5h5"/><path d="M9 13h6M9 17h6"/></svg>
                <strong>No blog posts yet</strong>
                <span>Write your first post to share wellness stories with customers.</span>
                <a href="add.php" class="btn btn-add" style="margin-top:6px;">Add Blog Post</a>
            </div>
        <?php else : ?>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Image</th>
                            <th>Status</th>
                            <th>Published</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="blogRows">
                        <?php foreach ($posts as $post) : ?>
                            <tr>
                                <td>
                                    <div class="post-title"><?= htmlspecialchars($post['title']) ?></div>
                                    <div class="post-slug"><?= htmlspecialchars($post['slug']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($post['category_name'] ?? '—') ?></td>
                                <td class="post-image"><?= htmlspecialchars($post['image'] ?? '—') ?></td>
                                <td><span class="status-badge status-<?= htmlspecialchars($post['status']) ?>"><?= htmlspecialchars($post['status']) ?></span></td>
                                <td class="muted-date"><?= htmlspecialchars($post['published_at'] ?? '—') ?></td>
                                <td class="muted-date"><?= htmlspecialchars($post['updated_at']) ?></td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="edit.php?id=<?= (int) $post['id'] ?>" class="btn btn-edit btn-sm" title="Edit">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"/><path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                                        </a>
                                        <a href="delete.php?id=<?= (int) $post['id'] ?>" class="btn btn-delete btn-sm" title="Delete" onclick="return confirm('Delete this post?');">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                        </a>
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
        var input = document.getElementById('blogSearch');
        var rows = document.querySelectorAll('#blogRows tr');
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