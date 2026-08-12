/**
 * Admin Orders Management Module
 */

const Orders = {
    filters: {
        status: ''
    },
    currentOrderId: null,

    initList() {
        this.filters.status = Utils.getQueryParam('status') || '';
        const select = document.getElementById('order-status-filter');
        if (select) select.value = this.filters.status;

        this.loadOrdersTable();
    },

    async loadOrdersTable() {
        const tbody = document.getElementById('orders-tbody');
        if (!tbody) return;

        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-12 text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-2"></div>
                    <span class="block text-sm text-slate-500 dark:text-slate-400">Loading orders...</span>
                </td>
            </tr>
        `;

        try {
            const q = new URLSearchParams();
            if (this.filters.status) q.append('status', this.filters.status);

            const res = await Api.get('/admin/orders?' + q.toString());
            if (res.success && res.data) {
                if (res.data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3 text-slate-400 dark:text-slate-500">
                                    <i class="ph ph-receipt text-2xl"></i>
                                </div>
                                <p class="text-sm font-medium text-slate-900 dark:text-white">No orders found</p>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Try adjusting your filters or search query.</p>
                            </td>
                        </tr>
                    `;
                    return;
                }

                let html = '';
                res.data.forEach(ord => {
                    const date = new Date(ord.created_at).toLocaleDateString('en-IN', {
                        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
                    });

                    let statusClass = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
                    let statusIcon = 'ph-clock';
                    if (ord.order_status === 'delivered') { statusClass = 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20'; statusIcon = 'ph-check-circle'; }
                    if (ord.order_status === 'pending') { statusClass = 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20'; statusIcon = 'ph-hourglass-high'; }
                    if (ord.order_status === 'confirmed' || ord.order_status === 'processing') { statusClass = 'bg-primary-50 text-primary-700 ring-1 ring-primary-600/20 dark:bg-primary-500/10 dark:text-primary-400 dark:ring-primary-500/20'; statusIcon = 'ph-arrows-clockwise'; }
                    if (ord.order_status === 'shipped') { statusClass = 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/20'; statusIcon = 'ph-truck'; }
                    if (ord.order_status === 'cancelled') { statusClass = 'bg-red-50 text-red-700 ring-1 ring-red-600/20 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/20'; statusIcon = 'ph-x-circle'; }

                    let paymentClass = 'text-slate-500 dark:text-slate-400';
                    let paymentIcon = 'ph-clock';
                    if (ord.payment_status === 'paid') { paymentClass = 'text-emerald-600 dark:text-emerald-400'; paymentIcon = 'ph-check-circle'; }
                    if (ord.payment_status === 'refunded') { paymentClass = 'text-red-600 dark:text-red-400'; paymentIcon = 'ph-arrow-u-down-left'; }

                    const initial = (ord.first_name || 'U').charAt(0).toUpperCase();

                    html += `
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-slate-300 rounded cursor-pointer dark:bg-slate-800 dark:border-slate-600 dark:checked:bg-primary-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-slate-900 dark:text-white">#${ord.order_number}</div>
                                <div class="text-xs font-semibold text-slate-900 dark:text-white mt-1">${Utils.formatCurrency(ord.total)}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 font-semibold flex-shrink-0 text-xs">
                                        ${initial}
                                    </div>
                                    <div class="ml-3 min-w-0">
                                        <div class="text-sm font-medium text-slate-900 dark:text-white truncate">${ord.first_name} ${ord.last_name}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 truncate">${ord.email}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-900 dark:text-white">${date}</div>
                                <div class="text-xs font-medium ${paymentClass} mt-0.5 flex items-center">
                                    <i class="ph ${paymentIcon} mr-1"></i> ${ord.payment_status.charAt(0).toUpperCase() + ord.payment_status.slice(1)}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ${statusClass}">
                                    <i class="ph ${statusIcon} mr-1.5 text-sm"></i>
                                    ${ord.order_status.charAt(0).toUpperCase() + ord.order_status.slice(1)}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="/admin/order-detail?id=${ord.id}" class="p-1.5 text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors rounded focus:outline-none focus:ring-2 focus:ring-primary-500" title="Manage Order">
                                    <i class="ph ph-arrow-square-out text-lg"></i>
                                </a>
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
            }
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-12 text-center text-red-500 font-medium">Failed to load orders: ${e.message}</td></tr>`;
        }
    },

    applyFilters() {
        const status = document.getElementById('order-status-filter').value;
        const url = new URL(window.location.href);
        if (status) url.searchParams.set('status', status); else url.searchParams.delete('status');
        window.location.href = url.toString();
    },

    /**
     * Initializer for detail page
     */
    async initDetail() {
        this.currentOrderId = Utils.getQueryParam('id');
        if (!this.currentOrderId) {
            window.location.href = '/admin/orders';
            return;
        }

        await this.loadOrderDetail();
    },

    async loadOrderDetail() {
        try {
            const res = await Api.get('/admin/orders/' + this.currentOrderId);
            if (res.success && res.data) {
                const ord = res.data.order || res.data;
                const items = ord.items || res.data.items || [];
                const history = ord.status_history || res.data.status_history || [];
                const payments = ord.payments || res.data.payments || [];
                const shipments = ord.shipments || res.data.shipments || [];

                // Render metrics
                document.getElementById('ord-number').innerText = ord.order_number;
                document.getElementById('ord-customer-name').innerText = ord.shipping_address ? ord.shipping_address.full_name : (ord.first_name + ' ' + ord.last_name);
                document.getElementById('ord-customer-email').innerText = ord.email || ord.guest_email || 'N/A';
                document.getElementById('ord-customer-phone').innerText = ord.shipping_address ? ord.shipping_address.phone : (ord.phone || 'N/A');

                const date = new Date(ord.created_at).toLocaleDateString('en-IN', {
                    year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
                });
                document.getElementById('ord-date').innerText = date;

                // Status selectors
                document.getElementById('ord-status-badge').innerText = ord.order_status.toUpperCase();
                let statusColor = 'bg-gray-100 text-gray-800';
                if (ord.order_status === 'delivered') statusColor = 'bg-emerald-100 text-emerald-800';
                if (ord.order_status === 'pending') statusColor = 'bg-amber-100 text-amber-800';
                if (ord.order_status === 'cancelled') statusColor = 'bg-red-100 text-red-800';
                if (ord.order_status === 'shipped') statusColor = 'bg-blue-100 text-blue-800';
                document.getElementById('ord-status-badge').className = `px-2.5 py-1 rounded-full font-bold uppercase tracking-wider text-[10px] ${statusColor}`;

                document.getElementById('ord-payment-badge').innerText = ord.payment_status.toUpperCase();
                let paymentColor = 'bg-gray-100 text-gray-800';
                if (ord.payment_status === 'paid') paymentColor = 'bg-emerald-100 text-emerald-800';
                if (ord.payment_status === 'refunded') paymentColor = 'bg-red-100 text-red-800';
                document.getElementById('ord-payment-badge').className = `px-2.5 py-1 rounded-full font-bold uppercase tracking-wider text-[10px] ${paymentColor}`;

                // Populate status change dropdown value
                document.getElementById('update-order-status-select').value = ord.order_status;

                // Addresses
                const sa = ord.shipping_address;
                if (sa) {
                    document.getElementById('shipping-address-box').innerHTML = `
                        <p class="text-sm font-medium text-slate-900 dark:text-white">${sa.full_name}</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">${sa.address_line1}${sa.address_line2 ? ', ' + sa.address_line2 : ''}</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">${sa.city}, ${sa.state} ${sa.pincode}</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">${sa.country}</p>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-300 mt-2 flex items-center">
                            <i class="ph ph-phone mr-1.5 text-slate-400"></i> ${sa.phone}
                        </p>
                    `;
                }

                const ba = ord.billing_address || sa;
                if (ba) {
                    document.getElementById('billing-address-box').innerHTML = `
                        <p class="text-sm font-medium text-slate-900 dark:text-white">${ba.full_name}</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">${ba.address_line1}${ba.address_line2 ? ', ' + ba.address_line2 : ''}</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">${ba.city}, ${ba.state} ${ba.pincode}</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">${ba.country}</p>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-300 mt-2 flex items-center">
                            <i class="ph ph-phone mr-1.5 text-slate-400"></i> ${ba.phone}
                        </p>
                    `;
                }

                // Render Order Items
                this.renderOrderItems(items);

                // Financial calculations
                document.getElementById('summary-subtotal').innerText = Utils.formatCurrency(ord.subtotal);
                document.getElementById('summary-discount').innerText = '-' + Utils.formatCurrency(ord.discount);
                document.getElementById('summary-tax').innerText = Utils.formatCurrency(ord.tax_amount);
                document.getElementById('summary-shipping').innerText = Utils.formatCurrency(ord.shipping_charge);
                document.getElementById('summary-total').innerText = Utils.formatCurrency(ord.total);

                // Render status logs timeline
                this.renderStatusTimeline(history);

                // Render payments log
                this.renderPayments(payments);

                // Render shipment dispatches
                this.renderShipments(shipments);

                // Populate Delhivery Tracking ID if present
                if (ord.tracking_id) {
                    const waybillInput = document.getElementById('admin-delhivery-waybill');
                    if (waybillInput) waybillInput.value = ord.tracking_id;
                    const infoEl = document.getElementById('delhivery-tracking-status-info');
                    if (infoEl) {
                        infoEl.innerHTML = `<span class="text-emerald-600 font-bold flex items-center"><i class="ph ph-check-circle mr-1 text-sm"></i>Active Waybill: ${ord.tracking_id}</span>`;
                    }
                }
            }
        } catch (e) {
            Utils.showToast("Failed to fetch order details: " + e.message, "error");
        }
    },

    async saveTrackingId() {
        if (!this.currentOrderId) return;
        const waybillInput = document.getElementById('admin-delhivery-waybill');
        const trackingId = waybillInput ? waybillInput.value.trim() : '';

        if (!trackingId) {
            Utils.showToast("Please enter a valid tracking ID / waybill.", "warning");
            return;
        }

        try {
            const res = await Api.put('/admin/orders/' + this.currentOrderId + '/tracking', { tracking_id: trackingId });
            if (res.success) {
                Utils.showToast("Delhivery Tracking ID saved successfully!", "success");
                const infoEl = document.getElementById('delhivery-tracking-status-info');
                if (infoEl) {
                    infoEl.innerHTML = `<span class="text-emerald-600 font-bold flex items-center"><i class="ph ph-check-circle mr-1 text-sm"></i>Active Waybill: ${trackingId}</span>`;
                }
            } else {
                Utils.showToast(res.message || "Failed to update tracking ID.", "error");
            }
        } catch (e) {
            Utils.showToast(e.message || "Error updating tracking ID.", "error");
        }
    },

    renderOrderItems(items) {
        const tbody = document.getElementById('order-items-tbody');
        if (!tbody) return;

        let html = '';
        items.forEach(it => {
            const attrText = it.attributes ? Object.entries(JSON.parse(it.attributes)).map(([k, v]) => `${k}: ${v}`).join(', ') : '';
            html += `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-slate-900 dark:text-white">${it.product_name}</span>
                            ${attrText ? `<span class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">${attrText}</span>` : ''}
                            <span class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 font-mono">SKU: ${it.sku || 'N/A'}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-slate-500 dark:text-slate-400">
                        ${Utils.formatCurrency(it.price)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-slate-900 dark:text-white font-medium">
                        x${it.quantity}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-slate-900 dark:text-white">
                        ${Utils.formatCurrency(it.total)}
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    },

    renderStatusTimeline(logs) {
        const container = document.getElementById('status-timeline-container');
        if (!container) return;

        if (logs.length === 0) {
            container.innerHTML = `<span class="text-sm text-slate-500 dark:text-slate-400">No activity recorded yet.</span>`;
            return;
        }

        let html = '<div class="flow-root"><ul role="list" class="-mb-8">';
        logs.forEach((log, idx) => {
            const date = new Date(log.created_at).toLocaleDateString('en-IN', {
                year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
            });
            const isLast = idx === logs.length - 1;

            let icon = 'ph-check';
            let color = 'text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 ring-slate-200 dark:ring-slate-700';
            
            if(log.status === 'delivered') { icon = 'ph-package'; color = 'text-emerald-600 bg-emerald-100 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:ring-emerald-900'; }
            if(log.status === 'shipped') { icon = 'ph-truck'; color = 'text-blue-600 bg-blue-100 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:ring-blue-900'; }
            if(log.status === 'cancelled') { icon = 'ph-x'; color = 'text-red-600 bg-red-100 ring-red-200 dark:bg-red-900/30 dark:text-red-400 dark:ring-red-900'; }

            html += `
                <li>
                    <div class="relative pb-8">
                        ${!isLast ? '<span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-slate-200 dark:bg-slate-700" aria-hidden="true"></span>' : ''}
                        <div class="relative flex space-x-3">
                            <div>
                                <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-slate-850 ${color}">
                                    <i class="ph ${icon} text-lg"></i>
                                </span>
                            </div>
                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                <div>
                                    <p class="text-sm text-slate-900 dark:text-white font-medium capitalize">${log.status}</p>
                                    ${log.comment ? `<p class="mt-1 text-sm text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/50 p-2 rounded-md border border-slate-100 dark:border-slate-800">${log.comment}</p>` : ''}
                                </div>
                                <div class="whitespace-nowrap text-right text-xs text-slate-500 dark:text-slate-400 flex-shrink-0">
                                    <time datetime="${log.created_at}">${date}</time>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            `;
        });
        html += '</ul></div>';
        container.innerHTML = html;
    },

    renderPayments(payments) {
        const container = document.getElementById('payments-log-container');
        if (!container) return;

        if (payments.length === 0) {
            container.innerHTML = `<span class="text-sm text-slate-500 dark:text-slate-400">No transaction records logged.</span>`;
            return;
        }

        let html = '<ul role="list" class="divide-y divide-slate-200 dark:divide-slate-800">';
        payments.forEach(pay => {
            const date = new Date(pay.created_at).toLocaleDateString('en-IN', {
                year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
            });

            let statusClass = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
            if (pay.status === 'success') statusClass = 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20';
            if (pay.status === 'failed') statusClass = 'bg-red-50 text-red-700 ring-1 ring-red-600/20 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/20';

            html += `
                <li class="py-3 flex justify-between items-center">
                    <div class="flex flex-col">
                        <span class="text-sm font-medium text-slate-900 dark:text-white font-mono">${pay.transaction_id || 'Cash / Offline'}</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex items-center uppercase tracking-wider">
                            ${pay.payment_method} &bull; ${date}
                        </span>
                    </div>
                    <div class="flex flex-col items-end gap-1.5">
                        <span class="text-sm font-bold text-slate-900 dark:text-white">${Utils.formatCurrency(pay.amount)}</span>
                        <span class="inline-flex items-center rounded px-2 py-0.5 text-[10px] font-medium uppercase tracking-wider ${statusClass}">${pay.status}</span>
                    </div>
                </li>
            `;
        });
        html += '</ul>';
        container.innerHTML = html;
    },

    renderShipments(shipments) {
        const container = document.getElementById('shipments-log-container');
        if (!container) return;

        if (shipments.length === 0) {
            container.innerHTML = `<span class="text-sm text-slate-500 dark:text-slate-400">No dispatches created yet. Use the form below to add tracking.</span>`;
            return;
        }

        let html = '<ul role="list" class="divide-y divide-slate-200 dark:divide-slate-800">';
        shipments.forEach(ship => {
            const shipDate = ship.shipped_at ? new Date(ship.shipped_at).toLocaleDateString('en-IN', {
                year: 'numeric', month: 'short', day: 'numeric'
            }) : 'Pending';

            html += `
                <li class="py-3 flex justify-between items-center">
                    <div class="flex flex-col">
                        <span class="text-sm font-medium text-slate-900 dark:text-white flex items-center">
                            <i class="ph ph-truck mr-1.5 text-slate-400"></i> ${ship.courier_name}
                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-mono tracking-tight">${ship.tracking_number}</span>
                    </div>
                    <div class="flex flex-col items-end gap-1.5">
                        <span class="inline-flex items-center rounded px-2 py-0.5 text-[10px] font-medium uppercase tracking-wider bg-blue-50 text-blue-700 ring-1 ring-blue-600/20 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/20">${ship.status}</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">${shipDate}</span>
                    </div>
                </li>
            `;
        });
        html += '</ul>';
        container.innerHTML = html;
    },

    /**
     * Submit Order Status update
     */
    async updateOrderStatus(e) {
        e.preventDefault();
        const select = document.getElementById('update-order-status-select');
        const comment = document.getElementById('update-order-status-comment').value;

        try {
            await Api.patch(`/admin/orders/${this.currentOrderId}/status`, {
                status: select.value,
                comment: comment
            });

            Utils.showToast("Order status successfully updated.", "success");
            document.getElementById('update-order-status-comment').value = '';
            
            // Reload details
            await this.loadOrderDetail();
        } catch (e) {
            Utils.showToast(e.message || "Failed to update status.", "error");
        }
    },

    /**
     * Dispatch Shipment tracking details
     */
    async addShipment(e) {
        e.preventDefault();
        const courier = document.getElementById('ship-courier').value;
        const tracking = document.getElementById('ship-tracking').value;

        if (!courier || !tracking) {
            Utils.showToast("Courier name and tracking code are required.", "warning");
            return;
        }

        try {
            await Api.post(`/admin/orders/${this.currentOrderId}/shipment`, {
                courier_name: courier,
                tracking_number: tracking
            });

            Utils.showToast("Shipment dispatch recorded successfully.", "success");
            document.getElementById('ship-courier').value = '';
            document.getElementById('ship-tracking').value = '';

            await this.loadOrderDetail();
        } catch (e) {
            Utils.showToast(e.message || "Failed to ship order.", "error");
        }
    },

    /**
     * Process order refund
     */
    async refundOrder() {
        const amountStr = prompt("Enter amount to refund:");
        if (amountStr === null) return;
        const amount = parseFloat(amountStr);
        if (isNaN(amount) || amount <= 0) {
            Utils.showToast("Please enter a valid numeric refund amount.", "warning");
            return;
        }

        const reason = prompt("Enter refund reason:");
        if (reason === null) return;

        try {
            await Api.post(`/admin/orders/${this.currentOrderId}/refund`, {
                amount: amount,
                reason: reason
            });

            Utils.showToast("Refund processed successfully.", "success");
            await this.loadOrderDetail();
        } catch (e) {
            Utils.showToast(e.message || "Failed to process refund.", "error");
        }
    },

    /**
     * Cancel active order
     */
    async cancelOrder() {
        const reason = prompt("Enter cancellation reason:");
        if (reason === null) return;

        try {
            await Api.post(`/admin/orders/${this.currentOrderId}/cancel`, {
                reason: reason
            });

            Utils.showToast("Order cancelled successfully.", "success");
            await this.loadOrderDetail();
        } catch (e) {
            Utils.showToast(e.message || "Failed to cancel order.", "error");
        }
    }
};

window.Orders = Orders;
