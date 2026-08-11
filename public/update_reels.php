<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Database.php';

try {
    $db = Database::getInstance();
    
    // Hardcoded updates to match festive theme instead of tech
    $updates = [
        1 => "Rakhi Special Set Preview",
        2 => "Premium Lumba Rakhi Showcase",
        3 => "Designer Bhaiya Bhabhi Rakhi",
        4 => "Festive Sweets Unboxing",
        5 => "Handcrafted Krishna Rakhi"
    ];

    $stmt = $db->prepare("UPDATE reels SET title = ? WHERE id = ?");
    
    foreach ($updates as $id => $title) {
        $stmt->execute([$title, $id]);
    }
    
    // Also update any others to a generic title if they exist beyond 5
    $stmtGeneric = $db->prepare("UPDATE reels SET title = 'Royal Festive Treat Showcase' WHERE id > 5");
    $stmtGeneric->execute();

    echo "Reels updated successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
