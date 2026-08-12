/**
 * E-Commerce Customer Dashboard Account Module
 */

const Account = {
    activeTab: 'dashboard',
    editingAddressId: null,

    /**
     * Initializer
     */
    async init() {
        if (!Auth.isLoggedIn()) {
            window.location.href = BASE_URL + 'login';
            return;
        }

        this.bindTabControls();
        this.renderActiveTab();
    },

    bindTabControls() {
        const tabs = ['dashboard', 'profile', 'addresses', 'orders', 'password'];
        tabs.forEach(tab => {
            const btn = document.getElementById(`tab-btn-${tab}`);
            if (btn) {
                btn.addEventListener('click', () => {
                    this.switchTab(tab);
                });
            }
        });

        // Logout binding
        const logoutBtn = document.getElementById('tab-btn-logout');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => {
                Auth.logout();
            });
        }
    },

    switchTab(tabName) {
        this.activeTab = tabName;
        
        // Toggle active button styling
        const tabs = ['dashboard', 'profile', 'addresses', 'orders', 'password'];
        tabs.forEach(t => {
            const btn = document.getElementById(`tab-btn-${t}`);
            const block = document.getElementById(`tab-content-${t}`);
            
            if (btn) {
                if (t === tabName) {
                    btn.className = "w-full flex items-center space-x-3 px-4 py-3 bg-[#990024] text-[#fffdf7] font-extrabold text-xs uppercase tracking-wider rounded-2xl transition duration-150 shadow-md";
                } else {
                    btn.className = "w-full flex items-center space-x-3 px-4 py-3 text-slate-600 hover:bg-[#990024]/10 hover:text-[#990024] text-xs font-bold uppercase tracking-wider rounded-2xl transition duration-150";
                }
            }
            if (block) {
                if (t === tabName) {
                    block.classList.remove('hidden');
                } else {
                    block.classList.add('hidden');
                }
            }
        });

        this.renderActiveTab();
    },

    renderActiveTab() {
        if (this.activeTab === 'dashboard') {
            this.loadDashboard();
        } else if (this.activeTab === 'profile') {
            this.loadProfile();
        } else if (this.activeTab === 'addresses') {
            this.loadAddresses();
        } else if (this.activeTab === 'orders') {
            this.loadOrders();
        } else if (this.activeTab === 'password') {
            this.bindPasswordForm();
        }
    },

    /**
     * Hydrate Sidebar and Banner User Profile Cards
     */
    updateUserProfileHeader(user) {
        if (!user) return;

        const fullName = `${user.first_name || ''} ${user.last_name || ''}`.trim() || 'Valued Member';
        const initials = ((user.first_name || 'U')[0] + (user.last_name || '')[0]).toUpperCase();

        // 1. Sidebar Card
        const navName = document.getElementById('account-nav-name');
        const navEmail = document.getElementById('account-nav-email');
        if (navName) navName.innerText = fullName;
        if (navEmail) navEmail.innerText = user.email || '';

        const avatarImg = document.getElementById('user-avatar-img');
        const avatarFallback = document.getElementById('user-avatar-fallback');
        const avatarInitials = document.getElementById('avatar-initials');

        if (user.avatar) {
            if (avatarImg) {
                avatarImg.src = user.avatar;
                avatarImg.classList.remove('hidden');
            }
            if (avatarFallback) avatarFallback.classList.add('hidden');
        } else {
            if (avatarImg) avatarImg.classList.add('hidden');
            if (avatarFallback) avatarFallback.classList.remove('hidden');
            if (avatarInitials) avatarInitials.innerText = initials;
        }

        // 2. Banner Avatar & Info
        const dashWelcome = document.getElementById('dash-welcome-name');
        if (dashWelcome) dashWelcome.innerText = fullName;

        const dashImg = document.getElementById('dash-banner-avatar-img');
        const dashFallback = document.getElementById('dash-banner-avatar-fallback');
        const dashInitials = document.getElementById('dash-avatar-initials');
        const dashEmail = document.getElementById('dash-banner-email');
        const dashPhone = document.getElementById('dash-banner-phone');

        if (dashEmail) dashEmail.innerText = user.email || '';

        if (user.avatar) {
            if (dashImg) {
                dashImg.src = user.avatar;
                dashImg.classList.remove('hidden');
            }
            if (dashFallback) dashFallback.classList.add('hidden');
        } else {
            if (dashImg) dashImg.classList.add('hidden');
            if (dashFallback) dashFallback.classList.remove('hidden');
            if (dashInitials) dashInitials.innerText = initials;
        }

        // 3. Verification & Badges
        const verifiedBadge = document.getElementById('user-verified-badge');
        const phoneStatus = document.getElementById('profile-phone-status-badge');
        const isVerified = parseInt(user.is_phone_verified) === 1;

        if (dashPhone) {
            dashPhone.innerHTML = isVerified
                ? `<i class="fas fa-check-circle text-emerald-400 mr-1"></i> +91 ${user.phone || ''}`
                : `<span class="text-amber-300 font-bold underline cursor-pointer" onclick="Auth.showPhoneOtpModal()"><i class="fas fa-exclamation-circle mr-1"></i> Verify Phone</span>`;
        }

        if (verifiedBadge) {
            if (isVerified) {
                verifiedBadge.classList.remove('hidden');
            } else {
                verifiedBadge.classList.add('hidden');
            }
        }

        if (phoneStatus) {
            if (isVerified) {
                phoneStatus.className = "text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 flex items-center";
                phoneStatus.innerHTML = `<i class="fas fa-check-circle mr-1"></i> Verified`;
            } else {
                phoneStatus.className = "text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-700 flex items-center cursor-pointer";
                phoneStatus.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i> Verify OTP`;
                phoneStatus.onclick = () => Auth.showPhoneOtpModal();
            }
        }

        // 4. Google Auth connected badge
        const googleBadge = document.getElementById('google-connected-badge');
        if (googleBadge) {
            if (user.avatar || (user.email && user.email.includes('gmail'))) {
                googleBadge.classList.remove('hidden');
            } else {
                googleBadge.classList.add('hidden');
            }
        }
    },

    /**
     * Dashboard Tab Loading
     */
    async loadDashboard() {
        const user = Auth.getCurrentUser();
        this.updateUserProfileHeader(user);

        const metricsGrid = document.getElementById('dash-metrics-grid');
        if (metricsGrid) {
            metricsGrid.innerHTML = `
                <div class="col-span-full py-8 text-center">
                    <div class="loader-spinner-dark mx-auto"></div>
                </div>
            `;
        }

        try {
            const [ordersRes, addrRes] = await Promise.all([
                Api.get('/orders?per_page=1').catch(() => ({ success: false })),
                Api.get('/addresses').catch(() => ({ success: false }))
            ]);

            if (metricsGrid) {
                const totalOrders = (ordersRes.success && ordersRes.pagination) ? ordersRes.pagination.total_items : 0;
                const totalAddresses = (addrRes.success && addrRes.data) ? addrRes.data.length : 0;
                const isPhoneVerified = user && parseInt(user.is_phone_verified) === 1;

                metricsGrid.innerHTML = `
                    <!-- Metric 1: Placed Orders -->
                    <div onclick="Account.switchTab('orders')" class="bg-white p-6 rounded-3xl border border-[#f59e0b]/20 shadow-sm flex items-center justify-between cursor-pointer hover:border-[#990024] hover:shadow-md transition">
                        <div>
                            <span class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Orders</span>
                            <span class="text-3xl font-black text-[#12090c] mt-1 block">${totalOrders}</span>
                            <span class="text-xs text-[#990024] font-bold mt-1 inline-block hover:underline">View Order History &rarr;</span>
                        </div>
                        <div class="w-14 h-14 bg-[#990024]/10 text-[#990024] rounded-2xl flex items-center justify-center text-xl border border-[#990024]/20 shadow-sm">
                            <i class="fas fa-box-open"></i>
                        </div>
                    </div>

                    <!-- Metric 2: Saved Delivery Addresses -->
                    <div onclick="Account.switchTab('addresses')" class="bg-white p-6 rounded-3xl border border-[#f59e0b]/20 shadow-sm flex items-center justify-between cursor-pointer hover:border-[#990024] hover:shadow-md transition">
                        <div>
                            <span class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Saved Addresses</span>
                            <span class="text-3xl font-black text-[#12090c] mt-1 block">${totalAddresses}</span>
                            <span class="text-xs text-[#990024] font-bold mt-1 inline-block hover:underline">Manage Addresses &rarr;</span>
                        </div>
                        <div class="w-14 h-14 bg-[#f59e0b]/15 text-[#f59e0b] rounded-2xl flex items-center justify-center text-xl border border-[#f59e0b]/30 shadow-sm">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                    </div>

                    <!-- Metric 3: Account Verification Status -->
                    <div class="bg-white p-6 rounded-3xl border border-[#f59e0b]/20 shadow-sm flex items-center justify-between">
                        <div>
                            <span class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Phone Verification</span>
                            <span class="text-lg font-black ${isPhoneVerified ? 'text-emerald-600' : 'text-amber-600'} mt-1 block">
                                ${isPhoneVerified ? 'Verified ✓' : 'Verification Required'}
                            </span>
                            ${!isPhoneVerified ? `
                                <button onclick="Auth.showPhoneOtpModal()" class="text-xs font-black text-white bg-[#990024] px-3 py-1 rounded-full mt-2 shadow-xs hover:bg-[#7a001c] transition">
                                    Verify 4-Digit OTP
                                </button>
                            ` : `<span class="text-xs text-gray-400 font-medium mt-1 block">Royal Club Security Active</span>`}
                        </div>
                        <div class="w-14 h-14 ${isPhoneVerified ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-amber-50 text-amber-600 border-amber-200'} rounded-2xl flex items-center justify-center text-xl border shadow-sm">
                            <i class="fas ${isPhoneVerified ? 'fa-user-check' : 'fa-shield-alt'}"></i>
                        </div>
                    </div>
                `;
            }
        } catch (e) {
            if (metricsGrid) metricsGrid.innerHTML = '';
        }
    },

    /**
     * Profile Tab Loading
     */
    async loadProfile() {
        const form = document.getElementById('frm-account-profile');
        if (!form) return;

        try {
            const res = await Api.get('/auth/me');
            if (res.success && res.user) {
                this.updateUserProfileHeader(res.user);

                document.getElementById('profile-first-name').value = res.user.first_name || '';
                document.getElementById('profile-last-name').value = res.user.last_name || '';
                document.getElementById('profile-email').value = res.user.email || '';
                document.getElementById('profile-phone').value = res.user.phone || '';

                if (!form.hasAttribute('data-bound')) {
                    form.setAttribute('data-bound', 'true');
                    form.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        const btn = form.querySelector('button[type="submit"]');
                        btn.disabled = true;
                        
                        try {
                            await Api.put('/profile', {
                                first_name: document.getElementById('profile-first-name').value,
                                last_name: document.getElementById('profile-last-name').value,
                                email: document.getElementById('profile-email').value,
                                phone: document.getElementById('profile-phone').value
                            });
                            Utils.showToast("Profile settings updated successfully!", "success");
                            
                            const refreshed = await Api.get('/auth/me');
                            localStorage.setItem('auth_user', JSON.stringify(refreshed.user));
                            this.updateUserProfileHeader(refreshed.user);
                        } catch (err) {
                            Utils.showToast(err.message || "Failed to update profile.", "error");
                        } finally {
                            btn.disabled = false;
                        }
                    });
                }
            }
        } catch (e) {
            Utils.showToast("Failed to fetch profile details: " + e.message, "error");
        }
    },

    /**
     * Addresses Tab Loading
     */
    async loadAddresses() {
        const list = document.getElementById('account-addresses-list');
        if (!list) return;

        list.innerHTML = `
            <div class="loader-spinner-dark mx-auto my-6"></div>
        `;

        try {
            const res = await Api.get('/addresses');
            if (res.success && res.data) {
                this.renderAddressesList(res.data);
            }
        } catch (e) {
            list.innerHTML = `<span class="text-xs text-red-500 font-semibold">Failed to fetch addresses</span>`;
        }

        // Bind address form submit (Add/Edit)
        const addrForm = document.getElementById('frm-account-address');
        if (addrForm && !addrForm.hasAttribute('data-bound')) {
            addrForm.setAttribute('data-bound', 'true');
            addrForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = addrForm.querySelector('button[type="submit"]');
                btn.disabled = true;

                const payload = {
                    type: document.getElementById('addr-type').value,
                    full_name: document.getElementById('addr-name').value,
                    phone: document.getElementById('addr-phone').value,
                    address_line1: document.getElementById('addr-line1').value,
                    address_line2: document.getElementById('addr-line2').value,
                    city: document.getElementById('addr-city').value,
                    state: document.getElementById('addr-state').value,
                    pincode: document.getElementById('addr-pincode').value,
                    is_default: document.getElementById('addr-default').checked ? 1 : 0
                };

                try {
                    if (this.editingAddressId) {
                        await Api.put('/addresses/' + this.editingAddressId, payload);
                        Utils.showToast("Address updated successfully!", "success");
                    } else {
                        await Api.post('/addresses', payload);
                        Utils.showToast("Address created successfully!", "success");
                    }
                    
                    this.resetAddressForm();
                    await this.loadAddresses();
                } catch (err) {
                    Utils.showToast(err.message || "Failed to save address.", "error");
                } finally {
                    btn.disabled = false;
                }
            });
        }
    },

    renderAddressesList(addresses) {
        const list = document.getElementById('account-addresses-list');
        if (!list) return;

        if (addresses.length === 0) {
            list.innerHTML = `
                <div class="text-center py-8 bg-gray-50 border border-dashed border-gray-200 rounded-xl">
                    <span class="text-xs text-gray-500 font-medium">No saved addresses. Add a new one below.</span>
                </div>
            `;
            return;
        }

        let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
        addresses.forEach(addr => {
            html += `
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 flex flex-col relative">
                    <div class="text-xs leading-relaxed text-gray-600 flex-grow">
                        <span class="font-bold text-gray-800 text-sm block mb-1">${addr.full_name} (${addr.type.toUpperCase()})</span>
                        <span class="block">${addr.address_line1}</span>
                        ${addr.address_line2 ? `<span class="block">${addr.address_line2}</span>` : ''}
                        <span class="block">${addr.city}, ${addr.state} - ${addr.pincode}</span>
                        <span class="block font-semibold mt-1"><i class="fas fa-phone mr-1"></i> ${addr.phone}</span>
                    </div>

                    <div class="mt-4 pt-3 border-t border-gray-200 flex justify-end space-x-3 text-xs">
                        <button onclick="Account.editAddress(${addr.id})" class="text-indigo-600 hover:text-indigo-800 font-semibold flex items-center"><i class="fas fa-edit mr-1"></i> Edit</button>
                        <button onclick="Account.deleteAddress(${addr.id})" class="text-red-500 hover:text-red-700 font-semibold flex items-center"><i class="fas fa-trash-alt mr-1"></i> Delete</button>
                    </div>

                    ${addr.is_default == 1 ? `
                        <span class="absolute top-4 right-4 bg-indigo-100 text-indigo-700 text-[10px] font-bold px-2 py-0.5 rounded">Default</span>
                    ` : ''}
                </div>
            `;
        });
        html += '</div>';
        list.innerHTML = html;
    },

    async editAddress(id) {
        try {
            const res = await Api.get('/addresses/' + id);
            if (res.success && res.data) {
                const addr = res.data;
                this.editingAddressId = addr.id;
                
                // Change header label
                document.getElementById('addr-form-title').innerText = "Edit Saved Address";
                document.getElementById('btn-cancel-edit-addr').classList.remove('hidden');

                // Populate
                document.getElementById('addr-type').value = addr.type;
                document.getElementById('addr-name').value = addr.full_name;
                document.getElementById('addr-phone').value = addr.phone;
                document.getElementById('addr-line1').value = addr.address_line1;
                document.getElementById('addr-line2').value = addr.address_line2 || '';
                document.getElementById('addr-city').value = addr.city;
                document.getElementById('addr-state').value = addr.state;
                document.getElementById('addr-pincode').value = addr.pincode;
                document.getElementById('addr-default').checked = addr.is_default == 1;

                // Scroll to form
                document.getElementById('account-address-form-sec').scrollIntoView({ behavior: 'smooth' });
            }
        } catch (e) {
            Utils.showToast("Failed to retrieve address details: " + e.message, "error");
        }
    },

    async deleteAddress(id) {
        if (!confirm("Are you sure you want to delete this address?")) return;

        try {
            await Api.delete('/addresses/' + id);
            Utils.showToast("Address deleted successfully.", "success");
            await this.loadAddresses();
        } catch (e) {
            Utils.showToast(e.message || "Failed to delete address.", "error");
        }
    },

    resetAddressForm() {
        this.editingAddressId = null;
        document.getElementById('addr-form-title').innerText = "Add New Address";
        document.getElementById('btn-cancel-edit-addr').classList.add('hidden');
        document.getElementById('frm-account-address').reset();
    },

    /**
     * Orders Tab Loading
     */
    async loadOrders() {
        const container = document.getElementById('account-orders-list');
        if (!container) return;

        container.innerHTML = `
            <div class="loader-spinner-dark mx-auto my-6"></div>
        `;

        try {
            const res = await Api.get('/orders');
            if (res.success && res.data) {
                this.renderOrdersList(res.data);
            }
        } catch (e) {
            container.innerHTML = `<span class="text-xs text-red-500 font-semibold">Failed to fetch order history</span>`;
        }
    },

    renderOrdersList(orders) {
        const container = document.getElementById('account-orders-list');
        if (!container) return;

        if (orders.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12 bg-gray-50 border border-dashed border-gray-200 rounded-xl">
                    <span class="text-xs text-gray-500 font-medium">You haven't placed any orders yet.</span>
                </div>
            `;
            return;
        }

        let html = `
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs text-left text-gray-500 border border-gray-100">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3.5">Order Number</th>
                            <th class="px-6 py-3.5">Date</th>
                            <th class="px-6 py-3.5">Payment</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5">Total</th>
                            <th class="px-6 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
        `;

        orders.forEach(ord => {
            const date = new Date(ord.created_at).toLocaleDateString('en-IN', {
                year: 'numeric', month: 'short', day: 'numeric'
            });

            // Status pills
            let statusClass = 'bg-gray-100 text-gray-800';
            if (ord.order_status === 'delivered') statusClass = 'bg-emerald-100 text-emerald-800';
            if (ord.order_status === 'pending') statusClass = 'bg-amber-100 text-amber-800';
            if (ord.order_status === 'shipped') statusClass = 'bg-blue-100 text-blue-800';
            if (ord.order_status === 'cancelled') statusClass = 'bg-red-100 text-red-800';

            let paymentStatusHtml = `<span class="px-6 py-4 font-semibold uppercase">${ord.payment_status}</span>`;
            if (ord.payment_status === 'pending_payment' || ord.payment_status === 'failed') {
                paymentStatusHtml = `<td class="px-6 py-4 font-semibold uppercase text-red-600">${ord.payment_status}</td>`;
            } else {
                paymentStatusHtml = `<td class="px-6 py-4 font-semibold uppercase text-emerald-600">${ord.payment_status}</td>`;
            }
            
            let actionHtml = `
                <a href="${BASE_URL}order-detail?id=${ord.id}" class="bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white font-semibold px-3 py-1.5 rounded-lg border border-indigo-100 hover:border-indigo-600 transition">
                    Details
                </a>
            `;

            if ((ord.payment_status === 'pending_payment' || ord.payment_status === 'pending' || ord.payment_status === 'failed') && ord.order_status !== 'expired' && ord.order_status !== 'cancelled') {
                 actionHtml = `
                    <a href="${BASE_URL}order-detail?id=${ord.id}&pay=1" class="bg-[#990024] hover:bg-[#7a001c] text-white font-semibold px-3 py-1.5 rounded-lg border border-[#990024] mr-2 transition">
                        Pay Online
                    </a>
                    ${actionHtml}
                 `;
            }

            html += `
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-bold text-gray-800">${ord.order_number}</td>
                    <td class="px-6 py-4 font-medium">${date}</td>
                    ${paymentStatusHtml}
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider text-[9px] ${statusClass}">
                            ${ord.order_status}
                        </span>
                    </td>
                    <td class="px-6 py-4 font-black text-gray-800">${Utils.formatCurrency(ord.total)}</td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        ${actionHtml}
                    </td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;
        container.innerHTML = html;
    },

    /**
     * Password Change Tab
     */
    bindPasswordForm() {
        const form = document.getElementById('frm-account-password');
        if (!form) return;

        if (!form.hasAttribute('data-bound')) {
            form.setAttribute('data-bound', 'true');
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const curPass = document.getElementById('pass-current').value;
                const newPass = document.getElementById('pass-new').value;
                const confPass = document.getElementById('pass-confirm').value;

                if (newPass !== confPass) {
                    Utils.showToast("New passwords do not match.", "error");
                    return;
                }

                const btn = form.querySelector('button[type="submit"]');
                btn.disabled = true;

                try {
                    await Api.post('/profile/change-password', {
                        current_password: curPass,
                        new_password: newPass
                    });
                    Utils.showToast("Password changed successfully!", "success");
                    form.reset();
                } catch (err) {
                    if (err.errors && err.errors.current_password) {
                        Utils.showToast(err.errors.current_password[0], "error");
                    } else {
                        Utils.showToast(err.message || "Failed to update password.", "error");
                    }
                } finally {
                    btn.disabled = false;
                }
            });
        }
    }
};

window.Account = Account;
document.addEventListener('DOMContentLoaded', () => {
    Account.init();
});
