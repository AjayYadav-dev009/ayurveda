<?php
$pageTitle = 'Delete Product';
$activeNav = 'products';
?>
<?php include __DIR__ . '/../../function/product.php'; ?>
<?php include __DIR__ . '/../../function/helper.php'; ?>
<?php include __DIR__ . '/../../includes/auth.php'; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$force = isset($_POST['force']) && $_POST['force'] === '1';

if ($id <= 0) {
    die('Invalid product id.');
}

$needsConfirm = false;
$confirmMessage = '';
$genericError = false;

try {
    deleteProduct($conn, $id, $force);
    redirect('index.php?deleted=1');
} catch (ProductHasOrderHistoryException $e) {
    $needsConfirm = true;
    $confirmMessage = $e->getMessage();
} catch (InvalidArgumentException $e) {
    $confirmMessage = $e->getMessage();
} catch (Exception $e) {
    $genericError = true;
}
?>
<?php include __DIR__ . '/../include/header.php'; ?>

<style>
    .padmin { max-width: 900px; margin: 0 auto; padding: 28px; }
    .padmin * { box-sizing: border-box; }
    .padmin .btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; font-size: 0.9rem; font-weight: 700; border-radius: 10px; border: 1px solid transparent; cursor: pointer; text-decoration: none; }
    .padmin .btn-secondary { background: #fff; color: var(--ink); border-color: var(--line); }
    .padmin .btn-secondary:hover { background: var(--mist); }
    .padmin .btn-danger { background: #b3382c; color: #fff; }
    .padmin .btn-danger:hover { background: #8f2c22; }
    .padmin .alert { padding: 12px 16px; border-radius: 10px; font-size: 0.88rem; margin-bottom: 16px; border: 1px solid transparent; }
    .padmin .alert-error { background: #fbeae7; border-color: #f0c2ba; color: #8f2c22; }
    .padmin .card-hint { font-size: 0.86rem; color: var(--muted); }
    .padmin .confirm-box { max-width: 520px; margin: 40px auto; background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 26px 28px; }
    .padmin .confirm-box h2 { margin-top: 0; }
    .padmin .confirm-actions { display: flex; gap: 10px; margin-top: 20px; }
</style>

<div class="padmin">
    <div class="confirm-box">
        <?php if ($needsConfirm) : ?>
            <h2>This product has order history</h2>
            <div class="alert alert-error"><?php echo htmlspecialchars($confirmMessage); ?></div>
            <p class="card-hint">Deleting it will remove the product listing, but past orders that reference it will keep their own record of what was purchased.</p>

            <div class="confirm-actions">
                <form method="POST" action="delete.php">
                    <input type="hidden" name="id" value="<?php echo (int) $id; ?>">
                    <input type="hidden" name="force" value="1">
                    <button type="submit" class="btn btn-danger">Yes, delete anyway</button>
                </form>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        <?php elseif ($genericError) : ?>
            <h2>Something went wrong</h2>
            <div class="alert alert-error">The product could not be deleted. Please try again.</div>
            <div class="confirm-actions">
                <a href="index.php" class="btn btn-secondary">Back to products</a>
            </div>
        <?php else : ?>
            <h2>Can't delete this product</h2>
            <div class="alert alert-error"><?php echo htmlspecialchars($confirmMessage); ?></div>
            <div class="confirm-actions">
                <a href="index.php" class="btn btn-secondary">Back to products</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>