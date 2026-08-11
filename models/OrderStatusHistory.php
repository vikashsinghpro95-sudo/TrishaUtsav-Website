<?php
/**
 * Order Status History Database Model
 */

class OrderStatusHistory {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Record a new status movement entry
     *
     * @param int $orderId
     * @param string $status Target order_status enum state
     * @param string|null $comment Optional explanation/reason
     * @param int|null $createdBy User ID of the changer (admin or user)
     * @return int Created history record ID
     */
    public function log(int $orderId, string $status, ?string $comment = null, ?int $createdBy = null): int {
        $stmt = $this->db->prepare("
            INSERT INTO order_status_history (order_id, status, comment, created_by)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$orderId, $status, $comment, $createdBy]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Fetch status history log for an order
     *
     * @param int $orderId
     * @return array
     */
    public function getByOrder(int $orderId): array {
        $stmt = $this->db->prepare("
            SELECT osh.*, u.first_name, u.last_name, r.name as role_name
            FROM order_status_history osh
            LEFT JOIN users u ON osh.created_by = u.id
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE osh.order_id = ?
            ORDER BY osh.created_at DESC, osh.id DESC
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }
}
