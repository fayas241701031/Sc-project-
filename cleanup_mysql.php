<?php
$paths = [
    __DIR__ . '/cleanup_mysql.php',
    'C:/xampp/mysql/data/mysql.pid',
    'C:/xampp/mysql/data/ibtmp1',
];
$results = [];
foreach ($paths as $path) {
    if ($path === __DIR__ . '/cleanup_mysql.php') {
        $results[] = "Cleanup script ready at $path";
        continue;
    }
    if (file_exists($path)) {
        if (@unlink($path)) {
            $results[] = "Deleted: $path";
        } else {
            $results[] = "Failed to delete: $path";
        }
    } else {
        $results[] = "Not found: $path";
    }
}
$results[] = "\nPlease stop XAMPP completely, then restart MySQL from the XAMPP Control Panel.";
$results[] = "After MySQL starts, open http://localhost/ecommerce-App/index.php";
header('Content-Type: text/plain; charset=utf-8');
echo implode("\n", $results);
?>