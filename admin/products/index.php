<?php
$pageTitle = 'Products';
$activeNav = 'products';
?>
<?php include __DIR__ . '/../../function/product.php'; ?>
<?php require_once __DIR__ . '/../../config/database.php'; ?>
<?php include __DIR__ . '/../../includes/auth.php'; ?>
<?php include __DIR__ . '/../include/header.php'; ?>

<?php
$products = getProducts($conn);
$productCount = mysqli_num_rows($products);
?>

<style>
    .ladm { max-width: 1200px; margin: 0 auto; }
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
    .ladm .btn-view { background: var(--sky-tint); color: var(--sky); }
    .ladm .btn-view:hover { background: #dcecf7; }
    .ladm .btn-edit { background: #fdf1de; color: #93650f; }
    .ladm .btn-edit:hover { background: #fbe6bf; }
    .ladm .btn-delete { background: #fdeceb; color: #c23b32; }
    .ladm .btn-delete:hover { background: #fadedb; }
    .ladm .btn-sm { padding: 7px 12px; font-size: 0.8rem; }
    .ladm .btn-icon { padding: 7px; }

    .ladm .card { background: #fff; border: 1px solid var(--line); border-radius: 16px; overflow: hidden; box-shadow: 0 1px 2px rgba(23, 72, 61, 0.04); }

    .ladm table { border-collapse: collapse; width: 100%; font-size: 0.9rem; }
    .ladm th, .ladm td { padding: 12px 16px; text-align: left; vertical-align: middle; border-bottom: 1px solid var(--line); }
    .ladm thead th { background: var(--mist); font-size: 0.74rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; color: var(--muted); }
    .ladm tbody tr:last-child td { border-bottom: none; }
    .ladm tbody tr:hover { background: #fafcf9; }

    .ladm .product-thumb { width: 46px; height: 46px; object-fit: cover; border-radius: 9px; border: 1px solid var(--line); display: block; }
    .ladm .no-thumb {
        width: 46px; height: 46px; display: flex; align-items: center; justify-content: center;
        background: var(--mist); color: var(--muted); border-radius: 9px;
    }
    .ladm .no-thumb svg { width: 18px; height: 18px; }

    .ladm .prod-title { font-weight: 700; color: var(--ink); }
    .ladm .prod-variants { margin-top: 2px; font-size: 0.78rem; color: var(--muted); }

    .ladm .price-sale { color: #b3382c; font-weight: 700; }
    .ladm .price-original { text-decoration: line-through; color: var(--muted); margin-left: 6px; font-size: 0.88em; }

    .ladm .badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 11px; border-radius: 99px; font-size: 0.78rem; font-weight: 700; }
    .ladm .badge::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .ladm .badge-active { background: var(--leaf-tint); color: var(--leaf-dark); }
    .ladm .badge-inactive { background: var(--mist); color: var(--muted); }
    .ladm .badge-draft { background: #fdf1de; color: #93650f; }

    .ladm .muted-date { color: var(--muted); font-size: 0.85rem; white-space: nowrap; }
    .ladm .actions-cell { display: flex; gap: 6px; flex-wrap: wrap; }

    .ladm .empty { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 60px 20px; text-align: center; color: var(--muted); }
    .ladm .empty svg { width: 40px; height: 40px; color: #c9d8cd; }
    .ladm .empty strong { color: var(--ink); font-size: 0.98rem; }

    @media (max-width: 900px) {
        .ladm .cat-desc, .ladm td:nth-child(6) { display: none; }
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
            <h1>Products</h1>
            <span class="subtitle"><?= $productCount ?> product<?= $productCount === 1 ? '' : 's' ?> in your catalogue</span>
        </div>
        <div class="toolbar-right">
            <label class="search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="search" id="prodSearch" placeholder="Search products…" aria-label="Search products">
            </label>
            <a href="add.php" class="btn btn-add">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Add product
            </a>
        </div>
    </div>

    <div class="card">
        <?php if ($productCount === 0) : ?>
            <div class="empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/></svg>
                <strong>No products yet</strong>
                <span>Add your first product to start selling.</span>
                <a href="add.php" class="btn btn-add" style="margin-top:6px;">Add product</a>
            </div>
        <?php else : ?>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="prodRows">
                        <?php foreach ($products as $product) :
                            $statusClass = match ($product['status']) {
                                'Active' => 'badge-active',
                                'Inactive' => 'badge-inactive',
                                default => 'badge-draft',
                            };
                            $hasSale = $product['base_sale_price'] !== null && $product['base_sale_price'] !== '';
                        ?>
                            <tr>
                                <td>
                                    <?php if (!empty($product['primary_image'])) : ?>
                                        <img class="product-thumb" src="<?= htmlspecialchars(getProductImageUrl($product['primary_image'])) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
                                    <?php else : ?>
                                        <span class="no-thumb" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="1.6"/><path d="m21 15-4.5-4.5L6 21"/></svg>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="prod-title"><?= htmlspecialchars($product['title']) ?></div>
                                    <?php if ((int) $product['has_variants'] === 1) : ?>
                                        <div class="prod-variants">Has variants</div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($product['primary_category_name'] ?? '—') ?></td>
                                <td>
                                    <?php if ($hasSale) : ?>
                                        <span class="price-sale">₹<?= number_format((float) $product['base_sale_price'], 2) ?></span>
                                        <span class="price-original">₹<?= number_format((float) $product['base_price'], 2) ?></span>
                                    <?php else : ?>
                                        ₹<?= number_format((float) $product['base_price'], 2) ?>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($product['status']) ?></span></td>
                                <td class="muted-date"><?= htmlspecialchars($product['created_at']) ?></td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="view.php?id=<?= (int) $product['id'] ?>" class="btn btn-view btn-sm" title="View">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <a href="edit.php?id=<?= (int) $product['id'] ?>" class="btn btn-edit btn-sm" title="Edit">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"/><path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg>
                                        </a>
                                        <form method="POST" action="delete.php" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                            <button type="submit" class="btn btn-delete btn-sm" onclick="return confirm('Delete this product?');" title="Delete">
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
        var input = document.getElementById('prodSearch');
        var rows = document.querySelectorAll('#prodRows tr');
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