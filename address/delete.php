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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'account/addresses/index.php');
}

$userId = $_SESSION['customer_id'];
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($id <= 0) {
    die('Invalid address id.');
}

try {
    deleteAddress($conn, $id, $userId);
    redirect(BASE_URL . 'account/addresses/index.php?deleted=1');
} catch (InvalidArgumentException $e) {
    die(htmlspecialchars($e->getMessage()));
} catch (Exception $e) {
    die('Something went wrong while deleting the address.');
}