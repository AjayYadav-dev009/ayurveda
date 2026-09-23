<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/helper.php';
require_once __DIR__ . '/../function/review.php';
require_once __DIR__ . '/../function/csrf.php';

if (!isCustomerLogin()) {
    redirect(BASE_URL . 'account/login.php?redirect=account/reviews.php');
}

$user = getUserById($conn, $_SESSION['customer_id']);

if ($user === null) {
    logoutUser();
    redirect(BASE_URL . 'account/login.php');
}

/**
 * ---------------------------------------------------------------------
 * Review submission
 * ---------------------------------------------------------------------
 * Unlike product_details.php (which already knows the product from the
 * URL slug), this page lists several reviewable products at once, so
 * product_id necessarily comes from the submitted form. That's safe
 * because submitProductReview() independently re-checks, server-side,
 * that this exact user has a delivered order for that exact product and
 * hasn't already reviewed it — the POST value is never trusted on its
 * own, only used as a lookup key that then gets fully re-validated.
 */
$reviewErrors = [];
$reviewErrorProductId = null;
$reviewJustSubmitted = isset($_GET['review']) && $_GET['review'] === 'submitted';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $submittedProductId = (int) ($_POST['product_id'] ?? 0);

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $reviewErrors['general'] = 'Your session has expired. Please refresh the page and try again.';
        $reviewErrorProductId = $submittedProductId;
    } else {
        try {
            submitProductReview(
                $conn,
                $_SESSION['customer_id'],
                $submittedProductId,
                $_POST['rating'] ?? null,
                $_POST['review'] ?? ''
            );
            redirect(BASE_URL . 'account/reviews.php?review=submitted');
        } catch (InvalidArgumentException $e) {
            $reviewErrors['general'] = $e->getMessage();
            $reviewErrorProductId = $submittedProductId;
        } catch (Exception $e) {
            error_log('Review submission failed: ' . $e->getMessage());
            $reviewErrors['general'] = 'Something went wrong while submitting your review. Please try again.';
            $reviewErrorProductId = $submittedProductId;
        }
    }
}

try {
    $reviewableProducts = getReviewableProductsForCustomer($conn, $user['id']);
} catch (Exception $e) {
    error_log('Failed to load reviewable products: ' . $e->getMessage());
    $reviewableProducts = [];
}

try {
    $myReviews = getReviewsForCustomer($conn, $user['id']);
} catch (Exception $e) {
    error_log('Failed to load customer reviews: ' . $e->getMessage());
    $myReviews = [];
}

$reviewCsrfToken = generateCSRFToken();

$activeNav = 'reviews';
require __DIR__ . '/account-sidebar.php';
?>

<style>
    .reviews-section {
        margin-bottom: 32px;
    }

    .reviews-section h2 {
        margin: 0 0 14px;
        font-size: 16px;
        font-weight: 800;
        color: var(--color-text);
    }

    .reviews-empty {
        font-size: 14px;
        color: var(--color-text-light);
    }

    /* --- Reviewable products --- */

    .reviewable-card {
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        padding: 16px 20px;
        margin-bottom: 12px;
    }

    .reviewable-card__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .reviewable-card__title {
        font-size: 14.5px;
        font-weight: 700;
        color: var(--color-text);
        text-decoration: none;
    }

    .reviewable-card__title:hover {
        color: var(--color-primary-dark);
        text-decoration: underline;
    }

    .reviewable-card__toggle {
        flex-shrink: 0;
        padding: 8px 16px;
        border-radius: var(--radius-md);
        border: 1px solid var(--color-primary);
        background: var(--color-white);
        color: var(--color-primary);
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
    }

    .reviewable-card[open] .reviewable-card__toggle,
    .reviewable-card__toggle:hover {
        background: var(--color-primary);
        color: var(--color-white);
    }

    .reviewable-card__head summary {
        list-style: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        width: 100%;
        cursor: pointer;
    }

    .reviewable-card__head summary::-webkit-details-marker {
        display: none;
    }

    .reviewable-card__form {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid var(--color-border);
    }

    .form-error {
        margin: 0 0 16px;
        padding: 12px 14px;
        font-size: 13px;
        color: #8a1c14;
        background: #fbeceb;
        border: 1px solid #f2c6c2;
        border-radius: var(--radius-sm);
    }

    .form-success {
        margin: 0 0 20px;
        padding: 12px 14px;
        font-size: 13px;
        color: var(--color-primary-dark);
        background: var(--color-primary-light);
        border: 1px solid var(--color-primary);
        border-radius: var(--radius-sm);
        line-height: 1.5;
    }

    .rv-block-label {
        display: block;
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 600;
        color: var(--color-text);
    }

    /* Pure-CSS interactive star rating: markup order 5,4,3,2,1 (reversed),
       displayed left-to-right via row-reverse, so ~ selects "this star
       and everything to its left" for both hover and :checked. */
    .rv-star-input {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
        gap: 2px;
        border: none;
        padding: 0;
        margin: 0 0 16px;
        width: max-content;
    }

    .rv-star-input input {
        position: absolute;
        opacity: 0;
        width: 1px;
        height: 1px;
    }

    .rv-star-input label {
        font-size: 28px;
        line-height: 1;
        color: var(--color-border);
        cursor: pointer;
        transition: color 0.1s ease;
    }

    .rv-star-input label:hover,
    .rv-star-input label:hover~label,
    .rv-star-input input:checked~label {
        color: var(--color-accent);
    }

    .reviewable-card__form textarea {
        width: 100%;
        min-height: 100px;
        padding: 12px 14px;
        font-size: 14px;
        font-family: inherit;
        color: var(--color-text);
        background: var(--color-bg);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-sm);
        box-sizing: border-box;
        resize: vertical;
        margin-bottom: 16px;
    }

    .reviewable-card__form textarea:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px var(--color-primary-light);
    }

    .reviewable-card__form button[type="submit"] {
        padding: 11px 24px;
        border: none;
        border-radius: var(--radius-md);
        background: var(--color-primary);
        color: var(--color-white);
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
    }

    .reviewable-card__form button[type="submit"]:hover {
        background: var(--color-primary-dark);
    }

    /* --- Review history --- */

    .my-review-card {
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        padding: 16px 20px;
        margin-bottom: 12px;
    }

    .my-review-card__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 6px;
    }

    .my-review-card__title {
        font-size: 14.5px;
        font-weight: 700;
        color: var(--color-text);
        text-decoration: none;
    }

    .my-review-card__title:hover {
        color: var(--color-primary-dark);
        text-decoration: underline;
    }

    .rv-stars {
        color: var(--color-border);
        letter-spacing: 1px;
        font-size: 14px;
        white-space: nowrap;
    }

    .rv-stars .filled {
        color: var(--color-accent);
    }

    .my-review-card__meta {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 12px;
        color: var(--color-text-light);
    }

    .my-review-card__text {
        margin: 6px 0 0;
        font-size: 14px;
        line-height: 1.6;
        color: var(--color-text);
        white-space: pre-line;
    }

    .rv-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .rv-badge--pending {
        background: #fdf3e3;
        color: #92650f;
    }

    .rv-badge--active {
        background: var(--color-primary-light);
        color: var(--color-primary-dark);
    }

    .rv-badge--rejected {
        background: #fbeceb;
        color: #8a1c14;
    }

    .rv-verified {
        color: var(--color-primary-dark);
        font-weight: 700;
    }
