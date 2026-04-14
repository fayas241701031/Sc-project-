<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db_connect.php';
$flash = getFlash();
$categories = getCategories($pdo);
$cartCount = cartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bazaar - Modern eCommerce</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <base href="<?= BASE_URL ?>/">
    <script>
        const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body <?= strpos($_SERVER['PHP_SELF'], 'index.php') !== false ? 'class="is-home"' : '' ?>>
<?php if (strpos($_SERVER['PHP_SELF'], 'index.php') === false): ?>
    <div class="back-btn-global" onclick="window.history.back()" title="Go Back">
        <i class="fas fa-chevron-left"></i>
    </div>
<?php endif; ?>
<header class="sticky-top">
    <nav class="navbar navbar-expand-lg">
        <div class="container container-fluid px-4 px-lg-5">
            <a class="navbar-brand brand-font" href="<?= BASE_URL ?>/index.php">Bazaar</a>
            <button class="navbar-toggler border-0" type="button" data-mdb-toggle="collapse" data-mdb-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars" style="color: var(--text-main)"></i>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 align-items-lg-center gap-2">
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/shop.php">Marketplace</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/cart.php">Cart <span class="badge badge-apple ms-1"><?= $cartCount ?></span></a></li>
                </ul>
                <ul class="navbar-nav align-items-lg-center gap-3">
                    <li class="nav-item">
                        <button class="theme-toggle" id="themeToggleBtn" aria-label="Toggle Dark Mode">
                            <i class="fas fa-moon"></i>
                        </button>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-mdb-toggle="dropdown" aria-expanded="false">
                                <?= escape($_SESSION['user']['nama']) ?>
                            </a>
                            <ul class="dropdown-menu glass" aria-labelledby="userDropdown">
                                <?php if($_SESSION['user']['role'] === 'admin'): ?>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/dashboard.php"><i class="fas fa-shield-alt me-2"></i>Admin Panel</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <?php if($_SESSION['user']['role'] === 'seller'): ?>
                                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/seller/dashboard.php"><i class="fas fa-store me-2"></i>Seller Dashboard</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/user/dashboard.php"><i class="fas fa-user me-2"></i>Buyer Dashboard</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/login.php">Log In</a></li>
                        <li class="nav-item"><a class="btn btn-primary btn-apple px-4 text-white" href="<?= BASE_URL ?>/signup.php">Sign Up</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
<script type="module">
    import { animate, scroll, inView, spring, stagger } from "https://cdn.jsdelivr.net/npm/motion@11.11.13/+esm";
    window.animate = animate;
    window.scroll = scroll;
    window.inView = inView;
    window.spring = spring;
    window.stagger = stagger;

    // Theme Toggle Logic
    const themeBtn = document.getElementById('themeToggleBtn');
    const updateIcon = (theme) => {
        themeBtn.innerHTML = theme === 'dark' ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
    };
    updateIcon(document.documentElement.getAttribute('data-theme'));

    themeBtn.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateIcon(newTheme);
        animate(themeBtn, { rotate: [0, 360] }, { duration: 0.5 });
    });
</script>
<?php if ($flash): ?>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
    <div id="flashToast" class="toast align-items-center text-bg-<?= $flash['type'] ?> border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <?= escape($flash['message']) ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-mdb-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toastEl = document.getElementById('flashToast');
        if (toastEl) {
            var toast = new mdb.Toast(toastEl);
            toast.show();
        }
    });
</script>
<?php endif; ?>
<main class="pb-5">
