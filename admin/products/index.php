<?php include __DIR__ . '/../../function/product.php'; ?>
<?php require_once __DIR__ . '/../../config/database.php'; ?>
<?php include __DIR__ . '/../../includes/auth.php'; ?>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<?php
$products = getProducts($conn);
?>

<style>
    table {
        border-collapse: collapse;
        width: 100%;
        font-family: sans-serif;
    }

    th,
    td {
        border: 2px solid #000000;
        padding: 8px;
        text-align: left;
        vertical-align: middle;
    }

    th {
        background-color: #dddcdc;
        font-weight: 900;
        color: #000000;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .product-thumb {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
        display: block;
    }

    .no-thumb {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #eee;
        color: #999;
        font-size: 0.7rem;
        border-radius: 4px;
        text-align: center;
    }

    .price-sale {
        color: #dc3545;
        font-weight: 600;
    }

    .price-original {
        text-decoration: line-through;
        color: #888;
        margin-left: 6px;
        font-size: 0.9em;
    }

    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .badge-active {
        background-color: #d4edda;
        color: #155724;
    }

    .badge-inactive {
        background-color: #f1f1f1;
        color: #555555;
    }

    .badge-draft {
        background-color: #fff3cd;
        color: #856404;
    }

    /* Base (from before) */
    .btn {
        display: inline-block;
        padding: 10px 20px;
        font-size: 1rem;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    /* Variants */
    .btn-add {
        background-color: #28a745;
    }

    .btn-add:hover {
        background-color: #218838;
    }

    .btn-view {
        background-color: #0d6efd;
        margin-bottom: 10px;
    }

    .btn-view:hover {
        background-color: #0b5ed7;
    }

    .btn-edit {
        background-color: #ffc107;
        color: #212529;
        margin-bottom: 10px;
    }

    .btn-edit:hover {
        background-color: #e0a800;
    }

    .btn-delete {
        background-color: #dc3545;
    }

    .btn-delete:hover {
        background-color: #bb2d3b;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 0.85rem;
    }

    .actions-cell {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    /* Focus & disabled (shared) */
    .btn:focus-visible {
        outline: 2px solid currentColor;
        outline-offset: 2px;
    }

    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>

<div>
    <a href="add.php" class="btn btn-add" style="margin-bottom: 10px;">Add product</a>
</div>

<table>
    <thead>
        <tr>
            <th>Image</th>
            <th>Title</th>
            <th>Category</th>
            <th>Price</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($products) === 0) : ?>
            <tr>
                <td colspan="7" style="text-align:center;">No products found.</td>
            </tr>
        <?php else : ?>
            <?php foreach ($products as $product) : ?>
                <?php
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
                            <img class="product-thumb" src="<?php echo htmlspecialchars($product['primary_image']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                        <?php else : ?>
                            <div class="no-thumb">No image</div>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($product['title']); ?></td>
                    <td><?php echo htmlspecialchars($product['primary_category_name'] ?? '—'); ?></td>
                    <td>
                        <?php if ($hasSale) : ?>
                            <span class="price-sale">₹<?php echo number_format((float) $product['base_sale_price'], 2); ?></span>
                            <span class="price-original">₹<?php echo number_format((float) $product['base_price'], 2); ?></span>
                        <?php else : ?>
                            ₹<?php echo number_format((float) $product['base_price'], 2); ?>
                        <?php endif; ?>
                        <?php if ((int) $product['has_variants'] === 1) : ?>
                            <div style="font-size:0.8em;color:#888;">(has variants)</div>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($product['status']); ?></span></td>
                    <td><?php echo htmlspecialchars($product['created_at']); ?></td>
                    <td>
                        <div class="actions-cell">
                            <a href="view.php?id=<?php echo (int) $product['id']; ?>" class="btn btn-view btn-sm">View</a>
                            <a href="edit.php?id=<?php echo (int) $product['id']; ?>" class="btn btn-edit btn-sm">Edit</a>
                            <form method="POST" action="delete.php" style="display:inline;">
                                <input type="hidden" name="id" value="<?= (int) $product['id']; ?>">
                                <button type="submit" class="btn btn-delete btn-sm" onclick="return confirm('Delete this product?');">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>