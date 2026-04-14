<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role']) && $_POST['role'] === 'seller') {
    $userId = $_SESSION['user']['id'];
    
    // Check if user is currently 'user'
    if ($_SESSION['user']['role'] === 'user') {
        $stmt = $pdo->prepare('UPDATE users SET role = "seller" WHERE id = ?');
        $stmt->execute([$userId]);
        
        // Update session
        $_SESSION['user']['role'] = 'seller';
        
        setFlash('Congratulations! You are now a verified Seller.', 'success');
        header('Location: ' . BASE_URL . '/seller/dashboard.php');
        exit;
    }
}

header('Location: ' . BASE_URL . '/user/dashboard.php');
exit;
