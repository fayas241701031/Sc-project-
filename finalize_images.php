<?php
require_once __DIR__ . '/includes/db_connect.php';

$aiDir = 'C:/Users/mohamad fayas/.gemini/antigravity/brain/a08cf9aa-8a39-4fde-ad79-7ae66e0262ac/';
$assetsDir = __DIR__ . '/assets/img/';

$mappings = [
    'Headphones' => ['ai' => 'product_headphones_1775469146005.png', 'dest' => 'product_headphones.png'],
    'Smartwatch' => ['ai' => 'product_smartwatch_1775469441670.png', 'dest' => 'product_smartwatch.png'],
    'Sneakers' =>   ['ai' => 'product_sneakers_1775469475410.png',   'dest' => 'product_sneakers.png'],
    'Jacket' =>     ['ai' => 'product_jacket_1775469859115.png',     'dest' => 'product_jacket.png'],
    'Lamp' =>       ['ai' => 'product_lamp_1775469872595.png',       'dest' => 'product_lamp.png'],
    'Pillow' =>     ['ai' => 'product_pillow_1775470023401.png',     'dest' => 'product_pillow.png'],
];

foreach ($mappings as $key => $files) {
    if (file_exists($aiDir . $files['ai'])) {
        copy($aiDir . $files['ai'], $assetsDir . $files['dest']);
        $localUrl = 'assets/img/' . $files['dest'];
        $stmt = $pdo->prepare('UPDATE products SET image = ? WHERE name LIKE ?');
        $stmt->execute([$localUrl, "%$key%"]);
        echo "Updated $key with AI image.\n";
    }
}

// Update the ones that downloaded correctly from Unsplash
$unsplashImages = [
    'Helmet' => 'assets/img/product_helmet.jpg',
    'Yoga' => 'assets/img/product_yoga.jpg'
];
foreach ($unsplashImages as $key => $localUrl) {
    $stmt = $pdo->prepare('UPDATE products SET image = ? WHERE name LIKE ?');
    $stmt->execute([$localUrl, "%$key%"]);
    echo "Updated $key with Unsplash image.\n";
}

echo "Finalizing images complete.\n";
?>
