/**
 * Admin Static Pages CMS Module
 */

const Pages = {
    list: [],
    editingPageId: null,

    async init() {
        await this.loadPagesTable();

        const form = document.getElementById('frm-page-save');
        if (form) {
            form.addEventListener('submit', (e) => this.savePage(e));
        }
    },

    async loadPagesTable() {
        const tbody = document.getElementById('pages-tbody');
        if (!tbody) return;

        tbody.innerHTML = `
            <tr>
                <td colspan="3" class="px-6 py-12 text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-2"></div>
                    <span class="block text-sm text-slate-500 dark:text-slate-400">Loading pages...</span>
                </td>
            </tr>
        `;

        try {
            const res = await Api.get('/admin/pages');
            if (res.success && res.data) {
                this.list = res.data;
                this.renderTable();
            }
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="4" class="px-6 py-8 text-center text-red-500 font-bold">Failed to load pages.</td></tr>`;
        }
    },

    renderTable() {
        const tbody = document.getElementById('pages-tbody');
        if (!tbody) return;

        if (this.list.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="3" class="px-6 py-12 text-center">
                        <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3 text-slate-400 dark:text-slate-500">
                            <i class="ph ph-files text-2xl"></i>
                        </div>
                        <p class="text-sm font-medium text-slate-900 dark:text-white">No pages added yet</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Create a static page like Terms or Privacy Policy.</p>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        this.list.forEach(p => {
            const date = new Date(p.created_at).toLocaleDateString('en-IN', {
                year: 'numeric', month: 'short', day: 'numeric'
            });

            html += `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                    <td class="px-6 py-4 whitespace-nowrap min-w-0">
                        <div class="flex items-center">
                            <div class="h-10 w-10 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700 shadow-sm flex-shrink-0">
                                <i class="ph ph-file-text text-xl"></i>
                            </div>
                            <div class="ml-4 min-w-0">
                                <div class="text-sm font-semibold text-slate-900 dark:text-white truncate">${p.title}</div>
                                <div class="text-xs text-primary-600 dark:text-primary-400 mt-0.5 font-mono truncate">/page/${p.slug}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                        <div class="flex items-center text-sm text-slate-500 dark:text-slate-400">
                            <i class="ph ph-calendar-blank mr-2 text-slate-400"></i> ${date}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick="Pages.editPage(${p.id})" class="p-1.5 text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors rounded focus:outline-none focus:ring-2 focus:ring-primary-500" title="Edit Page">
                                <i class="ph ph-pencil-simple text-lg"></i>
                            </button>
                            <button onclick="Pages.deletePage(${p.id})" class="p-1.5 text-slate-400 hover:text-red-600 dark:hover:text-red-400 transition-colors rounded focus:outline-none focus:ring-2 focus:ring-red-500" title="Delete Page">
                                <i class="ph ph-trash text-lg"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    },

    editPage(id) {
        const p = this.list.find(item => parseInt(item.id) === parseInt(id));
        if (!p) return;

        this.editingPageId = p.id;
        document.getElementById('page-form-title').innerHTML = `<i class="ph ph-pencil-simple mr-2 text-slate-400"></i> Edit Static Page`;
        document.getElementById('btn-cancel-edit-page').classList.remove('hidden');

        document.getElementById('page-title').value = p.title;
        document.getElementById('page-slug').value = p.slug;
        document.getElementById('page-content').value = p.content || '';
    },

    async deletePage(id) {
        if (!confirm("Are you sure you want to delete this static page?")) return;

        try {
            await Api.delete('/admin/pages/' + id);
            Utils.showToast("CMS page deleted successfully.", "success");
            await this.loadPagesTable();
        } catch (e) {
            Utils.showToast(e.message || "Failed to delete page.", "error");
        }
    },

    async savePage(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-save-page');
        btn.disabled = true;

        const title = document.getElementById('page-title').value.trim();
        let slug = document.getElementById('page-slug').value.trim();
        if (!slug) slug = title.toLowerCase().replace(/[^a-z0-9]+/g, '-');

        const payload = {
            title: title,
            slug: slug,
            content: document.getElementById('page-content').value
        };

        try {
            if (this.editingPageId) {
                await Api.put('/admin/pages/' + this.editingPageId, payload);
                Utils.showToast("Static page updated successfully!", "success");
            } else {
                await Api.post('/admin/pages', payload);
                Utils.showToast("Static page created successfully!", "success");
            }

            this.resetForm();
            await this.loadPagesTable();
        } catch (err) {
            Utils.showToast(err.message || "Failed to save page.", "error");
        } finally {
            btn.disabled = false;
        }
    },

    resetForm() {
        this.editingPageId = null;
        document.getElementById('page-form-title').innerHTML = `<i class="ph ph-file-plus mr-2 text-slate-400"></i> Create Page`;
        document.getElementById('btn-cancel-edit-page').classList.add('hidden');
        document.getElementById('frm-page-save').reset();
    }
};

window.Pages = Pages;
document.addEventListener('DOMContentLoaded', () => {
    Pages.init();
});
