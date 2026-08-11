<?php
include_once __DIR__ . '/includes/admin-header.php';
?>

<!-- KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
    <!-- Sales -->
    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Total Sales</span>
            <span id="kpi-sales" class="text-xl font-black text-gray-800 mt-1 block">₹0.00</span>
        </div>
        <div class="w-10 h-10 bg-primary-50 text-primary-600 rounded-lg flex items-center justify-center text-sm">
            <i class="fas fa-coins"></i>
        </div>
    </div>

    <!-- Today Sales -->
    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Today's Revenue</span>
            <span id="kpi-today-sales" class="text-xl font-black text-gray-800 mt-1 block">₹0.00</span>
        </div>
        <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center text-sm">
            <i class="fas fa-calendar-day"></i>
        </div>
    </div>

    <!-- Total Orders -->
    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Orders</span>
            <span id="kpi-orders" class="text-xl font-black text-gray-800 mt-1 block">0</span>
        </div>
        <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-sm">
            <i class="fas fa-shopping-bag"></i>
        </div>
    </div>

    <!-- Pending Orders -->
    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Pending</span>
            <span id="kpi-pending" class="text-xl font-black text-gray-800 mt-1 block">0</span>
        </div>
        <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center text-sm">
            <i class="fas fa-clock"></i>
        </div>
    </div>

    <!-- Total Customers -->
    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Customers</span>
            <span id="kpi-customers" class="text-xl font-black text-gray-800 mt-1 block">0</span>
        </div>
        <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center text-sm">
            <i class="fas fa-users"></i>
        </div>
    </div>

    <!-- Low Stock warning -->
    <div id="kpi-low-stock-card" class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between transition-colors">
        <div>
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Low Stock</span>
            <span id="kpi-low-stock" class="text-xl font-black text-gray-800 mt-1 block">0</span>
        </div>
        <div class="w-10 h-10 bg-red-50 text-red-600 rounded-lg flex items-center justify-center text-sm">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
    </div>
</div>

<!-- Chart and Recent Orders Grid -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    <!-- Sales Chart -->
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm xl:col-span-2 space-y-4">
        <h3 class="text-sm font-bold text-gray-700 pb-3 border-b border-gray-100 uppercase tracking-wider">
            <i class="fas fa-chart-area text-primary-500 mr-2"></i> Monthly Sales Trend
        </h3>
        <div class="relative h-80">
            <canvas id="salesTrendChart" class="w-full h-full"></canvas>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm xl:col-span-1 space-y-4">
        <div class="flex justify-between items-center pb-3 border-b border-gray-100">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">
                <i class="fas fa-history text-primary-500 mr-2"></i> Recent Orders
            </h3>
            <a href="/admin/orders.php" class="text-xs text-primary-600 font-bold hover:underline">View All</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-xs text-left text-gray-500">
                <tbody id="recent-orders-tbody" class="divide-y divide-gray-100">
                    <!-- Loaded dynamically -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Dashboard script -->
<script src="/admin/assets/js/dashboard.js"></script>

<?php
include_once __DIR__ . '/includes/admin-footer.php';
?>
