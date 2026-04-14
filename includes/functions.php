<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $protocol = 'https://';
    }
    $host_name = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $is_local = (strpos($host_name, 'localhost') !== false || strpos($host_name, '127.0.0.1') !== false || strpos($host_name, 'ngrok') !== false);
    
    if ($is_local) {
        define('BASE_URL', $protocol . $host_name . '/ecommerce-App');
    } else {
        define('BASE_URL', $protocol . $host_name);
    }
}

function escape($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function getCategories($pdo) {
    $stmt = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC');
    return $stmt->fetchAll();
}

function cartCount() {
    return isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
}

function setFlash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function getFlash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function isLoggedIn() {
    return !empty($_SESSION['user']);
}

function isAdmin() {
    return isLoggedIn() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

function formatPrice($price) {
    return '₹' . number_format($price, 2, '.', ',');
}
