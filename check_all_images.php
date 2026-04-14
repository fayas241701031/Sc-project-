<?php
require_once __DIR__ . '/includes/db_connect.php';

$stmt = $pdo->query('SELECT id, name, image FROM products');
$products = $stmt->fetchAll();

echo "ID | Name | Image URL\n";
echo "---|------|----------\n";
foreach ($products as $p) {
    echo $p['id'] . " | " . $p['name'] . " | " . $p['image'] . "\n";
}
?>
