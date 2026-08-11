<?php
/**
 * Admin Settings Controller
 */

class AdminSettingsController {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * GET /api/admin/settings
     * Fetch store configuration settings (Admin only)
     */
    public function show(): void {
        AuthMiddleware::handle(true);

        try {
            $stmt = $this->db->query("SELECT setting_key, setting_value FROM settings");
            $rows = $stmt->fetchAll();

            // Transform into key-value map
            $settings = [];
            foreach ($rows as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }

            Helper::jsonResponse([
                'success' => true,
                'data' => $settings
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve store settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/admin/settings
     * Save/Update store configurations (Admin only)
     */
    public function update(): void {
        $user = AuthMiddleware::handle(true);
        $adminId = (int)$user['id'];

        $data = Helper::getRequestBody();

        if (empty($data) || !is_array($data)) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Invalid configuration payload.'
            ], 422);
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO settings (setting_key, setting_value)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");

            // Capture old settings values for audit log
            $stmtOld = $this->db->query("SELECT setting_key, setting_value FROM settings");
            $oldRows = $stmtOld->fetchAll();
            $oldSettings = [];
            foreach ($oldRows as $or) {
                $oldSettings[$or['setting_key']] = $or['setting_value'];
            }

            foreach ($data as $key => $val) {
                $stmt->execute([trim($key), trim($val)]);
            }

            // Log action in audit logs
            Helper::logAction(
                $adminId,
                'update_store_settings',
                'settings',
                null,
                $oldSettings,
                $data
            );

            $this->db->commit();

            Helper::jsonResponse([
                'success' => true,
                'message' => 'Configurations updated successfully.'
            ], 200);

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to update configurations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/admin/settings/upload
     * Upload an asset file (video/image/logo) for settings
     */
    public function upload(): void {
        AuthMiddleware::handle(true);

        if (!isset($_FILES['file'])) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'No file received for upload.'
            ], 400);
        }

        $type = $_POST['type'] ?? 'general'; // can be 'video', 'brand', 'banner', 'logo', 'occasion', 'general'
        
        try {
            if ($type === 'video' || $type === 'reel') {
                $allowed = [
                    'video/mp4'  => 'mp4',
                    'video/webm' => 'webm',
                    'video/ogg'  => 'ogv',
                    'video/quicktime' => 'mov'
                ];
                $path = Helper::uploadFile($_FILES['file'], 'videos', $allowed, 100000000); // 100MB limit
            } elseif ($type === 'brand') {
                $allowed = [
                    'image/jpeg' => 'jpg',
                    'image/jpg'  => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp',
                    'image/gif'  => 'gif',
                    'image/svg+xml' => 'svg',
                ];
                $path = Helper::uploadFile($_FILES['file'], 'brands', $allowed, 30000000); // 30MB limit
            } elseif ($type === 'banner' || $type === 'occasion' || $type === 'occasions') {
                $allowed = [
                    'image/jpeg' => 'jpg',
                    'image/jpg'  => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp',
                    'image/gif'  => 'gif',
                    'image/svg+xml' => 'svg',
                ];
                $path = Helper::uploadFile($_FILES['file'], 'occasions', $allowed, 30000000); // 30MB limit
            } else {
                // Image logo or visual asset
                $allowed = [
                    'image/jpeg' => 'jpg',
                    'image/jpg'  => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp',
                    'image/gif'  => 'gif',
                    'image/svg+xml' => 'svg',
                ];
                $path = Helper::uploadFile($_FILES['file'], 'settings', $allowed, 30000000); // 30MB limit
            }

            Helper::jsonResponse([
                'success' => true,
                'message' => 'Asset uploaded successfully.',
                'path' => $path
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/homepage/sections
     * Fetch ordered list of homepage sections for rendering
     */
    public function getHomepageSections(): void {
        try {
            $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'homepage_sections' LIMIT 1");
            $stmt->execute();
            $json = $stmt->fetchColumn();

            $sections = $json ? json_decode($json, true) : null;
            if (!$sections) {
                $sections = [
                    ["id" => "categories", "name" => "Shop By Category", "enabled" => true, "order" => 1],
                    ["id" => "trending", "name" => "Trending Products", "enabled" => true, "order" => 2],
                    ["id" => "occasions", "name" => "Shop By Occasion", "enabled" => true, "order" => 3],
                    ["id" => "must_buy", "name" => "Must Buy Selection", "enabled" => true, "order" => 4],
                    ["id" => "reels", "name" => "Insta Product Reels", "enabled" => true, "order" => 5],
                    ["id" => "mega_sale", "name" => "Festive Sale Countdown", "enabled" => true, "order" => 6]
                ];
            } else {
                $hasMegaSale = false;
                foreach ($sections as $sec) {
                    if (($sec['id'] ?? '') === 'mega_sale') {
                        $hasMegaSale = true;
                        break;
                    }
                }
                if (!$hasMegaSale) {
                    $maxOrder = max(array_column($sections, 'order') ?: [0]);
                    $sections[] = ["id" => "mega_sale", "name" => "Festive Sale Countdown", "enabled" => true, "order" => $maxOrder + 1];
                }
            }

            // Sort by order ascending
            usort($sections, fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

            Helper::jsonResponse([
                'success' => true,
                'data' => $sections
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch homepage sections: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/admin/homepage/sections
     * Update homepage section order and visibility (Admin only)
     */
    public function updateHomepageSections(): void {
        AuthMiddleware::handle(true);

        $data = Helper::getRequestBody();
        if (empty($data['sections']) || !is_array($data['sections'])) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Invalid sections configuration array.'
            ], 422);
        }

        try {
            $json = json_encode(array_values($data['sections']), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $stmt = $this->db->prepare("
                INSERT INTO settings (setting_key, setting_value)
                VALUES ('homepage_sections', ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            $stmt->execute([$json]);

            Helper::jsonResponse([
                'success' => true,
                'message' => 'Homepage layout sections updated successfully.'
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to update homepage layout: ' . $e->getMessage()
            ], 500);
        }
    }
}
