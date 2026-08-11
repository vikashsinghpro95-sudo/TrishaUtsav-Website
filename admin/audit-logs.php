<?php
// admin/audit-logs.php
include_once __DIR__ . '/includes/admin-header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">System Audit Logs</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Track administrative actions, system events, and security access across the platform.</p>
    </div>
</div>

<div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col overflow-hidden">
    <!-- Toolbar -->
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex flex-wrap gap-4 items-center justify-between">
        <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
            <i class="ph ph-list-magnifying-glass mr-2 text-slate-400"></i> Event Logs
        </h3>

        <!-- Search and filters -->
        <div class="flex items-center space-x-3 w-full sm:w-auto">
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="ph ph-funnel text-slate-400"></i>
                </div>
                <select id="log-action-filter" class="block w-full pl-10 pr-10 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors appearance-none cursor-pointer">
                    <option value="">All Event Types</option>
                    <option value="create_product">Create Product</option>
                    <option value="update_product">Update Product</option>
                    <option value="delete_product">Delete Product</option>
                    <option value="change_customer_status">Block/Unblock Customer</option>
                    <option value="update_store_settings">Update Settings</option>
                    <option value="create_banner">Create Banner</option>
                    <option value="update_banner">Update Banner</option>
                    <option value="delete_banner">Delete Banner</option>
                    <option value="create_cms_page">Create CMS Page</option>
                    <option value="update_cms_page">Update CMS Page</option>
                    <option value="delete_cms_page">Delete CMS Page</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <i class="ph ph-caret-down text-slate-400"></i>
                </div>
            </div>

            <button onclick="AuditLogs.applyFilters()" class="inline-flex items-center justify-center px-4 py-2 border border-slate-300 dark:border-slate-700 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors whitespace-nowrap">
                Apply Filter
            </button>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="overflow-x-auto min-h-[400px]">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
            <thead class="bg-slate-50 dark:bg-slate-900/50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Operator</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action Event</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:table-cell">Target Entity</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">Details / Changes</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden lg:table-cell">IP Address</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date & Time</th>
                </tr>
            </thead>
            <tbody id="audit-logs-tbody" class="bg-white dark:bg-slate-850 divide-y divide-slate-200 dark:divide-slate-800">
                <!-- Loaded dynamically -->
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-2"></div>
                        <span class="block text-sm text-slate-500 dark:text-slate-400">Loading audit trail...</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
        <div id="audit-logs-pagination" class="flex justify-between items-center w-full">
            <!-- Loaded dynamically -->
        </div>
    </div>
</div>

<script src="/admin/assets/js/audit-logs.js?v=<?php echo time(); ?>"></script>

<?php
include_once __DIR__ . '/includes/admin-footer.php';
?>
