<?php
require_once dirname(__DIR__) . '/includes/db_connect.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'seller') {
    setFlash('Unauthorized access.', 'danger');
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$sellerId = $_SESSION['user']['id'];

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $orderId = (int)$_POST['order_id'];
    $newStatus = trim($_POST['status']);
    $notes = trim($_POST['notes']);

    // Validate if seller owns any product in this order
    $stmtValid = $pdo->prepare('SELECT 1 FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ? AND p.seller_id = ?');
    $stmtValid->execute([$orderId, $sellerId]);
    if ($stmtValid->fetch()) {
        try {
            $pdo->beginTransaction();
            
            // Update order core status
            $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
            $stmt->execute([$newStatus, $orderId]);
            
            // Insert tracking timeline logic
            $stmtTrack = $pdo->prepare('INSERT INTO order_tracking (order_id, status, notes) VALUES (?, ?, ?)');
            $stmtTrack->execute([$orderId, $newStatus, $notes]);
            
            $pdo->commit();
            setFlash('Order workflow successfully updated.', 'success');
        } catch (Exception $e) {
            $pdo->rollBack();
            setFlash('Failed to update order.', 'danger');
        }
    } else {
        setFlash('Unauthorized operation on this order.', 'danger');
    }
    header('Location: ' . BASE_URL . '/seller/orders.php');
    exit;
}

// Fetch all distinct incoming orders containing products from this seller
$stmt = $pdo->prepare('
    SELECT DISTINCT o.*, u.nama as buyer_name, u.email as buyer_email 
    FROM orders o 
    JOIN order_items oi ON o.id = oi.order_id 
    JOIN products p ON oi.product_id = p.id 
    JOIN users u ON o.user_id = u.id 
    WHERE p.seller_id = ? 
    ORDER BY o.created_at DESC
');
$stmt->execute([$sellerId]);
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

require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="container py-5 mt-4" id="sellerOrders">
    <div class="row align-items-center mb-5">
        <div class="col-md-12">
            <a href="<?= BASE_URL ?>/seller/dashboard.php" class="text-decoration-none fw-bold mb-3 d-inline-block" style="color: var(--text-muted);"><i class="fas fa-arrow-left me-2"></i>Seller Hub</a>
            <h1 class="display-4 fw-bold mb-2">Order Management</h1>
            <p style="color: var(--text-muted); font-size: 1.15rem;">Fulfill pending shipments and push updates to buyers.</p>
        </div>
    </div>

    <div class="card-apple p-4" style="background: var(--surface-solid);">
        <?php if(!$orders): ?>
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-3x mb-3" style="color: var(--text-muted); opacity: 0.5;"></i>
                <h4 class="fw-bold">No orders yet.</h4>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle table-borderless">
                    <thead style="border-bottom: 2px solid var(--border-color);">
                        <tr>
                            <th scope="col" style="color: var(--text-muted);">Order ID</th>
                            <th scope="col" style="color: var(--text-muted);">Customer</th>
                            <th scope="col" style="color: var(--text-muted);">Date</th>
                            <th scope="col" style="color: var(--text-muted);">Total</th>
                            <th scope="col" style="color: var(--text-muted);">Status</th>
                            <th scope="col" class="text-end" style="color: var(--text-muted);">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orders as $o): ?>
                        <tr class="border-bottom" style="border-color: var(--border-color) !important;">
                            <td class="fw-bold" style="color: var(--text-main);">#<?= str_pad($o['id'], 6, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <div style="color: var(--text-main); font-weight: 500;"><?= escape($o['buyer_name']) ?></div>
                                <div class="small" style="color: var(--text-muted);"><?= escape($o['buyer_email']) ?></div>
                            </td>
                            <td style="color: var(--text-muted);"><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
                            <td class="fw-bold" style="color: var(--text-main);"><?= formatPrice($o['total_amount']) ?></td>
                            <td>
                                <span class="badge <?= getStatusBadge($o['status']) ?> rounded-pill text-uppercase"><?= escape($o['status']) ?></span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-glass btn-apple" data-mdb-toggle="modal" data-mdb-target="#updateModal_<?= $o['id'] ?>">Update Status</button>
                            </td>
                        </tr>

                        <!-- Update Modal -->
                        <div class="modal fade" id="updateModal_<?= $o['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content glass" style="background: var(--surface-solid);">
                                    <div class="modal-header border-0">
                                        <h5 class="modal-title fw-bold">Update Order #<?= str_pad($o['id'], 6, '0', STR_PAD_LEFT) ?></h5>
                                        <button type="button" class="btn-close" data-mdb-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="<?= BASE_URL ?>/seller/orders.php" method="post">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="order_id" value="<?= escape($o['id']) ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold text-muted">Progress Status</label>
                                                <select class="form-select bg-transparent" name="status" style="color: var(--text-main); border-color: var(--border-color);">
                                                    <option value="pending" <?= $o['status'] === 'pending' ? 'selected' : '' ?>>Pending Validation</option>
                                                    <option value="paid" <?= $o['status'] === 'paid' ? 'selected' : '' ?>>Paid (Processing)</option>
                                                    <option value="shipped" <?= $o['status'] === 'shipped' ? 'selected' : '' ?>>Shipped (In Transit)</option>
                                                    <option value="delivered" <?= $o['status'] === 'delivered' ? 'selected' : '' ?>>Complete (Delivered)</option>
                                                </select>
                                            </div>
                                            <div class="mb-4">
                                                <label class="form-label small fw-bold text-muted">Tracking Notes Push</label>
                                                <textarea class="form-control bg-transparent" name="notes" rows="3" placeholder="E.g., Your package is out for delivery..." style="color: var(--text-main); border-color: var(--border-color);"></textarea>
                                                <div class="form-text small" style="color: var(--text-muted);">The buyer will see this in their Tracking timeline.</div>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-apple w-100">Send Update to Buyer</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        window.animate("#sellerOrders", { opacity: [0, 1], y: [20, 0] }, { duration: 0.6 });
    });
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
