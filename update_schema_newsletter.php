<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Database.php';

try {
    $db = Database::getInstance();
    
    // Create newsletter_subscribers table if it doesn't exist
    $query = "
        CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `email` VARCHAR(255) NOT NULL UNIQUE,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $db->exec($query);
    echo "Successfully created 'newsletter_subscribers' table if it didn't exist.\n";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
