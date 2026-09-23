<?php

/**
 * Fetch every order placed by a customer, newest first, each with its
 * line items attached so the page can show what was actually bought
 * (not just the order number/total). Shape matches what
 * account/my-orders.php loops over: id / number / item_count / total /
 * status / items.
 *
 * Each item in 'items' is:
 *   name         => string  (the product name AT THE TIME OF PURCHASE,
 *                             from order_items.product_name — kept as-is
 *                             even if the product has since been
 *                             renamed, so past orders stay accurate)
 *   variant_name => ?string
 *   quantity     => int
 *   url          => ?string (link to the product's current page, same
 *                             products/product_details.php?slug=...
 *                             pattern products.php already uses —
 *                             null if the product was deleted or is no
 *                             longer Active, so we don't link to a page
 *                             that 404s or shouldn't be browsable)
 *
 * @param mysqli $conn
 * @param int $userId
 * @return array<int, array{id:int, number:string, item_count:int, total:float, status:string, items:array}>
 * @throws Exception
 */
function getOrdersForCustomer($conn, $userId)
{
    $sql = "SELECT o.id,
                   o.order_number,
                   o.total,
                   o.order_status,
                   COALESCE(SUM(oi.quantity), 0) AS item_count
            FROM orders o
            LEFT JOIN order_items oi ON oi.order_id = o.id
            WHERE o.user_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'i', $userId);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error fetching orders: " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);

    $orders = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = [
            'id'         => (int) $row['id'],
            'number'     => $row['order_number'],
            'item_count' => (int) $row['item_count'],
            'total'      => (float) $row['total'],
            // order_status is a lowercase enum (pending, confirmed,
            // processing, shipped, delivered, cancelled, returned);
            // ucfirst() gives the display label my-orders.php shows,
            // and strtolower(ucfirst($x)) === $x so its
            // status-badge--{status} CSS class lookup still lines up.
            'status'     => ucfirst($row['order_status']),
            'items'      => [],
        ];
    }

    if (empty($orders)) {
        return $orders;
    }

    $orderIds = array_column($orders, 'id');
    $itemsByOrder = getOrderItemsForOrderIds($conn, $orderIds);

    foreach ($orders as &$order) {
        $order['items'] = $itemsByOrder[$order['id']] ?? [];
    }
    unset($order);

    return $orders;
}

/**
 * Fetch the line items for a set of order ids in one query, grouped by
 * order id, joined back to products for each item's current slug/status
 * so my-orders.php can link "what you bought" straight to that product.
 *
 * @param mysqli $conn
 * @param array<int,int> $orderIds
 * @return array<int, array<int, array{name:string, variant_name:?string, quantity:int, url:?string}>>
 * @throws Exception
 */
function getOrderItemsForOrderIds($conn, array $orderIds)
{
    if (empty($orderIds)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $sql = "SELECT oi.order_id,
                   oi.product_name,
                   oi.variant_name,
                   oi.quantity,
                   oi.price,
                   oi.subtotal,
                   p.slug AS product_slug,
                   p.status AS product_status
            FROM order_items oi
            LEFT JOIN products p ON p.id = oi.product_id
            WHERE oi.order_id IN ($placeholders)
            ORDER BY oi.id ASC";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    $types = str_repeat('i', count($orderIds));
    $stmt->bind_param($types, ...$orderIds);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error fetching order items: " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);

    $itemsByOrder = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $url = null;
        // Only link out if the product still exists and is still Active
        // — a deleted/deactivated product wouldn't have a working or
        // browsable page to send the customer to.
        if (!empty($row['product_slug']) && $row['product_status'] === 'Active') {
            $url = BASE_URL . 'products/product_details.php?slug=' . urlencode($row['product_slug']);
        }

        $itemsByOrder[(int) $row['order_id']][] = [
            'name'         => $row['product_name'],
            'variant_name' => $row['variant_name'],
            'quantity'     => (int) $row['quantity'],
            'price'        => (float) $row['price'],
            'subtotal'     => (float) $row['subtotal'],
            'url'          => $url,
        ];
    }

    return $itemsByOrder;
}

/**
 * Fetch one order's full details for the order-details page — everything
 * my-orders.php's list view leaves out: shipping address, payment
 * method/status, price breakdown, tracking, plus the same linked line
 * items getOrdersForCustomer() attaches.
 *
 * Deliberately requires $userId and matches it in the WHERE clause
 * (not just "does this order id exist") so a customer can never view
 * another customer's order by changing the id in the URL.
 *
 * @param mysqli $conn
 * @param int $orderId
 * @param int $userId
 * @return array|null null if the order doesn't exist or doesn't belong to this user
 * @throws Exception
 */
