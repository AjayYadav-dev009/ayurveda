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
<h1>Add Address</h1>

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

</main>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>