<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/helper.php';
require_once __DIR__ . '/../function/csrf.php';
require_once __DIR__ . '/../function/wishlist.php';

if (!isCustomerLogin()) {
    redirect(BASE_URL . 'account/login.php?redirect=account/wishlist.php');
}

$user = getUserById($conn, $_SESSION['customer_id']);

if ($user === null) {
    logoutUser();
    redirect(BASE_URL . 'account/login.php');
}

$removedNotice = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_product_id'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['wishlist_flash'] = ['type' => 'error', 'message' => 'Your session expired. Please try again.'];
    } else {
        $productId = (int) $_POST['remove_product_id'];
        try {
            $removed = removeFromWishlist($conn, $user['id'], $productId);
            $_SESSION['wishlist_flash'] = $removed
                ? ['type' => 'success', 'message' => 'Removed from your wishlist.']
                : null;
        } catch (Exception $e) {
            $_SESSION['wishlist_flash'] = ['type' => 'error', 'message' => 'Could not remove that item. Please try again.'];
        }
    }

    // Redirect so refreshing the page can't re-submit the removal.
    redirect(BASE_URL . 'account/wishlist.php');
}

$flash = $_SESSION['wishlist_flash'] ?? null;
unset($_SESSION['wishlist_flash']);

try {
    $wishlistItems = getWishlistForCustomer($conn, $user['id']);
} catch (Exception $e) {
    $wishlistItems = [];
}

$csrfToken = generateCSRFToken();

$activeNav = 'wishlist';
require __DIR__ . '/account-sidebar.php';
?>

<style>
    .wishlist-flash {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 20px;
        padding: 12px 16px;
        font-size: 13px;
        font-weight: 600;
        border-radius: var(--acc-radius-sm);
        line-height: 1.5;
    }

    .wishlist-flash--success {
        color: var(--acc-side);
        background: rgba(47, 158, 110, 0.1);
        border: 1px solid rgba(47, 158, 110, 0.25);
    }

    .wishlist-flash--error {
        color: #8a1c14;
        background: #fbeceb;
        border: 1px solid #f2c6c2;
    }

    .wishlist-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
        gap: 18px;
    }

    .wishlist-card {
        display: flex;
        flex-direction: column;
        background: var(--acc-white);
        border: 1px solid var(--acc-border);
        border-radius: var(--acc-radius-lg);
        box-shadow: var(--acc-shadow);
        overflow: hidden;
        transition: box-shadow 0.15s ease, border-color 0.15s ease;
    }

    .wishlist-card:hover {
        box-shadow: var(--acc-shadow-hover);
        border-color: var(--acc-gold);
    }

    .wishlist-card__media {
        position: relative;
        aspect-ratio: 1 / 1;
        background: var(--acc-bg);
    }

    .wishlist-card__media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .wishlist-card__media--empty {
        display: grid;
        place-items: center;
        color: var(--acc-text-light);
    }

    .wishlist-card__media--empty svg {
        width: 34px;
        height: 34px;
    }

    .wishlist-card__remove {
        position: absolute;
        top: 8px;
        right: 8px;
        display: grid;
        place-items: center;
        width: 30px;
        height: 30px;
        border: none;
        border-radius: 50%;
        background: rgba(28, 41, 37, 0.55);
        color: #fff;
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .wishlist-card__remove:hover {
        background: #b3261e;
    }

    .wishlist-card__remove svg {
        width: 15px;
        height: 15px;
    }

    .wishlist-card__badge {
        position: absolute;
        top: 8px;
        left: 8px;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        background: rgba(28, 41, 37, 0.72);
        color: #fff;
    }

    .wishlist-card__body {
        display: flex;
        flex-direction: column;
        flex: 1;
        padding: 14px 16px 16px;
    }

    .wishlist-card__title {
        margin: 0 0 6px;
        font-size: 14.5px;
        font-weight: 700;
        color: var(--acc-text);
        text-decoration: none;
        line-height: 1.35;
    }

    .wishlist-card__title:hover {
        color: var(--acc-side);
        text-decoration: underline;
    }

    .wishlist-card__price {
        margin: 0 0 14px;
        font-size: 15px;
        font-weight: 800;
        color: var(--acc-text);
    }

    .wishlist-card__price s {
        margin-left: 6px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--acc-text-light);
    }

    .wishlist-card__actions {
        margin-top: auto;
        display: flex;
        gap: 8px;
    }

    .wishlist-card__view {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 9px 10px;
        border-radius: var(--acc-radius-sm);
        background: var(--acc-side);
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        text-align: center;
        transition: background 0.15s ease;
    }

    .wishlist-card__view:hover {
        background: var(--acc-side-deep);
    }

    .wishlist-card__view--disabled {
        background: var(--acc-border);
        color: var(--acc-text-light);
        pointer-events: none;
    }

    .wishlist-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 12px;
        padding: 48px 20px;
        background: var(--acc-white);
        border: 1px dashed var(--acc-border);
        border-radius: var(--acc-radius-lg);
    }

    .wishlist-empty-state__icon {
        display: grid;
        place-items: center;
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: rgba(23, 72, 61, 0.08);
        color: var(--acc-side);
    }

    .wishlist-empty-state__icon svg {
        width: 24px;
        height: 24px;
    }

    .wishlist-empty-state p {
        margin: 0;
        font-size: 14px;
        color: var(--acc-text-light);
    }

    .wishlist-empty-state a {
        margin-top: 4px;
        display: inline-block;
        padding: 9px 20px;
        border-radius: 999px;
        background: var(--acc-side);
        color: #fff;
        font-size: 13.5px;
        font-weight: 700;
        text-decoration: none;
    }

    .wishlist-empty-state a:hover {
        background: var(--acc-side-deep);
    }
