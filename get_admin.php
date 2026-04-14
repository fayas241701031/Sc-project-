<?php
require_once __DIR__ . '/includes/db_connect.php';
$stmt = $pdo->query("SELECT email, role FROM users WHERE role = 'admin'");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($admins) {
    foreach ($admins as $admin) {
        echo "Email: " . $admin['email'] . " | Role: " . $admin['role'] . "\n";
    }
} else {
    echo "No admin user found in database.\n";
}
?>
