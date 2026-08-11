<?php
/**
 * Payment Database Model
 */

class Payment {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find payment by ID
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $payment = $stmt->fetch();
        if ($payment) {
            $payment['payment_data'] = $payment['payment_data'] ? json_decode($payment['payment_data'], true) : null;
        }
        return $payment ?: null;
    }

    /**
     * Create payment entry
     *
     * @param array $data
     * @return int
     */
    public function create(array $data): int {
        $paymentDataJson = isset($data['payment_data']) ? json_encode($data['payment_data'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;

        $stmt = $this->db->prepare("
            INSERT INTO payments (order_id, transaction_id, payment_method, amount, status, payment_data)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['order_id'],
            $data['transaction_id'] ?? null,
            $data['payment_method'],
            $data['amount'],
            $data['status'] ?? 'pending',
            $paymentDataJson
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Verify payment status, write transaction logs, and transition order states
     *
     * @param int $orderId
     * @param string $transactionId gateway receipt code
     * @param string $method payment channel used (razorpay, stripe, cod, etc.)
     * @param float $amount total transaction amount
     * @param string $status success / failed / pending
     * @param array|null $paymentData raw gateway payload
     * @return bool
     * @throws Exception
     */
    public function verifyPayment(
        int $orderId, 
        string $transactionId, 
        string $method, 
        float $amount, 
        string $status, 
        ?array $paymentData = null
    ): bool {
        try {
            $this->db->beginTransaction();

            // Find matching order
            $stmtOrder = $this->db->prepare("SELECT * FROM orders WHERE id = ? FOR UPDATE");
            $stmtOrder->execute([$orderId]);
            $order = $stmtOrder->fetch();

            if (!$order) {
                throw new Exception("Order not found.");
            }

            // Create payment log record
            $paymentId = $this->create([
                'order_id' => $orderId,
                'transaction_id' => $transactionId,
                'payment_method' => $method,
                'amount' => $amount,
                'status' => $status,
                'payment_data' => $paymentData
            ]);

            $historyModel = new OrderStatusHistory();

            if ($status === 'success') {
                // Update Order status to paid and confirmed
                $stmtUpdate = $this->db->prepare("
                    UPDATE orders 
                    SET payment_status = 'paid', order_status = 'confirmed', payment_method = ? 
                    WHERE id = ?
                ");
                $stmtUpdate->execute([$method, $orderId]);

                // Log status history transition
                $historyModel->log($orderId, 'confirmed', "Payment verified successfully. Transaction ID: $transactionId", null);
            } else {
                // Update Order payment status to failed
                $stmtUpdate = $this->db->prepare("
                    UPDATE orders 
                    SET payment_status = 'failed', payment_method = ? 
                    WHERE id = ?
                ");
                $stmtUpdate->execute([$method, $orderId]);
                
                // Do not update order_status to cancelled automatically so customer can retry payment
                $historyModel->log($orderId, $order['order_status'], "Payment verification failed. Transaction ID: $transactionId", null);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get list of all payments (for admin)
     *
     * @return array
     */
    public function all(): array {
        $stmt = $this->db->query("
            SELECT p.*, o.order_number, u.first_name, u.last_name, u.email
            FROM payments p
            INNER JOIN orders o ON p.order_id = o.id
            LEFT JOIN users u ON o.user_id = u.id
            ORDER BY p.id DESC
        ");
        $payments = $stmt->fetchAll();
        foreach ($payments as &$pay) {
            $pay['payment_data'] = $pay['payment_data'] ? json_decode($pay['payment_data'], true) : null;
        }
        return $payments;
    }
}
