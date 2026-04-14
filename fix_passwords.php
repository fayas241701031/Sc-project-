<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=ecommerce", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $hash = password_hash('password', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email IN (?, ?)');
    $stmt->execute([$hash, 'admin@bazaar.local', 'jane@bazaar.local']);

    echo 'Passwords updated successfully. New hash: ' . $hash;
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>