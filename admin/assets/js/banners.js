/**
 * Admin Banners Management Module
 */

const Banners = {
    list: [],
    editingBannerId: null,

    async init() {
        await this.loadBannersTable();

        const form = document.getElementById('frm-banner-save');
        if (form) {
            form.addEventListener('submit', (e) => this.saveBanner(e));
        }
    },

    async loadBannersTable() {
        const tbody = document.getElementById('banners-tbody');
        if (!tbody) return;

        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-6 py-12 text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-2"></div>
                    <span class="block text-sm text-slate-500 dark:text-slate-400">Loading banners...</span>
                </td>
            </tr>
        `;

        try {
            const res = await Api.get('/admin/banners');
            if (res.success && res.data) {
                this.list = res.data;
                this.renderTable();
            }
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-red-500 font-bold">Failed to load banners.</td></tr>`;
        }
    },

    renderTable() {
        const tbody = document.getElementById('banners-tbody');
        if (!tbody) return;

        if (this.list.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center">
                        <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3 text-slate-400 dark:text-slate-500">
                            <i class="ph ph-image text-2xl"></i>
                        </div>
                        <p class="text-sm font-medium text-slate-900 dark:text-white">No banners added yet</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Upload a banner image to showcase promotions.</p>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        this.list.forEach(b => {
            const statusClass = b.status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20' : 'bg-slate-100 text-slate-700 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:ring-slate-700';
            const bannerImg = b.image_url ? (FRONTEND_BASE_URL + '/' + b.image_url) : 'https://placehold.co/120x60/F1F5F9/94A3B8?text=Banner';

            html += `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="w-24 h-12 rounded bg-slate-100 dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                            <img src="${bannerImg}" alt="${b.title}" class="w-full h-full object-cover">
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap min-w-0">
                        <div class="text-sm font-medium text-slate-900 dark:text-white truncate max-w-xs">${b.title}</div>
                        ${b.link_url ? `<div class="text-xs text-primary-600 dark:text-primary-400 mt-0.5 truncate max-w-xs flex items-center"><i class="ph ph-link mr-1"></i>${b.link_url}</div>` : `<div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 italic">No link</div>`}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ${statusClass}">
                            ${b.status.charAt(0).toUpperCase() + b.status.slice(1)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick="Banners.editBanner(${b.id})" class="p-1.5 text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors rounded focus:outline-none focus:ring-2 focus:ring-primary-500" title="Edit Banner">
                                <i class="ph ph-pencil-simple text-lg"></i>
                            </button>
                            <button onclick="Banners.deleteBanner(${b.id})" class="p-1.5 text-slate-400 hover:text-red-600 dark:hover:text-red-400 transition-colors rounded focus:outline-none focus:ring-2 focus:ring-red-500" title="Delete Banner">
                                <i class="ph ph-trash text-lg"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    },

    editBanner(id) {
        const b = this.list.find(item => parseInt(item.id) === parseInt(id));
        if (!b) return;

        this.editingBannerId = b.id;
        document.getElementById('banner-form-title').innerHTML = `<i class="ph ph-pencil-simple mr-2 text-slate-400"></i> Edit Promo Banner`;
        document.getElementById('btn-cancel-edit-banner').classList.remove('hidden');

        document.getElementById('banner-title').value = b.title;
        document.getElementById('banner-image').value = b.image_url;
        document.getElementById('banner-link').value = b.link_url || '';
        document.getElementById('banner-status').value = b.status;
    },

    async deleteBanner(id) {
        if (!confirm("Are you sure you want to delete this promotional banner?")) return;

        try {
            await Api.delete('/admin/banners/' + id);
            Utils.showToast("Banner deleted successfully.", "success");
            await this.loadBannersTable();
        } catch (e) {
            Utils.showToast(e.message || "Failed to delete banner.", "error");
        }
    },

    async saveBanner(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-save-banner');
        btn.disabled = true;

        const payload = {
            title: document.getElementById('banner-title').value.trim(),
            image_url: document.getElementById('banner-image').value.trim(),
            link_url: document.getElementById('banner-link').value.trim() || null,
            status: document.getElementById('banner-status').value
        };

        try {
            if (this.editingBannerId) {
                await Api.put('/admin/banners/' + this.editingBannerId, payload);
                Utils.showToast("Banner updated successfully!", "success");
            } else {
                await Api.post('/admin/banners', payload);
                Utils.showToast("Banner created successfully!", "success");
            }

            this.resetForm();
            await this.loadBannersTable();
        } catch (err) {
            Utils.showToast(err.message || "Failed to save banner.", "error");
        } finally {
            btn.disabled = false;
        }
    },

    resetForm() {
        this.editingBannerId = null;
        document.getElementById('banner-form-title').innerHTML = `<i class="ph ph-plus-circle mr-2 text-slate-400"></i> Add Banner`;
        document.getElementById('btn-cancel-edit-banner').classList.add('hidden');
        document.getElementById('frm-banner-save').reset();
    },

    async uploadImage(input) {
        if (!input.files || input.files.length === 0) return;
        const file = input.files[0];
        
        const textInput = document.getElementById('banner-image');
        const originalPlaceholder = textInput.placeholder;
        textInput.placeholder = "Uploading image, please wait...";
        textInput.value = "";

        const formData = new FormData();
        formData.append('file', file);
        formData.append('type', 'banner');

        try {
            const token = localStorage.getItem('admin_token');
            const resRaw = await fetch(API_BASE_URL + '/admin/media/upload', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                body: formData
            });
            const res = await resRaw.json();
            
            if (res.success && res.path) {
                textInput.value = res.path;
                Utils.showToast("Banner image uploaded successfully!", "success");
            } else {
                throw new Error(res.message || "Upload failed.");
            }
        } catch (e) {
            textInput.placeholder = originalPlaceholder;
            Utils.showToast("Failed to upload: " + e.message, "error");
        } finally {
            input.value = "";
        }
    }
};

window.Banners = Banners;
document.addEventListener('DOMContentLoaded', () => {
    Banners.init();
});
