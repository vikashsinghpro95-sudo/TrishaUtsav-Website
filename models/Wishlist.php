<?php
/**
 * Wishlist Database Model
 */

class Wishlist {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get or create a wishlist record for a user
     *
     * @param int $userId
     * @return array
     */
    public function getOrCreateWishlist(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM wishlists WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $wishlist = $stmt->fetch();

        if ($wishlist) {
            return $wishlist;
        }

        $stmtInsert = $this->db->prepare("INSERT INTO wishlists (user_id) VALUES (?)");
        $stmtInsert->execute([$userId]);
        $wishlistId = (int)$this->db->lastInsertId();

        return [
            'id' => $wishlistId,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get all wishlist items with full product details for a user
     *
     * @param int $userId
     * @return array
     */
    public function getItems(int $userId): array {
        $wishlist = $this->getOrCreateWishlist($userId);
        $wishlistId = (int)$wishlist['id'];

        $sql = "
            SELECT 
                p.*,
                wi.added_at as wishlist_added_at,
                c.name as category_name,
                c.slug as category_slug,
                b.name as brand_name,
                b.slug as brand_slug
            FROM wishlist_items wi
            INNER JOIN products p ON wi.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE wi.wishlist_id = ? AND p.deleted_at IS NULL
            ORDER BY wi.added_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$wishlistId]);
        $items = $stmt->fetchAll();

        // Fetch primary image & gallery images for each product
        foreach ($items as &$prod) {
            $prod['is_in_wishlist'] = true;
            $stmtImg = $this->db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC");
            $stmtImg->execute([$prod['id']]);
            $images = $stmtImg->fetchAll();
            $prod['images'] = $images;

            $primary = array_filter($images, fn($img) => (int)$img['is_primary'] === 1);
            if (!empty($primary)) {
                $first = reset($primary);
                $prod['primary_image'] = $first['image_url'];
            } else if (!empty($images)) {
                $prod['primary_image'] = $images[0]['image_url'];
            } else {
                $prod['primary_image'] = null;
            }
        }
        unset($prod);

        return $items;
    }

    /**
     * Get array of product IDs in a user's wishlist
     *
     * @param int $userId
     * @return array
     */
    public function getWishlistProductIds(int $userId): array {
        $wishlist = $this->getOrCreateWishlist($userId);
        $wishlistId = (int)$wishlist['id'];

        $stmt = $this->db->prepare("SELECT product_id FROM wishlist_items WHERE wishlist_id = ?");
        $stmt->execute([$wishlistId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * Toggle product in user's wishlist (Add if missing, Remove if present)
     *
     * @param int $userId
     * @param int $productId
     * @return array ['action' => 'added'|'removed', 'count' => int]
     */
    public function toggleItem(int $userId, int $productId): array {
        $wishlist = $this->getOrCreateWishlist($userId);
        $wishlistId = (int)$wishlist['id'];

        $stmtCheck = $this->db->prepare("SELECT id FROM wishlist_items WHERE wishlist_id = ? AND product_id = ? LIMIT 1");
        $stmtCheck->execute([$wishlistId, $productId]);
        $existing = $stmtCheck->fetch();

        if ($existing) {
            $stmtDel = $this->db->prepare("DELETE FROM wishlist_items WHERE wishlist_id = ? AND product_id = ?");
            $stmtDel->execute([$wishlistId, $productId]);
            $action = 'removed';
        } else {
            $stmtIns = $this->db->prepare("INSERT IGNORE INTO wishlist_items (wishlist_id, product_id) VALUES (?, ?)");
            $stmtIns->execute([$wishlistId, $productId]);
            $action = 'added';
        }

        $count = $this->getItemCount($userId);

        return [
            'action' => $action,
            'count' => $count
        ];
    }

    /**
     * Remove specific product from wishlist
     *
     * @param int $userId
     * @param int $productId
     * @return bool
     */
    public function removeItem(int $userId, int $productId): bool {
        $wishlist = $this->getOrCreateWishlist($userId);
        $wishlistId = (int)$wishlist['id'];

        $stmt = $this->db->prepare("DELETE FROM wishlist_items WHERE wishlist_id = ? AND product_id = ?");
        return $stmt->execute([$wishlistId, $productId]);
    }

    /**
     * Get total count of items in wishlist for user
     *
     * @param int $userId
     * @return int
     */
    public function getItemCount(int $userId): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM wishlist_items wi 
            INNER JOIN wishlists w ON wi.wishlist_id = w.id 
            WHERE w.user_id = ?
        ");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Merge guest session wishlist into logged-in user wishlist
     *
     * @param int $userId
     * @param array $sessionProductIds
     * @return void
     */
    public function mergeSessionWishlist(int $userId, array $sessionProductIds): void {
        if (empty($sessionProductIds)) return;

        $wishlist = $this->getOrCreateWishlist($userId);
        $wishlistId = (int)$wishlist['id'];

        $stmt = $this->db->prepare("INSERT IGNORE INTO wishlist_items (wishlist_id, product_id) VALUES (?, ?)");
        foreach ($sessionProductIds as $pId) {
            $pId = (int)$pId;
            if ($pId > 0) {
                $stmt->execute([$wishlistId, $pId]);
            }
        }
    }
}
