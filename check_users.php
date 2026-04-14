<?php
require_once __DIR__ . '/includes/db_connect.php';
$stmt = $pdo->query('SELECT id, nama, email, is_email_verified, role FROM users ORDER BY id DESC LIMIT 10');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Recent Users</h3>";
echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Email</th><th>Verified</th><th>Role</th></tr>";
foreach ($users as $u) {
    echo "<tr><td>{$u['id']}</td><td>{$u['nama']}</td><td>{$u['email']}</td><td>{$u['is_email_verified']}</td><td>{$u['role']}</td></tr>";
}
echo "</table>";
?>
