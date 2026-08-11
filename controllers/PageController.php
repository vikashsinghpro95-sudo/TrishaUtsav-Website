<?php
/**
 * Public Page Controller
 */

class PageController {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * GET /api/pages/{slug}
     * Retrieve static page details by slug
     *
     * @param string $slug
     */
    public function show(string $slug): void {
        try {
            $stmt = $this->db->prepare("SELECT * FROM pages WHERE slug = ? LIMIT 1");
            $stmt->execute([$slug]);
            $page = $stmt->fetch();

            if (!$page) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'CMS Page not found.'
                ], 404);
            }

            Helper::jsonResponse([
                'success' => true,
                'data' => $page
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch CMS page: ' . $e->getMessage()
            ], 500);
        }
    }
}
