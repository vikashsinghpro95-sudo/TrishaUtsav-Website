<?php
/**
 * Product Database Model
 */

class Product {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find a product by ID
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, b.name as brand_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN brands b ON p.brand_id = b.id 
            WHERE p.id = ? 
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if ($product) {
            $product['images'] = $this->getImages($id);
            $product['attributes'] = $this->getAttributes($id);
        }
        return $product ?: null;
    }

    /**
     * Find a product by slug
     *
     * @param string $slug
     * @return array|null
     */
    public function findBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, b.name as brand_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN brands b ON p.brand_id = b.id 
            WHERE p.slug = ? 
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        $product = $stmt->fetch();
        if ($product) {
            $id = (int)$product['id'];
            $product['images'] = $this->getImages($id);
            $product['attributes'] = $this->getAttributes($id);
        }
        return $product ?: null;
    }

    /**
     * Retrieve all images for a product
     *
     * @param int $productId
     * @return array
     */
    public function getImages(int $productId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM product_images 
            WHERE product_id = ? 
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    /**
     * Retrieve all attributes for a product
     *
     * @param int $productId
     * @return array
     */
    public function getAttributes(int $productId): array {
        $stmt = $this->db->prepare("SELECT * FROM product_attributes WHERE product_id = ?");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    /**
     * Create a new product with images and attributes inside a transaction
     *
     * @param array $data
     * @return int Created product ID
     * @throws Exception
     */
    public function create(array $data): int {
        try {
            $this->db->beginTransaction();

            $brandId = !empty($data['brand_id']) ? (int)$data['brand_id'] : null;
            $occasionId = !empty($data['occasion_id']) ? (int)$data['occasion_id'] : null;
            $status = $data['status'] ?? 'draft';
            $featured = $data['featured'] ?? 0;
            $isTrending = $data['is_trending'] ?? 0;
            $isMustBuy = $data['is_must_buy'] ?? 0;

            $stmt = $this->db->prepare("
                INSERT INTO products (
                    category_id, brand_id, occasion_id, name, slug, sku, short_description, 
                    description, price, mrp, tax_rate, shipping_charge, stock_quantity, 
                    low_stock_threshold, weight, dimensions, status, featured, is_trending, is_must_buy,
                    meta_title, meta_keywords, meta_description
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                (int)$data['category_id'],
                $brandId,
                $occasionId,
                $data['name'],
                $data['slug'],
                $data['sku'] ?? null,
                $data['short_description'] ?? null,
                $data['description'] ?? null,
                $data['price'],
                $data['mrp'] ?? null,
                $data['tax_rate'] ?? 0.00,
                $data['shipping_charge'] ?? 0.00,
                $data['stock_quantity'] ?? 0,
                $data['low_stock_threshold'] ?? 5,
                $data['weight'] ?? null,
                $data['dimensions'] ?? null,
                $status,
                $featured,
                $isTrending,
                $isMustBuy,
                $data['meta_title'] ?? null,
                $data['meta_keywords'] ?? null,
                $data['meta_description'] ?? null
            ]);

            $productId = (int)$this->db->lastInsertId();

            // Insert attributes if present
            if (isset($data['attributes']) && is_array($data['attributes'])) {
                $stmtAttr = $this->db->prepare("
                    INSERT INTO product_attributes (product_id, attribute_name, attribute_value, extra_price)
                    VALUES (?, ?, ?, ?)
                ");
                foreach ($data['attributes'] as $attr) {
                    $stmtAttr->execute([
                        $productId,
                        $attr['attribute_name'],
                        $attr['attribute_value'],
                        $attr['extra_price'] ?? 0.00
                    ]);
                }
            }

            // Insert images if present
            if (isset($data['images']) && is_array($data['images'])) {
                $stmtImg = $this->db->prepare("
                    INSERT INTO product_images (product_id, image_url, alt_text, is_primary, sort_order)
                    VALUES (?, ?, ?, ?, ?)
                ");
                foreach ($data['images'] as $img) {
                    $stmtImg->execute([
                        $productId,
                        $img['image_url'],
                        $img['alt_text'] ?? null,
                        $img['is_primary'] ?? 0,
                        $img['sort_order'] ?? 0
                    ]);
                }
            }

            $this->db->commit();
            return $productId;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Update product details, with optional images/attributes synchronization
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function update(int $id, array $data): bool {
        try {
            $this->db->beginTransaction();

            $fields = [];
            $params = [];

            $updatable = [
                'category_id', 'brand_id', 'occasion_id', 'name', 'slug', 'sku', 'short_description', 
                'description', 'price', 'mrp', 'tax_rate', 'shipping_charge', 'stock_quantity', 
                'low_stock_threshold', 'weight', 'dimensions', 'status', 'featured', 'is_trending', 'is_must_buy',
                'meta_title', 'meta_keywords', 'meta_description'
            ];

            foreach ($updatable as $field) {
                if (array_key_exists($field, $data)) {
                    $fields[] = "`$field` = ?";
                    if ($field === 'category_id' || $field === 'brand_id' || $field === 'occasion_id') {
                        $params[] = !empty($data[$field]) ? (int)$data[$field] : null;
                    } else {
                        $params[] = $data[$field];
                    }
                }
            }

            if (!empty($fields)) {
                $params[] = $id;
                $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
            }

            // Sync attributes if sent
            if (isset($data['attributes']) && is_array($data['attributes'])) {
                $this->db->prepare("DELETE FROM product_attributes WHERE product_id = ?")->execute([$id]);
                $stmtAttr = $this->db->prepare("
                    INSERT INTO product_attributes (product_id, attribute_name, attribute_value, extra_price)
                    VALUES (?, ?, ?, ?)
                ");
                foreach ($data['attributes'] as $attr) {
                    $stmtAttr->execute([
                        $id,
                        $attr['attribute_name'],
                        $attr['attribute_value'],
                        $attr['extra_price'] ?? 0.00
                    ]);
                }
            }

            // Sync images if sent
            if (isset($data['images']) && is_array($data['images'])) {
                $this->db->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$id]);
                $stmtImg = $this->db->prepare("
                    INSERT INTO product_images (product_id, image_url, alt_text, is_primary, sort_order)
                    VALUES (?, ?, ?, ?, ?)
                ");
                foreach ($data['images'] as $img) {
                    $stmtImg->execute([
                        $id,
                        $img['image_url'],
                        $img['alt_text'] ?? null,
                        $img['is_primary'] ?? 0,
                        $img['sort_order'] ?? 0
                    ]);
                }
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Delete/Archive a product record (changes status to archived)
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("UPDATE products SET status = 'archived' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Add single image to an existing product
     *
     * @param int $productId
     * @param string $imageUrl
     * @param string|null $altText
     * @param int $isPrimary
     * @param int $sortOrder
     * @return int Added image ID
     */
    public function addImage(int $productId, string $imageUrl, ?string $altText = null, int $isPrimary = 0, int $sortOrder = 0): int {
        if ($isPrimary === 1) {
            $this->db->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?")->execute([$productId]);
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO product_images (product_id, image_url, alt_text, is_primary, sort_order)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$productId, $imageUrl, $altText, $isPrimary, $sortOrder]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Delete a single image from product
     *
     * @param int $productId
     * @param int $imageId
     * @return bool
     */
    public function deleteProductImage(int $productId, int $imageId): bool {
        $stmt = $this->db->prepare("SELECT image_url, is_primary FROM product_images WHERE id = ? AND product_id = ?");
        $stmt->execute([$imageId, $productId]);
        $img = $stmt->fetch();
        if (!$img) {
            return false;
        }

        $stmtDel = $this->db->prepare("DELETE FROM product_images WHERE id = ? AND product_id = ?");
        $stmtDel->execute([$imageId, $productId]);

        if ((int)$img['is_primary'] === 1) {
            $stmtNext = $this->db->prepare("SELECT id FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC LIMIT 1");
            $stmtNext->execute([$productId]);
            $nextId = $stmtNext->fetchColumn();
            if ($nextId) {
                $this->db->prepare("UPDATE product_images SET is_primary = 1 WHERE id = ?")->execute([$nextId]);
            }
        }

        $filePath = __DIR__ . '/../' . $img['image_url'];
        if (is_file($filePath)) {
            unlink($filePath);
        }

        return true;
    }

    /**
     * Delete multiple images from product (bulk delete)
     *
     * @param int $productId
     * @param array $imageIds Array of image IDs to delete
     * @return bool
     */
    public function deleteMultipleImages(int $productId, array $imageIds): bool {
        if (empty($imageIds)) return true;

        $placeholders = implode(',', array_fill(0, count($imageIds), '?'));
        $stmt = $this->db->prepare("SELECT id, image_url, is_primary FROM product_images WHERE product_id = ? AND id IN ($placeholders)");
        $stmt->execute(array_merge([$productId], $imageIds));
        $images = $stmt->fetchAll();

        if (empty($images)) {
            return false;
        }

        $stmtDel = $this->db->prepare("DELETE FROM product_images WHERE product_id = ? AND id IN ($placeholders)");
        $stmtDel->execute(array_merge([$productId], $imageIds));

        $wasPrimaryDeleted = false;
        foreach ($images as $img) {
            if ((int)$img['is_primary'] === 1) {
                $wasPrimaryDeleted = true;
            }
            $filePath = __DIR__ . '/../' . $img['image_url'];
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }

        if ($wasPrimaryDeleted) {
            $stmtNext = $this->db->prepare("SELECT id FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC LIMIT 1");
            $stmtNext->execute([$productId]);
            $nextId = $stmtNext->fetchColumn();
            if ($nextId) {
                $this->db->prepare("UPDATE product_images SET is_primary = 1 WHERE id = ?")->execute([$nextId]);
            }
        }

        return true;
    }

    /**
     * Update image metadata (alt_text and title)
     *
     * @param int $productId
     * @param int $imageId
     * @param array $data Contains optional 'alt_text' and 'title'
     * @return bool
     */
    public function updateImageMetadata(int $productId, int $imageId, array $data): bool {
        $fields = [];
        $params = [];

        if (array_key_exists('alt_text', $data)) {
            $fields[] = "alt_text = ?";
            $params[] = $data['alt_text'] !== null ? substr(trim($data['alt_text']), 0, 125) : null;
        }

        if (array_key_exists('title', $data)) {
            $fields[] = "title = ?";
            $params[] = $data['title'] !== null ? trim($data['title']) : null;
        }

        if (empty($fields)) {
            return true;
        }

        $params[] = $imageId;
        $params[] = $productId;

        $sql = "UPDATE product_images SET " . implode(', ', $fields) . " WHERE id = ? AND product_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Update the sort order and primary flag for a set of product images
     *
     * @param int $productId
     * @param array $imagesData Array of ['id' => 1, 'sort_order' => 0, 'is_primary' => 1]
     * @return bool
     * @throws Exception
     */
    public function updateImageOrder(int $productId, array $imagesData): bool {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                UPDATE product_images 
                SET sort_order = ?, is_primary = ? 
                WHERE id = ? AND product_id = ?
            ");

            foreach ($imagesData as $img) {
                if (!isset($img['id'])) continue;
                $stmt->execute([
                    (int)($img['sort_order'] ?? 0),
                    (int)($img['is_primary'] ?? 0),
                    (int)$img['id'],
                    $productId
                ]);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Adjust stock level and record the entry in the inventory_log
     *
     * @param int $productId
     * @param int $userId The admin user performing the adjustment
     * @param int $quantityChange Can be positive or negative
     * @param string|null $reason
     * @return bool
     * @throws Exception
     */
    public function adjustStock(int $productId, int $userId, int $quantityChange, ?string $reason = null): bool {
        try {
            $this->db->beginTransaction();

            // Lock the row for update to prevent race conditions
            $stmt = $this->db->prepare("SELECT stock_quantity FROM products WHERE id = ? FOR UPDATE");
            $stmt->execute([$productId]);
            $currentStock = $stmt->fetchColumn();

            if ($currentStock === false) {
                throw new Exception("Product not found.");
            }

            $newStock = $currentStock + $quantityChange;
            if ($newStock < 0) {
                throw new Exception("Inventory adjustment rejected. Resulting stock cannot be less than 0.");
            }

            // Update product stock level
            $stmtUpdate = $this->db->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
            $stmtUpdate->execute([$newStock, $productId]);

            // Log stock movement
            $changeType = 'adjusted';
            if ($quantityChange > 0) {
                $changeType = 'added';
            } elseif ($quantityChange < 0) {
                $changeType = 'removed';
            }

            $stmtLog = $this->db->prepare("
                INSERT INTO inventory_log (product_id, user_id, change_type, quantity_change, reason)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmtLog->execute([
                $productId,
                $userId,
                $changeType,
                $quantityChange,
                $reason
            ]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Search, Filter, Sort, and Paginate products
     *
     * @param array $filters Query parameters
     * @return array Matches list along with pagination details
     */
    public function searchAndFilter(array $filters): array {
        $query = "
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN occasions o ON p.occasion_id = o.id
            WHERE 1=1
        ";
        $params = [];

        // Status filter (defaults to published)
        if (!empty($filters['status'])) {
            $query .= " AND p.status = ?";
            $params[] = $filters['status'];
        } else {
            $query .= " AND p.status = 'published'";
        }

        // Category filter (supports ID or slug)
        if (!empty($filters['category'])) {
            if (is_numeric($filters['category'])) {
                $query .= " AND p.category_id = ?";
                $params[] = (int)$filters['category'];
            } else {
                $query .= " AND c.slug = ?";
                $params[] = $filters['category'];
            }
        } elseif (!empty($filters['category_id'])) {
            $query .= " AND p.category_id = ?";
            $params[] = (int)$filters['category_id'];
        }

        // Brand filter (supports ID or slug)
        if (!empty($filters['brand'])) {
            if (is_numeric($filters['brand'])) {
                $query .= " AND p.brand_id = ?";
                $params[] = (int)$filters['brand'];
            } else {
                $query .= " AND b.slug = ?";
                $params[] = $filters['brand'];
            }
        }

        // Occasion filter (supports ID or slug)
        if (!empty($filters['occasion'])) {
            if (is_numeric($filters['occasion'])) {
                $query .= " AND p.occasion_id = ?";
                $params[] = (int)$filters['occasion'];
            } else {
                $query .= " AND o.slug = ?";
                $params[] = $filters['occasion'];
            }
        } elseif (!empty($filters['occasion_id'])) {
            $query .= " AND p.occasion_id = ?";
            $params[] = (int)$filters['occasion_id'];
        }

        // Featured filter
        if (isset($filters['featured'])) {
            $query .= " AND p.featured = ?";
            $params[] = (int)$filters['featured'];
        }

        // Trending filter
        if (isset($filters['is_trending'])) {
            $query .= " AND p.is_trending = ?";
            $params[] = (int)$filters['is_trending'];
        }

        // Must Buy filter
        if (isset($filters['is_must_buy'])) {
            $query .= " AND p.is_must_buy = ?";
            $params[] = (int)$filters['is_must_buy'];
        }

        // Price range filters
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $query .= " AND p.price >= ?";
            $params[] = (float)$filters['min_price'];
        }
        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $query .= " AND p.price <= ?";
            $params[] = (float)$filters['max_price'];
        }

        // Search filter (uses FULLTEXT matching, falling back to LIKE)
        if (!empty($filters['search'])) {
            $query .= " AND (MATCH(p.name, p.short_description, p.description) AGAINST(? IN BOOLEAN MODE) OR p.name LIKE ?)";
            // Sanitize boolean operators for FULLTEXT search to prevent SQLSTATE[42000] syntax errors
            $safeSearch = preg_replace('/[+\-<>\~*\"@\(\)]/', ' ', $filters['search']);
            $params[] = trim($safeSearch) . '*';
            $params[] = '%' . $filters['search'] . '%';
        }

        // Calculate total count
        $countQuery = "SELECT COUNT(DISTINCT p.id) " . $query;
        $stmtCount = $this->db->prepare($countQuery);
        $stmtCount->execute($params);
        $totalItems = (int)$stmtCount->fetchColumn();

        // Sorting
        $sort = $filters['sort'] ?? 'newest';
        $orderBy = " ORDER BY p.created_at DESC";
        switch ($sort) {
            case 'price_asc':
                $orderBy = " ORDER BY p.price ASC";
                break;
            case 'price_desc':
                $orderBy = " ORDER BY p.price DESC";
                break;
            case 'name_asc':
                $orderBy = " ORDER BY p.name ASC";
                break;
            case 'name_desc':
                $orderBy = " ORDER BY p.name DESC";
                break;
            case 'newest':
            default:
                $orderBy = " ORDER BY p.created_at DESC";
                break;
        }
        
        $query .= $orderBy;

        // Pagination calculations
        $page = isset($filters['page']) ? (int)$filters['page'] : 1;
        $perPage = isset($filters['per_page']) ? (int)$filters['per_page'] : 10;
        if ($page < 1) $page = 1;
        if ($perPage < 1) $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $query .= " LIMIT $perPage OFFSET $offset";

        $selectQuery = "
            SELECT p.*, c.name as category_name, c.slug as category_slug, b.name as brand_name, b.slug as brand_slug
            " . $query;

        $stmtSelect = $this->db->prepare($selectQuery);
        $stmtSelect->execute($params);
        $products = $stmtSelect->fetchAll();

        // Batch populate relationships (eliminating N+1 query overhead)
        if (!empty($products)) {
            $productIds = array_column($products, 'id');
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));

            // Batch load images
            $stmtImgs = $this->db->prepare("SELECT * FROM product_images WHERE product_id IN ($placeholders) ORDER BY sort_order ASC, id ASC");
            $stmtImgs->execute($productIds);
            $imagesByProduct = [];
            foreach ($stmtImgs->fetchAll() as $img) {
                $imagesByProduct[(int)$img['product_id']][] = $img;
            }

            // Batch load attributes
            $stmtAttrs = $this->db->prepare("SELECT * FROM product_attributes WHERE product_id IN ($placeholders)");
            $stmtAttrs->execute($productIds);
            $attrsByProduct = [];
            foreach ($stmtAttrs->fetchAll() as $attr) {
                $attrsByProduct[(int)$attr['product_id']][] = $attr;
            }

            foreach ($products as &$product) {
                $pId = (int)$product['id'];
                $product['images'] = $imagesByProduct[$pId] ?? [];
                $product['attributes'] = $attrsByProduct[$pId] ?? [];

                $primaryImg = null;
                if (!empty($product['images'])) {
                    foreach ($product['images'] as $img) {
                        if (!empty($img['is_primary'])) {
                            $primaryImg = $img['image_url'];
                            break;
                        }
                    }
                    if (!$primaryImg && isset($product['images'][0])) {
                        $primaryImg = $product['images'][0]['image_url'];
                    }
                }
                $product['primary_image'] = $primaryImg;
            }
            unset($product);
        }

        $totalPages = ceil($totalItems / $perPage);

        return [
            'data' => $products,
            'pagination' => [
                'total_items'  => $totalItems,
                'total_pages'  => $totalPages,
                'current_page' => $page,
                'per_page'     => $perPage
            ]
        ];
    }
}
