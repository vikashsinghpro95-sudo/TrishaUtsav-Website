<?php
/**
 * User Database Model
 */

class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find a user by primary key ID
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name 
            FROM users u 
            INNER JOIN roles r ON u.role_id = r.id 
            WHERE u.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find a user by email address
     *
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name 
            FROM users u 
            INNER JOIN roles r ON u.role_id = r.id 
            WHERE u.email = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Create a new user record
     *
     * @param array $data User details
     * @return int Inserted user ID
     */
    public function create(array $data): int {
        $roleId = $data['role_id'] ?? 2; // Customer default
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $status = $data['status'] ?? 'active';
        $isVerified = $data['is_verified'] ?? 0;

        $stmt = $this->db->prepare("
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

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing user record dynamically
     *
     * @param int $id
     * @param array $data Fields to update
     * @return bool True if successful
     */
    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [];

        $updatable = ['first_name', 'last_name', 'phone', 'avatar', 'status', 'role_id', 'is_verified', 'is_phone_verified'];
        foreach ($updatable as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "`$field` = ?";
                $params[] = $data[$field];
            }
        }

        if (!empty($data['password'])) {
            $fields[] = "`password` = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete a user record
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get list of all users
     *
     * @return array
     */
    public function all(): array {
        $stmt = $this->db->query("
            SELECT u.id, u.role_id, r.name as role_name, u.first_name, u.last_name, u.email, u.phone, u.avatar, u.is_verified, u.status, u.created_at
            FROM users u
            INNER JOIN roles r ON u.role_id = r.id
            ORDER BY u.id DESC
        ");
        return $stmt->fetchAll();
    }
}
