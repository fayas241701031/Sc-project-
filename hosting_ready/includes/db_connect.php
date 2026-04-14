<?php
// InfinityFree Database Connection Details
// Replace the placeholders with your actual details from the InfinityFree Control Panel.

$host = 'sql112.infinityfree.com';
$db   = 'if0_41593807_Bazaar_db';
$user = 'if0_41593807';
$pass = 'MohamadFayas07';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
?>
