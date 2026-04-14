<?php
require_once __DIR__ . '/includes/header.php';
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

if (empty($_GET['order_id'])) {
    setFlash('Invalid Tracking ID.', 'danger');
    header('Location: ' . BASE_URL . '/user/orders.php');
    exit;
}

$orderId = (int)$_GET['order_id'];
$userId = $_SESSION['user']['id'];

// Fetch order
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    setFlash('Order not found or unauthorized.', 'danger');
    header('Location: ' . BASE_URL . '/user/orders.php');
    exit;
}

// Fetch items
$stmt = $pdo->prepare('SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

// Fetch tracking details (chronological)
$stmt = $pdo->prepare('SELECT * FROM order_tracking WHERE order_id = ? ORDER BY created_at ASC');
$stmt->execute([$orderId]);
$timeline = $stmt->fetchAll();

// Icon mapping
$statusIcons = [
    'pending' => 'box',
    'paid' => 'check-circle',
    'shipped' => 'truck',
    'delivered' => 'home'
];
?>

<style>
/* Apple-style Timeline CSS */
.timeline-track { position: relative; padding-left: 30px; margin-bottom: 2rem; }
.timeline-track::before {
    content: '';
    position: absolute;
    left: 14px;
    top: 5px;
    bottom: 5px;
    width: 2px;
    background: var(--border-color);
    z-index: 1;
}
.timeline-item { position: relative; margin-bottom: 2rem; }
.timeline-item:last-child { margin-bottom: 0; }
.timeline-icon {
    position: absolute;
    left: -30px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: var(--surface-solid);
    border: 2px solid var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
    color: var(--primary-color);
    font-size: 0.8rem;
    box-shadow: var(--glass-shadow);
}
.timeline-item.completed .timeline-icon {
    background: var(--primary-color);
    color: white;
}
.timeline-item.active .timeline-icon {
    background: var(--surface-solid);
    border: 4px solid var(--primary-color);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(0, 122, 255, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(0, 122, 255, 0); }
    100% { box-shadow: 0 0 0 0 rgba(0, 122, 255, 0); }
}
</style>

<section class="container py-5 mt-4" id="trackingSection">
    <div class="mb-5">
        <a href="<?= BASE_URL ?>/user/orders.php" class="text-decoration-none fw-bold" style="color: var(--text-muted);"><i class="fas fa-arrow-left me-2"></i>Back to Orders</a>
    </div>

    <div class="row g-5">
        <!-- Timeline Logic -->
        <div class="col-lg-7">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold m-0 text-capitalize">Tracking <span style="color: var(--text-muted);">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></span></h2>
                <span class="badge badge-apple fs-6 text-uppercase"><?= escape($order['status']) ?></span>
            </div>
            
            <div class="card-apple p-5 mb-5" style="background: var(--surface-solid);">
                <div class="timeline-track">
                    <?php 
                        $totalStages = count($timeline);
                        foreach($timeline as $index => $track): 
                            $isLast = ($index === $totalStages - 1);
                            $iconCode = $statusIcons[$track['status']] ?? 'dot-circle';
                            $activityState = $isLast ? 'active' : 'completed';
                    ?>
                    <div class="timeline-item <?= $activityState ?>">
                        <div class="timeline-icon">
                            <i class="fas fa-<?= $iconCode ?>"></i>
                        </div>
                        <div class="ps-3">
                            <h5 class="fw-bold mb-1 text-capitalize" style="color: var(--text-main);"><?= escape($track['status']) ?></h5>
                            <p class="small mb-2 fw-bold" style="color: var(--text-muted);"><?= date('M j, Y, g:i A', strtotime($track['created_at'])) ?></p>
                            <p style="color: var(--text-muted); font-size: 0.95rem;"><?= escape($track['notes']) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if($order['status'] !== 'delivered'): ?>
                    <div class="timeline-item">
                        <div class="timeline-icon" style="border-color: var(--border-color); color: var(--border-color);">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="ps-3">
                            <h5 class="fw-bold mb-1" style="color: var(--text-muted); opacity: 0.5;">Delivered</h5>
                            <p style="color: var(--text-muted); opacity: 0.5; font-size: 0.95rem;">Expected in 3-5 business days</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="col-lg-5">
            <div class="card-apple p-4 position-sticky" style="top: 100px; background: var(--surface);">
                <h5 class="fw-bold mb-4">Items inside</h5>
                
                <div style="max-height: 400px; overflow-y: auto;" class="pe-2 mb-4">
                    <?php foreach ($items as $item): ?>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-white rounded p-1 shadow-sm d-flex justify-content-center align-items-center" style="width: 70px; height: 70px;">
                                <img src="<?= escape($item['image']) ?>" alt="<?= escape($item['name']) ?>" class="img-fluid rounded skeleton" style="max-height: 60px; object-fit: contain;" onload="this.classList.remove('skeleton')" onerror="this.classList.remove('skeleton')">
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold m-0" style="font-size: 0.95rem;"><?= escape($item['name']) ?></h6>
                                <div class="text-muted small">Qty: <?= escape($item['quantity']) ?> &middot; <?= formatPrice($item['price']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <hr style="border-color: var(--border-color); opacity: 1;">
                
                <div class="d-flex justify-content-between align-items-center my-3">
                    <span class="fs-6 fw-bold" style="color: var(--text-muted);">Total Paid</span>
                    <strong class="fs-5 fw-bold" style="color: var(--text-main);"><?= formatPrice($order['total_amount']) ?></strong>
                </div>

                <?php if($order['status'] === 'delivered'): ?>
                    <button class="btn btn-outline-primary btn-apple w-100 mt-2">Write a Review</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        window.animate("#trackingSection", { opacity: [0, 1], y: [20, 0] }, { duration: 0.6, easing: [0.16, 1, 0.3, 1] });
        
        window.inView(".timeline-item", (info) => {
            window.animate(info.target, { opacity: [0, 1], x: [-20, 0] }, { duration: 0.5, delay: 0.2 });
        });
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
