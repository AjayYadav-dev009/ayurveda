<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../function/review.php';

$statusFilter = $_GET['status'] ?? '';
if (!in_array($statusFilter, REVIEW_STATUSES, true)) {
    $statusFilter = '';
}

$reviews = [];
$loadError = '';

try {
    $reviews = getAllReviews($conn, $statusFilter !== '' ? ['status' => $statusFilter] : []);
} catch (Exception $e) {
    $loadError = 'Something went wrong while loading the reviews.';
}

$approved = isset($_GET['approved']) && $_GET['approved'] === '1';
$rejected = isset($_GET['rejected']) && $_GET['rejected'] === '1';
$deleted = isset($_GET['deleted']) && $_GET['deleted'] === '1';
$updated = isset($_GET['updated']) && $_GET['updated'] === '1';

// Preserve the current status tab through the quick approve/reject POSTs
// on this same page, so the admin isn't bounced back to "All" after acting
// on a row from the "Pending" tab.
$statusQuery = $statusFilter !== '' ? '&status=' . urlencode($statusFilter) : '';
?>

<style>
    .reviews-wrap {
        font-family: sans-serif;
    }

    .reviews-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 15px;
    }

    .reviews-header h2 {
        margin: 0;
    }

    .reviews-tabs {
        display: flex;
        gap: 6px;
        margin-bottom: 18px;
    }

    .reviews-tabs a {
        display: inline-block;
        padding: 7px 14px;
        border-radius: 4px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        color: #495057;
        background-color: #f1f1f1;
    }

    .reviews-tabs a.is-active {
        background-color: #212529;
        color: #fff;
    }

    .btn {
        display: inline-block;
        padding: 8px 16px;
        font-size: 0.9rem;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-view {
        background-color: #6c757d;
    }

    .btn-edit {
        background-color: #ffc107;
        color: #212529;
    }

    .btn-delete {
        background-color: #dc3545;
    }

    .btn-approve {
        background-color: #218838;
    }

    .btn-reject {
        background-color: #e08e0b;
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 0.8rem;
        margin-right: 4px;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        padding: 10px 15px;
        border-radius: 4px;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px 15px;
        border-radius: 4px;
        margin-bottom: 15px;
        font-weight: 600;
    }

    table.reviews-table {
        width: 100%;
        border-collapse: collapse;
    }

    .reviews-table th,
    .reviews-table td {
        text-align: left;
        padding: 10px 12px;
        border-bottom: 1px solid #e2e2e2;
        vertical-align: top;
    }

    .reviews-table th {
        background-color: #f8f9fa;
        font-weight: 700;
    }

    .review-snippet {
        max-width: 320px;
        color: #444;
    }

    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-active {
        background-color: #d4edda;
        color: #155724;
    }

    .badge-pending {
        background-color: #fff3cd;
        color: #856404;
    }

    .badge-rejected {
        background-color: #f8d7da;
        color: #721c24;
    }

    .badge-verified {
        background-color: #d1ecf1;
        color: #0c5460;
        margin-left: 4px;
    }

    .stars {
        color: #e0a800;
        letter-spacing: 1px;
        white-space: nowrap;
    }

    .empty-state {
        padding: 30px;
        text-align: center;
        color: #777;
    }

    .actions-cell form {
        display: inline;
    }
</style>

<div class="reviews-wrap">
    <div class="reviews-header">
        <h2>Product Reviews</h2>
    </div>

    <div class="reviews-tabs">
        <a href="index.php" class="<?= $statusFilter === '' ? 'is-active' : '' ?>">All</a>
        <a href="index.php?status=Pending" class="<?= $statusFilter === 'Pending' ? 'is-active' : '' ?>">Pending</a>
        <a href="index.php?status=Active" class="<?= $statusFilter === 'Active' ? 'is-active' : '' ?>">Active</a>
        <a href="index.php?status=Rejected" class="<?= $statusFilter === 'Rejected' ? 'is-active' : '' ?>">Rejected</a>
    </div>

    <?php if ($approved): ?>
        <p class="alert-success">Review approved.</p>
    <?php endif; ?>

    <?php if ($rejected): ?>
        <p class="alert-success">Review rejected.</p>
    <?php endif; ?>

    <?php if ($deleted): ?>
        <p class="alert-success">Review deleted.</p>
    <?php endif; ?>

    <?php if ($updated): ?>
        <p class="alert-success">Review updated.</p>
    <?php endif; ?>

    <?php if ($loadError !== ''): ?>
        <p class="alert-error"><?= htmlspecialchars($loadError) ?></p>
    <?php endif; ?>

    <?php if (empty($reviews) && $loadError === ''): ?>
        <p class="empty-state">No reviews found<?= $statusFilter !== '' ? ' with status "' . htmlspecialchars($statusFilter) . '"' : '' ?>.</p>
    <?php else: ?>
        <table class="reviews-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $review): ?>
                    <?php
                    $badgeClass = [
                        'Active' => 'badge-active',
                        'Pending' => 'badge-pending',
                        'Rejected' => 'badge-rejected',
                    ][$review['status']] ?? 'badge-pending';
                    ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($review['customer_name']) ?>
                            <?php if (!empty($review['verified_purchase'])): ?>
                                <span class="badge badge-verified">Verified</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($review['product_title']) ?></td>
                        <td><span class="stars"><?= str_repeat('&#9733;', (int) $review['rating']) . str_repeat('&#9734;', 5 - (int) $review['rating']) ?></span></td>
                        <td class="review-snippet"><?= htmlspecialchars(mb_strimwidth((string) $review['review'], 0, 90, '…')) ?></td>
                        <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($review['status']) ?></span></td>
                        <td><?= htmlspecialchars($review['created_at']) ?></td>
                        <td class="actions-cell">
                            <a href="view-one.php?id=<?= (int) $review['id'] ?>" class="btn btn-view btn-sm">View</a>
                            <a href="edit.php?id=<?= (int) $review['id'] ?>" class="btn btn-edit btn-sm">Edit</a>

                            <?php if ($review['status'] !== 'Active'): ?>
                                <form method="POST" action="set-status.php">
                                    <input type="hidden" name="id" value="<?= (int) $review['id'] ?>">
                                    <input type="hidden" name="status" value="Active">
                                    <input type="hidden" name="redirect_status" value="<?= htmlspecialchars($statusFilter) ?>">
                                    <button type="submit" class="btn btn-approve btn-sm">Approve</button>
                                </form>
                            <?php endif; ?>

                            <?php if ($review['status'] !== 'Rejected'): ?>
                                <form method="POST" action="set-status.php">
                                    <input type="hidden" name="id" value="<?= (int) $review['id'] ?>">
                                    <input type="hidden" name="status" value="Rejected">
                                    <input type="hidden" name="redirect_status" value="<?= htmlspecialchars($statusFilter) ?>">
                                    <button type="submit" class="btn btn-reject btn-sm">Reject</button>
                                </form>
                            <?php endif; ?>

                            <form method="POST" action="delete.php" onsubmit="return confirm('Delete this review? This cannot be undone.');">
                                <input type="hidden" name="id" value="<?= (int) $review['id'] ?>">
                                <input type="hidden" name="redirect_status" value="<?= htmlspecialchars($statusFilter) ?>">
                                <button type="submit" class="btn btn-delete btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>