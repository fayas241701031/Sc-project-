<?php
require_once __DIR__ . '/includes/db_connect.php';

$imagesDir = __DIR__ . '/assets/img/';
if (!is_dir($imagesDir)) {
    mkdir($imagesDir, 0777, true);
}

$images = [
    'Headphones' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80',
    'Smartwatch' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80',
    'Sneakers' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80',
    'Jacket' => 'https://images.unsplash.com/photo-1551028719-00167b16ebc5?auto=format&fit=crop&w=800&q=80',
    'Lamp' => 'https://images.unsplash.com/photo-1565808666545-e51df1bdc82f?auto=format&fit=crop&w=800&q=80',
    'Pillow' => 'https://images.unsplash.com/photo-1578500494198-246f612d03b3?auto=format&fit=crop&w=800&q=80',
    'Yoga' => 'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?auto=format&fit=crop&w=800&q=80',
    'Helmet' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=800&q=80',
];

foreach ($images as $key => $url) {
    echo "Downloading $key...\n";
    $imgData = @file_get_contents($url);
    if ($imgData !== false) {
        $filename = "product_" . strtolower($key) . ".jpg";
        file_put_contents($imagesDir . $filename, $imgData);
        $localUrl = 'assets/img/' . $filename;
        $stmt = $pdo->prepare('UPDATE products SET image = ? WHERE name LIKE ?');
        $stmt->execute([$localUrl, "%$key%"]);
    } else {
        echo "Failed to download $key.\n";
    }
}

// Hero images
$heroImages = [
    'hero_elec' => 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=1200&q=80',
    'hero_fash' => 'https://images.unsplash.com/photo-1523475496153-3d6cc2f3f8d6?auto=format&fit=crop&w=1200&q=80'
];

foreach ($heroImages as $key => $url) {
    echo "Downloading $key...\n";
    $imgData = @file_get_contents($url);
    if ($imgData !== false) {
        $filename = $key . ".jpg";
        file_put_contents($imagesDir . $filename, $imgData);
    } else {
        echo "Failed to download $key.\n";
    }
}

echo "Image download and update complete.\n";
?>
