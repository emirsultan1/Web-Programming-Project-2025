-- =========================================================
-- Clean rebuild (optional during development)
-- =========================================================
DROP DATABASE IF EXISTS clothing_store;
CREATE DATABASE clothing_store
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE clothing_store;

-- =========================================================
-- 1) USERS
-- =========================================================
CREATE TABLE users (
  user_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name           VARCHAR(100) NOT NULL,
  email          VARCHAR(150) NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  role           ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 2) CATEGORIES
-- =========================================================
CREATE TABLE categories (
  category_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name           VARCHAR(100) NOT NULL UNIQUE,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 3) PRODUCTS
--    - Keeps DECIMAL(10,2) for price (ok for now)
--    - FK: category_id (SET NULL on delete)
-- =========================================================
CREATE TABLE products (
  product_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name           VARCHAR(150) NOT NULL,
  description    TEXT,
  price          DECIMAL(10,2) NOT NULL,
  stock_qty      INT NOT NULL DEFAULT 0,
  category_id    INT UNSIGNED NULL,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_products_category (category_id),
  CONSTRAINT fk_products_category
    FOREIGN KEY (category_id)
    REFERENCES categories(category_id)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 4) ORDERS
--    - Status includes common states (keep your set; added updated_at)
-- =========================================================
CREATE TABLE orders (
  order_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id        INT UNSIGNED NOT NULL,
  order_date     DATETIME DEFAULT CURRENT_TIMESTAMP,
  total_amount   DECIMAL(10,2) NOT NULL DEFAULT 0,
  status         ENUM('pending','paid','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_orders_user (user_id),
  CONSTRAINT fk_orders_user
    FOREIGN KEY (user_id)
    REFERENCES users(user_id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 5) ORDER ITEMS
--    - Records chosen variant at time of purchase (size, color)
--    - Keeps a price snapshot in item_price
-- =========================================================
CREATE TABLE order_items (
  order_item_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id           INT UNSIGNED NOT NULL,
  product_id         INT UNSIGNED NOT NULL,
  size               VARCHAR(32) NULL,
  color              VARCHAR(64) NULL,
  quantity           INT UNSIGNED NOT NULL,
  item_price         DECIMAL(10,2) NOT NULL,
  created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_items_order   (order_id),
  INDEX idx_items_product (product_id),
  CONSTRAINT fk_items_order
    FOREIGN KEY (order_id)
    REFERENCES orders(order_id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_items_product
    FOREIGN KEY (product_id)
    REFERENCES products(product_id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 6) REVIEWS
--    - Prevent duplicate review per user/product
-- =========================================================
CREATE TABLE reviews (
  review_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id        INT UNSIGNED NOT NULL,
  product_id     INT UNSIGNED NOT NULL,
  rating         TINYINT UNSIGNED NOT NULL,
  comment        TEXT,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_product (user_id, product_id),
  INDEX idx_reviews_product (product_id),
  CONSTRAINT fk_reviews_user
    FOREIGN KEY (user_id)
    REFERENCES users(user_id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_reviews_product
    FOREIGN KEY (product_id)
    REFERENCES products(product_id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  -- If your MySQL >= 8.0.16, this CHECK will be enforced:
  CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- 7) PAYMENTS
--    - Added currency + transaction_id + metadata for future gateways
-- =========================================================
CREATE TABLE payments (
  payment_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id       INT UNSIGNED NOT NULL,
  payment_date   DATETIME DEFAULT CURRENT_TIMESTAMP,
  payment_method ENUM('credit_card','paypal','cash_on_delivery') NOT NULL DEFAULT 'credit_card',
  amount         DECIMAL(10,2) NOT NULL,
  currency       CHAR(3) NOT NULL DEFAULT 'USD',
  status         ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  transaction_id VARCHAR(191) NULL,
  metadata       JSON NULL,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_payments_order (order_id),
  CONSTRAINT fk_payments_order
    FOREIGN KEY (order_id)
    REFERENCES orders(order_id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- SAMPLE DATA (Optional)
-- =========================================================
INSERT INTO categories (name) VALUES
  ('Shirts'), ('Pants'), ('Shoes');

INSERT INTO users (name, email, password_hash, role) VALUES
  ('Admin User', 'admin@example.com', 'hashed_password_here', 'admin'),
  ('John Doe',   'john@example.com',  'hashed_password_here', 'customer');

INSERT INTO products (name, description, price, stock_qty, category_id) VALUES
  ('Blue Shirt',     'Cotton shirt in blue color', 29.99, 15, 1),
  ('Black Jeans',    'Slim fit jeans',             49.50,  8, 2),
  ('White Sneakers', 'Casual sneakers',            79.00, 12, 3);
