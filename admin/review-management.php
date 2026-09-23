<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../function/review.php';
require_once __DIR__ . '/../function/csrf.php';

$pageTitle = 'Review Management';
$activeNav = 'reviews';

/**
 * ---------------------------------------------------------------------
 * Actions: approve / reject / delete
 * ---------------------------------------------------------------------
 * POST-Redirect-GET back to the current listing URL (filters + page
 * preserved) with a one-shot ?msg= flag, so a page refresh never
 * repeats the action.
 */
$currentQuery = $_GET;
unset($currentQuery['msg']);
$listingUrl = 'review-management.php' . ($currentQuery ? ('?' . http_build_query($currentQuery)) : '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        redirect($listingUrl . (strpos($listingUrl, '?') !== false ? '&' : '?') . 'msg=csrf_failed');
    }

    $reviewId = (int) ($_POST['review_id'] ?? 0);
    $action = $_POST['action'];
    $msg = 'error';

    try {
        if ($action === 'approve' && $reviewId > 0) {
            updateReviewStatusForAdmin($conn, $reviewId, 'Active');
            $msg = 'approved';
        } elseif ($action === 'reject' && $reviewId > 0) {
            updateReviewStatusForAdmin($conn, $reviewId, 'Rejected');
            $msg = 'rejected';
        } elseif ($action === 'delete' && $reviewId > 0) {
            deleteReviewForAdmin($conn, $reviewId);
            $msg = 'deleted';
        }
    } catch (InvalidArgumentException $e) {
        $msg = 'error';
    } catch (Exception $e) {
        error_log('Admin review action failed: ' . $e->getMessage());
        $msg = 'error';
    }

    redirect($listingUrl . (strpos($listingUrl, '?') !== false ? '&' : '?') . 'msg=' . $msg);
}

/* -----------------------------------------------------------------------
 * Filters + listing
 * ---------------------------------------------------------------------*/

$filterStatus = isset($_GET['status']) && in_array($_GET['status'], REVIEW_STATUSES, true) ? $_GET['status'] : '';
$filterProductId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;
$filterRating = isset($_GET['rating']) ? (int) $_GET['rating'] : 0;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

$listing = ['reviews' => [], 'total' => 0, 'page' => 1, 'pages' => 1];
$statusCounts = ['Pending' => 0, 'Active' => 0, 'Rejected' => 0];
$filterProducts = [];
$loadError = '';

try {
    $listing = getReviewsForAdmin(
        $conn,
        [
            'status' => $filterStatus,
            'product_id' => $filterProductId ?: null,
            'rating' => $filterRating ?: null,
        ],
        $page,
        20
    );
    $statusCounts = getReviewStatusCounts($conn);
    $filterProducts = getProductsForReviewFilter($conn);
} catch (Exception $e) {
    error_log('Failed to load admin reviews: ' . $e->getMessage());
    $loadError = 'Reviews could not be loaded right now. Please try again shortly.';
}

$csrfToken = generateCSRFToken();

/** Build a filter URL, keeping the other current filters, changing one. */
function reviewFilterUrl($overrides)
{
    $params = array_merge([
        'status' => $_GET['status'] ?? '',
        'product_id' => $_GET['product_id'] ?? '',
        'rating' => $_GET['rating'] ?? '',
        'page' => $_GET['page'] ?? '',
    ], $overrides);
    $params = array_filter($params, function ($v) {
        return $v !== '' && $v !== null;
    });
    return 'review-management.php' . ($params ? ('?' . http_build_query($params)) : '');
}

include __DIR__ . '/include/header.php';
?>

