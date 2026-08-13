<?php
/**
 * Database Migration Script for Ekart Logistics Integration
 * Adds tracking_id, tracking_status, ekart_estimate_id, estimated_delivery_days to orders table safely
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Database.php';

try {
    $db = Database::getInstance();
    
    echo "Starting Ekart Logistics Database Migration...\n";

    $columns = [
        "tracking_id VARCHAR(50) DEFAULT NULL",
        "tracking_status VARCHAR(50) DEFAULT NULL",
        "estimated_delivery_days VARCHAR(50) DEFAULT NULL",
        "ekart_estimate_id VARCHAR(100) DEFAULT NULL"
    ];

    foreach ($columns as $columnDef) {
        $parts = explode(' ', $columnDef);
        $colName = $parts[0];
        
        try {
            $check = $db->query("SHOW COLUMNS FROM orders LIKE '$colName'");
            if (!$check || $check->rowCount() === 0) {
                $db->exec("ALTER TABLE orders ADD COLUMN $columnDef");
                echo "Added column: $colName\n";
            } else {
                echo "Column already exists: $colName\n";
            }
        } catch (PDOException $eCol) {
            echo "Notice on $colName: " . $eCol->getMessage() . "\n";
        }
    }

    echo "✅ Ekart Logistics Database Migration completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Migration error: " . $e->getMessage() . "\n";
}
