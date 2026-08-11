<?php
/**
 * Public Settings Controller
 */

class SettingsController {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * GET /api/settings
     * Fetch public configuration variables
     */
    public function show(): void {
        try {
            $stmt = $this->db->query("SELECT setting_key, setting_value FROM settings");
            $rows = $stmt->fetchAll();

            $publicKeys = [
                'store_name', 'contact_email', 'contact_phone', 'currency', 'store_logo',
                'hero_bg_type', 'hero_image_url', 'hero_video_url', 'hero_headline', 'hero_description',
                'hero_cta_text', 'hero_cta_link', 'hero_overlay_opacity', 'hero_overlay_color',
                'hero_bestseller_product_id',
                'hero_hangings_enabled', 'hero_hangings_type', 'hero_hangings_count', 'hero_hangings_gravity',
                'hero_mobile_bg_type', 'hero_mobile_image_url', 'hero_mobile_video_url', 'hero_mobile_height',
                'footer_about_text', 'footer_address', 'footer_operating_hours',
                'footer_social_instagram', 'footer_social_facebook', 'footer_social_whatsapp', 'footer_social_youtube',
                'footer_decorations_enabled', 'footer_copyright_text',
                'timer_section_enabled', 'timer_badge_text', 'timer_headline', 'timer_description',
                'timer_target_date', 'timer_cta_text', 'timer_cta_link'
            ];
            $settings = [];
            
            foreach ($rows as $row) {
                if (in_array($row['setting_key'], $publicKeys)) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                }
            }

            Helper::jsonResponse([
                'success' => true,
                'data' => $settings
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to load public settings: ' . $e->getMessage()
            ], 500);
        }
    }
}