<style>
    .rv-wrap {
        padding: 28px 32px;
    }

    .rv-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 22px;
    }

    .rv-head h1 {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
        color: var(--ink);
    }

    .rv-alert {
        margin-bottom: 18px;
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
    }

    .rv-alert--success {
        background: var(--leaf-tint);
        color: var(--leaf-dark);
    }

    .rv-alert--error {
        background: #fbeceb;
        color: #8a1c14;
    }

    .rv-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    .rv-tab {
        padding: 8px 16px;
        border-radius: 99px;
        background: var(--paper);
        border: 1px solid var(--line);
        color: var(--muted);
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
    }

    .rv-tab.active {
        background: var(--leaf-tint);
        border-color: var(--leaf);
        color: var(--leaf-dark);
    }

    .rv-filters {
        display: flex;
        gap: 12px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    .rv-filters select {
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid var(--line);
        background: var(--paper);
        color: var(--ink);
        font-size: 13px;
        font-family: inherit;
    }

    .rv-table-card {
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 14px;
        overflow: hidden;
    }

    table.rv-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
    }

    .rv-table th {
        text-align: left;
        padding: 12px 14px;
        background: var(--mist);
        color: var(--muted);
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1px solid var(--line);
    }

    .rv-table td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--line);
        color: var(--ink);
        vertical-align: top;
    }

    .rv-table tr:last-child td {
        border-bottom: none;
    }

    .rv-stars {
        color: var(--line);
        letter-spacing: 1px;
        white-space: nowrap;
    }

    .rv-stars .filled {
        color: #d9ac4f;
    }

    .rv-review-text {
        max-width: 320px;
        color: var(--muted);
        white-space: pre-line;
    }

    .rv-review-text[open] summary {
        margin-bottom: 6px;
    }

    .rv-review-text summary {
        cursor: pointer;
        color: var(--sky);
        font-weight: 600;
        list-style: none;
    }

    .rv-review-text summary::-webkit-details-marker {
        display: none;
    }

    .rv-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 700;
    }

    .rv-badge--Pending {
        background: #fdf3e3;
        color: #92650f;
    }

    .rv-badge--Active {
        background: var(--leaf-tint);
        color: var(--leaf-dark);
    }

    .rv-badge--Rejected {
        background: #fbeceb;
        color: #8a1c14;
    }

    .rv-verified {
        color: var(--leaf-dark);
        font-weight: 700;
        font-size: 12px;
    }

    .rv-unverified {
        color: var(--muted);
        font-size: 12px;
    }

    .rv-actions {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 90px;
    }

    .rv-actions form {
        margin: 0;
    }

    .rv-actions button {
        width: 100%;
        padding: 6px 10px;
        border-radius: 7px;
        border: 1px solid var(--line);
        background: var(--paper);
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }

    .rv-actions button.approve {
        color: var(--leaf-dark);
        border-color: var(--leaf);
    }

    .rv-actions button.reject {
        color: #92650f;
        border-color: #e8c98a;
    }

    .rv-actions button.delete {
        color: #8a1c14;
        border-color: #f2c6c2;
    }

    .rv-actions button:hover {
        opacity: 0.8;
    }

    .rv-empty {
        padding: 40px;
        text-align: center;
        color: var(--muted);
        font-size: 14px;
    }

    .rv-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 18px;
    }

    .rv-pagination a,
    .rv-pagination span {
        min-width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 8px;
        border-radius: 7px;
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
        color: var(--ink);
        border: 1px solid var(--line);
    }

    .rv-pagination .is-current {
        background: var(--leaf);
        border-color: var(--leaf);
        color: #fff;
    }

    .rv-pagination .is-disabled {
        opacity: 0.4;
        pointer-events: none;
    }
</style>

