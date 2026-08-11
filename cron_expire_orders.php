<?php
/**
 * Cron Job: Expire abandoned orders and restore inventory
 * Run this every 5-10 minutes via standard cron
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/config.php';

try {
    $db = Database::getInstance();
    
    // Find all online orders that are pending_payment and have passed expires_at
    $stmt = $db->prepare("
        SELECT id, order_number, user_id 
        FROM orders 
        WHERE payment_status = 'pending_payment' 
          AND expires_at IS NOT NULL 
          AND expires_at < NOW()
          AND order_status != 'expired'
          AND order_status != 'cancelled'
    ");
    $stmt->execute();
    $expiredOrders = $stmt->fetchAll();

    if (!$expiredOrders) {
        echo "No expired orders found.\n";
        exit;
    }

    $db->beginTransaction();

    $stmtUpdateOrder = $db->prepare("
        UPDATE orders 
        SET order_status = 'expired', 
            payment_status = 'failed' 
        WHERE id = ?
    ");

    $stmtGetItems = $db->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
    $stmtRestoreStock = $db->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?");
    
    $stmtInvLog = $db->prepare("
        INSERT INTO inventory_log (product_id, user_id, change_type, quantity_change, reason)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmtOrderLog = $db->prepare("
        INSERT INTO order_status_history (order_id, status, comment, created_by)
        VALUES (?, 'expired', 'Order automatically expired due to non-payment.', 0)
    ");

    $count = 0;
    foreach ($expiredOrders as $order) {
        $orderId = $order['id'];
        $userId = $order['user_id'];
        $orderNumber = $order['order_number'];

        // Mark as expired
        $stmtUpdateOrder->execute([$orderId]);

        // Get items and restore stock
        $stmtGetItems->execute([$orderId]);
        $items = $stmtGetItems->fetchAll();

        foreach ($items as $item) {
            $qty = (int)$item['quantity'];
            $pId = $item['product_id'];

            if ($qty > 0) {
                // Restore
                $stmtRestoreStock->execute([$qty, $pId]);
                // Log
                $stmtInvLog->execute([
                    $pId, 
                    $userId, 
                    'added', 
                    $qty, 
                    "Order Expired: $orderNumber"
                ]);
            }
        }

        // Log order history
        $stmtOrderLog->execute([$orderId]);

        $count++;
    }

    $db->commit();
    echo "Successfully expired $count orders and restored their inventory.\n";

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "Cron error: " . $e->getMessage() . "\n";
}
