<?php
require_once __DIR__ . '/includes/config.php';
include_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    
    <!-- Top Navigation & Action Header -->
    <div class="flex items-center justify-between flex-wrap gap-4 print:hidden">
        <a href="<?php echo BASE_URL; ?>account" class="inline-flex items-center text-xs font-bold text-slate-500 hover:text-[#990024] bg-white border border-slate-200/80 hover:border-[#990024]/30 px-3.5 py-2 rounded-xl transition duration-200 shadow-xs">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
        <button onclick="window.print()" class="inline-flex items-center text-xs font-bold text-slate-700 hover:text-[#990024] bg-white border border-slate-200/80 hover:border-[#990024]/30 px-3.5 py-2 rounded-xl transition duration-200 shadow-xs">
            <i class="fas fa-print mr-2 text-slate-400"></i> Print / Download Invoice
        </button>
    </div>

    <!-- Order Detail Card Container (Dynamic JS Hydration) -->
    <div id="order-detail-container">
        <!-- Skeleton Loading State -->
        <div class="bg-white rounded-3xl border border-slate-100 p-8 shadow-sm space-y-6 animate-pulse">
            <div class="h-6 bg-slate-100 rounded-md w-1/3"></div>
            <div class="h-20 bg-slate-50 rounded-2xl"></div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2 h-40 bg-slate-50 rounded-2xl"></div>
                <div class="md:col-span-1 h-40 bg-slate-50 rounded-2xl"></div>
            </div>
        </div>
    </div>

</div>

