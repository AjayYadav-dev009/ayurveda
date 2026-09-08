<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/product.php';

/**
 * =============================================================================
 * Cart Functions
 * =============================================================================
 * Table: cart
 *   id          bigint UNSIGNED PK
 *   user_id     bigint UNSIGNED  (FK -> users.id, ON DELETE CASCADE)
 *   product_id  bigint UNSIGNED  (FK -> products.id, ON DELETE CASCADE)
 *   variant_id  bigint UNSIGNED  nullable (FK -> product_variants.id, ON DELETE CASCADE)
 *   quantity    int UNSIGNED     default 1
 *   created_at, updated_at
 *   UNIQUE (user_id, product_id, variant_id)
 *
 * -----------------------------------------------------------------------------
 * Stock rule enforced everywhere in this file (never skipped):
 *
 *   requested quantity -> look up CURRENT stock from the DB right now
 *                       -> compare
 *                       -> allow or reject
 *
 * $_POST['quantity'] (or any other client-supplied number) is NEVER trusted
 * as the truth about what's in stock, and it is NEVER used to compute
 * remaining stock — the current stock is always re-read from `products`
 * (simple products) or `product_variants` (variant products) at the moment
 * of the add/update, so a stale page or a tampered request can't oversell
 * an item. See requires products.stock — run migration_add_products_stock.sql
 * first if you haven't already.
 * -----------------------------------------------------------------------------
 */

/**
 * Thrown when a requested quantity exceeds what's actually available.
 * Carries the real available stock so the caller can show
 * "Only 3 left in stock" instead of a generic error.
 */
class InsufficientStockException extends InvalidArgumentException
{
    /** @var int */
    public $available;

    public function __construct($message, $available)
    {
        parent::__construct($message);
        $this->available = $available;
    }
}

/**
 * Resolve and validate a product/variant combination, returning everything
 * needed to price it and check stock. This is the one place that decides
 * "is this product+variant combo currently purchasable, and how much of it
 * is left" — every function below that touches stock goes through it
 * rather than re-deriving the rules itself.
 *
 * @param mysqli $conn
 * @param int $productId
 * @param int|null $variantId
 * @return array{product: array, variant: array|null, available_stock: int, unit_price: float}
 * @throws InvalidArgumentException If the product/variant doesn't exist, isn't
 *         purchasable (wrong status), or the variant doesn't belong to the product.
 * @throws Exception On a database error.
 */
function resolveCartLineContext($conn, $productId, $variantId)
{
    // getProductById() returns a raw mysqli_result (not a fetched array) —
    // that's the existing contract throughout function/product.php, so
    // fetch it here the same way its other callers do.
    $productResult = getProductById($conn, $productId);
    $product = mysqli_fetch_assoc($productResult);
    if (!$product) {
        throw new InvalidArgumentException('This product is no longer available.');
    }
    if ($product['status'] !== 'Active') {
        throw new InvalidArgumentException('This product is not currently available for purchase.');
    }

    $hasVariants = (int) $product['has_variants'] === 1;

    if ($hasVariants) {
        if ($variantId === null) {
            throw new InvalidArgumentException('Please select an option for this product.');
        }

        $variant = getProductVariantById($conn, $variantId);
        if ($variant === null || (int) $variant['product_id'] !== (int) $productId) {
            throw new InvalidArgumentException('The selected option is no longer available.');
        }
        if ($variant['status'] !== 'Active') {
            throw new InvalidArgumentException('The selected option is not currently available.');
        }

        return [
            'product' => $product,
            'variant' => $variant,
            'available_stock' => (int) $variant['stock'],
            'unit_price' => $variant['sale_price'] !== null ? (float) $variant['sale_price'] : (float) $variant['price'],
        ];
    }

    // Simple product — a variant id should never be supplied for it.
    if ($variantId !== null) {
        throw new InvalidArgumentException('Invalid product option.');
    }

    return [
        'product' => $product,
        'variant' => null,
        'available_stock' => (int) ($product['stock'] ?? 0),
        'unit_price' => $product['base_sale_price'] !== null ? (float) $product['base_sale_price'] : (float) $product['base_price'],
    ];
}

/**
 * Get every item in a user's cart, with product/variant display data and
 * live stock joined in — not just the raw cart rows. Each item also
 * reports whether it's still purchasable at its current quantity, since
 * stock (or a product's status) can change after something was added.
 *
 * @param mysqli $conn
 * @param int $userId
 * @return array<int, array> Each item: cart row + title, image, unit_price,
 *         line_total, available_stock, is_valid, issue (string|null)
 * @throws Exception
 */
