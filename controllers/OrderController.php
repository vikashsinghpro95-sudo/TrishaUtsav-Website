<?php
/**
 * Order Controller (Customer specific)
 */

class OrderController {
    private Order $orderModel;

    public function __construct() {
        $this->orderModel = new Order();
    }

    /**
     * GET /api/orders
     * List paginated order history for customer
     */
    public function index(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $filters = [
            'status'   => $_GET['status'] ?? null,
            'page'     => $_GET['page'] ?? 1,
            'per_page' => $_GET['per_page'] ?? 10
        ];

        try {
            $orders = $this->orderModel->findByUser($userId, $filters);
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
     * GET /api/orders/{id}
     * Retrieve complete order details
     *
     * @param string $id Order ID
     */
    public function show(string $id): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];
        $orderId = (int)$id;

        try {
            $order = $this->orderModel->findWithDetails($orderId);

            if (!$order || (int)$order['user_id'] !== $userId) {
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
     * POST /api/orders/{id}/cancel
     * Cancel an order (Allowed only if status is pending/confirmed)
     *
     * @param string $id Order ID
     */
    public function cancel(string $id): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];
        $orderId = (int)$id;

        $data = Helper::getRequestBody();
        if (empty($data['comment'])) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Reason for cancellation (comment) is required.'
            ], 422);
        }

        $comment = trim($data['comment']);

        try {
            $this->orderModel->cancel($orderId, $userId, $comment, false);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Order cancelled successfully. Restocked inventory.'
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Order cancellation failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * POST /api/orders/{id}/return
     * Initiate return process for delivered orders
     *
     * @param string $id Order ID
     */
    public function requestReturn(string $id): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];
        $orderId = (int)$id;

        $data = Helper::getRequestBody();
        if (empty($data['comment'])) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Reason for return (comment) is required.'
            ], 422);
        }

        $comment = trim($data['comment']);

        try {
            $this->orderModel->requestReturn($orderId, $userId, $comment);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Return request submitted successfully.'
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Return request failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * GET /api/orders/{id}/tracking
     * Retrieve live Delhivery tracking status for an order
     *
     * @param string $id Order ID
     */
    public function getTracking(string $id): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];
        $orderId = (int)$id;

        try {
            $order = $this->orderModel->find($orderId);

            if (!$order || ((int)$order['user_id'] !== $userId && ($user['role_name'] ?? '') !== 'admin')) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Order not found or unauthorized.'
                ], 404);
            }

            $trackingId = $order['tracking_id'] ?? null;

            // Check shipments table fallback
            if (empty($trackingId)) {
                $db = Database::getInstance();
                $stmt = $db->prepare("SELECT tracking_number FROM shipments WHERE order_id = ? AND tracking_number IS NOT NULL AND tracking_number != '' ORDER BY id DESC LIMIT 1");
                $stmt->execute([$orderId]);
                $shipment = $stmt->fetch();
                if ($shipment) {
                    $trackingId = $shipment['tracking_number'];
                }
            }

            if (empty($trackingId)) {
                Helper::jsonResponse([
                    'success' => true,
                    'tracked' => false,
                    'message' => 'Shipment tracking ID has not been assigned yet.'
                ], 200);
                return;
            }

            $delhivery = new DelhiveryService();
            $trackingData = $delhivery->trackShipment($trackingId);

            Helper::jsonResponse([
                'success' => true,
                'tracked' => true,
                'data' => $trackingData
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch tracking details: ' . $e->getMessage()
            ], 500);
        }
    }
}
