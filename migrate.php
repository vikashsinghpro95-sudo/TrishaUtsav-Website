<?php
require_once __DIR__ . '/config/database.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'shipping_charge'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE products ADD COLUMN shipping_charge DECIMAL(10,2) DEFAULT 50.00 AFTER tax_rate");
        echo "Column 'shipping_charge' added successfully to 'products' table.<br>";
    } else {
        echo "Column 'shipping_charge' already exists.<br>";
    }
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>
