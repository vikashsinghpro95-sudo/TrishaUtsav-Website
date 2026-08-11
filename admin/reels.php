<?php
// admin/reels.php
include_once __DIR__ . '/includes/admin-header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Insta Reels</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage vertical product video reels for the homepage.</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="AdminReels.openCreateModal()" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors shadow-sm">
            <i class="ph ph-plus mr-2"></i> Add New Reel
        </button>
    </div>
</div>

<div class="space-y-6">
    <!-- Reels Grid -->
    <div id="admin-reels-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
        <div class="col-span-full text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-2"></div>
            <span class="block text-sm text-slate-500 dark:text-slate-400">Loading reels...</span>
        </div>
    </div>
</div>

<!-- Modal Create / Edit Reel -->
<div id="modal-reel" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden opacity-0 transition-opacity duration-300">
    <div class="bg-white dark:bg-slate-850 rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300" id="modal-reel-content">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-900/50">
            <h3 id="modal-reel-title" class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                <i class="ph ph-video-camera mr-2 text-slate-400"></i> Add New Reel
            </h3>
            <button onclick="AdminReels.closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors focus:outline-none">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <form id="frm-reel" onsubmit="AdminReels.save(event)" class="p-6 space-y-4">
            <input type="hidden" id="reel-id">

            <div>
                <label for="reel-title-input" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Reel Title</label>
                <input type="text" id="reel-title-input" required placeholder="e.g. iPhone 16 Pro Unboxing" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Vertical Video File (9:16)</label>
                <div class="flex items-center space-x-3">
                    <input type="file" id="reel-video-file" accept="video/mp4,video/webm" class="hidden" onchange="AdminReels.uploadVideo(this)">
                    <button type="button" onclick="document.getElementById('reel-video-file').click()" class="inline-flex items-center px-3 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors whitespace-nowrap">
                        <i class="ph ph-upload-simple mr-2"></i> Upload Video
                    </button>
                    <input type="text" id="reel-video-url" required readonly class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 placeholder-slate-400 sm:text-sm transition-colors" placeholder="No file uploaded">
                </div>
            </div>

            <div>
                <label for="reel-product-id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Linked Product <span class="text-slate-400 font-normal">(Optional)</span></label>
                <select id="reel-product-id" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors cursor-pointer">
                    <option value="">-- Select Product --</option>
                </select>
            </div>

            <div>
                <label for="reel-caption" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Caption / Hashtags</label>
                <textarea id="reel-caption" rows="2" placeholder="e.g. Unboxing the festive tech! ✨ #TrishaUtsav" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors resize-none"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="reel-sort-order" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Sort Order</label>
                    <input type="number" id="reel-sort-order" value="0" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                </div>
                <div>
                    <label for="reel-status" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                    <select id="reel-status" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors cursor-pointer">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="AdminReels.closeModal()" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors focus:outline-none">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium bg-primary-600 hover:bg-primary-700 text-white transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">Save Reel</button>
            </div>
        </form>
    </div>
</div>

