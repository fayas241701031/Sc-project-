<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db_connect.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email AND role = :role LIMIT 1');
        $stmt->execute([':email' => $email, ':role' => 'admin']);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['user'] = [
                'id' => $admin['id'],
                'nama' => $admin['nama'],
                'email' => $admin['email'],
                'role' => $admin['role'],
            ];
            header('Location: ' . BASE_URL . '/admin/index.php');
            exit;
        }
        setFlash('Administrator login failed.', 'danger');
    } else {
        setFlash('Fill in both fields.', 'warning');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bazaar Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css" rel="stylesheet">
    <style>body{font-family:'Poppins',sans-serif;background:#f6f9ff;} .login-panel{max-width:420px;margin:8vh auto;}</style>
</head>
<body>
<div class="login-panel card shadow-sm p-4">
    <div class="card-body">
        <h3 class="fw-bold mb-3">Bazaar Admin</h3>
        <?php if ($flash = getFlash()): ?>
            <div class="alert alert-<?= $flash['type'] ?>"><?= escape($flash['message']) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Admin email</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
