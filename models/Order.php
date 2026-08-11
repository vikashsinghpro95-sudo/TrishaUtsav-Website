<?php
/**
 * Order Database Model
 */

class Order {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find standard order by ID
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        return $order ?: null;
    }

    /**
     * Fetch complete order details including related records
     *
     * @param int $orderId
     * @return array|null
     */
    public function findWithDetails(int $orderId): ?array {
        $order = $this->find($orderId);
        if (!$order) {
            return null;
        }

        // Fetch user info if user_id exists
        if (!empty($order['user_id'])) {
            $stmtUser = $this->db->prepare("SELECT first_name, last_name, email, phone FROM users WHERE id = ? LIMIT 1");
            $stmtUser->execute([(int)$order['user_id']]);
            $user = $stmtUser->fetch();
            if ($user) {
                $order['first_name'] = $user['first_name'];
                $order['last_name'] = $user['last_name'];
                $order['email'] = $user['email'];
                $order['phone'] = $user['phone'];
            }
        }

        // Fetch items
        $itemModel = new OrderItem();
        $order['items'] = $itemModel->getByOrder($orderId);

        // Fetch status history
        $historyModel = new OrderStatusHistory();
        $order['status_history'] = $historyModel->getByOrder($orderId);

        // Fetch addresses
        $addressModel = new Address();
        $order['shipping_address'] = $order['shipping_address_id'] ? $addressModel->find((int)$order['shipping_address_id']) : null;
        $order['billing_address'] = $order['billing_address_id'] ? $addressModel->find((int)$order['billing_address_id']) : null;

        // Fetch payments
        $stmtPay = $this->db->prepare("SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC");
        $stmtPay->execute([$orderId]);
        $order['payments'] = $stmtPay->fetchAll();
        foreach ($order['payments'] as &$pay) {
            $pay['payment_data'] = $pay['payment_data'] ? json_decode($pay['payment_data'], true) : null;
        }
        unset($pay);

        // Fetch shipments
        $stmtShip = $this->db->prepare("SELECT * FROM shipments WHERE order_id = ? ORDER BY id DESC");
        $stmtShip->execute([$orderId]);
        $order['shipments'] = $stmtShip->fetchAll();

        // Fetch refunds
        $stmtRef = $this->db->prepare("SELECT * FROM refunds WHERE order_id = ? ORDER BY id DESC");
        $stmtRef->execute([$orderId]);
        $order['refunds'] = $stmtRef->fetchAll();

        return $order;
    }

    /**
     * Create order record
     *
     * @param array $data Order details
     * @return int Created order ID
     */
    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO orders (
                order_number, user_id, guest_email, shipping_address_id, billing_address_id,
                subtotal, tax_amount, shipping_charge, discount, coupon_code, total,
                payment_method, payment_status, order_status, notes,
                razorpay_order_id, razorpay_payment_id, razorpay_signature, attempts, expires_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['order_number'],
            $data['user_id'] ?? null,
            $data['guest_email'] ?? null,
            $data['shipping_address_id'],
            $data['billing_address_id'] ?? $data['shipping_address_id'],
            $data['subtotal'],
            $data['tax_amount'] ?? 0.00,
            $data['shipping_charge'] ?? 0.00,
            $data['discount'] ?? 0.00,
            $data['coupon_code'] ?? null,
            $data['total'],
            $data['payment_method'],
            $data['payment_status'] ?? 'pending',
            $data['order_status'] ?? 'pending',
            $data['notes'] ?? null,
            $data['razorpay_order_id'] ?? null,
            $data['razorpay_payment_id'] ?? null,
            $data['razorpay_signature'] ?? null,
            $data['attempts'] ?? 0,
            $data['expires_at'] ?? null
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update order with Razorpay Gateway Details
     */
    public function updateRazorpayDetails(int $orderId, string $razorpayOrderId, string $expiresAt, int $attempts): bool {
        $stmt = $this->db->prepare("UPDATE orders SET razorpay_order_id = ?, expires_at = ?, attempts = ? WHERE id = ?");
        return $stmt->execute([$razorpayOrderId, $expiresAt, $attempts, $orderId]);
    }

    /**
     * Update payment verification details
     */
    public function finalizeRazorpayPayment(int $orderId, string $paymentId, string $signature, string $paymentStatus, string $orderStatus): bool {
        $stmt = $this->db->prepare("UPDATE orders SET razorpay_payment_id = ?, razorpay_signature = ?, payment_method = 'razorpay', payment_status = ?, order_status = ? WHERE id = ?");
        return $stmt->execute([$paymentId, $signature, $paymentStatus, $orderStatus, $orderId]);
    }

    /**
     * Mark order payment as failed
     */
    public function markPaymentFailed(int $orderId): bool {
        $stmt = $this->db->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = ?");
        return $stmt->execute([$orderId]);
    }

    /**
     * Cancel unpaid order and restock inventory
     */
    public function cancelUnpaidOrder(int $orderId, string $reason = 'Payment failed'): void {
        try {
            if (!$this->db->inTransaction()) {
                $this->db->beginTransaction();
            }

            $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ? FOR UPDATE");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();

            if (!$order || $order['payment_status'] === 'paid' || $order['order_status'] === 'cancelled') {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                return;
            }

            // Update order status to cancelled and payment status to failed
            $stmtUpdate = $this->db->prepare("UPDATE orders SET order_status = 'cancelled', payment_status = 'failed' WHERE id = ?");
            $stmtUpdate->execute([$orderId]);

            // Restock items
            $itemModel = new OrderItem();
            $items = $itemModel->getByOrder($orderId);
            
            $stmtRestock = $this->db->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?");
            $stmtInvLog = $this->db->prepare("
                INSERT INTO inventory_log (product_id, user_id, change_type, quantity_change, reason)
                VALUES (?, ?, ?, ?, ?)
            ");

            foreach ($items as $item) {
                if (!empty($item['product_id'])) {
                    $stmtRestock->execute([(int)$item['quantity'], (int)$item['product_id']]);
                    $stmtInvLog->execute([
                        (int)$item['product_id'],
                        (int)$order['user_id'],
                        'added',
                        (int)$item['quantity'],
                        "Restocked from failed payment: " . $order['order_number']
                    ]);
                }
            }

            // Log status history
            $historyModel = new OrderStatusHistory();
            $historyModel->log($orderId, 'cancelled', $reason, (int)$order['user_id']);

            if ($this->db->inTransaction()) {
                $this->db->commit();
            }
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
        }
    }

    /**
     * Cancel an order: RESTOCK products and LOG inventory, create refund request if paid
     *
     * @param int $orderId
     * @param int $userId Person who cancelled
     * @param string $comment Reason for cancellation
     * @param bool $isAdmin Admin status override
     * @return bool
     * @throws Exception
     */
    public function cancel(int $orderId, int $userId, string $comment, bool $isAdmin = false): bool {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ? FOR UPDATE");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();

            if (!$order) {
                throw new Exception("Order not found.");
            }

            if (!$isAdmin && (int)$order['user_id'] !== $userId) {
                throw new Exception("Unauthorized. You cannot cancel this order.");
            }

            // Can only cancel pending or confirmed status orders
            if (!in_array($order['order_status'], ['pending', 'confirmed'])) {
                throw new Exception("Order cannot be cancelled in its current state: " . $order['order_status']);
            }

            // Update status
            $stmtUpdate = $this->db->prepare("UPDATE orders SET order_status = 'cancelled' WHERE id = ?");
            $stmtUpdate->execute([$orderId]);

            // Add history entry
            $historyModel = new OrderStatusHistory();
            $historyModel->log($orderId, 'cancelled', $comment, $userId);

            // Restock products and log in inventory_log
            $itemModel = new OrderItem();
            $items = $itemModel->getByOrder($orderId);
            
            $stmtRestock = $this->db->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?");
            $stmtInvLog = $this->db->prepare("
                INSERT INTO inventory_log (product_id, user_id, change_type, quantity_change, reason)
                VALUES (?, ?, ?, ?, ?)
            ");

            foreach ($items as $item) {
                if (!empty($item['product_id'])) {
                    $stmtRestock->execute([$item['quantity'], $item['product_id']]);
                    $stmtInvLog->execute([
                        $item['product_id'],
                        $userId,
                        'added',
                        $item['quantity'],
                        "Restocked from cancelled order: " . $order['order_number']
                    ]);
                }
            }

            // Release coupon usage if coupon applied
            if (!empty($order['coupon_code'])) {
                $couponModel = new Coupon();
                $couponModel->decrementUsage($order['coupon_code']);
            }

            // Refund request if payment was completed
            if ($order['payment_status'] === 'paid') {
                $stmtPay = $this->db->prepare("SELECT id FROM payments WHERE order_id = ? AND status = 'success' ORDER BY id DESC LIMIT 1");
                $stmtPay->execute([$orderId]);
                $paymentId = $stmtPay->fetchColumn();

                if ($paymentId) {
                    $stmtRef = $this->db->prepare("
                        INSERT INTO refunds (payment_id, order_id, amount, reason, status)
                        VALUES (?, ?, ?, ?, 'pending')
                    ");
                    $stmtRef->execute([
                        $paymentId,
                        $orderId,
                        $order['total'],
                        "Cancellation Auto-Refund: " . $comment
                    ]);
                }
                
                // Set payment status to refunded
                $this->db->prepare("UPDATE orders SET payment_status = 'refunded' WHERE id = ?")->execute([$orderId]);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Initiate return process for delivered orders
     *
     * @param int $orderId
     * @param int $userId Customer ID
     * @param string $comment Return reason
     * @return bool
     * @throws Exception
     */
    public function requestReturn(int $orderId, int $userId, string $comment): bool {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ? FOR UPDATE");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();

            if (!$order) {
                throw new Exception("Order not found.");
            }

            if ((int)$order['user_id'] !== $userId) {
                throw new Exception("Unauthorized. You cannot return this order.");
            }

            if ($order['order_status'] !== 'delivered') {
                throw new Exception("Only delivered orders can be returned.");
            }

            $stmtUpdate = $this->db->prepare("UPDATE orders SET order_status = 'returned' WHERE id = ?");
            $stmtUpdate->execute([$orderId]);

            $historyModel = new OrderStatusHistory();
            $historyModel->log($orderId, 'returned', $comment, $userId);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Fetch orders list for a customer (paginated)
     *
     * @param int $userId
     * @param array $filters Query parameters
     * @return array
     */
    public function findByUser(int $userId, array $filters): array {
        $query = "FROM orders WHERE user_id = ?";
        $params = [$userId];

        if (!empty($filters['status'])) {
            $query .= " AND order_status = ?";
            $params[] = $filters['status'];
        }

        // Calculate count
        $stmtCount = $this->db->prepare("SELECT COUNT(*) " . $query);
        $stmtCount->execute($params);
        $totalItems = (int)$stmtCount->fetchColumn();

        // Pagination
        $page = isset($filters['page']) ? (int)$filters['page'] : 1;
        $perPage = isset($filters['per_page']) ? (int)$filters['per_page'] : 10;
        $offset = ($page - 1) * $perPage;

        $selectQuery = "SELECT * " . $query . " ORDER BY id DESC LIMIT $perPage OFFSET $offset";
        $stmtSelect = $this->db->prepare($selectQuery);
        $stmtSelect->execute($params);
        $orders = $stmtSelect->fetchAll();

        // Populate items
        $itemModel = new OrderItem();
        foreach ($orders as &$order) {
            $order['items'] = $itemModel->getByOrder((int)$order['id']);
        }

        return [
            'data' => $orders,
            'pagination' => [
                'total_items'  => $totalItems,
                'total_pages'  => ceil($totalItems / $perPage),
                'current_page' => $page,
                'per_page'     => $perPage
            ]
        ];
    }

    /**
     * Fetch all orders list for administrators (paginated, with advanced filters)
     *
     * @param array $filters Query parameters
     * @return array
     */
    public function allWithFilters(array $filters): array {
        $query = "
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        // Filter status
        if (!empty($filters['status'])) {
            $query .= " AND o.order_status = ?";
            $params[] = $filters['status'];
        }

        // Filter date ranges
        if (!empty($filters['start_date'])) {
            $query .= " AND o.created_at >= ?";
            $params[] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $query .= " AND o.created_at <= ?";
            $params[] = $filters['end_date'] . ' 23:59:59';
        }

        // Filter search term
        if (!empty($filters['search'])) {
            $query .= " AND (o.order_number LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR o.guest_email LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        // Calculate count
        $stmtCount = $this->db->prepare("SELECT COUNT(DISTINCT o.id) " . $query);
        $stmtCount->execute($params);
        $totalItems = (int)$stmtCount->fetchColumn();

        // Pagination
        $page = isset($filters['page']) ? (int)$filters['page'] : 1;
        $perPage = isset($filters['per_page']) ? (int)$filters['per_page'] : 10;
        $offset = ($page - 1) * $perPage;

        $selectQuery = "
            SELECT o.*, u.first_name, u.last_name, u.email 
            " . $query . " 
            ORDER BY o.id DESC 
            LIMIT $perPage OFFSET $offset
        ";
        $stmtSelect = $this->db->prepare($selectQuery);
        $stmtSelect->execute($params);
        $orders = $stmtSelect->fetchAll();

        // Populate items
        $itemModel = new OrderItem();
        foreach ($orders as &$order) {
            $order['items'] = $itemModel->getByOrder((int)$order['id']);
        }

        return [
            'data' => $orders,
            'pagination' => [
                'total_items'  => $totalItems,
                'total_pages'  => ceil($totalItems / $perPage),
                'current_page' => $page,
                'per_page'     => $perPage
            ]
        ];
    }
}
