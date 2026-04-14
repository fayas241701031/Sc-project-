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

// Handle Add Product Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $catId = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    
    // Image Upload Logic
    $imagePath = 'https://via.placeholder.com/600x400';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $filename = uniqid('prod_') . '.' . $ext;
            $destination = dirname(__DIR__) . '/assets/img/' . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $imagePath = BASE_URL . '/assets/img/' . $filename;
            }
        }
    }

    $stmt = $pdo->prepare('INSERT INTO products (name, description, category_id, price, stock, image, seller_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$name, $desc, $catId, $price, $stock, $imagePath, $sellerId]);
    setFlash('Product published successfully.', 'success');
    header('Location: ' . BASE_URL . '/seller/products.php');
    exit;
}

// Handle Delete Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $prodId = (int)$_POST['id'];
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ? AND seller_id = ?');
    $stmt->execute([$prodId, $sellerId]);
    setFlash('Product deleted.', 'warning');
    header('Location: ' . BASE_URL . '/seller/products.php');
    exit;
}

$stmt = $pdo->query('SELECT * FROM categories');
$categories = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.seller_id = ? ORDER BY p.created_at DESC');
$stmt->execute([$sellerId]);
$products = $stmt->fetchAll();

require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="container py-5 mt-4" id="sellerProducts">
    <div class="row align-items-center mb-5">
        <div class="col-md-8">
            <a href="<?= BASE_URL ?>/seller/dashboard.php" class="text-decoration-none fw-bold mb-3 d-inline-block" style="color: var(--text-muted);"><i class="fas fa-arrow-left me-2"></i>Seller Hub</a>
            <h1 class="display-4 fw-bold mb-2">My Products</h1>
        </div>
        <div class="col-md-4 text-md-end">
            <button class="btn btn-primary btn-apple" data-mdb-toggle="modal" data-mdb-target="#addProductModal"><i class="fas fa-plus me-2"></i>New Product</button>
        </div>
    </div>

    <div class="card-apple p-4" style="background: var(--surface-solid);">
        <?php if(!$products): ?>
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-3x mb-3" style="color: var(--text-muted); opacity: 0.5;"></i>
                <h4 class="fw-bold">No products yet.</h4>
                <p style="color: var(--text-muted);">List your first product to start earning.</p>
                <button class="btn btn-outline-primary btn-apple mt-3" data-mdb-toggle="modal" data-mdb-target="#addProductModal">Add Product</button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle text-center table-borderless">
                    <thead style="border-bottom: 2px solid var(--border-color);">
                        <tr>
                            <th scope="col" style="color: var(--text-muted);">Item</th>
                            <th scope="col" style="color: var(--text-muted);">Category</th>
                            <th scope="col" style="color: var(--text-muted);">Price</th>
                            <th scope="col" style="color: var(--text-muted);">Stock</th>
                            <th scope="col" style="color: var(--text-muted);">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($products as $p): ?>
                        <tr class="border-bottom" style="border-color: var(--border-color) !important;">
                            <td class="text-start py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-white rounded d-flex align-items-center justify-content-center p-1" style="width: 50px; height: 50px;">
                                        <img src="<?= escape($p['image']) ?>" alt="<?= escape($p['name']) ?>" class="skeleton" style="max-height: 40px; object-fit: contain;" onload="this.classList.remove('skeleton')" onerror="this.classList.remove('skeleton')">
                                    </div>
                                    <div class="fw-bold" style="color: var(--text-main); font-size: 0.95rem;"><?= escape($p['name']) ?></div>
                                </div>
                            </td>
                            <td style="color: var(--text-muted);"><?= escape($p['cat_name']) ?></td>
                            <td class="fw-bold" style="color: var(--text-main);"><?= formatPrice($p['price']) ?></td>
                            <td>
                                <span class="badge <?= $p['stock'] > 0 ? 'bg-success' : 'bg-danger' ?> bg-opacity-25 <?= $p['stock'] > 0 ? 'text-success' : 'text-danger' ?> rounded-pill px-3">
                                    <?= $p['stock'] ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2 justify-content-center">
                                    <form action="<?= BASE_URL ?>/seller/products.php" method="post" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= escape($p['id']) ?>">
                                        <button type="submit" class="btn btn-sm btn-link text-danger m-0 p-1"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content glass" style="background: var(--surface-solid);">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Create Product Listing</h5>
                <button type="button" class="btn-close" data-mdb-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="<?= BASE_URL ?>/seller/products.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-muted">Product Name</label>
                            <input type="text" class="form-control bg-transparent" name="name" required style="color: var(--text-main); border-color: var(--border-color);">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-muted">Description</label>
                            <textarea class="form-control bg-transparent" name="description" rows="3" required style="color: var(--text-main); border-color: var(--border-color);"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Category</label>
                            <select class="form-select bg-transparent" name="category_id" required style="color: var(--text-main); border-color: var(--border-color);">
                                <?php foreach($categories as $c): ?>
                                    <option value="<?= escape($c['id']) ?>" style="background: var(--surface-solid); color: var(--text-main);"><?= escape($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Price (₹)</label>
                            <input type="number" step="0.01" class="form-control bg-transparent" name="price" required style="color: var(--text-main); border-color: var(--border-color);">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Stock Quantity</label>
                            <input type="number" class="form-control bg-transparent" name="stock" required style="color: var(--text-main); border-color: var(--border-color);">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-muted">Product Image</label>
                            <input type="file" class="form-control bg-transparent" name="image" accept="image/*" required style="color: var(--text-main); border-color: var(--border-color);">
                            <div class="form-text small" style="color: var(--text-muted);">Max size 5MB.</div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-apple w-100 mt-4">Publish Product</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        window.animate("#sellerProducts", { opacity: [0, 1], y: [20, 0] }, { duration: 0.6 });
    });
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
