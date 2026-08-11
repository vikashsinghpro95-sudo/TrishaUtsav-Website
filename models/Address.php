<?php
/**
 * Address Database Model
 */

class Address {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find address by ID
     *
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM addresses WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $address = $stmt->fetch();
        return $address ?: null;
    }

    /**
     * Find all addresses belonging to a user
     *
     * @param int $userId
     * @return array
     */
    public function findByUser(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Create new address record
     *
     * @param array $data Address details
     * @return int Created address ID
     */
    public function create(array $data): int {
        try {
            $this->db->beginTransaction();

            $userId = (int)$data['user_id'];
            $type = $data['type'] ?? 'shipping';
            $isDefault = isset($data['is_default']) ? (int)$data['is_default'] : 0;

            // If this is set to default, or if it is the first address of this type, make it default
            $existingCount = $this->countType($userId, $type);
            if ($existingCount === 0) {
                $isDefault = 1;
            }

            $stmt = $this->db->prepare("
                INSERT INTO addresses (
                    user_id, type, is_default, full_name, phone, 
                    address_line1, address_line2, city, state, pincode, country
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $userId,
                $type,
                $isDefault,
                $data['full_name'],
                $data['phone'],
                $data['address_line1'],
                $data['address_line2'] ?? null,
                $data['city'],
                $data['state'],
                $data['pincode'],
                $data['country'] ?? 'India'
            ]);

            $addressId = (int)$this->db->lastInsertId();

            // If marked as default, unset other default flags of same type
            if ($isDefault === 1) {
                $this->unsetOtherDefaults($userId, $type, $addressId);
            }

            $this->db->commit();
            return $addressId;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Update an address record dynamically
     *
     * @param int $id
     * @param int $userId Required for authorization check
     * @param array $data Fields to update
     * @return bool
     */
    public function update(int $id, int $userId, array $data): bool {
        try {
            $this->db->beginTransaction();

            $fields = [];
            $params = [];

            $updatable = [
                'type', 'is_default', 'full_name', 'phone', 
                'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country'
            ];

            foreach ($updatable as $field) {
                if (array_key_exists($field, $data)) {
                    $fields[] = "`$field` = ?";
                    $params[] = $data[$field];
                }
            }

            if (empty($fields)) {
                $this->db->rollBack();
                return false;
            }

            $params[] = $id;
            $params[] = $userId; // Safety check

            $sql = "UPDATE addresses SET " . implode(', ', $fields) . " WHERE id = ? AND user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            // Fetch updated address details to check if is_default was enabled
            $address = $this->find($id);
            if ($address && (int)$address['is_default'] === 1) {
                $this->unsetOtherDefaults($userId, $address['type'], $id);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Delete address record
     *
     * @param int $id
     * @param int $userId Safety ownership validation
     * @return bool
     */
    public function delete(int $id, int $userId): bool {
        try {
            $this->db->beginTransaction();

            $address = $this->find($id);
            if (!$address || (int)$address['user_id'] !== $userId) {
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
            $deleted = $stmt->execute([$id, $userId]);

            // If the deleted address was default, make another address of same type the default
            if ((int)$address['is_default'] === 1) {
                $stmtNext = $this->db->prepare("SELECT id FROM addresses WHERE user_id = ? AND type = ? LIMIT 1");
                $stmtNext->execute([$userId, $address['type']]);
                $nextId = $stmtNext->fetchColumn();
                if ($nextId) {
                    $this->db->prepare("UPDATE addresses SET is_default = 1 WHERE id = ?")->execute([$nextId]);
                }
            }

            $this->db->commit();
            return $deleted;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Count the number of addresses of a specific type for a user
     *
     * @param int $userId
     * @param string $type
     * @return int
     */
    private function countType(int $userId, string $type): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM addresses WHERE user_id = ? AND type = ?");
        $stmt->execute([$userId, $type]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Remove the is_default flag from other addresses of same type
     *
     * @param int $userId
     * @param string $type
     * @param int $excludeId The address to keep as default
     */
    private function unsetOtherDefaults(int $userId, string $type, int $excludeId): void {
        $stmt = $this->db->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ? AND type = ? AND id != ?");
        $stmt->execute([$userId, $type, $excludeId]);
    }
}
