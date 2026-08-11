/**
 * Admin Coupons Management Module
 */

const Coupons = {
    list: [],
    editingCouponId: null,

    async init() {
        await this.loadCouponsTable();

        const form = document.getElementById('frm-coupon-save');
        if (form) {
            form.addEventListener('submit', (e) => this.saveCoupon(e));
        }
    },

    async loadCouponsTable() {
        const tbody = document.getElementById('coupons-tbody');
        if (!tbody) return;

        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-12 text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-2"></div>
                    <span class="block text-sm text-slate-500 dark:text-slate-400">Loading coupons...</span>
                </td>
            </tr>
        `;

        try {
            const res = await Api.get('/admin/coupons');
            if (res.success && res.data) {
                this.list = res.data;
                this.renderTable();
            }
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-red-500 font-bold">Failed to load coupons.</td></tr>`;
        }
    },

    renderTable() {
        const tbody = document.getElementById('coupons-tbody');
        if (!tbody) return;

        if (this.list.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3 text-slate-400 dark:text-slate-500">
                            <i class="ph ph-ticket text-2xl"></i>
                        </div>
                        <p class="text-sm font-medium text-slate-900 dark:text-white">No coupons active</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Create a new coupon to offer discounts.</p>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        this.list.forEach(c => {
            const expiry = c.expiry_date ? new Date(c.expiry_date).toLocaleDateString('en-IN') : 'Never';
            const value = c.type === 'percentage' ? `${parseFloat(c.value)}%` : Utils.formatCurrency(c.value);
            let statusClass = parseInt(c.is_active) === 1 ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20' : 'bg-red-50 text-red-700 ring-1 ring-red-600/20 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/20';

            html += `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="inline-flex items-center px-2.5 py-1 rounded border border-dashed border-primary-300 dark:border-primary-700 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-mono font-bold text-sm tracking-wide">
                            ${c.code}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                        <div class="text-sm font-bold text-slate-900 dark:text-white">${value} <span class="font-normal text-slate-500 dark:text-slate-400">Off</span></div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 capitalize">${c.type.replace('_', ' ')}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                        <div class="text-sm text-slate-900 dark:text-white">Min spend: ${Utils.formatCurrency(c.min_cart_value)}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center">
                            <i class="ph ph-clock mr-1"></i> Expires: ${expiry}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ${statusClass}">
                            ${parseInt(c.is_active) === 1 ? 'Active' : 'Disabled'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick="Coupons.editCoupon(${c.id})" class="p-1.5 text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors rounded focus:outline-none focus:ring-2 focus:ring-primary-500" title="Edit Coupon">
                                <i class="ph ph-pencil-simple text-lg"></i>
                            </button>
                            <button onclick="Coupons.deleteCoupon(${c.id})" class="p-1.5 text-slate-400 hover:text-red-600 dark:hover:text-red-400 transition-colors rounded focus:outline-none focus:ring-2 focus:ring-red-500" title="Delete Coupon">
                                <i class="ph ph-trash text-lg"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    },

    editCoupon(id) {
        const c = this.list.find(item => parseInt(item.id) === parseInt(id));
        if (!c) return;

        this.editingCouponId = c.id;
        document.getElementById('coupon-form-title').innerHTML = `<i class="ph ph-pencil-simple mr-2 text-slate-400"></i> Edit Coupon`;
        document.getElementById('btn-cancel-edit-coupon').classList.remove('hidden');

        document.getElementById('coupon-code').value = c.code;
        document.getElementById('coupon-type').value = c.type;
        document.getElementById('coupon-value').value = c.value;
        document.getElementById('coupon-min-val').value = c.min_cart_value;
        document.getElementById('coupon-max-disc').value = c.max_discount || '';
        document.getElementById('coupon-limit').value = c.usage_limit || '';
        document.getElementById('coupon-expiry').value = c.expiry_date || '';
        document.getElementById('coupon-active').checked = parseInt(c.is_active) === 1;
    },

    async deleteCoupon(id) {
        if (!confirm("Are you sure you want to delete this coupon code permanently?")) return;

        try {
            await Api.delete('/admin/coupons/' + id);
            Utils.showToast("Coupon deleted successfully.", "success");
            await this.loadCouponsTable();
        } catch (e) {
            Utils.showToast(e.message || "Failed to delete coupon.", "error");
        }
    },

    async saveCoupon(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-save-coupon');
        btn.disabled = true;

        const payload = {
            code: document.getElementById('coupon-code').value.toUpperCase().trim(),
            type: document.getElementById('coupon-type').value,
            value: parseFloat(document.getElementById('coupon-value').value) || 0.00,
            min_cart_value: parseFloat(document.getElementById('coupon-min-val').value) || 0.00,
            max_discount: parseFloat(document.getElementById('coupon-max-disc').value) || null,
            usage_limit: parseInt(document.getElementById('coupon-limit').value) || null,
            expiry_date: document.getElementById('coupon-expiry').value || null,
            is_active: document.getElementById('coupon-active').checked ? 1 : 0
        };

        try {
            if (this.editingCouponId) {
                await Api.put('/admin/coupons/' + this.editingCouponId, payload);
                Utils.showToast("Coupon updated successfully!", "success");
            } else {
                await Api.post('/admin/coupons', payload);
                Utils.showToast("Coupon created successfully!", "success");
            }

            this.resetForm();
            await this.loadCouponsTable();
        } catch (err) {
            Utils.showToast(err.message || "Failed to save coupon details.", "error");
        } finally {
            btn.disabled = false;
        }
    },

    resetForm() {
        this.editingCouponId = null;
        document.getElementById('coupon-form-title').innerHTML = `<i class="ph ph-plus-circle mr-2 text-slate-400"></i> Create Coupon`;
        document.getElementById('btn-cancel-edit-coupon').classList.add('hidden');
        document.getElementById('frm-coupon-save').reset();
    }
};

window.Coupons = Coupons;
document.addEventListener('DOMContentLoaded', () => {
    Coupons.init();
});
