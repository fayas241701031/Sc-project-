<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

    // 1. Validate CAPTCHA
    $verifyUrl = "https://www.google.com/recaptcha/api/siteverify?secret=" . RECAPTCHA_SECRET_KEY . "&response=" . $recaptchaResponse;
    $response = file_get_contents($verifyUrl);
    $responseData = json_decode($response);

    if (defined('RECAPTCHA_SECRET_KEY') && !empty(RECAPTCHA_SECRET_KEY) && RECAPTCHA_SECRET_KEY !== 'your_secret_key' && !$responseData->success) {
        setFlash('Please complete the CAPTCHA verification.', 'danger');
    } elseif (!$email || !$password) {
        setFlash('Email and password are required.', 'danger');
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // 2. Check Email Verification
            if ($user['is_email_verified'] == 0) {
                setFlash('Please verify your email before logging in.', 'warning');
                header('Location: ' . BASE_URL . '/login.php');
                exit;
            }

            $_SESSION['user'] = [
                'id' => $user['id'],
                'nama' => $user['nama'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];
            setFlash('Welcome back!','success');
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }
        setFlash('Invalid credentials. Please try again.', 'danger');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="container py-5 mt-5" id="loginContainer">
    <div class="row justify-content-center align-items-center">
        <div class="col-md-6 col-lg-4">
            <div class="card-apple p-5 shadow-lg border-0 bg-glass text-center" style="background: var(--surface-solid);">
                <div class="mb-5">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px; background: rgba(0, 122, 255, 0.1); color: var(--primary-color); font-size: 2rem;">
                        <i class="fas fa-user-lock"></i>
                    </div>
                    <h2 class="fw-bold m-0">Welcome Back</h2>
                    <p style="color: var(--text-muted);" class="mt-2 text-wrap">Log in to your Bazaar account to continue shopping.</p>
                </div>

                <form id="loginForm" method="post" action="<?= BASE_URL ?>/login.php">
                    <div class="mb-4 text-start">
                        <label class="form-label small fw-bold text-muted">Email Address</label>
                        <input type="email" class="form-control" name="email" required placeholder="name@example.com" style="background: var(--surface);">
                    </div>
                    <div class="mb-5 text-start">
                        <div class="d-flex justify-content-between">
                            <label class="form-label small fw-bold text-muted">Password</label>
                            <a href="<?= BASE_URL ?>/forgot_password.php" class="small text-decoration-none" style="color: var(--primary-color);">Forgot?</a>
                        </div>
                        <input type="password" class="form-control" name="password" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;" style="background: var(--surface);">
                    </div>

                    <?php if (defined('RECAPTCHA_SITE_KEY') && !empty(RECAPTCHA_SITE_KEY) && RECAPTCHA_SITE_KEY !== 'your_site_key'): ?>
                        <div class="mb-4 d-flex justify-content-center">
                            <div class="g-recaptcha" data-sitekey="<?= RECAPTCHA_SITE_KEY ?>"></div>
                        </div>
                        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                    <?php endif; ?>

                    <button type="submit" id="loginSubmit" class="btn btn-primary btn-apple btn-lg w-100 py-3 mb-4">Log In</button>
                    
                    <div class="mt-2 text-center text-muted small">
                        Don't have an account? <a href="<?= BASE_URL ?>/signup.php" class="fw-bold text-decoration-none" style="color: var(--primary-color);">Sign up</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script type="module">
    document.addEventListener('DOMContentLoaded', () => {
        window.animate("#loginContainer", { opacity: [0, 1], y: [20, 0] }, { duration: 0.8, easing: [0.16, 1, 0.3, 1] });
        
        const btn = document.getElementById('loginSubmit');
        document.getElementById('loginForm').addEventListener('submit', () => {
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Authenticating...';
            btn.classList.add('disabled');
        });
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>