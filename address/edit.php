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

$activeNav = 'addresses';
require __DIR__ . '/../account-sidebar.php';
?>

<style>
    .address-form {
        max-width: 480px;
    }

    .address-form label {
        display: block;
        margin-top: 14px;
        margin-bottom: 6px;
        font-size: 13px;
        font-weight: 600;
        color: var(--color-text);
    }

    .address-form label:first-of-type {
        margin-top: 0;
    }

    .address-form input[type="text"] {
        width: 100%;
        padding: 11px 14px;
        font-size: 14px;
        font-family: inherit;
        color: var(--color-text);
        background: var(--color-bg);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-sm);
        box-sizing: border-box;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .address-form input[type="text"]:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px var(--color-primary-light);
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

    .form-note {
        margin-top: 10px;
        font-size: 13px;
        color: var(--color-text-light);
    }

    .back-link {
        display: inline-block;
        margin-bottom: 16px;
        font-size: 13px;
        font-weight: 600;
        color: var(--color-primary);
    }

    .back-link:hover {
        color: var(--color-primary-dark);
        text-decoration: underline;
    }

    .address-form button[type="submit"] {
        margin-top: 20px;
        padding: 12px 24px;
        font-size: 14px;
        font-weight: 700;
        font-family: inherit;
        color: var(--color-white);
        background: var(--color-primary);
        border: none;
        border-radius: var(--radius-md);
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .address-form button[type="submit"]:hover {
        background: var(--color-primary-dark);
    }
</style>

<a href="index.php" class="back-link">&laquo; Back to addresses</a>
<h1>Edit Address</h1>

<?php if (!empty($errors['general'])): ?>
    <p class="form-error"><?= htmlspecialchars($errors['general']) ?></p>
<?php endif; ?>

<form class="address-form" method="post" action="">
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

</main>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>