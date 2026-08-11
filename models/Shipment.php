<?php
/**
 * Shipment Database Model
 */

class Shipment {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find shipment by ID
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM shipments WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $shipment = $stmt->fetch();
        return $shipment ?: null;
    }

    /**
     * Find shipments associated with an order
     *
     * @param int $orderId
     * @return array
     */
    public function getByOrder(int $orderId): array {
        $stmt = $this->db->prepare("SELECT * FROM shipments WHERE order_id = ? ORDER BY id DESC");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    /**
     * Create a shipment record, transition order status to 'shipped', log in status history and audit
     *
     * @param int $orderId
     * @param string|null $courierName
     * @param string|null $trackingNumber
     * @param int $adminId Administrator performing dispatch
     * @return int Created shipment ID
     * @throws Exception
     */
    public function createShipment(int $orderId, ?string $courierName, ?string $trackingNumber, int $adminId): int {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ? FOR UPDATE");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();

            if (!$order) {
                throw new Exception("Order not found.");
            }

            // Validate that we only ship processed/confirmed orders
            if (in_array($order['order_status'], ['cancelled', 'returned', 'shipped', 'delivered'])) {
                throw new Exception("Cannot ship order at its current status: " . $order['order_status']);
            }

            // Create shipment entry
            $stmtInsert = $this->db->prepare("
                INSERT INTO shipments (order_id, courier_name, tracking_number, status, shipped_at)
                VALUES (?, ?, ?, 'shipped', NOW())
            ");
            $stmtInsert->execute([$orderId, $courierName, $trackingNumber]);
            $shipmentId = (int)$this->db->lastInsertId();

            // Update order status to shipped
            $stmtUpdateOrder = $this->db->prepare("UPDATE orders SET order_status = 'shipped' WHERE id = ?");
            $stmtUpdateOrder->execute([$orderId]);

            // Add order status history entry
            $historyModel = new OrderStatusHistory();
            $historyModel->log(
                $orderId,
                'shipped',
                "Shipment dispatched. Courier: " . ($courierName ?? 'Standard') . ". Tracking ID: " . ($trackingNumber ?? 'N/A'),
                $adminId
            );

            // Log administrative audit action
            Helper::logAction(
                $adminId,
                'dispatch_order',
                'orders',
                $orderId,
                ['order_status' => $order['order_status']],
                ['order_status' => 'shipped']
            );

            $this->db->commit();
            return $shipmentId;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Update tracking status for an active shipment
     *
     * @param int $id Shipment ID
     * @param string $status enum: 'pending', 'shipped', 'in_transit', 'delivered', 'returned'
     * @param int $adminId
     * @return bool
     * @throws Exception
     */
    public function updateStatus(int $id, string $status, int $adminId): bool {
        try {
            $this->db->beginTransaction();

            $shipment = $this->find($id);
            if (!$shipment) {
                throw new Exception("Shipment not found.");
            }

            $orderId = (int)$shipment['order_id'];

            // Update shipment status
            $deliveredAt = ($status === 'delivered') ? date('Y-m-d H:i:s') : null;
            if ($deliveredAt) {
                $stmt = $this->db->prepare("UPDATE shipments SET status = ?, delivered_at = ? WHERE id = ?");
                $stmt->execute([$status, $deliveredAt, $id]);
            } else {
                $stmt = $this->db->prepare("UPDATE shipments SET status = ? WHERE id = ?");
                $stmt->execute([$status, $id]);
            }

            // If shipment is marked delivered, update order status to delivered
            if ($status === 'delivered') {
                $stmtOrder = $this->db->prepare("UPDATE orders SET order_status = 'delivered' WHERE id = ?");
                $stmtOrder->execute([$orderId]);

                $historyModel = new OrderStatusHistory();
                $historyModel->log($orderId, 'delivered', "Delivery confirmed by courier tracking.", $adminId);

                // Audit log
                Helper::logAction(
                    $adminId,
                    'deliver_order',
                    'orders',
                    $orderId,
                    ['order_status' => 'shipped'],
                    ['order_status' => 'delivered']
                );
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
