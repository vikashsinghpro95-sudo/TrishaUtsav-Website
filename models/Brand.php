<?php
/**
 * Brand Database Model
 */

class Brand {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find brand by ID
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM brands WHERE id = ? AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([$id]);
        $brand = $stmt->fetch();
        return $brand ?: null;
    }

    /**
     * Find brand by slug
     *
     * @param string $slug
     * @return array|null
     */
    public function findBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("SELECT * FROM brands WHERE slug = ? AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([$slug]);
        $brand = $stmt->fetch();
        return $brand ?: null;
    }

    /**
     * Create new brand
     *
     * @param array $data
     * @return int
     */
    public function create(array $data): int {
        $status = $data['status'] ?? 'active';
        $stmt = $this->db->prepare("
            INSERT INTO brands (name, slug, logo, status)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['logo'] ?? null,
            $status
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * List all brands (excluding soft-deleted)
     *
     * @return array
     */
    public function all(): array {
        $stmt = $this->db->query("SELECT * FROM brands WHERE deleted_at IS NULL ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Update brand details
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [];

        if (array_key_exists('name', $data)) {
            $fields[] = "name = ?";
            $params[] = $data['name'];
        }
        if (array_key_exists('slug', $data)) {
            $fields[] = "slug = ?";
            $params[] = $data['slug'];
        }
        if (array_key_exists('logo', $data)) {
            $fields[] = "logo = ?";
            $params[] = $data['logo'];
        }
        if (array_key_exists('status', $data)) {
            $fields[] = "status = ?";
            $params[] = $data['status'];
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE brands SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Soft delete a brand
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("UPDATE brands SET status = 'inactive', deleted_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
