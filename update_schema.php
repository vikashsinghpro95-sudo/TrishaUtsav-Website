<?php
require_once __DIR__ . '/config/database.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Modify order_status
    $pdo->exec("ALTER TABLE orders MODIFY COLUMN order_status ENUM('pending','confirmed','processing','packed','shipped','out_for_delivery','delivered','cancelled','returned','expired') DEFAULT 'pending'");
    
    // Also modify payment_status if not already a VARCHAR (some setups have it as ENUM)
    $pdo->exec("ALTER TABLE orders MODIFY COLUMN payment_status VARCHAR(50) DEFAULT 'pending'");
    
    // Check and add title to product_images
    $stmt = $pdo->query("SHOW COLUMNS FROM product_images LIKE 'title'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE product_images ADD COLUMN title VARCHAR(255) NULL AFTER alt_text");
        echo "Successfully added title column to product_images table.\n";
    }

    // Check and add shipping_charge to products
    $stmt2 = $pdo->query("SHOW COLUMNS FROM products LIKE 'shipping_charge'");
    if (!$stmt2->fetch()) {
        $pdo->exec("ALTER TABLE products ADD COLUMN shipping_charge DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER tax_rate");
        echo "Successfully added shipping_charge column to products table.\n";
    } else {
        $pdo->exec("ALTER TABLE products MODIFY COLUMN shipping_charge DECIMAL(10,2) NOT NULL DEFAULT 0.00");
    }

    // Check and add SEO metadata columns to products table
    $seoColumns = [
        'meta_title'       => "ALTER TABLE products ADD COLUMN meta_title VARCHAR(255) NULL AFTER is_must_buy",
        'meta_keywords'    => "ALTER TABLE products ADD COLUMN meta_keywords TEXT NULL AFTER meta_title",
        'meta_description' => "ALTER TABLE products ADD COLUMN meta_description TEXT NULL AFTER meta_keywords",
    ];

    foreach ($seoColumns as $col => $sql) {
        $chk = $pdo->query("SHOW COLUMNS FROM products LIKE '$col'");
        if (!$chk->fetch()) {
            $pdo->exec($sql);
            echo "Successfully added '$col' column to products table.\n";
        }
    }

    // Check and add missing Razorpay and payment fields to orders table
    $orderColumns = [
        'guest_email'         => "ALTER TABLE orders ADD COLUMN guest_email VARCHAR(191) NULL AFTER user_id",
        'notes'               => "ALTER TABLE orders ADD COLUMN notes TEXT NULL AFTER order_status",
        'razorpay_order_id'   => "ALTER TABLE orders ADD COLUMN razorpay_order_id VARCHAR(255) NULL AFTER notes",
        'razorpay_payment_id' => "ALTER TABLE orders ADD COLUMN razorpay_payment_id VARCHAR(255) NULL AFTER razorpay_order_id",
        'razorpay_signature'  => "ALTER TABLE orders ADD COLUMN razorpay_signature VARCHAR(255) NULL AFTER razorpay_payment_id",
        'attempts'            => "ALTER TABLE orders ADD COLUMN attempts INT NOT NULL DEFAULT 0 AFTER razorpay_signature",
        'expires_at'          => "ALTER TABLE orders ADD COLUMN expires_at DATETIME NULL AFTER attempts"
    ];

    foreach ($orderColumns as $col => $sql) {
        $chk = $pdo->query("SHOW COLUMNS FROM orders LIKE '$col'");
        if (!$chk->fetch()) {
            $pdo->exec($sql);
            echo "Successfully added '$col' column to orders table.\n";
        }
    }
    
    echo "Successfully updated orders table schemas.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
