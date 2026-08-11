<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance();
    
    // Check if shipping_charge column exists in products table
    $stmt = $db->query("SHOW COLUMNS FROM products LIKE 'shipping_charge'");
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        $db->exec("ALTER TABLE products ADD COLUMN shipping_charge DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER tax_rate");
        echo "Successfully added 'shipping_charge' column to products table.\n";
        
        // Update existing products to have a default shipping charge of 50.00 for legacy reasons
        $db->exec("UPDATE products SET shipping_charge = 50.00");
        echo "Updated existing products to have a default shipping charge of 50.00.\n";
    } else {
        echo "Column 'shipping_charge' already exists in products table.\n";
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
