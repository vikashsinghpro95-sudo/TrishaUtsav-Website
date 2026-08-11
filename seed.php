<?php
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Access denied: Database seeder scripts can only be run via CLI.\n");
}
/**
 * Database Seed Script - V2
 * Runs in CLI to recreate and seed all tables for the E-Commerce System.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Database.php';

try {
    $db = Database::getInstance();
    echo "Connected to the database successfully.\n";

    // Disable foreign key checks for dropping/recreating
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // Drop all tables
    $tables = [
        'refunds',
        'payments',
        'shipments',
        'order_status_history',
        'order_items',
        'orders',
        'coupon_restrictions',
        'coupons',
        'cart_items',
        'carts',
        'addresses',
        'audit_logs',
        'inventory_log',
        'product_attributes',
        'product_images',
        'products',
        'brands',
        'categories',
        'user_sessions',
        'users',
        'roles',
        'settings',
        'banners',
        'pages'
    ];

    foreach ($tables as $table) {
        $db->exec("DROP TABLE IF EXISTS `$table` CASCADE");
        echo "Dropped table `$table` (if existed).\n";
    }

    // Create roles
    $db->exec("CREATE TABLE roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `roles`.\n";

    // Create users
    $db->exec("CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role_id INT NOT NULL DEFAULT 2,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(191) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NULL,
        avatar VARCHAR(255) NULL,
        is_verified TINYINT(1) DEFAULT 0,
        is_phone_verified TINYINT(1) DEFAULT 0,
        status ENUM('active','inactive','banned') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (email),
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `users`.\n";

    // Create user_sessions
    $db->exec("CREATE TABLE user_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(255) NOT NULL UNIQUE,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `user_sessions`.\n";

    // Create categories
    $db->exec("CREATE TABLE categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        parent_id INT NULL,
        name VARCHAR(191) NOT NULL,
        slug VARCHAR(191) NOT NULL UNIQUE,
        description TEXT NULL,
        image VARCHAR(255) NULL,
        status ENUM('active','inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (parent_id),
        FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `categories`.\n";

    // Create brands
    $db->exec("CREATE TABLE brands (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(191) NOT NULL UNIQUE,
        slug VARCHAR(191) NOT NULL UNIQUE,
        logo VARCHAR(255) NULL,
        status ENUM('active','inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `brands`.\n";

    // Create products
    $db->exec("CREATE TABLE products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        brand_id INT NULL,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        sku VARCHAR(100) UNIQUE,
        short_description TEXT NULL,
        description LONGTEXT NULL,
        price DECIMAL(12,2) NOT NULL,
        mrp DECIMAL(12,2) NULL,
        tax_rate DECIMAL(5,2) DEFAULT 0.00,
        stock_quantity INT NOT NULL DEFAULT 0,
        low_stock_threshold INT DEFAULT 5,
        weight DECIMAL(8,3) NULL,
        dimensions VARCHAR(100) NULL,
        status ENUM('draft','published','archived') DEFAULT 'draft',
        featured TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (category_id),
        INDEX (brand_id),
        FULLTEXT INDEX (name, short_description, description),
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
        FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `products`.\n";

    // Create product_images
    $db->exec("CREATE TABLE product_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        image_url VARCHAR(255) NOT NULL,
        alt_text VARCHAR(255) NULL,
        is_primary TINYINT(1) DEFAULT 0,
        sort_order INT DEFAULT 0,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `product_images`.\n";

    // Create product_attributes
    $db->exec("CREATE TABLE product_attributes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        attribute_name VARCHAR(100) NOT NULL,
        attribute_value VARCHAR(255) NOT NULL,
        extra_price DECIMAL(10,2) DEFAULT 0.00,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `product_attributes`.\n";

    // Create inventory_log
    $db->exec("CREATE TABLE inventory_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        user_id INT NOT NULL,
        change_type ENUM('added','removed','adjusted') NOT NULL,
        quantity_change INT NOT NULL,
        reason VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `inventory_log`.\n";

    // Create audit_logs
    $db->exec("CREATE TABLE audit_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        action VARCHAR(255) NOT NULL,
        entity_type VARCHAR(100) NULL,
        entity_id INT NULL,
        old_value JSON NULL,
        new_value JSON NULL,
        ip_address VARCHAR(45) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `audit_logs`.\n";

    // Create addresses
    $db->exec("CREATE TABLE addresses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type ENUM('shipping','billing') DEFAULT 'shipping',
        is_default TINYINT(1) DEFAULT 0,
        full_name VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        address_line1 VARCHAR(255) NOT NULL,
        address_line2 VARCHAR(255) NULL,
        city VARCHAR(100) NOT NULL,
        state VARCHAR(100) NOT NULL,
        pincode VARCHAR(10) NOT NULL,
        country VARCHAR(100) DEFAULT 'India',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `addresses`.\n";

    // Create carts
    $db->exec("CREATE TABLE carts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL UNIQUE,
        coupon_code VARCHAR(50) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `carts`.\n";

    // Create cart_items
    $db->exec("CREATE TABLE cart_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cart_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        price DECIMAL(12,2) NOT NULL,
        attributes JSON NULL,
        FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `cart_items`.\n";

    // Create coupons
    $db->exec("CREATE TABLE coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        type ENUM('percentage','fixed','free_shipping') NOT NULL,
        value DECIMAL(10,2) NOT NULL,
        min_cart_value DECIMAL(12,2) DEFAULT 0.00,
        max_discount DECIMAL(12,2) NULL,
        usage_limit INT NULL,
        used_count INT DEFAULT 0,
        expiry_date DATE NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `coupons`.\n";

    // Create coupon_restrictions
    $db->exec("CREATE TABLE coupon_restrictions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        coupon_id INT NOT NULL,
        restrict_type ENUM('product','category','user') NOT NULL,
        restrict_id INT NOT NULL,
        FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `coupon_restrictions`.\n";

    // Create orders
    $db->exec("CREATE TABLE orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(20) NOT NULL UNIQUE,
        user_id INT NULL,
        guest_email VARCHAR(255) NULL,
        shipping_address_id INT NULL,
        billing_address_id INT NULL,
        subtotal DECIMAL(12,2) NOT NULL,
        tax_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        shipping_charge DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        discount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        coupon_code VARCHAR(50) NULL,
        total DECIMAL(12,2) NOT NULL,
        payment_method VARCHAR(50) NULL,
        payment_status ENUM('pending','paid','failed','refunded','partially_refunded') DEFAULT 'pending',
        order_status ENUM('pending','confirmed','processing','packed','shipped','out_for_delivery','delivered','cancelled','returned') DEFAULT 'pending',
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (user_id),
        INDEX (order_number),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `orders`.\n";

    // Create order_items
    $db->exec("CREATE TABLE order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NULL,
        product_name VARCHAR(255) NOT NULL,
        sku VARCHAR(100) NULL,
        price DECIMAL(12,2) NOT NULL,
        quantity INT NOT NULL,
        total DECIMAL(12,2) NOT NULL,
        attributes JSON NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `order_items`.\n";

    // Create order_status_history
    $db->exec("CREATE TABLE order_status_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        status ENUM('pending','confirmed','processing','packed','shipped','out_for_delivery','delivered','cancelled','returned') NOT NULL,
        comment TEXT NULL,
        created_by INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `order_status_history`.\n";

    // Create payments
    $db->exec("CREATE TABLE payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        transaction_id VARCHAR(255) NULL,
        payment_method VARCHAR(50) NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        status ENUM('pending','success','failed') DEFAULT 'pending',
        payment_data JSON NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `payments`.\n";

    // Create refunds
    $db->exec("CREATE TABLE refunds (
        id INT AUTO_INCREMENT PRIMARY KEY,
        payment_id INT NOT NULL,
        order_id INT NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        reason TEXT NULL,
        status ENUM('pending','processed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `refunds`.\n";

    // Create shipments
    $db->exec("CREATE TABLE shipments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        courier_name VARCHAR(100) NULL,
        tracking_number VARCHAR(100) NULL,
        shipped_at DATETIME NULL,
        delivered_at DATETIME NULL,
        status ENUM('pending','shipped','in_transit','delivered','returned') DEFAULT 'pending',
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `shipments`.\n";

    // Create settings
    $db->exec("CREATE TABLE settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `settings`.\n";

    // Create banners
    $db->exec("CREATE TABLE banners (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        image_url VARCHAR(255) NOT NULL,
        link_url VARCHAR(255) NULL,
        status ENUM('active','inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `banners`.\n";

    // Create pages
    $db->exec("CREATE TABLE pages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content LONGTEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created table `pages`.\n";

    // Enable foreign key checks back
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "Foreign key checks re-enabled.\n";

    // ----------------------------------------------------
    // Seed Default Roles & Seed Admin User
    // ----------------------------------------------------
    $stmt = $db->prepare("INSERT INTO roles (id, name) VALUES (?, ?) ON DUPLICATE KEY UPDATE name=name");
    $stmt->execute([1, 'Super Admin']);
    $stmt->execute([2, 'Customer']);
    echo "Seeded default roles (Super Admin, Customer).\n";

    $adminEmail = 'admin@example.com';
    $adminPassword = password_hash('Admin@123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (role_id, first_name, last_name, email, password, phone, status, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([1, 'System', 'Administrator', $adminEmail, $adminPassword, '1234567890', 'active', 1]);
    echo "Seeded default admin user (admin@example.com / Admin@123).\n";

    // ----------------------------------------------------
    // Seed Sample Coupons
    // ----------------------------------------------------
    $stmt = $db->prepare("INSERT INTO coupons (code, type, value, min_cart_value, max_discount, usage_limit, is_active, expiry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    // WELCOME10: 10% off, min spend ₹100, max discount ₹50
    $stmt->execute(['WELCOME10', 'percentage', 10.00, 100.00, 50.00, 100, 1, date('Y-m-d', strtotime('+30 days'))]);
    
    // FLAT50: ₹50 off, min spend ₹200
    $stmt->execute(['FLAT50', 'fixed', 50.00, 200.00, null, 50, 1, date('Y-m-d', strtotime('+30 days'))]);
    
    // FREESHIP: Free shipping coupon
    $stmt->execute(['FREESHIP', 'free_shipping', 0.00, 0.00, null, null, 1, date('Y-m-d', strtotime('+30 days'))]);
    
    // EXPIRED20: Expired 20% off coupon
    $stmt->execute(['EXPIRED20', 'percentage', 20.00, 0.00, null, null, 1, date('Y-m-d', strtotime('-1 day'))]);
    
    echo "Seeded sample coupons.\n";

    // ----------------------------------------------------
    // Seed Sample Brands
    // ----------------------------------------------------
    $db->exec("INSERT INTO brands (id, name, slug, logo, status) VALUES 
        (1, 'Apple', 'apple', '', 'active'),
        (2, 'Samsung', 'samsung', '', 'active'),
        (3, 'OnePlus', 'oneplus', '', 'active'),
        (4, 'Xiaomi', 'xiaomi', '', 'active'),
        (5, 'Generic', 'generic', '', 'active')
    ");
    echo "Seeded brands.\n";

    // ----------------------------------------------------
    // Seed Sample Categories
    // ----------------------------------------------------
    $stmt = $db->prepare("INSERT INTO categories (id, parent_id, name, slug, description, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([1, null, 'Electronics', 'electronics', 'Gadgets and electronic appliances', 'active']);
    $stmt->execute([2, 1, 'Smartphones', 'smartphones', 'Handheld mobile devices', 'active']);
    $stmt->execute([3, 1, 'Laptops', 'laptops', 'Notebook computing machines', 'active']);
    $stmt->execute([4, 1, 'Audio Devices', 'audio', 'Headphones, earbuds, and speakers', 'active']);
    echo "Seeded categories.\n";

    // ----------------------------------------------------
    // Seed Sample Products
    // ----------------------------------------------------
    $stmt = $db->prepare("INSERT INTO products (id, category_id, brand_id, name, slug, sku, price, tax_rate, stock_quantity, status, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([1, 2, 1, 'iPhone 16 Pro', 'iphone-16-pro', 'IPH16PRO', 119900.00, 18.00, 50, 'published', 1]);
    $stmt->execute([2, 2, 2, 'Samsung Galaxy S24', 'samsung-galaxy-s24', 'SAMS24', 99900.00, 12.00, 10, 'published', 1]);
    $stmt->execute([3, 4, 5, 'Cheaper Cable', 'cheaper-cable', 'CABLE01', 100.00, 5.00, 100, 'published', 0]);
    $stmt->execute([4, 3, 1, 'MacBook Air M3', 'macbook-air-m3', 'MBA13M3', 114900.00, 18.00, 15, 'published', 1]);
    $stmt->execute([5, 4, 3, 'OnePlus Buds Pro', 'oneplus-buds-pro', 'OPBUDS', 9900.00, 18.00, 40, 'published', 1]);
    echo "Seeded products.\n";

    // Seed product attributes
    $stmt = $db->prepare("INSERT INTO product_attributes (product_id, attribute_name, attribute_value, extra_price) VALUES (?, ?, ?, ?)");
    $stmt->execute([1, 'Color', 'Titanium Grey', 0.00]);
    $stmt->execute([1, 'Storage', '256GB', 10000.00]);
    $stmt->execute([2, 'Color', 'Onyx Black', 0.00]);
    echo "Seeded product attributes.\n";

    // Seed product images
    $stmtImg = $db->prepare("INSERT INTO product_images (product_id, image_url, alt_text, is_primary, sort_order) VALUES (?, ?, ?, ?, ?)");
    $stmtImg->execute([1, 'uploads/products/iphone_16_pro.png', 'iPhone 16 Pro Titanium Grey', 1, 0]);
    $stmtImg->execute([2, 'uploads/products/samsung_s24.png', 'Samsung Galaxy S24 Onyx Black', 1, 0]);
    $stmtImg->execute([4, 'uploads/products/macbook_m3.png', 'MacBook Air M3 Space Grey', 1, 0]);
    $stmtImg->execute([5, 'uploads/products/oneplus_buds.png', 'OnePlus Buds Pro Matte Black', 1, 0]);
    echo "Seeded product images.\n";

    // Seed Customer Users
    $custPassword = password_hash('Customer@123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (id, role_id, first_name, last_name, email, password, phone, status, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([2, 2, 'Rahul', 'Sharma', 'rahul@example.com', $custPassword, '9876543211', 'active', 1]);
    $stmt->execute([3, 2, 'Priya', 'Patel', 'priya@example.com', $custPassword, '9876543212', 'active', 1]);
    $stmt->execute([4, 2, 'Amit', 'Verma', 'amit@example.com', $custPassword, '9876543213', 'active', 1]);
    echo "Seeded customer users.\n";

    // Seed Sample Addresses
    $stmt = $db->prepare("INSERT INTO addresses (id, user_id, type, is_default, full_name, phone, address_line1, address_line2, city, state, pincode, country) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([1, 2, 'shipping', 1, 'Rahul Sharma', '9876543211', 'Flat 402, Block B, Sunshine Apts', 'Sector 56', 'Gurugram', 'Haryana', '122011', 'India']);
    $stmt->execute([2, 2, 'billing', 1, 'Rahul Sharma', '9876543211', 'Flat 402, Block B, Sunshine Apts', 'Sector 56', 'Gurugram', 'Haryana', '122011', 'India']);
    $stmt->execute([3, 3, 'shipping', 1, 'Priya Patel', '9875643212', '12, Shanti Kunj', 'Near Park Avenue', 'Mumbai', 'Maharashtra', '400001', 'India']);
    echo "Seeded addresses.\n";

    // Seed Sample Orders
    $stmt = $db->prepare("
        INSERT INTO orders (id, order_number, user_id, guest_email, shipping_address_id, billing_address_id, subtotal, tax_amount, shipping_charge, discount, total, payment_method, payment_status, order_status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([1, 'ORD-10001', 2, null, 1, 2, 119900.00, 21582.00, 0.00, 50.00, 141432.00, 'Card', 'paid', 'delivered', date('Y-m-d H:i:s', strtotime('-14 days'))]);
    $stmt->execute([2, 'ORD-10002', 3, null, 3, 3, 99900.00, 11988.00, 0.00, 0.00, 111888.00, 'UPI', 'paid', 'processing', date('Y-m-d H:i:s', strtotime('-1 day'))]);
    $stmt->execute([3, 'ORD-10003', 2, null, 1, 2, 9900.00, 1782.00, 50.00, 0.00, 11732.00, 'COD', 'pending', 'pending', date('Y-m-d H:i:s')]);
    echo "Seeded orders.\n";

    // Seed Order Items
    $stmtItem = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, sku, price, quantity, total, attributes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtItem->execute([1, 1, 'iPhone 16 Pro', 'IPH16PRO', 119900.00, 1, 119900.00, json_encode(['Color' => 'Titanium Grey', 'Storage' => '256GB'])]);
    $stmtItem->execute([2, 2, 'Samsung Galaxy S24', 'SAMS24', 99900.00, 1, 99900.00, json_encode(['Color' => 'Onyx Black'])]);
    $stmtItem->execute([3, 5, 'OnePlus Buds Pro', 'OPBUDS', 9900.00, 1, 9900.00, null]);
    echo "Seeded order items.\n";

    // Seed Order Status History
    $stmtHist = $db->prepare("INSERT INTO order_status_history (order_id, status, comment, created_at) VALUES (?, ?, ?, ?)");
    $stmtHist->execute([1, 'pending', 'Order placed successfully', date('Y-m-d H:i:s', strtotime('-14 days'))]);
    $stmtHist->execute([1, 'confirmed', 'Order confirmed by store admin', date('Y-m-d H:i:s', strtotime('-13 days'))]);
    $stmtHist->execute([1, 'shipped', 'Dispatched via BlueDart tracking AW7729831', date('Y-m-d H:i:s', strtotime('-12 days'))]);
    $stmtHist->execute([1, 'delivered', 'Handed over to customer', date('Y-m-d H:i:s', strtotime('-10 days'))]);
    $stmtHist->execute([2, 'pending', 'Order placed successfully', date('Y-m-d H:i:s', strtotime('-1 day'))]);
    $stmtHist->execute([2, 'processing', 'Processing payment approval', date('Y-m-d H:i:s', strtotime('-12 hours'))]);
    $stmtHist->execute([3, 'pending', 'Order placed successfully', date('Y-m-d H:i:s')]);
    echo "Seeded order status histories.\n";

    // Seed Payments
    $stmtPay = $db->prepare("INSERT INTO payments (order_id, transaction_id, payment_method, amount, status, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtPay->execute([1, 'TXN-991209341', 'Card', 141432.00, 'success', date('Y-m-d H:i:s', strtotime('-14 days'))]);
    $stmtPay->execute([2, 'TXN-991209342', 'UPI', 111888.00, 'success', date('Y-m-d H:i:s', strtotime('-1 day'))]);
    $stmtPay->execute([3, null, 'COD', 11732.00, 'pending', date('Y-m-d H:i:s')]);
    echo "Seeded payments.\n";

    // Seed settings
    $stmtSet = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
    $stmtSet->execute(['store_name', 'Trisha Utsav']);
    $stmtSet->execute(['contact_email', 'support@trishautsav.com']);
    $stmtSet->execute(['contact_phone', '9876543210']);
    $stmtSet->execute(['currency', 'INR']);
    $stmtSet->execute(['tax_rate', '18.00']);
    $stmtSet->execute(['shipping_fee', '50.00']);
    $stmtSet->execute(['store_logo', '']);
    $stmtSet->execute(['hero_video_url', 'https://assets.mixkit.co/videos/preview/mixkit-diya-lamps-burning-in-the-dark-43301-large.mp4']);
    $stmtSet->execute(['hero_headline', 'Celebrate the Colors of Joy']);
    $stmtSet->execute(['hero_description', 'Explore premium electronics, state-of-the-art smartphones, and lifestyle items customized exactly for you.']);
    $stmtSet->execute(['hero_cta_text', 'Explore Festivities']);
    $stmtSet->execute(['hero_cta_link', 'shop.php']);
    $stmtSet->execute(['hero_overlay_opacity', '0.35']);
    $stmtSet->execute(['hero_overlay_color', '#000000']);
    $stmtSet->execute(['hero_hangings_enabled', 'true']);
    $stmtSet->execute(['hero_hangings_type', 'mixed']);
    $stmtSet->execute(['hero_hangings_count', '6']);
    $stmtSet->execute(['hero_hangings_gravity', '1.0']);
    echo "Seeded settings.\n";

    // Seed banners
    $stmtBan = $db->prepare("INSERT INTO banners (title, image_url, link_url, status) VALUES (?, ?, ?, ?)");
    $stmtBan->execute(['Grand Festive Launch', 'uploads/banners/hero_festive.png', 'shop.php', 'active']);
    echo "Seeded banners.\n";

    // Seed CMS pages
    $stmtPag = $db->prepare("INSERT INTO pages (title, slug, content) VALUES (?, ?, ?)");
    $stmtPag->execute(['About Our Brand', 'about', '<p>Welcome to Trisha Utsav. We deliver high-quality products directly to you.</p>']);
    echo "Seeded CMS pages.\n";

    echo "Seeding completed successfully.\n";

} catch (Exception $e) {
    echo "Error running database seeder: " . $e->getMessage() . "\n";
    exit(1);
}
