<?php
/**
 * Occasion Database Model
 */

class Occasion {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find occasion by ID
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM occasions WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $occasion = $stmt->fetch();
        return $occasion ?: null;
    }

    /**
     * Find occasion by slug
     *
     * @param string $slug
     * @return array|null
     */
    public function findBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("SELECT * FROM occasions WHERE slug = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$slug]);
        $occasion = $stmt->fetch();
        return $occasion ?: null;
    }

    /**
     * Get active occasions with product counts
     *
     * @return array
     */
    public function getActive(): array {
        $stmt = $this->db->query("
            SELECT o.*, COUNT(p.id) as product_count 
            FROM occasions o 
            LEFT JOIN products p ON p.occasion_id = o.id AND p.status = 'published'
            WHERE o.status = 'active' 
            GROUP BY o.id 
            ORDER BY o.sort_order ASC, o.id ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Fetch all occasions for admin
     *
     * @return array
     */
    public function all(): array {
        $stmt = $this->db->query("SELECT * FROM occasions ORDER BY sort_order ASC, id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Create occasion
     *
     * @param array $data
     * @return int
     */
    public function create(array $data): int {
        $sortOrder = isset($data['sort_order']) ? (int)$data['sort_order'] : 0;
        $status = $data['status'] ?? 'active';

        $stmt = $this->db->prepare("
            INSERT INTO occasions (name, slug, image_url, sort_order, status)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['image_url'] ?? null,
            $sortOrder,
            $status
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update occasion
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [];

        $updatable = ['name', 'slug', 'image_url', 'sort_order', 'status'];
        foreach ($updatable as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "`$field` = ?";
                $params[] = ($field === 'sort_order') ? (int)$data[$field] : $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE occasions SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete occasion
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM occasions WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