</style>

<h1>My Reviews</h1>

<?php if ($reviewJustSubmitted): ?>
    <p class="form-success">Thanks! Your review has been submitted and will appear once approved.</p>
<?php endif; ?>

<div class="reviews-section">
    <h2>Products You Can Review</h2>

    <?php if (empty($reviewableProducts)): ?>
        <p class="reviews-empty">Products you've purchased and received will show up here so you can review them.</p>
    <?php else: ?>
        <?php foreach ($reviewableProducts as $rp): ?>
            <details class="reviewable-card" <?php echo $reviewErrorProductId === $rp['id'] ? 'open' : ''; ?>>
                <summary class="reviewable-card__head">
                    <a class="reviewable-card__title" href="<?php echo htmlspecialchars(BASE_URL . 'products/product_details.php?slug=' . $rp['slug']); ?>" onclick="event.stopPropagation();"><?php echo htmlspecialchars($rp['title']); ?></a>
                    <span class="reviewable-card__toggle">Write a Review</span>
                </summary>

                <div class="reviewable-card__form">
                    <?php if ($reviewErrorProductId === $rp['id'] && !empty($reviewErrors['general'])): ?>
                        <p class="form-error"><?php echo htmlspecialchars($reviewErrors['general']); ?></p>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo BASE_URL; ?>account/reviews.php">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($reviewCsrfToken); ?>">
                        <input type="hidden" name="submit_review" value="1">
                        <input type="hidden" name="product_id" value="<?php echo (int) $rp['id']; ?>">

                        <span class="rv-block-label">Your Rating</span>
                        <fieldset class="rv-star-input">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" id="rv-star<?php echo $rp['id']; ?>-<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
                                <label for="rv-star<?php echo $rp['id']; ?>-<?php echo $i; ?>" title="<?php echo $i; ?> star<?php echo $i === 1 ? '' : 's'; ?>">&#9733;</label>
                            <?php endfor; ?>
                        </fieldset>

                        <label for="rv-text-<?php echo $rp['id']; ?>" class="rv-block-label">Your Review (optional)</label>
                        <textarea id="rv-text-<?php echo $rp['id']; ?>" name="review" maxlength="2000" placeholder="Write your review..."></textarea>

                        <button type="submit">Submit Review</button>
                    </form>
                </div>
            </details>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="reviews-section">
    <h2>Your Reviews</h2>

    <?php if (empty($myReviews)): ?>
        <p class="reviews-empty">You haven't written any reviews yet.</p>
    <?php else: ?>
        <?php foreach ($myReviews as $review): ?>
            <div class="my-review-card">
                <div class="my-review-card__head">
                    <a class="my-review-card__title" href="<?php echo htmlspecialchars(BASE_URL . 'products/product_details.php?slug=' . $review['product_slug']); ?>"><?php echo htmlspecialchars($review['product_title']); ?></a>
                    <div class="my-review-card__meta">
                        <span class="rv-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="<?php echo $i <= $review['rating'] ? 'filled' : ''; ?>">&#9733;</span>
                            <?php endfor; ?>
                        </span>
                        <span class="rv-badge rv-badge--<?php echo strtolower($review['status']); ?>"><?php echo htmlspecialchars($review['status']); ?></span>
                        <?php if ($review['verified_purchase']): ?>
                            <span class="rv-verified">&#10003; Verified Purchase</span>
                        <?php endif; ?>
                        <span><?php echo date('d M Y', strtotime($review['created_at'])); ?></span>
                    </div>
                </div>
                <?php if (!empty($review['review'])): ?>
                    <p class="my-review-card__text"><?php echo htmlspecialchars($review['review']); ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
