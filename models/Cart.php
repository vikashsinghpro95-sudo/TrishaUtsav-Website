<?php
/**
 * Cart Database Model
 */

class Cart {
    private PDO $db;
    private CartItem $cartItemModel;
    private Coupon $couponModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->cartItemModel = new CartItem();
        $this->couponModel = new Coupon();
    }

    /**
     * Get or create the cart for a user
     *
     * @param int $userId
     * @return array
     */
    public function getOrCreateCart(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM carts WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $cart = $stmt->fetch();

        if (!$cart) {
            $stmtInsert = $this->db->prepare("INSERT INTO carts (user_id) VALUES (?)");
            $stmtInsert->execute([$userId]);
            $cartId = (int)$this->db->lastInsertId();
            return [
                'id' => $cartId,
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        return $cart;
    }

    /**
     * Clear all items in a cart
     *
     * @param int $cartId
     * @return bool
     */
    public function clear(int $cartId): bool {
        $stmt = $this->db->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        return $stmt->execute([$cartId]);
    }

    /**
     * Get complete cart items listing and summary metrics (subtotal, discount, tax, shipping, total)
     *
     * @param int $userId
     * @param string|null $couponCode Optional code to apply and test
     * @return array
     */
    public function getCartDetails(int $userId, ?string $couponCode = null): array {
        $cart = $this->getOrCreateCart($userId);
        $cartId = (int)$cart['id'];

        // Use persisted coupon code if no specific code was passed
        if ($couponCode === null) {
            $couponCode = $cart['coupon_code'] ?? null;
        }

        // Get items in the cart
        $items = $this->cartItemModel->getByCart($cartId);

        $subtotal = 0.00;
        $totalShippingCharge = 0.00;
        $totalWeight = 0.0;
        foreach ($items as &$item) {
            $item['price'] = (float)$item['price'];
            $item['quantity'] = (int)$item['quantity'];
            $item['weight'] = isset($item['weight']) ? (float)$item['weight'] : 0.5;
            $item['item_subtotal'] = round($item['price'] * $item['quantity'], 2);
            $subtotal += $item['item_subtotal'];
            $totalWeight += ($item['weight'] * $item['quantity']);
            $item['attributes'] = $item['attributes'] ? json_decode($item['attributes'], true) : null;
            
            $itemShipping = isset($item['shipping_charge']) ? (float)$item['shipping_charge'] : 0.00;
            $totalShippingCharge += ($itemShipping * $item['quantity']);
        }
        unset($item); // break ref

        $discount = 0.00;
        $appliedCoupon = null;
        $shipping = $totalShippingCharge;

        if (!empty($couponCode) && $subtotal > 0) {
            try {
                // Validate coupon and get details
                $coupon = $this->couponModel->validate($couponCode, $subtotal, $userId, $items);
                $discount = (float)$coupon['calculated_discount'];
                $appliedCoupon = $coupon;

                if ($coupon['type'] === 'free_shipping') {
                    $shipping = 0.00;
                }
            } catch (Exception $e) {
                // If coupon is invalid, we do not apply it but return the error in cart details
                $appliedCoupon = [
                    'code' => $couponCode,
                    'invalid' => true,
                    'error' => $e->getMessage()
                ];
            }
        }

        // Calculate itemized tax after distributing discount proportionally
        $tax = 0.00;
        $discountRatio = 1.0;
        if ($subtotal > 0) {
            $discountRatio = ($subtotal - $discount) / $subtotal;
        }

        foreach ($items as &$item) {
            $itemDiscountedSubtotal = $item['item_subtotal'] * $discountRatio;
            $itemTax = $itemDiscountedSubtotal * ((float)$item['tax_rate'] / 100);
            $item['item_tax'] = round($itemTax, 2);
            $tax += $item['item_tax'];
        }
        unset($item);

        // Normalize metrics
        $subtotal = round($subtotal, 2);
        $discount = round($discount, 2);
        $tax = round($tax, 2);
        $shipping = round($shipping, 2);
        $total = round($subtotal - $discount + $tax + $shipping, 2);

        // Enforce total cannot be negative
        if ($total < 0) {
            $total = 0.00;
        }

        return [
            'cart_id' => $cartId,
            'user_id' => $userId,
            'items' => $items,
            'summary' => [
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax'      => $tax,
                'shipping' => $shipping,
                'total'    => $total,
                'total_weight' => round($totalWeight, 3),
                'applied_coupon' => $appliedCoupon
            ]
        ];
    }

    /**
     * Persist coupon code to the carts table
     *
     * @param int $cartId
     * @param string|null $couponCode
     * @return bool
     */
    public function applyCouponCode(int $cartId, ?string $couponCode): bool {
        $stmt = $this->db->prepare("UPDATE carts SET coupon_code = ? WHERE id = ?");
        return $stmt->execute([$couponCode, $cartId]);
    }
}
