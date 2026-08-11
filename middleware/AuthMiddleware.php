<?php
/**
 * Authentication Middleware
 */

class AuthMiddleware {
    /**
     * Intercept and authenticate incoming request.
     * Halts request with JSON response if unauthorized or forbidden.
     *
     * @param bool $requireAdmin If true, restricts access to users with role_id = 1
     * @return array The authenticated user profile (excluding password)
     */
    public static function handle(bool $requireAdmin = false): array {
        $headers = self::getHeaders();
        $token = null;

        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s(\S+)/i', $authHeader, $matches)) {
                $token = $matches[1];
            } else {
                $token = trim($authHeader);
            }
        }

        if (!$token) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Unauthorized. Authorization token is missing.'
            ], 401);
        }

        try {
            $db = Database::getInstance();
            
            // Get user session details
            $stmt = $db->prepare("
                SELECT u.*, us.token, us.expires_at 
                FROM user_sessions us
                INNER JOIN users u ON us.user_id = u.id
                WHERE us.token = ? AND us.expires_at > NOW()
                LIMIT 1
            ");
            $stmt->execute([$token]);
            $userSession = $stmt->fetch();

            if (!$userSession) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Unauthorized. Token is invalid or expired.'
                ], 401);
            }

            if ($userSession['status'] !== 'active') {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Forbidden. User account is ' . $userSession['status'] . '.'
                ], 403);
            }

            // Security: remove password hash
            unset($userSession['password']);

            // Inject current session token for logout/audit logs convenience
            $userSession['active_token'] = $token;

            // Set global auth user context
            Auth::setCurrentUser($userSession);

            // Enforce role restriction if requested
            if ($requireAdmin && !Auth::isAdmin()) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Forbidden. Admin credentials required.'
                ], 403);
            }

            return $userSession;

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Authentication server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retrieve all HTTP request headers across server environments
     *
     * @return array
     */
    private static function getHeaders(): array {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }
        
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (strpos($name, 'HTTP_') === 0) {
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$headerName] = $value;
            } elseif ($name === 'CONTENT_TYPE') {
                $headers['Content-Type'] = $value;
            } elseif ($name === 'CONTENT_LENGTH') {
                $headers['Content-Length'] = $value;
            }
        }
        return $headers;
    }
}
