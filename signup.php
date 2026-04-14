<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    
    if (!$name || !$email || !$password || !$confirm) {
        setFlash('All fields are required.', 'danger');
    } elseif ($password !== $confirm) {
        setFlash('Passwords do not match.', 'danger');
    } else {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            setFlash('Email is already registered.', 'warning');
        } else {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));
            
            $stmt = $pdo->prepare('INSERT INTO users (nama, email, password, role, is_email_verified, email_token, token_expiry) VALUES (:name, :email, :password, :role, 0, :token, :expiry)');
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':role' => 'user',
                ':token' => $token,
                ':expiry' => $expiry
            ]);
            
            $mailStatus = sendVerificationEmail($email, $name, $token);
            if ($mailStatus === true) {
                setFlash('Account created! Please check your email to verify your account.', 'success');
            } else {
                setFlash('Account created, but email failed. Error: ' . $mailStatus, 'warning');
            }
            
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="container py-5 mt-4" id="signupContainer">
    <div class="row justify-content-center align-items-center">
        <div class="col-md-7 col-lg-5">
            <div class="card-apple p-5 shadow-lg border-0 bg-glass" style="background: var(--surface-solid);">
                <div class="text-center mb-5">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 70px; height: 70px; background: rgba(52, 199, 89, 0.1); color: #34c759; font-size: 1.8rem;">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h2 class="fw-bold m-0">Create Account</h2>
                    <p style="color: var(--text-muted);" class="mt-2">Join the most premium marketplace experience.</p>
                </div>

                <form id="signupForm" method="post" action="<?= BASE_URL ?>/signup.php">
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Full Name</label>
                        <input type="text" class="form-control" name="name" required placeholder="John Appleseed" style="background: var(--surface);">
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Email Address</label>
                        <input type="email" class="form-control" name="email" required placeholder="john@example.com" style="background: var(--surface);">
                    </div>
                    <div class="row g-3 mb-5">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Password</label>
                            <input type="password" class="form-control" name="password" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;" style="background: var(--surface);">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Confirm</label>
                            <input type="password" class="form-control" name="confirm" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;" style="background: var(--surface);">
                        </div>
                    </div>
                    
                    <button type="submit" id="signupSubmit" class="btn btn-primary btn-apple btn-lg w-100 py-3 mb-4">Create Bazaar Account</button>
                    
                    <div class="mt-2 text-center text-muted small">
                        Already have an account? <a href="<?= BASE_URL ?>/login.php" class="fw-bold text-decoration-none" style="color: var(--primary-color);">Log in</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        window.animate("#signupContainer", { opacity: [0, 1], y: [20, 0] }, { duration: 0.8, easing: [0.16, 1, 0.3, 1] });
        
        const btn = document.getElementById('signupSubmit');
        document.getElementById('signupForm').addEventListener('submit', () => {
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';
            btn.classList.add('disabled');
        });
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>