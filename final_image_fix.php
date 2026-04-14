<?php
require_once __DIR__ . '/includes/db_connect.php';

$images = [
    1 => 'https://images.unsplash.com/photo-1592750475338-74575a4958fc?q=80&w=1000&auto=format&fit=crop', // iPhone
    2 => 'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?q=80&w=1000&auto=format&fit=crop', // MacBook
    3 => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=1000&auto=format&fit=crop', // Headphones
    4 => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=1000&auto=format&fit=crop', // Smartwatch
    5 => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=1000&auto=format&fit=crop', // Sneakers
    6 => 'https://images.unsplash.com/photo-1542272604-787c3835535d?q=80&w=1000&auto=format&fit=crop', // Denim Jacket
    7 => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?q=80&w=1000&auto=format&fit=crop', // Lamp
    8 => 'https://images.unsplash.com/photo-1578500494198-246f612d03b3?q=80&w=1000&auto=format&fit=crop', // Pillow
    9 => 'https://images.unsplash.com/photo-1599447421416-3414500d18a5?q=80&w=1000&auto=format&fit=crop', // Yoga Mat
];

foreach ($images as $id => $url) {
    $stmt = $pdo->prepare('UPDATE products SET image = ? WHERE id = ?');
    $stmt->execute([$url, $id]);
}

echo "Final image sync complete.\n";
?>
