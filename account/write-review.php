<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/helper.php';
require_once __DIR__ . '/../function/csrf.php';
require_once __DIR__ . '/../function/review.php';

// Not logged in? Send them to log in first.
if (!isCustomerLogin()) {
    redirect(BASE_URL . 'account/login.php?redirect=account/reviews.php');
    exit;
}

$user = getUserById($conn, $_SESSION['customer_id']);

// Stale session pointing at an account that no longer exists.
if ($user === null) {
    logoutUser();
    redirect(BASE_URL . 'account/login.php');
    exit;
}

$userId     = (int) $_SESSION['customer_id'];
$reviewsUrl = BASE_URL . 'account/reviews.php';

// Sends the customer back to their reviews list with a message.
$bounce = function ($type, $message) use ($reviewsUrl) {
    $_SESSION['review_flash'] = ['type' => $type, 'message' => $message];
    redirect($reviewsUrl);
    exit; // never fall through to the form, even if redirect() doesn't exit
};

// The product comes from the URL (?product_id=… or the older ?slug=…).
// It is only ever trusted after the purchase check below.
$productId = (int) ($_GET['product_id'] ?? 0);
$slug      = trim((string) ($_GET['slug'] ?? ''));

try {
    $product = getProductForReview($conn, $productId, $slug);
} catch (Throwable $e) {
    $product = null;
}
if (!$product) {
    $bounce('error', 'That product could not be found.');
}
$productId = (int) $product['id'];

// Gate: only customers who bought (and received) this product may continue.
try {
    $canReview = customerHasPurchasedProduct($conn, $userId, $productId);
} catch (Throwable $e) {
    $canReview = false;
}
if (!$canReview) {
    $bounce('error', 'You can only review products you have purchased and received.');
}

$existing = getCustomerReview($conn, $userId, $productId);
$isEdit   = $existing !== null;
$error    = null;

$form = [
    'rating' => $isEdit ? (int) $existing['rating'] : 0,
    'review' => $isEdit ? (string) ($existing['review'] ?? '') : '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = [
        'rating' => (int) ($_POST['rating'] ?? 0),
        'review' => is_scalar($_POST['review'] ?? '') ? (string) ($_POST['review'] ?? '') : '',
    ];

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Your session expired. Please try again.';
    } else {
        try {
            // saveCustomerReview() re-checks the purchase itself.
            $result = saveCustomerReview($conn, $userId, $productId, $_POST['rating'] ?? '', $form['review']);
            $bounce(
                'success',
                $result === 'created'
                    ? 'Thanks for your review! It will appear on the store once it has been approved.'
                    : 'Your review was updated. It will appear on the store once it has been approved.'
            );
        } catch (InvalidArgumentException $e) {
            $error = $e->getMessage();
        } catch (Throwable $e) {
            $error = 'We couldn\'t save your review. Please try again.';
        }
    }
}

$csrfToken = generateCSRFToken();
$activeNav = 'reviews';
require __DIR__ . '/account-sidebar.php';
?>

