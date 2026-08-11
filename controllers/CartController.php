<?php
/**
 * Cart Controller
 */

class CartController {
    private Cart $cartModel;
    private CartItem $cartItemModel;
    private Product $productModel;
    private Coupon $couponModel;

    public function __construct() {
        $this->cartModel = new Cart();
        $this->cartItemModel = new CartItem();
        $this->productModel = new Product();
        $this->couponModel = new Coupon();
    }

    /**
     * GET /api/cart
     * View active cart contents and totals
     */
    public function index(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        try {
            $details = $this->cartModel->getCartDetails($userId);
            Helper::jsonResponse([
                'success' => true,
                'data' => $details
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch cart: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/cart/count
     * Get total quantity of items in customer's cart
     */
    public function count(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        try {
            $cart = $this->cartModel->getOrCreateCart($userId);
            $cartId = (int)$cart['id'];

            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT SUM(quantity) FROM cart_items WHERE cart_id = ?");
            $stmt->execute([$cartId]);
            $count = (int)$stmt->fetchColumn();

            Helper::jsonResponse([
                'success' => true,
                'count' => $count
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch cart count: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/cart/add
     * Add product to cart (or increment quantity if already present)
     */
    public function add(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $data = Helper::getRequestBody();

        $validator = new Validator($data);
        $errors = $validator->validate([
            'product_id' => ['required', 'numeric']
        ]);

        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        $productId = (int)$data['product_id'];
        $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;
        $attributes = isset($data['attributes']) && is_array($data['attributes']) ? $data['attributes'] : null;

        if ($quantity < 1) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Quantity must be at least 1.'
            ], 422);
        }

        try {
            $product = $this->productModel->find($productId);
            if (!$product || $product['status'] !== 'published') {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Product not found or unavailable.'
                ], 404);
            }

            // Get active cart
            $cart = $this->cartModel->getOrCreateCart($userId);
            $cartId = (int)$cart['id'];

            // Check if item is already in cart
            $existingItem = $this->cartItemModel->findItemInCart($cartId, $productId, $attributes);
            $currentQtyInCart = $existingItem ? (int)$existingItem['quantity'] : 0;
            $newQuantity = $currentQtyInCart + $quantity;

            // Verify stock limit
            if ($newQuantity > (int)$product['stock_quantity']) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => "Insufficient stock. Only " . $product['stock_quantity'] . " units available. You already have $currentQtyInCart in cart."
                ], 400);
            }

            if ($existingItem) {
                // Update quantity
                $this->cartItemModel->updateQuantity((int)$existingItem['id'], $newQuantity);
            } else {
                // Determine actual purchase price (price + extra prices for selected attributes if configured)
                $price = (float)$product['price'];
                if ($attributes) {
                    $prodAttrs = $product['attributes'] ?? [];
                    foreach ($attributes as $key => $val) {
                        foreach ($prodAttrs as $pAttr) {
                            if (strtolower($pAttr['attribute_name']) === strtolower($key) && strtolower($pAttr['attribute_value']) === strtolower($val)) {
                                $price += (float)$pAttr['extra_price'];
                            }
                        }
                    }
                }

                // Insert into cart items
                $this->cartItemModel->create($cartId, $productId, $quantity, $price, $attributes);
            }

            // Return updated details
            $details = $this->cartModel->getCartDetails($userId);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Product added to cart.',
                'data' => $details
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to add item to cart: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/cart/update/{id}
     * Update quantity of a cart item
     *
     * @param string $id Cart Item ID
     */
    public function update(string $id): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];
        $itemId = (int)$id;

        $data = Helper::getRequestBody();
        if (!isset($data['quantity'])) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Quantity field is required.'
            ], 422);
        }

        $quantity = (int)$data['quantity'];

        try {
            $item = $this->cartItemModel->find($itemId);
            $cart = $this->cartModel->getOrCreateCart($userId);

            if (!$item || (int)$item['cart_id'] !== (int)$cart['id']) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Cart item not found.'
                ], 404);
            }

            if ($quantity <= 0) {
                $this->cartItemModel->delete($itemId);
            } else {
                // Verify product stock levels
                $product = $this->productModel->find((int)$item['product_id']);
                if ($quantity > (int)$product['stock_quantity']) {
                    Helper::jsonResponse([
                        'success' => false,
                        'message' => "Insufficient stock. Only " . $product['stock_quantity'] . " units available."
                    ], 400);
                }
                $this->cartItemModel->updateQuantity($itemId, $quantity);
            }

            $details = $this->cartModel->getCartDetails($userId);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Cart updated successfully.',
                'data' => $details
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to update quantity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/cart/remove/{id}
     * Remove item from cart
     *
     * @param string $id Cart Item ID
     */
    public function remove(string $id): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];
        $itemId = (int)$id;

        try {
            $item = $this->cartItemModel->find($itemId);
            $cart = $this->cartModel->getOrCreateCart($userId);

            if (!$item || (int)$item['cart_id'] !== (int)$cart['id']) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Cart item not found.'
                ], 404);
            }

            $this->cartItemModel->delete($itemId);

            $details = $this->cartModel->getCartDetails($userId);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Item removed from cart.',
                'data' => $details
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to remove item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/cart/apply-coupon
     * Apply coupon and recalculate totals
     */
    public function applyCoupon(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $data = Helper::getRequestBody();
        if (empty($data['code'])) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Coupon code is required.'
            ], 422);
        }

        $code = trim($data['code']);

        try {
            // Get cart details to extract subtotal and items
            $cart = $this->cartModel->getOrCreateCart($userId);
            $cartId = (int)$cart['id'];
            $items = $this->cartItemModel->getByCart($cartId);

            if (empty($items)) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Cannot apply coupon to an empty cart.'
                ], 400);
            }

            $subtotal = 0.00;
            foreach ($items as $item) {
                $subtotal += round((float)$item['price'] * (int)$item['quantity'], 2);
            }

            // Validate Coupon first to return descriptive error if invalid
            $this->couponModel->validate($code, $subtotal, $userId, $items);

            // Persist applied coupon
            $this->cartModel->applyCouponCode($cartId, $code);

            // Get updated details
            $details = $this->cartModel->getCartDetails($userId);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Coupon applied successfully.',
                'data' => $details
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * DELETE /api/cart/coupon
     * Clear applied coupon
     */
    public function removeCoupon(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        try {
            $cart = $this->cartModel->getOrCreateCart($userId);
            $cartId = (int)$cart['id'];

            $this->cartModel->applyCouponCode($cartId, null);

            $details = $this->cartModel->getCartDetails($userId);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Coupon removed.',
                'data' => $details
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to remove coupon: ' . $e->getMessage()
            ], 500);
        }
    }
}
