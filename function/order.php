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