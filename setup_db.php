<?php
$host = '127.0.0.1';
$port = '3307';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $pdo->exec('DROP DATABASE IF EXISTS ecommerce');
    $pdo->exec('CREATE DATABASE IF NOT EXISTS ecommerce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo->exec('USE ecommerce');

    $sql = file_get_contents(__DIR__ . '/database.sql');

    $statements = array_filter(array_map('trim', explode(';', $sql)), function ($statement) {
        $statement = trim($statement);
        if ($statement === '' || strpos($statement, '--') === 0) {
            return false;
        }
        if (preg_match('/^(CREATE\s+DATABASE|USE\s+ecommerce)/i', $statement)) {
            return false;
        }
        return true;
    });

    foreach ($statements as $statement) {
        if ($statement) {
            $pdo->exec($statement . ';');
        }
    }

    echo '<div style="font-family: Arial, sans-serif; padding: 20px; background: #f0f9ff; border-radius: 8px;">';
    echo '<h2 style="color: #0066cc;">✓ Database setup complete</h2>';
    echo '<p>The <strong>ecommerce</strong> database was recreated successfully with the full schema.</p>';
    echo '<p><a href="' . htmlspecialchars((isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : '') . dirname($_SERVER['REQUEST_URI'])) . '/index.php">Open Bazaar Home</a></p>';
    echo '</div>';

} catch (PDOException $e) {
    echo '<div style="color: red; padding: 20px; background: #ffe6e6; border-radius: 8px;">';
    echo '<strong>Database setup error:</strong> ' . htmlspecialchars($e->getMessage());
    echo '</div>';
}
?>