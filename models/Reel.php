<?php
/**
 * Reel Database Model
 */

class Reel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find reel by ID
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT r.*, p.name as product_name, p.slug as product_slug, p.price as product_price,
                   (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as product_image
            FROM reels r
            LEFT JOIN products p ON r.product_id = p.id
            WHERE r.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $reel = $stmt->fetch();
        return $reel ?: null;
    }

    /**
     * Fetch all active reels for public display
     *
     * @return array
     */
    public function getActive(): array {
        $stmt = $this->db->query("
            SELECT r.*, p.name as product_name, p.slug as product_slug, p.price as product_price,
                   (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as product_image
            FROM reels r
            LEFT JOIN products p ON r.product_id = p.id
            WHERE r.status = 'active'
            ORDER BY r.sort_order ASC, r.id DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Fetch all reels for admin panel
     *
     * @return array
     */
    public function all(): array {
        $stmt = $this->db->query("
            SELECT r.*, p.name as product_name, p.slug as product_slug
            FROM reels r
            LEFT JOIN products p ON r.product_id = p.id
            ORDER BY r.sort_order ASC, r.id DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Create a new reel
     *
     * @param array $data
     * @return int
     */
    public function create(array $data): int {
        $productId = !empty($data['product_id']) ? (int)$data['product_id'] : null;
        $sortOrder = isset($data['sort_order']) ? (int)$data['sort_order'] : 0;
        $status = $data['status'] ?? 'active';

        $stmt = $this->db->prepare("
            INSERT INTO reels (product_id, title, video_url, thumbnail_url, caption, sort_order, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $productId,
            $data['title'],
            $data['video_url'],
            $data['thumbnail_url'] ?? null,
            $data['caption'] ?? null,
            $sortOrder,
            $status
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing reel
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [];

        $updatable = ['product_id', 'title', 'video_url', 'thumbnail_url', 'caption', 'sort_order', 'status'];
        foreach ($updatable as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "`$field` = ?";
                if ($field === 'product_id') {
                    $params[] = !empty($data[$field]) ? (int)$data[$field] : null;
                } elseif ($field === 'sort_order') {
                    $params[] = (int)$data[$field];
                } else {
                    $params[] = $data[$field];
                }
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE reels SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete a reel
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM reels WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
