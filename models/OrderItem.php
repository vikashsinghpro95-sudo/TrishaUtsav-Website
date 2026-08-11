<?php
/**
 * Order Item Database Model
 */

class OrderItem {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create order item records
     *
     * @param int $orderId
     * @param array $item Details containing product_id, product_name, sku, price, quantity, attributes
     * @return int Created item ID
     */
    public function create(int $orderId, array $item): int {
        $attrJson = !empty($item['attributes']) ? json_encode($item['attributes'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
        $total = round((float)$item['price'] * (int)$item['quantity'], 2);

        $stmt = $this->db->prepare("
            INSERT INTO order_items (
                order_id, product_id, product_name, sku, price, quantity, total, attributes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $orderId,
            $item['product_id'],
            $item['product_name'],
            $item['sku'],
            $item['price'],
            $item['quantity'],
            $total,
            $attrJson
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Get items associated with an order
     *
     * @param int $orderId
     * @return array
     */
    public function getByOrder(int $orderId): array {
        $stmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll();
        foreach ($items as &$item) {
            $item['attributes'] = $item['attributes'] ? json_decode($item['attributes'], true) : null;
        }
        return $items;
    }
}
