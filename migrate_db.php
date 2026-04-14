<?php
require_once __DIR__ . '/includes/db_connect.php';

try {
    echo "1. Upgrading users table...\n";
    // Check if role is already enum
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (strpos($row['Type'], 'enum') !== false && strpos($row['Type'], 'seller') === false) {
        $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin', 'seller') NOT NULL DEFAULT 'user'");
    }

    // Insert mock seller
    $hash = password_hash('password', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (nama, email, password, role) VALUES ('Official Store', 'seller@bazaar.local', ?, 'seller')");
    $stmt->execute([$hash]);

    // Get seller ID
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = 'seller@bazaar.local'");
    $stmt->execute();
    $sellerId = $stmt->fetchColumn();

    echo "2. Upgrading products table...\n";
    // Check if seller_id column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'seller_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE products ADD COLUMN seller_id INT DEFAULT NULL");
        $pdo->exec("UPDATE products SET seller_id = $sellerId");
        $pdo->exec("ALTER TABLE products MODIFY COLUMN seller_id INT NOT NULL");
        $pdo->exec("ALTER TABLE products ADD CONSTRAINT fk_product_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE");
    }

    echo "3. Creating reviews table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        user_id INT NOT NULL,
        rating INT NOT NULL CHECK(rating >= 1 AND rating <= 5),
        comment TEXT NOT NULL,
        is_verified_purchase BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    echo "4. Creating wishlist table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS wishlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE KEY user_product (user_id, product_id)
    ) ENGINE=InnoDB;");

    echo "5. Upgrading orders and creating order_tracking table...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'status'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    // Be careful, might fail if some orders have status not in the new enum. 
    // We update them first.
    $pdo->exec("UPDATE orders SET status = 'pending' WHERE status NOT IN ('pending', 'paid', 'shipped', 'delivered')");
    $pdo->exec("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'shipped', 'delivered') NOT NULL DEFAULT 'pending'");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_tracking (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        status ENUM('pending', 'paid', 'shipped', 'delivered') NOT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    echo "6. Creating disputes table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS disputes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        user_id INT NOT NULL,
        status ENUM('pending', 'resolved', 'closed') NOT NULL DEFAULT 'pending',
        reason TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    echo "\nDatabase migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
