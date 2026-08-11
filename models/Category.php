<?php
/**
 * Category Database Model
 */

class Category {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find category by ID
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ? AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([$id]);
        $category = $stmt->fetch();
        return $category ?: null;
    }

    /**
     * Find category by slug
     *
     * @param string $slug
     * @return array|null
     */
    public function findBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE slug = ? AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([$slug]);
        $category = $stmt->fetch();
        return $category ?: null;
    }

    /**
     * Create a new category
     *
     * @param array $data
     * @return int
     */
    public function create(array $data): int {
        $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;
        $status = $data['status'] ?? 'active';

        $stmt = $this->db->prepare("
            INSERT INTO categories (parent_id, name, slug, description, image, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $parentId,
            $data['name'],
            $data['slug'],
            $data['description'] ?? null,
            $data['image'] ?? null,
            $status
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update category dynamically
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [];

        $updatable = ['parent_id', 'name', 'slug', 'description', 'image', 'status'];
        foreach ($updatable as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "`$field` = ?";
                if ($field === 'parent_id') {
                    $params[] = !empty($data[$field]) ? (int)$data[$field] : null;
                } else {
                    $params[] = $data[$field];
                }
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE categories SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Soft delete category by setting deleted_at and status = inactive
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("UPDATE categories SET status = 'inactive', deleted_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get active categories with product counts
     *
     * @return array
     */
    public function getActiveWithCount(): array {
        $stmt = $this->db->query("
            SELECT c.*, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON p.category_id = c.id AND p.status = 'published'
            WHERE c.status = 'active' AND c.deleted_at IS NULL 
            GROUP BY c.id 
            ORDER BY c.name ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get active categories represented as a tree structure
     *
     * @return array
     */
    public function getTree(): array {
        $stmt = $this->db->query("SELECT * FROM categories WHERE status = 'active' AND deleted_at IS NULL ORDER BY name ASC");
        $categories = $stmt->fetchAll();
        return $this->buildTree($categories, null);
    }

    /**
     * Recursively construct nested categories tree array
     *
     * @param array $categories
     * @param int|null $parentId
     * @return array
     */
    private function buildTree(array $categories, ?int $parentId): array {
        $branch = [];
        foreach ($categories as $category) {
            $catParentId = ($category['parent_id'] !== null) ? (int)$category['parent_id'] : null;
            if ($catParentId === $parentId) {
                $children = $this->buildTree($categories, (int)$category['id']);
                $category['children'] = $children;
                $branch[] = $category;
            }
        }
        return $branch;
    }

    /**
     * List all categories (for admin, excluding soft-deleted)
     *
     * @return array
     */
    public function all(): array {
        $stmt = $this->db->query("SELECT * FROM categories WHERE deleted_at IS NULL ORDER BY id DESC");
        return $stmt->fetchAll();
    }
}