function getOrderDetailsForCustomer($conn, $orderId, $userId)
{
    $sql = "SELECT id, order_number, subtotal, discount, tax, shipping, total,
                   payment_method, payment_status, order_status,
                   shipping_name, shipping_phone, shipping_address, shipping_city,
                   shipping_state, shipping_country, shipping_pincode,
                   tracking_number, shipping_provider, created_at
            FROM orders
            WHERE id = ? AND user_id = ?
            LIMIT 1";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'ii', $orderId, $userId);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error fetching order: " . mysqli_stmt_error($stmt));
    }

    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (!$row) {
        return null;
    }

    $order = [
        'id'                => (int) $row['id'],
        'number'            => $row['order_number'],
        'subtotal'          => (float) $row['subtotal'],
        'discount'          => (float) $row['discount'],
        'tax'               => (float) $row['tax'],
        'shipping_cost'     => (float) $row['shipping'],
        'total'             => (float) $row['total'],
        'payment_method'    => $row['payment_method'],
        'payment_status'    => ucfirst($row['payment_status']),
        'status'            => ucfirst($row['order_status']),
        'shipping_name'     => $row['shipping_name'],
        'shipping_phone'    => $row['shipping_phone'],
        'shipping_address'  => $row['shipping_address'],
        'shipping_city'     => $row['shipping_city'],
        'shipping_state'    => $row['shipping_state'],
        'shipping_country'  => $row['shipping_country'],
        'shipping_pincode'  => $row['shipping_pincode'],
        'tracking_number'   => $row['tracking_number'],
        'shipping_provider' => $row['shipping_provider'],
        'created_at'        => $row['created_at'],
        'items'             => [],
    ];

    $itemsByOrder = getOrderItemsForOrderIds($conn, [$order['id']]);
    $order['items'] = $itemsByOrder[$order['id']] ?? [];

    return $order;
}

/**
 * Admin-side order functions (list, details, manual status change).
 * Same style as function/order.php: mysqli prepared statements, $conn first,
 * throws Exception on failure.
 */

const ORDER_STATUSES   = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];
const PAYMENT_STATUSES = ['pending', 'paid', 'failed', 'refunded'];

/**
 * Prepare + execute a query and return all rows as assoc arrays.
 *
 * @param mysqli $conn
 * @param string $sql
 * @param string $types  bind_param type string ('' when no params)
 * @param array  $params
 * @return array
 * @throws Exception
 */
function adminOrderFetchAll($conn, $sql, $types = '', array $params = [])
{
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error executing query: " . mysqli_stmt_error($stmt));
    }
    $rows = mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

/**
 * Prepare + execute a write query.
 *
 * @throws Exception
 */
function adminOrderExec($conn, $sql, $types = '', array $params = [])
{
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error executing query: " . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);
}

/**
 * Paginated, filterable order list for the admin.
 *
 * @return array{orders:array, total:int, page:int, pages:int}
 * @throws Exception
 */
function getOrdersForAdmin($conn, $search = '', $status = '', $paymentStatus = '', $page = 1, $perPage = 20)
{
    $where  = [];
    $types  = '';
    $params = [];

    $search = trim((string) $search);
    if ($search !== '') {
        $where[] = '(o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR o.shipping_phone LIKE ?)';
        $like = '%' . $search . '%';
        $types .= 'ssss';
        array_push($params, $like, $like, $like, $like);
    }
    if (in_array($status, ORDER_STATUSES, true)) {
        $where[] = 'o.order_status = ?';
        $types .= 's';
        $params[] = $status;
    }
    if (in_array($paymentStatus, PAYMENT_STATUSES, true)) {
        $where[] = 'o.payment_status = ?';
        $types .= 's';
        $params[] = $paymentStatus;
    }
    $w = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $countRow = adminOrderFetchAll(
        $conn,
        "SELECT COUNT(*) AS c FROM orders o LEFT JOIN users u ON u.id = o.user_id $w",
        $types,
        $params
    );
    $total   = (int) $countRow[0]['c'];
    $perPage = max(1, (int) $perPage);
    $pages   = max(1, (int) ceil($total / $perPage));
    $page    = min(max(1, (int) $page), $pages);
    $offset  = ($page - 1) * $perPage;

    $orders = adminOrderFetchAll(
        $conn,
        "SELECT o.id, o.order_number, o.total, o.payment_method, o.payment_status,
                o.order_status, o.created_at,
                u.name AS user_name, u.email AS user_email
         FROM orders o
         LEFT JOIN users u ON u.id = o.user_id
         $w
         ORDER BY o.id DESC
         LIMIT $perPage OFFSET $offset",
        $types,
        $params
    );

    return ['orders' => $orders, 'total' => $total, 'page' => $page, 'pages' => $pages];
}

