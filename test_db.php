<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Database.php';

try {
    $db = Database::getInstance();
    echo "Database Connection Successful!\n";
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(', ', $tables);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
