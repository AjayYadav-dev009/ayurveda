<?php

/**
 * Reads a single value from the settings table.
 *
 * @param mysqli $conn
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function getSetting($conn, $key, $default = null)
{
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row !== null ? $row['setting_value'] : $default;
}

/**
 * Returns the logged-in user's cart, with price/stock pulled fresh from
 * products/product_variants so nothing here is ever trusted from the client.
 *
 * @param mysqli $conn
 * @param int $userId
 * @return array
 */
function getCartItems($conn, $userId)
{
    $sql = "SELECT
                c.id AS cart_id,
                c.quantity,
                c.product_id,
                c.variant_id,
                p.title AS product_title,
                p.status AS product_status,
                p.base_price,
                p.base_sale_price,
                p.stock AS product_stock,
                v.variant_name,
                v.sku,
                v.price AS variant_price,
                v.sale_price AS variant_sale_price,
                v.stock AS variant_stock,
                v.status AS variant_status,
                (SELECT image FROM product_images pi WHERE pi.product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) AS image
            FROM cart c
            INNER JOIN products p ON p.id = c.product_id
            LEFT JOIN product_variants v ON v.id = c.variant_id
            WHERE c.user_id = ?
            ORDER BY c.id DESC";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Error preparing statement: " . mysqli_error($conn));
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];

    while ($row = $result->fetch_assoc()) {

        // Price always comes from the DB row for the variant if one is
        // selected, otherwise from the product's own base price. Stock is
        // read from products.stock for a simple product and from
        // product_variants.stock for a variant — both are tracked in this
        // schema, so this is never null either way.
        if ($row['variant_id'] !== null) {
            $price = $row['variant_sale_price'] !== null ? $row['variant_sale_price'] : $row['variant_price'];
            $stock = $row['variant_stock'];
            $variantName = $row['variant_name'];
            $sku = $row['sku'];
            $isAvailable = $row['product_status'] === 'Active' && $row['variant_status'] === 'Active';
        } else {
            $price = $row['base_sale_price'] !== null ? $row['base_sale_price'] : $row['base_price'];
            $stock = $row['product_stock'];
            $variantName = null;
            $sku = null;
            $isAvailable = $row['product_status'] === 'Active';
        }

        $price = (float) $price;
        $lineTotal = round($price * (int) $row['quantity'], 2);

        $items[] = [
            'cart_id' => (int) $row['cart_id'],
            'product_id' => (int) $row['product_id'],
            'variant_id' => $row['variant_id'] !== null ? (int) $row['variant_id'] : null,
            'product_title' => $row['product_title'],
            'variant_name' => $variantName,
            'name' => $variantName !== null ? $row['product_title'] . ' - ' . $variantName : $row['product_title'],
            'sku' => $sku,
            'image' => $row['image'],
            'quantity' => (int) $row['quantity'],
            'price' => $price,
            'line_total' => $lineTotal,
            'stock' => $stock !== null ? (int) $stock : null,
            'is_available' => $isAvailable,
        ];
    }

    return $items;
}

/**
 * @param array $cartItems
 * @return float
 */
function calculateCartSubtotal(array $cartItems)
{
    $subtotal = 0;

    foreach ($cartItems as $item) {
        $subtotal += $item['line_total'];
    }

    return round($subtotal, 2);
}

/**
 * @param mysqli $conn
 * @param int $userId
 * @return array
 */
