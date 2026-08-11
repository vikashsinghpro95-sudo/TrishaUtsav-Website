<?php
// admin/categories.php
include_once __DIR__ . '/includes/admin-header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Categories</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Organize your product catalog into hierarchical categories.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
    
    <!-- Categories List (Left side) -->
    <div class="lg:col-span-2 bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                <i class="ph ph-folders mr-2 text-slate-400"></i> Category Structure
            </h3>
        </div>

        <div class="overflow-x-auto min-h-[300px]">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-900/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Category</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden sm:table-cell">Slug</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24">Actions</th>
                    </tr>
                </thead>
                <tbody id="categories-tbody" class="bg-white dark:bg-slate-850 divide-y divide-slate-200 dark:divide-slate-800">
                    <!-- Loaded dynamically -->
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-2"></div>
                            <span class="block text-sm text-slate-500 dark:text-slate-400">Loading categories...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Category Form (Right side) -->
    <div class="lg:col-span-1">
        <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden sticky top-24">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
                <h3 id="cat-form-title" class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-plus-circle mr-2 text-slate-400"></i> Add Category
                </h3>
                <button id="btn-cancel-edit-cat" onclick="Categories.resetForm()" class="hidden text-xs font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 px-2.5 py-1 rounded transition-colors">
                    Cancel
                </button>
            </div>

            <form id="frm-category-save" class="p-5 space-y-4">
                <div>
                    <label for="cat-name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Name</label>
                    <input type="text" id="cat-name" required placeholder="e.g. Smartwatches" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                </div>

                <div>
                    <label for="cat-slug" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Slug</label>
                    <input type="text" id="cat-slug" placeholder="e.g. smartwatches" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                </div>

                <div>
                    <label for="cat-parent" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Parent Category</label>
                    <select id="cat-parent" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors cursor-pointer">
                        <option value="">No Parent (Root Category)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Thumbnail</label>
                    <div class="flex items-center space-x-3">
                        <input type="file" id="cat-image-file" accept="image/*" class="hidden" onchange="Categories.uploadImage(this)">
                        <button type="button" onclick="document.getElementById('cat-image-file').click()" class="inline-flex items-center px-3 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors whitespace-nowrap">
                            <i class="ph ph-upload mr-2"></i> Upload
                        </button>
                        <input type="text" id="cat-image" readonly class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 placeholder-slate-400 sm:text-sm transition-colors" placeholder="No image">
                    </div>
                </div>

                <div>
                    <label for="cat-status" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                    <select id="cat-status" required class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors cursor-pointer">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div>
                    <label for="cat-desc" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <textarea id="cat-desc" rows="3" placeholder="Optional summary..." class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors resize-none"></textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" id="btn-save-category" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                        Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const Categories = {
        list: [],
        editingCategoryId: null,

        async init() {
            await this.loadCategories();
            document.getElementById('frm-category-save').addEventListener('submit', (e) => this.saveCategory(e));
        },

        async loadCategories() {
            const tbody = document.getElementById('categories-tbody');
            if (!tbody) return;

            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-2"></div>
                    </td>
                </tr>
            `;

            try {
                const res = await Api.get('/categories');
                if (res.success && res.data) {
                    this.list = res.data;
                    this.renderTable();
                    this.renderDropdown();
                }
            } catch (e) {
                tbody.innerHTML = `<tr><td colspan="4" class="px-6 py-8 text-center text-red-500 font-medium">Failed to load categories.</td></tr>`;
            }
        },

        renderTable() {
            const tbody = document.getElementById('categories-tbody');
            if (!tbody) return;

            if (this.list.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3 text-slate-400 dark:text-slate-500">
                                <i class="ph ph-folders text-2xl"></i>
                            </div>
                            <p class="text-sm font-medium text-slate-900 dark:text-white">No categories found</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Get started by creating a new root category.</p>
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            const renderRow = (cats, level = 0) => {
                cats.forEach(cat => {
                    
                    let statusClass = 'bg-red-50 text-red-700 ring-1 ring-red-600/20 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/20';
                    if (cat.status === 'active') {
                        statusClass = 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20';
                    }
                    
                    const imgUrl = (cat.image || cat.image_url) ? (FRONTEND_BASE_URL + (cat.image || cat.image_url)) : 'https://placehold.co/100/F8FAFC/94A3B8?text=Cat';

                    let indentHtml = '';
                    if (level > 0) {
                        for(let i=0; i<level; i++) {
                            indentHtml += `<div class="w-6 border-b border-slate-300 dark:border-slate-600 ml-2 inline-block -translate-y-2"></div>`;
                        }
                    }

                    html += `
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    ${indentHtml}
                                    <div class="${level > 0 ? 'ml-2' : ''} flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <img class="h-10 w-10 rounded object-cover shadow-sm border border-slate-200 dark:border-slate-700" src="${imgUrl}" alt="${cat.name}">
                                        </div>
                                        <div class="ml-4 min-w-0">
                                            <div class="text-sm font-medium text-slate-900 dark:text-white">${cat.name}</div>
                                            ${level > 0 ? '' : '<span class="inline-flex items-center mt-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">Root</span>'}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                                <div class="text-sm text-slate-500 dark:text-slate-400">/${cat.slug}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ${statusClass}">
                                    ${cat.status.charAt(0).toUpperCase() + cat.status.slice(1)}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="Categories.editCategory(${cat.id})" class="p-1.5 text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors rounded focus:outline-none focus:ring-2 focus:ring-primary-500" title="Edit Category">
                                        <i class="ph ph-pencil-simple text-lg"></i>
                                    </button>
                                    <button onclick="Categories.deleteCategory(${cat.id})" class="p-1.5 text-slate-400 hover:text-red-600 dark:hover:text-red-400 transition-colors rounded focus:outline-none focus:ring-2 focus:ring-red-500" title="Delete Category">
                                        <i class="ph ph-trash text-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;

                    if (cat.children && cat.children.length > 0) {
                        renderRow(cat.children, level + 1);
                    }
                });
            };

            renderRow(this.list);
            tbody.innerHTML = html;
        },

        renderDropdown() {
            const select = document.getElementById('cat-parent');
            if (!select) return;

            let html = '<option value="">No Parent (Root Category)</option>';
            const flatten = (cats, prefix = '') => {
                cats.forEach(c => {
                    if (this.editingCategoryId && parseInt(c.id) === parseInt(this.editingCategoryId)) return;
                    
                    html += `<option value="${c.id}">${prefix}${c.name}</option>`;
                    if (c.children && c.children.length > 0) flatten(c.children, prefix + '\u2003\u2003'); // em spaces for indent
                });
            };

            flatten(this.list);
            select.innerHTML = html;
        },

        async uploadImage(input) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];

            const formData = new FormData();
            formData.append('file', file);
            formData.append('type', 'general');

            try {
                const res = await Api.uploadFile('/admin/media/upload', formData);
                if (res.success && res.path) {
                    document.getElementById('cat-image').value = res.path;
                    if(window.Utils && Utils.showToast) Utils.showToast("Image uploaded successfully!", "success");
                }
            } catch (e) {
                if(window.Utils && Utils.showToast) Utils.showToast("Failed to upload image: " + e.message, "error");
            }
        },

        editCategory(id) {
            const findById = (cats, targetId) => {
                for (let c of cats) {
                    if (parseInt(c.id) === parseInt(targetId)) return c;
                    if (c.children && c.children.length > 0) {
                        const found = findById(c.children, targetId);
                        if (found) return found;
                    }
                }
                return null;
            };

            const cat = findById(this.list, id);
            if (!cat) return;

            this.editingCategoryId = cat.id;
            document.getElementById('cat-form-title').innerHTML = `<i class="ph ph-pencil-simple mr-2 text-slate-400"></i> Edit Category`;
            document.getElementById('btn-cancel-edit-cat').classList.remove('hidden');

            document.getElementById('cat-name').value = cat.name;
            document.getElementById('cat-slug').value = cat.slug;
            document.getElementById('cat-image').value = cat.image || cat.image_url || '';
            document.getElementById('cat-parent').value = cat.parent_id || '';
            document.getElementById('cat-status').value = cat.status;
            document.getElementById('cat-desc').value = cat.description || '';

            this.renderDropdown();
            
            // Scroll to form on mobile
            if(window.innerWidth < 1024) {
                document.getElementById('frm-category-save').scrollIntoView({behavior: 'smooth'});
            }
        },

        async deleteCategory(id) {
            if (!confirm("Are you sure you want to delete this category? Subcategories will be moved to root level.")) return;

            try {
                await Api.delete('/admin/categories/' + id);
                if(window.Utils && Utils.showToast) Utils.showToast("Category deleted successfully.", "success");
                await this.loadCategories();
            } catch (e) {
                if(window.Utils && Utils.showToast) Utils.showToast(e.message || "Failed to delete category.", "error");
            }
        },

        async saveCategory(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-save-category');
            btn.disabled = true;

            const name = document.getElementById('cat-name').value;
            let slug = document.getElementById('cat-slug').value;
            if (!slug) slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-');

            const payload = {
                name: name,
                slug: slug,
                image: document.getElementById('cat-image').value || null,
                parent_id: document.getElementById('cat-parent').value || null,
                status: document.getElementById('cat-status').value,
                description: document.getElementById('cat-desc').value
            };

            try {
                if (this.editingCategoryId) {
                    await Api.put('/admin/categories/' + this.editingCategoryId, payload);
                    if(window.Utils && Utils.showToast) Utils.showToast("Category updated successfully!", "success");
                } else {
                    await Api.post('/admin/categories', payload);
                    if(window.Utils && Utils.showToast) Utils.showToast("Category created successfully!", "success");
                }

                this.resetForm();
                await this.loadCategories();
            } catch (err) {
                if(window.Utils && Utils.showToast) Utils.showToast(err.message || "Failed to save category.", "error");
            } finally {
                btn.disabled = false;
            }
        },

        resetForm() {
            this.editingCategoryId = null;
            document.getElementById('cat-form-title').innerHTML = `<i class="ph ph-plus-circle mr-2 text-slate-400"></i> Add Category`;
            document.getElementById('btn-cancel-edit-cat').classList.add('hidden');
            document.getElementById('frm-category-save').reset();
            document.getElementById('cat-image').value = '';
            this.renderDropdown();
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        Categories.init();
    });
</script>

<?php
include_once __DIR__ . '/includes/admin-footer.php';
?>
