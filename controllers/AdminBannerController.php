<?php
/**
 * Admin Banner Controller
 */

class AdminBannerController {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * GET /api/admin/banners
     * List all banners (Admin only)
     */
    public function index(): void {
        AuthMiddleware::handle(true);

        try {
            $stmt = $this->db->query("SELECT * FROM banners ORDER BY id DESC");
            $banners = $stmt->fetchAll();

            Helper::jsonResponse([
                'success' => true,
                'data' => $banners
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch banners: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/admin/banners
     * Create a banner (Admin only)
     */
    public function store(): void {
        $user = AuthMiddleware::handle(true);
        $adminId = (int)$user['id'];

        $data = Helper::getRequestBody();

        $validator = new Validator($data);
        $errors = $validator->validate([
            'title'     => ['required', 'maxLength:255'],
            'image_url' => ['required', 'maxLength:255']
        ]);

        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        try {
            $status = $data['status'] ?? 'active';
            $stmt = $this->db->prepare("
                INSERT INTO banners (title, image_url, link_url, status)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['title'],
                $data['image_url'],
                $data['link_url'] ?? null,
                $status
            ]);
            $bannerId = (int)$this->db->lastInsertId();

            Helper::logAction($adminId, 'create_banner', 'banners', $bannerId, null, $data);

            Helper::jsonResponse([
                'success' => true,
                'message' => 'Banner created successfully.',
                'banner_id' => $bannerId
            ], 201);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to save banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/admin/banners/{id}
     * Update banner details (Admin only)
     *
     * @param string $id Banner ID
     */
    public function update(string $id): void {
        $user = AuthMiddleware::handle(true);
        $adminId = (int)$user['id'];
        $bannerId = (int)$id;

        // Verify exists
        $stmtFind = $this->db->prepare("SELECT * FROM banners WHERE id = ?");
        $stmtFind->execute([$bannerId]);
        $banner = $stmtFind->fetch();

        if (!$banner) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Banner not found.'
            ], 404);
        }

        $data = Helper::getRequestBody();

        try {
            $fields = [];
            $params = [];
            foreach (['title', 'image_url', 'link_url', 'status'] as $key) {
                if (array_key_exists($key, $data)) {
                    $fields[] = "$key = ?";
                    $params[] = $data[$key];
                }
            }

            if (!empty($fields)) {
                $params[] = $bannerId;
                $stmt = $this->db->prepare("UPDATE banners SET " . implode(", ", $fields) . " WHERE id = ?");
                $stmt->execute($params);

                Helper::logAction($adminId, 'update_banner', 'banners', $bannerId, $banner, $data);
            }

            Helper::jsonResponse([
                'success' => true,
                'message' => 'Banner updated successfully.'
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to update banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/admin/banners/{id}
     * Delete banner (Admin only)
     *
     * @param string $id Banner ID
     */
    public function destroy(string $id): void {
        $user = AuthMiddleware::handle(true);
        $adminId = (int)$user['id'];
        $bannerId = (int)$id;

        // Verify exists
        $stmtFind = $this->db->prepare("SELECT * FROM banners WHERE id = ?");
        $stmtFind->execute([$bannerId]);
        $banner = $stmtFind->fetch();

        if (!$banner) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Banner not found.'
            ], 404);
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM banners WHERE id = ?");
            $stmt->execute([$bannerId]);

            Helper::logAction($adminId, 'delete_banner', 'banners', $bannerId, $banner, null);

            Helper::jsonResponse([
                'success' => true,
                'message' => 'Banner deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to delete banner: ' . $e->getMessage()
            ], 500);
        }
    }
}