function getCart($conn, $userId)
{
    $userId = (int) $userId;

    $sql = "SELECT c.id, c.user_id, c.product_id, c.variant_id, c.quantity, c.created_at, c.updated_at,
                   p.title, p.slug, p.status AS product_status, p.has_variants,
                   p.base_price, p.base_sale_price, p.stock AS product_stock,
                   pv.variant_name, pv.price AS variant_price, pv.sale_price AS variant_sale_price,
                   pv.stock AS variant_stock, pv.status AS variant_status,
                   (SELECT pi.image FROM product_images pi
                     WHERE pi.product_id = p.id
                     ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC LIMIT 1) AS image
            FROM cart c
            INNER JOIN products p ON p.id = c.product_id
            LEFT JOIN product_variants pv ON pv.id = c.variant_id
            WHERE c.user_id = ?
            ORDER BY c.id DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . mysqli_error($conn));
    }
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $items = [];
    foreach ($rows as $row) {
        $isVariant = $row['variant_id'] !== null;

        if ($isVariant) {
            $displayName = $row['title'] . ' - ' . $row['variant_name'];
            $unitPrice = $row['variant_sale_price'] !== null ? (float) $row['variant_sale_price'] : (float) $row['variant_price'];
            $availableStock = (int) $row['variant_stock'];
            $purchasable = $row['product_status'] === 'Active' && $row['variant_status'] === 'Active';
        } else {
            $displayName = $row['title'];
            $unitPrice = $row['base_sale_price'] !== null ? (float) $row['base_sale_price'] : (float) $row['base_price'];
            $availableStock = (int) $row['product_stock'];
            $purchasable = $row['product_status'] === 'Active';
        }

        $quantity = (int) $row['quantity'];

        $issue = null;
        if (!$purchasable) {
            $issue = 'No longer available';
        } elseif ($quantity > $availableStock) {
            $issue = $availableStock > 0
                ? "Only {$availableStock} left in stock"
                : 'Out of stock';
        }

        $items[] = [
            'id' => (int) $row['id'],
            'product_id' => (int) $row['product_id'],
            'variant_id' => $row['variant_id'] !== null ? (int) $row['variant_id'] : null,
            'name' => $displayName,
            'slug' => $row['slug'],
            'image' => $row['image'],
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => round($unitPrice * $quantity, 2),
            'available_stock' => $availableStock,
            'is_valid' => $issue === null,
            'issue' => $issue,
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
        ];
    }

    return $items;
}

/**
 * Get a single raw cart row (not the enriched shape getCart() returns),
 * scoped to the owning user. Used internally to find an existing line
 * before deciding whether to insert or increment it.
 *
 * @param mysqli $conn
 * @param int $userId
 * @param int $productId
 * @param int|null $variantId
 * @return array|null
 * @throws Exception
 */
function getCartItem($conn, $userId, $productId, $variantId)
{
    $userId = (int) $userId;
    $productId = (int) $productId;

    if ($variantId === null) {
        $sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND variant_id IS NULL LIMIT 1";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Error preparing statement: ' . mysqli_error($conn));
        }
        $stmt->bind_param('ii', $userId, $productId);
    } else {
        $variantId = (int) $variantId;
        $sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND variant_id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Error preparing statement: ' . mysqli_error($conn));
        }
        $stmt->bind_param('iii', $userId, $productId, $variantId);
    }

    $stmt->execute();

    return $stmt->get_result()->fetch_assoc() ?: null;
}

/**
 * Add a product (or product variant) to a user's cart. If it's already in
 * the cart, the requested quantity is added to what's already there
 * (rather than replacing it) and the combined total is what's checked
 * against stock.
 *
 * @param mysqli $conn
 * @param int $userId
 * @param int $productId
 * @param int|null $variantId
 * @param int $quantity Requested quantity to add. Must be a positive integer —
 *        this is the one piece of client input in this whole flow, and it is
 *        only ever used as "how many more", never as a stock figure.
 * @return array The resulting cart row's id and new total quantity.
 * @throws InvalidArgumentException Invalid input, unpurchasable product, or not enough stock.
 * @throws Exception On a database error.
 */
