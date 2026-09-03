<?php include __DIR__ . '/../../function/product.php'; ?>
<?php include __DIR__ . '/../../function/category.php'; ?>
<?php include __DIR__ . '/../../function/helper.php'; ?>
<?php require_once __DIR__ . '/../../config/database.php'; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$force = isset($_POST['force']) && $_POST['force'] === '1';

if ($id <= 0) {
    die('Invalid product id.');
}

try {
    deleteProduct($conn, $id, $force);
    header('Location: index.php?deleted=1');
    exit;
} catch (ProductHasOrderHistoryException $e) {
    // Needs confirmation: show the impact and ask the admin to confirm
    // before deleting again with force = true.
    ?>
    <p><?php echo htmlspecialchars($e->getMessage()); ?></p>

    <form method="POST" action="delete.php" style="display:inline;">
        <input type="hidden" name="id" value="<?php echo (int) $id; ?>">
        <input type="hidden" name="force" value="1">
        <button type="submit">Yes, delete anyway</button>
    </form>

    <a href="index.php">Cancel</a>
    <?php
} catch (InvalidArgumentException $e) {
    ?>
    <p><?php echo htmlspecialchars($e->getMessage()); ?></p>
    <a href="index.php">Back to products</a>
    <?php
} catch (Exception $e) {
    ?>
    <p>Something went wrong while deleting the product.</p>
    <a href="index.php">Back to products</a>
    <?php
}