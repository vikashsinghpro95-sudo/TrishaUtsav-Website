<?php
/**
 * Wishlist Database Migration (Add-only, Safe)
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Database.php';

try {
    $db = Database::getInstance();

    echo "Running Wishlist Schema Update...\n";

    // 1. Create wishlists table
    $db->exec("
        CREATE TABLE IF NOT EXISTS wishlists (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_wishlist (user_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✅ Table `wishlists` created or already exists.\n";

    // 2. Create wishlist_items table
    $db->exec("
        CREATE TABLE IF NOT EXISTS wishlist_items (
            id INT PRIMARY KEY AUTO_INCREMENT,
            wishlist_id INT NOT NULL,
            product_id INT NOT NULL,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (wishlist_id) REFERENCES wishlists(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE KEY unique_wishlist_product (wishlist_id, product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✅ Table `wishlist_items` created or already exists.\n";

    echo "🎉 Wishlist Migration Complete!\n";
} catch (Exception $e) {
    echo "❌ Migration Error: " . $e->getMessage() . "\n";
}
