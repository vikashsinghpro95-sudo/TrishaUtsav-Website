<?php
/**
 * Admin Audit Logs Controller
 */

class AdminAuditLogsController {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * GET /api/admin/audit-logs
     * Fetch paginated system activity audit logs (Admin only)
     */
    public function index(): void {
        AuthMiddleware::handle(true);

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 15;
        if ($page < 1) $page = 1;
        if ($perPage < 1) $perPage = 15;
        if ($perPage > 100) $perPage = 100;
        $offset = ($page - 1) * $perPage;

        $actionFilter = $_GET['action'] ?? '';
        $userIdFilter = $_GET['user_id'] ?? '';

        try {
            // Count total matching logs
            $countSql = "SELECT COUNT(*) FROM audit_logs a JOIN users u ON a.user_id = u.id WHERE 1=1";
            $params = [];

            if (!empty($actionFilter)) {
                $countSql .= " AND a.action = ?";
                $params[] = $actionFilter;
            }
            if (!empty($userIdFilter)) {
                $countSql .= " AND a.user_id = ?";
                $params[] = (int)$userIdFilter;
            }

            $stmtCount = $this->db->prepare($countSql);
            $stmtCount->execute($params);
            $totalItems = (int)$stmtCount->fetchColumn();
            $totalPages = ceil($totalItems / $perPage);

            // Fetch records
            $dataSql = "
                SELECT a.*, u.first_name, u.last_name, u.email 
                FROM audit_logs a 
                JOIN users u ON a.user_id = u.id 
                WHERE 1=1
            ";

            if (!empty($actionFilter)) {
                $dataSql .= " AND a.action = ?";
            }
            if (!empty($userIdFilter)) {
                $dataSql .= " AND a.user_id = ?";
            }

            $dataSql .= " ORDER BY a.id DESC LIMIT $perPage OFFSET $offset";

            $stmtData = $this->db->prepare($dataSql);
            $stmtData->execute($params);
            $logs = $stmtData->fetchAll();

            // Format json values
            foreach ($logs as &$log) {
                $log['old_value'] = $log['old_value'] ? json_decode($log['old_value'], true) : null;
                $log['new_value'] = $log['new_value'] ? json_decode($log['new_value'], true) : null;
            }
            unset($log);

            Helper::jsonResponse([
                'success' => true,
                'data' => $logs,
                'pagination' => [
                    'current_page' => $page,
                    'per_page'     => $perPage,
                    'total_pages'  => $totalPages,
                    'total_items'  => $totalItems
                ]
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve audit logs: ' . $e->getMessage()
            ], 500);
        }
    }
}