</style>

<h1>My Wishlist</h1>

<?php if ($flash): ?>
    <div class="wishlist-flash wishlist-flash--<?= htmlspecialchars($flash['type']) ?>">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<?php if (empty($wishlistItems)): ?>
    <div class="wishlist-empty-state">
        <span class="wishlist-empty-state__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 8.6c0-3-2.3-5.4-5.2-5.4-1.8 0-3.4.9-4.4 2.4C10.2 4.1 8.6 3.2 6.8 3.2c-2.9 0-5.2 2.4-5.2 5.4 0 6.1 8.6 11 8.6 11s8.6-4.9 8.6-11Z"/></svg>
        </span>
        <p>Nothing here yet — tap the heart on any product to save it for later.</p>
        <a href="<?= htmlspecialchars(BASE_URL) ?>">Browse products</a>
    </div>
<?php else: ?>
    <div class="wishlist-grid">
        <?php foreach ($wishlistItems as $item): ?>
            <div class="wishlist-card">
                <div class="wishlist-card__media<?= $item['image_url'] === null ? ' wishlist-card__media--empty' : '' ?>">
                    <?php if (!$item['in_stock']): ?>
                        <span class="wishlist-card__badge">Out of stock</span>
                    <?php endif; ?>

                    <form method="post" action="<?= htmlspecialchars(BASE_URL . 'account/wishlist.php') ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="remove_product_id" value="<?= (int) $item['product_id'] ?>">
                        <button type="submit" class="wishlist-card__remove" title="Remove from wishlist" aria-label="Remove from wishlist">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </form>

                    <?php if ($item['image_url'] !== null): ?>
                        <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" loading="lazy">
                    <?php else: ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-5-5L5 21"/></svg>
                    <?php endif; ?>
                </div>

                <div class="wishlist-card__body">
                    <a class="wishlist-card__title" href="<?= htmlspecialchars($item['url']) ?>"><?= htmlspecialchars($item['title']) ?></a>

                    <p class="wishlist-card__price">
                        <?= $item['has_variants'] ? 'From ' : '' ?>&#8377;<?= number_format($item['price'], 2) ?>
                        <?php if ($item['compare_at_price'] !== null): ?>
                            <s>&#8377;<?= number_format($item['compare_at_price'], 2) ?></s>
                        <?php endif; ?>
                    </p>

                    <div class="wishlist-card__actions">
                        <a class="wishlist-card__view<?= $item['in_stock'] ? '' : ' wishlist-card__view--disabled' ?>" href="<?= htmlspecialchars($item['url']) ?>">
                            <?= $item['in_stock'] ? 'View Product' : 'Unavailable' ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