function addToCart($conn, $userId, $productId, $variantId, $quantity)
{
    $userId = (int) $userId;
    $productId = (int) $productId;
    $variantId = $variantId !== null && $variantId !== '' ? (int) $variantId : null;
    $quantity = (int) $quantity;

    if ($quantity < 1) {
        throw new InvalidArgumentException('Quantity must be at least 1.');
    }

    $context = resolveCartLineContext($conn, $productId, $variantId);
    $availableStock = $context['available_stock'];

    $conn->begin_transaction();

    try {
        $existing = getCartItem($conn, $userId, $productId, $variantId);
        $newQuantity = $quantity + ($existing ? (int) $existing['quantity'] : 0);

        if ($newQuantity > $availableStock) {
            throw new InsufficientStockException(
                $availableStock > 0
                    ? "Only {$availableStock} left in stock."
                    : 'This item is out of stock.',
                $availableStock
            );
        }

        if ($existing) {
            $updateStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            if (!$updateStmt) {
                throw new Exception('Error preparing statement: ' . mysqli_error($conn));
            }
            $existingId = (int) $existing['id'];
            $updateStmt->bind_param('ii', $newQuantity, $existingId);
            $updateStmt->execute();
            $cartId = $existingId;
        } else {
            $insertStmt = $conn->prepare(
                "INSERT INTO cart (user_id, product_id, variant_id, quantity) VALUES (?, ?, ?, ?)"
            );
            if (!$insertStmt) {
                throw new Exception('Error preparing statement: ' . mysqli_error($conn));
            }
            $insertStmt->bind_param('iiii', $userId, $productId, $variantId, $newQuantity);
            $insertStmt->execute();
            $cartId = (int) $conn->insert_id;
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

    return ['id' => $cartId, 'quantity' => $newQuantity];
}

/**
 * Change the quantity of an existing cart line to an exact value (not an
 * increment). Re-checks current stock at the moment of the update — the
 * item may have been added when stock was higher.
 *
 * @param mysqli $conn
 * @param int $userId
 * @param int $cartId
 * @param int $quantity New quantity. Must be a positive integer — to remove
 *        an item entirely, use removeFromCart() instead of passing 0.
 * @return bool
 * @throws InvalidArgumentException If the cart line doesn't belong to this
 *         user, the quantity is invalid, or there isn't enough stock.
 * @throws Exception On a database error.
 */
function updateCartQuantity($conn, $userId, $cartId, $quantity)
{
    $userId = (int) $userId;
    $cartId = (int) $cartId;
    $quantity = (int) $quantity;

    if ($quantity < 1) {
        throw new InvalidArgumentException('Quantity must be at least 1. Use removeFromCart() to remove an item.');
    }

    $sql = "SELECT * FROM cart WHERE id = ? AND user_id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . mysqli_error($conn));
    }
    $stmt->bind_param('ii', $cartId, $userId);
    $stmt->execute();
    $cartItem = $stmt->get_result()->fetch_assoc();

    if (!$cartItem) {
        throw new InvalidArgumentException('Cart item not found.');
    }

    $context = resolveCartLineContext($conn, $cartItem['product_id'], $cartItem['variant_id']);

    if ($quantity > $context['available_stock']) {
        throw new InsufficientStockException(
            $context['available_stock'] > 0
                ? "Only {$context['available_stock']} left in stock."
                : 'This item is out of stock.',
            $context['available_stock']
        );
    }

    $updateStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    if (!$updateStmt) {
        throw new Exception('Error preparing statement: ' . mysqli_error($conn));
    }
    $updateStmt->bind_param('iii', $quantity, $cartId, $userId);
    $updateStmt->execute();

    return true;
}

/**
 * Remove a single line from a user's cart.
 *
 * @param mysqli $conn
 * @param int $userId
 * @param int $cartId
 * @return bool
 * @throws Exception
 */
function removeFromCart($conn, $userId, $cartId)
{
    $userId = (int) $userId;
    $cartId = (int) $cartId;

    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . mysqli_error($conn));
    }
    $stmt->bind_param('ii', $cartId, $userId);
    $stmt->execute();

    return $stmt->affected_rows > 0;
}

/**
 * Empty a user's entire cart (e.g. after a successful checkout).
 *
 * @param mysqli $conn
 * @param int $userId
 * @return bool
 * @throws Exception
 */
function clearCart($conn, $userId)
{
    $userId = (int) $userId;

    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . mysqli_error($conn));
    }
    $stmt->bind_param('i', $userId);
    $stmt->execute();

    return true;
}

/**
 * Compute cart totals for display and for the checkout gate.
 *
 * `can_checkout` is false if the cart is empty or if any line is invalid
 * (out of stock, quantity exceeds current stock, or the product/variant is
 * no longer active) — checkout.php should check this before letting the
 * order through, not just trust that whatever's in the cart is purchasable.
 *
 * @param mysqli $conn
 * @param int $userId
 * @return array{item_count: int, subtotal: float, has_invalid_items: bool, can_checkout: bool, items: array}
 * @throws Exception
 */
function getCartTotals($conn, $userId)
{
    $items = getCart($conn, $userId);

    $itemCount = 0;
    $subtotal = 0.0;
    $hasInvalidItems = false;

    foreach ($items as $item) {
        $itemCount += $item['quantity'];
        $subtotal += $item['line_total'];
        if (!$item['is_valid']) {
            $hasInvalidItems = true;
        }
    }

    return [
        'item_count' => $itemCount,
        'subtotal' => round($subtotal, 2),
        'has_invalid_items' => $hasInvalidItems,
        'can_checkout' => !empty($items) && !$hasInvalidItems,
        'items' => $items,
    ];
}