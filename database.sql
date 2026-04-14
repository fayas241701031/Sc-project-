-- Bazaar eCommerce - Ultimate Schema
-- Fully featured with Reviews, Wishlist, Tracking, and Disputes

CREATE DATABASE IF NOT EXISTS ecommerce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecommerce;

-- 1. Users Table (Enhanced)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user', 'seller', 'admin') NOT NULL DEFAULT 'user',
  is_email_verified TINYINT(1) DEFAULT 0,
  email_token VARCHAR(100) DEFAULT NULL,
  token_expiry DATETIME DEFAULT NULL,
  is_verified_seller TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Categories
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Products (Enhanced with Seller ID)
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  category_id INT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  image VARCHAR(255) NOT NULL,
  popularity INT NOT NULL DEFAULT 0,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  seller_id INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
  FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Orders (Enhanced Enums)
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  status ENUM('pending', 'paid', 'shipped', 'delivered') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Order Items
CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. Reviews (NEW)
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK(rating >= 1 AND rating <= 5),
    comment TEXT NOT NULL,
    is_verified_purchase BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. Wishlist (NEW)
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY user_product (user_id, product_id)
) ENGINE=InnoDB;

-- 8. Order Tracking (NEW)
CREATE TABLE IF NOT EXISTS order_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status ENUM('pending', 'paid', 'shipped', 'delivered') NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 9. Disputes (NEW)
CREATE TABLE IF NOT EXISTS disputes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('pending', 'resolved', 'closed') NOT NULL DEFAULT 'pending',
    reason TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 10. Verification Requests
CREATE TABLE IF NOT EXISTS verification_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  business_name VARCHAR(150) NOT NULL,
  id_proof VARCHAR(255) NOT NULL,
  brand_proof TEXT NOT NULL,
  contact_details VARCHAR(255) NOT NULL,
  status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 11. Payments
CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  transaction_id VARCHAR(100) NOT NULL,
  payment_status VARCHAR(50) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Mock Data
INSERT IGNORE INTO categories (id, name) VALUES
(1, 'Electronics'), (2, 'Fashion'), (3, 'Home'), (4, 'Sports');

INSERT IGNORE INTO users (id, nama, email, password, role) VALUES
(1, 'Admin User', 'admin@bazaar.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
(2, 'Jane Doe', 'jane@bazaar.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

INSERT IGNORE INTO products (id, name, description, category_id, price, stock, image, popularity, status, seller_id) VALUES
(1, 'iPhone 15 Pro', 'Experience the power of Titanium. Dynamic Island, 48MP camera, and A17 Pro chip.', 1, 129900, 15, 'https://images.unsplash.com/photo-1696426742994-65dffc0f3c83?q=80&w=1000&auto=format&fit=crop', 98, 'active', 1),
(2, 'MacBook Air M3', 'Incredibly thin and fast. The M3 chip brings even greater capabilities to the 13-inch Air.', 1, 114900, 10, 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?q=80&w=1000&auto=format&fit=crop', 95, 'active', 1),
(3, 'Wireless Headphones', 'Premium noise-cancelling headphones with deep bass and long battery life.', 1, 8999, 28, 'https://images.unsplash.com/photo-1546435770-a3e4265029b6?q=80&w=1000&auto=format&fit=crop', 85, 'active', 1),
(4, 'Smartwatch Pro', 'Sleek smartwatch with fitness tracking, notifications, and water resistance.', 1, 12999, 35, 'https://images.unsplash.com/photo-1508685096489-7aac29ddaa9b?q=80&w=1000&auto=format&fit=crop', 74, 'active', 1),
(5, 'Casual Sneakers', 'Comfortable sneakers in a modern silhouette for everyday wear.', 2, 4999, 42, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=1000&auto=format&fit=crop', 62, 'active', 1),
(6, 'Denim Jacket', 'Classic denim jacket with a soft lining for a timeless streetwear look.', 2, 6499, 18, 'https://images.unsplash.com/photo-1551028719-00167b16ebc5?q=80&w=1000&auto=format&fit=crop', 48, 'active', 1),
(7, 'Modern Lamp', 'Minimalist table lamp with warm LED lighting for living room and office.', 3, 3999, 55, 'https://images.unsplash.com/photo-1534073828943-f801091bb18c?q=80&w=1000&auto=format&fit=crop', 39, 'active', 1),
(8, 'Cozy Throw Pillow', 'Premium velvet cushion for a stylish and comfy sofa update.', 3, 5199, 63, 'https://images.unsplash.com/photo-1584100936595-c0654b55a2e2?q=80&w=1000&auto=format&fit=crop', 44, 'active', 1),
(9, 'Yoga Mat', 'Eco-friendly yoga mat with non-slip surface and extra cushioning.', 4, 1299, 80, 'https://images.unsplash.com/photo-1599447421416-3414500d18a5?q=80&w=1000&auto=format&fit=crop', 52, 'active', 1);
