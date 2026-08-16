<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Database.php';

try {
    $db = Database::getInstance();
    
    // Fetch all orders
    $stmt = $db->query("SELECT id, order_number, created_at FROM orders ORDER BY created_at ASC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $count = 1;
    $updated = 0;
    
    foreach ($orders as $order) {
        $orderId = $order['id'];
        
        // Skip if already has INV format
        if (strpos($order['order_number'], 'INV-') === 0) {
            $count++;
            continue;
        }
        
        // Get the first product for this order
        $itemStmt = $db->prepare("SELECT product_id FROM order_items WHERE order_id = ? LIMIT 1");
        $itemStmt->execute([$orderId]);
        $productId = $itemStmt->fetchColumn() ?: 0;
        
        // Generate new order number
        $orderExists = true;
        $orderNumber = '';
        $stmtCheckORD = $db->prepare("SELECT COUNT(*) FROM orders WHERE order_number = ? AND id != ?");
        
        while ($orderExists) {
            $rand = strtoupper(bin2hex(random_bytes(2)));
            $orderNumber = "INV-P{$productId}-{$count}-{$rand}";
            
            $stmtCheckORD->execute([$orderNumber, $orderId]);
            if ((int)$stmtCheckORD->fetchColumn() === 0) {
                $orderExists = false;
            }
        }
        
        // Update order
        $updateStmt = $db->prepare("UPDATE orders SET order_number = ? WHERE id = ?");
        $updateStmt->execute([$orderNumber, $orderId]);
        
        echo "Updated Order ID {$orderId} from {$order['order_number']} to {$orderNumber}\n";
        
        $count++;
        $updated++;
    }
    
    echo "Successfully updated {$updated} orders to the new invoice format.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
