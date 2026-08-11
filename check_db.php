<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Database.php';

try {
    $db = Database::getInstance();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $db->query("SELECT id, name FROM products");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total rows: " . count($rows) . "\n";
    foreach ($rows as $r) {
        echo $r['id'] . " - " . $r['name'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
