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

$errors = [];
$success = false;

$rating = (int) $review['rating'];
$reviewText = (string) ($review['review'] ?? '');
$status = $review['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
    $reviewText = trim($_POST['review'] ?? '');
    $status = $_POST['status'] ?? 'Pending';

    if ($rating < 1 || $rating > 5) {
        $errors['rating'] = 'Select a rating between 1 and 5.';
    }

    if (!in_array($status, REVIEW_STATUSES, true)) {
        $errors['status'] = 'Select a valid status.';
    }

    if (empty($errors)) {
        try {
            updateReview($conn, $id, [
                'rating' => $rating,
                'review' => $reviewText,
                'status' => $status,
            ]);
            $success = true;
            $review = getReviewById($conn, $id);
            $rating = (int) $review['rating'];
            $reviewText = (string) ($review['review'] ?? '');
            $status = $review['status'];
        } catch (InvalidArgumentException $e) {
            $errors['general'] = $e->getMessage();
        } catch (Exception $e) {
            $errors['general'] = 'Something went wrong while saving the review.';
        }
    }
}
?>

<style>
    .review-edit-wrap {
        font-family: sans-serif;
        max-width: 560px;
    }

    label {
        display: block;
        font-weight: 600;
        margin-top: 14px;
        margin-bottom: 4px;
    }

    input,
    select,
    textarea {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 0.95rem;
        box-sizing: border-box;
    }

    textarea {
        min-height: 120px;
        resize: vertical;
    }

    .field-error {
        color: #c00;
        font-size: 0.85rem;
        margin: 4px 0 0;
    }

    input[type="submit"] {
        margin-top: 20px;
        width: auto;
        padding: 10px 22px;
        background-color: #218838;
        color: #fff;
        border: none;
        font-weight: 600;
        cursor: pointer;
    }
</style>

<div class="review-edit-wrap">
    <p><a href="view-one.php?id=<?= (int) $id ?>">&laquo; Back to review</a></p>

    <h2>Edit Review</h2>
    <p style="color: #666;">
        By <strong><?= htmlspecialchars($review['customer_name']) ?></strong>
        on <strong><?= htmlspecialchars($review['product_title']) ?></strong>
    </p>

    <?php if ($success): ?>
        <p style="color: #218838; font-weight: 600;">Review updated successfully.</p>
    <?php endif; ?>

    <?php if (!empty($errors['general'])): ?>
        <p style="color: #c00; font-weight: 600;"><?= htmlspecialchars($errors['general']) ?></p>
    <?php endif; ?>

    <form method="post" action="">

        <label for="rating">Rating</label>
        <select id="rating" name="rating">
            <?php for ($i = 5; $i >= 1; $i--): ?>
                <option value="<?= $i ?>" <?= $rating === $i ? 'selected' : '' ?>><?= $i ?> star<?= $i === 1 ? '' : 's' ?></option>
            <?php endfor; ?>
        </select>
        <?php if (!empty($errors['rating'])): ?>
            <p class="field-error"><?= htmlspecialchars($errors['rating']) ?></p>
        <?php endif; ?>

        <label for="review">Review text</label>
        <textarea id="review" name="review"><?= htmlspecialchars($reviewText) ?></textarea>

        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending</option>
            <option value="Active" <?= $status === 'Active' ? 'selected' : '' ?>>Active (visible on site)</option>
            <option value="Rejected" <?= $status === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>
        <?php if (!empty($errors['status'])): ?>
            <p class="field-error"><?= htmlspecialchars($errors['status']) ?></p>
        <?php endif; ?>

        <input type="submit" value="Save Changes">
    </form>
</div>