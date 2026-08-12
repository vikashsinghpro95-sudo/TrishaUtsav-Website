/**
 * TrishaUtsav — Product Detail Page Module (Editorial Design)
 */

const ProductPage = {
    product: null,
    selectedAttributes: {},
    galleryIndex: 0,
    galleryImages: [],

    async init() {
        const slug = Utils.getQueryParam('slug') || Utils.getQueryParam('id');
        if (!slug) {
            window.location.href = BASE_URL + '404.php';
            return;
        }

        // Skeleton is already rendered in PHP – just fetch data
        try {
            const res = await Api.get('/products/' + slug);
            if (res.success && res.data) {
                this.product = res.data;

                // Dynamically update document title and SEO meta keywords/description
                const p = this.product;
                if (p.meta_title) {
                    document.title = p.meta_title;
                } else if (p.name) {
                    document.title = `${p.name} | Trisha Utsav`;
                }

                if (p.meta_keywords) {
                    let metaKw = document.querySelector('meta[name="keywords"]');
                    if (!metaKw) {
                        metaKw = document.createElement('meta');
                        metaKw.name = 'keywords';
                        document.head.appendChild(metaKw);
                    }
                    metaKw.content = p.meta_keywords;
                }

                if (p.meta_description || p.short_description) {
                    let metaDesc = document.querySelector('meta[name="description"]');
                    if (!metaDesc) {
                        metaDesc = document.createElement('meta');
                        metaDesc.name = 'description';
                        document.head.appendChild(metaDesc);
                    }
                    metaDesc.content = p.meta_description || p.short_description;
                }

                this.renderDetails();
                this.loadRelatedProducts();
                this.initStickyBar();
            } else {
                window.location.href = BASE_URL + '404.php';
            }
        } catch (e) {
            const container = document.getElementById('pdp-container');
            if (container) {
                container.innerHTML = `
                    <div class="text-center py-20" style="grid-column:1/-1">
                        <i class="fas fa-exclamation-triangle text-red-400 text-4xl mb-4"></i>
                        <h3 class="font-display text-lg text-gray-700">Failed to load product</h3>
                        <p class="text-gray-500 text-sm mt-1">${e.message || 'Product not found.'}</p>
                        <a href="${BASE_URL}shop" class="btn-gold mt-6 inline-flex">Back to Shop</a>
                    </div>
                `;
            }
        }
    },

    renderDetails() {
        const container = document.getElementById('pdp-container');
        if (!container) return;

        const prod = this.product;
        const basePrice = parseFloat(prod.price);
        const mrp = prod.mrp ? parseFloat(prod.mrp) : null;
        const discount = mrp && mrp > basePrice ? Math.round(((mrp - basePrice) / mrp) * 100) : 0;

        // Group attributes
        const groupedAttrs = {};
        if (prod.attributes && prod.attributes.length > 0) {
            prod.attributes.forEach(attr => {
                const name = attr.attribute_name;
                if (!groupedAttrs[name]) groupedAttrs[name] = [];
                groupedAttrs[name].push(attr);
            });
        }
        for (let name in groupedAttrs) {
            this.selectedAttributes[name] = groupedAttrs[name][0].attribute_value;
        }

        // Build gallery images
        this.galleryImages = [];
        // Robust image URL fixer
        const fixImg = (path) => {
            if (!path) return '';
            if (path.startsWith('http://') || path.startsWith('https://')) return path;
            // Remove any leading slash(es) and prepend exactly one
            return '/' + path.replace(/^\/+/, '');
        };

        if (prod.primary_image) this.galleryImages.push(fixImg(prod.primary_image));
        if (prod.images && prod.images.length > 0) {
            prod.images.forEach(img => {
                const url = fixImg(img.image_url);
                if (url && !this.galleryImages.includes(url)) this.galleryImages.push(url);
            });
        }
        if (this.galleryImages.length === 0) {
            this.galleryImages.push(`${BASE_URL}assets/images/product_placeholder.jpg`);
        }

        const placeholder = `${BASE_URL}assets/images/product_placeholder.jpg`;

        // Gallery slides
        const slidesHtml = this.galleryImages.map((url, i) => `
            <div class="pdp-main-slide">
                <img src="${url}" alt="${prod.name} – Image ${i + 1}" onerror="this.onerror=null;this.src='${placeholder}';" draggable="false" loading="${i === 0 ? 'eager' : 'lazy'}">
            </div>
        `).join('');

        // Dots (mobile)
        const dotsHtml = this.galleryImages.length > 1 ? `
            <div class="pdp-dots lg:hidden" id="pdp-dots">
                ${this.galleryImages.map((_, i) => `<button class="pdp-dot ${i === 0 ? 'active' : ''}" onclick="ProductPage.goToSlide(${i})" aria-label="Image ${i + 1}"></button>`).join('')}
            </div>
        ` : '';

        // Thumbnails – now correctly hidden on mobile (only visible on lg screens)
        const thumbsHtml = this.galleryImages.length > 1 ? `
            <div class="pdp-thumbs hidden lg:flex" id="pdp-thumbs">
                ${this.galleryImages.map((url, i) => `
                    <button class="pdp-thumb ${i === 0 ? 'active' : ''}" onclick="ProductPage.goToSlide(${i})" aria-label="Thumbnail ${i + 1}">
                        <img src="${url}" alt="" onerror="this.onerror=null;this.src='${placeholder}';" loading="lazy">
                    </button>
                `).join('')}
            </div>
        ` : '';

        // Variants
        let variantsHtml = '';
        for (let name in groupedAttrs) {
            variantsHtml += `
                <div class="mb-4">
                    <span class="eyebrow block mb-2">${name}</span>
                    <div class="flex flex-wrap gap-2">
            `;
            groupedAttrs[name].forEach(attr => {
                const isSelected = this.selectedAttributes[name] === attr.attribute_value;
                const extra = parseFloat(attr.extra_price) > 0 ? ` (+₹${parseFloat(attr.extra_price)})` : '';
                variantsHtml += `
                    <button type="button"
                            onclick="ProductPage.selectVariant(this, '${name}', '${attr.attribute_value}')"
                            class="variant-pill ${isSelected ? 'selected' : ''}"
                            aria-pressed="${isSelected}">
                        ${attr.attribute_value}${extra}
                    </button>
                `;
            });
            variantsHtml += `</div></div>`;
        }

        // Stock
        const isOutOfStock = parseInt(prod.stock_quantity) <= 0;
        const isLowStock = !isOutOfStock && parseInt(prod.stock_quantity) <= parseInt(prod.low_stock_threshold);
        let stockHtml = '';
        if (isOutOfStock) {
            stockHtml = `<span class="bg-red-100 text-red-700 text-xs font-black px-3 py-1 rounded-full border border-red-200 shadow-sm inline-flex items-center"><i class="fas fa-times-circle mr-1.5"></i> Out of Stock</span>`;
        } else if (isLowStock) {
            stockHtml = `<span class="bg-amber-100 text-amber-800 text-xs font-black px-3 py-1 rounded-full border border-amber-200 shadow-sm animate-pulse inline-flex items-center"><i class="fas fa-exclamation-circle mr-1.5"></i> Only ${prod.stock_quantity} left!</span>`;
        } else {
            stockHtml = `<span class="bg-emerald-100 text-emerald-800 text-xs font-black px-3 py-1 rounded-full border border-emerald-200 shadow-sm inline-flex items-center"><i class="fas fa-check-circle mr-1.5"></i> In Stock</span>`;
        }

        // Description / Specs for accordion
        const descContent = prod.description || '<p>Authentic handcrafted Indian celebration item curated with royal precision.</p>';
        let specsRows = '';
        if (prod.weight) specsRows += `<tr class="border-b border-gray-100"><td class="px-3 py-2.5 font-semibold bg-gray-50/60 w-2/5 text-[11px]">Weight</td><td class="px-3 py-2.5 text-[11px]">${prod.weight} kg</td></tr>`;
        if (prod.dimensions) specsRows += `<tr class="border-b border-gray-100"><td class="px-3 py-2.5 font-semibold bg-gray-50/60 w-2/5 text-[11px]">Dimensions</td><td class="px-3 py-2.5 text-[11px]">${prod.dimensions}</td></tr>`;
        if (prod.sku) specsRows += `<tr class="border-b border-gray-100"><td class="px-3 py-2.5 font-semibold bg-gray-50/60 w-2/5 text-[11px]">SKU</td><td class="px-3 py-2.5 text-[11px]">${prod.sku}</td></tr>`;
        if (prod.brand_name) specsRows += `<tr><td class="px-3 py-2.5 font-semibold bg-gray-50/60 w-2/5 text-[11px]">Brand</td><td class="px-3 py-2.5 text-[11px]">${prod.brand_name}</td></tr>`;

        // ============ BUILD LAYOUT ============
        container.innerHTML = `
            <!-- Gallery Rail (Left) -->
            <div class="pdp-gallery-wrapper">
                <div class="pdp-gallery">
                    <div class="pdp-main-swipe relative" id="pdp-gallery">
                        ${slidesHtml}
                        ${discount > 0 ? `<span class="absolute top-4 left-4 bg-[#f59e0b] text-[#12090c] text-xs font-black px-4 py-1.5 rounded-full shadow-lg z-10 uppercase tracking-widest">-${discount}% OFF</span>` : ''}
                    </div>
                    ${dotsHtml}
                </div>
                ${thumbsHtml}
            </div>

            <!-- Details Panel (Right) -->
            <div class="pdp-details-wrapper pdp-buy">
                <!-- Brand & Stock Row -->
                <div class="flex items-center justify-between gap-2 mb-4 flex-wrap">
                    <span class="eyebrow">${prod.brand_name || 'Trisha Utsav'}</span>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="ProductPage.shareProduct()" class="text-gray-400 hover:text-[#990024] transition flex items-center justify-center w-8 h-8 rounded-full hover:bg-[#990024]/10" aria-label="Share Product" title="Share Product">
                            <i class="fas fa-share-nodes"></i>
                        </button>
                        ${stockHtml}
                    </div>
                </div>

                <!-- Title -->
                <h1 class="font-display font-black text-3xl sm:text-4xl text-[#12090c] mb-4 leading-tight tracking-tight">${prod.name}</h1>

                <!-- Rating -->
                <div class="flex items-center gap-2 mb-6">
                    <div class="flex items-center gap-1 text-[#f59e0b] text-sm">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <span class="text-[#12090c] font-black text-sm">4.9</span>
                    <span class="text-gray-400 font-bold text-xs ml-1">(Handcrafted Rating)</span>
                </div>

                <div class="rule-gold my-6"></div>

                <!-- Price Block -->
                <div class="flex items-center gap-4 mb-6 flex-wrap">
                    <span id="pdp-price" class="font-display text-4xl sm:text-5xl font-black text-[#990024] tracking-tight">${Utils.formatCurrency(basePrice)}</span>
                    <div class="flex flex-col">
                        ${mrp ? `<span class="text-sm text-gray-400 line-through font-bold">${Utils.formatCurrency(mrp)}</span>` : ''}
                        ${discount > 0 ? `<span class="text-[#f59e0b] text-xs font-black uppercase tracking-wider mt-0.5">SAVE ${discount}%</span>` : ''}
                    </div>
                </div>

                <!-- Short Description -->
                <p class="text-sm sm:text-base text-gray-600 mb-8 leading-relaxed" style="max-width:50ch">${prod.short_description || 'Handcrafted premium Indian festival item, beautifully detailed for your celebrations.'}</p>

                <!-- Variants -->
                ${variantsHtml ? `<div class="mb-8">${variantsHtml}</div>` : ''}

                <!-- Qty + CTAs -->
                <div id="pdp-cta-zone" class="space-y-6 pt-2">
                    <div class="flex items-center gap-4">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Quantity</span>
                        <div class="qty-controls">
                            <button type="button" onclick="ProductPage.adjustQty(-1)" class="qty-btn" aria-label="Decrease quantity"><i class="fas fa-minus"></i></button>
                            <span id="pdp-qty-display" class="qty-value">1</span>
                            <input type="hidden" id="pdp-qty" value="1" min="1" max="${prod.stock_quantity}">
                            <button type="button" onclick="ProductPage.adjustQty(1)" class="qty-btn" aria-label="Increase quantity"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 flex-1 w-full">
                            <button id="btn-add-to-cart" onclick="ProductPage.addToCart(false)" ${isOutOfStock ? 'disabled' : ''} class="btn-secondary-massive">
                                <i class="fas fa-shopping-bag text-lg"></i>
                                <span>ADD TO CART</span>
                            </button>
                            <button id="btn-buy-now" onclick="ProductPage.addToCart(true)" ${isOutOfStock ? 'disabled' : ''} class="btn-primary-massive">
                                <i class="fas fa-bolt text-lg text-[#f59e0b]"></i>
                                <span>BUY NOW</span>
                            </button>
                        </div>
                        
                        <button type="button" 
                            id="pdp-wishlist-btn" 
                            data-wishlist-id="${prod.id}" 
                            onclick="WishlistManager.toggle(${prod.id}, this, event)" 
                            class="w-full sm:w-auto inline-flex items-center justify-center space-x-2 px-6 py-3.5 rounded-full border-2 ${typeof WishlistManager !== 'undefined' && WishlistManager.isWishlisted(prod.id) ? 'border-[#990024] text-[#990024] bg-red-50/50 hover:bg-red-100/50' : 'border-gray-300 hover:border-[#990024] text-gray-700 hover:text-[#990024] bg-white'} font-bold text-xs uppercase tracking-wider transition duration-300 shadow-sm shrink-0">
                            <i class="${typeof WishlistManager !== 'undefined' && WishlistManager.isWishlisted(prod.id) ? 'fas fa-heart text-[#990024]' : 'far fa-heart text-gray-700'}"></i>
                            <span class="wishlist-btn-text">${typeof WishlistManager !== 'undefined' && WishlistManager.isWishlisted(prod.id) ? 'Remove from Wishlist' : 'Add to Wishlist'}</span>
                        </button>
                    </div>
                </div>

                <!-- Trust Badges (Under CTA) -->
                <div class="trust-row">
                    <div class="trust-badge">
                        <div class="trust-icon"><i class="fas fa-truck-fast"></i></div>
                        <div>
                            <div class="trust-title">Free Delivery</div>
                            <div class="trust-desc">On orders ₹499+</div>
                        </div>
                    </div>
                    <div class="trust-badge">
                        <div class="trust-icon"><i class="fas fa-shield-halved"></i></div>
                        <div>
                            <div class="trust-title">100% Secure</div>
                            <div class="trust-desc">Safe Payments</div>
                        </div>
                    </div>
                    <div class="trust-badge">
                        <div class="trust-icon"><i class="fas fa-undo"></i></div>
                        <div>
                            <div class="trust-title">7-Day Returns</div>
                            <div class="trust-desc">Easy exchange</div>
                        </div>
                    </div>
                    <div class="trust-badge">
                        <div class="trust-icon"><i class="fas fa-leaf"></i></div>
                        <div>
                            <div class="trust-title">Handcrafted</div>
                            <div class="trust-desc">Made in India</div>
                        </div>
                    </div>
                </div>

                <!-- Accordion Sections (Moved into details column for desktop) -->
                <div class="pdp-accordions">
                    <div class="pdp-accordion open" data-accordion>
                        <button type="button" class="pdp-accordion-trigger" onclick="ProductPage.toggleAccordion(this)">
                            <span>Product Description</span>
                            <i class="fas fa-chevron-down pdp-accordion-icon"></i>
                        </button>
                        <div class="pdp-accordion-body">
                            <div class="pdp-accordion-content space-y-4">
                                ${descContent}
                            </div>
                        </div>
                    </div>

                    ${specsRows ? `
                    <div class="pdp-accordion" data-accordion>
                        <button type="button" class="pdp-accordion-trigger" onclick="ProductPage.toggleAccordion(this)">
                            <span>Specifications</span>
                            <i class="fas fa-chevron-down pdp-accordion-icon"></i>
                        </button>
                        <div class="pdp-accordion-body">
                            <div class="pdp-accordion-content">
                                <table class="w-full text-gray-600 border border-gray-100 rounded-sm overflow-hidden text-sm">
                                    <tbody>${specsRows}</tbody>
                                </table>
                            </div>
                        </div>
                    </div>` : ''}

                    <div class="pdp-accordion" data-accordion>
                        <button type="button" class="pdp-accordion-trigger" onclick="ProductPage.toggleAccordion(this)">
                            <span>Shipping & Returns</span>
                            <i class="fas fa-chevron-down pdp-accordion-icon"></i>
                        </button>
                        <div class="pdp-accordion-body">
                            <div class="pdp-accordion-content space-y-2">
                                <p><strong>Free shipping</strong> on orders above ₹499. Standard delivery within 5-7 business days.</p>
                                <p>Easy <strong>7-day return policy</strong>. Items must be unused and in original packaging.</p>
                                <p>For queries, contact our <strong>24/7 support team</strong>.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        this.updateDisplayedPrice();
        this.initGallerySwipe();
    },

    // ============ Gallery Logic ============
    initGallerySwipe() {
        const gallery = document.getElementById('pdp-gallery');
        if (!gallery) return;
        gallery.addEventListener('scroll', () => {
            const slideWidth = gallery.scrollWidth / this.galleryImages.length;
            const newIndex = Math.round(gallery.scrollLeft / slideWidth);
            if (newIndex !== this.galleryIndex) {
                this.galleryIndex = newIndex;
                this.updateGalleryIndicators();
            }
        }, { passive: true });
    },

    goToSlide(index) {
        const gallery = document.getElementById('pdp-gallery');
        if (!gallery) return;
        this.galleryIndex = index;
        const slideWidth = gallery.scrollWidth / this.galleryImages.length;
        gallery.scrollTo({ left: slideWidth * index, behavior: 'smooth' });
        this.updateGalleryIndicators();
    },

    updateGalleryIndicators() {
        document.querySelectorAll('#pdp-dots .pdp-dot').forEach((dot, i) => {
            dot.classList.toggle('active', i === this.galleryIndex);
        });
        document.querySelectorAll('#pdp-thumbs .pdp-thumb').forEach((thumb, i) => {
            thumb.classList.toggle('active', i === this.galleryIndex);
        });
    },

    toggleAccordion(trigger) {
        const accordion = trigger.closest('.pdp-accordion');
        if (accordion) accordion.classList.toggle('open');
    },

    // ============ Sticky Bar ============
    initStickyBar() {
        const ctaZone = document.getElementById('pdp-cta-zone');
        const stickyBar = document.getElementById('pdp-sticky-bar');
        if (!ctaZone || !stickyBar) return;

        this.updateStickyBarPrice();

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                stickyBar.classList.toggle('hidden-bar', entry.isIntersecting);
            });
        }, { threshold: 0.1 });
        observer.observe(ctaZone);
    },

    updateStickyBarPrice() {
        const priceEl = document.getElementById('sticky-bar-price');
        const mrpEl = document.getElementById('sticky-bar-mrp');
        if (!priceEl || !this.product) return;

        let price = parseFloat(this.product.price);
        const prodAttrs = this.product.attributes || [];
        for (let name in this.selectedAttributes) {
            const val = this.selectedAttributes[name];
            const match = prodAttrs.find(a => a.attribute_name === name && a.attribute_value === val);
            if (match) price += parseFloat(match.extra_price);
        }
        priceEl.innerText = Utils.formatCurrency(price);
        if (mrpEl && this.product.mrp) mrpEl.innerText = Utils.formatCurrency(this.product.mrp);
    },

    // ============ Variants & Price ============
    selectVariant(button, attributeName, value) {
        this.selectedAttributes[attributeName] = value;
        const container = button.parentNode;
        container.querySelectorAll('.variant-pill').forEach(btn => {
            btn.classList.remove('selected');
            btn.setAttribute('aria-pressed', 'false');
        });
        button.classList.add('selected');
        button.setAttribute('aria-pressed', 'true');
        this.updateDisplayedPrice();
        this.updateStickyBarPrice();
    },

    updateDisplayedPrice() {
        if (!this.product) return;
        let price = parseFloat(this.product.price);
        const prodAttrs = this.product.attributes || [];
        for (let name in this.selectedAttributes) {
            const val = this.selectedAttributes[name];
            const match = prodAttrs.find(a => a.attribute_name === name && a.attribute_value === val);
            if (match) price += parseFloat(match.extra_price);
        }
        const el = document.getElementById('pdp-price');
        if (el) el.innerText = Utils.formatCurrency(price);
    },

    adjustQty(dir) {
        const input = document.getElementById('pdp-qty');
        const display = document.getElementById('pdp-qty-display');
        if (!input) return;
        let val = parseInt(input.value) + dir;
        const max = parseInt(input.max) || 1;
        if (val < 1) val = 1;
        if (val > max) val = max;
        input.value = val;
        if (display) display.textContent = val;
    },

    // ============ Add to Cart & Buy Now ============
    async addToCart(isBuyNow = false) {
        const btn = isBuyNow
            ? (document.getElementById('btn-buy-now') || document.getElementById('sticky-btn-buy'))
            : (document.getElementById('btn-add-to-cart') || document.getElementById('sticky-btn-cart'));
        const qty = parseInt(document.getElementById('pdp-qty')?.value) || 1;

        if (isBuyNow) {
            await Utils.buyNow(this.product.id, qty, null, btn, this.selectedAttributes);
            return;
        }

        if (btn) {
            btn.disabled = true;
            btn._origHtml = btn.innerHTML;
            btn.innerHTML = `<span class="loader-spinner !w-4 !h-4"></span> <span>Adding…</span>`;
        }

        if (!Auth.isLoggedIn()) {
            try {
                let guestCart = JSON.parse(localStorage.getItem('guest_cart')) || [];

                let price = parseFloat(this.product.price);
                const prodAttrs = this.product.attributes || [];
                for (let name in this.selectedAttributes) {
                    const val = this.selectedAttributes[name];
                    const match = prodAttrs.find(a => a.attribute_name === name && a.attribute_value === val);
                    if (match) price += parseFloat(match.extra_price);
                }

                const primaryImg = this.product.primary_image || (this.product.images && this.product.images.length > 0 ? this.product.images[0].image_url : '');

                const existingIndex = guestCart.findIndex(item => {
                    if (item.product_id !== this.product.id) return false;
                    return JSON.stringify(item.attributes) === JSON.stringify(this.selectedAttributes);
                });

                if (existingIndex > -1) {
                    guestCart[existingIndex].quantity += qty;
                } else {
                    guestCart.push({
                        id: Date.now() + Math.floor(Math.random() * 1000),
                        product_id: this.product.id,
                        product_name: this.product.name,
                        product_image: primaryImg,
                        sku: this.product.sku,
                        price: price,
                        quantity: qty,
                        attributes: this.selectedAttributes
                    });
                }

                localStorage.setItem('guest_cart', JSON.stringify(guestCart));

                Utils.showToast("Added to cart!", "success");
                if (window.CartModule) window.CartModule.updateCartBadge();
            } catch (e) {
                Utils.showToast("Failed to add to cart.", "error");
            }

            if (btn) { btn.disabled = false; btn.innerHTML = btn._origHtml; }
            return;
        }

        try {
            await Api.post('/cart/add', {
                product_id: this.product.id,
                quantity: qty,
                attributes: this.selectedAttributes
            });
            Utils.showToast("Added to cart!", "success");
            if (window.CartModule) window.CartModule.updateCartBadge();
        } catch (e) {
            Utils.showToast(e.message || "Failed to add to cart.", "error");
        }

        if (btn) { btn.disabled = false; btn.innerHTML = btn._origHtml; }
    },

    // ============ Related Products ============
    async loadRelatedProducts() {
        const container = document.getElementById('related-products-grid');
        const section = document.getElementById('related-products-section');
        if (!container || !section) return;

        try {
            const catId = this.product ? this.product.category_id : null;
            let res = catId ? await Api.get(`/products?category=${catId}&per_page=8`) : null;
            let products = (res && res.success && res.data) ? res.data : [];
            let filtered = products.filter(p => parseInt(p.id) !== parseInt(this.product.id)).slice(0, 6);

            if (filtered.length < 4) {
                const fallbackRes = await Api.get('/products?per_page=8');
                if (fallbackRes && fallbackRes.success && fallbackRes.data) {
                    const ids = new Set(filtered.map(p => p.id));
                    fallbackRes.data.forEach(p => {
                        if (!ids.has(p.id) && parseInt(p.id) !== parseInt(this.product.id) && filtered.length < 6) {
                            filtered.push(p);
                        }
                    });
                }
            }

            if (filtered.length === 0) { section.classList.add('hidden'); return; }
            section.classList.remove('hidden');

            const placeholder = `${BASE_URL}assets/images/product_placeholder.jpg`;

            // Use the same robust fixImg logic as main product
            const fixFn = (path) => {
                if (!path) return '';
                if (path.startsWith('http://') || path.startsWith('https://')) return path;
                return '/' + path.replace(/^\/+/, '');
            };

            let html = '';
            filtered.forEach(p => {
                const price = parseFloat(p.price);
                const pMrp = p.mrp ? parseFloat(p.mrp) : (price * 1.25);
                const pDiscount = Math.round(((pMrp - price) / pMrp) * 100);
                const rawImg = p.primary_image || (p.images && p.images.length > 0 ? p.images[0].image_url : null);
                const imgUrl = rawImg ? fixFn(rawImg) : placeholder;

                html += `
                    <div class="group bg-white rounded-2xl overflow-hidden relative transition-all duration-300 hover:-translate-y-1">
                        <a href="${BASE_URL}product?slug=${p.slug}" class="relative bg-gray-50 aspect-[4/5] overflow-hidden block rounded-2xl">
                            <img src="${imgUrl}" alt="${p.name}" onerror="this.onerror=null;this.src='${placeholder}';" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105">
                            ${pDiscount > 0 ? `
                                <span class="absolute top-3 left-3 bg-[#f59e0b] text-[#12090c] font-black text-[10px] uppercase tracking-widest px-3 py-1.5 rounded-full shadow-sm z-10">
                                    -${pDiscount}%
                                </span>
                            ` : ''}
                        </a>
                        <div class="py-4 flex flex-col space-y-1">
                            <h3 class="font-sans font-bold text-sm text-[#12090c] leading-snug line-clamp-2 hover:text-[#990024] transition">
                                <a href="${BASE_URL}product?slug=${p.slug}">${p.name}</a>
                            </h3>
                            <div class="flex items-center gap-2 pt-1">
                                <span class="text-sm font-black text-[#990024]">${Utils.formatCurrency(price)}</span>
                                <span class="text-xs text-gray-400 line-through font-bold">${Utils.formatCurrency(pMrp)}</span>
                            </div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        } catch (e) {
            container.innerHTML = `<div class="text-center py-8 text-red-500 font-bold"><i class="fas fa-exclamation-triangle text-3xl mb-3 block"></i> Failed to load related products.</div>`;
        }
    },

    // ==========================================
    // SHARE CAPABILITY
    // ==========================================
    shareProduct() {
        if (!this.product) return;
        
        const title = this.product.name;
        const text = this.product.short_description || `Check out ${title} at Trisha Utsav!`;
        const url = `${BASE_URL}product?slug=${this.product.slug}`;
        
        if (navigator.share) {
            navigator.share({
                title: title,
                text: text,
                url: url
            }).catch(console.error);
        } else {
            // Fallback to custom modal
            const modal = document.getElementById('share-modal');
            if (!modal) return;
            
            const encodedUrl = encodeURIComponent(url);
            const encodedText = encodeURIComponent(text);
            const encodedTitle = encodeURIComponent(title);
            
            document.getElementById('share-btn-wa').href = `https://wa.me/?text=${encodedTitle}%20-%20${encodedUrl}`;
            document.getElementById('share-btn-fb').href = `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`;
            document.getElementById('share-btn-tw').href = `https://twitter.com/intent/tweet?url=${encodedUrl}&text=${encodedText}`;
            document.getElementById('share-btn-tg').href = `https://t.me/share/url?url=${encodedUrl}&text=${encodedTitle}`;
            
            document.getElementById('share-link-input').value = url;
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            // Lock body scroll
            document.body.style.overflow = 'hidden';
        }
    },
    
    closeShareModal() {
        const modal = document.getElementById('share-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }
    },
    
    copyShareLink() {
        const input = document.getElementById('share-link-input');
        if (input) {
            input.select();
            input.setSelectionRange(0, 99999); /* For mobile devices */
            navigator.clipboard.writeText(input.value).then(() => {
                if (window.Utils && window.Utils.showToast) {
                    window.Utils.showToast('Link copied to clipboard!', 'success');
                }
                this.closeShareModal();
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }
    }
};

window.ProductPage = ProductPage;
document.addEventListener('DOMContentLoaded', () => {
    ProductPage.init();
});