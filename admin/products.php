<?php
// admin/products.php
include_once __DIR__ . '/includes/admin-header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Products</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage your store's inventory and product details.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="/admin/product-edit" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors shadow-sm">
            <i class="ph ph-plus mr-2 text-lg"></i> Add Product
        </a>
    </div>
</div>

<div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col overflow-hidden">
    
    <!-- Toolbar -->
    <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex flex-col lg:flex-row lg:items-center gap-4 justify-between">
        <div class="flex-1 max-w-lg w-full relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="ph ph-magnifying-glass text-slate-400"></i>
            </div>
            <input type="text" id="prod-search" placeholder="Search products by name, SKU..." class="block w-full pl-10 pr-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg leading-5 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
        </div>
        
        <div class="flex items-center gap-3 flex-wrap">
            <select id="prod-category-filter" class="block w-full sm:w-auto pl-3 pr-10 py-2 text-base border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-lg transition-colors cursor-pointer appearance-none">
                <option value="">All Categories</option>
            </select>

            <select id="prod-status-filter" class="block w-full sm:w-auto pl-3 pr-10 py-2 text-base border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-lg transition-colors cursor-pointer appearance-none">
                <option value="">All Statuses</option>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
                <option value="archived">Archived</option>
            </select>

            <button onclick="Products.applyFilters()" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors shadow-sm">
                <i class="ph ph-funnel mr-2"></i> Filter
            </button>
        </div>
    </div>

    <!-- Bulk Actions Bar (Hidden by default) -->
    <div id="bulk-actions-bar" class="hidden px-4 py-3 bg-primary-50 dark:bg-primary-900/20 border-b border-primary-100 dark:border-primary-900/50 flex items-center justify-between transition-all">
        <span class="text-sm font-medium text-primary-700 dark:text-primary-300"><span id="selected-count">0</span> products selected</span>
        <div class="flex gap-2">
            <button class="px-3 py-1.5 text-xs font-medium text-red-700 bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50 rounded transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1">
                Delete Selected
            </button>
        </div>
    </div>

    <!-- Products Table -->
    <div class="overflow-x-auto min-h-[400px]">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 table-fixed">
            <thead class="bg-slate-50 dark:bg-slate-900/50">
                <tr>
                    <th scope="col" class="w-12 px-6 py-3 text-left">
                        <input type="checkbox" id="select-all" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-slate-300 rounded cursor-pointer dark:bg-slate-800 dark:border-slate-600 dark:checked:bg-primary-500">
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Product</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">Category</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Price & Stock</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24">Actions</th>
                </tr>
            </thead>
            <tbody id="products-tbody" class="bg-white dark:bg-slate-850 divide-y divide-slate-200 dark:divide-slate-800">
                <!-- Loaded dynamically -->
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex items-center justify-between sm:px-6">
        <div id="products-pagination" class="w-full flex justify-between sm:justify-end items-center gap-2">
            <!-- Loaded dynamically -->
        </div>
    </div>
</div>

<script src="/admin/assets/js/products.js?v=<?php echo time(); ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Products.initList();

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
        setTimeout(styleSelects, 500); // Apply after load
    });
</script>

<?php
include_once __DIR__ . '/includes/admin-footer.php';
?>
