<?php
require_once __DIR__ . '/auth.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['delete_id'])) {
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = :id');
        $stmt->execute([':id' => (int)$_POST['delete_id']]);
        setFlash('Category deleted.', 'success');
        header('Location: ' . BASE_URL . '/admin/manage_categories.php');
        exit;
    }
    if (!empty($_POST['name'])) {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $pdo->prepare('UPDATE categories SET name = :name WHERE id = :id');
            $stmt->execute([':name' => trim($_POST['name']), ':id' => $id]);
            setFlash('Category updated.', 'success');
        } else {
            $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (:name)');
            $stmt->execute([':name' => trim($_POST['name'])]);
            setFlash('Category added.', 'success');
        }
        header('Location: ' . BASE_URL . '/admin/manage_categories.php');
        exit;
    }
}
$stmt = $pdo->query('SELECT * FROM categories ORDER BY id DESC');
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Bazaar Admin</title>
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
                <a class="nav-link active" href="<?= BASE_URL ?>/admin/manage_categories.php">Categories</a>
                <a class="nav-link" href="<?= BASE_URL ?>/admin/manage_orders.php">Orders</a>
                <a class="nav-link" href="<?= BASE_URL ?>/admin/manage_users.php">Users</a>
                <a class="nav-link text-danger" href="<?= BASE_URL ?>/logout.php">Logout</a>
            </nav>
        </aside>
        <main class="col-lg-9 col-xl-10 py-4 px-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold">Manage categories</h2>
                    <p class="text-muted">Add, edit, or remove store categories.</p>
                </div>
            </div>
            <?php if ($flash = getFlash()): ?>
                <div class="alert alert-<?= $flash['type'] ?>"><?= escape($flash['message']) ?></div>
            <?php endif; ?>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 p-4">
                        <h5 class="mb-4">Add category</h5>
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Category name</label>
                                <input class="form-control" name="name" required>
                            </div>
                            <button class="btn btn-primary">Add category</button>
                        </form>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 p-4">
                        <h5 class="mb-4">Category list</h5>
                        <ul class="list-group">
                            <?php foreach ($categories as $category): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= escape($category['name']) ?>
                                    <form method="post" class="d-flex gap-2">
                                        <input type="hidden" name="delete_id" value="<?= escape($category['id']) ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete category?');">Delete</button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
