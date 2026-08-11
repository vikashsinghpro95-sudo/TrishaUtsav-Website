<?php
// admin/orders.php
include_once __DIR__ . '/includes/admin-header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Orders</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage and fulfill customer orders.</p>
    </div>
</div>

<div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col overflow-hidden">
    
    <!-- Toolbar -->
    <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex flex-col lg:flex-row lg:items-center gap-4 justify-between">
        <div class="flex-1 max-w-lg w-full relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="ph ph-magnifying-glass text-slate-400"></i>
            </div>
            <input type="text" id="order-search" placeholder="Search by order ID, customer name..." class="block w-full pl-10 pr-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg leading-5 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
        </div>
        
        <div class="flex items-center gap-3 flex-wrap">
            <select id="order-status-filter" class="block w-full sm:w-auto pl-3 pr-10 py-2 text-base border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-lg transition-colors cursor-pointer appearance-none">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="processing">Processing</option>
                <option value="packed">Packed</option>
                <option value="shipped">Shipped</option>
                <option value="out_for_delivery">Out For Delivery</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
                <option value="returned">Returned</option>
            </select>

            <button onclick="Orders.applyFilters()" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors shadow-sm">
                <i class="ph ph-funnel mr-2"></i> Filter
            </button>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="overflow-x-auto min-h-[400px]">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 table-fixed">
            <thead class="bg-slate-50 dark:bg-slate-900/50">
                <tr>
                    <th scope="col" class="w-12 px-6 py-3 text-left">
                        <input type="checkbox" id="select-all" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-slate-300 rounded cursor-pointer dark:bg-slate-800 dark:border-slate-600 dark:checked:bg-primary-500">
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Order</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">Customer</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date & Payment</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24">Actions</th>
                </tr>
            </thead>
            <tbody id="orders-tbody" class="bg-white dark:bg-slate-850 divide-y divide-slate-200 dark:divide-slate-800">
                <!-- Loaded dynamically -->
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-2"></div>
                        <span class="block text-sm text-slate-500 dark:text-slate-400">Loading orders...</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between sm:px-6">
        <div id="orders-pagination" class="w-full flex justify-between sm:justify-end items-center gap-2">
            <!-- Loaded dynamically -->
        </div>
    </div>
</div>

<script src="/admin/assets/js/orders.js?v=<?php echo time(); ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Orders.initList();

        // Custom styling for dynamically added selects
        const styleSelects = () => {
            const selects = document.querySelectorAll('select');
            selects.forEach(s => {
                s.style.backgroundImage = `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E")`;
                s.style.backgroundPosition = `right 0.5rem center`;
                s.style.backgroundRepeat = `no-repeat`;
                s.style.backgroundSize = `1.5em 1.5em`;
            });
        };
        setTimeout(styleSelects, 500);
    });
</script>

<?php
include_once __DIR__ . '/includes/admin-footer.php';
?>
