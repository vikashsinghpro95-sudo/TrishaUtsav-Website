/**
 * Trisha Utsav - Festive Occasions Frontend Controller
 * Optimized Chip-Based Filters, AJAX Loading, Mobile-Friendly UI
 */
const OccasionPage = {
    selectedSlug: '',
    selectedId: null,
    currentPage: 1,
    currentSort: 'newest',
    perPage: 12,
    allOccasions: [],
    products: [],
    totalItems: 0,
    totalPages: 1,
    isLoading: false,
    isLoadingMore: false,

    init() {
        // Read URL query parameters (?occasion=diwali, ?slug=diwali, ?occasion_id=1, ?id=1)
        const params = new URLSearchParams(window.location.search);
        if (params.has('occasion')) {
            const val = params.get('occasion');
            if (val && val !== 'all') {
                if (isNaN(val)) this.selectedSlug = val;
                else this.selectedId = parseInt(val);
            }
        } else if (params.has('slug')) {
            const val = params.get('slug');
            if (val && val !== 'all') this.selectedSlug = val;
        } else if (params.has('occasion_id')) {
            const val = params.get('occasion_id');
            if (val && val !== 'all') this.selectedId = parseInt(val);
        } else if (params.has('id')) {
            const val = params.get('id');
            if (val && val !== 'all') this.selectedId = parseInt(val);
        }

        if (params.has('sort')) {
            this.currentSort = params.get('sort');
            const sortSelect = document.getElementById('sort-select');
            if (sortSelect) sortSelect.value = this.currentSort;
        }

        if (params.has('page')) {
            this.currentPage = parseInt(params.get('page')) || 1;
        }

        // Use inline initial occasions if available for instant render
        if (window.INITIAL_OCCASIONS && Array.isArray(window.INITIAL_OCCASIONS) && window.INITIAL_OCCASIONS.length > 0) {
            this.allOccasions = window.INITIAL_OCCASIONS;
            this.matchSelectedOccasion();
            this.renderOccasionChips();
            this.loadProducts(false);
        } else {
            this.loadOccasions();
        }

        // Listen to browser back/forward navigation
        window.addEventListener('popstate', (e) => this.handlePopState(e));
    },

    matchSelectedOccasion() {
        if (!this.allOccasions || this.allOccasions.length === 0) return;
        if (this.selectedSlug) {
            const match = this.allOccasions.find(o => o.slug === this.selectedSlug || o.slug.toLowerCase() === this.selectedSlug.toLowerCase());
            if (match) {
                this.selectedId = match.id;
            }
        } else if (this.selectedId) {
            const match = this.allOccasions.find(o => parseInt(o.id) === parseInt(this.selectedId));
            if (match) {
                this.selectedSlug = match.slug;
            }
        }
    },

    async loadOccasions() {
        try {
            const res = await Api.get('/occasions');
            if (res.success && Array.isArray(res.data)) {
                this.allOccasions = res.data;
                this.matchSelectedOccasion();
                this.renderOccasionChips();
            } else {
                this.renderOccasionChips();
            }
        } catch (e) {
            console.error("Failed to load occasions:", e);
            this.renderOccasionChips();
        } finally {
            this.loadProducts(false);
        }
    },

    renderOccasionChips() {
        const container = document.getElementById('occasions-cards-container');
        if (!container) return;

        const isAllSelected = !this.selectedSlug && !this.selectedId;

        let html = `
            <!-- All Chip -->
            <button type="button" onclick="OccasionPage.selectOccasion('all')" 
                class="chip-item inline-flex items-center space-x-1.5 px-3 py-1.5 sm:px-4 sm:py-2.5 rounded-full text-[11px] sm:text-xs font-bold transition-all duration-200 ease-in-out cursor-pointer flex-shrink-0 min-h-[32px] sm:min-h-[42px] border shadow-2xs ${isAllSelected ? 'bg-[#990024] text-white border-[#990024] ring-2 ring-[#990024]/30 shadow-xs font-extrabold' : 'bg-white hover:bg-amber-500/5 text-gray-700 border-gray-200 hover:border-[#990024]/40 hover:text-[#990024]'}">
                ${isAllSelected ? '<i class="fas fa-check-circle text-amber-400 text-[10px] sm:text-xs shrink-0"></i>' : '<span class="text-xs sm:text-sm leading-none">✨</span>'}
                <span class="whitespace-nowrap">All Occasions</span>
            </button>
        `;

        this.allOccasions.forEach(occ => {
            const isSelected = (this.selectedSlug && occ.slug.toLowerCase() === this.selectedSlug.toLowerCase()) || 
                               (this.selectedId && parseInt(this.selectedId) === parseInt(occ.id));
            const emoji = occ.emoji || '🎉';
            const name = Utils.escapeHtml(occ.name);
            const count = occ.product_count || 0;

            html += `
                <button type="button" onclick="OccasionPage.selectOccasion('${occ.slug}', ${occ.id})" 
                    id="chip-occ-${occ.id}"
                    class="chip-item inline-flex items-center space-x-1.5 px-3 py-1.5 sm:px-4 sm:py-2.5 rounded-full text-[11px] sm:text-xs font-bold transition-all duration-200 ease-in-out cursor-pointer flex-shrink-0 min-h-[32px] sm:min-h-[42px] border shadow-2xs ${isSelected ? 'bg-[#990024] text-white border-[#990024] ring-2 ring-[#990024]/30 shadow-xs font-extrabold' : 'bg-white hover:bg-amber-500/5 text-gray-700 border-gray-200 hover:border-[#990024]/40 hover:text-[#990024]'}">
                    ${isSelected ? '<i class="fas fa-check-circle text-amber-400 text-[10px] sm:text-xs shrink-0"></i>' : `<span class="text-xs sm:text-sm leading-none">${emoji}</span>`}
                    <span class="whitespace-nowrap">${name}</span>
                    ${count > 0 ? `<span class="text-[9px] sm:text-[10px] px-1.5 py-0.2 rounded-full ${isSelected ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500'}">${count}</span>` : ''}
                </button>
            `;
        });

        container.innerHTML = html;

        // Auto-scroll active chip into view smoothly
        setTimeout(() => {
            const activeChip = container.querySelector('.bg-\\[\\#990024\\]');
            if (activeChip) {
                activeChip.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }
        }, 100);
    },

    scrollChips(offset) {
        const container = document.getElementById('occasions-cards-container');
        if (container) {
            container.scrollBy({ left: offset, behavior: 'smooth' });
        }
    },

    selectOccasion(slug, id = null) {
        if (slug === 'all' || !slug) {
            this.selectedSlug = '';
            this.selectedId = null;
        } else {
            this.selectedSlug = slug;
            this.selectedId = id;
        }
        this.currentPage = 1;

        // Update URL state cleanly without full page refresh
        const newUrl = this.selectedSlug ? `${window.location.pathname}?occasion=${encodeURIComponent(this.selectedSlug)}` : window.location.pathname;
        window.history.pushState({ occasion: this.selectedSlug, id: this.selectedId }, '', newUrl);

        // Update active chip visuals instantly
        this.renderOccasionChips();

        // Fetch products dynamically
        this.loadProducts(false);
    },

    handlePopState(e) {
        const params = new URLSearchParams(window.location.search);
        const occasion = params.get('occasion') || params.get('slug') || '';
        this.selectedSlug = occasion;
        this.selectedId = null;
        this.currentPage = parseInt(params.get('page')) || 1;
        this.currentSort = params.get('sort') || 'newest';

        this.matchSelectedOccasion();
        this.renderOccasionChips();
        this.loadProducts(false);
    },

    async loadProducts(append = false) {
        if (this.isLoading) return;
        this.isLoading = true;

        const countLabel = document.getElementById('products-count-label');
        const heading = document.getElementById('products-grid-heading');

        if (!append) {
            this.renderSkeletonLoader();
        } else {
            this.setLoadMoreState(true);
        }

        try {
            let endpoint = `/occasions-products?page=${this.currentPage}&limit=${this.perPage}&sort=${this.currentSort}`;
            if (this.selectedId) {
                endpoint += `&occasion_id=${this.selectedId}`;
            } else if (this.selectedSlug) {
                endpoint += `&occasion=${encodeURIComponent(this.selectedSlug)}`;
            }

            const res = await Api.get(endpoint);

            if (res.success) {
                const fetchedProducts = res.data || [];
                this.totalItems = res.total ?? fetchedProducts.length;
                this.totalPages = res.last_page ?? 1;

                if (append) {
                    this.products = [...this.products, ...fetchedProducts];
                } else {
                    this.products = fetchedProducts;
                }

                // Update Active Occasion Banner
                if (res.occasion) {
                    this.updateActiveOccasionBanner(res.occasion);
                } else if (this.selectedSlug || this.selectedId) {
                    const match = this.allOccasions.find(o => o.slug === this.selectedSlug || parseInt(o.id) === parseInt(this.selectedId));
                    this.updateActiveOccasionBanner(match || null);
                } else {
                    this.updateActiveOccasionBanner(null);
                }

                // Update Heading & Count Label
                if (heading) {
                    if (res.occasion && res.occasion.name) {
                        heading.innerText = `${res.occasion.name} Collection`;
                    } else if (this.selectedSlug) {
                        const match = this.allOccasions.find(o => o.slug === this.selectedSlug);
                        heading.innerText = match ? `${match.name} Collection` : `Festive Collection`;
                    } else {
                        heading.innerText = `All Festive Products`;
                    }
                }

                if (countLabel) {
                    const shownCount = this.products.length;
                    countLabel.innerText = `Showing ${shownCount} of ${this.totalItems} product${this.totalItems === 1 ? '' : 's'}`;
                }

                this.renderProductGrid(append ? fetchedProducts : this.products, append);
                this.renderPaginationControls();

            } else {
                this.renderEmptyState();
            }
        } catch (e) {
            console.error("Failed to load products for occasion:", e);
            this.renderEmptyState();
        } finally {
            this.isLoading = false;
            this.isLoadingMore = false;
            this.setLoadMoreState(false);
        }
    },

    async loadMoreProducts() {
        if (this.currentPage >= this.totalPages || this.isLoading) return;
        this.currentPage++;
        await this.loadProducts(true);
    },

    setLoadMoreState(loading) {
        const btn = document.getElementById('btn-load-more');
        const text = document.getElementById('load-more-text');
        const icon = document.getElementById('load-more-icon');
        if (!btn || !text || !icon) return;

        if (loading) {
            btn.disabled = true;
            text.innerText = 'Loading More...';
            icon.className = 'fas fa-spinner fa-spin text-xs ml-1';
        } else {
            btn.disabled = false;
            text.innerText = 'Load More Products';
            icon.className = 'fas fa-chevron-down text-xs ml-1';
        }
    },

    updateActiveOccasionBanner(occasion) {
        const banner = document.getElementById('active-occasion-banner');
        if (!banner) return;

        if (occasion) {
            banner.classList.remove('hidden');
            const titleEl = document.getElementById('active-occ-title');
            const descEl = document.getElementById('active-occ-desc');
            const countEl = document.getElementById('active-occ-count');
            const badgeTextEl = document.getElementById('active-occ-badge-text');

            if (titleEl) titleEl.innerText = `${occasion.name} Collection`;
            if (descEl) descEl.innerText = occasion.description || `Hand-curated premium festive products for ${occasion.name}.`;
            if (countEl) countEl.innerText = `${occasion.product_count || this.totalItems} Item${(occasion.product_count || this.totalItems) === 1 ? '' : 's'} Tagged`;
            if (badgeTextEl) badgeTextEl.innerText = occasion.name.toUpperCase() + ' SELECTION';
        } else {
            banner.classList.add('hidden');
        }
    },

    renderSkeletonLoader() {
        const grid = document.getElementById('occasion-products-grid');
        if (!grid) return;

        const skeletons = Array(8).fill(0).map(() => `
            <div class="bg-white rounded-2xl border border-gray-100 p-3 flex flex-col justify-between space-y-3 animate-pulse shadow-2xs">
                <div class="w-full aspect-square bg-gray-200 rounded-xl"></div>
                <div class="space-y-2">
                    <div class="h-3 bg-gray-200 rounded w-1/3"></div>
                    <div class="h-4 bg-gray-200 rounded w-4/5"></div>
                    <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                    <div class="h-5 bg-gray-200 rounded w-1/2"></div>
                    <div class="w-9 h-9 bg-gray-200 rounded-full"></div>
                </div>
            </div>
        `).join('');

        grid.innerHTML = skeletons;
    },

    renderProductGrid(productsToRender, isAppend = false) {
        const grid = document.getElementById('occasion-products-grid');
        if (!grid) return;

        if (!isAppend && productsToRender.length === 0) {
            this.renderEmptyState();
            return;
        }

        const html = productsToRender.map(p => {
            const primaryImg = p.images && p.images.length > 0 ? Utils.fixImageUrl(p.images[0].image_url) : (p.primary_image ? Utils.fixImageUrl(p.primary_image) : `${BASE_URL}assets/images/product_placeholder.jpg`);
            const secondaryImg = p.images && p.images.length > 1 ? Utils.fixImageUrl(p.images[1].image_url) : primaryImg;
            const price = parseFloat(p.price || 0);
            const origPrice = parseFloat(p.mrp || p.original_price || p.compare_at_price || 0);
            const hasDiscount = origPrice > price;
            const discountPercent = hasDiscount ? Math.round(((origPrice - price) / origPrice) * 100) : 0;
            const productUrl = `${BASE_URL}product.php?slug=${p.slug}`;

            return `
                <div class="product-card group bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-2xs hover:shadow-xl hover:border-[#990024]/30 transition-all duration-300 flex flex-col justify-between overflow-hidden relative animate-fade-in">
                    <!-- Image Container with Aspect Ratio -->
                    <div class="relative overflow-hidden aspect-square bg-gray-50">
                        <a href="${productUrl}" class="block w-full h-full">
                            <img src="${primaryImg}" 
                                 alt="${Utils.escapeHtml(p.name)}" 
                                 loading="lazy" 
                                 width="300" 
                                 height="300"
                                 onerror="this.onerror=null;this.src='${BASE_URL}assets/images/product_placeholder.jpg';" 
                                 class="w-full h-full object-cover transition-opacity duration-500 ease-in-out group-hover:opacity-0">
                            <img src="${secondaryImg}" 
                                 alt="${Utils.escapeHtml(p.name)} Alternate" 
                                 loading="lazy" 
                                 width="300" 
                                 height="300"
                                 onerror="this.onerror=null;this.src='${BASE_URL}assets/images/product_placeholder.jpg';" 
                                 class="absolute inset-0 w-full h-full object-cover transition-all duration-500 ease-in-out opacity-0 group-hover:opacity-100 group-hover:scale-105">
                        </a>

                        ${hasDiscount ? `
                            <span class="absolute top-2.5 left-2.5 bg-[#990024] text-white font-black text-[9px] uppercase tracking-wider px-2 py-0.5 rounded-full shadow-md z-10">
                                -${discountPercent}% OFF
                            </span>
                        ` : ''}

                        ${p.stock_quantity <= 0 ? `
                            <span class="absolute top-2.5 right-2.5 bg-gray-900/90 text-white font-black text-[9px] uppercase tracking-wider px-2 py-0.5 rounded-full shadow-md z-10">
                                Out of Stock
                            </span>
                        ` : ''}
                    </div>

                    <!-- Details Container -->
                    <div class="p-3 sm:p-4 flex flex-col flex-grow justify-between space-y-2.5">
                        <div class="space-y-1">
                            ${p.category_name ? `
                                <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-[#f59e0b] block truncate">${Utils.escapeHtml(p.category_name)}</span>
                            ` : ''}
                            <h3 class="font-sans font-extrabold text-xs sm:text-sm text-[#12090c] leading-snug line-clamp-2 hover:text-[#990024] transition">
                                <a href="${productUrl}">${Utils.escapeHtml(p.name)}</a>
                            </h3>
                        </div>

                        <div class="mt-2.5 pt-2 sm:pt-2.5 border-t border-gray-100 space-y-2">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-xs sm:text-base font-black text-[#990024]">${Utils.formatCurrency(price)}</span>
                                    ${hasDiscount ? `<span class="text-[10px] sm:text-xs text-gray-400 line-through font-bold ml-1">${Utils.formatCurrency(origPrice)}</span>` : ''}
                                </div>

                                <button onclick="OccasionPage.addToCart(${p.id})" 
                                    ${p.stock_quantity <= 0 ? 'disabled' : ''} 
                                    class="w-8 h-8 sm:w-9 sm:h-9 rounded-full border border-[#990024] text-[#990024] hover:bg-[#990024] hover:text-white disabled:bg-gray-100 disabled:border-gray-200 disabled:text-gray-400 transition duration-200 flex items-center justify-center cursor-pointer shrink-0" 
                                    aria-label="Add ${Utils.escapeHtml(p.name)} to cart" 
                                    title="Add to Cart">
                                    <i class="fas fa-shopping-bag text-xs"></i>
                                </button>
                            </div>
                            <button onclick="Utils.buyNow(${p.id}, 1, null, this)" 
                                ${p.stock_quantity <= 0 ? 'disabled' : ''} 
                                class="w-full py-1.5 sm:py-2 px-3 rounded-xl bg-[#990024] hover:bg-[#7a001c] disabled:bg-gray-200 disabled:text-gray-400 text-white font-extrabold text-[10px] sm:text-xs shadow-2xs hover:shadow-xs transition duration-200 flex items-center justify-center space-x-1.5 cursor-pointer" 
                                aria-label="Buy ${Utils.escapeHtml(p.name)} now" 
                                title="Buy Now (Direct Checkout)">
                                <i class="fas fa-bolt text-[9px] sm:text-[10px] text-amber-300"></i>
                                <span>Buy Now</span>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        if (isAppend) {
            grid.insertAdjacentHTML('beforeend', html);
        } else {
            grid.innerHTML = html;
        }
    },

    renderEmptyState() {
        const grid = document.getElementById('occasion-products-grid');
        const countLabel = document.getElementById('products-count-label');
        const loadMoreContainer = document.getElementById('load-more-container');
        const paginationContainer = document.getElementById('occasion-pagination-container');

        if (countLabel) countLabel.innerText = "0 products found";
        if (loadMoreContainer) loadMoreContainer.classList.add('hidden');
        if (paginationContainer) paginationContainer.innerHTML = '';

        if (grid) {
            grid.innerHTML = `
                <div class="col-span-full py-12 sm:py-16 text-center space-y-4 bg-white p-6 sm:p-10 rounded-3xl border border-gray-100 shadow-xs animate-fade-in">
                    <div class="w-16 h-16 bg-[#990024]/10 text-[#990024] rounded-full flex items-center justify-center mx-auto text-2xl border border-[#990024]/20 shadow-sm">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="font-display text-base sm:text-lg font-bold text-[#12090c]">No products for this occasion yet</h4>
                        <p class="text-xs text-gray-500 font-medium max-w-md mx-auto">We are actively expanding our festive collection. Try clearing your filter to view all available products.</p>
                    </div>
                    <button onclick="OccasionPage.selectOccasion('all')" class="inline-flex items-center space-x-2 bg-[#990024] text-white text-xs font-black px-6 py-3 rounded-full shadow-md uppercase tracking-wider hover:bg-[#7a001c] transition">
                        <i class="fas fa-rotate-left mr-1"></i>
                        <span>Browse All Products</span>
                    </button>
                </div>
            `;
        }
    },

    renderPaginationControls() {
        const loadMoreContainer = document.getElementById('load-more-container');
        const paginationContainer = document.getElementById('occasion-pagination-container');

        // Toggle Load More Button
        if (loadMoreContainer) {
            if (this.currentPage < this.totalPages) {
                loadMoreContainer.classList.remove('hidden');
            } else {
                loadMoreContainer.classList.add('hidden');
            }
        }

        // Toggle Page Numbers Pagination
        if (!paginationContainer) return;
        if (this.totalPages <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let html = '';
        for (let i = 1; i <= this.totalPages; i++) {
            const isActive = i === this.currentPage;
            html += `
                <button onclick="OccasionPage.goToPage(${i})" 
                    class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl font-extrabold text-xs transition cursor-pointer ${isActive ? 'bg-[#990024] text-white shadow-md' : 'bg-white border border-gray-200 text-gray-700 hover:border-[#990024] hover:text-[#990024]'}">
                    ${i}
                </button>
            `;
        }
        paginationContainer.innerHTML = html;
    },

    goToPage(page) {
        if (page === this.currentPage || page < 1 || page > this.totalPages) return;
        this.currentPage = page;

        // Update URL query parameter
        const params = new URLSearchParams(window.location.search);
        params.set('page', page);
        const newUrl = `${window.location.pathname}?${params.toString()}`;
        window.history.pushState({ page }, '', newUrl);

        this.loadProducts(false);
        const gridHeading = document.getElementById('products-grid-heading');
        if (gridHeading) {
            gridHeading.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    },

    handleSortChange(val) {
        this.currentSort = val;
        this.currentPage = 1;

        const params = new URLSearchParams(window.location.search);
        params.set('sort', val);
        params.set('page', 1);
        const newUrl = `${window.location.pathname}?${params.toString()}`;
        window.history.pushState({ sort: val }, '', newUrl);

        this.loadProducts(false);
    },

    async addToCart(productId) {
        if (!localStorage.getItem('auth_token')) {
            Utils.showToast("Please login to add items to your cart.", "info");
            setTimeout(() => { window.location.href = `${BASE_URL}login.php`; }, 1000);
            return;
        }

        try {
            await Api.post('/cart/add', { product_id: productId, quantity: 1 });
            Utils.showToast("Item added to your shopping cart!", "success");
            if (window.CartModule) window.CartModule.updateCartBadge();
        } catch (e) {
            Utils.showToast(e.message || "Failed to add product to cart.", "error");
        }
    }
};

document.addEventListener('DOMContentLoaded', () => OccasionPage.init());
