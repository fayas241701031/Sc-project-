-- Bazaar eCommerce database schema (InfinityFree Ready)
-- IMPORTANT: Do NOT include CREATE DATABASE or USE statements here.

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

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

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

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

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

CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  transaction_id VARCHAR(100) NOT NULL,
  payment_status VARCHAR(50) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO categories (name) VALUES
('Electronics'),
('Fashion'),
('Home'),
('Sports');

INSERT INTO users (nama, email, password, role) VALUES
('Admin User', 'admin@bazaar.local', '$2b$12$LNcP11xkDPrrKu2R.P58guPD3wAxS1MgAUxhzgam5K/djtXDs0qLS', 'admin'),
('Jane Doe', 'jane@bazaar.local', '$2b$12$LNcP11xkDPrrKu2R.P58guPD3wAxS1MgAUxhzgam5K/djtXDs0qLS', 'user');

INSERT INTO products (name, description, category_id, price, stock, image, popularity, status) VALUES
('Wireless Headphones', 'Premium noise-cancelling headphones with deep bass and long battery life.', 1, 8999, 28, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80', 85, 'active'),
('Smartwatch Pro', 'Sleek smartwatch with fitness tracking, notifications, and water resistance.', 1, 12999, 35, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80', 74, 'active'),
('Casual Sneakers', 'Comfortable sneakers in a modern silhouette for everyday wear.', 2, 4999, 42, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80', 62, 'active'),
('Denim Jacket', 'Classic denim jacket with a soft lining for a timeless streetwear look.', 2, 6499, 18, 'https://images.unsplash.com/photo-1551028719-00167b16ebc5?auto=format&fit=crop&w=800&q=80', 48, 'active'),
('Modern Lamp', 'Minimalist table lamp with warm LED lighting for living room and office.', 3, 3999, 55, 'https://images.unsplash.com/photo-1565808666545-e51df1bdc82f?auto=format&fit=crop&w=800&q=80', 39, 'active'),
('Cozy Throw Pillow', 'Premium velvet cushion for a stylish and comfy sofa update.', 3, 5199, 63, 'https://images.unsplash.com/photo-1578500494198-246f612d03b3?auto=format&fit=crop&w=800&q=80', 44, 'active'),
('Yoga Mat', 'Eco-friendly yoga mat with non-slip surface and extra cushioning.', 4, 2299, 80, 'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?auto=format&fit=crop&w=800&q=80', 52, 'active'),
('Mountain Bike Helmet', 'Lightweight helmet with impact protection for road and trail rides.', 4, 4299, 21, 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=800&q=80', 57, 'active');
