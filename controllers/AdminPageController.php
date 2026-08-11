<?php
/**
 * Admin Static Pages CMS Controller
 */

class AdminPageController {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * GET /api/admin/pages
     * List all static CMS pages (Admin only)
     */
    public function index(): void {
        AuthMiddleware::handle(true);

        try {
            $stmt = $this->db->query("SELECT * FROM pages ORDER BY title ASC");
            $pages = $stmt->fetchAll();

            Helper::jsonResponse([
                'success' => true,
                'data' => $pages
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch CMS pages: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/admin/pages
     * Create a static page (Admin only)
     */
    public function store(): void {
        $user = AuthMiddleware::handle(true);
        $adminId = (int)$user['id'];

        $data = Helper::getRequestBody();
        if (empty($data['slug']) && !empty($data['title'])) {
            $data['slug'] = Helper::slugify($data['title']);
        }

        $validator = new Validator($data);
        $errors = $validator->validate([
            'title' => ['required', 'maxLength:255'],
            'slug'  => ['required', 'unique:pages,slug', 'maxLength:255']
        ]);

        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO pages (title, slug, content)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $data['title'],
                $data['slug'],
                $data['content'] ?? null
            ]);
            $pageId = (int)$this->db->lastInsertId();

            Helper::logAction($adminId, 'create_cms_page', 'pages', $pageId, null, $data);

            Helper::jsonResponse([
                'success' => true,
                'message' => 'CMS page created successfully.',
                'page_id' => $pageId
            ], 201);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to save page: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/admin/pages/{id}
     * Update static page details (Admin only)
     *
     * @param string $id Page ID
     */
    public function update(string $id): void {
        $user = AuthMiddleware::handle(true);
        $adminId = (int)$user['id'];
        $pageId = (int)$id;

        // Verify exists
        $stmtFind = $this->db->prepare("SELECT * FROM pages WHERE id = ?");
        $stmtFind->execute([$pageId]);
        $page = $stmtFind->fetch();

        if (!$page) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Page not found.'
            ], 404);
        }

        $data = Helper::getRequestBody();
        if (!empty($data['slug'])) {
            $data['slug'] = Helper::slugify($data['slug']);
        }

        $validator = new Validator($data);
        $rules = [];
        if (array_key_exists('title', $data)) $rules['title'] = ['required', 'maxLength:255'];
        if (array_key_exists('slug', $data)) $rules['slug'] = ['required', "unique:pages,slug,$pageId", 'maxLength:255'];

        $errors = $validator->validate($rules);
        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        try {
            $fields = [];
            $params = [];
            foreach (['title', 'slug', 'content'] as $key) {
                if (array_key_exists($key, $data)) {
                    $fields[] = "$key = ?";
                    $params[] = $data[$key];
                }
            }

            if (!empty($fields)) {
                $params[] = $pageId;
                $stmt = $this->db->prepare("UPDATE pages SET " . implode(", ", $fields) . " WHERE id = ?");
                $stmt->execute($params);

                Helper::logAction($adminId, 'update_cms_page', 'pages', $pageId, $page, $data);
            }

            Helper::jsonResponse([
                'success' => true,
                'message' => 'CMS page updated successfully.'
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to update page: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/admin/pages/{id}
     * Delete page (Admin only)
     *
     * @param string $id Page ID
     */
    public function destroy(string $id): void {
        $user = AuthMiddleware::handle(true);
        $adminId = (int)$user['id'];
        $pageId = (int)$id;

        // Verify exists
        $stmtFind = $this->db->prepare("SELECT * FROM pages WHERE id = ?");
        $stmtFind->execute([$pageId]);
        $page = $stmtFind->fetch();

        if (!$page) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Page not found.'
            ], 404);
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM pages WHERE id = ?");
            $stmt->execute([$pageId]);

            Helper::logAction($adminId, 'delete_cms_page', 'pages', $pageId, $page, null);

            Helper::jsonResponse([
                'success' => true,
                'message' => 'CMS page deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to delete page: ' . $e->getMessage()
            ], 500);
        }
    }
}
