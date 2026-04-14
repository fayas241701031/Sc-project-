<?php
require_once __DIR__ . '/../includes/header.php';
if (!isLoggedIn()) {
    setFlash('Please log in to view your profile.', 'warning');
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}
$user = $_SESSION['user'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if ($name && $email) {
        $stmt = $pdo->prepare('UPDATE users SET nama = :name, email = :email WHERE id = :id');
        $stmt->execute([':name' => $name, ':email' => $email, ':id' => $user['id']]);
        $_SESSION['user']['nama'] = $name;
        $_SESSION['user']['email'] = $email;
        setFlash('Profile updated successfully.', 'success');
        header('Location: ' . BASE_URL . '/user/profile.php');
        exit;
    }
}
?>
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 p-4">
                <h3 class="fw-bold mb-4">Profile management</h3>
                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full name</label>
                            <input type="text" class="form-control" name="name" value="<?= escape($user['nama']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= escape($user['email']) ?>" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>