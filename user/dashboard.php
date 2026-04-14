<?php
require_once dirname(__DIR__) . '/includes/header.php';
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Fetch basic stats
$userId = $_SESSION['user']['id'];
$stmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
$stmt->execute([$userId]);
$totalOrders = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM wishlist WHERE user_id = ?');
$stmt->execute([$userId]);
$totalWishlist = $stmt->fetchColumn();

?>
<section class="container py-5 mt-4" id="dashboardSection">
    <div class="row align-items-center mb-5">
        <div class="col-md-9">
            <h1 class="display-4 fw-bold mb-2">Hello, <?= escape(explode(' ', $_SESSION['user']['nama'])[0]) ?></h1>
            <p style="color: var(--text-muted); font-size: 1.15rem;">Manage your profile, orders, and wishlist.</p>
        </div>
        <div class="col-md-3 text-md-end">
            <!-- Become a Seller Hook -->
            <?php if($_SESSION['user']['role'] === 'user'): ?>
            <form action="<?= BASE_URL ?>/upgrade_role.php" method="post">
                <input type="hidden" name="role" value="seller">
                <button type="submit" class="btn btn-outline-primary btn-apple">Become a Seller</button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4 grid-item">
            <div class="card-apple p-4 d-flex flex-column justify-content-between h-100" style="background: var(--surface-solid);">
                <div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-4" style="width: 50px; height: 50px; background: rgba(0, 122, 255, 0.1); color: var(--primary-color); font-size: 1.2rem;">
                        <i class="fas fa-box"></i>
                    </div>
                    <h5 class="fw-bold fs-5">Order History</h5>
                    <p class="small" style="color: var(--text-muted);">Track, return, or buy items again. (<?= $totalOrders ?> orders)</p>
                </div>
                <a href="<?= BASE_URL ?>/user/orders.php" class="btn btn-primary btn-apple w-100 mt-4">View Orders</a>
            </div>
        </div>

        <div class="col-md-4 grid-item">
            <div class="card-apple p-4 d-flex flex-column justify-content-between h-100" style="background: var(--surface-solid);">
                <div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-4" style="width: 50px; height: 50px; background: rgba(255, 45, 85, 0.1); color: #ff2d55; font-size: 1.2rem;">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h5 class="fw-bold fs-5">Your Wishlist</h5>
                    <p class="small" style="color: var(--text-muted);">View saved items and move them to your bag. (<?= $totalWishlist ?> items)</p>
                </div>
                <a href="<?= BASE_URL ?>/user/wishlist.php" class="btn btn-glass w-100 mt-4" style="border-radius: 99px;">View Wishlist</a>
            </div>
        </div>

        <div class="col-md-4 grid-item">
            <div class="card-apple p-4 d-flex flex-column justify-content-between h-100" style="background: var(--surface-solid);">
                <div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-4" style="width: 50px; height: 50px; background: rgba(52, 199, 89, 0.1); color: #34c759; font-size: 1.2rem;">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <h5 class="fw-bold fs-5">Profile Settings</h5>
                    <p class="small" style="color: var(--text-muted);">Update generic settings and addresses.</p>
                </div>
                <a href="<?= BASE_URL ?>/user/profile.php" class="btn btn-glass w-100 mt-4" style="border-radius: 99px;">Edit Settings</a>
            </div>
        </div>
    </div>
</section>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        window.animate("#dashboardSection", { opacity: [0, 1], y: [20, 0] }, { duration: 0.6 });
        window.inView(".grid-item", (info) => {
            window.animate(info.target, { opacity: [0, 1], scale: [0.95, 1] }, { delay: window.stagger(0.1), duration: 0.5 });
        });
    });
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
