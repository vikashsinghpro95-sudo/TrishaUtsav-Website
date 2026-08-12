<?php
/**
 * Public Migration Runner for Delhivery Database Columns
 */
header('Content-Type: text/plain');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Database.php';

try {
    $db = Database::getInstance();
    
    echo "Starting Delhivery Database Migration...\n";

    $columnsToAdd = [
        "tracking_id VARCHAR(50) DEFAULT NULL",
        "tracking_status VARCHAR(50) DEFAULT NULL",
        "delhivery_pincode_verified TINYINT(1) DEFAULT 0",
        "estimated_delivery_days VARCHAR(50) DEFAULT NULL"
    ];

    foreach ($columnsToAdd as $colDef) {
        $colName = explode(' ', trim($colDef))[0];
        try {
            $stmtCheck = $db->query("SHOW COLUMNS FROM orders LIKE '$colName'");
            if ($stmtCheck && $stmtCheck->rowCount() > 0) {
                echo "Column '$colName' already exists in 'orders' table.\n";
            } else {
                $db->exec("ALTER TABLE orders ADD COLUMN $colDef");
                echo "Added column '$colName' to 'orders' table.\n";
            }
        } catch (PDOException $eCol) {
            echo "Notice for '$colName': " . $eCol->getMessage() . "\n";
        }
    }

    echo "✅ Delhivery Database Migration completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
}
