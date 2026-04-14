<?php
require_once __DIR__ . '/auth.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = trim($_POST['status'] ?? '');
    $orderId = (int)($_POST['order_id'] ?? 0);
    if ($orderId && $status) {
        $stmt = $pdo->prepare('UPDATE orders SET status = :status WHERE id = :id');
        $stmt->execute([':status' => $status, ':id' => $orderId]);
        setFlash('Order status updated.', 'success');
        header('Location: ' . BASE_URL . '/admin/manage_orders.php');
        exit;
    }
}
$stmt = $pdo->query('SELECT o.*, u.nama AS user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC');
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Bazaar Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css" rel="stylesheet">
    <style>body{font-family:'Poppins',sans-serif;background:#f7f8ff;} .sidebar{min-height:100vh;}</style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <aside class="col-lg-3 col-xl-2 bg-white border-end sidebar p-4">
            <a href="<?= BASE_URL ?>/admin/index.php" class="text-decoration-none"><h4 class="fw-bold">Bazaar Admin</h4></a>
            <nav class="nav flex-column gap-2 mt-4">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/index.php">Dashboard</a>
                <a class="nav-link" href="<?= BASE_URL ?>/admin/manage_products.php">Products</a>
                <a class="nav-link" href="<?= BASE_URL ?>/admin/manage_categories.php">Categories</a>
                <a class="nav-link active" href="<?= BASE_URL ?>/admin/manage_orders.php">Orders</a>
                <a class="nav-link" href="<?= BASE_URL ?>/admin/manage_users.php">Users</a>
                <a class="nav-link text-danger" href="<?= BASE_URL ?>/logout.php">Logout</a>
            </nav>
        </aside>
        <main class="col-lg-9 col-xl-10 py-4 px-5">
            <div class="mb-4">
                <h2 class="fw-bold">Manage orders</h2>
                <p class="text-muted">Update order statuses and track customer purchases.</p>
            </div>
            <?php if ($flash = getFlash()): ?>
                <div class="alert alert-<?= $flash['type'] ?>"><?= escape($flash['message']) ?></div>
            <?php endif; ?>
            <div class="table-responsive">
                <table class="table align-middle bg-white shadow-sm rounded-4 overflow-hidden">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Total</th>
                            <th>Placed</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= escape($order['id']) ?></td>
                                <td><?= escape($order['user_name']) ?></td>
                                <td><?= formatPrice($order['total_amount']) ?></td>
                                <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                <td><span class="badge bg-<?= $order['status'] === 'delivered' ? 'success' : ($order['status'] === 'shipped' ? 'info' : 'warning') ?>"><?= escape(ucfirst($order['status'])) ?></span></td>
                                <td>
                                    <form method="post" class="d-flex gap-2">
                                        <input type="hidden" name="order_id" value="<?= escape($order['id']) ?>">
                                        <select class="form-select form-select-sm" name="status">
                                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                            <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