/**
 * Everything the admin order page shows: the order, its items, payments
 * and status history. Unlike getOrderDetailsForCustomer() this is NOT
 * scoped to a user — admins can open any order.
 *
 * @return array|null null if the order doesn't exist
 * @throws Exception
 */
function getOrderDetailsForAdmin($conn, $orderId)
{
    $rows = adminOrderFetchAll(
        $conn,
        "SELECT o.*, u.name AS user_name, u.email AS user_email
         FROM orders o
         LEFT JOIN users u ON u.id = o.user_id
         WHERE o.id = ? LIMIT 1",
        'i',
        [(int) $orderId]
    );
    if (!$rows) {
        return null;
    }
    $order = $rows[0];

    $order['items'] = adminOrderFetchAll(
        $conn,
        "SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC",
        'i',
        [(int) $orderId]
    );
    $order['payments'] = adminOrderFetchAll(
        $conn,
        "SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC",
        'i',
        [(int) $orderId]
    );
    $order['logs'] = adminOrderFetchAll(
        $conn,
        "SELECT l.*, a.name AS admin_name
         FROM order_status_logs l
         LEFT JOIN admins a ON a.id = l.admin_id
         WHERE l.order_id = ?
         ORDER BY l.id DESC",
        'i',
        [(int) $orderId]
    );

    return $order;
}

/**
 * Order statuses that mean "this stock is back on the shelf" —
 * cancelling or returning an order releases the stock that was
 * decremented for it at createOrder() time.
 */
const RESTOCKING_ORDER_STATUSES = ['cancelled', 'returned'];

/**
 * Adds each line item's quantity back onto products.stock /
 * product_variants.stock. Called when an order moves INTO cancelled or
 * returned.
 *
 * @param mysqli $conn
 * @param int $orderId
 * @throws Exception
 */
function restockOrderItems($conn, $orderId)
{
    $items = adminOrderFetchAll(
        $conn,
        "SELECT product_id, variant_id, quantity FROM order_items WHERE order_id = ?",
        'i',
        [(int) $orderId]
    );

    foreach ($items as $item) {
        if ($item['variant_id'] !== null) {
            adminOrderExec(
                $conn,
                "UPDATE product_variants SET stock = stock + ? WHERE id = ?",
                'ii',
                [(int) $item['quantity'], (int) $item['variant_id']]
            );
        } else {
            adminOrderExec(
                $conn,
                "UPDATE products SET stock = stock + ? WHERE id = ?",
                'ii',
                [(int) $item['quantity'], (int) $item['product_id']]
            );
        }
    }
}

/**
 * Reverse of restockOrderItems() — deducts stock again. Called when an
 * order moves OUT of cancelled/returned back into an active status, so
 * stock that was put back isn't double-counted as available. Throws if
 * any item no longer has enough stock, so the caller's transaction rolls
 * back instead of going negative.
 *
 * @param mysqli $conn
 * @param int $orderId
 * @throws Exception
 */
function deductOrderItemsStock($conn, $orderId)
{
    $items = adminOrderFetchAll(
        $conn,
        "SELECT product_id, variant_id, quantity, product_name FROM order_items WHERE order_id = ?",
        'i',
        [(int) $orderId]
    );

    foreach ($items as $item) {
        if ($item['variant_id'] !== null) {
            $stmt = mysqli_prepare($conn, "UPDATE product_variants SET stock = stock - ? WHERE id = ? AND stock >= ?");
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
        }
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }

        $targetId = $item['variant_id'] !== null ? (int) $item['variant_id'] : (int) $item['product_id'];
        mysqli_stmt_bind_param($stmt, 'iii', $item['quantity'], $targetId, $item['quantity']);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) === 0) {
            throw new RuntimeException("Not enough stock left to move \"{$item['product_name']}\" out of cancelled/returned.");
        }
    }
}

