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
    redirect(BASE_URL . 'account/login.php?redirect=account/addresses/add.php');
}

$userId = $_SESSION['customer_id'];

$errors = [];
$form = [
    'full_name' => $_SESSION['customer_name'] ?? '',
    'phone' => '',
    'address_line1' => '',
    'address_line2' => '',
    'city' => '',
    'state' => '',
    'country' => 'India',
    'pincode' => '',
];
$makeDefault = false;

// A user's first address is always their default (enforced in
// createAddress() too), so hide the checkbox and just show a note instead.
try {
    $isFirstAddress = empty(getUserAddresses($conn, $userId));
} catch (Exception $e) {
    $isFirstAddress = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = [
        'full_name' => $_POST['full_name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'address_line1' => $_POST['address_line1'] ?? '',
        'address_line2' => $_POST['address_line2'] ?? '',
        'city' => $_POST['city'] ?? '',
        'state' => $_POST['state'] ?? '',
        'country' => $_POST['country'] ?? 'India',
        'pincode' => $_POST['pincode'] ?? '',
    ];
    $makeDefault = isset($_POST['is_default']);

    try {
        createAddress($conn, $userId, $form, $makeDefault);
        redirect(BASE_URL . 'account/addresses/index.php?added=1');
    } catch (InvalidArgumentException $e) {
        $errors['general'] = $e->getMessage();
    } catch (Exception $e) {
        $errors['general'] = 'Something went wrong while saving the address.';
    }
}
?>

<style>
    .address-form-wrap {
        max-width: 480px;
        margin: 0 auto;
        font-family: sans-serif;
    }

    .address-form-wrap label {
        display: block;
        margin-top: 12px;
        margin-bottom: 4px;
        font-weight: 600;
    }

    .address-form-wrap input[type="text"] {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    .form-error {
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px 15px;
        border-radius: 4px;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .form-note {
        font-size: 0.85rem;
        color: #666;
    }

    button[type="submit"] {
        margin-top: 18px;
        padding: 10px 20px;
        font-weight: 600;
        color: #fff;
        background-color: #28a745;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
</style>

<div class="address-form-wrap">
    <p><a href="index.php">&laquo; Back to addresses</a></p>

    <h2>Add Address</h2>

    <?php if (!empty($errors['general'])): ?>
        <p class="form-error"><?= htmlspecialchars($errors['general']) ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label for="full_name">Full name</label>
        <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($form['full_name']) ?>" required>

        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($form['phone']) ?>" required>

        <label for="address_line1">Address line 1</label>
        <input type="text" id="address_line1" name="address_line1" value="<?= htmlspecialchars($form['address_line1']) ?>" required>

        <label for="address_line2">Address line 2 (optional)</label>
        <input type="text" id="address_line2" name="address_line2" value="<?= htmlspecialchars($form['address_line2']) ?>">

        <label for="city">City</label>
        <input type="text" id="city" name="city" value="<?= htmlspecialchars($form['city']) ?>" required>

        <label for="state">State</label>
        <input type="text" id="state" name="state" value="<?= htmlspecialchars($form['state']) ?>" required>

        <label for="country">Country</label>
        <input type="text" id="country" name="country" value="<?= htmlspecialchars($form['country']) ?>" required>

        <label for="pincode">Pincode</label>
        <input type="text" id="pincode" name="pincode" value="<?= htmlspecialchars($form['pincode']) ?>" required>

        <?php if ($isFirstAddress): ?>
            <p class="form-note">This will be set as your default address since it's your first one.</p>
        <?php else: ?>
            <label>
                <input type="checkbox" name="is_default" value="1" <?= $makeDefault ? 'checked' : '' ?>>
                Set as default address
            </label>
        <?php endif; ?>

        <button type="submit">Save Address</button>
    </form>
</div>