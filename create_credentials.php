<?php
require_once __DIR__ . '/includes/db_connect.php';

// Prepare hashed passwords
$adminPass = password_hash('admin123', PASSWORD_DEFAULT);
$userPass = password_hash('user123', PASSWORD_DEFAULT);
$sellerPass = password_hash('seller123', PASSWORD_DEFAULT);

try {
    // Inject Admin
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (nama, email, password, role) VALUES ('System Admin', 'admin@bazaar.local', ?, 'admin')");
    $stmt->execute([$adminPass]);

    // Inject User
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (nama, email, password, role) VALUES ('Test User', 'user@bazaar.local', ?, 'user')");
    $stmt->execute([$userPass]);
    
    // Inject Seller (Override the migrate_db one with a known password just in case)
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (nama, email, password, role) VALUES ('Test Seller', 'seller@bazaar.local', ?, 'seller')");
    $stmt->execute([$sellerPass]);
    
    echo "Done";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