<!-- Print Styles override for invoice printing -->
<style>
@media print {
    header, footer, nav, #mobile-bottom-nav, .print\:hidden {
        display: none !important;
    }
    body {
        background: #fff !important;
        color: #000 !important;
    }
    .shadow-sm, .shadow-md, .shadow-xl {
        box-shadow: none !important;
    }
    .border {
        border-color: #e2e8f0 !important;
    }
}
</style>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    const OrderDetail = {
        orderId: null,
        order: null,

        async init() {
            if (!Auth.isLoggedIn()) {
                window.location.href = BASE_URL + 'login';
                return;
            }

            this.orderId = Utils.getQueryParam('id');
            if (!this.orderId) {
                window.location.href = BASE_URL + '404';
                return;
            }

            await this.loadOrder();
            
            if (Utils.getQueryParam('pay') == '1' && this.order && (this.order.payment_status === 'pending_payment' || this.order.payment_status === 'pending' || this.order.payment_status === 'failed')) {
                setTimeout(() => {
                    this.payNow();
                }, 500);
            }
        },

        async loadOrder() {
            const container = document.getElementById('order-detail-container');
            container.innerHTML = `
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-12 text-center flex flex-col justify-center items-center space-y-4">
                    <div class="loader-spinner-dark"></div>
                    <span class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Loading Order Details...</span>
                </div>
            `;

            try {
                const res = await Api.get('/orders/' + this.orderId);
                if (res.success && res.data) {
                    this.order = res.data;
                    this.renderOrder();
                } else {
                    window.location.href = BASE_URL + '404';
                }
            } catch (e) {
                container.innerHTML = `
                    <div class="text-center py-16 px-6 bg-white border border-slate-100 rounded-3xl shadow-sm space-y-3">
                        <div class="w-16 h-16 rounded-full bg-red-50 text-red-500 mx-auto flex items-center justify-center text-2xl">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-800">Failed to Load Order Details</h3>
                        <p class="text-slate-500 text-xs max-w-sm mx-auto">${e.message || 'Access denied or order record not found.'}</p>
                        <a href="${BASE_URL}account" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-[#990024] text-white font-bold text-xs shadow-md hover:bg-[#7a001c] transition mt-2">
                            Return to Dashboard
                        </a>
                    </div>
                `;
            }
        },

        /**
         * Render 4-step progress stepper
         */
        renderStepper(status) {
            const s = (status || '').toLowerCase();
            if (s === 'cancelled' || s === 'failed' || s === 'expired') {
                return `
                    <div class="mt-6 pt-6 border-t border-slate-100">
                        <div class="bg-red-50/90 border border-red-200/80 rounded-2xl p-4 flex items-center gap-3.5 text-xs text-red-700">
                            <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-lg flex-shrink-0">
                                <i class="fas fa-circle-xmark"></i>
                            </div>
                            <div>
                                <span class="font-extrabold block text-sm text-red-900">Order ${s.toUpperCase()}</span>
                                <span class="text-red-700/90 text-xs">This order has been ${s}. Stock has been released or refunded accordingly.</span>
                            </div>
                        </div>
                    </div>
                `;
            }

            const steps = [
                { key: 'placed', label: 'Placed', icon: 'fa-shopping-bag' },
                { key: 'confirmed', label: 'Confirmed', icon: 'fa-check-double' },
                { key: 'shipped', label: 'Shipped', icon: 'fa-truck-fast' },
                { key: 'delivered', label: 'Delivered', icon: 'fa-house-circle-check' }
            ];

            let currentStep = 1;
            if (s === 'confirmed' || s === 'processing') currentStep = 2;
            if (s === 'shipped' || s === 'out_for_delivery') currentStep = 3;
            if (s === 'delivered') currentStep = 4;

            return `
                <div class="mt-6 pt-6 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Order Progress</span>
                        <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-100">
                            Step ${currentStep} of 4: ${steps[currentStep - 1].label}
                        </span>
                    </div>
                    
                    <!-- Desktop Progress Line & Icons -->
                    <div class="hidden sm:flex items-center justify-between relative px-4">
                        <div class="absolute left-10 right-10 top-5 h-1 bg-slate-100 z-0"></div>
                        <div class="absolute left-10 top-5 h-1 bg-gradient-to-r from-[#990024] to-[#f59e0b] z-0 transition-all duration-500" style="width: ${((currentStep - 1) / 3) * 85}%"></div>

                        ${steps.map((step, idx) => {
                            const stepNum = idx + 1;
                            const isPassed = stepNum <= currentStep;
                            const isCurrent = stepNum === currentStep;

                            let circleClass = 'bg-slate-100 text-slate-400 border-2 border-white shadow-xs';
                            if (isPassed) circleClass = 'bg-gradient-to-tr from-[#990024] to-[#c7002f] text-white border-2 border-white shadow-md shadow-red-900/20';
                            if (isCurrent) circleClass = 'bg-[#990024] text-white ring-4 ring-red-100 border-2 border-white shadow-lg';

                            return `
                                <div class="relative z-10 flex flex-col items-center group">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-bold transition-all ${circleClass}">
                                        ${isPassed && !isCurrent ? '<i class="fas fa-check"></i>' : `<i class="fas ${step.icon}"></i>`}
                                    </div>
                                    <span class="text-xs font-bold mt-2 ${isPassed ? 'text-slate-900' : 'text-slate-400'}">${step.label}</span>
                                </div>
                            `;
                        }).join('')}
                    </div>

                    <!-- Mobile Stepper Bar -->
                    <div class="sm:hidden grid grid-cols-4 gap-1.5 pt-1">
                        ${steps.map((step, idx) => {
                            const stepNum = idx + 1;
                            const isPassed = stepNum <= currentStep;

                            return `
                                <div class="flex flex-col items-center text-center space-y-1">
                                    <div class="w-full h-1.5 rounded-full ${isPassed ? 'bg-[#990024]' : 'bg-slate-100'} transition-all"></div>
                                    <span class="text-[10px] font-extrabold ${isPassed ? 'text-[#990024]' : 'text-slate-400'} uppercase tracking-tight truncate w-full">${step.label}</span>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            `;
        },

        renderOrder() {
            const container = document.getElementById('order-detail-container');
            const ord = this.order;

            const date = new Date(ord.created_at).toLocaleDateString('en-IN', {
                year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
            });

            // Status badges styling
            let statusClass = 'bg-slate-100 text-slate-700 border-slate-200';
            if (ord.order_status === 'delivered') statusClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            if (ord.order_status === 'pending') statusClass = 'bg-amber-50 text-amber-700 border-amber-200';
            if (ord.order_status === 'confirmed') statusClass = 'bg-blue-50 text-blue-700 border-blue-200';
            if (ord.order_status === 'shipped') statusClass = 'bg-indigo-50 text-indigo-700 border-indigo-200';
            if (ord.order_status === 'cancelled') statusClass = 'bg-red-50 text-red-700 border-red-200';

            let payClass = 'bg-slate-100 text-slate-700 border-slate-200';
            if (ord.payment_status === 'paid') payClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            if (ord.payment_status === 'pending') payClass = 'bg-amber-50 text-amber-700 border-amber-200';
            if (ord.payment_status === 'pending_payment') payClass = 'bg-red-50 text-red-700 border-red-200';
            if (ord.payment_status === 'refunded') payClass = 'bg-purple-50 text-purple-700 border-purple-200';

            // Items List
            let itemsHtml = '';
            ord.items.forEach(item => {
                const placeholder = `${BASE_URL}assets/images/product_placeholder.jpg`;
                const imgUrl = item.primary_image ? (BASE_URL + item.primary_image) : placeholder;
                
                let attrsText = '';
                if (item.attributes) {
                    const parts = [];
                    for (let key in item.attributes) {
                        parts.push(`<span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2 py-0.5 rounded-md mr-1">${key}: ${item.attributes[key]}</span>`);
                    }
                    attrsText = `<div class="flex flex-wrap gap-1 mt-1.5">${parts.join('')}</div>`;
                }

                itemsHtml += `
                    <div class="flex items-center gap-4 py-4 border-b border-slate-100 last:border-0">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl overflow-hidden bg-slate-50 border border-slate-100 flex-shrink-0">
                            <img src="${imgUrl}" alt="${item.product_name}" onerror="this.onerror=null;this.src='${placeholder}';" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-grow min-w-0">
                            <span class="font-black text-slate-900 text-xs sm:text-sm block truncate">${item.product_name}</span>
                            ${item.sku ? `<span class="text-slate-400 block text-[11px] font-semibold mt-0.5">SKU: ${item.sku}</span>` : ''}
                            ${attrsText}
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="font-black text-slate-900 text-sm block">${Utils.formatCurrency(item.price * item.quantity)}</span>
                            <span class="text-slate-400 block text-xs mt-0.5 font-medium">${Utils.formatCurrency(item.price)} × ${item.quantity}</span>
                        </div>
                    </div>
                `;
            });

            // Action overrides (Cancel / Return / Pay Now buttons)
            const canCancel = ord.order_status === 'pending' || ord.order_status === 'confirmed';
            const canReturn = ord.order_status === 'delivered';
            const canPay = (ord.payment_status === 'pending_payment' || ord.payment_status === 'pending' || ord.payment_status === 'failed') && ord.order_status !== 'cancelled' && ord.order_status !== 'expired';

            let actionButtonsHtml = '';
            if (canCancel) {
                actionButtonsHtml += `
                    <button onclick="OrderDetail.cancelOrder()" class="w-full sm:w-auto inline-flex items-center justify-center min-h-[44px] px-5 py-2.5 rounded-xl bg-red-50 hover:bg-red-600 border border-red-200 text-red-600 hover:text-white font-bold text-xs transition duration-200 shadow-xs">
                        <i class="fas fa-ban mr-1.5"></i> Cancel Order
                    </button>
                `;
            }
            if (canReturn) {
                actionButtonsHtml += `
                    <button onclick="OrderDetail.returnOrder()" class="w-full sm:w-auto inline-flex items-center justify-center min-h-[44px] px-5 py-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-600 border border-indigo-200 text-indigo-600 hover:text-white font-bold text-xs transition duration-200 shadow-xs">
                        <i class="fas fa-rotate-left mr-1.5"></i> Request Return
                    </button>
                `;
            }
            if (canPay) {
                actionButtonsHtml += `
                    <button onclick="OrderDetail.payNow()" class="w-full sm:w-auto inline-flex items-center justify-center min-h-[44px] px-6 py-2.5 rounded-xl bg-[#990024] hover:bg-[#7a001c] text-white font-bold text-xs shadow-md shadow-red-900/20 transition duration-200">
                        <i class="fas fa-credit-card mr-2"></i> Pay Online Now
                    </button>
                `;
            }

            let alertBanner = '';
            if (canPay) {
                const isCod = ord.payment_method === 'cod';
                const bannerTitle = isCod ? 'Pay Online & Enjoy Instant Confirmation' : 'Payment Pending / Action Required';
                const bannerMsg = isCod ? 'This order is currently set to Cash on Delivery. You can pay online securely right now!' : 'Please complete your online payment to confirm your order.';
                const btnText = isCod ? 'Pay Online Now' : 'Complete Payment';

                alertBanner = `
                    <div class="bg-gradient-to-r from-amber-500/10 via-amber-50 to-orange-50 border border-amber-500/30 p-4 sm:p-5 mb-6 rounded-3xl shadow-sm flex items-center justify-between flex-wrap gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-2xl bg-amber-500/20 text-amber-700 flex items-center justify-center text-xl flex-shrink-0">
                                <i class="fas ${isCod ? 'fa-wallet' : 'fa-triangle-exclamation'}"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-sm text-amber-950">${bannerTitle}</h3>
                                <p class="text-xs text-amber-800/90 mt-0.5">${bannerMsg}</p>
                            </div>
                        </div>
                        <button onclick="OrderDetail.payNow()" class="w-full sm:w-auto inline-flex items-center justify-center min-h-[44px] px-6 py-2.5 rounded-xl bg-[#990024] hover:bg-[#7a001c] text-white font-bold text-xs shadow-md transition duration-200">
                            <i class="fas fa-lock mr-2"></i> ${btnText}
                        </button>
                    </div>
                `;
            }

            // Shipment tracking info card
            let shipmentHtml = '';
            if (ord.shipment) {
                shipmentHtml = `
                    <div class="bg-gradient-to-r from-indigo-50 to-blue-50/80 p-5 rounded-3xl border border-indigo-100 shadow-sm space-y-3 text-xs text-slate-600 mt-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-extrabold text-indigo-950 flex items-center">
                                <i class="fas fa-truck-fast mr-2 text-indigo-600"></i> Shipment Tracking Info
                            </h3>
                            <span class="text-[10px] font-bold uppercase tracking-wider bg-indigo-100 text-indigo-700 px-2.5 py-0.5 rounded-full">In Transit</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-white/80 p-3.5 rounded-2xl border border-indigo-100/60">
                            <div>
                                <span class="block text-slate-400 font-extrabold uppercase text-[10px]">Courier Partner</span>
                                <span class="text-slate-800 font-bold text-sm mt-0.5 block">${ord.shipment.courier_name}</span>
                            </div>
                            <div>
                                <span class="block text-slate-400 font-extrabold uppercase text-[10px]">Tracking Code</span>
                                <span class="text-indigo-600 font-black text-sm mt-0.5 block select-all">${ord.shipment.tracking_number}</span>
                            </div>
                        </div>
                    </div>
                `;
            }

            const stepperHtml = this.renderStepper(ord.order_status);

            // Render main card container
            container.innerHTML = `
                ${alertBanner}

                <!-- Main Order Summary Card -->
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-5 sm:p-8 space-y-6">
                    
                    <!-- Top Info Bar -->
                    <div class="flex flex-wrap items-center justify-between gap-4 pb-6 border-b border-slate-100">
                        <div>
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest block">Order Reference</span>
                            <div class="flex items-center gap-2 mt-0.5">
                                <h2 class="text-xl sm:text-2xl font-black text-slate-900">${ord.order_number}</h2>
                                <button onclick="navigator.clipboard.writeText('${ord.order_number}'); Utils.showToast('Order number copied!', 'success');" class="text-slate-400 hover:text-slate-700 transition p-1" title="Copy Order Number">
                                    <i class="far fa-copy text-sm"></i>
                                </button>
                            </div>
                            <span class="text-xs text-slate-400 font-medium block mt-0.5">${date}</span>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-3 py-1 text-xs font-extrabold uppercase tracking-wider rounded-full border ${statusClass}">
                                Status: ${ord.order_status}
                            </span>
                            <span class="px-3 py-1 text-xs font-extrabold uppercase tracking-wider rounded-full border ${payClass}">
                                Payment: ${ord.payment_status}
                            </span>
                        </div>
                    </div>

                    <!-- Visual Order Progress Stepper -->
                    ${stepperHtml}

                    <!-- Grid Layout: Ordered Items (Left) & Delivery/Pricing Details (Right) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pt-4">
                        
                        <!-- Left 2 Columns: Ordered Items -->
                        <div class="md:col-span-2 space-y-4">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">
                                    Ordered Items (${ord.items.length})
                                </h3>
                            </div>
                            <div class="divide-y divide-slate-100">
                                ${itemsHtml}
                            </div>
                        </div>

                        <!-- Right 1 Column: Delivery Address & Price Receipt Card -->
                        <div class="md:col-span-1 space-y-6">
                            
                            <!-- Shipping Address Card -->
                            <div class="bg-slate-50/80 p-5 rounded-2xl border border-slate-100 text-xs space-y-2">
                                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block mb-1">Delivery Address</span>
                                <span class="font-bold text-slate-900 text-sm block">${ord.shipping_address ? (ord.shipping_address.full_name || 'Valued Customer') : 'Valued Customer'}</span>
                                <span class="text-slate-600 block leading-relaxed">${ord.shipping_address ? (ord.shipping_address.address_line1 || '') : ''}</span>
                                ${ord.shipping_address && ord.shipping_address.address_line2 ? `<span class="text-slate-600 block leading-relaxed">${ord.shipping_address.address_line2}</span>` : ''}
                                <span class="text-slate-600 block leading-relaxed">${ord.shipping_address ? `${ord.shipping_address.city || ''}, ${ord.shipping_address.state || ''} - ${ord.shipping_address.pincode || ''}` : ''}</span>
                                ${ord.shipping_address && ord.shipping_address.phone ? `
                                    <a href="tel:${ord.shipping_address.phone}" class="inline-flex items-center font-bold text-slate-800 mt-2 hover:text-[#990024] transition">
                                        <i class="fas fa-phone mr-1.5 text-slate-400"></i> +91 ${ord.shipping_address.phone}
                                    </a>
                                ` : ''}
                            </div>

                            <!-- Payment Method Info -->
                            <div class="bg-slate-50/80 p-4 rounded-2xl border border-slate-100 text-xs flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block mb-0.5">Payment Method</span>
                                    <span class="font-black text-slate-800 text-sm uppercase">${ord.payment_method}</span>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase border ${payClass}">
                                    ${ord.payment_status}
                                </span>
                            </div>

                            <!-- Pricing Receipt Breakdown -->
                            <div class="bg-slate-50/80 p-5 rounded-2xl border border-slate-100 space-y-2.5 text-xs text-slate-600">
                                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block mb-2">Price Breakdown</span>
                                
                                <div class="flex justify-between items-center">
                                    <span>Subtotal</span>
                                    <span class="font-bold text-slate-800">${Utils.formatCurrency(ord.subtotal)}</span>
                                </div>
                                ${parseFloat(ord.discount) > 0 ? `
                                    <div class="flex justify-between items-center text-emerald-600 font-bold">
                                        <span>Discount</span>
                                        <span>-${Utils.formatCurrency(ord.discount)}</span>
                                    </div>
                                ` : ''}
                                <div class="flex justify-between items-center">
                                    <span>GST (Tax)</span>
                                    <span class="font-bold text-slate-800">${Utils.formatCurrency(ord.tax_amount)}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span>Shipping</span>
                                    <span class="font-bold text-slate-800">${parseFloat(ord.shipping_charge) > 0 ? Utils.formatCurrency(ord.shipping_charge) : '<span class="text-emerald-600 font-extrabold uppercase text-[10px]">FREE</span>'}</span>
                                </div>
                                <div class="flex justify-between items-center text-base font-black text-[#990024] pt-3 border-t border-slate-200/80">
                                    <span>Total Amount</span>
                                    <span>${Utils.formatCurrency(ord.total)}</span>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Bottom Action Buttons Container -->
                    ${actionButtonsHtml ? `
                        <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-end gap-3 print:hidden">
                            ${actionButtonsHtml}
                        </div>
                    ` : ''}

                </div>

                ${shipmentHtml}
            `;
        },

        async cancelOrder() {
            const comment = prompt("Please provide a reason for cancelling this order:");
            if (comment === null) return;
            if (!comment.trim()) {
                Utils.showToast("Cancellation reason is required.", "warning");
                return;
            }

            try {
                await Api.post(`/orders/${this.orderId}/cancel`, { comment });
                Utils.showToast("Order cancelled successfully.", "success");
                await this.loadOrder();
            } catch (e) {
                Utils.showToast(e.message || "Failed to cancel order.", "error");
            }
        },

        async returnOrder() {
            const comment = prompt("Please provide a reason for returning this order:");
            if (comment === null) return;
            if (!comment.trim()) {
                Utils.showToast("Return reason is required.", "warning");
                return;
            }

            try {
                await Api.post(`/orders/${this.orderId}/return`, { comment });
                Utils.showToast("Return request submitted successfully.", "success");
                await this.loadOrder();
            } catch (e) {
                Utils.showToast(e.message || "Failed to submit return request.", "error");
            }
        },

        async payNow() {
            try {
                const initRes = await Api.post('/payments/initiate', { order_id: this.orderId });
                
                if (initRes.success) {
                    var options = {
                        "key": initRes.key_id,
                        "amount": initRes.amount * 100,
                        "currency": initRes.currency,
                        "name": "Trisha Utsav",
                        "description": "Retry Festive Order " + initRes.order_number,
                        "order_id": initRes.gateway_order_id,
                        "handler": async (response) => {
                            try {
                                const verifyRes = await Api.post('/payments/verify', {
                                    order_id: this.orderId,
                                    payment_id: response.razorpay_payment_id,
                                    status: 'success',
                                    gateway_data: {
                                        razorpay_order_id: response.razorpay_order_id,
                                        razorpay_signature: response.razorpay_signature
                                    }
                                });

                                if (verifyRes.success) {
                                    Utils.showToast("Payment successful! Order confirmed.", "success");
                                    await this.loadOrder();
                                } else {
                                    throw new Error("Verification failed.");
                                }
                            } catch (err) {
                                Utils.showToast("Payment verification failed.", "error");
                                await this.loadOrder();
                            }
                        },
                        "modal": {
                            "ondismiss": async () => {
                                await Api.post('/payments/failed', { order_id: this.orderId, reason: 'user_dismissed' });
                                Utils.showToast("Payment cancelled. You can try again.", "warning");
                                await this.loadOrder();
                            }
                        },
                        "theme": {
                            "color": "#990024"
                        }
                    };
                    var rzp1 = new Razorpay(options);
                    rzp1.on('payment.failed', async (response) => {
                        await Api.post('/payments/verify', {
                            order_id: this.orderId,
                            payment_id: response.error.metadata.payment_id || 'failed_txn',
                            status: 'failed',
                            gateway_data: response.error
                        });
                        Utils.showToast("Payment failed. Please try another method.", "error");
                        await this.loadOrder();
                    });
                    rzp1.open();
                } else {
                    Utils.showToast(initRes.message || "Could not initiate payment.", "warning");
                    await this.loadOrder();
                }
            } catch (e) {
                Utils.showToast("Gateway error: " + e.message, "error");
            }
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        OrderDetail.init();
    });
</script>

<?php
include_once __DIR__ . '/includes/footer.php';
?>
