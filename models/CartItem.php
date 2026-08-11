<?php
/**
 * Cart Item Database Model
 */

class CartItem {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find cart item by ID
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM cart_items WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        return $item ?: null;
    }

    /**
     * Find item in cart with matching product ID and JSON attributes
     *
     * @param int $cartId
     * @param int $productId
     * @param array|null $attributes
     * @return array|null
     */
    public function findItemInCart(int $cartId, int $productId, ?array $attributes): ?array {
        $stmt = $this->db->prepare("SELECT * FROM cart_items WHERE cart_id = ? AND product_id = ?");
        $stmt->execute([$cartId, $productId]);
        $items = $stmt->fetchAll();

        foreach ($items as $item) {
            $itemAttrs = $item['attributes'] ? json_decode($item['attributes'], true) : null;
            if ($this->compareAttributes($itemAttrs, $attributes)) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Insert new item into the cart
     *
     * @param int $cartId
     * @param int $productId
     * @param int $quantity
     * @param float $price Captured price
     * @param array|null $attributes Selected options
     * @return int
     */
    public function create(int $cartId, int $productId, int $quantity, float $price, ?array $attributes): int {
        $attrJson = $attributes ? json_encode($attributes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
        
        $stmt = $this->db->prepare("
            INSERT INTO cart_items (cart_id, product_id, quantity, price, attributes)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$cartId, $productId, $quantity, $price, $attrJson]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update quantity for a cart item
     *
     * @param int $id
     * @param int $quantity
     * @return bool
     */
    public function updateQuantity(int $id, int $quantity): bool {
        if ($quantity <= 0) {
            return $this->delete($id);
        }
        $stmt = $this->db->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        return $stmt->execute([$quantity, $id]);
    }

    /**
     * Delete an item from the cart
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM cart_items WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get all items belonging to a cart
     *
     * @param int $cartId
     * @return array
     */
    public function getByCart(int $cartId): array {
        $stmt = $this->db->prepare("
            SELECT ci.*, p.name as product_name, p.slug as product_slug, p.sku, p.tax_rate, p.shipping_charge, p.stock_quantity as available_stock, p.status as product_status,
                   (SELECT image_url FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC, id ASC LIMIT 1) as primary_image
            FROM cart_items ci
            INNER JOIN products p ON ci.product_id = p.id
            WHERE ci.cart_id = ?
        ");
        $stmt->execute([$cartId]);
        return $stmt->fetchAll();
    }

    /**
     * Verify equality of two attribute arrays irrespective of sorting order
     *
     * @param array|null $a
     * @param array|null $b
     * @return bool
     */
    private function compareAttributes(?array $a, ?array $b): bool {
        if ($a === null && $b === null) {
            return true;
        }
        if ($a === null || $b === null) {
            return false;
        }
        ksort($a);
        ksort($b);
        return json_encode($a) === json_encode($b);
    }
}
