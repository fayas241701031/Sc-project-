<?php
require_once dirname(__DIR__) . '/includes/db_connect.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$userId = $_SESSION['user']['id'];

// Handle Remove logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_wishlist') {
    $productId = (int)$_POST['product_id'];
    $stmt = $pdo->prepare('DELETE FROM wishlist WHERE user_id = ? AND product_id = ?');
    $stmt->execute([$userId, $productId]);
    setFlash('Item removed from wishlist.', 'warning');
    header('Location: ' . BASE_URL . '/user/wishlist.php');
    exit;
}

// Handle Add logic (often reached from product.php, assuming we put a heart icon)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_wishlist') {
    $productId = (int)$_POST['product_id'];
    $stmt = $pdo->prepare('INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)');
    $stmt->execute([$userId, $productId]);
    setFlash('Item added to wishlist!', 'success');
    header('Location: ' . BASE_URL . '/user/wishlist.php');
    exit;
}

$stmt = $pdo->prepare('SELECT p.* FROM products p JOIN wishlist w ON p.id = w.product_id WHERE w.user_id = ? ORDER BY w.created_at DESC');
$stmt->execute([$userId]);
$wishlist = $stmt->fetchAll();

require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="container py-5 mt-4" id="wishlistSection">
    <div class="row mb-5 align-items-center">
        <div class="col-md-8">
            <a href="<?= BASE_URL ?>/user/dashboard.php" class="text-decoration-none fw-bold mb-3 d-inline-block" style="color: var(--text-muted);"><i class="fas fa-arrow-left me-2"></i>Dashboard</a>
            <h1 class="display-4 fw-bold mb-2">Wishlist</h1>
            <p style="color: var(--text-muted); font-size: 1.15rem;">Items you've saved for later.</p>
        </div>
    </div>

    <div class="row g-4 wishlist-grid">
        <?php if (!$wishlist): ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-heart-broken fa-3x mb-3" style="color: var(--text-muted); opacity: 0.5;"></i>
                <h4 class="fw-bold">Your wishlist is empty.</h4>
                <p style="color: var(--text-muted);">Save interesting products here while browsing.</p>
                <a href="<?= BASE_URL ?>/shop.php" class="btn btn-outline-primary btn-apple mt-3">Explore Marketplace</a>
            </div>
        <?php else: ?>
            <?php foreach ($wishlist as $item): ?>
                <div class="col-md-3 col-sm-6 wishlist-item">
                    <div class="card-apple d-flex flex-column position-relative h-100">
                        <form action="<?= BASE_URL ?>/user/wishlist.php" method="post" class="position-absolute" style="top: 10px; right: 10px; z-index: 5;">
                            <input type="hidden" name="action" value="remove_wishlist">
                            <input type="hidden" name="product_id" value="<?= escape($item['id']) ?>">
                            <button type="submit" class="btn btn-light rounded-circle shadow-sm" style="width: 40px; height: 40px;"><i class="fas fa-times text-danger"></i></button>
                        </form>
                        
                        <img src="<?= escape($item['image']) ?>" class="card-img-top w-100 p-3 skeleton" alt="<?= escape($item['name']) ?>" style="height: 250px; object-fit: contain;" onload="this.classList.remove('skeleton')" onerror="this.classList.remove('skeleton')">
                        
                        <div class="p-4 d-flex flex-column flex-grow-1">
                            <h5 class="fw-bold mb-1 fs-6"><?= escape($item['name']) ?></h5>
                            <p class="small mb-3" style="color: var(--text-muted);"><?= substr(escape($item['description']), 0, 50) ?>...</p>
                            
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <strong class="fs-5"><?= formatPrice($item['price']) ?></strong>
                                <form action="<?= BASE_URL ?>/cart.php" method="post">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?= escape($item['id']) ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary btn-apple btn-sm px-3 rounded-pill" <?= $item['stock']==0 ? 'disabled' : '' ?>>Add to Bag</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        window.animate("#wishlistSection", { opacity: [0, 1], y: [20, 0] }, { duration: 0.6 });
        window.inView(".wishlist-item", (info) => {
            window.animate(info.target, { opacity: [0, 1], scale: [0.95, 1] }, { delay: window.stagger(0.1), duration: 0.5 });
        });
    });
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
