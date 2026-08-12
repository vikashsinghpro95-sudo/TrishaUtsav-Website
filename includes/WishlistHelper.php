<?php
/**
 * Wishlist Helper for PHP Layouts and Header State
 */

class WishlistHelper {

    /**
     * Get active Wishlist product IDs for currently authenticated user or guest session
     *
     * @return array Array of integer Product IDs
     */
    public static function getWishlistProductIds(): array {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        // 1. Check if user token/session exists
        $user = Auth::getCurrentUser();
        
        // If not in Auth static property, check Authorization Bearer token header or session
        if (!$user && function_exists('getallheaders')) {
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
            if ($authHeader && preg_match('/Bearer\s(\S+)/i', $authHeader, $matches)) {
                $token = $matches[1];
                try {
                    $db = Database::getInstance();
                    $stmt = $db->prepare("
                        SELECT u.* FROM user_sessions us
                        INNER JOIN users u ON us.user_id = u.id
                        WHERE us.token = ? AND us.expires_at > NOW() AND u.status = 'active'
                        LIMIT 1
                    ");
                    $stmt->execute([$token]);
                    $user = $stmt->fetch() ?: null;
                } catch (Exception $e) {}
            }
        }

        if ($user && !empty($user['id'])) {
            try {
                $wishlistModel = new Wishlist();
                return $wishlistModel->getWishlistProductIds((int)$user['id']);
            } catch (Exception $e) {
                return [];
            }
        }

        // 2. Guest User: Return IDs stored in session
        if (!isset($_SESSION['wishlist']) || !is_array($_SESSION['wishlist'])) {
            $_SESSION['wishlist'] = [];
        }

        return array_values(array_map('intval', $_SESSION['wishlist']));
    }
}
