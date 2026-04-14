<?php
require_once __DIR__ . '/includes/header.php';
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $message = 'If this email exists, a password reset link has been sent to your inbox.';
        } else {
            $message = 'If this email exists, a password reset link has been sent.';
        }
    }
}
?>
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card shadow-sm border-0 p-4">
                <h3 class="fw-bold mb-3">Forgot password</h3>
                <?php if ($message): ?>
                    <div class="alert alert-info"><?= escape($message) ?></div>
                <?php else: ?>
                    <p class="text-muted">Enter your email to receive a password reset instruction.</p>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send reset link</button>
                    </form>
                <?php endif; ?>
                <div class="mt-3 text-center text-muted">
                    <a href="<?= BASE_URL ?>/login.php">Back to login</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>