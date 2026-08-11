/**
 * Admin Products Management Module
 */

const Products = {
    filters: {
        page: 1,
        per_page: 10,
        search: '',
        category: '',
        status: '' // draft, published, archived
    },
    editingProductId: null,
    attributesList: [],

    /**
     * Initialize Product List page
     */
    initList() {
        this.filters.search = Utils.getQueryParam('search') || '';
        this.filters.category = Utils.getQueryParam('category') || '';
        this.filters.status = Utils.getQueryParam('status') || '';
        this.filters.page = parseInt(Utils.getQueryParam('page')) || 1;

        // Set inputs
        const searchInput = document.getElementById('prod-search');
        if (searchInput) searchInput.value = this.filters.search;
        
        const statusSelect = document.getElementById('prod-status-filter');
        if (statusSelect) statusSelect.value = this.filters.status;

        this.loadCategoriesDropdown('prod-category-filter');
        this.loadProductsTable();
    },

    /**
     * Populate Category selectors in filter bar
     */
    async loadCategoriesDropdown(elementId) {
        const select = document.getElementById(elementId);
        if (!select) return;

        try {
            const res = await Api.get('/categories');
            if (res.success && res.data) {
                let html = '<option value="">All Categories</option>';
                const flatten = (cats, prefix = '') => {
                    cats.forEach(c => {
                        html += `<option value="${c.id}">${prefix}${c.name}</option>`;
                        if (c.children && c.children.length > 0) flatten(c.children, prefix + '-- ');
                    });
                };
                flatten(res.data);
                select.innerHTML = html;
                select.value = this.filters.category;
            }
        } catch (e) {}
    },

    async loadProductsTable() {
        const tbody = document.getElementById('products-tbody');
        const pagination = document.getElementById('products-pagination');
        if (!tbody) return;

        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-12 text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-2"></div>
                    <span class="block text-sm text-slate-500 dark:text-slate-400">Loading catalog...</span>
                </td>
            </tr>
        `;

        try {
            const q = new URLSearchParams();
            for (let key in this.filters) {
                if (this.filters[key] !== '') q.append(key, this.filters[key]);
            }

            const res = await Api.get('/admin/products?' + q.toString());
            if (res.success && res.data) {
                if (res.data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3 text-slate-400 dark:text-slate-500">
                                    <i class="ph ph-package text-2xl"></i>
                                </div>
                                <p class="text-sm font-medium text-slate-900 dark:text-white">No products found</p>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Try adjusting your filters or search query.</p>
                            </td>
                        </tr>
                    `;
                    if (pagination) pagination.innerHTML = '';
                    return;
                }

                let html = '';
                res.data.forEach(prod => {
                    const price = parseFloat(prod.price) || 0.00;
                    const rawImg = prod.primary_image || (prod.images && prod.images.length > 0 ? prod.images[0].image_url : null);
                    const fallbackPath = typeof FRONTEND_BASE_URL !== 'undefined' && FRONTEND_BASE_URL !== '' ? FRONTEND_BASE_URL.replace(/\/$/, '') + '/assets/images/product_placeholder.jpg' : '/assets/images/product_placeholder.jpg';
                    const imgUrl = rawImg ? Utils.fixImageUrl(rawImg) : fallbackPath;

                    const statusStr = prod.status || 'draft';
                    let statusClass = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
                    let statusIcon = 'ph-file-dashed';
                    if (statusStr === 'published') {
                        statusClass = 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20';
                        statusIcon = 'ph-check-circle';
                    } else if (statusStr === 'draft') {
                        statusClass = 'bg-slate-50 text-slate-700 ring-1 ring-slate-600/20 dark:bg-slate-500/10 dark:text-slate-400 dark:ring-slate-500/20';
                        statusIcon = 'ph-pencil-simple';
                    } else if (statusStr === 'archived') {
                        statusClass = 'bg-red-50 text-red-700 ring-1 ring-red-600/20 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/20';
                        statusIcon = 'ph-archive';
                    }

                    html += `
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-slate-300 rounded cursor-pointer dark:bg-slate-800 dark:border-slate-600 dark:checked:bg-primary-500">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <img class="h-10 w-10 rounded object-cover shadow-sm border border-slate-200 dark:border-slate-700" src="${imgUrl}" alt="${prod.name}" onerror="this.onerror=null;this.src='${fallbackPath}';">
                                    </div>
                                    <div class="ml-4 min-w-0">
                                        <div class="text-sm font-medium text-slate-900 dark:text-white truncate">${prod.name}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 truncate">SKU: ${prod.sku || 'N/A'}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                                <div class="text-sm text-slate-900 dark:text-white">${prod.category_name || 'Uncategorized'}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-slate-900 dark:text-white">${Utils.formatCurrency(price)}</div>
                                <div class="text-xs ${prod.stock_quantity <= prod.low_stock_threshold ? 'text-red-600 dark:text-red-400 font-medium flex items-center' : 'text-slate-500 dark:text-slate-400'} mt-0.5">
                                    ${prod.stock_quantity <= prod.low_stock_threshold ? '<i class="ph ph-warning-circle mr-1"></i>' : ''}
                                    ${prod.stock_quantity} in stock
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ${statusClass}">
                                    <i class="ph ${statusIcon} mr-1.5 text-sm"></i>
                                    ${statusStr.charAt(0).toUpperCase() + statusStr.slice(1)}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="Products.adjustStock(${prod.id}, ${prod.stock_quantity})" class="p-1.5 text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 transition-colors rounded focus:outline-none focus:ring-2 focus:ring-amber-500" title="Adjust Stock">
                                        <i class="ph ph-warehouse text-lg"></i>
                                    </button>
                                    <a href="/admin/product-edit.php?id=${prod.id}" class="p-1.5 text-slate-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors rounded focus:outline-none focus:ring-2 focus:ring-primary-500" title="Edit Product">
                                        <i class="ph ph-pencil-simple text-lg"></i>
                                    </a>
                                    <button onclick="Products.deleteProduct(${prod.id})" class="p-1.5 text-slate-400 hover:text-red-600 dark:hover:text-red-400 transition-colors rounded focus:outline-none focus:ring-2 focus:ring-red-500" title="Delete Product">
                                        <i class="ph ph-trash text-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
                this.renderPagination(res.pagination, pagination);
            } else {
                tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-12 text-center text-red-500 font-medium">Failed to load catalog: Invalid server response.</td></tr>`;
            }
        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-12 text-center text-red-500 font-medium">Failed to load catalog: ${e.message || e}</td></tr>`;
        }
    },

    renderPagination(pag, container) {
        if (!container || !pag || pag.total_pages <= 1) {
            if (container) container.innerHTML = '';
            return;
        }

        const maxVisible = 5;
        let startPage = Math.max(1, pag.current_page - Math.floor(maxVisible / 2));
        let endPage = Math.min(pag.total_pages, startPage + maxVisible - 1);

        if (endPage - startPage + 1 < maxVisible) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }

        let html = '<div class="flex items-center gap-1">';

        // Prev button
        if (pag.current_page > 1) {
            html += `<button onclick="Products.goToPage(${pag.current_page - 1})" class="p-1.5 border border-gray-300 rounded hover:bg-red-50 hover:border-red-300 text-gray-600"><i class="fas fa-chevron-left text-[10px]"></i></button>`;
        }

        if (startPage > 1) {
            html += `<button onclick="Products.goToPage(1)" class="px-2.5 py-1 rounded text-xs transition border border-gray-300 hover:bg-gray-50 text-gray-600">1</button>`;
            if (startPage > 2) html += `<span class="px-1 text-gray-400">...</span>`;
        }

        for (let i = startPage; i <= endPage; i++) {
            const isCurrent = i === pag.current_page;
            const btnClass = isCurrent
                ? 'bg-red-600 text-white font-bold border border-red-600 shadow-sm'
                : 'border border-gray-300 hover:bg-red-50 hover:border-red-300 text-gray-600 bg-white';
            html += `<button onclick="Products.goToPage(${i})" class="px-2.5 py-1 rounded text-xs transition ${btnClass}">${i}</button>`;
        }

        if (endPage < pag.total_pages) {
            if (endPage < pag.total_pages - 1) html += `<span class="px-1 text-gray-400">...</span>`;
            html += `<button onclick="Products.goToPage(${pag.total_pages})" class="px-2.5 py-1 rounded text-xs transition border border-gray-300 hover:bg-gray-50 text-gray-600">${pag.total_pages}</button>`;
        }

        // Next button
        if (pag.current_page < pag.total_pages) {
            html += `<button onclick="Products.goToPage(${pag.current_page + 1})" class="p-1.5 border border-gray-300 rounded hover:bg-red-50 hover:border-red-300 text-gray-600"><i class="fas fa-chevron-right text-[10px]"></i></button>`;
        }

        html += '</div>';
        container.innerHTML = html;
    },

    goToPage(p) {
        const url = new URL(window.location.href);
        url.searchParams.set('page', p);
        window.location.href = url.toString();
    },

    applyFilters() {
        const search = document.getElementById('prod-search').value;
        const status = document.getElementById('prod-status-filter').value;
        const category = document.getElementById('prod-category-filter').value;

        const url = new URL(window.location.href);
        url.searchParams.set('page', 1);
        if (search) url.searchParams.set('search', search); else url.searchParams.delete('search');
        if (status) url.searchParams.set('status', status); else url.searchParams.delete('status');
        if (category) url.searchParams.set('category', category); else url.searchParams.delete('category');

        window.location.href = url.toString();
    },

    async adjustStock(id, currentQty) {
        const input = prompt(`Current stock is ${currentQty} units. Enter quantity offset (e.g. 10 to add, -5 to subtract):`);
        if (input === null) return;
        
        const change = parseInt(input);
        if (isNaN(change)) {
            Utils.showToast("Please enter a valid numeric value.", "warning");
            return;
        }

        const reason = prompt("Enter adjustment reason:", "Manual admin stock adjustment");
        if (reason === null) return;

        try {
            await Api.patch(`/admin/products/${id}/stock`, {
                quantity_change: change,
                reason: reason
            });
            Utils.showToast("Stock quantity successfully adjusted.", "success");
            this.loadProductsTable();
        } catch (e) {
            Utils.showToast(e.message || "Failed to adjust stock.", "error");
        }
    },

    async deleteProduct(id) {
        if (!confirm("Are you sure you want to delete/archive this product?")) return;

        try {
            await Api.delete(`/admin/products/${id}`);
            Utils.showToast("Product successfully archived.", "success");
            this.loadProductsTable();
        } catch (e) {
            Utils.showToast(e.message || "Failed to archive product.", "error");
        }
    },

    // --- PRODUCT EDIT FORM MODULE ---
    editingProductId: null,
    attributesList: [],
    currentImages: [],
    editingMetadataImageId: null,
    croppingImageObj: null,
    uploadQueue: [],
    isUploading: false,

    /**
     * Initialize Product Editing Page Form
     */
    async initForm() {
        await this.loadCategoriesDropdown('edit-prod-category');
        await this.loadBrandsSelect();
        await this.loadOccasionsSelect();

        this.editingProductId = Utils.getQueryParam('id');
        
        if (this.editingProductId) {
            document.getElementById('edit-prod-title').innerText = "Edit Catalog Product";
            document.getElementById('image-upload-section').classList.remove('hidden');
            await this.loadProductFormDetails();
            
            // Set up Drag & Drop uploader zone listeners
            this.setupDragAndDrop();
            this.setupSelectAll();
        } else {
            document.getElementById('edit-prod-title').innerText = "Add New Product";
        }

        // Bind form save
        const form = document.getElementById('frm-product-edit');
        if (form) {
            form.addEventListener('submit', (e) => this.saveProduct(e));
        }
    },

    setupDragAndDrop() {
        const dropzone = document.getElementById('image-dropzone');
        const fileInput = document.getElementById('image-file-input');
        if (!dropzone || !fileInput) return;

        // Trigger input click on dropzone click
        dropzone.addEventListener('click', () => fileInput.click());

        // File input changed
        fileInput.addEventListener('change', (e) => {
            if (e.target.files && e.target.files.length > 0) {
                this.handleFilesSelection(e.target.files);
            }
        });

        // Drag events styling classes
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropzone.classList.remove('border-slate-300');
                dropzone.classList.add('border-red-500', 'bg-red-50/10');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropzone.classList.remove('border-red-500', 'bg-red-50/10');
                dropzone.classList.add('border-slate-300');
            }, false);
        });

        // Drop event
        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            if (dt && dt.files && dt.files.length > 0) {
                this.handleFilesSelection(dt.files);
            }
        }, false);
    },

    setupSelectAll() {
        const selectAll = document.getElementById('gallery-select-all');
        if (!selectAll) return;

        selectAll.addEventListener('change', (e) => {
            const checkboxes = document.querySelectorAll('.gallery-item-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = e.target.checked;
            });
            this.updateBulkDeleteState();
        });
    },

    handleFilesSelection(files) {
        const queueContainer = document.getElementById('upload-queue-container');
        const queueList = document.getElementById('queue-list');
        if (!queueContainer || !queueList) return;

        let addedAny = false;
        
        for (let i = 0; i < files.length; i++) {
            const f = files[i];
            
            const allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/avif', 'image/gif', 'image/svg+xml'];
            if (!allowed.includes(f.type)) {
                Utils.showToast(`File "${f.name}" format is not supported. Only JPEG, PNG, WEBP, AVIF, GIF, SVG.`, "warning");
                continue;
            }
            
            if (f.size > 5 * 1024 * 1024) {
                Utils.showToast(`File "${f.name}" is too large (max 5MB limit).`, "warning");
                continue;
            }
            
            const queueId = 'q-' + Math.random().toString(36).substr(2, 9);
            const queueItem = {
                id: queueId,
                file: f,
                status: 'pending',
                progress: 0,
                xhr: null
            };
            this.uploadQueue.push(queueItem);
            addedAny = true;
            
            const li = document.createElement('div');
            li.id = queueId;
            li.className = 'flex flex-col p-2.5 border border-slate-200 rounded-lg bg-white space-y-1.5';
            li.innerHTML = `
                <div class="flex items-center justify-between text-xs">
                    <span class="font-medium text-slate-800 truncate max-w-[250px]" title="${f.name}">${f.name} (${(f.size / (1024 * 1024)).toFixed(2)} MB)</span>
                    <div class="flex items-center gap-2">
                        <span class="progress-pct font-mono text-slate-500">0%</span>
                        <button type="button" onclick="Products.cancelQueueItem('${queueId}')" class="text-slate-400 hover:text-red-500"><i class="ph ph-x-circle text-base"></i></button>
                    </div>
                </div>
                <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
                    <div class="progress-bar bg-red-600 h-full transition-all" style="width: 0%"></div>
                </div>
            `;
            queueList.appendChild(li);
        }

        if (addedAny) {
            queueContainer.classList.remove('hidden');
            this.processUploadQueue();
        }
    },

    cancelQueueItem(queueId) {
        const itemIdx = this.uploadQueue.findIndex(i => i.id === queueId);
        if (itemIdx === -1) return;
        const item = this.uploadQueue[itemIdx];
        if (item.status === 'uploading' && item.xhr) {
            item.xhr.abort();
        }
        const el = document.getElementById(queueId);
        if (el) el.remove();
        this.uploadQueue.splice(itemIdx, 1);
        this.updateQueueStatusText();
        this.processUploadQueue();
    },

    updateQueueStatusText() {
        const total = this.uploadQueue.length;
        const uploaded = this.uploadQueue.filter(i => i.status === 'success').length;
        const txt = document.getElementById('queue-status-text');
        if (txt) {
            txt.textContent = `${uploaded}/${total} uploaded`;
        }
    },

    async processUploadQueue() {
        if (this.isUploading) return;
        
        const next = this.uploadQueue.find(i => i.status === 'pending');
        if (!next) {
            this.isUploading = false;
            this.updateQueueStatusText();
            
            const active = this.uploadQueue.filter(i => i.status === 'pending' || i.status === 'uploading');
            if (active.length === 0) {
                setTimeout(() => {
                    const queueContainer = document.getElementById('upload-queue-container');
                    const queueList = document.getElementById('queue-list');
                    if (queueContainer) queueContainer.classList.add('hidden');
                    if (queueList) queueList.innerHTML = '';
                    this.uploadQueue = [];
                }, 2000);
            }
            return;
        }

        this.isUploading = true;
        next.status = 'uploading';
        this.updateQueueStatusText();

        const formData = new FormData();
        formData.append('image', next.file);
        
        const defaultAlt = next.file.name.replace(/\.[^/.]+$/, "").replace(/[-_]/g, " ");
        formData.append('alt_text', defaultAlt);

        const xhr = new XMLHttpRequest();
        next.xhr = xhr;

        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const pct = Math.round((e.loaded / e.total) * 100);
                next.progress = pct;
                const el = document.getElementById(next.id);
                if (el) {
                    el.querySelector('.progress-pct').textContent = pct + '%';
                    el.querySelector('.progress-bar').style.width = pct + '%';
                }
            }
        });

        xhr.addEventListener('load', () => {
            this.isUploading = false;
            try {
                const res = JSON.parse(xhr.responseText);
                if (xhr.status >= 200 && xhr.status < 300 && res.success) {
                    next.status = 'success';
                    const el = document.getElementById(next.id);
                    if (el) el.remove();
                    this.loadProductFormDetails();
                } else {
                    next.status = 'error';
                    const el = document.getElementById(next.id);
                    if (el) {
                        el.querySelector('.progress-pct').textContent = 'Error';
                        el.querySelector('.progress-bar').classList.replace('bg-red-600', 'bg-amber-500');
                    }
                    Utils.showToast(res.message || "Failed to upload image.", "error");
                }
            } catch (err) {
                next.status = 'error';
            }
            this.processUploadQueue();
        });

        xhr.addEventListener('error', () => {
            this.isUploading = false;
            next.status = 'error';
            const el = document.getElementById(next.id);
            if (el) {
                el.querySelector('.progress-pct').textContent = 'Failed';
                el.querySelector('.progress-bar').classList.replace('bg-red-600', 'bg-red-500');
            }
            this.processUploadQueue();
        });

        const token = localStorage.getItem('admin_token');
        xhr.open('POST', `/api/admin/products/${this.editingProductId}/images`);
        xhr.setRequestHeader('Accept', 'application/json');
        if (token) {
            xhr.setRequestHeader('Authorization', `Bearer ${token}`);
        }
        xhr.send(formData);
    },

    async loadBrandsSelect() {
        const select = document.getElementById('edit-prod-brand');
        if (!select) return;

        try {
            const res = await Api.get('/brands');
            if (res.success && res.data) {
                let html = '<option value="">No Brand (Generic)</option>';
                res.data.forEach(b => {
                    html += `<option value="${b.id}">${b.name}</option>`;
                });
                select.innerHTML = html;
            }
        } catch (e) {}
    },

    async loadOccasionsSelect() {
        const select = document.getElementById('edit-prod-occasion');
        if (!select) return;

        try {
            const res = await Api.get('/occasions');
            if (res.success && res.data) {
                let html = '<option value="">None / All Occasions General Catalog</option>';
                res.data.forEach(o => {
                    html += `<option value="${o.id}">${o.name}</option>`;
                });
                select.innerHTML = html;
            }
        } catch (e) {}
    },

    async loadProductFormDetails() {
        try {
            const res = await Api.get('/admin/products/' + this.editingProductId);
            if (res.success && res.data) {
                const prod = res.data;

                document.getElementById('edit-prod-name').value = prod.name || '';
                document.getElementById('edit-prod-slug').value = prod.slug || '';
                document.getElementById('edit-prod-sku').value = prod.sku || '';
                document.getElementById('edit-prod-price').value = prod.price || '';
                document.getElementById('edit-prod-mrp').value = prod.mrp || '';
                document.getElementById('edit-prod-tax-rate').value = prod.tax_rate || '0.00';
                if (document.getElementById('edit-prod-shipping')) document.getElementById('edit-prod-shipping').value = prod.shipping_charge || '0.00';
                document.getElementById('edit-prod-stock').value = prod.stock_quantity || '0';
                document.getElementById('edit-prod-low-stock').value = prod.low_stock_threshold || '5';
                document.getElementById('edit-prod-category').value = prod.category_id || '';
                document.getElementById('edit-prod-brand').value = prod.brand_id || '';
                if (document.getElementById('edit-prod-occasion')) document.getElementById('edit-prod-occasion').value = prod.occasion_id || '';
                document.getElementById('edit-prod-weight').value = prod.weight || '';
                document.getElementById('edit-prod-dimensions').value = prod.dimensions || '';
                document.getElementById('edit-prod-status').value = prod.status || 'draft';
                document.getElementById('edit-prod-featured').checked = parseInt(prod.featured) === 1;
                if (document.getElementById('edit-prod-is-trending')) document.getElementById('edit-prod-is-trending').checked = parseInt(prod.is_trending) === 1;
                if (document.getElementById('edit-prod-is-must-buy')) document.getElementById('edit-prod-is-must-buy').checked = parseInt(prod.is_must_buy) === 1;
                document.getElementById('edit-prod-short-desc').value = prod.short_description || '';
                document.getElementById('edit-prod-desc').value = prod.description || '';
                if (document.getElementById('edit-prod-meta-title')) document.getElementById('edit-prod-meta-title').value = prod.meta_title || '';
                if (document.getElementById('edit-prod-meta-keywords')) document.getElementById('edit-prod-meta-keywords').value = prod.meta_keywords || '';
                if (document.getElementById('edit-prod-meta-desc')) document.getElementById('edit-prod-meta-desc').value = prod.meta_description || '';

                // Load attributes
                this.attributesList = prod.attributes || [];
                this.renderAttributes();

                // Load attached images
                this.renderProductAttachedImages(prod.images || []);
            }
        } catch (e) {
            Utils.showToast("Failed to fetch product details: " + e.message, "error");
        }
    },

    renderProductAttachedImages(images) {
        this.currentImages = images;
        const container = document.getElementById('attached-images-container');
        if (!container) return;

        if (images.length === 0) {
            container.innerHTML = `<span class="col-span-full text-sm text-slate-500 font-medium text-center py-8">No images attached to this product yet. Drag & drop files above.</span>`;
            return;
        }

        let html = '';
        images.forEach((img, index) => {
            const imgUrl = Utils.fixImageUrl(img.image_url);
            const isCover = parseInt(img.is_primary) === 1;
            const position = index + 1;
            
            html += `
                <div class="relative bg-white border border-slate-200 rounded-lg overflow-hidden group shadow-sm cursor-move flex flex-col transition-all duration-150 hover:shadow" data-id="${img.id}">
                    <!-- Checkbox & Numbering -->
                    <div class="absolute top-2 left-2 z-10 flex items-center gap-1.5">
                        <input type="checkbox" data-select-id="${img.id}" onchange="Products.updateBulkDeleteState()" class="gallery-item-checkbox h-4 w-4 text-red-600 focus:ring-red-500 border-slate-300 rounded cursor-pointer shadow-sm">
                        <span class="bg-black/60 text-white text-[9px] font-bold font-mono px-1.5 py-0.5 rounded shadow-sm">
                            #${position}
                        </span>
                    </div>

                    <!-- Cover Badge -->
                    ${isCover ? `
                        <span class="absolute top-2 right-2 z-10 bg-red-600 text-white text-[9px] font-bold px-2 py-0.5 rounded shadow-sm flex items-center">
                            <i class="ph-fill ph-star mr-1"></i> Cover
                        </span>
                    ` : ''}

                    <!-- Image Thumbnail with Lightbox link -->
                    <div class="aspect-square w-full overflow-hidden bg-slate-50 flex items-center justify-center relative">
                        <img src="${imgUrl}" alt="${img.alt_text || ''}" title="${img.title || ''}" class="w-full h-full object-cover transition-transform group-hover:scale-105" onerror="this.onerror=null;this.src='/assets/images/product_placeholder.jpg';">
                    </div>

                    <!-- Alt text helper bar -->
                    <div class="p-2 bg-slate-50 border-t border-slate-150 flex items-center justify-between text-[10px] text-slate-500">
                        <span class="truncate max-w-[80%]" title="${img.alt_text || 'No Alt Text'}">
                            ${img.alt_text ? `<span class="text-slate-800 font-semibold">Alt:</span> ${img.alt_text}` : '<span class="text-amber-600 font-semibold"><i class="ph ph-warning-circle mr-1"></i>No Alt Tag</span>'}
                        </span>
                        <span class="font-mono text-slate-400">${img.title ? 'T' : ''}</span>
                    </div>

                    <!-- Overlay Controls -->
                    <div class="absolute inset-0 bg-slate-950/70 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center flex-col gap-2 p-2">
                        <div class="flex gap-1.5">
                            <a href="${imgUrl}" data-fslightbox="product-gallery" class="p-1.5 bg-white text-slate-800 rounded-lg hover:text-red-650 transition shadow-md" title="View Fullscreen">
                                <i class="ph ph-magnifying-glass-plus text-sm"></i>
                            </a>
                            <button type="button" onclick="Products.openMetadataModal(${img.id})" class="p-1.5 bg-white text-slate-800 rounded-lg hover:text-red-650 transition shadow-md" title="Edit Properties">
                                <i class="ph ph-pencil-simple text-sm"></i>
                            </button>
                            <button type="button" onclick="Products.openCropperModal(${img.id})" class="p-1.5 bg-white text-slate-800 rounded-lg hover:text-red-655 transition shadow-md" title="Crop Image">
                                <i class="ph ph-crop text-sm"></i>
                            </button>
                            <button type="button" onclick="Products.deleteImage(${img.id})" class="p-1.5 bg-white text-slate-800 rounded-lg hover:text-red-655 transition shadow-md" title="Delete Image">
                                <i class="ph ph-trash text-sm"></i>
                            </button>
                        </div>
                        
                        ${!isCover ? `
                            <button type="button" onclick="Products.setPrimaryImage(${img.id})" class="mt-2 text-white text-[9px] font-bold bg-red-600 hover:bg-red-700 px-2 py-1 rounded transition shadow-sm">
                                Make Cover
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;

        // Refresh FsLightbox
        if (typeof refreshFsLightbox === 'function') {
            refreshFsLightbox();
        }

        // Initialize SortableJS
        if (typeof Sortable !== 'undefined') {
            new Sortable(container, {
                animation: 150,
                onEnd: () => {
                    this.saveImageOrder();
                }
            });
        }
    },

    async saveImageOrder() {
        const container = document.getElementById('attached-images-container');
        if (!container) return;
        
        const items = container.querySelectorAll('[data-id]');
        const imagesData = [];
        
        items.forEach((item, index) => {
            const id = item.getAttribute('data-id');
            const hasPrimaryBadge = item.querySelector('.ph-fill\\.ph-star, .ph-fill.ph-star') !== null;
            imagesData.push({
                id: id,
                sort_order: index,
                is_primary: hasPrimaryBadge ? 1 : 0
            });
        });

        try {
            await Api.put(`/admin/products/${this.editingProductId}/images`, { images: imagesData });
            Utils.showToast("Image layout order saved successfully.", "success");
            this.loadProductFormDetails();
        } catch (e) {
            Utils.showToast("Failed to save image order: " + e.message, "error");
        }
    },

    async setPrimaryImage(imageId) {
        const container = document.getElementById('attached-images-container');
        if (!container) return;
        
        const items = container.querySelectorAll('[data-id]');
        const imagesData = [];
        
        items.forEach((item, index) => {
            const id = item.getAttribute('data-id');
            imagesData.push({
                id: id,
                sort_order: index,
                is_primary: parseInt(id) === parseInt(imageId) ? 1 : 0
            });
        });

        try {
            await Api.put(`/admin/products/${this.editingProductId}/images`, { images: imagesData });
            Utils.showToast("Primary image updated.", "success");
            this.loadProductFormDetails();
        } catch (e) {
            Utils.showToast("Failed to update primary image: " + e.message, "error");
        }
    },

    async deleteImage(imageId) {
        if (!confirm("Are you sure you want to delete this image?")) return;

        try {
            await Api.delete(`/admin/products/${this.editingProductId}/images/${imageId}`);
            Utils.showToast("Image deleted successfully.", "success");
            this.loadProductFormDetails();
        } catch (e) {
            Utils.showToast("Failed to delete image: " + e.message, "error");
        }
    },

    updateBulkDeleteState() {
        const checkboxes = document.querySelectorAll('.gallery-item-checkbox:checked');
        const count = checkboxes.length;
        const bulkBar = document.getElementById('gallery-bulk-actions');
        const countText = document.getElementById('gallery-selected-count');

        if (bulkBar && countText) {
            if (count > 0) {
                bulkBar.classList.remove('hidden');
                countText.textContent = count;
            } else {
                bulkBar.classList.add('hidden');
            }
        }
    },

    async bulkDeleteImages() {
        const checkboxes = document.querySelectorAll('.gallery-item-checkbox:checked');
        const imageIds = Array.from(checkboxes).map(cb => parseInt(cb.getAttribute('data-select-id')));
        
        if (imageIds.length === 0) return;
        if (!confirm(`Are you sure you want to delete the ${imageIds.length} selected images?`)) return;

        try {
            await Api.post(`/admin/products/${this.editingProductId}/images/bulk-delete`, {
                image_ids: imageIds
            });
            Utils.showToast("Selected images deleted successfully.", "success");
            
            const bulkBar = document.getElementById('gallery-bulk-actions');
            if (bulkBar) bulkBar.classList.add('hidden');
            const selectAllCheckbox = document.getElementById('gallery-select-all');
            if (selectAllCheckbox) selectAllCheckbox.checked = false;

            this.loadProductFormDetails();
        } catch (e) {
            Utils.showToast(e.message || "Failed to bulk delete images.", "error");
        }
    },

    openMetadataModal(imageId) {
        const img = this.currentImages.find(i => i.id == imageId);
        if (!img) return;

        this.editingMetadataImageId = imageId;
        const modal = document.getElementById('metadata-modal');
        const altInput = document.getElementById('meta-alt-text');
        const titleInput = document.getElementById('meta-title');
        const counter = document.getElementById('alt-char-counter');
        const validationMsg = document.getElementById('alt-validation-msg');

        if (!modal || !altInput || !titleInput) return;

        altInput.value = img.alt_text || '';
        titleInput.value = img.title || '';
        
        counter.textContent = `${altInput.value.length}/125`;
        validationMsg.classList.add('hidden');
        altInput.classList.remove('border-red-500');

        altInput.oninput = function() {
            counter.textContent = `${altInput.value.length}/125`;
        };

        const saveBtn = document.getElementById('btn-save-metadata');
        saveBtn.onclick = () => this.saveMetadata();

        modal.classList.remove('hidden');
    },

    closeMetadataModal() {
        const modal = document.getElementById('metadata-modal');
        if (modal) modal.classList.add('hidden');
        this.editingMetadataImageId = null;
    },

    async saveMetadata() {
        const altInput = document.getElementById('meta-alt-text');
        const titleInput = document.getElementById('meta-title');
        const validationMsg = document.getElementById('alt-validation-msg');

        validationMsg.classList.add('hidden');
        altInput.classList.remove('border-red-500');

        const altVal = altInput.value.trim();
        const titleVal = titleInput.value.trim();

        if (altVal.length === 0) {
            validationMsg.classList.remove('hidden');
            altInput.classList.add('border-red-500');
            return;
        }

        try {
            await Api.put(`/admin/products/${this.editingProductId}/images/${this.editingMetadataImageId}`, {
                alt_text: altVal,
                title: titleVal || null
            });
            Utils.showToast("Metadata saved successfully.", "success");
            this.closeMetadataModal();
            this.loadProductFormDetails();
        } catch(e) {
            Utils.showToast(e.message || "Failed to update image metadata.", "error");
        }
    },

    openCropperModal(imageId) {
        const imgObj = this.currentImages.find(i => i.id == imageId);
        if (!imgObj) return;

        this.croppingImageObj = imgObj;
        const modal = document.getElementById('cropper-modal');
        const imgEl = document.getElementById('cropper-image');
        if (!modal || !imgEl) return;

        imgEl.src = Utils.fixImageUrl(imgObj.image_url);

        modal.classList.remove('hidden');

        if (window.cropper) {
            window.cropper.destroy();
        }

        setTimeout(() => {
            window.cropper = new Cropper(imgEl, {
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false
            });
        }, 100);

        const confirmBtn = document.getElementById('btn-confirm-crop');
        confirmBtn.onclick = () => this.applyCrop();
    },

    closeCropperModal() {
        const modal = document.getElementById('cropper-modal');
        if (modal) modal.classList.add('hidden');
        if (window.cropper) {
            window.cropper.destroy();
            window.cropper = null;
        }
        this.croppingImageObj = null;
    },

    async applyCrop() {
        if (!window.cropper || !this.croppingImageObj) return;

        const confirmBtn = document.getElementById('btn-confirm-crop');
        confirmBtn.disabled = true;
        confirmBtn.textContent = "Processing...";

        window.cropper.getCroppedCanvas({
            maxWidth: 1200,
            maxHeight: 1200
        }).toBlob(async (blob) => {
            if (!blob) {
                Utils.showToast("Failed to generate cropped image blob.", "error");
                confirmBtn.disabled = false;
                confirmBtn.textContent = "Apply & Re-upload";
                return;
            }

            const formData = new FormData();
            const origName = this.croppingImageObj.image_url.split('/').pop() || 'cropped.jpg';
            formData.append('image', blob, origName);
            formData.append('alt_text', this.croppingImageObj.alt_text || 'Cropped product photo');
            formData.append('title', this.croppingImageObj.title || '');
            formData.append('is_primary', this.croppingImageObj.is_primary || 0);
            formData.append('sort_order', this.croppingImageObj.sort_order || 0);

            try {
                const res = await Api.post(`/admin/products/${this.editingProductId}/images`, formData);
                await Api.delete(`/admin/products/${this.editingProductId}/images/${this.croppingImageObj.id}`);
                
                Utils.showToast("Image cropped and updated successfully.", "success");
                this.closeCropperModal();
                this.loadProductFormDetails();
            } catch (e) {
                Utils.showToast(e.message || "Failed to upload cropped image.", "error");
            } finally {
                confirmBtn.disabled = false;
                confirmBtn.textContent = "Apply & Re-upload";
            }
        }, 'image/jpeg', 0.9);
    },

    addAttribute() {
        const nameInput = document.getElementById('attr-name-input');
        const valInput = document.getElementById('attr-val-input');
        const priceInput = document.getElementById('attr-price-input');

        const name = nameInput.value.trim();
        const value = valInput.value.trim();
        const extraPrice = parseFloat(priceInput.value) || 0.00;

        if (!name || !value) {
            Utils.showToast("Attribute Name and Value are required.", "warning");
            return;
        }

        this.attributesList.push({
            attribute_name: name,
            attribute_value: value,
            extra_price: extraPrice
        });

        valInput.value = '';
        priceInput.value = '0.00';

        this.renderAttributes();
    },

    removeAttribute(index) {
        this.attributesList.splice(index, 1);
        this.renderAttributes();
    },

    renderAttributes() {
        const container = document.getElementById('attributes-list-container');
        if (!container) return;

        if (this.attributesList.length === 0) {
            container.innerHTML = `<p class="text-sm text-slate-500 dark:text-slate-400">No variants added yet.</p>`;
            return;
        }

        let html = '<div class="flex flex-wrap gap-2">';
        this.attributesList.forEach((attr, idx) => {
            html += `
                <span class="inline-flex items-center bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-800 text-primary-700 dark:text-primary-300 text-sm px-3 py-1.5 rounded-lg font-medium shadow-sm transition-colors">
                    <span><span class="opacity-75 mr-1">${attr.attribute_name}:</span> ${attr.attribute_value} <span class="ml-1 text-xs opacity-75">(${Utils.formatCurrency(attr.extra_price)})</span></span>
                    <button type="button" onclick="Products.removeAttribute(${idx})" class="ml-2 pl-2 border-l border-primary-200 dark:border-primary-700 text-primary-400 hover:text-primary-700 dark:hover:text-primary-200 transition-colors focus:outline-none">
                        <i class="ph ph-x"></i>
                    </button>
                </span>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    },

    /**
     * Submit Product create or update
     */
    async saveProduct(e) {
        e.preventDefault();
        
        const missingAlt = this.currentImages.some(img => !img.alt_text || img.alt_text.trim() === '');
        if (missingAlt) {
            const proceed = confirm("Warning: One or more images do not have SEO Alt Text set. This can negatively impact search rankings. Do you still want to save?");
            if (!proceed) return;
        }

        const btn = document.getElementById('btn-save-product');
        btn.disabled = true;

        const payload = {
            category_id: document.getElementById('edit-prod-category').value,
            brand_id: document.getElementById('edit-prod-brand').value || null,
            occasion_id: document.getElementById('edit-prod-occasion') ? (document.getElementById('edit-prod-occasion').value || null) : null,
            name: document.getElementById('edit-prod-name').value,
            slug: document.getElementById('edit-prod-slug').value || null,
            sku: document.getElementById('edit-prod-sku').value || null,
            price: document.getElementById('edit-prod-price').value,
            mrp: document.getElementById('edit-prod-mrp').value || null,
            tax_rate: document.getElementById('edit-prod-tax-rate').value,
            shipping_charge: document.getElementById('edit-prod-shipping') ? document.getElementById('edit-prod-shipping').value : '0.00',
            stock_quantity: document.getElementById('edit-prod-stock').value,
            low_stock_threshold: document.getElementById('edit-prod-low-stock').value,
            weight: document.getElementById('edit-prod-weight').value || null,
            dimensions: document.getElementById('edit-prod-dimensions').value || null,
            status: document.getElementById('edit-prod-status').value,
            featured: document.getElementById('edit-prod-featured').checked ? 1 : 0,
            is_trending: (document.getElementById('edit-prod-is-trending') && document.getElementById('edit-prod-is-trending').checked) ? 1 : 0,
            is_must_buy: (document.getElementById('edit-prod-is-must-buy') && document.getElementById('edit-prod-is-must-buy').checked) ? 1 : 0,
            short_description: document.getElementById('edit-prod-short-desc').value,
            description: document.getElementById('edit-prod-desc').value,
            meta_title: document.getElementById('edit-prod-meta-title') ? document.getElementById('edit-prod-meta-title').value : null,
            meta_keywords: document.getElementById('edit-prod-meta-keywords') ? document.getElementById('edit-prod-meta-keywords').value : null,
            meta_description: document.getElementById('edit-prod-meta-desc') ? document.getElementById('edit-prod-meta-desc').value : null,
            attributes: this.attributesList
        };

        try {
            if (this.editingProductId) {
                await Api.put('/admin/products/' + this.editingProductId, payload);
                Utils.showToast("Product updated successfully!", "success");
            } else {
                const res = await Api.post('/admin/products', payload);
                Utils.showToast("Product created successfully! Upload images next.", "success");
                
                setTimeout(() => {
                    window.location.href = `/admin/product-edit.php?id=${res.product_id}`;
                }, 1000);
                return;
            }
            
            await this.loadProductFormDetails();
        } catch (err) {
            Utils.showToast(err.message || "Failed to save product details.", "error");
        } finally {
            btn.disabled = false;
        }
    }
};

window.Products = Products;


