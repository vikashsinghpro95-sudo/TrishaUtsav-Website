<?php
/**
 * Auth Helper Class
 */

class Auth {
    private static ?array $currentUser = null;

    /**
     * Set the currently authenticated user
     *
     * @param array $user
     */
    public static function setCurrentUser(array $user): void {
        self::$currentUser = $user;
    }

    /**
     * Get the currently authenticated user
     *
     * @return array|null
     */
    public static function getCurrentUser(): ?array {
        return self::$currentUser;
    }

    /**
     * Check if the current user has admin role (role_id = 1)
     *
     * @return bool
     */
    public static function isAdmin(): bool {
        return self::$currentUser !== null && (int)self::$currentUser['role_id'] === 1;
    }

    /**
     * Authenticate user credentials and return a session token
     *
     * @param string $email
     * @param string $password
     * @return string|null Token if successful, null on invalid credentials
     * @throws Exception
     */
    public static function login(string $email, string $password): ?string {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return null;
        }

        if ($user['status'] !== 'active') {
            throw new Exception("Your account is " . $user['status'] . ". Access denied.");
        }

        if (!password_verify($password, $user['password'])) {
            return null;
        }

        // Generate security token
        $token = bin2hex(random_bytes(32));

        $stmt = $db->prepare("INSERT INTO user_sessions (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))");
        $stmt->execute([$user['id'], $token]);

        return $token;
    }

    /**
     * Create session token for a given user ID directly
     *
     * @param int $userId
     * @return string
     */
    public static function createSessionForUser(int $userId): string {
        $db = Database::getInstance();
        $token = bin2hex(random_bytes(32));
        $stmt = $db->prepare("INSERT INTO user_sessions (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))");
        $stmt->execute([$userId, $token]);
        return $token;
    }

    /**
     * Register a new customer user and return session token
     *
     * @param array $data Registration input
     * @return string Session token for the newly created user
     */
    public static function register(array $data): string {
        $db = Database::getInstance();

        $roleId = 2; // Customer default
        $status = 'active';
        $isVerified = 0;
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt = $db->prepare("
            INSERT INTO users (role_id, first_name, last_name, email, password, phone, avatar, is_verified, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $roleId,
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $hashedPassword,
            $data['phone'] ?? null,
            $data['avatar'] ?? null,
            $isVerified,
            $status
        ]);

        $userId = $db->lastInsertId();

        // Auto-login user by creating session token
        $token = bin2hex(random_bytes(32));

        $stmt = $db->prepare("INSERT INTO user_sessions (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))");
        $stmt->execute([$userId, $token]);

        return $token;
    }

    /**
     * Revoke and destroy session token
     *
     * @param string $token
     * @return bool True if session was deleted
     */
    public static function logout(string $token): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM user_sessions WHERE token = ?");
        $stmt->execute([$token]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Create a password reset token for a valid active user email
     *
     * @param string $email
     * @return string|null Token if email exists, null otherwise
     */
    public static function requestPasswordReset(string $email): ?string {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id, status FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || $user['status'] !== 'active') {
            return null;
        }

        // Invalidate previous tokens
        $db->prepare("DELETE FROM user_password_resets WHERE email = ?")->execute([$email]);

        // Generate new token valid for 1 hour
        $token = bin2hex(random_bytes(32));

        $stmtInsert = $db->prepare("INSERT INTO user_password_resets (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
        $stmtInsert->execute([$email, $token]);

        return $token;
    }

    /**
     * Reset password using a valid reset token
     *
     * @param string $token
     * @param string $newPassword
     * @return bool
     * @throws Exception
     */
    public static function resetPasswordWithToken(string $token, string $newPassword): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT email FROM user_password_resets WHERE token = ? AND expires_at > NOW() LIMIT 1");
        $stmt->execute([$token]);
        $resetRequest = $stmt->fetch();

        if (!$resetRequest) {
            throw new Exception("Invalid or expired password reset token.");
        }

        $email = $resetRequest['email'];
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmtUpdate = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmtUpdate->execute([$hashedPassword, $email]);

        // Clean up reset token
        $db->prepare("DELETE FROM user_password_resets WHERE email = ?")->execute([$email]);

        return true;
    }
}