<style>
    .rv-card {
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-soft);
        padding: 26px 28px;
    }

    .rv-product {
        margin: 0 0 4px;
        font-size: 13px;
        font-weight: 700;
        color: var(--color-text-light);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .rv-product-name {
        margin: 0 0 22px;
        font-size: 18px;
        font-weight: 800;
        color: var(--color-text);
    }

    .rv-error {
        margin: 0 0 18px;
        padding: 12px 14px;
        font-size: 13px;
        line-height: 1.5;
        color: #8a1c14;
        background: #fbeceb;
        border: 1px solid #e6b3af;
        border-radius: var(--radius-sm);
    }

    .rv-note {
        margin: 0 0 18px;
        padding: 12px 14px;
        font-size: 13px;
        line-height: 1.5;
        color: #856404;
        background: #fff3cd;
        border-radius: var(--radius-sm);
    }

    .rv-field {
        margin-bottom: 22px;
    }

    .rv-label {
        display: block;
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 700;
        color: var(--color-text);
    }

    .rv-optional {
        font-weight: 500;
        color: var(--color-text-light);
    }

    /* Star picker: radios are listed 5→1 and shown reversed, so hovering or
       checking a star also lights up every star before it. */
    .rv-stars {
        display: inline-flex;
        flex-direction: row-reverse;
        gap: 4px;
        position: relative;
    }

    .rv-stars input {
        position: absolute;
        opacity: 0;
        width: 1px;
        height: 1px;
    }

    .rv-stars label {
        font-size: 34px;
        line-height: 1;
        color: #d5d9d6;
        cursor: pointer;
        transition: color 0.1s ease;
    }

    .rv-stars input:checked~label,
    .rv-stars label:hover,
    .rv-stars label:hover~label {
        color: #f5a623;
    }

    .rv-stars input:focus-visible+label {
        outline: 2px solid var(--color-primary);
        outline-offset: 2px;
        border-radius: 4px;
    }

    .rv-textarea {
        width: 100%;
        min-height: 150px;
        padding: 12px 14px;
        font-family: inherit;
        font-size: 14px;
        line-height: 1.6;
        color: var(--color-text);
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-sm);
        box-sizing: border-box;
        resize: vertical;
    }

    .rv-textarea:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px var(--color-primary-light);
    }

    .rv-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .rv-btn {
        display: inline-block;
        padding: 10px 20px;
        font-family: inherit;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        border-radius: var(--radius-sm);
        border: 1px solid var(--color-primary);
        transition: background 0.15s ease, color 0.15s ease;
    }

    .rv-btn--primary {
        background: var(--color-primary);
        color: var(--color-white);
    }

    .rv-btn--primary:hover {
        background: var(--color-primary-dark);
        border-color: var(--color-primary-dark);
    }

    .rv-btn--ghost {
        background: var(--color-white);
        color: var(--color-primary);
    }

    .rv-btn--ghost:hover {
        background: var(--color-primary-light);
        color: var(--color-primary-dark);
    }
</style>

<h1><?= $isEdit ? 'Edit your review' : 'Write a review' ?></h1>

<div class="rv-card">
    <p class="rv-product">You bought</p>
    <h2 class="rv-product-name"><?= htmlspecialchars($product['title']) ?></h2>

    <?php if ($error): ?>
        <div class="rv-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($isEdit && $existing['status'] === 'Active'): ?>
        <div class="rv-note">Your review is currently published. If you change it, it will be checked again before it shows on the store.</div>
    <?php endif; ?>

    <form method="post" action="<?= htmlspecialchars(BASE_URL . 'account/write-review.php?product_id=' . $productId) ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <div class="rv-field">
            <span class="rv-label" id="rv-rating-label">Your rating</span>
            <div class="rv-stars" role="radiogroup" aria-labelledby="rv-rating-label">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" id="rv-star-<?= $i ?>" name="rating" value="<?= $i ?>"
                        <?= $form['rating'] === $i ? 'checked' : '' ?> <?= $i === 1 ? 'required' : '' ?>>
                    <label for="rv-star-<?= $i ?>" title="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>">★</label>
                <?php endfor; ?>
            </div>
        </div>

        <div class="rv-field">
            <label class="rv-label" for="rv-review">Your review <span class="rv-optional">(optional)</span></label>
            <textarea class="rv-textarea" id="rv-review" name="review"
                maxlength="<?= REVIEW_MAX_LENGTH() ?>"
                placeholder="What did you like or dislike? How did it work for you?"><?= htmlspecialchars($form['review']) ?></textarea>
        </div>

        <div class="rv-actions">
            <button type="submit" class="rv-btn rv-btn--primary"><?= $isEdit ? 'Update review' : 'Submit review' ?></button>
            <a class="rv-btn rv-btn--ghost" href="<?= htmlspecialchars($reviewsUrl) ?>">Cancel</a>
        </div>
    </form>
</div>

</main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
