<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../function/category.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = $_POST['id'] ?? null;

if (!$id || !is_numeric($id)) {
    header('Location: index.php');
    exit;
}

try {

    deleteCategory($conn, (int)$id);

    header('Location: index.php');
    exit;

} catch (Exception $e) {

    echo "Error: " . htmlspecialchars($e->getMessage());
}