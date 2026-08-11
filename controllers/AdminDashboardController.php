<?php
/**
 * Admin Dashboard Controller
 */

class AdminDashboardController {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * GET /api/admin/dashboard
     * Fetch KPI metrics and recent transactions (Admin only)
     */
    public function index(): void {
        AuthMiddleware::handle(true); // Admin auth

        try {
            // 1. Total Sales
            $stmtSales = $this->db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE order_status != 'cancelled' AND payment_status = 'paid'");
            $totalSales = (float)$stmtSales->fetchColumn();

            // 2. Today's Sales
            $stmtTodaySales = $this->db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE order_status != 'cancelled' AND payment_status = 'paid' AND DATE(created_at) = CURDATE()");
            $todaySales = (float)$stmtTodaySales->fetchColumn();

            // 3. Total Orders
            $stmtTotalOrders = $this->db->query("SELECT COUNT(*) FROM orders");
            $totalOrders = (int)$stmtTotalOrders->fetchColumn();

            // 4. Pending Orders
            $stmtPending = $this->db->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'");
            $pendingOrders = (int)$stmtPending->fetchColumn();

            // 5. Total Customers
            $stmtCustomers = $this->db->query("SELECT COUNT(*) FROM users WHERE role_id = 2");
            $totalCustomers = (int)$stmtCustomers->fetchColumn();

            // 6. Low Stock Products
            $stmtLowStock = $this->db->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= low_stock_threshold");
            $lowStockProducts = (int)$stmtLowStock->fetchColumn();

            // 7. Recent Orders
            $stmtRecent = $this->db->query("
                SELECT o.*, u.first_name, u.last_name 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                ORDER BY o.id DESC 
                LIMIT 5
            ");
            $recentOrders = $stmtRecent->fetchAll();

            // 8. Sales Trend (Sales Over Time)
            $stmtTrend = $this->db->query("
                SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COALESCE(SUM(total), 0) as sales 
                FROM orders 
                WHERE order_status != 'cancelled' AND payment_status = 'paid' 
                GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
                ORDER BY month ASC 
                LIMIT 12
            ");
            $salesOverTime = $stmtTrend->fetchAll();

            Helper::jsonResponse([
                'success' => true,
                'data' => [
                    'total_sales'        => $totalSales,
                    'today_sales'        => $todaySales,
                    'total_orders'       => $totalOrders,
                    'pending_orders'     => $pendingOrders,
                    'total_customers'    => $totalCustomers,
                    'low_stock_products' => $lowStockProducts,
                    'recent_orders'      => $recentOrders,
                    'sales_over_time'    => $salesOverTime
                ]
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve dashboard metrics: ' . $e->getMessage()
            ], 500);
        }
    }
}
