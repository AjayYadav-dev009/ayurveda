<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';

$q       = trim((string) ($_GET['q'] ?? ''));
$fStatus = in_array($_GET['status'] ?? '', ORDER_STATUSES, true) ? $_GET['status'] : '';
$fPay    = in_array($_GET['payment'] ?? '', PAYMENT_STATUSES, true) ? $_GET['payment'] : '';

try {
    $data = getOrdersForAdmin($conn, $q, $fStatus, $fPay, (int) ($_GET['page'] ?? 1), 20);
} catch (Exception $ex) {
    http_response_code(500);
    exit('Could not load orders: ' . e($ex->getMessage()));
}
$orders = $data['orders'];
$total  = $data['total'];
$page   = $data['page'];
$pages  = $data['pages'];

$filterQs = array_filter(['q' => $q, 'status' => $fStatus, 'payment' => $fPay]);
$flash = flash();

$pageTitle = 'Orders';
$activeNav = 'orders';
require __DIR__ . '/../include/header.php';
orders_styles();
?>
<div class="page">
    <h1>Orders</h1>
    <p class="sub">Manually change order and payment status for testing. Every change is written to the status log.</p>

    <?php if ($flash): ?><div class="flash flash--<?= $flash[0] === 'ok' ? 'ok' : 'err' ?>"><?= e($flash[1]) ?></div><?php endif; ?>

    <div class="card">
        <form class="filters" method="get">
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Order #, name, email, phone" size="30">
            <select name="status">
                <option value="">All order statuses</option>
                <?php foreach (ORDER_STATUSES as $s): ?><option value="<?= $s ?>" <?= $fStatus === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
            </select>
            <select name="payment">
                <option value="">All payment statuses</option>
                <?php foreach (PAYMENT_STATUSES as $s): ?><option value="<?= $s ?>" <?= $fPay === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
            </select>
            <button class="btn" type="submit">Filter</button>
            <?php if ($filterQs): ?><a class="btn btn--ghost" href="index.php">Reset</a><?php endif; ?>
            <span class="muted"><?= $total ?> order<?= $total === 1 ? '' : 's' ?></span>
        </form>
    </div>

    <div class="card tablewrap">
        <table class="t">
            <thead><tr>
                <th>Order</th><th>Customer</th><th>Total</th><th>Placed</th><th>Current</th><th>Quick update</th>
            </tr></thead>
            <tbody>
            <?php if (!$orders): ?>
                <tr><td colspan="6" class="muted">No orders found.</td></tr>
            <?php endif; ?>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td><a href="view.php?id=<?= (int) $o['id'] ?>"><?= e($o['order_number']) ?></a></td>
                    <td><?= e($o['user_name'] ?? '—') ?><br><span class="muted"><?= e($o['user_email'] ?? '') ?></span></td>
                    <td><?= money($o['total']) ?><br><span class="muted"><?= e($o['payment_method'] ?? '') ?></span></td>
                    <td class="muted"><?= e(date('d M Y, H:i', strtotime($o['created_at']))) ?></td>
                    <td><?= status_badge($o['order_status']) ?> <?= status_badge($o['payment_status']) ?></td>
                    <td>
                        <form class="rowform" method="post" action="update.php">
                            <input type="hidden" name="csrf_token" value="<?= e(generateCSRFToken()) ?>">
                            <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                            <input type="hidden" name="back" value="<?= e($_SERVER['QUERY_STRING'] ?? '') ?>">
                            <select name="order_status" title="Order status">
                                <?php foreach (ORDER_STATUSES as $s): ?><option value="<?= $s ?>" <?= $o['order_status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
                            </select>
                            <select name="payment_status" title="Payment status">
                                <?php foreach (PAYMENT_STATUSES as $s): ?><option value="<?= $s ?>" <?= $o['payment_status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
                            </select>
                            <button class="btn" type="submit">Save</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($pages > 1): ?>
        <div class="pager">
            <?php for ($i = 1; $i <= $pages; $i++):
                $href = 'index.php?' . http_build_query($filterQs + ['page' => $i]); ?>
                <?php if ($i === $page): ?><span class="cur"><?= $i ?></span><?php else: ?><a href="<?= e($href) ?>"><?= $i ?></a><?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
</main></div>