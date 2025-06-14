-- 建立資料庫，並設定字元集為 utf8mb4
CREATE DATABASE IF NOT EXISTS ecommerce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecommerce;

-- 商品表
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT '商品名稱',
    price DECIMAL(10,2) NOT NULL COMMENT '商品價格',
    image_url VARCHAR(255) NOT NULL COMMENT '商品圖片網址',
    description TEXT COMMENT '商品描述',
    stock_quantity INT NOT NULL DEFAULT 0 COMMENT '庫存數量',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '商品建立時間',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '商品最後更新時間',
    INDEX idx_name (name),
    INDEX idx_price (price),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='商品資訊表';

-- 使用者表
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE COMMENT '使用者名稱 (唯一)',
    email VARCHAR(100) NOT NULL UNIQUE COMMENT '電子郵件 (唯一)',
    password_hash VARCHAR(255) NOT NULL COMMENT '密碼雜湊值',
    first_name VARCHAR(50) COMMENT '名字',
    last_name VARCHAR(50) COMMENT '姓氏',
    address VARCHAR(255) COMMENT '送貨地址',
    phone_number VARCHAR(20) COMMENT '電話號碼',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '帳戶建立時間',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '帳戶最後更新時間',
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='使用者帳戶資訊表';

-- 訂單表
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT '下訂單的使用者ID',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '訂單建立日期',
    total_amount DECIMAL(10,2) NOT NULL COMMENT '訂單總金額',
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending' COMMENT '訂單狀態',
    shipping_address VARCHAR(255) COMMENT '訂單送貨地址',
    CONSTRAINT fk_orders_user_id FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_order_date (order_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='訂單總覽表';

-- 訂單商品項目表
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL COMMENT '所屬訂單的ID',
    product_id INT NOT NULL COMMENT '商品的ID',
    quantity INT NOT NULL COMMENT '商品數量',
    price_at_purchase DECIMAL(10,2) NOT NULL COMMENT '購買時的商品單價',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '項目建立時間',
    CONSTRAINT fk_order_items_order_id FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_order_items_product_id FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id),
    INDEX idx_created_at (created_at),
    INDEX idx_order_product (order_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='訂單包含的商品細項表';

-- 熱銷商品快取表
CREATE TABLE IF NOT EXISTS top_products_cache (
    product_id INT PRIMARY KEY COMMENT '熱銷商品ID',
    name VARCHAR(100) NOT NULL COMMENT '商品名稱',
    price DECIMAL(10,2) NOT NULL COMMENT '商品價格',
    image_url VARCHAR(255) NOT NULL COMMENT '商品圖片網址',
    sales_count INT NOT NULL COMMENT '銷售數量',
    rank_order INT NOT NULL COMMENT '熱銷排名',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '快取更新時間',
    CONSTRAINT fk_top_products_cache_product_id FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='熱銷商品快取表';