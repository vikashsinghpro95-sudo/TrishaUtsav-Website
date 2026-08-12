/**
 * Admin Customers Management Module
 */

const Customers = {
    searchQuery: '',
    currentCustomerId: null,

    initList() {
        this.loadCustomersTable();

        const searchInput = document.getElementById('customer-search');
        if (searchInput) {
            searchInput.addEventListener('input', Utils.debounce(() => {
                this.searchQuery = searchInput.value;
                this.loadCustomersTable();
            }, 450));
        }
    },

    async loadCustomersTable() {
        const tbody = document.getElementById('customers-tbody');
        if (!tbody) return;

        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-12 text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-2"></div>
                    <span class="block text-sm text-slate-500 dark:text-slate-400">Loading customers...</span>
                </td>
            </tr>
        `;

        try {
            const path = '/admin/customers' + (this.searchQuery ? `?search=${encodeURIComponent(this.searchQuery)}` : '');
            const res = await Api.get(path);
            
            if (res.success && res.data) {
                if (res.data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3 text-slate-400 dark:text-slate-500">
                                    <i class="ph ph-users text-2xl"></i>
                                </div>
                                <p class="text-sm font-medium text-slate-900 dark:text-white">No customers found</p>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Adjust your search to find what you're looking for.</p>
                            </td>
                        </tr>
                    `;
                    return;
                }

                let html = '';
                res.data.forEach(cust => {
                    let statusClass = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
                    if (cust.status === 'active') statusClass = 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20';
                    if (cust.status === 'banned') statusClass = 'bg-red-50 text-red-700 ring-1 ring-red-600/20 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/20';
                    
                    const date = new Date(cust.created_at).toLocaleDateString('en-IN', {
                        year: 'numeric', month: 'short', day: 'numeric'
                    });
                    
                    const initial = (cust.first_name || 'U').charAt(0).toUpperCase();

                    html += `
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-700 dark:text-primary-400 font-bold flex-shrink-0 text-sm">
                                        ${initial}
                                    </div>
                                    <div class="ml-4 min-w-0">
                                        <div class="text-sm font-medium text-slate-900 dark:text-white truncate">${cust.first_name} ${cust.last_name}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Joined ${date}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                                <div class="text-sm text-slate-900 dark:text-white flex items-center">
                                    <i class="ph ph-envelope-simple mr-1.5 text-slate-400"></i> ${cust.email}
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center">
                                    <i class="ph ph-phone mr-1.5 text-slate-400"></i> ${cust.phone || 'N/A'}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ${statusClass}">
                                    ${cust.status.charAt(0).toUpperCase() + cust.status.slice(1)}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm text-slate-900 dark:text-white font-medium">${cust.total_orders}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm text-slate-900 dark:text-white font-semibold">${Utils.formatCurrency(cust.total_spent)}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="/admin/customer-detail?id=${cust.id}" class="p-1.5 text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors rounded focus:outline-none focus:ring-2 focus:ring-primary-500" title="View Profile">
                                    <i class="ph ph-user-circle text-lg"></i>
                                </a>
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
            }
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-12 text-center text-red-500 font-medium">Failed to load customers: ${e.message}</td></tr>`;
        }
    },

    /**
     * Initialize customer details page
     */
    async initDetail() {
        this.currentCustomerId = Utils.getQueryParam('id');
        if (!this.currentCustomerId) {
            window.location.href = '/admin/customers';
            return;
        }

        await this.loadCustomerProfile();
    },

    async loadCustomerProfile() {
        try {
            const res = await Api.get('/admin/customers/' + this.currentCustomerId);
            if (res.success && res.data) {
                const profile = res.data.profile;
                const addresses = res.data.addresses || [];
                const orders = res.data.orders || [];

                // Render metrics
                document.getElementById('cust-name').innerText = profile.first_name + ' ' + profile.last_name;
                document.getElementById('cust-email').innerText = profile.email;
                document.getElementById('cust-phone').innerText = profile.phone || 'N/A';
                
                const createdDate = new Date(profile.created_at).toLocaleDateString('en-IN', {
                    year: 'numeric', month: 'short', day: 'numeric'
                });
                document.getElementById('cust-joined').innerText = createdDate;

                // Status Badge
                const statusBadge = document.getElementById('cust-status-badge');
                statusBadge.innerText = profile.status.toUpperCase();
                let statusColor = 'bg-gray-100 text-gray-850';
                if (profile.status === 'active') statusColor = 'bg-emerald-100 text-emerald-800';
                if (profile.status === 'banned') statusColor = 'bg-red-100 text-red-800';
                statusBadge.className = `px-2.5 py-1 rounded-full font-bold uppercase tracking-wider text-[10px] ${statusColor}`;

                // Action buttons triggers
                const actionBtn = document.getElementById('btn-cust-status-action');
                if (profile.status === 'active') {
                    actionBtn.innerText = 'Ban Customer Account';
                    actionBtn.className = 'w-full bg-red-600 hover:bg-red-700 text-white font-bold text-xs py-2 rounded-lg shadow-sm transition';
                    actionBtn.onclick = () => this.toggleCustomerStatus('banned');
                } else {
                    actionBtn.innerText = 'Activate Customer Account';
                    actionBtn.className = 'w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2 rounded-lg shadow-sm transition';
                    actionBtn.onclick = () => this.toggleCustomerStatus('active');
                }

                // Render Addresses
                this.renderAddresses(addresses);

                // Render Order History
                this.renderOrders(orders);
            }
        } catch (e) {
            Utils.showToast("Failed to fetch customer profile: " + e.message, "error");
        }
    },

    renderAddresses(addresses) {
        const container = document.getElementById('addresses-container');
        if (!container) return;

        if (addresses.length === 0) {
            container.innerHTML = `<span class="text-sm text-slate-500 dark:text-slate-400">No addresses registered.</span>`;
            return;
        }

        let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
        addresses.forEach(addr => {
            html += `
                <div class="p-4 border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 rounded-xl space-y-1 relative group hover:border-primary-300 dark:hover:border-primary-700 transition-colors">
                    ${parseInt(addr.is_default) === 1 ? `
                        <span class="absolute top-4 right-4 bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Default</span>
                    ` : ''}
                    <div class="flex items-center gap-2 mb-2">
                        <i class="ph ph-${addr.type === 'shipping' ? 'truck' : 'file-text'} text-slate-400"></i>
                        <p class="font-semibold text-slate-900 dark:text-white capitalize">${addr.type}</p>
                    </div>
                    <p class="font-medium text-slate-800 dark:text-slate-200">${addr.full_name}</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">${addr.address_line1}${addr.address_line2 ? ', ' + addr.address_line2 : ''}</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">${addr.city}, ${addr.state} ${addr.pincode}</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">${addr.country}</p>
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300 mt-2 flex items-center pt-2 border-t border-slate-200 dark:border-slate-700">
                        <i class="ph ph-phone mr-1.5 text-slate-400"></i> ${addr.phone}
                    </p>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    },

    renderOrders(orders) {
        const tbody = document.getElementById('orders-tbody');
        if (!tbody) return;

        if (orders.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                        Customer has not placed any orders yet.
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        orders.forEach(ord => {
            const date = new Date(ord.created_at).toLocaleDateString('en-IN', {
                year: 'numeric', month: 'short', day: 'numeric'
            });

            let statusClass = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
            if (ord.order_status === 'delivered') statusClass = 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20';
            if (ord.order_status === 'pending') statusClass = 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20';
            if (ord.order_status === 'shipped') statusClass = 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/20';
            if (ord.order_status === 'cancelled') statusClass = 'bg-red-50 text-red-700 ring-1 ring-red-600/20 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/20';

            html += `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-white">
                        #${ord.order_number}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                        ${date}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ${statusClass}">
                            ${ord.order_status.charAt(0).toUpperCase() + ord.order_status.slice(1)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-slate-900 dark:text-white">
                        ${Utils.formatCurrency(ord.total)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="/admin/order-detail?id=${ord.id}" class="p-1.5 text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors rounded focus:outline-none focus:ring-2 focus:ring-primary-500" title="View Order">
                            <i class="ph ph-arrow-square-out text-lg"></i>
                        </a>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    },

    async toggleCustomerStatus(newStatus) {
        if (!confirm(`Are you sure you want to change this customer status to '${newStatus}'?`)) return;

        try {
            await Api.patch(`/admin/customers/${this.currentCustomerId}/status`, {
                status: newStatus
            });
            Utils.showToast(`Customer account successfully set to ${newStatus}.`, "success");
            await this.loadCustomerProfile();
        } catch (e) {
            Utils.showToast(e.message || "Failed to update customer status.", "error");
        }
    }
};

window.Customers = Customers;
