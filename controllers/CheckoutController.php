<?php
/**
 * Checkout Controller
 */

class CheckoutController {
    private Cart $cartModel;
    private Address $addressModel;
    private Order $orderModel;
    private OrderItem $orderItemModel;
    private OrderStatusHistory $historyModel;
    private Product $productModel;
    private Coupon $couponModel;
    private PDO $db;

    public function __construct() {
        $this->cartModel = new Cart();
        $this->addressModel = new Address();
        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
        $this->historyModel = new OrderStatusHistory();
        $this->productModel = new Product();
        $this->couponModel = new Coupon();
        $this->db = Database::getInstance();
    }

    /**
     * POST /api/checkout
     * Process checkout and place order
     */
    public function process(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $data = Helper::getRequestBody();

        $validator = new Validator($data);
        $errors = $validator->validate([
            'shipping_address_id' => ['required', 'numeric'],
            'payment_method'      => ['required']
        ]);

        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        $shippingAddressId = (int)$data['shipping_address_id'];
        $billingAddressId = isset($data['billing_address_id']) ? (int)$data['billing_address_id'] : $shippingAddressId;
        $paymentMethod = trim($data['payment_method']);
        $notes = $data['notes'] ?? null;
        $couponCode = isset($data['coupon_code']) ? trim($data['coupon_code']) : null;

        // Ekart live shipping charge provided by frontend after real-time API estimation
        $ekartShippingCharge = isset($data['ekart_shipping_charge']) ? max(0.0, (float)$data['ekart_shipping_charge']) : null;

        // Check if this is a direct "Buy Now" checkout
        $directToken = $data['direct_order'] ?? $_GET['direct_order'] ?? null;
        if (!empty($directToken)) {
            $this->processDirectCheckout($userId, $data, $directToken);
            return;
        }

        // ----------------------------------------------------
        // Step 1: Validate Addresses belong to Customer
        // ----------------------------------------------------
        $shippingAddress = $this->addressModel->find($shippingAddressId);
        if (!$shippingAddress || (int)$shippingAddress['user_id'] !== $userId) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Invalid shipping address selected.'
            ], 422);
        }

        $billingAddress = $this->addressModel->find($billingAddressId);
        if (!$billingAddress || (int)$billingAddress['user_id'] !== $userId) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Invalid billing address selected.'
            ], 422);
        }

        try {
            // ----------------------------------------------------
            // Step 2: Fetch Cart Details & Verify Empty Check
            // ----------------------------------------------------
            $cartDetails = $this->cartModel->getCartDetails($userId, $couponCode);
            $cartId = (int)$cartDetails['cart_id'];
            $items = $cartDetails['items'];

            if (empty($items)) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Your cart is empty. Cannot checkout.'
                ], 400);
            }

            // If a coupon code was passed but marked invalid, abort checkout
            $appliedCoupon = $cartDetails['summary']['applied_coupon'];
            if ($appliedCoupon && isset($appliedCoupon['invalid']) && $appliedCoupon['invalid'] === true) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Checkout rejected: ' . $appliedCoupon['error']
                ], 422);
            }

            // Start Transaction to avoid race condition on stock and order entries
            $this->db->beginTransaction();

            // ----------------------------------------------------
            // Step 3: Double-check Stock Levels with Row Lock
            // ----------------------------------------------------
            $stmtLock = $this->db->prepare("SELECT stock_quantity, status FROM products WHERE id = ? FOR UPDATE");
            
            foreach ($items as $item) {
                $stmtLock->execute([$item['product_id']]);
                $prod = $stmtLock->fetch();

                if (!$prod || $prod['status'] !== 'published') {
                    throw new Exception("Product '{$item['product_name']}' is no longer available.");
                }

                if ((int)$item['quantity'] > (int)$prod['stock_quantity']) {
                    throw new Exception("Insufficient stock for product '{$item['product_name']}'. Only {$prod['stock_quantity']} units available.");
                }
            }

            // ----------------------------------------------------
            // Step 4: Generate Unique Order Number
            // ----------------------------------------------------
            $orderNumber = '';
            $orderExists = true;
            $stmtCheckORD = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE order_number = ?");
            
            while ($orderExists) {
                $rand = strtoupper(bin2hex(random_bytes(2))); // 4 chars
                $orderNumber = 'ORD-' . date('Ymd') . '-' . $rand;
                
                $stmtCheckORD->execute([$orderNumber]);
                if ((int)$stmtCheckORD->fetchColumn() === 0) {
                    $orderExists = false;
                }
            }

            // ----------------------------------------------------
            // Step 5: Save Order Record
            // ----------------------------------------------------
            $summary = $cartDetails['summary'];

            // Override shipping with live Ekart estimate if provided by frontend
            // Free shipping still applies for orders >= ₹499 (takes priority)
            if ($ekartShippingCharge !== null && $summary['subtotal'] < 499.0) {
                $summary['shipping'] = $ekartShippingCharge;
                $summary['total'] = round(
                    $summary['subtotal'] - $summary['discount'] + $summary['tax'] + $ekartShippingCharge,
                    2
                );
                if ($summary['total'] < 0) {
                    $summary['total'] = 0.0;
                }
            }

            $isOnlinePayment = in_array($paymentMethod, ['razorpay', 'upi']);

            $orderId = $this->orderModel->create([
                'order_number'        => $orderNumber,
                'user_id'             => $userId,
                'shipping_address_id' => $shippingAddressId,
                'billing_address_id'  => $billingAddressId,
                'subtotal'            => $summary['subtotal'],
                'tax_amount'          => $summary['tax'],
                'shipping_charge'     => $summary['shipping'],
                'discount'            => $summary['discount'],
                'coupon_code'         => $appliedCoupon ? $appliedCoupon['code'] : null,
                'total'               => $summary['total'],
                'payment_method'      => $paymentMethod,
                'payment_status'      => ($paymentMethod === 'cod') ? 'pending' : 'pending_payment',
                'order_status'        => 'pending',
                'notes'               => $notes,
                'expires_at'          => $isOnlinePayment ? date('Y-m-d H:i:s', strtotime('+30 minutes')) : null
            ]);

            // ----------------------------------------------------
            // Step 6: Save Order Items, Restock Check, & Logs
            // ----------------------------------------------------
            $stmtUpdateStock = $this->db->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
            $stmtInvLog = $this->db->prepare("
                INSERT INTO inventory_log (product_id, user_id, change_type, quantity_change, reason)
                VALUES (?, ?, ?, ?, ?)
            ");

            foreach ($items as $item) {
                // Insert order item snapshot
                $this->orderItemModel->create($orderId, [
                    'product_id'   => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'sku'          => $item['sku'],
                    'price'        => $item['price'],
                    'quantity'     => $item['quantity'],
                    'attributes'   => $item['attributes']
                ]);

                // Reduce inventory stock
                $stmtUpdateStock->execute([$item['quantity'], $item['product_id']]);

                // Record inventory movement log safely
                try {
                    $stmtInvLog->execute([
                        $item['product_id'],
                        $userId,
                        'removed',
                        -((int)$item['quantity']),
                        "Order Placed: $orderNumber"
                    ]);
                } catch (Throwable $eInv) {
                    error_log("Inventory log skipped: " . $eInv->getMessage());
                }
            }

            // Record initial order status history safely
            try {
                $this->historyModel->log($orderId, 'pending', 'Order placed successfully via checkout.', $userId);
            } catch (Throwable $eHist) {
                error_log("Order history log skipped: " . $eHist->getMessage());
            }

            if (!$isOnlinePayment) {
                // Increment coupon usage limit count if applied
                if ($appliedCoupon) {
                    $this->couponModel->incrementUsage($appliedCoupon['code']);
                }

                // Clear the shopping cart
                $this->cartModel->clear($cartId);

                // Remove persisted coupon code in cart table
                $this->cartModel->applyCouponCode($cartId, null);
            }

            $this->db->commit();

            Helper::jsonResponse([
                'success'      => true,
                'message'      => 'Order placed successfully.',
                'order_id'     => $orderId,
                'order_number' => $orderNumber,
                'total'        => $summary['total'],
                'payment_method' => $paymentMethod
            ], 201);

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Checkout failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/buy-now
     * Create direct-checkout token for a single product skipping normal cart
     */
    public function buyNow(): void {
        $data = Helper::getRequestBody();

        $productId = isset($data['product_id']) ? (int)$data['product_id'] : 0;
        $quantity = isset($data['quantity']) ? max(1, (int)$data['quantity']) : 1;
        $variantId = $data['variant_id'] ?? null;
        $attributes = $data['attributes'] ?? null;

        if ($productId <= 0) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Invalid product selected.'
            ], 422);
        }

        $product = $this->productModel->find($productId);
        if (!$product || $product['status'] !== 'published') {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Product is no longer available.'
            ], 404);
        }

        if ((int)$product['stock_quantity'] < $quantity || (int)$product['stock_quantity'] <= 0) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Product is currently out of stock.'
            ], 422);
        }

        // Calculate item price including attributes if present
        $unitPrice = (float)$product['price'];
        if (!empty($attributes) && is_array($attributes) && !empty($product['attributes'])) {
            foreach ($attributes as $attrName => $attrVal) {
                foreach ($product['attributes'] as $pAttr) {
                    if ($pAttr['attribute_name'] === $attrName && $pAttr['attribute_value'] === $attrVal) {
                        $unitPrice += (float)($pAttr['extra_price'] ?? 0);
                    }
                }
            }
        }

        // Generate a 32-character cryptographically secure token
        $token = bin2hex(random_bytes(16));

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['direct_orders'])) {
            $_SESSION['direct_orders'] = [];
        }

        $_SESSION['direct_orders'][$token] = [
            'token'        => $token,
            'product_id'   => $productId,
            'quantity'     => $quantity,
            'variant_id'   => $variantId,
            'attributes'   => $attributes,
            'price'        => $unitPrice,
            'created_at'   => time(),
            'expires_at'   => time() + 1800 // 30 minutes expiry
        ];

        $baseUrl = defined('BASE_URL') ? BASE_URL : '/';

        Helper::jsonResponse([
            'success'      => true,
            'token'        => $token,
            'redirect_url' => $baseUrl . 'checkout.php?direct_order=' . $token
        ], 200);
    }

    /**
     * GET /api/checkout/direct-summary
     * Retrieve single product payload for direct order checkout token
     */
    public function getDirectSummary(): void {
        $token = $_GET['direct_order'] ?? $_GET['token'] ?? null;

        if (empty($token)) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Direct order token missing.'
            ], 400);
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $directData = $_SESSION['direct_orders'][$token] ?? null;

        if (!$directData || time() > ($directData['expires_at'] ?? 0)) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Direct order session has expired or is invalid. Please start over.'
            ], 400);
        }

        $productId = (int)$directData['product_id'];
        $quantity = (int)$directData['quantity'];

        $product = $this->productModel->find($productId);
        if (!$product || $product['status'] !== 'published') {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Product is no longer available.'
            ], 404);
        }

        if ((int)$product['stock_quantity'] < $quantity) {
            Helper::jsonResponse([
                'success' => false,
                'message' => "Insufficient stock for {$product['name']}."
            ], 422);
        }

        $unitPrice = (float)$directData['price'];
        $itemTotal = $unitPrice * $quantity;

        // Dynamic Shipping Calculation (per product from database)
        $shippingFee = (float)($product['shipping_charge'] ?? 0);
        $totalShipping = $shippingFee * $quantity;
        
        $tax = 0.00;
        $discount = 0.00;
        $grandTotal = $itemTotal + $totalShipping + $tax - $discount;

        $items = [
            [
                'id'            => 'direct_' . $productId,
                'cart_id'       => null,
                'product_id'    => $productId,
                'product_name'  => $product['name'],
                'sku'           => $product['sku'] ?? '',
                'price'         => $unitPrice,
                'quantity'      => $quantity,
                'product_image' => $product['primary_image'] ?? ($product['images'][0]['image_url'] ?? ''),
                'attributes'    => $directData['attributes'] ?? null,
                'shipping_fee'  => $shippingFee
            ]
        ];

        $summary = [
            'subtotal'       => $itemTotal,
            'discount'       => $discount,
            'tax'            => $tax,
            'shipping'       => $totalShipping,
            'total'          => $grandTotal,
            'applied_coupon' => null
        ];

        Helper::jsonResponse([
            'success'      => true,
            'direct_order' => true,
            'token'        => $token,
            'data'         => [
                'items'   => $items,
                'summary' => $summary
            ]
        ], 200);
    }

    /**
     * Process checkout for direct "Buy Now" token
     */
    private function processDirectCheckout(int $userId, array $data, string $token): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $directData = $_SESSION['direct_orders'][$token] ?? null;

        if (!$directData || time() > ($directData['expires_at'] ?? 0)) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Direct checkout session expired or invalid. Please try again.'
            ], 400);
        }

        $shippingAddressId = (int)$data['shipping_address_id'];
        $billingAddressId = isset($data['billing_address_id']) ? (int)$data['billing_address_id'] : $shippingAddressId;
        $paymentMethod = trim($data['payment_method']);
        $notes = $data['notes'] ?? null;

        // Validate Addresses belong to Customer
        $shippingAddress = $this->addressModel->find($shippingAddressId);
        if (!$shippingAddress || (int)$shippingAddress['user_id'] !== $userId) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Invalid shipping address selected.'
            ], 422);
        }

        $productId = (int)$directData['product_id'];
        $quantity = (int)$directData['quantity'];
        $unitPrice = (float)$directData['price'];

        $this->db->beginTransaction();
        try {
            // Row lock stock check
            $stmtLock = $this->db->prepare("SELECT id, name, sku, price, stock_quantity, status, shipping_charge FROM products WHERE id = ? FOR UPDATE");
            $stmtLock->execute([$productId]);
            $prod = $stmtLock->fetch();

            if (!$prod || $prod['status'] !== 'published') {
                throw new Exception("Product is no longer available.");
            }

            if ($quantity > (int)$prod['stock_quantity']) {
                throw new Exception("Insufficient stock available for '{$prod['name']}'. Only {$prod['stock_quantity']} left.");
            }

            // Generate Unique Order Number
            $orderNumber = '';
            $orderExists = true;
            $stmtCheckORD = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE order_number = ?");
            while ($orderExists) {
                $rand = strtoupper(bin2hex(random_bytes(2)));
                $orderNumber = 'ORD-' . date('Ymd') . '-' . $rand;
                $stmtCheckORD->execute([$orderNumber]);
                if ((int)$stmtCheckORD->fetchColumn() === 0) {
                    $orderExists = false;
                }
            }

            $subtotal = $unitPrice * $quantity;

            // Use Ekart shipping if provided; free shipping for orders >= ₹499
            $ekartShippingDirect = isset($data['ekart_shipping_charge']) ? max(0.0, (float)$data['ekart_shipping_charge']) : null;
            if ($subtotal >= 499.0) {
                $shippingCharge = 0.0;
            } elseif ($ekartShippingDirect !== null) {
                $shippingCharge = $ekartShippingDirect;
            } else {
                $shippingCharge = (float)($prod['shipping_charge'] ?? 0) * $quantity;
            }
            $grandTotal = round($subtotal + $shippingCharge, 2);

            $isOnlinePayment = in_array($paymentMethod, ['razorpay', 'upi']);

            $orderId = $this->orderModel->create([
                'order_number'        => $orderNumber,
                'user_id'             => $userId,
                'shipping_address_id' => $shippingAddressId,
                'billing_address_id'  => $billingAddressId,
                'subtotal'            => $subtotal,
                'tax_amount'          => 0.00,
                'shipping_charge'     => $shippingCharge,
                'discount'            => 0.00,
                'coupon_code'         => null,
                'total'               => $grandTotal,
                'payment_method'      => $paymentMethod,
                'payment_status'      => ($paymentMethod === 'cod') ? 'pending' : 'pending_payment',
                'order_status'        => 'pending',
                'notes'               => $notes,
                'expires_at'          => $isOnlinePayment ? date('Y-m-d H:i:s', strtotime('+30 minutes')) : null
            ]);

            // Insert Order Item
            $this->orderItemModel->create($orderId, [
                'product_id'   => $productId,
                'product_name' => $prod['name'],
                'sku'          => $prod['sku'] ?? '',
                'price'        => $unitPrice,
                'quantity'     => $quantity,
                'attributes'   => $directData['attributes'] ? json_encode($directData['attributes']) : null
            ]);

            // Deduct stock
            $stmtUpdateStock = $this->db->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
            $stmtUpdateStock->execute([$quantity, $productId]);

            // Inventory Movement Log safely
            try {
                $stmtInvLog = $this->db->prepare("
                    INSERT INTO inventory_log (product_id, user_id, change_type, quantity_change, reason)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmtInvLog->execute([
                    $productId,
                    $userId,
                    'removed',
                    -$quantity,
                    "Direct Buy Order Placed: $orderNumber"
                ]);
            } catch (Throwable $eInv) {
                error_log("Direct order inventory log skipped: " . $eInv->getMessage());
            }

            // Status History Log safely
            try {
                $this->historyModel->log($orderId, 'pending', 'Direct buy order placed successfully.', $userId);
            } catch (Throwable $eHist) {
                error_log("Direct order history log skipped: " . $eHist->getMessage());
            }

            if (!$isOnlinePayment) {
                unset($_SESSION['direct_orders'][$token]);
            }

            $this->db->commit();

            Helper::jsonResponse([
                'success'        => true,
                'message'        => 'Order placed successfully.',
                'order_id'       => $orderId,
                'order_number'   => $orderNumber,
                'total'          => $grandTotal,
                'payment_method' => $paymentMethod
            ], 201);

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Checkout failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
