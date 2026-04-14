<?php
require_once __DIR__ . '/includes/db_connect.php';

$updates = [
    'Headphones' => 'https://picsum.photos/seed/headphone1/800/600',
    'Smartwatch' => 'https://picsum.photos/seed/watch1/800/600',
    'Sneakers' => 'https://picsum.photos/seed/sneaker1/800/600',
    'Jacket' => 'https://picsum.photos/seed/jacket1/800/600',
    'Lamp' => 'https://picsum.photos/seed/lamp1/800/600',
    'Pillow' => 'https://picsum.photos/seed/pillow1/800/600',
    'Yoga' => 'https://picsum.photos/seed/yoga1/800/600',
    'Helmet' => 'https://picsum.photos/seed/helmet1/800/600',
];

foreach ($updates as $key => $url) {
    $stmt = $pdo->prepare('UPDATE products SET image = ? WHERE name LIKE ?');
    $stmt->execute([$url, "%$key%"]);
}

echo "Database images updated successfully.\n";

// Ensure passwords are 'password'
$hash = password_hash('password', PASSWORD_DEFAULT);
$stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email IN (?, ?)');
$stmt->execute([$hash, 'admin@bazaar.local', 'jane@bazaar.local']);
echo "Passwords ensured to be 'password'.\n";
?>
