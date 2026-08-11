<?php
// admin/occasions.php
include_once __DIR__ . '/includes/admin-header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Festive Occasions</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage occasion collections (Diwali Delights, Weddings, etc.).</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="AdminOccasions.openCreateModal()" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors shadow-sm">
            <i class="ph ph-plus mr-2"></i> Add New Occasion
        </button>
    </div>
</div>

<div class="space-y-6">
    <!-- Occasions Table / Grid -->
    <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto min-h-[400px]">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-900/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Occasion Name & Banner</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">URL Slug</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:table-cell">Sort Order</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24">Actions</th>
                    </tr>
                </thead>
                <tbody id="admin-occasions-tbody" class="bg-white dark:bg-slate-850 divide-y divide-slate-200 dark:divide-slate-800">
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-2"></div>
                            <span class="block text-sm text-slate-500 dark:text-slate-400">Loading occasions...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Create / Edit Occasion -->
<div id="modal-occasion" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden opacity-0 transition-opacity duration-300">
    <div class="bg-white dark:bg-slate-850 rounded-2xl max-w-md w-full shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300" id="modal-occasion-content">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-900/50">
            <h3 id="modal-occasion-title" class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                <i class="ph ph-confetti mr-2 text-slate-400"></i> Add New Occasion
            </h3>
            <button onclick="AdminOccasions.closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors focus:outline-none">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <form id="frm-occasion" onsubmit="AdminOccasions.save(event)" class="p-6 space-y-4">
            <input type="hidden" id="occasion-id">

            <div>
                <label for="occasion-name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Occasion Name</label>
                <input type="text" id="occasion-name" required placeholder="e.g. Diwali Delights" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
            </div>

            <div>
                <label for="occasion-slug" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">URL Slug</label>
                <input type="text" id="occasion-slug" placeholder="e.g. diwali-delights" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors font-mono">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Background Image</label>
                <div class="flex items-center space-x-3">
                    <input type="file" id="occasion-image-file" accept="image/*" class="hidden" onchange="AdminOccasions.uploadImage(this)">
                    <button type="button" onclick="document.getElementById('occasion-image-file').click()" class="inline-flex items-center px-3 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors whitespace-nowrap">
                        <i class="ph ph-upload-simple mr-2"></i> Upload Banner
                    </button>
                    <input type="text" id="occasion-image-url" readonly class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 placeholder-slate-400 sm:text-sm transition-colors" placeholder="No file uploaded">
                </div>
            </div>

            <div id="occasion-preview-box" class="hidden border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden aspect-[16/9] relative bg-slate-900">
                <img id="occasion-img-preview" class="w-full h-full object-cover" src="">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 to-transparent p-3 flex items-end">
                    <span id="occasion-preview-name" class="text-white text-sm font-bold">Preview</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="occasion-sort-order" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Sort Order</label>
                    <input type="number" id="occasion-sort-order" value="0" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                </div>
                <div>
                    <label for="occasion-status" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                    <select id="occasion-status" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors cursor-pointer">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="AdminOccasions.closeModal()" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors focus:outline-none">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium bg-primary-600 hover:bg-primary-700 text-white transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">Save Occasion</button>
            </div>
        </form>
    </div>
</div>

