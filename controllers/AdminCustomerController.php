<?php
/**
 * Admin Customer Management Controller
 */

class AdminCustomerController {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * GET /api/admin/customers
     * List all customers (Admin only)
     */
    public function index(): void {
        AuthMiddleware::handle(true);

        $search = $_GET['search'] ?? '';

        try {
            $sql = "
                SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.status, u.created_at,
                       COUNT(o.id) as total_orders,
                       COALESCE(SUM(o.total), 0) as total_spent
                FROM users u
                LEFT JOIN orders o ON u.id = o.user_id AND o.order_status != 'cancelled'
                WHERE u.role_id = 2
            ";

            $params = [];
            if (!empty($search)) {
                $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
                $searchVal = "%$search%";
                $params = [$searchVal, $searchVal, $searchVal, $searchVal];
            }

            $sql .= " GROUP BY u.id ORDER BY u.id DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $customers = $stmt->fetchAll();

            Helper::jsonResponse([
                'success' => true,
                'data' => $customers
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve customers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/admin/customers/{id}
     * Customer details + addresses + orders list (Admin only)
     *
     * @param string $id Customer User ID
     */
    public function show(string $id): void {
        AuthMiddleware::handle(true);
        $userId = (int)$id;

        try {
            // Find user profile
            $stmtUser = $this->db->prepare("SELECT id, first_name, last_name, email, phone, status, created_at FROM users WHERE id = ? AND role_id = 2 LIMIT 1");
            $stmtUser->execute([$userId]);
            $user = $stmtUser->fetch();

            if (!$user) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Customer not found.'
                ], 404);
            }

            // Find user addresses
            $stmtAddr = $this->db->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
            $stmtAddr->execute([$userId]);
            $addresses = $stmtAddr->fetchAll();

            // Find user orders
            $stmtOrders = $this->db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
            $stmtOrders->execute([$userId]);
            $orders = $stmtOrders->fetchAll();

            Helper::jsonResponse([
                'success' => true,
                'data' => [
                    'profile'   => $user,
                    'addresses' => $addresses,
                    'orders'    => $orders
                ]
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch customer profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PATCH /api/admin/customers/{id}/status
     * Ban, block or activate user status (Admin only)
     *
     * @param string $id Customer User ID
     */
    public function updateStatus(string $id): void {
        $admin = AuthMiddleware::handle(true);
        $adminId = (int)$admin['id'];
        $userId = (int)$id;

        $data = Helper::getRequestBody();
        if (empty($data['status']) || !in_array($data['status'], ['active', 'inactive', 'banned'])) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Status field is required and must be active, inactive, or banned.'
            ], 422);
        }

        $status = trim($data['status']);

        try {
            // Verify customer exists
            $stmtCheck = $this->db->prepare("SELECT * FROM users WHERE id = ? AND role_id = 2 LIMIT 1");
            $stmtCheck->execute([$userId]);
            $user = $stmtCheck->fetch();

            if (!$user) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Customer not found.'
                ], 404);
            }

            // Update status
            $stmt = $this->db->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$status, $userId]);

            // If user is banned/blocked, optionally clear their active sessions to force log out
            if ($status === 'banned' || $status === 'inactive') {
                $stmtSessions = $this->db->prepare("DELETE FROM user_sessions WHERE user_id = ?");
                $stmtSessions->execute([$userId]);
            }

            // Log action in audit logs
            Helper::logAction(
                $adminId,
                'change_customer_status',
                'users',
                $userId,
                ['status' => $user['status']],
                ['status' => $status]
            );

            Helper::jsonResponse([
                'success' => true,
                'message' => "Customer status changed to '$status'."
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to update customer status: ' . $e->getMessage()
            ], 500);
        }
    }
}
