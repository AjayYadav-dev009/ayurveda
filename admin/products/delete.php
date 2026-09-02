<?php require_once __DIR__ . '/../../config/database.php'; ?>
<?php require_once __DIR__ . '/index.php'; ?>

<?php

/**
 * Thrown when a product delete is blocked pending confirmation because
 * the product has order history. Deleting is still safe (past orders
 * keep their own snapshot of product_name/sku/price in order_items, and
 * order_items.product_id is set to NULL automatically), but the admin
 * should be told the live product link will be gone.
 */
class ProductHasOrderHistoryException extends InvalidArgumentException
{
    public int $orderItemCount;

    public function __construct(int $orderItemCount, string $message)
    {
        parent::__construct($message);
        $this->orderItemCount = $orderItemCount;
    }
}

/**
 * Delete a product.
 *
 * product_details, product_images, product_variants, reviews, wishlist,
 * and cart rows are removed automatically (ON DELETE CASCADE). Past
 * order_items rows are kept for order history but have their
 * product_id set to NULL (ON DELETE SET NULL) -- they already store
 * their own product_name/sku/price snapshot, so historical orders
 * remain intact and readable, they just lose the clickable link back
 * to this product.
 *
 * If the product appears in any past orders, confirmation is required
 * first (same two-step pattern as categories):
 *
 *   1. Call with $force = false (default). If the product has order
 *      history, nothing is deleted -- a ProductHasOrderHistoryException
 *      is thrown so the caller can warn the user and ask to confirm.
 *   2. Call again with $force = true to actually delete.
 *
 * A product with no order history is deleted immediately regardless
 * of $force.
 *
 * @param mysqli $conn
 * @param int $id
 * @param bool $force
 * @return bool
 * @throws Exception
 * @throws InvalidArgumentException
 * @throws ProductHasOrderHistoryException
 */
function deleteProduct($conn, $id, $force = false)
{
    $id = (int) $id;

    $existing = getProductById($conn, $id);
    $productRow = mysqli_fetch_assoc($existing);
    if (!$productRow) {
        throw new InvalidArgumentException('Product not found.');
    }

    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS cnt FROM order_items WHERE product_id = ?");
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error checking order history: " . mysqli_error($conn));
    }
    $result = mysqli_stmt_get_result($stmt);
    $orderItemCount = (int) mysqli_fetch_assoc($result)['cnt'];

    if ($orderItemCount > 0 && !$force) {
        throw new ProductHasOrderHistoryException(
            $orderItemCount,
            'This product appears in ' . $orderItemCount . ' past order item' . ($orderItemCount === 1 ? '' : 's') .
                '. Those orders will keep their own record of the product name, price and SKU, but the live ' .
                'link back to this product will be removed and it will disappear from the store. Do you still want to delete it?'
        );
    }

    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error deleting product: ' . mysqli_error($conn));
    }

    if (mysqli_stmt_affected_rows($stmt) === 0) {
        throw new InvalidArgumentException('Product not found.');
    }

    return true;
}