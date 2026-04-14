<?php
require_once __DIR__ . '/auth.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['delete_id'])) {
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
    $stmt->execute([':id' => (int)$_POST['delete_id']]);
    setFlash('Product deleted successfully.', 'success');
    header('Location: ' . BASE_URL . '/admin/manage_products.php');
    exit;
}
$stmt = $pdo->query('SELECT p.*, c.name AS category_name, u.nama AS seller_name FROM products p JOIN categories c ON p.category_id = c.id LEFT JOIN users u ON p.seller_id = u.id ORDER BY p.id DESC');
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Bazaar Admin</title>
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
                <a class="nav-link active" href="<?= BASE_URL ?>/admin/manage_products.php">Products</a>
                <a class="nav-link" href="<?= BASE_URL ?>/admin/manage_categories.php">Categories</a>
                <a class="nav-link" href="<?= BASE_URL ?>/admin/manage_orders.php">Orders</a>
                <a class="nav-link" href="<?= BASE_URL ?>/admin/manage_users.php">Users</a>
                <a class="nav-link text-danger" href="<?= BASE_URL ?>/logout.php">Logout</a>
            </nav>
        </aside>
        <main class="col-lg-9 col-xl-10 py-4 px-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold">Manage products</h2>
                    <p class="text-muted">Add, edit, or delete products and upload images.</p>
                </div>
                <a href="<?= BASE_URL ?>/admin/add_product.php" class="btn btn-primary">Add product</a>
            </div>
            <?php if ($flash = getFlash()): ?>
                <div class="alert alert-<?= $flash['type'] ?>"><?= escape($flash['message']) ?></div>
            <?php endif; ?>
            <div class="table-responsive">
                <table class="table align-middle bg-white shadow-sm rounded-4 overflow-hidden">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Seller</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= escape($product['id']) ?></td>
                                <td><img src="<?= escape($product['image']) ?>" width="60" class="rounded-3"></td>
                                <td><?= escape($product['name']) ?></td>
                                <td><span class="badge bg-light text-dark shadow-0 border"><?= escape($product['seller_name'] ?? 'Admin') ?></span></td>
                                <td><?= escape($product['category_name']) ?></td>
                                <td><?= formatPrice($product['price']) ?></td>
                                <td><?= escape($product['stock']) ?></td>
                                <td><?= escape(ucfirst($product['status'] ?? 'active')) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/add_product.php?edit_id=<?= escape($product['id']) ?>" class="btn btn-sm btn-outline-primary me-1">Edit</a>
                                    <form method="post" class="d-inline-block" onsubmit="return confirm('Delete this product?');">
                                        <input type="hidden" name="delete_id" value="<?= escape($product['id']) ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
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
