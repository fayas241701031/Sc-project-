<?php
require_once dirname(__DIR__) . '/includes/header.php';
if (!isLoggedIn() || $_SESSION['user']['role'] !== 'seller') {
    setFlash('Unauthorized access.', 'danger');
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$sellerId = $_SESSION['user']['id'];

// Analytics mapping
// Total products
$stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE seller_id = ?');
$stmt->execute([$sellerId]);
$totalProducts = $stmt->fetchColumn();

// Total Orders where items belong to seller
$stmt = $pdo->prepare('SELECT COUNT(DISTINCT o.id) FROM orders o JOIN order_items oi ON oi.order_id = o.id JOIN products p ON oi.product_id = p.id WHERE p.seller_id = ?');
$stmt->execute([$sellerId]);
$totalOrders = $stmt->fetchColumn();

// Total Revenue
$stmt = $pdo->prepare('SELECT SUM(oi.price * oi.quantity) FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE p.seller_id = ?');
$stmt->execute([$sellerId]);
$totalRevenue = $stmt->fetchColumn() ?: 0;
?>

<section class="container py-5 mt-4" id="sellerDash">
    <div class="row align-items-center mb-5">
        <div class="col-md-9">
            <h1 class="display-4 fw-bold mb-2">Seller Hub</h1>
            <p style="color: var(--text-muted); font-size: 1.15rem;">Manage your products, track orders, and view performance.</p>
        </div>
        <div class="col-md-3 text-md-end">
            <a href="<?= BASE_URL ?>/seller/products.php" class="btn btn-primary btn-apple">Manage Products</a>
        </div>
    </div>

    <!-- Analytics Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card-apple p-4 d-flex flex-column h-100" style="background: var(--surface-solid);">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(52, 199, 89, 0.1); color: #34c759; font-size: 1.2rem;">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h5 class="fw-bold m-0" style="color: var(--text-muted);">Total Revenue</h5>
                </div>
                <h2 class="display-5 fw-bold mt-auto" style="color: var(--text-main);"><?= formatPrice($totalRevenue) ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <a href="<?= BASE_URL ?>/seller/orders.php" class="text-decoration-none">
                <div class="card-apple p-4 d-flex flex-column h-100" style="background: var(--surface-solid);">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(0, 122, 255, 0.1); color: var(--primary-color); font-size: 1.2rem;">
                            <i class="fas fa-box"></i>
                        </div>
                        <h5 class="fw-bold m-0" style="color: var(--text-muted);">Total Orders</h5>
                    </div>
                    <div class="d-flex justify-content-between align-items-end mt-auto">
                        <h2 class="display-5 fw-bold m-0" style="color: var(--text-main);"><?= $totalOrders ?></h2>
                        <i class="fas fa-arrow-right" style="color: var(--primary-color);"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?= BASE_URL ?>/seller/products.php" class="text-decoration-none">
                <div class="card-apple p-4 d-flex flex-column h-100" style="background: var(--surface-solid);">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(255, 149, 0, 0.1); color: #ff9500; font-size: 1.2rem;">
                            <i class="fas fa-tags"></i>
                        </div>
                        <h5 class="fw-bold m-0" style="color: var(--text-muted);">Active Products</h5>
                    </div>
                    <div class="d-flex justify-content-between align-items-end mt-auto">
                        <h2 class="display-5 fw-bold m-0" style="color: var(--text-main);"><?= $totalProducts ?></h2>
                        <i class="fas fa-arrow-right" style="color: var(--primary-color);"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>
</section>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        window.animate("#sellerDash", { opacity: [0, 1], y: [20, 0] }, { duration: 0.6 });
        window.animate(".card-apple", { opacity: [0, 1], scale: [0.95, 1] }, { delay: window.stagger(0.1), duration: 0.5 });
    });
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
