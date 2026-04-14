<?php
require_once __DIR__ . '/auth.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['delete_id'])) {
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id AND role != "admin"');
    $stmt->execute([':id' => (int)$_POST['delete_id']]);
    setFlash('User deleted.', 'success');
    header('Location: ' . BASE_URL . '/admin/manage_users.php');
    exit;
}
$stmt = $pdo->query('SELECT id, nama, email, role, created_at FROM users ORDER BY created_at DESC');
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Bazaar Admin</title>
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
                <a class="nav-link" href="<?= BASE_URL ?>/admin/manage_orders.php">Orders</a>
                <a class="nav-link active" href="<?= BASE_URL ?>/admin/manage_users.php">Users</a>
                <a class="nav-link text-danger" href="<?= BASE_URL ?>/logout.php">Logout</a>
            </nav>
        </aside>
        <main class="col-lg-9 col-xl-10 py-4 px-5">
            <div class="mb-4">
                <h2 class="fw-bold">Manage users</h2>
                <p class="text-muted">View registered customers and remove accounts if needed.</p>
            </div>
            <?php if ($flash = getFlash()): ?>
                <div class="alert alert-<?= $flash['type'] ?>"><?= escape($flash['message']) ?></div>
            <?php endif; ?>
            <div class="table-responsive">
                <table class="table align-middle bg-white shadow-sm rounded-4 overflow-hidden">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= escape($user['id']) ?></td>
                                <td><?= escape($user['nama']) ?></td>
                                <td><?= escape($user['email']) ?></td>
                                <td><?= escape($user['role']) ?></td>
                                <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <form method="post" onsubmit="return confirm('Remove this user?');">
                                            <input type="hidden" name="delete_id" value="<?= escape($user['id']) ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Admin</span>
                                    <?php endif; ?>
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
