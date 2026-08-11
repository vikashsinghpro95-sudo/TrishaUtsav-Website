<?php
// admin/customer-detail.php
include_once __DIR__ . '/includes/admin-header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <div class="flex items-center gap-2 mb-2">
            <a href="/admin/customers.php" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                <i class="ph ph-arrow-left text-lg"></i>
            </a>
            <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Back to Customers</span>
        </div>
        <div class="flex items-center gap-3">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center">
                <span id="cust-name">Loading Profile...</span>
            </h2>
            <span id="cust-status-badge" class="px-2.5 py-1 rounded-md font-medium text-xs bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300">LOADING</span>
        </div>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Customer since <span id="cust-joined" class="font-medium text-slate-700 dark:text-slate-300">Loading...</span></p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 lg:gap-8 pb-12">
    <!-- Main Content Column -->
    <div class="xl:col-span-2 space-y-6">
        
        <!-- Address book -->
        <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-map-pin mr-2 text-slate-400"></i> Address Book
                </h3>
            </div>
            <div class="p-5">
                <div id="addresses-container">
                    <!-- Loaded dynamically -->
                </div>
            </div>
        </div>

        <!-- Orders log -->
        <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-shopping-cart mr-2 text-slate-400"></i> Purchase History
                </h3>
            </div>

            <div class="overflow-x-auto min-h-[300px]">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-900/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Order</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-20">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="orders-tbody" class="bg-white dark:bg-slate-850 divide-y divide-slate-200 dark:divide-slate-800">
                        <!-- Loaded dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sidebar / Fulfillment Column -->
    <div class="xl:col-span-1 space-y-6">
        
        <!-- Account details card -->
        <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-identification-card mr-2 text-slate-400"></i> Contact Details
                </h3>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1">Email Address</span>
                    <div class="flex items-center">
                        <i class="ph ph-envelope-simple text-slate-400 mr-2 text-lg"></i>
                        <span id="cust-email" class="text-sm font-medium text-slate-900 dark:text-white truncate">Loading...</span>
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1">Phone Number</span>
                    <div class="flex items-center">
                        <i class="ph ph-phone text-slate-400 mr-2 text-lg"></i>
                        <span id="cust-phone" class="text-sm font-medium text-slate-900 dark:text-white">Loading...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Administrative Controls -->
        <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden border-t-4 border-t-red-500">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-base font-semibold text-red-600 dark:text-red-400 flex items-center">
                    <i class="ph ph-shield-warning mr-2"></i> Administrative Action
                </h3>
            </div>
            <div class="p-5">
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">
                    Banning a user immediately revokes session access and prevents them from signing in, placing orders, or editing shopping carts.
                </p>
                <button id="btn-cust-status-action" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                    Toggle Status
                </button>
            </div>
        </div>

    </div>
</div>

<script src="/admin/assets/js/customers.js?v=<?php echo time(); ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Customers.initDetail();
    });
</script>

<?php
include_once __DIR__ . '/includes/admin-footer.php';
?>
