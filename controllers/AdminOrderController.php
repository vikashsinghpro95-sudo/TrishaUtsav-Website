<?php
/**
 * Admin Order Management Controller (Admin only)
 */

class AdminOrderController {
    private Order $orderModel;
    private OrderStatusHistory $historyModel;
    private Shipment $shipmentModel;
    private Refund $refundModel;
    private PDO $db;

    public function __construct() {
        $this->orderModel = new Order();
        $this->historyModel = new OrderStatusHistory();
        $this->shipmentModel = new Shipment();
        $this->refundModel = new Refund();
        $this->db = Database::getInstance();
    }

    /**
     * GET /api/admin/orders
     * List all orders with advanced filtering (status, date range, search)
     */
    public function index(): void {
        AuthMiddleware::handle(true); // Admin auth

        $filters = [
            'status'     => $_GET['status'] ?? null,
            'start_date' => $_GET['start_date'] ?? null,
            'end_date'   => $_GET['end_date'] ?? null,
            'search'     => $_GET['search'] ?? null,
            'page'       => $_GET['page'] ?? 1,
            'per_page'   => $_GET['per_page'] ?? 10
        ];

        try {
            $orders = $this->orderModel->allWithFilters($filters);
            Helper::jsonResponse([
                'success' => true,
                'data' => $orders['data'],
                'pagination' => $orders['pagination']
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/admin/orders/{id}
     * Retrieve full order profile with history, shipment, and payments
     *
     * @param string $id Order ID
     */
    public function show(string $id): void {
        AuthMiddleware::handle(true); // Admin auth
        $orderId = (int)$id;

        try {
            $order = $this->orderModel->findWithDetails($orderId);

            if (!$order) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Order not found.'
                ], 404);
            }

            Helper::jsonResponse([
                'success' => true,
                'data' => $order
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PATCH /api/admin/orders/{id}/status
     * Transition order status and record in history and audits
     *
     * @param string $id Order ID
     */
    public function updateStatus(string $id): void {
        $user = AuthMiddleware::handle(true); // Admin auth
        $adminId = (int)$user['id'];
        $orderId = (int)$id;

        $data = Helper::getRequestBody();
        if (empty($data['status'])) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Target status field is required.'
            ], 422);
        }

        $status = trim($data['status']);
        $comment = $data['comment'] ?? 'Status updated by administrator';

        // Check if status is valid enum member
        $allowedStatuses = ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'out_for_delivery', 'delivered', 'cancelled', 'returned'];
        if (!in_array($status, $allowedStatuses)) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Invalid order status value.'
            ], 422);
        }

        try {
            $order = $this->orderModel->find($orderId);
            if (!$order) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Order not found.'
                ], 404);
            }

            // BUG-004 Fix: Strict state machine transition validation
            $statusRanks = [
                'pending' => 1,
                'confirmed' => 2,
                'processing' => 3,
                'packed' => 4,
                'shipped' => 5,
                'out_for_delivery' => 6,
                'delivered' => 7,
                'cancelled' => 99,
                'returned' => 99
            ];

            $currentRank = $statusRanks[$order['order_status']] ?? 0;
            $newRank = $statusRanks[$status] ?? 0;

            if ($newRank < $currentRank && $status !== 'cancelled' && $status !== 'returned') {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => "Invalid state transition. Cannot move order backward from {$order['order_status']} to $status."
                ], 422);
            }

            // Update order status
            $stmt = $this->db->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
            $stmt->execute([$status, $orderId]);

            // Add history entry
            $this->historyModel->log($orderId, $status, $comment, $adminId);

            // Add audit log entry
            Helper::logAction(
                $adminId,
                'update_order_status',
                'orders',
                $orderId,
                ['order_status' => $order['order_status']],
                ['order_status' => $status]
            );

            Helper::jsonResponse([
                'success' => true,
                'message' => "Order status updated to '$status'."
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/admin/orders/{id}/cancel
     * Force cancel an order (Admin override)
     *
     * @param string $id Order ID
     */
    public function cancel(string $id): void {
        $user = AuthMiddleware::handle(true); // Admin auth
        $adminId = (int)$user['id'];
        $orderId = (int)$id;

        $data = Helper::getRequestBody();
        $reason = $data['reason'] ?? 'Cancelled by administrator';

        try {
            $this->orderModel->cancel($orderId, $adminId, $reason, true);

            // Log admin audit action
            Helper::logAction($adminId, 'cancel_order', 'orders', $orderId, null, ['reason' => $reason]);

            Helper::jsonResponse([
                'success' => true,
                'message' => 'Order force cancelled by admin successfully.'
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to cancel order: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * POST /api/admin/orders/{id}/refund
     * Record a manual refund entry for paid orders
     *
     * @param string $id Order ID
     */
    public function refund(string $id): void {
        $user = AuthMiddleware::handle(true); // Admin auth
        $adminId = (int)$user['id'];
        $orderId = (int)$id;

        $data = Helper::getRequestBody();

        $validator = new Validator($data);
        $errors = $validator->validate([
            'amount' => ['required', 'numeric'],
            'reason' => ['required']
        ]);

        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        $amount = (float)$data['amount'];
        $reason = trim($data['reason']);

        try {
            $order = $this->orderModel->find($orderId);
            if (!$order) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Order not found.'
                ], 404);
            }

            // Find successful payment for the order
            $stmtPay = $this->db->prepare("SELECT * FROM payments WHERE order_id = ? AND status = 'success' ORDER BY id DESC LIMIT 1");
            $stmtPay->execute([$orderId]);
            $payment = $stmtPay->fetch();

            if (!$payment) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Cannot refund. No successful payment record exists for this order.'
                ], 400);
            }

            $this->db->beginTransaction();

            // Create refund entry (status processed directly since it's manual admin logging)
            $refundId = $this->refundModel->create([
                'payment_id' => $payment['id'],
                'order_id'   => $orderId,
                'amount'     => $amount,
                'reason'     => $reason,
                'status'     => 'processed'
            ]);

            // Update order payment status
            $newPaymentStatus = ($amount >= (float)$order['total']) ? 'refunded' : 'partially_refunded';
            $stmtUpdateOrder = $this->db->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
            $stmtUpdateOrder->execute([$newPaymentStatus, $orderId]);

            // Log status history transition comment
            $this->historyModel->log($orderId, $order['order_status'], "Refund processed: ₹" . number_format($amount, 2) . ". Reason: $reason", $adminId);

            // Log admin audit action
            Helper::logAction(
                $adminId,
                'manual_refund',
                'refunds',
                $refundId,
                null,
                ['order_id' => $orderId, 'amount' => $amount, 'reason' => $reason, 'payment_status' => $newPaymentStatus]
            );

            $this->db->commit();

            Helper::jsonResponse([
                'success' => true,
                'message' => 'Manual refund record created successfully.'
            ], 200);

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to process refund: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/admin/orders/{id}/shipment
     * Add shipment details and dispatch order
     *
     * @param string $id Order ID
     */
    public function addShipment(string $id): void {
        $user = AuthMiddleware::handle(true); // Admin auth
        $adminId = (int)$user['id'];
        $orderId = (int)$id;

        $data = Helper::getRequestBody();

        $validator = new Validator($data);
        $errors = $validator->validate([
            'courier_name'     => ['required'],
            'tracking_number'  => ['required']
        ]);

        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        $courierName = trim($data['courier_name']);
        $trackingNumber = trim($data['tracking_number']);

        try {
            $shipmentId = $this->shipmentModel->createShipment($orderId, $courierName, $trackingNumber, $adminId);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Shipment tracking information added and order status set to shipped.',
                'shipment_id' => $shipmentId
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to add shipment: ' . $e->getMessage()
            ], 400);
        }
    }
}
