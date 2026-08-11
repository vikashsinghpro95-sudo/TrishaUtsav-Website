<?php
/**
 * Coupon Database Model
 */

class Coupon {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find coupon by ID
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM coupons WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $coupon = $stmt->fetch();
        return $coupon ?: null;
    }

    /**
     * Find coupon by promo code
     *
     * @param string $code
     * @return array|null
     */
    public function findByCode(string $code): ?array {
        $stmt = $this->db->prepare("SELECT * FROM coupons WHERE LOWER(code) = LOWER(?) LIMIT 1");
        $stmt->execute([$code]);
        $coupon = $stmt->fetch();
        return $coupon ?: null;
    }

    /**
     * Create new coupon
     *
     * @param array $data Coupon properties
     * @return int Created coupon ID
     */
    public function create(array $data): int {
        $expiry = !empty($data['expiry_date']) ? $data['expiry_date'] : null;
        $usageLimit = !empty($data['usage_limit']) ? (int)$data['usage_limit'] : null;
        $maxDiscount = !empty($data['max_discount']) ? (float)$data['max_discount'] : null;

        $stmt = $this->db->prepare("
            INSERT INTO coupons (
                code, type, value, min_cart_value, max_discount, 
                usage_limit, is_active, expiry_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            strtoupper($data['code']),
            $data['type'],
            $data['value'],
            $data['min_cart_value'] ?? 0.00,
            $maxDiscount,
            $usageLimit,
            $data['is_active'] ?? 1,
            $expiry
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update coupon details dynamically
     *
     * @param int $id
     * @param array $data Fields to update
     * @return bool
     */
    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [];

        $updatable = ['code', 'type', 'value', 'min_cart_value', 'max_discount', 'usage_limit', 'is_active', 'expiry_date'];
        foreach ($updatable as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "`$field` = ?";
                if ($field === 'code') {
                    $params[] = strtoupper($data[$field]);
                } elseif ($field === 'expiry_date') {
                    $params[] = !empty($data[$field]) ? $data[$field] : null;
                } else {
                    $params[] = $data[$field];
                }
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE coupons SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete coupon record
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM coupons WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Add restriction to a coupon
     *
     * @param int $couponId
     * @param string $type enum: 'product', 'category', 'user'
     * @param int $restrictId Target entity ID
     * @return int Created restriction ID
     */
    public function addRestriction(int $couponId, string $type, int $restrictId): int {
        $stmt = $this->db->prepare("
            INSERT INTO coupon_restrictions (coupon_id, restrict_type, restrict_id)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$couponId, $type, $restrictId]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Get all restrictions for a coupon
     *
     * @param int $couponId
     * @return array
     */
    public function getRestrictions(int $couponId): array {
        $stmt = $this->db->prepare("SELECT * FROM coupon_restrictions WHERE coupon_id = ?");
        $stmt->execute([$couponId]);
        return $stmt->fetchAll();
    }

    /**
     * Remove a specific restriction
     *
     * @param int $restrictionId
     * @return bool
     */
    public function deleteRestriction(int $restrictionId): bool {
        $stmt = $this->db->prepare("DELETE FROM coupon_restrictions WHERE id = ?");
        return $stmt->execute([$restrictionId]);
    }

    /**
     * Increment coupon used_count when an order is placed
     *
     * @param string $code
     * @return bool
     */
    public function incrementUsage(string $code): bool {
        $stmt = $this->db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE LOWER(code) = LOWER(?)");
        return $stmt->execute([$code]);
    }

    /**
     * Decrement coupon usage (e.g. if order gets cancelled)
     *
     * @param string $code
     * @return bool
     */
    public function decrementUsage(string $code): bool {
        $stmt = $this->db->prepare("UPDATE coupons SET used_count = GREATEST(0, used_count - 1) WHERE LOWER(code) = LOWER(?)");
        return $stmt->execute([$code]);
    }

    /**
     * Get list of all coupons
     *
     * @return array
     */
    public function all(): array {
        $stmt = $this->db->query("SELECT * FROM coupons ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Validate coupon eligibility and return coupon details with discount
     *
     * @param string $code
     * @param float $subtotal Current cart subtotal
     * @param int $userId Current customer ID
     * @param array $cartItems Items currently in the cart to check restrictions
     * @return array Validated coupon data containing 'calculated_discount'
     * @throws Exception if coupon is invalid or constraints are unmet
     */
    public function validate(string $code, float $subtotal, int $userId, array $cartItems = []): array {
        $coupon = $this->findByCode($code);
        if (!$coupon) {
            throw new Exception("Coupon code is invalid.");
        }
        if ((int)$coupon['is_active'] !== 1) {
            throw new Exception("Coupon code is inactive.");
        }
        if ($coupon['expiry_date'] !== null && $coupon['expiry_date'] < date('Y-m-d')) {
            throw new Exception("Coupon code has expired.");
        }
        if ($coupon['usage_limit'] !== null && (int)$coupon['used_count'] >= (int)$coupon['usage_limit']) {
            throw new Exception("Coupon code usage limit has been reached.");
        }
        if ($subtotal < (float)$coupon['min_cart_value']) {
            throw new Exception("Minimum cart value of ₹" . number_format($coupon['min_cart_value'], 2) . " required for this coupon.");
        }

        // Verify restrictions (if any exist)
        $restrictions = $this->getRestrictions((int)$coupon['id']);
        if (!empty($restrictions)) {
            $userRestricted = false;
            $userAllowed = false;
            
            $productRestricted = false;
            $productAllowed = false;
            
            $categoryRestricted = false;
            $categoryAllowed = false;

            foreach ($restrictions as $res) {
                if ($res['restrict_type'] === 'user') {
                    $userRestricted = true;
                    if ((int)$res['restrict_id'] === $userId) {
                        $userAllowed = true;
                    }
                }
                if ($res['restrict_type'] === 'product') {
                    $productRestricted = true;
                    foreach ($cartItems as $item) {
                        if ((int)$item['product_id'] === (int)$res['restrict_id']) {
                            $productAllowed = true;
                            break;
                        }
                    }
                }
                if ($res['restrict_type'] === 'category') {
                    $categoryRestricted = true;
                    foreach ($cartItems as $item) {
                        if ((int)$item['category_id'] === (int)$res['restrict_id']) {
                            $categoryAllowed = true;
                            break;
                        }
                    }
                }
            }

            if ($userRestricted && !$userAllowed) {
                throw new Exception("This coupon code is not valid for your account.");
            }
            if ($productRestricted && !$productAllowed) {
                throw new Exception("This coupon code does not apply to the products in your cart.");
            }
            if ($categoryRestricted && !$categoryAllowed) {
                throw new Exception("This coupon code does not apply to the categories of products in your cart.");
            }
        }

        // Calculate discount amount
        $discount = 0.00;
        if ($coupon['type'] === 'percentage') {
            $discount = $subtotal * ((float)$coupon['value'] / 100);
            if ($coupon['max_discount'] !== null && $discount > (float)$coupon['max_discount']) {
                $discount = (float)$coupon['max_discount'];
            }
        } elseif ($coupon['type'] === 'fixed') {
            $discount = (float)$coupon['value'];
            if ($discount > $subtotal) {
                $discount = $subtotal;
            }
        } elseif ($coupon['type'] === 'free_shipping') {
            $discount = 0.00; // Discount on shipping handled separately in shipping calculation
        }

        $coupon['calculated_discount'] = round($discount, 2);
        return $coupon;
    }
}
