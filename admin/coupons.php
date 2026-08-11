<?php
// admin/coupons.php
include_once __DIR__ . '/includes/admin-header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Coupons</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage promotional discount codes and offers.</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 lg:gap-8">
    
    <!-- Coupons List (Left side) -->
    <div class="xl:col-span-2 bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                <i class="ph ph-ticket mr-2 text-slate-400"></i> Active Promotions
            </h3>
        </div>

        <div class="overflow-x-auto min-h-[400px]">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-900/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Coupon Code</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:table-cell">Offer</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">Usage/Expiry</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24">Actions</th>
                    </tr>
                </thead>
                <tbody id="coupons-tbody" class="bg-white dark:bg-slate-850 divide-y divide-slate-200 dark:divide-slate-800">
                    <!-- Loaded dynamically -->
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-2"></div>
                            <span class="block text-sm text-slate-500 dark:text-slate-400">Loading coupons...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Coupon Form (Right side) -->
    <div class="xl:col-span-1">
        <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden sticky top-24">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
                <h3 id="coupon-form-title" class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-plus-circle mr-2 text-slate-400"></i> Create Coupon
                </h3>
                <button id="btn-cancel-edit-coupon" onclick="Coupons.resetForm()" class="hidden text-xs font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 px-2.5 py-1 rounded transition-colors">
                    Cancel
                </button>
            </div>

            <form id="frm-coupon-save" class="p-5 space-y-4">
                <div>
                    <label for="coupon-code" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Coupon Code</label>
                    <input type="text" id="coupon-code" required placeholder="e.g. SUMMER20" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors uppercase font-mono">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="coupon-type" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Type</label>
                        <select id="coupon-type" required class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors cursor-pointer">
                            <option value="percentage">Percentage Off</option>
                            <option value="fixed">Fixed Amount</option>
                            <option value="free_shipping">Free Shipping</option>
                        </select>
                    </div>
                    <div>
                        <label for="coupon-value" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Value</label>
                        <input type="number" step="0.01" id="coupon-value" required placeholder="0.00" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="coupon-min-val" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Min Spend (₹)</label>
                        <input type="number" step="0.01" id="coupon-min-val" value="0.00" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                    </div>
                    <div>
                        <label for="coupon-max-disc" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Max Discount (₹)</label>
                        <input type="number" step="0.01" id="coupon-max-disc" placeholder="No limit" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="coupon-limit" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Usage Limit</label>
                        <input type="number" id="coupon-limit" placeholder="Unlimited" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                    </div>
                    <div>
                        <label for="coupon-expiry" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Expiry Date</label>
                        <input type="date" id="coupon-expiry" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors cursor-pointer text-slate-500">
                    </div>
                </div>

                <div class="pt-2">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="coupon-active" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-primary-600"></div>
                        <span class="ml-3 text-sm font-medium text-slate-900 dark:text-slate-300">Status Active</span>
                    </label>
                </div>

                <div class="pt-2">
                    <button type="submit" id="btn-save-coupon" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                        Save Coupon
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/admin/assets/js/coupons.js?v=<?php echo time(); ?>"></script>

<?php
include_once __DIR__ . '/includes/admin-footer.php';
?>
