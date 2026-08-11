<?php
/**
 * Payment Controller (Refactored for Razorpay Live)
 */

class PaymentController {
    private Payment $paymentModel;
    private Order $orderModel;
    private Cart $cartModel;
    private Coupon $couponModel;
    private PDO $db;

    public function __construct() {
        $this->paymentModel = new Payment();
        $this->orderModel = new Order();
        $this->cartModel = new Cart();
        $this->couponModel = new Coupon();
        $this->db = Database::getInstance();
    }

    private function logPaymentEvent(?int $orderId, string $event, array $data): void {
        try {
            $stmt = $this->db->prepare("INSERT INTO payment_logs (order_id, event, gateway_response) VALUES (?, ?, ?)");
            $stmt->execute([$orderId, $event, json_encode($data)]);
        } catch (Exception $e) {
            // Silently ignore log failures to not break the flow
        }
    }

    /**
     * POST /api/payments/initiate
     */
    public function initiate(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $data = Helper::getRequestBody();
        $orderId = (int)($data['order_id'] ?? 0);

        try {
            $order = $this->orderModel->find($orderId);
            if (!$order || (int)$order['user_id'] !== $userId) {
                Helper::jsonResponse(['success' => false, 'message' => 'Order not found.'], 404);
            }

            if ($order['payment_status'] === 'paid' || $order['payment_status'] === 'captured') {
                Helper::jsonResponse(['success' => true, 'message' => 'Order is already paid.'], 200);
            }

            if ($order['order_status'] === 'expired' || $order['order_status'] === 'cancelled') {
                Helper::jsonResponse(['success' => false, 'message' => 'Order is no longer valid for payment.'], 400);
            }

            // If order has expires_at and it has passed, mark expired
            if (!empty($order['expires_at']) && strtotime($order['expires_at']) < time()) {
                Helper::jsonResponse(['success' => false, 'message' => 'Payment window has expired.'], 400);
            }

            $razorpayKeyId = 'rzp_live_TMO8owzBVuPjvl';
            $razorpayKeySecret = '8pwyR5W7X3IKkp7IYGEBO6pd';
            $amountInPaise = (int)round((float)$order['total'] * 100);

            $ch = curl_init('https://api.razorpay.com/v1/orders');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $razorpayKeyId . ':' . $razorpayKeySecret);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'amount'   => $amountInPaise,
                'currency' => 'INR',
                'receipt'  => $order['order_number']
            ]));
            
            $response = curl_exec($ch);
            curl_close($ch);

            $rzpData = json_decode($response, true);
            $razorpayOrderId = $rzpData['id'] ?? null;

            if (!$razorpayOrderId) {
                throw new Exception("Failed to generate Razorpay order.");
            }

            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            $attempts = (int)($order['attempts'] ?? 0) + 1;
            
            $this->orderModel->updateRazorpayDetails($orderId, $razorpayOrderId, $expiresAt, $attempts);
            $this->logPaymentEvent($orderId, 'initiate', ['razorpay_order_id' => $razorpayOrderId, 'amount' => $amountInPaise, 'attempts' => $attempts]);

            Helper::jsonResponse([
                'success'           => true,
                'order_id'          => $orderId,
                'order_number'      => $order['order_number'],
                'gateway_order_id'  => $razorpayOrderId,
                'key_id'            => $razorpayKeyId,
                'amount'            => (float)$order['total'],
                'currency'          => 'INR'
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse(['success' => false, 'message' => 'Payment initiation failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/payments/verify
     */
    public function verify(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $data = Helper::getRequestBody();
        $orderId = (int)($data['order_id'] ?? 0);
        $paymentId = $data['payment_id'] ?? '';
        $gatewayData = $data['gateway_data'] ?? [];
        
        $rzpOrderId = $gatewayData['razorpay_order_id'] ?? '';
        $rzpSignature = $gatewayData['razorpay_signature'] ?? '';

        try {
            $order = $this->orderModel->find($orderId);
            if (!$order || (int)$order['user_id'] !== $userId) {
                Helper::jsonResponse(['success' => false, 'message' => 'Order not found.'], 404);
            }

            if ($order['payment_status'] === 'paid' || $order['payment_status'] === 'captured') {
                Helper::jsonResponse(['success' => true, 'message' => 'Order already paid.'], 200);
            }

            $razorpayKeySecret = '8pwyR5W7X3IKkp7IYGEBO6pd';
            $expectedSignature = hash_hmac('sha256', $rzpOrderId . '|' . $paymentId, $razorpayKeySecret);
            
            if (!hash_equals($expectedSignature, $rzpSignature)) {
                $this->logPaymentEvent($orderId, 'verify_failed', ['reason' => 'Invalid signature', 'provided' => $rzpSignature, 'expected' => $expectedSignature]);
                $this->orderModel->cancelUnpaidOrder($orderId, 'Payment signature verification failed.');
                Helper::jsonResponse(['success' => false, 'message' => 'Payment signature verification failed.'], 400);
            }

            // Success
            $this->orderModel->finalizeRazorpayPayment($orderId, $paymentId, $rzpSignature, 'paid', 'confirmed');
            
            // Insert into legacy payments table to keep standard flow unbroken
            $this->paymentModel->verifyPayment($orderId, $paymentId, 'razorpay', (float)$order['total'], 'success', $gatewayData);
            
            $this->logPaymentEvent($orderId, 'verify_success', ['payment_id' => $paymentId]);
            
            // Log history
            $historyModel = new OrderStatusHistory();
            $historyModel->log($orderId, 'confirmed', 'Payment verified successfully via Razorpay.', $userId);

            // Increment coupon usage limit count if applied
            if (!empty($order['coupon_code'])) {
                $this->couponModel->incrementUsage($order['coupon_code']);
            }

            // Clear the shopping cart for this user
            try {
                $stmtCart = $this->db->prepare("SELECT id FROM carts WHERE user_id = ?");
                $stmtCart->execute([$userId]);
                $cartId = $stmtCart->fetchColumn();
                if ($cartId) {
                    $this->cartModel->clear((int)$cartId);
                    $this->cartModel->applyCouponCode((int)$cartId, null);
                }
            } catch (Exception $ce) {
                // Silently ignore cart clear errors
            }

            Helper::jsonResponse(['success' => true, 'message' => 'Payment verified. Order confirmed.'], 200);

        } catch (Exception $e) {
            $this->logPaymentEvent($orderId, 'verify_error', ['error' => $e->getMessage()]);
            Helper::jsonResponse(['success' => false, 'message' => 'Verification error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/payments/failed
     */
    public function paymentFailed(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $data = Helper::getRequestBody();
        $orderId = (int)($data['order_id'] ?? 0);

        try {
            $order = $this->orderModel->find($orderId);
            if ($order && (int)$order['user_id'] === $userId && $order['payment_status'] === 'pending_payment') {
                $this->orderModel->cancelUnpaidOrder($orderId, 'Payment cancelled or modal closed by user.');
                $this->logPaymentEvent($orderId, 'modal_dismissed', $data);
            }
            Helper::jsonResponse(['success' => true, 'message' => 'Payment marked as failed. Order cancelled.'], 200);
        } catch (Exception $e) {
            Helper::jsonResponse(['success' => false, 'message' => 'Failed to process.'], 500);
        }
    }

    /**
     * POST /api/webhooks/razorpay
     */
    public function webhook(): void {
        $webhookSecret = 'TU_WH_SECRET_2026';
        $payload = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

        if (!$signature) {
            http_response_code(400);
            die("Missing signature");
        }

        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
        if (!hash_equals($expectedSignature, $signature)) {
            $this->logPaymentEvent(null, 'webhook_invalid_sig', ['ip' => $_SERVER['REMOTE_ADDR']]);
            http_response_code(400);
            die("Invalid signature");
        }

        $event = json_decode($payload, true);
        if (!$event) {
            http_response_code(400);
            die("Invalid payload");
        }

        $eventType = $event['event'] ?? '';
        $paymentEntity = $event['payload']['payment']['entity'] ?? [];
        $rzpOrderId = $paymentEntity['order_id'] ?? null;
        $paymentId = $paymentEntity['id'] ?? null;

        if ($rzpOrderId) {
            $stmt = $this->db->prepare("SELECT id, payment_status, total FROM orders WHERE razorpay_order_id = ? LIMIT 1");
            $stmt->execute([$rzpOrderId]);
            $order = $stmt->fetch();

            if ($order) {
                $orderId = (int)$order['id'];
                $this->logPaymentEvent($orderId, "webhook_$eventType", $paymentEntity);

                if ($eventType === 'payment.captured') {
                    if ($order['payment_status'] !== 'paid' && $order['payment_status'] !== 'captured') {
                        $this->orderModel->finalizeRazorpayPayment($orderId, $paymentId, 'WEBHOOK_OVERRIDE', 'paid', 'confirmed');
                        $this->paymentModel->verifyPayment($orderId, $paymentId, 'razorpay', (float)$order['total'], 'success', $paymentEntity);
                        
                        $historyModel = new OrderStatusHistory();
                        $historyModel->log($orderId, 'confirmed', 'Payment automatically captured via Webhook.', 0);
                    }
                } elseif ($eventType === 'payment.failed') {
                    if ($order['payment_status'] === 'pending_payment') {
                        $this->orderModel->markPaymentFailed($orderId);
                    }
                }
            }
        }

        http_response_code(200);
        echo json_encode(['status' => 'ok']);
    }

    /**
     * GET /api/admin/payments
     */
    public function adminIndex(): void {
        AuthMiddleware::handle(true);
        try {
            $payments = $this->paymentModel->all();
            Helper::jsonResponse(['success' => true, 'data' => $payments], 200);
        } catch (Exception $e) {
            Helper::jsonResponse(['success' => false, 'message' => 'Failed to retrieve payments.'], 500);
        }
    }
}
