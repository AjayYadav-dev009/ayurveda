<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../function/review.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    die('Invalid review id.');
}

$review = getReviewById($conn, $id);
if ($review === null) {
    die('Review not found.');
}

$badgeClass = [
    'Active' => 'badge-active',
    'Pending' => 'badge-pending',
    'Rejected' => 'badge-rejected',
][$review['status']] ?? 'badge-pending';

include __DIR__ . '/../include/header.php'
?>

<style>
    dl {
        font-family: sans-serif;
    }

    dt {
        font-weight: 700;
        margin-top: 10px;
    }

    dd {
        margin-left: 0;
        margin-bottom: 5px;
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
        margin-left: 6px;
    }

    .stars {
        color: #e0a800;
        letter-spacing: 2px;
        font-size: 1.1rem;
    }

    .review-text {
        max-width: 640px;
        line-height: 1.6;
        color: #333;
    }

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
    }

    .btn-edit {
        background-color: #ffc107;
        color: #212529;
    }

    .btn-delete {
        background-color: #dc3545;
    }

    .btn-back {
        background-color: #6c757d;
    }

    .btn-approve {
        background-color: #218838;
    }

    .btn-reject {
        background-color: #e08e0b;
    }

    .actions-row {
        margin-bottom: 15px;
        display: flex;
        gap: 8px;
    }

    .actions-row form {
        display: inline;
    }
</style>

<div class="actions-row">
    <a href="index.php" class="btn btn-back">&laquo; Back to reviews</a>
    <a href="edit.php?id=<?php echo (int) $review['id']; ?>" class="btn btn-edit">Edit</a>

    <?php if ($review['status'] !== 'Active'): ?>
        <form method="POST" action="set-status.php">
            <input type="hidden" name="id" value="<?php echo (int) $review['id']; ?>">
            <input type="hidden" name="status" value="Active">
            <button type="submit" class="btn btn-approve">Approve</button>
        </form>
    <?php endif; ?>

    <?php if ($review['status'] !== 'Rejected'): ?>
        <form method="POST" action="set-status.php">
            <input type="hidden" name="id" value="<?php echo (int) $review['id']; ?>">
            <input type="hidden" name="status" value="Rejected">
            <button type="submit" class="btn btn-reject">Reject</button>
        </form>
    <?php endif; ?>

    <form method="POST" action="delete.php" onsubmit="return confirm('Delete this review? This cannot be undone.');">
        <input type="hidden" name="id" value="<?php echo (int) $review['id']; ?>">
        <button type="submit" class="btn btn-delete">Delete</button>
    </form>
</div>

<h2>
    <?php echo htmlspecialchars($review['customer_name']); ?>
    <?php if (!empty($review['verified_purchase'])): ?>
        <span class="badge badge-verified">Verified Purchase</span>
    <?php endif; ?>
</h2>
<p>
    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($review['status']); ?></span>
</p>

<dl>
    <dt>Customer email</dt>
    <dd><?php echo htmlspecialchars($review['customer_email']); ?></dd>

    <dt>Product</dt>
    <dd><a href="<?php echo BASE_URL; ?>product_details.php?slug=<?php echo urlencode($review['product_slug']); ?>" target="_blank"><?php echo htmlspecialchars($review['product_title']); ?></a></dd>

    <dt>Rating</dt>
    <dd><span class="stars"><?php echo str_repeat('&#9733;', (int) $review['rating']) . str_repeat('&#9734;', 5 - (int) $review['rating']); ?></span></dd>

    <dt>Review</dt>
    <dd class="review-text"><?php echo $review['review'] !== null ? nl2br(htmlspecialchars($review['review'])) : '<em>No written review — rating only.</em>'; ?></dd>

    <dt>Submitted at</dt>
    <dd><?php echo htmlspecialchars($review['created_at']); ?></dd>

    <dt>Last updated</dt>
    <dd><?php echo htmlspecialchars($review['updated_at']); ?></dd>
</dl>

<?php include __DIR__ . '/../include/footer.php' ?>