/**
 * Manually change an order (for testing). There are NO transition rules:
 * any status can be set from any other. Keys read from $in (all optional,
 * missing keys are left unchanged): order_status, payment_status,
 * tracking_number, shipping_provider, note.
 *
 * Also syncs payments.status (and paid_at) when the payment status
 * changes, restocks/deducts product stock when order_status crosses in
 * or out of cancelled/returned, and writes rows to order_status_logs.
 *
 * @param int|null $adminId
 * @return array{0:bool, 1:string} [success, message]
 */
function updateOrderForAdmin($conn, $orderId, array $in, $adminId = null)
{
    $orderId = (int) $orderId;
    mysqli_begin_transaction($conn);

    try {
        $cur = adminOrderFetchAll(
            $conn,
            "SELECT order_status, payment_status FROM orders WHERE id = ? FOR UPDATE",
            'i',
            [$orderId]
        );
        if (!$cur) {
            mysqli_rollback($conn);
            return [false, 'Order not found.'];
        }
        $cur = $cur[0];

        $newOrder = (isset($in['order_status']) && in_array($in['order_status'], ORDER_STATUSES, true))
            ? $in['order_status'] : $cur['order_status'];
        $newPay = (isset($in['payment_status']) && in_array($in['payment_status'], PAYMENT_STATUSES, true))
            ? $in['payment_status'] : $cur['payment_status'];

        $set    = ['order_status = ?', 'payment_status = ?'];
        $types  = 'ss';
        $params = [$newOrder, $newPay];

        foreach (['tracking_number' => 150, 'shipping_provider' => 100] as $col => $max) {
            if (array_key_exists($col, $in)) {
                $val = trim((string) $in[$col]);
                $set[]    = "$col = ?";
                $types   .= 's';
                $params[] = $val === '' ? null : mb_substr($val, 0, $max);
            }
        }
        $types   .= 'i';
        $params[] = $orderId;
        adminOrderExec($conn, 'UPDATE orders SET ' . implode(', ', $set) . ' WHERE id = ?', $types, $params);

        $note         = trim((string) ($in['note'] ?? ''));
        $orderChanged = $newOrder !== $cur['order_status'];
        $payChanged   = $newPay !== $cur['payment_status'];

        if ($orderChanged) {
            $wasRestocked = in_array($cur['order_status'], RESTOCKING_ORDER_STATUSES, true);
            $isRestocked  = in_array($newOrder, RESTOCKING_ORDER_STATUSES, true);

            if ($isRestocked && !$wasRestocked) {
                // Order just became cancelled/returned — release its stock.
                restockOrderItems($conn, $orderId);
            } elseif ($wasRestocked && !$isRestocked) {
                // Order is being moved back out of cancelled/returned —
                // take that stock again. Throws (and rolls back the whole
                // transaction, below) if there isn't enough left.
                deductOrderItemsStock($conn, $orderId);
            }
        }

        if ($payChanged) {
            adminOrderExec(
                $conn,
                "UPDATE payments
                 SET status = ?,
                     paid_at = CASE WHEN ? = 'paid' THEN COALESCE(paid_at, NOW()) ELSE paid_at END
                 WHERE order_id = ?",
                'ssi',
                [$newPay, $newPay, $orderId]
            );
        }

        $logSql = "INSERT INTO order_status_logs (order_id, old_status, new_status, note, admin_id)
                   VALUES (?, ?, ?, ?, ?)";
        if ($orderChanged) {
            adminOrderExec($conn, $logSql, 'isssi', [
                $orderId,
                $cur['order_status'],
                $newOrder,
                $note !== '' ? mb_substr($note, 0, 500) : 'Manual change',
                $adminId,
            ]);
        }
        if ($payChanged) {
            $n = "Payment status: {$cur['payment_status']} → $newPay"
                . ($note !== '' && !$orderChanged ? " ($note)" : '');
            adminOrderExec($conn, $logSql, 'isssi', [$orderId, $newOrder, $newOrder, mb_substr($n, 0, 500), $adminId]);
        }

        mysqli_commit($conn);
        return [true, ($orderChanged || $payChanged) ? 'Order updated.' : 'Saved (no status change).'];
    } catch (Throwable $ex) {
        mysqli_rollback($conn);
        return [false, 'Update failed: ' . $ex->getMessage()];
    }
}
