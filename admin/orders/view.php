<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';

try {
    $o = getOrderDetailsForAdmin($conn, (int) ($_GET['id'] ?? 0));
} catch (Exception $ex) {
    http_response_code(500);
    exit('Could not load order: ' . e($ex->getMessage()));
}
if (!$o) {
    http_response_code(404);
    exit('Order not found.');
}
$id = (int) $o['id'];

$flash = flash();
$pageTitle = 'Order ' . $o['order_number'];
$activeNav = 'orders';
require __DIR__ . '/../include/header.php';
orders_styles();
?>
<div class="page">
    <p class="muted" style="margin:0 0 8px"><a href="index.php" style="color:var(--sky);font-weight:700;text-decoration:none">← All orders</a></p>
    <h1>Order <?= e($o['order_number']) ?></h1>
    <p class="sub">Placed <?= e(date('d M Y, H:i', strtotime($o['created_at']))) ?> · <?= status_badge($o['order_status']) ?> <?= status_badge($o['payment_status']) ?></p>

    <?php if ($flash): ?><div class="flash flash--<?= $flash[0] === 'ok' ? 'ok' : 'err' ?>"><?= e($flash[1]) ?></div><?php endif; ?>

    <div class="grid">
        <div>
            <div class="card tablewrap">
                <h2>Items</h2>
                <table class="t">
                    <thead><tr><th>Product</th><th>SKU</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
                    <tbody>
                    <?php foreach ($o['items'] as $it): ?>
                        <tr>
                            <td><?= e($it['product_name']) ?><?php if ($it['variant_name']): ?><br><span class="muted"><?= e($it['variant_name']) ?></span><?php endif; ?></td>
                            <td class="muted"><?= e($it['sku'] ?? '') ?></td>
                            <td><?= money($it['price']) ?></td>
                            <td><?= (int) $it['quantity'] ?></td>
                            <td><?= money($it['subtotal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$o['items']): ?><tr><td colspan="5" class="muted">No items.</td></tr><?php endif; ?>
                    </tbody>
                </table>
                <dl class="kv" style="margin-top:14px;max-width:320px;margin-left:auto">
                    <dt>Subtotal</dt><dd><?= money($o['subtotal']) ?></dd>
                    <dt>Discount</dt><dd>− <?= money($o['discount']) ?><?= $o['coupon_code'] ? ' <span class="muted">(' . e($o['coupon_code']) . ')</span>' : '' ?></dd>
                    <dt>Tax</dt><dd><?= money($o['tax']) ?></dd>
                    <dt>Shipping</dt><dd><?= money($o['shipping']) ?></dd>
                    <dt><strong>Total</strong></dt><dd><strong><?= money($o['total']) ?></strong></dd>
                </dl>
            </div>

            <div class="card tablewrap">
                <h2>Status history</h2>
                <table class="t">
                    <thead><tr><th>When</th><th>Change</th><th>Note</th><th>By</th></tr></thead>
                    <tbody>
                    <?php foreach ($o['logs'] as $l): ?>
                        <tr>
                            <td class="muted"><?= e(date('d M Y, H:i', strtotime($l['created_at']))) ?></td>
                            <td><?= $l['old_status'] ? status_badge($l['old_status']) . ' → ' : '' ?><?= status_badge($l['new_status']) ?></td>
                            <td class="muted"><?= e($l['note'] ?? '') ?></td>
                            <td class="muted"><?= e($l['admin_name'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$o['logs']): ?><tr><td colspan="4" class="muted">No changes logged yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <div class="card">
                <h2>Update status</h2>
                <form class="f" method="post" action="update.php">
                    <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">
                    <input type="hidden" name="order_id" value="<?= $id ?>">
                    <input type="hidden" name="return" value="view">

                    <label>Order status</label>
                    <select name="order_status">
                        <?php foreach (ORDER_STATUSES as $s): ?><option value="<?= $s ?>" <?= $o['order_status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
                    </select>

                    <label>Payment status</label>
                    <select name="payment_status">
                        <?php foreach (PAYMENT_STATUSES as $s): ?><option value="<?= $s ?>" <?= $o['payment_status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
                    </select>

                    <label>Shipping provider</label>
                    <input type="text" name="shipping_provider" maxlength="100" value="<?= e($o['shipping_provider'] ?? '') ?>">

                    <label>Tracking number</label>
                    <input type="text" name="tracking_number" maxlength="150" value="<?= e($o['tracking_number'] ?? '') ?>">

                    <label>Note (saved in history)</label>
                    <textarea name="note" rows="2" maxlength="500" placeholder="Optional"></textarea>

                    <p style="margin:14px 0 0"><button class="btn" type="submit">Save changes</button></p>
                </form>
            </div>

            <div class="card">
                <h2>Customer &amp; shipping</h2>
                <dl class="kv">
                    <dt>Customer</dt><dd><?= e($o['user_name'] ?? '—') ?><br><span class="muted"><?= e($o['user_email'] ?? '') ?></span></dd>
                    <dt>Ship to</dt><dd><?= e($o['shipping_name']) ?><br><?= nl2br(e($o['shipping_address'])) ?><br><?= e($o['shipping_city']) ?>, <?= e($o['shipping_state']) ?> <?= e($o['shipping_pincode']) ?><br><?= e($o['shipping_country']) ?></dd>
                    <dt>Phone</dt><dd><?= e($o['shipping_phone']) ?></dd>
                    <dt>Payment</dt><dd><?= e($o['payment_method'] ?? '—') ?></dd>
                    <?php if ($o['notes']): ?><dt>Order notes</dt><dd><?= nl2br(e($o['notes'])) ?></dd><?php endif; ?>
                </dl>
            </div>

            <?php if ($o['payments']): ?>
            <div class="card">
                <h2>Payments</h2>
                <?php foreach ($o['payments'] as $p): ?>
                    <dl class="kv" style="margin-bottom:10px">
                        <dt>Gateway</dt><dd><?= e($p['gateway'] ?? '—') ?></dd>
                        <dt>Txn ID</dt><dd><?= e($p['transaction_id'] ?? '—') ?></dd>
                        <dt>Amount</dt><dd><?= money($p['amount']) ?> <?= status_badge($p['status']) ?></dd>
                        <dt>Paid at</dt><dd><?= e($p['paid_at'] ?? '—') ?></dd>
                    </dl>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</main></div>