<script>
const AdminReels = {
    products: [],

    async init() {
        await this.loadProducts();
        await this.loadReels();
    },

    async loadProducts() {
        try {
            const res = await Api.get('/admin/products?per_page=100');
            if (res.success && res.data) {
                this.products = res.data;
                const select = document.getElementById('reel-product-id');
                let html = '<option value="">-- Select Product --</option>';
                res.data.forEach(p => {
                    html += `<option value="${p.id}">${p.name} (₹${p.price})</option>`;
                });
                select.innerHTML = html;
            }
        } catch(e) {}
    },

    async loadReels() {
        const grid = document.getElementById('admin-reels-grid');
        try {
            const res = await Api.get('/admin/reels');
            if (res.success && res.data) {
                if (res.data.length === 0) {
                    grid.innerHTML = `
                        <div class="col-span-full text-center py-12">
                            <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3 text-slate-400 dark:text-slate-500">
                                <i class="ph ph-video-camera text-2xl"></i>
                            </div>
                            <p class="text-sm font-medium text-slate-900 dark:text-white">No reels created yet</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Click "Add New Reel" to upload vertical videos.</p>
                        </div>
                    `;
                    return;
                }

                let html = '';
                res.data.forEach(r => {
                    const videoSrc = r.video_url.startsWith('http') ? r.video_url : (FRONTEND_BASE_URL + r.video_url);
                    const statusClass = r.status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20' : 'bg-slate-100 text-slate-700 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:ring-slate-700';

                    html += `
                        <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col group transition-shadow hover:shadow-md">
                            <div class="relative bg-slate-900 aspect-[9/16] overflow-hidden flex items-center justify-center">
                                <video src="${videoSrc}" muted loop class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"></video>
                                <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-transparent to-black/60"></div>
                                <span class="absolute top-3 left-3 bg-white/20 backdrop-blur-md text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider flex items-center border border-white/10">
                                    <i class="ph-fill ph-play-circle text-white mr-1 text-xs"></i> Reel
                                </span>
                                <span class="absolute top-3 right-3 ${statusClass} text-[9px] font-bold px-2 py-0.5 rounded-full uppercase border-none bg-white/90 dark:bg-slate-900/90 shadow-sm">
                                    ${r.status}
                                </span>
                                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-md border border-white/30 flex items-center justify-center text-white hover:bg-white/30 transition-colors">
                                        <i class="ph-fill ph-play text-xl"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-4 space-y-3 flex-grow flex flex-col justify-between">
                                <div>
                                    <h4 class="font-semibold text-slate-900 dark:text-white text-sm line-clamp-1">${r.title}</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mt-1 leading-relaxed">${r.caption || 'No caption'}</p>
                                    ${r.product_name ? `<div class="mt-3"><span class="inline-flex items-center px-2 py-1 rounded bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 text-[10px] font-medium"><i class="ph ph-tag mr-1 text-slate-400"></i>${r.product_name}</span></div>` : ''}
                                </div>
                                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center gap-2">
                                    <button onclick="AdminReels.edit(${r.id})" class="flex-1 inline-flex justify-center items-center px-3 py-1.5 border border-slate-200 dark:border-slate-700 rounded-md text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                        <i class="ph ph-pencil-simple mr-1.5"></i> Edit
                                    </button>
                                    <button onclick="AdminReels.remove(${r.id})" class="inline-flex justify-center items-center px-3 py-1.5 border border-transparent rounded-md text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                grid.innerHTML = html;
            }
        } catch(e) {
            grid.innerHTML = `<div class="col-span-full text-center text-red-500 text-sm py-8">Failed to load reels.</div>`;
        }
    },

    openCreateModal() {
        document.getElementById('modal-reel-title').innerHTML = `<i class="ph ph-video-camera mr-2 text-slate-400"></i> Add New Reel`;
        document.getElementById('reel-id').value = '';
        document.getElementById('frm-reel').reset();
        const modal = document.getElementById('modal-reel');
        const content = document.getElementById('modal-reel-content');
        modal.classList.remove('hidden');
        // Trigger reflow
        void modal.offsetWidth;
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-95');
    },

    closeModal() {
        const modal = document.getElementById('modal-reel');
        const content = document.getElementById('modal-reel-content');
        modal.classList.add('opacity-0');
        content.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    },

    async uploadVideo(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const formData = new FormData();
        formData.append('file', file);
        formData.append('type', 'reel');

        const inputUrl = document.getElementById('reel-video-url');
        inputUrl.placeholder = "Uploading video...";
        
        try {
            const res = await Api.uploadFile('/admin/media/upload', formData);
            if (res.success) {
                inputUrl.value = res.path;
                if(window.Utils && Utils.showToast) Utils.showToast('Video uploaded successfully!', 'success');
            }
        } catch(e) {
            inputUrl.placeholder = "No file uploaded";
            if(window.Utils && Utils.showToast) Utils.showToast(e.message || 'Video upload failed', 'error');
        }
    },

    async save(e) {
        e.preventDefault();
        const id = document.getElementById('reel-id').value;
        const payload = {
            title: document.getElementById('reel-title-input').value,
            video_url: document.getElementById('reel-video-url').value,
            product_id: document.getElementById('reel-product-id').value || null,
            caption: document.getElementById('reel-caption').value,
            sort_order: document.getElementById('reel-sort-order').value || 0,
            status: document.getElementById('reel-status').value
        };

        try {
            const res = id ? await Api.put('/admin/reels/' + id, payload) : await Api.post('/admin/reels', payload);
            if (res.success) {
                if(window.Utils && Utils.showToast) Utils.showToast(res.message || 'Saved successfully!', 'success');
                this.closeModal();
                await this.loadReels();
            }
        } catch(err) {
            if(window.Utils && Utils.showToast) Utils.showToast(err.message || 'Operation failed', 'error');
        }
    },

    async edit(id) {
        try {
            const res = await Api.get('/admin/reels');
            if (res.success && res.data) {
                const r = res.data.find(item => parseInt(item.id) === parseInt(id));
                if (r) {
                    document.getElementById('modal-reel-title').innerHTML = `<i class="ph ph-pencil-simple mr-2 text-slate-400"></i> Edit Reel`;
                    document.getElementById('reel-id').value = r.id;
                    document.getElementById('reel-title-input').value = r.title;
                    document.getElementById('reel-video-url').value = r.video_url;
                    document.getElementById('reel-product-id').value = r.product_id || '';
                    document.getElementById('reel-caption').value = r.caption || '';
                    document.getElementById('reel-sort-order').value = r.sort_order || 0;
                    document.getElementById('reel-status').value = r.status || 'active';
                    
                    const modal = document.getElementById('modal-reel');
                    const content = document.getElementById('modal-reel-content');
                    modal.classList.remove('hidden');
                    void modal.offsetWidth;
                    modal.classList.remove('opacity-0');
                    content.classList.remove('scale-95');
                }
            }
        } catch(e) {}
    },

    async remove(id) {
        if (!confirm('Are you sure you want to delete this reel?')) return;
        try {
            const res = await Api.delete('/admin/reels/' + id);
            if (res.success) {
                if(window.Utils && Utils.showToast) Utils.showToast('Reel deleted successfully.', 'success');
                await this.loadReels();
            }
        } catch(e) {
            if(window.Utils && Utils.showToast) Utils.showToast(e.message || 'Delete failed', 'error');
        }
    }
};

document.addEventListener('DOMContentLoaded', () => AdminReels.init());
</script>

<?php
include_once __DIR__ . '/includes/admin-footer.php';
?>
