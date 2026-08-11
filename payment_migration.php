<?php
require_once __DIR__ . '/config/database.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "Starting Payment Schema Migration...<br><br>";

    // Add razorpay_order_id
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'razorpay_order_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN razorpay_order_id VARCHAR(255) NULL DEFAULT NULL AFTER payment_method");
        echo "✅ Added 'razorpay_order_id' to orders.<br>";
    } else {
        echo "ℹ️ Column 'razorpay_order_id' already exists.<br>";
    }

    // Add razorpay_payment_id
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'razorpay_payment_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN razorpay_payment_id VARCHAR(255) NULL DEFAULT NULL AFTER razorpay_order_id");
        echo "✅ Added 'razorpay_payment_id' to orders.<br>";
    } else {
        echo "ℹ️ Column 'razorpay_payment_id' already exists.<br>";
    }

    // Add razorpay_signature
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'razorpay_signature'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN razorpay_signature VARCHAR(255) NULL DEFAULT NULL AFTER razorpay_payment_id");
        echo "✅ Added 'razorpay_signature' to orders.<br>";
    } else {
        echo "ℹ️ Column 'razorpay_signature' already exists.<br>";
    }

    // Add attempts
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'attempts'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN attempts INT DEFAULT 0 AFTER razorpay_signature");
        echo "✅ Added 'attempts' to orders.<br>";
    } else {
        echo "ℹ️ Column 'attempts' already exists.<br>";
    }

    // Add expires_at
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'expires_at'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN expires_at DATETIME NULL DEFAULT NULL AFTER attempts");
        echo "✅ Added 'expires_at' to orders.<br>";
    } else {
        echo "ℹ️ Column 'expires_at' already exists.<br>";
    }

    // Update payment_status Enum (modify existing column definition, typically varchar or enum)
    // First, let's check what it is. To be safe, we'll just alter it to VARCHAR(50) if it isn't already.
    $pdo->exec("ALTER TABLE orders MODIFY COLUMN payment_status VARCHAR(50) DEFAULT 'pending'");
    echo "✅ Modified 'payment_status' to allow custom states.<br>";
    
    // Create payment_logs table
    $createLogsTable = "
        CREATE TABLE IF NOT EXISTS payment_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NULL,
            event VARCHAR(255) NOT NULL,
            gateway_response JSON NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_order_id (order_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($createLogsTable);
    echo "✅ Created 'payment_logs' table.<br>";

    echo "<br><strong>Migration Complete!</strong>";

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>
