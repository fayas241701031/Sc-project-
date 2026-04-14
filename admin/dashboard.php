<?php
require_once dirname(__DIR__) . '/includes/db_connect.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isLoggedIn() || $_SESSION['user']['role'] !== 'admin') {
    setFlash('Unauthorized access. Admin privileges required.', 'danger');
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// --- POST HANDLERS ---
// 1. User Role Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_role') {
    $targetUserId = (int)$_POST['user_id'];
    $newRole = $_POST['role'];
    if (in_array($newRole, ['user', 'seller', 'admin'])) {
        $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stmt->execute([$newRole, $targetUserId]);
        setFlash('User role updated successfully.', 'success');
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
        exit;
    }
}

// 2. Product Moderation (Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_product') {
    $prodId = (int)$_POST['product_id'];
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$prodId]);
    setFlash('Product permanently removed.', 'warning');
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

// 3. Category Management
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_category') {
        $catName = trim($_POST['cat_name']);
        if ($catName) {
            $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
            $stmt->execute([$catName]);
            setFlash('Category created.', 'success');
        }
    }
    if ($_POST['action'] === 'delete_category') {
        $catId = (int)$_POST['category_id'];
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$catId]);
        setFlash('Category deleted.', 'warning');
    }
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

// 4. Order Status Update (Admin override)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_order_status') {
    $orderId = (int)$_POST['order_id'];
    $newStatus = trim($_POST['status']);
    $notes = trim($_POST['notes']);
    
    try {
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$newStatus, $orderId]);
        $pdo->prepare('INSERT INTO order_tracking (order_id, status, notes) VALUES (?, ?, ?)')->execute([$orderId, $newStatus, $notes]);
        $pdo->commit();
        setFlash('Global order status updated.', 'success');
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('Error updating order.', 'danger');
    }
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

// 5. Verification Review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'review_verification') {
    $requestId = (int)$_POST['request_id'];
    $status = $_POST['status'];
    $userId = (int)$_POST['user_id'];
    
    $stmt = $pdo->prepare('UPDATE verification_requests SET status = ? WHERE id = ?');
    $stmt->execute([$status, $requestId]);
    
    if ($status === 'approved') {
        $pdo->prepare('UPDATE users SET is_verified_seller = 1 WHERE id = ?')->execute([$userId]);
        setFlash('Seller verified successfully.', 'success');
    } else {
        $pdo->prepare('UPDATE users SET is_verified_seller = 0 WHERE id = ?')->execute([$userId]);
        setFlash('Verification request rejected.', 'warning');
    }
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

// --- FETCH DATA ---
$totalUsers = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalSellers = $pdo->query('SELECT COUNT(*) FROM users WHERE role="seller"')->fetchColumn();
$totalProducts = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalRevenue = $pdo->query('SELECT SUM(total_amount) FROM orders')->fetchColumn() ?: 0;

$users = $pdo->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
$allProducts = $pdo->query('SELECT p.*, c.name as cat_name, u.nama as seller_name FROM products p JOIN categories c ON p.category_id = c.id JOIN users u ON p.seller_id = u.id ORDER BY p.created_at DESC')->fetchAll();
$categories = $pdo->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();
$allOrders = $pdo->query('SELECT o.*, u.nama as buyer_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC')->fetchAll();
$verificationRequests = $pdo->query('SELECT vr.*, u.nama, u.email FROM verification_requests vr JOIN users u ON vr.user_id = u.id WHERE vr.status = "pending" ORDER BY vr.created_at ASC')->fetchAll();

