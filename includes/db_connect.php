<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Environment Detection (Local, Ngrok, or Live)
$host_name = $_SERVER['HTTP_HOST'] ?? 'localhost';
$is_local = (strpos($host_name, 'localhost') !== false || strpos($host_name, '127.0.0.1') !== false || strpos($host_name, 'ngrok') !== false);

// Detect Protocol (Handle HTTPS and Ngrok/Proxy HTTPS)
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $protocol = 'https://';
}

if (!defined('BASE_URL')) {
    if ($is_local) {
        define('BASE_URL', $protocol . $host_name . '/ecommerce-App');
    } else {
        define('BASE_URL', $protocol . $host_name);
    }
}

if ($is_local) {
    // Local Settings (XAMPP on Port 3307)
    $host = '127.0.0.1';
    $port = '3307';
    $db = 'ecommerce';
    $user = 'root';
    $pass = '';
} else {
    // Live Settings (InfinityFree)
    $host = 'sql112.infinityfree.com';
    $db = 'if0_41593807_Bazaar_db';
    $user = 'if0_41593807';
    $pass = 'MohamadFayas07';
}

$charset = 'utf8mb4';
$dsn = $is_local ? "mysql:host=$host;port=$port;dbname=$db;charset=$charset" : "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
?>