<div class="rv-wrap">
    <div class="rv-head">
        <h1>Review Management</h1>
    </div>

    <?php
    $msg = $_GET['msg'] ?? '';
    $msgMap = [
        'approved' => ['success', 'Review approved and is now live.'],
        'rejected' => ['success', 'Review rejected.'],
        'deleted'  => ['success', 'Review deleted.'],
        'error'    => ['error', 'That action could not be completed. Please try again.'],
        'csrf_failed' => ['error', 'Your session expired. Please try the action again.'],
    ];
    ?>
    <?php if ($msg && isset($msgMap[$msg])): ?>
        <div class="rv-alert rv-alert--<?php echo $msgMap[$msg][0]; ?>"><?php echo htmlspecialchars($msgMap[$msg][1]); ?></div>
    <?php endif; ?>

    <?php if ($loadError): ?>
        <div class="rv-alert rv-alert--error"><?php echo htmlspecialchars($loadError); ?></div>
    <?php endif; ?>

    <div class="rv-tabs">
        <a class="rv-tab <?php echo $filterStatus === '' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(reviewFilterUrl(['status' => '', 'page' => ''])); ?>">
            All (<?php echo array_sum($statusCounts); ?>)
        </a>
        <a class="rv-tab <?php echo $filterStatus === 'Pending' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(reviewFilterUrl(['status' => 'Pending', 'page' => ''])); ?>">
            Pending (<?php echo $statusCounts['Pending']; ?>)
        </a>
        <a class="rv-tab <?php echo $filterStatus === 'Active' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(reviewFilterUrl(['status' => 'Active', 'page' => ''])); ?>">
            Active (<?php echo $statusCounts['Active']; ?>)
        </a>
        <a class="rv-tab <?php echo $filterStatus === 'Rejected' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(reviewFilterUrl(['status' => 'Rejected', 'page' => ''])); ?>">
            Rejected (<?php echo $statusCounts['Rejected']; ?>)
        </a>
    </div>

    <form class="rv-filters" method="GET" action="review-management.php">
        <?php if ($filterStatus !== ''): ?>
            <input type="hidden" name="status" value="<?php echo htmlspecialchars($filterStatus); ?>">
        <?php endif; ?>

        <select name="product_id" onchange="this.form.submit()">
            <option value="">All Products</option>
            <?php foreach ($filterProducts as $p): ?>
                <option value="<?php echo (int) $p['id']; ?>" <?php echo $filterProductId === (int) $p['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($p['title']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="rating" onchange="this.form.submit()">
            <option value="">All Ratings</option>
            <?php for ($r = 5; $r >= 1; $r--): ?>
                <option value="<?php echo $r; ?>" <?php echo $filterRating === $r ? 'selected' : ''; ?>><?php echo $r; ?> Star<?php echo $r === 1 ? '' : 's'; ?></option>
            <?php endfor; ?>
        </select>
    </form>

    <div class="rv-table-card">
        <?php if ($listing['total'] > 0): ?>
            <table class="rv-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Verified</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listing['reviews'] as $review): ?>
                        <tr>
                            <td>#<?php echo (int) $review['id']; ?></td>
                            <td><?php echo htmlspecialchars($review['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($review['product_title']); ?></td>
                            <td>
                                <span class="rv-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="<?php echo $i <= $review['rating'] ? 'filled' : ''; ?>">&#9733;</span>
                                    <?php endfor; ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($review['review'])): ?>
                                    <?php if (mb_strlen($review['review']) > 90): ?>
                                        <details class="rv-review-text">
                                            <summary>View review</summary>
                                            <?php echo htmlspecialchars($review['review']); ?>
                                        </details>
                                    <?php else: ?>
                                        <span class="rv-review-text"><?php echo htmlspecialchars($review['review']); ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="rv-review-text">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($review['verified_purchase']): ?>
                                    <span class="rv-verified">&#10003; Verified</span>
                                <?php else: ?>
                                    <span class="rv-unverified">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="rv-badge rv-badge--<?php echo $review['status']; ?>"><?php echo htmlspecialchars($review['status']); ?></span></td>
                            <td><?php echo date('d M Y', strtotime($review['created_at'])); ?></td>
                            <td>
                                <div class="rv-actions">
                                    <?php if ($review['status'] === 'Pending'): ?>
                                        <form method="POST" action="review-management.php">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                            <input type="hidden" name="review_id" value="<?php echo (int) $review['id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="approve">Approve</button>
                                        </form>
                                        <form method="POST" action="review-management.php">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                            <input type="hidden" name="review_id" value="<?php echo (int) $review['id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="reject">Reject</button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" action="review-management.php" onsubmit="return confirm('Delete this review permanently?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                        <input type="hidden" name="review_id" value="<?php echo (int) $review['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="delete">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($listing['pages'] > 1): ?>
                <nav class="rv-pagination" aria-label="Review pages">
                    <a href="<?php echo htmlspecialchars(reviewFilterUrl(['page' => max(1, $listing['page'] - 1)])); ?>" class="<?php echo $listing['page'] <= 1 ? 'is-disabled' : ''; ?>">&laquo; Prev</a>
                    <?php for ($p = 1; $p <= $listing['pages']; $p++): ?>
                        <?php if ($p === $listing['page']): ?>
                            <span class="is-current"><?php echo $p; ?></span>
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars(reviewFilterUrl(['page' => $p])); ?>"><?php echo $p; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <a href="<?php echo htmlspecialchars(reviewFilterUrl(['page' => min($listing['pages'], $listing['page'] + 1)])); ?>" class="<?php echo $listing['page'] >= $listing['pages'] ? 'is-disabled' : ''; ?>">Next &raquo;</a>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="rv-empty">No reviews match these filters.</div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/include/footer.php'; ?>