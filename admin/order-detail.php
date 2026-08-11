<?php
// admin/order-detail.php
include_once __DIR__ . '/includes/admin-header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <div class="flex items-center gap-2 mb-2">
            <a href="/admin/orders.php" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                <i class="ph ph-arrow-left text-lg"></i>
            </a>
            <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Back to Orders</span>
        </div>
        <div class="flex items-center gap-3">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center">
                Order <span id="ord-number" class="text-primary-600 ml-2">Loading...</span>
            </h2>
            <span id="ord-status-badge" class="px-2.5 py-1 rounded-md font-medium text-xs bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300">LOADING</span>
            <span id="ord-payment-badge" class="px-2.5 py-1 rounded-md font-medium text-xs bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300">LOADING</span>
        </div>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Placed on <span id="ord-date" class="font-medium text-slate-700 dark:text-slate-300">Loading...</span></p>
    </div>

    <!-- Actions -->
    <div class="flex items-center gap-3">
        <button onclick="Orders.refundOrder()" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors shadow-sm">
            <i class="ph ph-arrow-u-down-left mr-2"></i> Refund
        </button>
        <button onclick="Orders.cancelOrder()" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-red-700 bg-red-50 border border-transparent rounded-lg hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors shadow-sm">
            <i class="ph ph-x-circle mr-2"></i> Cancel Order
        </button>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 lg:gap-8 pb-12">
    <!-- Main Content Column -->
    <div class="xl:col-span-2 space-y-6">
        
        <!-- Customer & Addresses grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Shipping Address -->
            <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                        <i class="ph ph-truck mr-2 text-slate-400"></i> Shipping Address
                    </h3>
                </div>
                <div class="p-5">
                    <div id="shipping-address-box" class="space-y-1">
                        <span class="text-sm text-slate-500 dark:text-slate-400">Loading details...</span>
                    </div>
                </div>
            </div>

            <!-- Billing Address -->
            <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                        <i class="ph ph-file-text mr-2 text-slate-400"></i> Billing Address
                    </h3>
                </div>
                <div class="p-5">
                    <div id="billing-address-box" class="space-y-1">
                        <span class="text-sm text-slate-500 dark:text-slate-400">Loading details...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-package mr-2 text-slate-400"></i> Order Items
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-900/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Product</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Price</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Qty</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody id="order-items-tbody" class="bg-white dark:bg-slate-850 divide-y divide-slate-200 dark:divide-slate-800">
                        <!-- Loaded dynamically -->
                    </tbody>
                </table>
            </div>

            <!-- Pricing Summary -->
            <div class="px-6 py-5 border-t border-slate-200 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-900/30">
                <div class="flex justify-end">
                    <div class="w-full sm:w-80 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Subtotal</span>
                            <span id="summary-subtotal" class="font-medium text-slate-900 dark:text-white">₹0.00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Discount</span>
                            <span id="summary-discount" class="font-medium text-emerald-600 dark:text-emerald-400">-₹0.00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Tax (GST)</span>
                            <span id="summary-tax" class="font-medium text-slate-900 dark:text-white">₹0.00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Shipping</span>
                            <span id="summary-shipping" class="font-medium text-slate-900 dark:text-white">₹0.00</span>
                        </div>
                        <div class="flex justify-between pt-3 border-t border-slate-200 dark:border-slate-700">
                            <span class="text-base font-semibold text-slate-900 dark:text-white">Total</span>
                            <span id="summary-total" class="text-lg font-bold text-primary-600 dark:text-primary-400">₹0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Payments Log -->
        <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-credit-card mr-2 text-slate-400"></i> Transactions
                </h3>
            </div>
            <div class="p-5">
                <div id="payments-log-container" class="space-y-1">
                    <!-- Loaded dynamically -->
                </div>
            </div>
        </div>

    </div>

    <!-- Sidebar / Fulfillment Column -->
    <div class="xl:col-span-1 space-y-6">
        
        <!-- Customer Meta Card -->
        <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-user mr-2 text-slate-400"></i> Customer
                </h3>
            </div>
            <div class="p-5 space-y-4">
                <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-700 dark:text-primary-400 font-bold flex-shrink-0">
                        <span id="ord-customer-initial">U</span>
                    </div>
                    <div class="ml-3 min-w-0">
                        <p class="text-sm font-medium text-slate-900 dark:text-white truncate" id="ord-customer-name">LOADING...</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 truncate" id="ord-customer-email">LOADING...</p>
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 text-sm">
                    <div class="flex justify-between items-center py-1">
                        <span class="text-slate-500 dark:text-slate-400">Phone</span>
                        <span class="font-medium text-slate-900 dark:text-white" id="ord-customer-phone">LOADING...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Status Update -->
        <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-check-square-offset mr-2 text-slate-400"></i> Update Status
                </h3>
            </div>
            <div class="p-5">
                <form onsubmit="Orders.updateOrderStatus(event)" class="space-y-4">
                    <div>
                        <label for="update-order-status-select" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                        <select id="update-order-status-select" required class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors cursor-pointer">
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
                    </div>

                    <div>
                        <label for="update-order-status-comment" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Comment <span class="text-slate-400 font-normal">(Optional)</span></label>
                        <input type="text" id="update-order-status-comment" placeholder="Note to append to history" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                    </div>

                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                        Update Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Shipments Dispatch -->
        <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-truck mr-2 text-slate-400"></i> Dispatch & Shipments
                </h3>
            </div>
            
            <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                <div id="shipments-log-container">
                    <!-- Loaded dynamically -->
                </div>
            </div>

            <div class="p-5 bg-slate-50/50 dark:bg-slate-900/50">
                <h4 class="text-sm font-medium text-slate-900 dark:text-white mb-3">Add Tracking</h4>
                <form onsubmit="Orders.addShipment(event)" class="space-y-3">
                    <div>
                        <label for="ship-courier" class="sr-only">Courier Partner</label>
                        <input type="text" id="ship-courier" placeholder="Courier Name (e.g. DHL)" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                    </div>
                    <div>
                        <label for="ship-tracking" class="sr-only">Tracking Number</label>
                        <input type="text" id="ship-tracking" placeholder="Tracking Number" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                    </div>
                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                        Add Tracking
                    </button>
                </form>
            </div>
        </div>

        <!-- Tracking Status Timeline Log -->
        <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-clock-counter-clockwise mr-2 text-slate-400"></i> Activity Timeline
                </h3>
            </div>
            <div class="p-5">
                <div id="status-timeline-container">
                    <!-- Loaded dynamically -->
                </div>
            </div>
        </div>

    </div>
</div>

<script src="/admin/assets/js/orders.js?v=<?php echo time(); ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Orders.initDetail();
    });
</script>

<?php
include_once __DIR__ . '/includes/admin-footer.php';
?>
