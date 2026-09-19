<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/helper.php';
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

$flash = $_SESSION['review_flash'] ?? null;
unset($_SESSION['review_flash']);

// Only products this customer has actually bought (delivered orders) show up
// here — that is what makes them reviewable.
$loadFailed = false;
try {
    $items = getReviewableProducts($conn, (int) $_SESSION['customer_id']);
} catch (Throwable $e) {
    $items = [];
    $loadFailed = true;
}

$statusLabels = [
    'Pending'  => ['Awaiting approval', 'pending'],
    'Active'   => ['Published', 'active'],
    'Rejected' => ['Not approved', 'rejected'],
];

$activeNav = 'reviews';
require __DIR__ . '/account-sidebar.php';
?>

<style>
    .rv-flash {
        margin: 0 0 20px;
        padding: 12px 14px;
        font-size: 13px;
        line-height: 1.5;
        border-radius: var(--radius-sm);
        border: 1px solid transparent;
    }

    .rv-flash--success {
        color: var(--color-primary-dark);
        background: var(--color-primary-light);
        border-color: var(--color-primary);
    }

    .rv-flash--error {
        color: #8a1c14;
        background: #fbeceb;
        border-color: #e6b3af;
    }

    .rv-intro {
        margin: -8px 0 20px;
        font-size: 14px;
        color: var(--color-text-light);
        line-height: 1.6;
    }

    .rv-list {
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-soft);
        padding: 8px 28px;
    }

    .rv-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        padding: 20px 0;
        border-bottom: 1px solid var(--color-border);
    }

    .rv-item:last-child {
        border-bottom: none;
    }

    .rv-item__main {
        min-width: 0;
        flex: 1 1 auto;
    }

    .rv-item__title {
        margin: 0 0 4px;
        font-size: 15px;
        font-weight: 700;
        color: var(--color-text);
    }

    .rv-item__meta {
        font-size: 12.5px;
        color: var(--color-text-light);
    }

    .rv-item__review {
        margin-top: 12px;
    }

    .rv-stars-static {
        font-size: 17px;
        letter-spacing: 2px;
        color: #f5a623;
    }

    .rv-stars-static .is-empty {
        color: #d5d9d6;
    }

    .rv-item__text {
        margin: 6px 0 0;
        font-size: 14px;
        line-height: 1.6;
        color: var(--color-text);
        white-space: pre-line;
        word-break: break-word;
    }

    .rv-badge {
        display: inline-block;
        margin-left: 8px;
        padding: 2px 9px;
        border-radius: 12px;
        font-size: 11.5px;
        font-weight: 700;
        vertical-align: middle;
    }

    .rv-badge--active {
        background: var(--color-primary-light);
        color: var(--color-primary-dark);
    }

    .rv-badge--pending {
        background: #fff3cd;
        color: #856404;
    }

    .rv-badge--rejected {
        background: #fbeceb;
        color: #8a1c14;
    }

    .rv-btn {
        flex-shrink: 0;
        display: inline-block;
        padding: 9px 16px;
        font-family: inherit;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
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

    .rv-empty {
        text-align: center;
        padding: 44px 20px;
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-soft);
    }

    .rv-empty h2 {
        margin: 0 0 8px;
        font-size: 16px;
        font-weight: 800;
        color: var(--color-text);
    }

    .rv-empty p {
        margin: 0 0 18px;
        font-size: 14px;
        line-height: 1.6;
        color: var(--color-text-light);
    }

    @media (max-width: 600px) {
        .rv-list {
            padding: 4px 18px;
        }

        .rv-item {
            flex-direction: column;
            gap: 14px;
        }
    }
</style>

<h1>My Reviews</h1>

<?php if ($flash): ?>
    <div class="rv-flash rv-flash--<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<?php if ($loadFailed): ?>
    <div class="rv-flash rv-flash--error">We couldn't load your purchases right now. Please try again in a moment.</div>
<?php elseif (empty($items)): ?>
    <div class="rv-empty">
        <h2>Nothing to review yet</h2>
        <p>You can review a product once your order for it has been delivered.<br>
            Products you've received will show up here.</p>
        <a class="rv-btn rv-btn--primary" href="<?= htmlspecialchars(BASE_URL . 'account/my-orders.php') ?>">View my orders</a>
    </div>
<?php else: ?>
    <p class="rv-intro">Share your experience with products you've received. Reviews are checked before they appear on the store.</p>

    <div class="rv-list">
        <?php foreach ($items as $item):
            $hasReview = $item['review_id'] !== null;
            $rating    = $hasReview ? (int) $item['rating'] : 0;
            $label     = $hasReview ? ($statusLabels[$item['review_status']] ?? [$item['review_status'], 'pending']) : null;
            $writeUrl  = BASE_URL . 'account/write-review.php?product_id=' . (int) $item['id'];
        ?>
            <div class="rv-item">
                <div class="rv-item__main">
                    <h2 class="rv-item__title"><?= htmlspecialchars($item['title']) ?></h2>
                    <div class="rv-item__meta">Purchased <?= htmlspecialchars(date('d M Y', strtotime($item['last_purchased']))) ?></div>

                    <?php if ($hasReview): ?>
                        <div class="rv-item__review">
                            <span class="rv-stars-static" aria-label="<?= $rating ?> out of 5 stars">
                                <?= str_repeat('★', $rating) ?><span class="is-empty"><?= str_repeat('★', 5 - $rating) ?></span>
                            </span>
                            <span class="rv-badge rv-badge--<?= htmlspecialchars($label[1]) ?>"><?= htmlspecialchars($label[0]) ?></span>
                            <?php if ($item['review'] !== null && $item['review'] !== ''): ?>
                                <p class="rv-item__text"><?= htmlspecialchars($item['review']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <a class="rv-btn <?= $hasReview ? 'rv-btn--ghost' : 'rv-btn--primary' ?>" href="<?= htmlspecialchars($writeUrl) ?>">
                    <?= $hasReview ? 'Edit review' : 'Write a review' ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
