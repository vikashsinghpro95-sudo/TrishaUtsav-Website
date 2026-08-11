<?php
// admin/brands.php
include_once __DIR__ . '/includes/admin-header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Brands</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage product brands and their associated logos.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
    <!-- Brands List (Left side) -->
    <div class="lg:col-span-2 bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                <i class="ph ph-copyright mr-2 text-slate-400"></i> Brands Directory
            </h3>
        </div>

        <div class="overflow-x-auto min-h-[300px]">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-900/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Brand Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:table-cell">Slug</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24">Actions</th>
                    </tr>
                </thead>
                <tbody id="brands-tbody" class="bg-white dark:bg-slate-850 divide-y divide-slate-200 dark:divide-slate-800">
                    <!-- Loaded dynamically -->
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-2"></div>
                            <span class="block text-sm text-slate-500 dark:text-slate-400">Loading brands...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Brand Form (Right side) -->
    <div class="lg:col-span-1">
        <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden sticky top-24">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
                <h3 id="brand-form-title" class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-plus-circle mr-2 text-slate-400"></i> Add Brand
                </h3>
                <button id="btn-cancel-edit-brand" onclick="Brands.resetForm()" class="hidden text-xs font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 px-2.5 py-1 rounded transition-colors">
                    Cancel
                </button>
            </div>

            <form id="frm-brand-save" class="p-5 space-y-4">
                <div>
                    <label for="brand-name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Brand Name</label>
                    <input type="text" id="brand-name" required placeholder="e.g. Apple, Samsung" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                </div>

                <div>
                    <label for="brand-slug" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Slug</label>
                    <input type="text" id="brand-slug" placeholder="e.g. apple" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Brand Logo</label>
                    <div class="flex items-center space-x-3">
                        <input type="file" id="brand-logo-file" accept="image/*" class="hidden" onchange="Brands.uploadLogo(this)">
                        <button type="button" onclick="document.getElementById('brand-logo-file').click()" class="inline-flex items-center px-3 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors whitespace-nowrap">
                            <i class="ph ph-upload mr-2"></i> Upload
                        </button>
                        <input type="text" id="brand-logo" readonly class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 placeholder-slate-400 sm:text-sm transition-colors" placeholder="No file uploaded">
                    </div>
                </div>

                <div>
                    <label for="brand-status" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                    <select id="brand-status" required class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors cursor-pointer">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="pt-2">
                    <button type="submit" id="btn-save-brand" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                        Save Brand
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const Brands = {
        list: [],
        editingBrandId: null,

        async init() {
            await this.loadBrands();
            document.getElementById('frm-brand-save').addEventListener('submit', (e) => this.saveBrand(e));
        },

        async loadBrands() {
            const tbody = document.getElementById('brands-tbody');
            if (!tbody) return;

            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-2"></div>
                    </td>
                </tr>
            `;

            try {
                const res = await Api.get('/brands');
                if (res.success && res.data) {
                    this.list = res.data;
                    this.renderTable();
                }
            } catch (e) {
                tbody.innerHTML = `<tr><td colspan="4" class="px-6 py-8 text-center text-red-500 font-medium">Failed to load brands.</td></tr>`;
            }
        },

        renderTable() {
            const tbody = document.getElementById('brands-tbody');
            if (!tbody) return;

            if (this.list.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3 text-slate-400 dark:text-slate-500">
                                <i class="ph ph-copyright text-2xl"></i>
                            </div>
                            <p class="text-sm font-medium text-slate-900 dark:text-white">No brands found</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Get started by creating a new brand.</p>
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            this.list.forEach(brand => {
                let statusClass = 'bg-red-50 text-red-700 ring-1 ring-red-600/20 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/20';
                if (brand.status === 'active') {
                    statusClass = 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20';
                }
                
                const logoImg = brand.logo ? (FRONTEND_BASE_URL + '/' + brand.logo.replace(/^\//, '')) : 'https://placehold.co/80x40/F8FAFC/94A3B8?text=Brand';

                html += `
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-16 flex-shrink-0 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded p-1 flex items-center justify-center">
                                    <img class="max-h-full max-w-full object-contain" src="${logoImg}" alt="${brand.name}">
                                </div>
                                <div class="ml-4 min-w-0">
                                    <div class="text-sm font-medium text-slate-900 dark:text-white truncate">${brand.name}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                            <div class="text-sm text-slate-500 dark:text-slate-400">/${brand.slug}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ${statusClass}">
                                ${brand.status.charAt(0).toUpperCase() + brand.status.slice(1)}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="Brands.editBrand(${brand.id})" class="p-1.5 text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors rounded focus:outline-none focus:ring-2 focus:ring-primary-500" title="Edit Brand">
                                    <i class="ph ph-pencil-simple text-lg"></i>
                                </button>
                                <button onclick="Brands.deleteBrand(${brand.id})" class="p-1.5 text-slate-400 hover:text-red-600 dark:hover:text-red-400 transition-colors rounded focus:outline-none focus:ring-2 focus:ring-red-500" title="Delete Brand">
                                    <i class="ph ph-trash text-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        },

        editBrand(id) {
            const brand = this.list.find(b => parseInt(b.id) === parseInt(id));
            if (!brand) return;

            this.editingBrandId = brand.id;
            document.getElementById('brand-form-title').innerHTML = `<i class="ph ph-pencil-simple mr-2 text-slate-400"></i> Edit Brand`;
            document.getElementById('btn-cancel-edit-brand').classList.remove('hidden');

            document.getElementById('brand-name').value = brand.name;
            document.getElementById('brand-slug').value = brand.slug;
            document.getElementById('brand-status').value = brand.status;
            document.getElementById('brand-logo').value = brand.logo || '';
            
            // Scroll to form on mobile
            if(window.innerWidth < 1024) {
                document.getElementById('frm-brand-save').scrollIntoView({behavior: 'smooth'});
            }
        },

        async deleteBrand(id) {
            if (!confirm("Are you sure you want to delete this brand? Products linked to this brand will lose their association.")) return;

            try {
                await Api.delete('/admin/brands/' + id);
                if(window.Utils && Utils.showToast) Utils.showToast("Brand deleted successfully.", "success");
                await this.loadBrands();
            } catch (e) {
                if(window.Utils && Utils.showToast) Utils.showToast(e.message || "Failed to delete brand.", "error");
            }
        },

        async saveBrand(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-save-brand');
            btn.disabled = true;

            const name = document.getElementById('brand-name').value;
            let slug = document.getElementById('brand-slug').value;
            if (!slug) slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-');

            const payload = {
                name: name,
                slug: slug,
                status: document.getElementById('brand-status').value,
                logo: document.getElementById('brand-logo').value || null
            };

            try {
                if (this.editingBrandId) {
                    await Api.put('/admin/brands/' + this.editingBrandId, payload);
                    if(window.Utils && Utils.showToast) Utils.showToast("Brand updated successfully!", "success");
                } else {
                    await Api.post('/admin/brands', payload);
                    if(window.Utils && Utils.showToast) Utils.showToast("Brand created successfully!", "success");
                }

                this.resetForm();
                await this.loadBrands();
            } catch (err) {
                if(window.Utils && Utils.showToast) Utils.showToast(err.message || "Failed to save brand.", "error");
            } finally {
                btn.disabled = false;
            }
        },

        resetForm() {
            this.editingBrandId = null;
            document.getElementById('brand-form-title').innerHTML = `<i class="ph ph-plus-circle mr-2 text-slate-400"></i> Add Brand`;
            document.getElementById('btn-cancel-edit-brand').classList.add('hidden');
            document.getElementById('frm-brand-save').reset();
            document.getElementById('brand-logo').value = '';
        },

        async uploadLogo(input) {
            if (!input.files || input.files.length === 0) return;
            const file = input.files[0];
            
            const textInput = document.getElementById('brand-logo');
            const originalPlaceholder = textInput.placeholder;
            textInput.placeholder = "Uploading logo...";
            textInput.value = "";

            const formData = new FormData();
            formData.append('file', file);
            formData.append('type', 'brand');

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
                    if(window.Utils && Utils.showToast) Utils.showToast("Logo uploaded successfully!", "success");
                } else {
                    throw new Error(res.message || "Upload failed.");
                }
            } catch (e) {
                textInput.placeholder = originalPlaceholder;
                if(window.Utils && Utils.showToast) Utils.showToast("Failed to upload: " + e.message, "error");
            } finally {
                input.value = "";
            }
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        Brands.init();
    });
</script>

<?php
include_once __DIR__ . '/includes/admin-footer.php';
?>
