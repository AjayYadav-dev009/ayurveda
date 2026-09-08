<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../function/customer-auth.php';
require_once __DIR__ . '/../../function/address.php';
require_once __DIR__ . '/../../function/helper.php';

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
?>

<style>
    .addresses-wrap {
        max-width: 640px;
        margin: 0 auto;
        font-family: sans-serif;
    }

    .addresses-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 15px;
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

    .btn-add { background-color: #28a745; }
    .btn-edit { background-color: #ffc107; color: #212529; }
    .btn-delete { background-color: #dc3545; }
    .btn-star { background-color: #007bff; }
    .btn-sm { padding: 5px 10px; font-size: 0.8rem; margin-right: 4px; }

    .form-success {
        background-color: #d4edda;
        color: #155724;
        padding: 10px 15px;
        border-radius: 4px;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .form-error {
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px 15px;
        border-radius: 4px;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .address-card {
        border: 1px solid #e2e2e2;
        border-radius: 6px;
        padding: 14px 16px;
        margin-bottom: 12px;
        position: relative;
    }

    .address-card p {
        margin: 2px 0;
    }

    .default-badge {
        display: inline-block;
        background-color: #d4edda;
        color: #155724;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 10px;
        margin-bottom: 6px;
    }

    .address-actions {
        margin-top: 10px;
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
        color: #777;
    }
</style>

<div class="addresses-wrap">
    <div class="addresses-header">
        <h2>My Addresses</h2>
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
                    <a href="edit.php?id=<?= (int) $address['id'] ?>" class="btn btn-edit btn-sm">Edit</a>

                    <?php if ((int) $address['is_default'] !== 1): ?>
                        <form method="POST" action="index.php">
                            <input type="hidden" name="action" value="set_default">
                            <input type="hidden" name="id" value="<?= (int) $address['id'] ?>">
                            <button type="submit" class="btn btn-star btn-sm">Set as Default</button>
                        </form>
                    <?php endif; ?>

                    <form method="POST" action="delete.php" onsubmit="return confirm('Delete this address?');">
                        <input type="hidden" name="id" value="<?= (int) $address['id'] ?>">
                        <button type="submit" class="btn btn-delete btn-sm">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>