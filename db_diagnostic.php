<?php
require_once __DIR__ . '/includes/db_connect.php';

$stmt = $pdo->query('SELECT id, name, image FROM products');
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total products: " . count($products) . "\n\n";
echo "ID | Name | Image URL\n";
echo "---|------|----------\n";
foreach ($products as $p) {
    echo $p['id'] . " | " . $p['name'] . " | " . $p['image'] . "\n";
}

$stmt = $pdo->query('SELECT id, name FROM categories');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "\nTotal categories: " . count($categories) . "\n";
foreach ($categories as $c) {
    echo $c['id'] . ": " . $c['name'] . "\n";
}
?>
