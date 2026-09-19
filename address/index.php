<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/address.php';
require_once __DIR__ . '/../function/helper.php';

if (!isCustomerLogin()) {
    redirect(BASE_URL . 'account/login.php?redirect=account/addresses/index.php');
}

$userId = $_SESSION['customer_id'];

$errors = [];

// "Set as default" posts back to this same page rather than a separate
// file, since the address list here is the natural home for that action.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'set_default') {
    $addressId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    try {
        setDefaultAddress($conn, $addressId, $userId);
        redirect(BASE_URL . 'account/addresses/index.php?default_set=1');
    } catch (InvalidArgumentException $e) {
        $errors['general'] = $e->getMessage();
    } catch (Exception $e) {
        $errors['general'] = 'Something went wrong while updating your default address.';
    }
}

try {
    $addresses = getUserAddresses($conn, $userId);
} catch (Exception $e) {
    $addresses = [];
    $errors['general'] = 'Something went wrong while loading your addresses.';
}

$deleted = isset($_GET['deleted']);
$added = isset($_GET['added']);
$updated = isset($_GET['updated']);
$defaultSet = isset($_GET['default_set']);

$activeNav = 'addresses';

include __DIR__ . '/../includes/header.php';
?>

<style>
    .addresses-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .btn {
        display: inline-block;
        padding: 9px 18px;
        font-size: 13px;
        font-weight: 700;
        font-family: inherit;
        text-align: center;
        text-decoration: none;
        border: none;
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }

    .btn-add {
        color: var(--color-white);
        background: var(--color-primary);
    }

    .btn-add:hover {
        background: var(--color-primary-dark);
    }

    .btn-outline {
        color: var(--color-primary-dark);
        background: transparent;
        border: 1px solid var(--color-border);
    }

    .btn-outline:hover {
        background: var(--color-primary-light);
        border-color: var(--color-primary);
    }

    .btn-danger {
        color: #b3261e;
        background: transparent;
        border: 1px solid var(--color-border);
    }

    .btn-danger:hover {
        background: #fbeceb;
        border-color: #f2c6c2;
        color: #8a1c14;
    }

    .btn-sm {
        padding: 7px 14px;
        font-size: 12px;
        margin-right: 6px;
    }

    .form-success {
        margin: 0 0 16px;
        padding: 12px 14px;
        font-size: 13px;
        color: var(--color-primary-dark);
        background: var(--color-primary-light);
        border: 1px solid var(--color-primary);
        border-radius: var(--radius-sm);
        line-height: 1.5;
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

    .address-card {
        position: relative;
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        padding: 16px 20px;
        margin-bottom: 14px;
        transition: box-shadow 0.15s ease, border-color 0.15s ease;
    }

    .address-card:hover {
        box-shadow: var(--shadow-soft);
        border-color: var(--color-primary);
    }

    .address-card p {
        margin: 2px 0;
        font-size: 14px;
        color: var(--color-text);
    }

    .default-badge {
        display: inline-block;
        padding: 3px 10px;
        margin-bottom: 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
        background: var(--color-primary-light);
        color: var(--color-primary-dark);
    }

    .address-actions {
        margin-top: 12px;
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .address-actions form {
        display: inline;
    }

    .empty-state {
        padding: 30px;
        text-align: center;
        font-size: 14px;
        color: var(--color-text-light);
    }
</style>

<div class="addresses-header">
    <h1 style="margin: 0;">My Addresses</h1>
    <a href="add.php" class="btn btn-add">+ Add Address</a>
</div>

<?php if ($added): ?>
    <p class="form-success">Address added successfully.</p>
<?php endif; ?>
<?php if ($updated): ?>
    <p class="form-success">Address updated successfully.</p>
<?php endif; ?>
<?php if ($deleted): ?>
    <p class="form-success">Address deleted successfully.</p>
<?php endif; ?>
<?php if ($defaultSet): ?>
    <p class="form-success">Default address updated.</p>
<?php endif; ?>
<?php if (!empty($errors['general'])): ?>
    <p class="form-error"><?= htmlspecialchars($errors['general']) ?></p>
<?php endif; ?>

<?php if (empty($addresses) && empty($errors)): ?>
    <p class="empty-state">You haven't saved any addresses yet. You'll need at least one before you can check out.</p>
<?php else: ?>
    <?php foreach ($addresses as $address): ?>
        <div class="address-card">
            <?php if ((int) $address['is_default'] === 1): ?>
                <span class="default-badge">Default</span>
            <?php endif; ?>
            <p><strong><?= htmlspecialchars($address['full_name']) ?></strong> &middot; <?= htmlspecialchars($address['phone']) ?></p>
            <p><?= htmlspecialchars($address['address_line1']) ?></p>
            <?php if (!empty($address['address_line2'])): ?>
                <p><?= htmlspecialchars($address['address_line2']) ?></p>
            <?php endif; ?>
            <p><?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['state']) ?> <?= htmlspecialchars($address['pincode']) ?></p>
            <p><?= htmlspecialchars($address['country']) ?></p>

            <div class="address-actions">
                <a href="edit.php?id=<?= (int) $address['id'] ?>" class="btn btn-outline btn-sm">Edit</a>

                <?php if ((int) $address['is_default'] !== 1): ?>
                    <form method="POST" action="index.php">
                        <input type="hidden" name="action" value="set_default">
                        <input type="hidden" name="id" value="<?= (int) $address['id'] ?>">
                        <button type="submit" class="btn btn-outline btn-sm">Set as Default</button>
                    </form>
                <?php endif; ?>

                <form method="POST" action="delete.php" onsubmit="return confirm('Delete this address?');">
                    <input type="hidden" name="id" value="<?= (int) $address['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>