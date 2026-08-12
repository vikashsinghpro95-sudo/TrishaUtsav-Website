<?php
/**
 * Database Optimization & Indexing Migration Script
 * Adds composite indexes to products, product_images, categories, occasions, wishlist tables
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Database.php';

try {
    $db = Database::getInstance();
    
    echo "Starting Database Optimization & Indexing...\n";

    $indexes = [
        "CREATE INDEX idx_products_status_created ON products (status, created_at DESC)",
        "CREATE INDEX idx_products_status_category ON products (status, category_id)",
        "CREATE INDEX idx_products_status_occasion ON products (status, occasion_id)",
        "CREATE INDEX idx_products_status_trending ON products (status, is_trending)",
        "CREATE INDEX idx_products_status_must_buy ON products (status, is_must_buy)",
        "CREATE INDEX idx_products_status_featured ON products (status, featured)",
        "CREATE INDEX idx_product_images_pid_sort ON product_images (product_id, is_primary, sort_order)",
        "CREATE INDEX idx_product_attributes_pid ON product_attributes (product_id)"
    ];

    foreach ($indexes as $sql) {
        try {
            $db->exec($sql);
            echo "Successfully applied index: " . substr($sql, 0, 50) . "...\n";
        } catch (PDOException $eIdx) {
            // Ignore if index already exists
            echo "Notice: " . $eIdx->getMessage() . "\n";
        }
    }

    echo "✅ Database indexing completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Optimization script error: " . $e->getMessage() . "\n";
}
