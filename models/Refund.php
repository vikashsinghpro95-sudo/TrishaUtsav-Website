<?php
/**
 * Refund Database Model
 */

class Refund {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find refund by ID
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM refunds WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $refund = $stmt->fetch();
        return $refund ?: null;
    }

    /**
     * Get refunds related to an order
     *
     * @param int $orderId
     * @return array
     */
    public function getByOrder(int $orderId): array {
        $stmt = $this->db->prepare("SELECT * FROM refunds WHERE order_id = ? ORDER BY id DESC");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    /**
     * Log a new refund request
     *
     * @param array $data
     * @return int
     */
    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO refunds (payment_id, order_id, amount, reason, status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['payment_id'],
            $data['order_id'],
            $data['amount'],
            $data['reason'] ?? null,
            $data['status'] ?? 'pending'
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Mark a refund as processed (Admin action)
     *
     * @param int $id
     * @param int $adminId
     * @return bool
     */
    public function process(int $id, int $adminId): bool {
        $refund = $this->find($id);
        if (!$refund) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE refunds SET status = 'processed' WHERE id = ?");
        $updated = $stmt->execute([$id]);

        if ($updated) {
            // Log admin audit action
            Helper::logAction(
                $adminId,
                'process_refund',
                'refunds',
                $id,
                ['status' => 'pending'],
                ['status' => 'processed']
            );
        }

        return $updated;
    }

    /**
     * Get list of all refunds
     *
     * @return array
     */
    public function all(): array {
        $stmt = $this->db->query("
            SELECT r.*, o.order_number, p.transaction_id, u.first_name, u.last_name
            FROM refunds r
            INNER JOIN orders o ON r.order_id = o.id
            INNER JOIN payments p ON r.payment_id = p.id
            LEFT JOIN users u ON o.user_id = u.id
            ORDER BY r.id DESC
        ");
        return $stmt->fetchAll();
    }
}
