/**
 * E-Commerce Customer Shop Listing Page Module
 */

const Shop = {
    filters: {
        page: 1,
        per_page: 9,
        search: '',
        category: '',
        occasion: '',
        brand: '',
        sort: 'newest',
        min_price: '',
        max_price: '',
        is_trending: ''
    },

    /**
     * Initializer
     */
    init() {
        // Read URL query params
        this.filters.search = Utils.getQueryParam('search') || '';
        this.filters.category = Utils.getQueryParam('category') || Utils.getQueryParam('category_id') || Utils.getQueryParam('slug') || '';
        this.filters.occasion = Utils.getQueryParam('occasion') || Utils.getQueryParam('occasion_id') || '';
        this.filters.brand = Utils.getQueryParam('brand') || '';
        this.filters.sort = Utils.getQueryParam('sort') || 'newest';
        this.filters.min_price = Utils.getQueryParam('min_price') || '';
        this.filters.max_price = Utils.getQueryParam('max_price') || '';
        this.filters.is_trending = Utils.getQueryParam('is_trending') || '';
        this.filters.page = parseInt(Utils.getQueryParam('page')) || 1;

        // Set inputs values
        const searchInput = document.getElementById('search-input');
        if (searchInput && this.filters.search) {
            searchInput.value = this.filters.search;
        }

        const sortSelect = document.getElementById('shop-sort');
        if (sortSelect) {
            sortSelect.value = this.filters.sort;
            sortSelect.addEventListener('change', (e) => {
                this.updateQueryParam('sort', e.target.value);
            });
        }

        const minPriceInput = document.getElementById('filter-min-price');
        const maxPriceInput = document.getElementById('filter-max-price');
        if (minPriceInput && maxPriceInput) {
            minPriceInput.value = this.filters.min_price;
            maxPriceInput.value = this.filters.max_price;
            
            document.getElementById('btn-apply-price').addEventListener('click', () => {
                this.updateQueryParams({
                    min_price: minPriceInput.value,
                    max_price: maxPriceInput.value,
                    page: 1 // reset page
                });
            });
        }

        // Load content
        this.loadSidebarCategories();
        this.loadSidebarBrands();
        this.loadProducts(false);

        // Handle browser back/forward buttons for filters
        window.addEventListener('popstate', (e) => {
            this.initFromUrl();
            this.loadProducts(false);
        });
    },

    initFromUrl() {
        this.filters.search = Utils.getQueryParam('search') || '';
        this.filters.category = Utils.getQueryParam('category') || Utils.getQueryParam('category_id') || Utils.getQueryParam('slug') || '';
        this.filters.occasion = Utils.getQueryParam('occasion') || Utils.getQueryParam('occasion_id') || '';
        this.filters.brand = Utils.getQueryParam('brand') || '';
        this.filters.sort = Utils.getQueryParam('sort') || 'newest';
        this.filters.min_price = Utils.getQueryParam('min_price') || '';
        this.filters.max_price = Utils.getQueryParam('max_price') || '';
        this.filters.is_trending = Utils.getQueryParam('is_trending') || '';
        this.filters.page = parseInt(Utils.getQueryParam('page')) || 1;
        
        // Sync checkboxes and inputs if needed
        const sortSelect = document.getElementById('shop-sort');
        if (sortSelect) sortSelect.value = this.filters.sort;
        
        // We could also re-check checkboxes in the sidebar but for brevity we rely on the API call
        // and initial render. True hydration would re-sync DOM elements.
    },

    toggleMobileFilters() {
        const sidebar = document.getElementById('filter-sidebar');
        const backdrop = document.getElementById('mobile-filter-backdrop');
        
        if (sidebar.classList.contains('-translate-x-full')) {
            // Open
            sidebar.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
            // Small delay to allow display:block to apply before animating opacity
            setTimeout(() => backdrop.classList.remove('opacity-0'), 10);
            document.body.classList.add('overflow-hidden');
        } else {
            // Close
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('opacity-0');
            setTimeout(() => backdrop.classList.add('hidden'), 300);
            document.body.classList.remove('overflow-hidden');
        }
    },

    /**
     * Fetch and build categories tree in sidebar
     */
    async loadSidebarCategories() {
        const container = document.getElementById('sidebar-categories');
        if (!container) return;

        try {
            const res = await Api.get('/categories');
            if (res.success && res.data) {
                container.innerHTML = this.renderCategoryTree(res.data);
            }
        } catch (e) {
            container.innerHTML = `<span class="text-xs text-red-500">Failed to load categories</span>`;
        }
    },

    /**
     * Recursively render nested category items
     *
     * @param {array} categories
     * @return {string} HTML
     */
    renderCategoryTree(categories) {
        let html = '<ul class="space-y-2 pl-1">';
        categories.forEach(cat => {
            const isActive = this.filters.category == cat.slug || this.filters.category == cat.id;
            const activeClass = isActive ? 'font-bold text-indigo-600' : 'text-gray-600 hover:text-indigo-600';
            
            html += `
                <li>
                    <a href="#" onclick="Shop.filterCategory('${cat.slug}'); return false;" class="text-sm transition flex justify-between items-center ${activeClass}">
                        <span>${cat.name}</span>
                        ${cat.children && cat.children.length > 0 ? `<i class="fas fa-chevron-down text-[10px] text-gray-400"></i>` : ''}
                    </a>
            `;

            if (cat.children && cat.children.length > 0) {
                html += `<div class="pl-3 mt-1 border-l border-gray-100">${this.renderCategoryTree(cat.children)}</div>`;
            }

            html += '</li>';
        });
        html += '</ul>';
        return html;
    },

    filterCategory(slug) {
        this.updateQueryParams({ category: slug, page: 1 });
    },

    /**
     * Fetch and build active brands in sidebar
     */
    async loadSidebarBrands() {
        const container = document.getElementById('sidebar-brands');
        if (!container) return;

        try {
            const res = await Api.get('/brands');
            if (res.success && res.data) {
                let html = '<div class="space-y-2">';
                res.data.forEach(brand => {
                    const isChecked = this.filters.brand == brand.slug || this.filters.brand == brand.id;
                    html += `
                        <label class="flex items-center text-sm text-gray-600 hover:text-indigo-600 cursor-pointer">
                            <input type="checkbox" name="brand_filter" value="${brand.slug}" ${isChecked ? 'checked' : ''} onchange="Shop.filterBrand(this)" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mr-2.5">
                            <span>${brand.name}</span>
                        </label>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            }
        } catch (e) {
            container.innerHTML = `<span class="text-xs text-red-500">Failed to load brands</span>`;
        }
    },

    filterBrand(checkbox) {
        const selected = checkbox.checked ? checkbox.value : '';
        this.updateQueryParams({ brand: selected, page: 1 });
    },

    /**
     * Fetch products list with filters and render grid
     */
    async loadProducts(append = false) {
        const grid = document.getElementById('product-grid');
        const btnLoadMore = document.getElementById('btn-load-more');
        const countSpan = document.getElementById('results-count');
        
        if (!grid) return;

        if (!append) {
            grid.innerHTML = `
                <div class="col-span-full flex flex-col justify-center items-center py-20 space-y-3">
                    <div class="loader-spinner-dark"></div>
                    <span class="text-gray-500 text-sm">Searching catalog...</span>
                </div>
            `;
            if (btnLoadMore) btnLoadMore.classList.add('hidden');
        } else {
            const originalText = btnLoadMore.innerHTML;
            btnLoadMore.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Loading...';
            btnLoadMore.disabled = true;
        }

        try {
            // Build query params string
            const q = new URLSearchParams();
            for (let key in this.filters) {
                if (this.filters[key] !== '') {
                    q.append(key, this.filters[key]);
                }
            }

            const res = await Api.get('/products?' + q.toString());
            
            if (res.success && res.data) {
                // Update results count
                const count = res.pagination.total_items;
                if (countSpan) {
                    countSpan.innerText = `${count} product${count !== 1 ? 's' : ''} found`;
                }

                if (res.data.length === 0 && !append) {
                    grid.innerHTML = `
                        <div class="col-span-full text-center py-20 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                            <i class="fas fa-search text-gray-300 text-5xl mb-4"></i>
                            <h3 class="text-lg font-semibold text-gray-700">No products found</h3>
                            <p class="text-gray-500 text-sm mt-1">Try broadening your search terms or clearing price filters.</p>
                            <button onclick="Shop.clearAllFilters()" class="mt-4 bg-[#990024] text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-[#7a001c] transition">
                                Reset Filters
                            </button>
                        </div>
                    `;
                    if (btnLoadMore) btnLoadMore.classList.add('hidden');
                    return;
                }

                // Render product grid
                let html = '';
                const fixFn = (window.Utils && typeof window.Utils.fixImageUrl === 'function') 
                    ? window.Utils.fixImageUrl 
                    : (path => BASE_URL + path.replace(/^\/+/, ''));

                res.data.forEach(prod => {
                    const price = parseFloat(prod.price);
                    const mrp = prod.mrp ? parseFloat(prod.mrp) : (price * 1.25);
                    const discount = Math.round(((mrp - price) / mrp) * 100);
                    const rawImg1 = prod.primary_image || (prod.images && prod.images.length > 0 ? prod.images[0].image_url : null);
                    let rawImg2 = null;
                    if (prod.images && prod.images.length > 1) {
                        const second = prod.images.find(i => i.image_url !== rawImg1);
                        if (second) rawImg2 = second.image_url;
                    }
                    const imgUrl1 = rawImg1 ? fixFn(rawImg1) : `${BASE_URL}assets/images/product_placeholder.jpg`;
                    const imgUrl2 = rawImg2 ? fixFn(rawImg2) : null;

                    html += `
                        <div class="product-card group bg-white rounded-3xl border border-[#f59e0b]/20 shadow-sm hover:shadow-xl hover:border-[#990024]/40 transition-all duration-300 flex flex-col justify-between overflow-hidden relative ${parseInt(prod.stock_quantity) <= 0 ? 'grayscale opacity-70 pointer-events-none' : ''}">
                            <!-- Wishlist Heart Icon Overlay -->
                            ${typeof WishlistManager !== 'undefined' ? WishlistManager.renderHeartButton(prod.id) : ''}
                            <a href="${BASE_URL}product?slug=${prod.slug}" class="relative bg-gray-50 aspect-square overflow-hidden block">
                                <img src="${imgUrl1}" alt="${prod.name}" onerror="this.onerror=null;this.src='${BASE_URL}assets/images/product_placeholder.jpg';" loading="lazy" decoding="async" class="w-full h-full object-cover transition-all duration-700 ease-out transform group-hover:scale-105 ${imgUrl2 ? 'group-hover:opacity-0' : ''}">
                                ${imgUrl2 ? `
                                    <img src="${imgUrl2}" alt="${prod.name}" onerror="this.onerror=null;this.src='${BASE_URL}assets/images/product_placeholder.jpg';" loading="lazy" decoding="async" class="absolute inset-0 w-full h-full object-cover opacity-0 scale-100 group-hover:opacity-100 group-hover:scale-105 transition-all duration-700 ease-out">
                                ` : ''}
                                ${parseInt(prod.stock_quantity) <= 0 ? `
                                    <div class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-black/40 backdrop-blur-[2px]">
                                        <div class="bg-white/90 border border-gray-200 rounded-2xl px-4 py-2.5 shadow-xl flex flex-col items-center gap-1">
                                            <i class="fas fa-clock text-gray-500 text-sm"></i>
                                            <span class="text-[11px] font-black text-gray-800 uppercase tracking-widest">Coming Soon</span>
                                        </div>
                                    </div>
                                ` : ''}
                            </a>
                            <div class="p-4 sm:p-5 flex flex-col flex-grow justify-between space-y-3">
                                <div class="space-y-1">
                                    <div class="flex items-center space-x-1 text-[#f59e0b] text-[10px] mb-1">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                        <span class="text-gray-400 font-bold ml-1">(4.9)</span>
                                    </div>
                                    <h3 class="font-sans font-extrabold text-xs sm:text-sm text-[#12090c] leading-snug line-clamp-2 hover:text-[#990024] transition">
                                        <a href="${BASE_URL}product?slug=${prod.slug}">${prod.name}</a>
                                    </h3>
                                </div>
                                <div class="mt-2.5 pt-2 sm:pt-2.5 border-t border-gray-100 space-y-2">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <span class="text-xs sm:text-base font-black text-[#990024]">${Utils.formatCurrency(price)}</span>
                                            ${mrp ? `<span class="text-[10px] sm:text-xs text-gray-400 line-through font-bold ml-1">${Utils.formatCurrency(mrp)}</span>` : ''}
                                        </div>

                                        <button onclick="Shop.quickAdd(${prod.id})" 
                                            ${parseInt(prod.stock_quantity) <= 0 ? 'disabled' : ''} 
                                            class="w-8 h-8 sm:w-9 sm:h-9 rounded-full border border-[#990024] text-[#990024] hover:bg-[#990024] hover:text-white disabled:bg-gray-100 disabled:border-gray-200 disabled:text-gray-400 transition duration-200 flex items-center justify-center cursor-pointer shrink-0" 
                                            aria-label="Add ${prod.name} to cart" 
                                            title="Add to Cart">
                                            <i class="fas fa-shopping-bag text-xs"></i>
                                        </button>
                                    </div>
                                    <button onclick="Utils.buyNow(${prod.id}, 1, null, this)" 
                                        ${parseInt(prod.stock_quantity) <= 0 ? 'disabled' : ''} 
                                        class="w-full py-1.5 sm:py-2 px-3 rounded-xl bg-[#990024] hover:bg-[#7a001c] disabled:bg-gray-200 disabled:text-gray-400 text-white font-extrabold text-[10px] sm:text-xs shadow-2xs hover:shadow-xs transition duration-200 flex items-center justify-center space-x-1.5 cursor-pointer" 
                                        aria-label="Buy ${prod.name} now" 
                                        title="Buy Now (Direct Checkout)">
                                        <i class="fas fa-bolt text-[9px] sm:text-[10px] text-amber-300"></i>
                                        <span>Buy Now</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });

                if (append) {
                    grid.innerHTML += html;
                } else {
                    grid.innerHTML = html;
                }

                // Handle Load More Button Visibility
                if (btnLoadMore) {
                    if (res.pagination.current_page < res.pagination.total_pages) {
                        btnLoadMore.classList.remove('hidden');
                        btnLoadMore.innerHTML = 'Load More';
                        btnLoadMore.disabled = false;
                    } else {
                        btnLoadMore.classList.add('hidden');
                    }
                }

                // Close mobile filters if open
                if (window.innerWidth < 1024) {
                    const sidebar = document.getElementById('filter-sidebar');
                    if (!sidebar.classList.contains('-translate-x-full')) {
                        this.toggleMobileFilters();
                    }
                }
            }
        } catch (e) {
            if (!append) {
                grid.innerHTML = `<span class="text-red-500 col-span-full py-10 text-center text-xs font-bold">Failed to load catalog: ${e.message}</span>`;
            } else {
                if (btnLoadMore) {
                    btnLoadMore.innerHTML = 'Load More';
                    btnLoadMore.disabled = false;
                }
            }
        }
    },

    loadMore() {
        this.filters.page++;
        
        // Update URL quietly
        const url = new URL(window.location.href);
        url.searchParams.set('page', this.filters.page);
        window.history.replaceState({}, '', url.toString());

        this.loadProducts(true);
    },

    // renderPagination removed as we use Load More

    goToPage(page) {
        this.updateQueryParam('page', page);
    },

    /**
     * Trigger quick add to cart (requires login verification)
     *
     * @param {number} productId
     */
    async quickAdd(productId) {
        if (!Auth.isLoggedIn()) {
            Utils.showToast("Please login to add items to your cart.", "info");
            setTimeout(() => {
                window.location.href = BASE_URL + 'login?redirect=shop';
            }, 1000);
            return;
        }
        
        try {
            await Api.post('/cart/add', {
                product_id: productId,
                quantity: 1
            });
            Utils.showToast("Product added to cart!", "success");
            
            // Trigger cart count update
            if (window.CartModule) {
                window.CartModule.updateCartBadge();
            }
        } catch (e) {
            Utils.showToast(e.message || "Failed to add to cart.", "error");
        }
    },

    clearAllFilters() {
        window.location.href = BASE_URL + 'shop';
    },

    updateQueryParam(key, value) {
        const url = new URL(window.location.href);
        if (value !== '') {
            url.searchParams.set(key, value);
            this.filters[key] = value;
        } else {
            url.searchParams.delete(key);
            this.filters[key] = '';
        }
        window.history.pushState({}, '', url.toString());
        this.loadProducts(false);
    },

    updateQueryParams(params) {
        const url = new URL(window.location.href);
        for (let key in params) {
            if (params[key] !== '') {
                url.searchParams.set(key, params[key]);
                this.filters[key] = params[key];
            } else {
                url.searchParams.delete(key);
                this.filters[key] = '';
            }
        }
        window.history.pushState({}, '', url.toString());
        this.loadProducts(false);
    }
};

window.Shop = Shop;
document.addEventListener('DOMContentLoaded', () => {
    Shop.init();
});
