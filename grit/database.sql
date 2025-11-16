-- Database: grit
CREATE DATABASE IF NOT EXISTS grit;
USE grit;

-- Table structure for table `users`
CREATE TABLE IF NOT EXISTS users (
  id INT(11) NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'customer') DEFAULT 'customer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);

-- Table structure for table `categories`
CREATE TABLE IF NOT EXISTS categories (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);

-- Table structure for table `products`
CREATE TABLE IF NOT EXISTS products (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(200) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  category_id INT(11),
  image VARCHAR(255),
  stock INT(11) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Table structure for table `orders`
CREATE TABLE IF NOT EXISTS orders (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11),
  total_amount DECIMAL(10,2) NOT NULL,
  status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Table structure for table `order_items`
CREATE TABLE IF NOT EXISTS order_items (
  id INT(11) NOT NULL AUTO_INCREMENT,
  order_id INT(11),
  product_id INT(11),
  quantity INT(11) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Table structure for table `user_profiles`
CREATE TABLE IF NOT EXISTS user_profiles (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  first_name VARCHAR(50),
  last_name VARCHAR(50),
  phone VARCHAR(20),
  address TEXT,
  city VARCHAR(100),
  country VARCHAR(100) DEFAULT 'Philippines',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table structure for table `messages`
CREATE TABLE IF NOT EXISTS messages (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  subject VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);

-- Insert default admin user (password is 'admin123' hashed with bcrypt)
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@grit.com', '$2y$10$DKIqYZ0zI1J4iibcXJ4ftOkk2DigBP/TQWyisUxoVCRAxskL6B37S', 'admin');

-- Insert sample categories
INSERT INTO categories (name, description) VALUES 
('Streetwear', 'Urban clothing inspired by Bacolod''s underground scene'),
('Accessories', 'Caps, beanies, and urban accessories'),
('Footwear', 'Street shoes and sneakers'),
('Limited Edition', 'Exclusive drops and collaborations');

-- Insert sample products
INSERT INTO products (name, description, price, category_id, image, stock) VALUES 
('Bacolod Underground Hoodie', 'Signature black hoodie featuring Bacolod street art inspiration', 799.00, 1, 'black-hoodie.jpg', 20),
('Negros Street Denim', 'Locally-inspired denim jacket with urban Bacolod flair', 1299.00, 1, 'denim-jacket.jpg', 15),
('Silay Leather Wallet', 'Genuine leather wallet crafted by Negros artisans', 599.00, 2, 'leather-wallet.jpg', 30),
('Bacolod Canvas Sneakers', 'Limited edition sneakers inspired by Bacolod''s vibrant street culture', 1499.00, 3, 'sneakers.jpg', 25);