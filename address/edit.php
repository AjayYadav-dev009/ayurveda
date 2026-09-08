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
    redirect(BASE_URL . 'account/login.php?redirect=account/addresses/edit.php');
}

$userId = $_SESSION['customer_id'];

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    die('Invalid address id.');
}

$address = getAddressById($conn, $id, $userId);
if ($address === null) {
    die('Address not found.');
}

$errors = [];
$form = [
    'full_name' => $address['full_name'],
    'phone' => $address['phone'],
    'address_line1' => $address['address_line1'],
    'address_line2' => $address['address_line2'] ?? '',
    'city' => $address['city'],
    'state' => $address['state'],
    'country' => $address['country'],
    'pincode' => $address['pincode'],
];
$isDefault = (int) $address['is_default'] === 1;

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
    $isDefault = isset($_POST['is_default']);

    try {
        updateAddress($conn, $id, $userId, $form, $isDefault);
        redirect(BASE_URL . 'account/addresses/index.php?updated=1');
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

    <h2>Edit Address</h2>

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
        <input type="text" id="address_line2" name="address_line2" value="<?= htmlspecialchars($form['address_line2'] ?? '') ?>">

        <label for="city">City</label>
        <input type="text" id="city" name="city" value="<?= htmlspecialchars($form['city']) ?>" required>

        <label for="state">State</label>
        <input type="text" id="state" name="state" value="<?= htmlspecialchars($form['state']) ?>" required>

        <label for="country">Country</label>
        <input type="text" id="country" name="country" value="<?= htmlspecialchars($form['country']) ?>" required>

        <label for="pincode">Pincode</label>
        <input type="text" id="pincode" name="pincode" value="<?= htmlspecialchars($form['pincode']) ?>" required>

        <?php if ((int) $address['is_default'] === 1): ?>
            <p class="form-note">This is your default address. Since it's your only or current default, it will stay the default when you save.</p>
        <?php else: ?>
            <label>
                <input type="checkbox" name="is_default" value="1" <?= $isDefault ? 'checked' : '' ?>>
                Set as default address
            </label>
        <?php endif; ?>

        <button type="submit">Save Changes</button>
    </form>
</div>