<script>
const AdminOccasions = {
    async init() {
        await this.load();
    },

    async load() {
        const tbody = document.getElementById('admin-occasions-tbody');
        try {
            const res = await Api.get('/admin/occasions');
            if (res.success && res.data) {
                if (res.data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3 text-slate-400 dark:text-slate-500">
                                    <i class="ph ph-confetti text-2xl"></i>
                                </div>
                                <p class="text-sm font-medium text-slate-900 dark:text-white">No occasions added yet</p>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Create an occasion to group related products.</p>
                            </td>
                        </tr>
                    `;
                    return;
                }

                let html = '';
                res.data.forEach(o => {
                    const banner = o.image_url ? Utils.fixImageUrl(o.image_url) : 'https://placehold.co/120x60/F1F5F9/94A3B8?text=Occasion';
                    const statusClass = o.status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20' : 'bg-slate-100 text-slate-700 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:ring-slate-700';

                    html += `
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-16 flex-shrink-0 rounded overflow-hidden bg-slate-100 dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                                        <img src="${banner}" class="h-full w-full object-cover" alt="">
                                    </div>
                                    <div class="ml-4 min-w-0">
                                        <div class="text-sm font-medium text-slate-900 dark:text-white truncate">${o.name}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-mono">ID #${o.id}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                                <span class="text-sm text-slate-500 dark:text-slate-400 font-mono">${o.slug}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                                <span class="text-sm font-semibold text-slate-900 dark:text-white">${o.sort_order || 0}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ${statusClass}">
                                    ${o.status.charAt(0).toUpperCase() + o.status.slice(1)}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="AdminOccasions.edit(${o.id})" class="p-1.5 text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors rounded focus:outline-none focus:ring-2 focus:ring-primary-500" title="Edit">
                                        <i class="ph ph-pencil-simple text-lg"></i>
                                    </button>
                                    <button onclick="AdminOccasions.remove(${o.id})" class="p-1.5 text-slate-400 hover:text-red-600 dark:hover:text-red-400 transition-colors rounded focus:outline-none focus:ring-2 focus:ring-red-500" title="Delete">
                                        <i class="ph ph-trash text-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
            }
        } catch(e) {
            tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-red-500 font-medium">Failed to load occasions.</td></tr>`;
        }
    },

    openCreateModal() {
        document.getElementById('modal-occasion-title').innerHTML = `<i class="ph ph-confetti mr-2 text-slate-400"></i> Add New Occasion`;
        document.getElementById('occasion-id').value = '';
        document.getElementById('frm-occasion').reset();
        document.getElementById('occasion-preview-box').classList.add('hidden');
        
        const modal = document.getElementById('modal-occasion');
        const content = document.getElementById('modal-occasion-content');
        modal.classList.remove('hidden');
        void modal.offsetWidth;
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-95');
    },

    closeModal() {
        const modal = document.getElementById('modal-occasion');
        const content = document.getElementById('modal-occasion-content');
        modal.classList.add('opacity-0');
        content.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    },

    async uploadImage(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        
        if (file.size > 30 * 1024 * 1024) {
            if(window.Utils && Utils.showToast) Utils.showToast("Selected image size (" + (file.size / (1024 * 1024)).toFixed(1) + "MB) exceeds the 30MB limit.", "error");
            return;
        }

        const formData = new FormData();
        formData.append('file', file);
        formData.append('type', 'occasion');

        const inputUrl = document.getElementById('occasion-image-url');
        inputUrl.placeholder = "Uploading image...";

        try {
            const res = await Api.uploadFile('/admin/media/upload', formData);
            if (res.success && res.path) {
                inputUrl.value = res.path;
                const previewImg = document.getElementById('occasion-img-preview');
                previewImg.src = Utils.fixImageUrl(res.path);
                document.getElementById('occasion-preview-name').innerText = document.getElementById('occasion-name').value || 'Preview';
                document.getElementById('occasion-preview-box').classList.remove('hidden');
                if(window.Utils && Utils.showToast) Utils.showToast('Background banner uploaded successfully!', 'success');
            }
        } catch(e) {
            inputUrl.placeholder = "No file uploaded";
            if(window.Utils && Utils.showToast) Utils.showToast(e.message || 'Upload failed', 'error');
        }
    },

    async save(e) {
        e.preventDefault();
        const id = document.getElementById('occasion-id').value;
        const payload = {
            name: document.getElementById('occasion-name').value.trim(),
            slug: document.getElementById('occasion-slug').value.trim() || document.getElementById('occasion-name').value.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-'),
            image_url: document.getElementById('occasion-image-url').value.trim(),
            sort_order: parseInt(document.getElementById('occasion-sort-order').value) || 0,
            status: document.getElementById('occasion-status').value
        };

        try {
            const res = id ? await Api.put('/admin/occasions/' + id, payload) : await Api.post('/admin/occasions', payload);
            if (res.success) {
                if(window.Utils && Utils.showToast) Utils.showToast(res.message || 'Occasion saved successfully!', 'success');
                this.closeModal();
                await this.load();
            }
        } catch(err) {
            if(window.Utils && Utils.showToast) Utils.showToast(err.message || 'Operation failed', 'error');
        }
    },

    async edit(id) {
        try {
            const res = await Api.get('/admin/occasions');
            if (res.success && res.data) {
                const o = res.data.find(item => parseInt(item.id) === parseInt(id));
                if (o) {
                    document.getElementById('modal-occasion-title').innerHTML = `<i class="ph ph-pencil-simple mr-2 text-slate-400"></i> Edit Occasion`;
                    document.getElementById('occasion-id').value = o.id;
                    document.getElementById('occasion-name').value = o.name;
                    document.getElementById('occasion-slug').value = o.slug;
                    document.getElementById('occasion-image-url').value = o.image_url || '';
                    document.getElementById('occasion-sort-order').value = o.sort_order || 0;
                    document.getElementById('occasion-status').value = o.status || 'active';

                    if (o.image_url) {
                        const previewImg = document.getElementById('occasion-img-preview');
                        previewImg.src = Utils.fixImageUrl(o.image_url);
                        document.getElementById('occasion-preview-name').innerText = o.name;
                        document.getElementById('occasion-preview-box').classList.remove('hidden');
                    } else {
                        document.getElementById('occasion-preview-box').classList.add('hidden');
                    }

                    const modal = document.getElementById('modal-occasion');
                    const content = document.getElementById('modal-occasion-content');
                    modal.classList.remove('hidden');
                    void modal.offsetWidth;
                    modal.classList.remove('opacity-0');
                    content.classList.remove('scale-95');
                }
            }
        } catch(e) {}
    },

    async remove(id) {
        if (!confirm('Are you sure you want to delete this occasion?')) return;
        try {
            const res = await Api.delete('/admin/occasions/' + id);
            if (res.success) {
                if(window.Utils && Utils.showToast) Utils.showToast('Occasion deleted.', 'success');
                await this.load();
            }
        } catch(e) {
            if(window.Utils && Utils.showToast) Utils.showToast(e.message || 'Delete failed', 'error');
        }
    }
};

document.addEventListener('DOMContentLoaded', () => AdminOccasions.init());
</script>

<?php
include_once __DIR__ . '/includes/admin-footer.php';
?>
