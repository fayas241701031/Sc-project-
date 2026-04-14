<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $productId = (int)$_POST['product_id'];
        $quantity = max(1, (int)$_POST['quantity']);
        $stmt = $pdo->prepare('SELECT id, name, price, stock, image FROM products WHERE id = :id');
        $stmt->execute([':id' => $productId]);
        $product = $stmt->fetch();
        if ($product) {
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$productId] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'image' => $product['image'],
                    'quantity' => $quantity,
                    'stock' => $product['stock'],
                ];
            }
            setFlash('Product added to cart.', 'success');
            header('Location: ' . BASE_URL . '/cart.php');
            exit;
        }
    }
    if ($action === 'update') {
        foreach ($_POST['quantity'] as $productId => $qty) {
            $qty = max(1, (int)$qty);
            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId]['quantity'] = $qty;
            }
        }
        setFlash('Cart updated successfully.','success');
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }
    if ($action === 'remove') {
        $productId = (int)$_POST['product_id'];
        unset($_SESSION['cart'][$productId]);
        setFlash('Item removed from cart.','warning');
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }
}

$cart = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cart as $item) {
    if (is_numeric($item['price']) && is_numeric($item['quantity'])) {
        $total += $item['price'] * $item['quantity'];
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="container py-5 mt-4" id="cartSection">
    <div class="row mb-5 align-items-center">
        <div class="col-md-12 text-center text-md-start">
            <h1 class="display-4 fw-bold mb-2">Your Bag</h1>
            <p style="color: var(--text-muted); font-size: 1.15rem;">Review your items before checkout.</p>
        </div>
    </div>

    <div class="row g-5">
        <div class="col-lg-8">
            <div class="card-apple p-4" style="background: var(--surface);">
                <?php if (!$cart): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-bag fa-3x mb-3" style="color: var(--text-muted); opacity: 0.5;"></i>
                        <h4 class="fw-bold">Your bag is empty.</h4>
                        <p style="color: var(--text-muted);">Explore our marketplace to find premium items.</p>
                        <a href="<?= BASE_URL ?>/shop.php" class="btn btn-primary btn-apple mt-3 px-4">Continue Shopping</a>
                    </div>
                <?php else: ?>
                    <form action="<?= BASE_URL ?>/cart.php" method="post" id="cartForm">
                        <input type="hidden" name="action" value="update">
                        <?php foreach ($cart as $item): ?>
                            <div class="d-flex align-items-center gap-4 py-4 border-bottom" style="border-color: var(--border-color) !important;">
                                <div class="bg-white rounded-4 p-2 shadow-sm d-flex justify-content-center align-items-center" style="width: 120px; height: 120px;">
                                    <img src="<?= escape($item['image']) ?>" alt="<?= escape($item['name']) ?>" class="img-fluid rounded skeleton" style="max-height: 100px; object-fit: contain;" onload="this.classList.remove('skeleton')" onerror="this.classList.remove('skeleton')">
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="fw-bold m-0"><?= escape($item['name']) ?></h5>
                                        <h5 class="fw-bold m-0 text-end"><?= formatPrice($item['price'] * $item['quantity']) ?></h5>
                                    </div>
                                    <p class="mb-3" style="color: var(--text-muted);"><?= formatPrice($item['price']) ?> each</p>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="bg-transparent border rounded-pill d-flex align-items-center px-3" style="border-color: var(--border-color) !important; width: fit-content;">
                                            <button type="button" class="btn btn-link px-2 py-1 shadow-0 text-decoration-none" style="color: var(--text-main);" onclick="this.parentNode.querySelector('input[type=number]').stepDown(); document.getElementById('updateCartBtn').click();">-</button>
                                            <input type="number" class="form-control text-center bg-transparent border-0 shadow-none fw-bold p-0" name="quantity[<?= escape($item['id']) ?>]" value="<?= escape($item['quantity']) ?>" min="1" max="<?= escape($item['stock']) ?>" style="width: 40px; color: var(--text-main); appearance: none; -moz-appearance: textfield;">
                                            <button type="button" class="btn btn-link px-2 py-1 shadow-0 text-decoration-none" style="color: var(--text-main);" onclick="this.parentNode.querySelector('input[type=number]').stepUp(); document.getElementById('updateCartBtn').click();">+</button>
                                        </div>
                                        
                                        <button type="button" class="btn btn-sm btn-link text-danger fw-bold text-decoration-none" onclick="document.getElementById('remove_<?= $item['id'] ?>').submit()">Remove</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <!-- Hidden global update button triggered by JS stepUp/stepDown -->
                        <button type="submit" id="updateCartBtn" class="d-none">Update</button>
                    </form>
                    
                    <!-- Hidden remove forms -->
                    <?php foreach ($cart as $item): ?>
                    <form action="<?= BASE_URL ?>/cart.php" method="post" id="remove_<?= $item['id'] ?>">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="product_id" value="<?= escape($item['id']) ?>">
                    </form>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card-apple p-4 position-sticky" style="top: 100px; background: var(--surface-solid);">
                <h4 class="fw-bold mb-4">Summary</h4>
                <div class="d-flex justify-content-between mb-3">
                    <span style="color: var(--text-muted);">Subtotal</span>
                    <strong style="color: var(--text-main);"><?= formatPrice($total) ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span style="color: var(--text-muted);">Shipping</span>
                    <span class="badge" style="background: rgba(52, 199, 89, 0.1); color: #34c759; border-radius: 99px;">Free</span>
                </div>
                
                <hr class="my-4" style="border-color: var(--border-color); opacity: 1;">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="fs-5 fw-bold" style="color: var(--text-main);">Total</span>
                    <strong class="fs-4 fw-bold" style="color: var(--text-main);"><?= formatPrice($total) ?></strong>
                </div>
                <a href="<?= BASE_URL ?>/checkout.php" class="btn btn-primary btn-apple btn-lg w-100 py-3 <?= $cart ? '' : 'disabled' ?>">Checkout</a>
            </div>
        </div>
    </div>
</section>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        window.animate("#cartSection", { opacity: [0, 1], y: [20, 0] }, { duration: 0.6, easing: [0.16, 1, 0.3, 1] });
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
