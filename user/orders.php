<?php
require_once dirname(__DIR__) . '/includes/header.php';
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$stmt = $pdo->prepare('
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count,
           (SELECT name FROM products p JOIN order_items oi ON oi.product_id = p.id WHERE oi.order_id = o.id LIMIT 1) as first_item_name
    FROM orders o 
    WHERE user_id = :user_id 
    ORDER BY created_at DESC
');
$stmt->execute([':user_id' => $_SESSION['user']['id']]);
$orders = $stmt->fetchAll();

function getStatusBadge($status) {
    switch ($status) {
        case 'pending': return 'bg-warning text-dark';
        case 'paid': return 'bg-info text-white';
        case 'shipped': return 'bg-primary text-white';
        case 'delivered': return 'bg-success text-white';
        default: return 'bg-secondary text-white';
    }
}
?>

<section class="container py-5 mt-4" id="ordersSection">
    <div class="row mb-5 align-items-center">
        <div class="col-md-8">
            <h1 class="display-4 fw-bold mb-2">Order History</h1>
            <p style="color: var(--text-muted); font-size: 1.15rem;">Track, return, or buy items again.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="<?= BASE_URL ?>/shop.php" class="btn btn-outline-primary btn-apple">Continue Shopping</a>
        </div>
    </div>

    <div class="row g-4">
        <?php if (!$orders): ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-box-open fa-3x mb-3" style="color: var(--text-muted); opacity: 0.5;"></i>
                <h4 class="fw-bold">No orders found.</h4>
                <p style="color: var(--text-muted);">When you buy something, it will appear here.</p>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="col-md-6 col-lg-4 order-card">
                    <div class="card-apple p-4 d-flex flex-column h-100" style="background: var(--surface-solid);">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold" style="color: var(--text-muted); font-size: 0.9rem;">Order #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></span>
                            <span class="badge rounded-pill <?= getStatusBadge($order['status']) ?> text-uppercase"><?= escape($order['status']) ?></span>
                        </div>
                        
                        <h5 class="fw-bold mb-1 fs-6 text-truncate"><?= escape($order['first_item_name']) ?></h5>
                        <?php if($order['item_count'] > 1): ?>
                            <p class="small text-muted mb-3">+ <?= $order['item_count'] - 1 ?> other item(s)</p>
                        <?php else: ?>
                            <p class="small text-muted mb-3">1 item</p>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between align-items-end mb-4 flex-grow-1">
                            <div>
                                <p class="small m-0 fw-bold" style="color: var(--text-muted); text-transform: uppercase;">Total</p>
                                <strong class="fs-5" style="color: var(--text-main);"><?= formatPrice($order['total_amount']) ?></strong>
                            </div>
                            <div class="text-end">
                                <p class="small m-0" style="color: var(--text-muted);"><?= date('M j, Y', strtotime($order['created_at'])) ?></p>
                            </div>
                        </div>
                        
                        <a href="<?= BASE_URL ?>/tracking.php?order_id=<?= $order['id'] ?>" class="btn btn-primary btn-apple w-100 mt-auto">Track Package</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        window.animate("#ordersSection > .row:first-child", { opacity: [0, 1], y: [20, 0] }, { duration: 0.6 });
        window.inView(".order-card", (info) => {
            window.animate(info.target, { opacity: [0, 1], scale: [0.95, 1] }, { duration: 0.5 });
        });
    });
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>