function getUserAddresses($conn, $userId)
{
    $stmt = $conn->prepare(
        "SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC"
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $addresses = [];
    while ($row = $result->fetch_assoc()) {
        $addresses[] = $row;
    }

    return $addresses;
}

/**
 * @param mysqli $conn
 * @param int $id
 * @param int $userId
 * @return array|null
 */
function getAddressById($conn, $id, $userId)
{
    $stmt = $conn->prepare(
        "SELECT * FROM addresses WHERE id = ? AND user_id = ? LIMIT 1"
    );
    $stmt->bind_param('ii', $id, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

/**
 * @param mysqli $conn
 * @param int $userId
 * @param array $data
 * @return int Inserted address id
 */
function addAddress($conn, $userId, array $data)
{
    if (!empty($data['is_default'])) {
        $reset = $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
        $reset->bind_param('i', $userId);
        $reset->execute();
    }

    $stmt = $conn->prepare(
        "INSERT INTO addresses (user_id, full_name, phone, address_line1, address_line2, city, state, country, pincode, is_default)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $isDefault = !empty($data['is_default']) ? 1 : 0;

    $stmt->bind_param(
        'issssssssi',
        $userId,
        $data['full_name'],
        $data['phone'],
        $data['address_line1'],
        $data['address_line2'],
        $data['city'],
        $data['state'],
        $data['country'],
        $data['pincode'],
        $isDefault
    );

    $stmt->execute();

    return $conn->insert_id;
}

/**
 * Plain lookup - no validation, just fetches the row.
 *
 * @param mysqli $conn
 * @param string $code
 * @return array|null
 */
function getCouponByCode($conn, $code)
{
    $stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? LIMIT 1");
    $stmt->bind_param('s', $code);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

/**
 * Runs every eligibility check against an already-fetched coupon row:
 * active, within date range, minimum order met, usage limit, per-user limit.
 * Does not touch discount math - see calculateCouponDiscount() for that.
 *
 * @param mysqli $conn
 * @param array $coupon Row from getCouponByCode()
 * @param int $userId
 * @param float $subtotal
 * @return array ['valid' => bool, 'message' => string]
 */
function validateCoupon($conn, array $coupon, $userId, $subtotal)
{
    if ((int) $coupon['status'] !== 1) {
        return ['valid' => false, 'message' => 'This coupon is no longer active.'];
    }

    $now = date('Y-m-d H:i:s');

    if ($coupon['start_date'] !== null && $now < $coupon['start_date']) {
        return ['valid' => false, 'message' => 'This coupon is not active yet.'];
    }

    if ($coupon['end_date'] !== null && $now > $coupon['end_date']) {
        return ['valid' => false, 'message' => 'This coupon has expired.'];
    }

    if ($subtotal < (float) $coupon['minimum_order']) {
        return ['valid' => false, 'message' => 'Minimum order of ₹' . number_format((float) $coupon['minimum_order'], 2) . ' required for this coupon.'];
    }

    if ($coupon['usage_limit'] !== null && (int) $coupon['used_count'] >= (int) $coupon['usage_limit']) {
        return ['valid' => false, 'message' => 'This coupon has reached its usage limit.'];
    }

    if ($coupon['per_user_limit'] !== null) {
        $usageStmt = $conn->prepare("SELECT COUNT(*) AS used FROM coupon_usages WHERE coupon_id = ? AND user_id = ?");
        $usageStmt->bind_param('ii', $coupon['id'], $userId);
        $usageStmt->execute();
        $used = (int) $usageStmt->get_result()->fetch_assoc()['used'];

        if ($used >= (int) $coupon['per_user_limit']) {
            return ['valid' => false, 'message' => 'You have already used this coupon the maximum number of times.'];
        }
    }

    return ['valid' => true, 'message' => 'Coupon applied.'];
}

/**
 * Pure discount math for an already-validated coupon: percentage or fixed,
 * capped by maximum_discount and by the subtotal itself.
 *
 * @param array $coupon Row from getCouponByCode()
 * @param float $subtotal
 * @return float
 */
function calculateCouponDiscount(array $coupon, $subtotal)
{
    if ($coupon['discount_type'] === 'percentage') {
        $discount = $subtotal * ((float) $coupon['discount_value'] / 100);
        if ($coupon['maximum_discount'] !== null) {
            $discount = min($discount, (float) $coupon['maximum_discount']);
        }
    } else {
        $discount = (float) $coupon['discount_value'];
    }

    return round(min($discount, $subtotal), 2);
}

/**
 * Records that a coupon was used on an order and bumps its used_count.
 * Called once, at order-creation time - never during validation/preview.
 *
 * @param mysqli $conn
 * @param int $couponId
 * @param int $userId
 * @param int $orderId
 */
function recordCouponUsage($conn, $couponId, $userId, $orderId)
{
    $usageStmt = $conn->prepare(
        "INSERT INTO coupon_usages (coupon_id, user_id, order_id) VALUES (?, ?, ?)"
    );
    $usageStmt->bind_param('iii', $couponId, $userId, $orderId);
    $usageStmt->execute();

    $incrementStmt = $conn->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?");
    $incrementStmt->bind_param('i', $couponId);
    $incrementStmt->execute();
}

/**
 * Convenience wrapper combining the three lookup/validate/calculate steps
 * above, for callers (like the checkout page) that just want a one-shot
 * "is this code usable, and if so what's the discount" answer.
 *
 * @param mysqli $conn
 * @param string $code
 * @param int $userId
 * @param float $subtotal
 * @return array ['valid' => bool, 'message' => string, 'coupon' => array|null, 'discount' => float]
 */
function applyCouponCode($conn, $code, $userId, $subtotal)
{
    $coupon = getCouponByCode($conn, $code);

    if (!$coupon) {
        return ['valid' => false, 'message' => 'Invalid coupon code.', 'coupon' => null, 'discount' => 0];
    }

    $check = validateCoupon($conn, $coupon, $userId, $subtotal);

    if (!$check['valid']) {
        return ['valid' => false, 'message' => $check['message'], 'coupon' => null, 'discount' => 0];
    }

    $discount = calculateCouponDiscount($coupon, $subtotal);

    return ['valid' => true, 'message' => $check['message'], 'coupon' => $coupon, 'discount' => $discount];
}

/**
 * Calculates shipping. Free above the configured minimum (0 = always free
 * when shipping is disabled); otherwise a flat rate.
 *
 * NOTE: there's no per-order shipping rate in the settings table yet, so
 * this uses a flat ₹SHIPPING_FLAT_RATE constant below the free threshold.
 * Move this into `settings` if you want it configurable from the admin panel.
 *
 * @param mysqli $conn
 * @param float $subtotal
 * @return float
 */
function calculateShipping($conn, $subtotal)
{
    $shippingEnabled = (int) getSetting($conn, 'shipping_enabled', 1);

    if (!$shippingEnabled) {
        return 0.0;
    }

    $freeShippingMinimum = (float) getSetting($conn, 'free_shipping_minimum', 0);

    if ($freeShippingMinimum > 0 && $subtotal >= $freeShippingMinimum) {
        return 0.0;
    }

    return SHIPPING_FLAT_RATE;
}

/**
 * @param mysqli $conn
 * @param float $amountAfterDiscount
 * @return float
 */
function calculateTax($conn, $amountAfterDiscount)
{
    $taxEnabled = (int) getSetting($conn, 'tax_enabled', 0);

    if (!$taxEnabled) {
        return 0.0;
    }

    $taxPercentage = (float) getSetting($conn, 'tax_percentage', 0);

    return round($amountAfterDiscount * ($taxPercentage / 100), 2);
}

/**
 * @return string
 */
function generateOrderNumber()
{
    return 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

/**
 * Creates the order from server-verified data, decrements product/variant
 * stock, records coupon usage, and empties the user's cart. Runs inside a
 * transaction so a failure partway through doesn't leave things half-done.
 *
 * @param mysqli $conn
 * @param int $userId
 * @param array $address
 * @param array $cartItems
 * @param array|null $coupon
 * @param float $discount
 * @param float $subtotal
 * @param float $tax
 * @param float $shipping
 * @param float $total
 * @param string $paymentMethod
 * @return int Order id
 * @throws Exception
 */
function createOrder($conn, $userId, array $address, array $cartItems, $coupon, $discount, $subtotal, $tax, $shipping, $total, $paymentMethod)
{
    $conn->begin_transaction();

    try {
        // Re-check stock for every item right before committing, in case
        // it changed since the cart was last loaded. Applies to simple
        // products (products.stock) just as much as variants
        // (product_variants.stock) — stock is null for neither now.
        foreach ($cartItems as $item) {
            if ($item['stock'] !== null && $item['quantity'] > $item['stock']) {
                throw new RuntimeException("Not enough stock for {$item['name']}.");
            }
        }

        $orderNumber = generateOrderNumber();

        $stmt = $conn->prepare(
            "INSERT INTO orders (
                user_id, order_number, subtotal, discount, tax, shipping, total,
                coupon_id, coupon_code, payment_method, payment_status, order_status,
                shipping_name, shipping_phone, shipping_address, shipping_city, shipping_state, shipping_country, shipping_pincode
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', ?, ?, ?, ?, ?, ?, ?)"
        );

        $couponId = $coupon !== null ? $coupon['id'] : null;
        $couponCode = $coupon !== null ? $coupon['code'] : null;
        $shippingAddressLine = $address['address_line1'] . (!empty($address['address_line2']) ? ', ' . $address['address_line2'] : '');

        $stmt->bind_param(
            'isdddddisssssssss',
            $userId,
            $orderNumber,
            $subtotal,
            $discount,
            $tax,
            $shipping,
            $total,
            $couponId,
            $couponCode,
            $paymentMethod,
            $address['full_name'],
            $address['phone'],
            $shippingAddressLine,
            $address['city'],
            $address['state'],
            $address['country'],
            $address['pincode']
        );

        $stmt->execute();
        $orderId = $conn->insert_id;

        $itemStmt = $conn->prepare(
            "INSERT INTO order_items (order_id, product_id, variant_id, product_name, variant_name, sku, price, quantity, subtotal)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        foreach ($cartItems as $item) {
            $itemStmt->bind_param(
                'iiisssdid',
                $orderId,
                $item['product_id'],
                $item['variant_id'],
                $item['product_title'],
                $item['variant_name'],
                $item['sku'],
                $item['price'],
                $item['quantity'],
                $item['line_total']
            );
            $itemStmt->execute();

            if ($item['variant_id'] !== null) {
                $stockStmt = $conn->prepare(
                    "UPDATE product_variants SET stock = stock - ? WHERE id = ? AND stock >= ?"
                );
                $stockStmt->bind_param('iii', $item['quantity'], $item['variant_id'], $item['quantity']);
                $stockStmt->execute();

                if ($stockStmt->affected_rows === 0) {
                    throw new RuntimeException("Not enough stock for {$item['name']}.");
                }
            } else {
                $stockStmt = $conn->prepare(
                    "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?"
                );
                $stockStmt->bind_param('iii', $item['quantity'], $item['product_id'], $item['quantity']);
                $stockStmt->execute();

                if ($stockStmt->affected_rows === 0) {
                    throw new RuntimeException("Not enough stock for {$item['name']}.");
                }
            }
        }

        if ($coupon !== null) {
            recordCouponUsage($conn, $coupon['id'], $userId, $orderId);
        }

        $clearCartStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clearCartStmt->bind_param('i', $userId);
        $clearCartStmt->execute();

        $conn->commit();

        return $orderId;
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}