function getAdminBadge($status) {
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

<style>
    .admin-tab-btn { border-radius: 12px; padding: 12px 24px; transition: all 0.3s; color: var(--text-muted); border: none; background: transparent; }
    .admin-tab-btn.active { background: var(--primary-color) !important; color: white !important; box-shadow: 0 4px 12px rgba(0, 122, 255, 0.3); }
    .admin-tab-btn:hover:not(.active) { background: rgba(120, 120, 128, 0.1); }
</style>

<section class="container py-5 mt-4" id="adminDashboardSection">
    <div class="row align-items-center mb-5">
        <div class="col-md-9">
            <h1 class="display-4 fw-bold mb-2">Bazaar <span style="color: var(--primary-color);">Command</span> Center</h1>
            <p style="color: var(--text-muted); font-size: 1.15rem;">Manage ecosystems, users, products, and global logistics.</p>
        </div>
        <div class="col-md-3 text-md-end">
            <a href="<?= BASE_URL ?>/admin/add_product.php" class="btn btn-primary btn-apple">Add Global Product</a>
        </div>
    </div>

    <!-- STATS -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card-apple p-4 d-flex flex-column h-100" style="background: var(--surface-solid);">
                <span class="small fw-bold text-uppercase mb-2" style="color: var(--text-muted);">Platform Revenue</span>
                <h3 class="fw-bold m-0" style="color: var(--text-main);"><?= formatPrice($totalRevenue) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-apple p-4 d-flex flex-column h-100" style="background: var(--surface-solid);">
                <span class="small fw-bold text-uppercase mb-2" style="color: var(--text-muted);">Active Products</span>
                <h3 class="fw-bold m-0" style="color: var(--text-main);"><?= $totalProducts ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-apple p-4 d-flex flex-column h-100" style="background: var(--surface-solid);">
                <span class="small fw-bold text-uppercase mb-2" style="color: var(--text-muted);">Verified Vendors</span>
                <h3 class="fw-bold m-0" style="color: var(--text-main);"><?= $totalSellers ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-apple p-4 d-flex flex-column h-100" style="background: var(--surface-solid);">
                <span class="small fw-bold text-uppercase mb-2" style="color: var(--text-muted);">Global Users</span>
                <h3 class="fw-bold m-0" style="color: var(--text-main);"><?= $totalUsers ?></h3>
            </div>
        </div>
    </div>

    <!-- NAVIGATION TABS -->
    <div class="bg-white rounded-4 shadow-sm p-2 mb-5 d-flex gap-2 flex-wrap" id="adminTabs" style="width: fit-content; margin: 0 auto; background: var(--surface-solid) !important;">
        <button class="admin-tab-btn active" data-target="tab-users">Users</button>
        <button class="admin-tab-btn" data-target="tab-products">Products</button>
        <button class="admin-tab-btn" data-target="tab-categories">Categories</button>
        <button class="admin-tab-btn" data-target="tab-orders">Orders</button>
        <button class="admin-tab-btn" data-target="tab-verifications">Verifications <span class="badge bg-danger rounded-pill"><?= count($verificationRequests) ?></span></button>
    </div>

    <div id="adminTabContent">
        <!-- USERS TAB -->
        <div class="admin-pane active" id="tab-users">
            <div class="card-apple p-4" style="background: var(--surface-solid);">
                <h5 class="fw-bold mb-4">User Management</h5>
                <div class="table-responsive">
                    <table class="table align-middle table-borderless">
                        <thead style="border-bottom: 2px solid var(--border-color);">
                            <tr>
                                <th class="text-muted">User</th>
                                <th class="text-muted">Role</th>
                                <th class="text-muted">Joined</th>
                                <th class="text-muted text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $u): ?>
                            <tr class="border-bottom" style="border-color: var(--border-color) !important;">
                                <td class="py-3">
                                    <div class="fw-bold" style="color: var(--text-main);"><?= escape($u['nama']) ?></div>
                                    <div class="small text-muted"><?= escape($u['email']) ?></div>
                                </td>
                                <td><span class="badge border bg-transparent text-uppercase <?= $u['role']==='admin'?'border-danger text-danger':($u['role']==='seller'?'border-success text-success':'border-primary text-primary') ?>"><?= escape($u['role']) ?></span></td>
                                <td class="text-muted"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                                <td class="text-end">
                                    <form action="<?= BASE_URL ?>/admin/dashboard.php" method="post" class="d-flex gap-2 justify-content-end">
                                        <input type="hidden" name="action" value="update_role">
                                        <input type="hidden" name="user_id" value="<?= escape($u['id']) ?>">
                                        <select class="form-select form-select-sm bg-transparent w-auto" name="role" style="color: var(--text-main); border-color: var(--border-color);">
                                            <option value="user" <?= $u['role']==='user'?'selected':'' ?>>User</option>
                                            <option value="seller" <?= $u['role']==='seller'?'selected':'' ?>>Seller</option>
                                            <option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>Admin</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary btn-apple p-2"><i class="fas fa-save"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- PRODUCTS TAB -->
        <div class="admin-pane d-none" id="tab-products">
            <div class="card-apple p-4" style="background: var(--surface-solid);">
                <h5 class="fw-bold mb-4">Marketplace Moderation</h5>
                <div class="table-responsive">
                    <table class="table align-middle table-borderless">
                        <thead style="border-bottom: 2px solid var(--border-color);">
                            <tr>
                                <th class="text-muted">Product</th>
                                <th class="text-muted">Vendor</th>
                                <th class="text-muted">Price</th>
                                <th class="text-muted text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($allProducts as $p): ?>
                            <tr class="border-bottom" style="border-color: var(--border-color) !important;">
                                <td class="py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= escape($p['image']) ?>" style="width: 40px; height: 40px; object-fit: contain; border-radius: 8px;">
                                        <div>
                                            <div class="fw-bold" style="color: var(--text-main);"><?= escape($p['name']) ?></div>
                                            <div class="small text-muted"><?= escape($p['cat_name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark shadow-0 border"><?= escape($p['seller_name']) ?></span></td>
                                <td class="fw-bold"><?= formatPrice($p['price']) ?></td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="<?= BASE_URL ?>/admin/add_product.php?edit_id=<?= $p['id'] ?>" class="btn btn-sm btn-link"><i class="fas fa-edit"></i></a>
                                        <form action="<?= BASE_URL ?>/admin/dashboard.php" method="post" onsubmit="return confirm('Permanent delete?');">
                                            <input type="hidden" name="action" value="delete_product">
                                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-link text-danger"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- CATEGORIES TAB -->
        <div class="admin-pane d-none" id="tab-categories">
            <div class="row g-4">
                <div class="col-md-5">
                    <div class="card-apple p-4" style="background: var(--surface-solid);">
                        <h5 class="fw-bold mb-4">New Category</h5>
                        <form action="<?= BASE_URL ?>/admin/dashboard.php" method="post">
                            <input type="hidden" name="action" value="add_category">
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">Category Full Name</label>
                                <input type="text" name="cat_name" class="form-control" required style="border-radius: 12px; background: var(--surface);">
                            </div>
                            <button type="submit" class="btn btn-primary btn-apple w-100">Create Category</button>
                        </form>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="card-apple p-4" style="background: var(--surface-solid);">
                        <h5 class="fw-bold mb-4">Active Categories</h5>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach($categories as $c): ?>
                            <div class="badge-apple rounded-pill px-3 py-2 border d-flex align-items-center gap-3" style="background: var(--surface);">
                                <span class="fw-bold" style="color: var(--text-main);"><?= escape($c['name']) ?></span>
                                <form action="<?= BASE_URL ?>/admin/dashboard.php" method="post" class="m-0" onsubmit="return confirm('Note: Deleting category might affect linked products.');">
                                    <input type="hidden" name="action" value="delete_category">
                                    <input type="hidden" name="category_id" value="<?= $c['id'] ?>">
                                    <button type="submit" class="btn btn-link text-danger p-0" style="line-height: 1;"><i class="fas fa-times-circle"></i></button>
                                </form>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ORDERS TAB -->
        <div class="admin-pane d-none" id="tab-orders">
            <div class="card-apple p-4" style="background: var(--surface-solid);">
                <h5 class="fw-bold mb-4">Global Order Logs</h5>
                <div class="table-responsive">
                    <table class="table align-middle table-borderless">
                        <thead style="border-bottom: 2px solid var(--border-color);">
                            <tr>
                                <th class="text-muted">Order ID</th>
                                <th class="text-muted">Buyer</th>
                                <th class="text-muted">Total</th>
                                <th class="text-muted">Status</th>
                                <th class="text-muted text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($allOrders as $o): ?>
                            <tr class="border-bottom" style="border-color: var(--border-color) !important;">
                                <td class="py-3">
                                    <div class="fw-bold" style="color: var(--text-main);">#<?= str_pad($o['id'], 6, '0', STR_PAD_LEFT) ?></div>
                                    <div class="small text-muted"><?= date('M j, Y', strtotime($o['created_at'])) ?></div>
                                </td>
                                <td><?= escape($o['buyer_name']) ?></td>
                                <td class="fw-bold"><?= formatPrice($o['total_amount']) ?></td>
                                <td><span class="badge <?= getAdminBadge($o['status']) ?> rounded-pill text-uppercase"><?= escape($o['status']) ?></span></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-glass btn-apple" data-mdb-toggle="modal" data-mdb-target="#adminOrderModal_<?= $o['id'] ?>">Manage</button>
                                </td>
                            </tr>

                            <!-- Status Modal Overlay -->
                            <div class="modal fade" id="adminOrderModal_<?= $o['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content glass" style="background: var(--surface-solid);">
                                        <div class="modal-header border-0">
                                            <h5 class="modal-title fw-bold">Manage Order #<?= $o['id'] ?></h5>
                                            <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="<?= BASE_URL ?>/admin/dashboard.php" method="post">
                                                <input type="hidden" name="action" value="update_order_status">
                                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-muted">Override Status</label>
                                                    <select class="form-select bg-transparent" name="status" style="color: var(--text-main); border-color: var(--border-color);">
                                                        <option value="pending" <?= $o['status']==='pending'?'selected':'' ?>>Pending</option>
                                                        <option value="paid" <?= $o['status']==='paid'?'selected':'' ?>>Paid</option>
                                                        <option value="shipped" <?= $o['status']==='shipped'?'selected':'' ?>>Shipped</option>
                                                        <option value="delivered" <?= $o['status']==='delivered'?'selected':'' ?>>Delivered</option>
                                                    </select>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="form-label small fw-bold text-muted">Tracking Notes</label>
                                                    <textarea class="form-control bg-transparent" name="notes" rows="3" style="color: var(--text-main); border-color: var(--border-color);"></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-primary btn-apple w-100">Apply Update</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- VERIFICATIONS TAB -->
        <div class="admin-pane d-none" id="tab-verifications">
            <div class="card-apple p-4" style="background: var(--surface-solid);">
                <h5 class="fw-bold mb-4">Pending Brand Verifications</h5>
                <?php if (empty($verificationRequests)): ?>
                    <p class="text-center py-5 text-muted">No pending verification requests.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle table-borderless">
                        <thead style="border-bottom: 2px solid var(--border-color);">
                            <tr>
                                <th class="text-muted">Seller</th>
                                <th class="text-muted">Business Name</th>
                                <th class="text-muted">Documents</th>
                                <th class="text-muted text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($verificationRequests as $vr): ?>
                            <tr class="border-bottom" style="border-color: var(--border-color) !important;">
                                <td class="py-3">
                                    <div class="fw-bold" style="color: var(--text-main);"><?= escape($vr['nama']) ?></div>
                                    <div class="small text-muted"><?= escape($vr['email']) ?></div>
                                </td>
                                <td><?= escape($vr['business_name']) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/<?= $vr['id_proof'] ?>" target="_blank" class="btn btn-sm btn-outline-primary me-2">View ID</a>
                                    <button class="btn btn-sm btn-outline-info" data-mdb-toggle="popover" title="Brand Proof" data-mdb-content="<?= escape($vr['brand_proof']) ?>">Proof Info</button>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <form action="<?= BASE_URL ?>/admin/dashboard.php" method="post">
                                            <input type="hidden" name="action" value="review_verification">
                                            <input type="hidden" name="request_id" value="<?= $vr['id'] ?>">
                                            <input type="hidden" name="user_id" value="<?= $vr['user_id'] ?>">
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-sm btn-success px-3">Approve</button>
                                        </form>
                                        <form action="<?= BASE_URL ?>/admin/dashboard.php" method="post">
                                            <input type="hidden" name="action" value="review_verification">
                                            <input type="hidden" name="request_id" value="<?= $vr['id'] ?>">
                                            <input type="hidden" name="user_id" value="<?= $vr['user_id'] ?>">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-sm btn-danger px-3">Reject</button>
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
        </div>
    </div>

    <!-- MODALS SECTION (Moved outside table for stability) -->
    <?php foreach($allOrders as $o): ?>
    <div class="modal fade" id="adminOrderModal_<?= $o['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass" style="background: var(--surface-solid);">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Manage Order #<?= $o['id'] ?></h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="<?= BASE_URL ?>/admin/dashboard.php" method="post">
                        <input type="hidden" name="action" value="update_order_status">
                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Override Status</label>
                            <select class="form-select bg-transparent" name="status" style="color: var(--text-main); border-color: var(--border-color);">
                                <option value="pending" <?= $o['status']==='pending'?'selected':'' ?>>Pending</option>
                                <option value="paid" <?= $o['status']==='paid'?'selected':'' ?>>Paid</option>
                                <option value="shipped" <?= $o['status']==='shipped'?'selected':'' ?>>Shipped</option>
                                <option value="delivered" <?= $o['status']==='delivered'?'selected':'' ?>>Delivered</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Tracking Notes</label>
                            <textarea class="form-control bg-transparent" name="notes" rows="3" style="color: var(--text-main); border-color: var(--border-color);"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-apple w-100">Apply Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</section>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        window.animate("#adminDashboardSection", { opacity: [0, 1], y: [20, 0] }, { duration: 0.6 });

        const tabs = document.querySelectorAll('.admin-tab-btn');
        const panes = document.querySelectorAll('.admin-pane');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const target = tab.getAttribute('data-target');
                
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                panes.forEach(pane => {
                    if (pane.id === target) {
                        pane.classList.remove('d-none');
                        window.animate(pane, { opacity: [0, 1], scale: [0.98, 1] }, { duration: 0.4 });
                    } else {
                        pane.classList.add('d-none');
                        pane.style.opacity = 0;
                    }
                });
            });
        });
    });
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
