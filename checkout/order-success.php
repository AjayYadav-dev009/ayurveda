<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/customer.php';
require_once __DIR__ . '/../function/helper.php';
require_once __DIR__ . '/../function/order.php';

if (!isCustomerLogin()) {
    redirect(BASE_URL . 'account/login.php');
}

$userId = $_SESSION['customer_id'];
$orderId = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;

$order = $orderId ? getOrderDetailsForCustomer($conn, $orderId, $userId) : null;

if (!$order) {
    redirect(BASE_URL . 'account/my-orders.php');
}

?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<style>
    .success-section {
        padding: 56px 20px 90px;
        background: var(--color-bg);
        position: relative;
        overflow: hidden;
    }

    .success-wrap {
        max-width: 760px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
    }

    .success-hero {
        text-align: center;
        margin-bottom: 36px;
    }

    .success-icon {
        width: 96px;
        height: 96px;
        margin: 0 auto 20px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 12px 30px rgba(46, 204, 113, 0.35);
        animation: pop-in 0.5s cubic-bezier(.26,1.4,.44,1) both;
    }

    .success-icon svg {
        width: 46px;
        height: 46px;
        stroke: #fff;
        stroke-width: 3;
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    @keyframes pop-in {
        0% { transform: scale(0); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }

    .success-title {
        font-size: 30px;
        font-weight: 800;
        color: var(--color-text);
        margin: 0 0 8px;
    }

    .success-sub {
        font-size: 16px;
        color: var(--color-text-muted, #666);
        margin: 0;
    }

    .success-order-number {
        display: inline-block;
        margin-top: 14px;
        padding: 8px 18px;
        border-radius: 999px;
        background: #fff7e6;
        border: 1px dashed #f0c674;
        color: #a5680b;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .success-card {
        background: var(--color-surface, #fff);
        border-radius: 16px;
        padding: 26px 28px;
        margin-bottom: 22px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.06);
    }

    .success-card h2 {
        font-size: 18px;
        margin: 0 0 16px;
        color: var(--color-text);
    }

    .success-item {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
        font-size: 14px;
    }

    .success-item:last-child { border-bottom: none; }

    .success-item-name { color: var(--color-text); font-weight: 600; }
    .success-item-meta { color: #888; font-size: 13px; }
    .success-item-price { color: var(--color-text); font-weight: 600; white-space: nowrap; }

    .success-summary-row {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        font-size: 14px;
        color: #555;
    }

    .success-summary-total {
        display: flex;
        justify-content: space-between;
        padding-top: 12px;
        margin-top: 8px;
        border-top: 2px solid #eee;
        font-size: 17px;
        font-weight: 800;
        color: var(--color-text);
    }

    .success-address p {
        margin: 0;
        font-size: 14px;
        line-height: 1.6;
        color: #555;
    }

    .success-actions {
        display: flex;
        gap: 14px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 8px;
    }

    .success-btn {
        display: inline-block;
        padding: 14px 30px;
        border-radius: 10px;
        font-weight: 700;
        text-decoration: none;
        font-size: 15px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .success-btn--primary {
        background: var(--color-primary, #2e7d32);
        color: #fff;
        box-shadow: 0 6px 16px rgba(46, 125, 50, 0.3);
    }

    .success-btn--secondary {
        background: #fff;
        color: var(--color-text);
        border: 1px solid #ddd;
    }

    .success-btn:hover {
        transform: translateY(-2px);
    }

    .confetti {
        position: absolute;
        top: -20px;
        width: 10px;
        height: 16px;
        opacity: 0.85;
        animation: confetti-fall linear forwards;
        z-index: 1;
    }

    @keyframes confetti-fall {
        to {
            transform: translateY(110vh) rotate(540deg);
            opacity: 0.9;
        }
    }
</style>

<section class="success-section" id="confettiHost">
    <div class="success-wrap">
        <div class="success-hero">
            <div class="success-icon">
                <svg viewBox="0 0 24 24"><polyline points="4 12 10 18 20 6"></polyline></svg>
            </div>
            <h1 class="success-title">Yay! Your order is confirmed 🎉</h1>
            <p class="success-sub">Thank you for shopping with us — your order is being prepared with care.</p>
            <div class="success-order-number">Order #<?php echo htmlspecialchars($order['number']); ?></div>
        </div>

        <div class="success-card">
            <h2>What you ordered</h2>
            <?php foreach ($order['items'] as $item): ?>
                <div class="success-item">
                    <div>
                        <div class="success-item-name">
                            <?php echo htmlspecialchars($item['name']); ?>
                            <?php if (!empty($item['variant_name'])): ?>
                                <span class="success-item-meta">&mdash; <?php echo htmlspecialchars($item['variant_name']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="success-item-meta">Qty <?php echo (int) $item['quantity']; ?> &times; &#8377;<?php echo number_format($item['price'], 2); ?></div>
                    </div>
                    <div class="success-item-price">&#8377;<?php echo number_format($item['subtotal'], 2); ?></div>
                </div>
            <?php endforeach; ?>

            <div class="success-summary-row">
                <span>Subtotal</span>
                <span>&#8377;<?php echo number_format($order['subtotal'], 2); ?></span>
            </div>
            <?php if ($order['discount'] > 0): ?>
                <div class="success-summary-row">
                    <span>Discount</span>
                    <span>&minus;&#8377;<?php echo number_format($order['discount'], 2); ?></span>
                </div>
            <?php endif; ?>
            <div class="success-summary-row">
                <span>Shipping</span>
                <span><?php echo $order['shipping_cost'] > 0 ? '&#8377;' . number_format($order['shipping_cost'], 2) : 'Free'; ?></span>
            </div>
            <?php if ($order['tax'] > 0): ?>
                <div class="success-summary-row">
                    <span>Tax</span>
                    <span>&#8377;<?php echo number_format($order['tax'], 2); ?></span>
                </div>
            <?php endif; ?>
            <div class="success-summary-total">
                <span>Total</span>
                <span>&#8377;<?php echo number_format($order['total'], 2); ?></span>
            </div>
        </div>

        <div class="success-card success-address">
            <h2>Delivering to</h2>
            <p>
                <strong><?php echo htmlspecialchars($order['shipping_name']); ?></strong> &middot; <?php echo htmlspecialchars($order['shipping_phone']); ?><br>
                <?php echo htmlspecialchars($order['shipping_address']); ?><br>
                <?php echo htmlspecialchars($order['shipping_city']); ?>, <?php echo htmlspecialchars($order['shipping_state']); ?> - <?php echo htmlspecialchars($order['shipping_pincode']); ?>, <?php echo htmlspecialchars($order['shipping_country']); ?>
            </p>
            <p style="margin-top:10px;">Payment method: <strong><?php echo htmlspecialchars($order['payment_method']); ?></strong></p>
        </div>

        <div class="success-actions">
            <a href="<?php echo BASE_URL; ?>account/my-orders.php" class="success-btn success-btn--secondary">View My Orders</a>
            <a href="<?php echo BASE_URL; ?>" class="success-btn success-btn--primary">Continue Shopping</a>
        </div>
    </div>
</section>

<script>
    (function () {
        var host = document.getElementById('confettiHost');
        var colors = ['#e74c3c', '#f1c40f', '#2ecc71', '#3498db', '#9b59b6', '#e67e22'];
        var count = 60;

        for (var i = 0; i < count; i++) {
            var piece = document.createElement('div');
            piece.className = 'confetti';
            piece.style.left = Math.random() * 100 + '%';
            piece.style.background = colors[Math.floor(Math.random() * colors.length)];
            piece.style.animationDuration = (2.5 + Math.random() * 2) + 's';
            piece.style.animationDelay = (Math.random() * 1.2) + 's';
            piece.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
            host.appendChild(piece);
        }

        setTimeout(function () {
            document.querySelectorAll('.confetti').forEach(function (el) { el.remove(); });
        }, 6000);
    })();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
