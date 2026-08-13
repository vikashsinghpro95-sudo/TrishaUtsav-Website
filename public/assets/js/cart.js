/**
 * TrishaUtsav — Cart Page Module (Mobile-First)
 */

const CartPage = {
    /**
     * Initializer
     */
    init() {
        this.loadCart();
    },

    /**
     * Fetch cart and render
     */
    async loadCart() {
        const container = document.getElementById('cart-container');
        if (!container) return;

        container.innerHTML = `
            <div class="flex flex-col justify-center items-center py-32 space-y-3">
                <div class="loader-spinner-dark"></div>
                <span class="text-gray-500 text-sm font-medium">Loading your cart...</span>
            </div>
        `;

        if (!Auth.isLoggedIn()) {
            const guestCart = JSON.parse(localStorage.getItem('guest_cart')) || [];
            let subtotal = 0;
            guestCart.forEach(item => {
                subtotal += (parseFloat(item.price) * parseInt(item.quantity));
            });
            const tax = subtotal * 0.18;
            const total = subtotal + tax;

            this.renderCart({
                items: guestCart,
                summary: {
                    subtotal: subtotal,
                    discount: 0,
                    tax: tax,
                    shipping: 0,
                    total: total
                }
            });
            return;
        }

        try {
            const res = await Api.get('/cart');
            if (res.success && res.data) {
                this.renderCart(res.data);
            }
        } catch (e) {
            container.innerHTML = `<div class="text-center py-12"><span class="text-red-500 text-sm font-bold">Failed to load cart: ${e.message}</span></div>`;
        }
    },

    /**
     * Render cart items + summary
     */
    renderCart(cart) {
        const container = document.getElementById('cart-container');
        if (!container) return;

        const items = cart.items || [];
        const summary = cart.summary || {};

        // Empty state
        if (items.length === 0) {
            this.hideStickyBar();
            container.innerHTML = `
                <div class="empty-cart-container animate-fade-in-up">
                    <i class="fas fa-shopping-bag empty-cart-icon"></i>
                    <h3 class="font-display text-2xl sm:text-3xl font-black text-[#12090c] mb-4">Your bag is empty</h3>
                    <p class="text-gray-500 text-sm max-w-md mx-auto mb-8 font-medium">Looks like you haven't added anything to your cart yet. Let's fix that!</p>
                    <a href="${BASE_URL}shop" class="btn-primary-massive !w-auto inline-flex !px-10">
                        <span>START SHOPPING</span>
                    </a>
                </div>
            `;
            return;
        }

        const placeholder = `${BASE_URL}assets/images/product_placeholder.jpg`;
        const fixFn = (window.Utils && typeof window.Utils.fixImageUrl === 'function')
            ? window.Utils.fixImageUrl
            : (p => ('/' + p.replace(/^\/+/, '')));

        // Cart items HTML
        let itemsHtml = '<div class="cart-items-list stagger-children">';
        items.forEach(item => {
            const itemUrl = BASE_URL + 'product?slug=' + (item.product_slug || item.slug || '');
            const rawImg = item.primary_image || item.product_image || item.image_url || item.image || (item.images && item.images.length > 0 ? item.images[0].image_url : null);
            const imgUrl = rawImg ? fixFn(rawImg) : placeholder;

            let attrsHtml = '';
            if (item.attributes) {
                const parts = [];
                for (let key in item.attributes) {
                    parts.push(`<span class="text-gray-500 font-bold">${key}: ${item.attributes[key]}</span>`);
                }
                attrsHtml = `<div class="flex flex-wrap gap-2 text-xs mt-1.5">${parts.join('<span class="text-gray-300">•</span>')}</div>`;
            }

            itemsHtml += `
                <div class="cart-card animate-fade-in-up">
                    <!-- Image -->
                    <a href="${itemUrl}" class="cart-item-img block group">
                        <img src="${imgUrl}" alt="${item.product_name}" onerror="this.onerror=null;this.src='${placeholder}';" loading="lazy" class="group-hover:scale-105 transition-transform duration-500 ease-out">
                    </a>
                    
                    <!-- Details & Controls -->
                    <div class="flex flex-col flex-1 min-w-0 py-1">
                        <div class="flex justify-between items-start gap-4 mb-4">
                            <div>
                                <h4 class="font-bold text-base sm:text-lg text-[#12090c] leading-snug line-clamp-2">
                                    <a href="${itemUrl}" class="hover:text-[#990024] transition">${item.product_name}</a>
                                </h4>
                                ${attrsHtml}
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="text-lg font-black text-[#12090c] block">${Utils.formatCurrency(item.price * item.quantity)}</span>
                                ${item.quantity > 1 ? `<span class="text-xs text-gray-500 font-bold">${Utils.formatCurrency(item.price)} each</span>` : ''}
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-auto pt-4 border-t border-gray-100">
                            <div class="qty-controls">
                                <button onclick="CartPage.updateQty(${item.id}, ${item.quantity - 1})" class="qty-btn" aria-label="Decrease quantity"><i class="fas fa-minus text-xs"></i></button>
                                <span class="qty-value leading-[44px]">${item.quantity}</span>
                                <button onclick="CartPage.updateQty(${item.id}, ${item.quantity + 1})" class="qty-btn" aria-label="Increase quantity"><i class="fas fa-plus text-xs"></i></button>
                            </div>
                            <button onclick="CartPage.removeItem(${item.id})" class="remove-btn" title="Remove Item" aria-label="Remove ${item.product_name}">Remove</button>
                        </div>
                    </div>
                </div>
            `;
        });
        itemsHtml += '</div>';

        // Coupon widget
        const coupon = summary.applied_coupon;
        const hasCoupon = coupon && !coupon.invalid;
        const couponError = coupon && coupon.invalid ? coupon.error : '';

        let couponHtml = '';
        if (hasCoupon) {
            couponHtml = `
                <div class="flex items-center justify-between bg-green-50 border border-green-200 rounded-md p-3 mb-4">
                    <div>
                        <span class="font-bold text-green-800 uppercase tracking-wider block text-xs">${coupon.code}</span>
                        <span class="text-green-700 text-xs">${coupon.type === 'percentage' ? `${coupon.value}%` : Utils.formatCurrency(coupon.value)} off</span>
                    </div>
                    <button onclick="CartPage.removeCoupon()" class="text-gray-400 hover:text-red-600 p-2" aria-label="Remove promo code"><i class="fas fa-times"></i></button>
                </div>
            `;
        } else {
            couponHtml = `
                <form id="frm-apply-coupon" onsubmit="CartPage.applyCoupon(event)" class="promo-form">
                    <input type="text" id="coupon-code" placeholder="Promo code" autocomplete="off" class="promo-input placeholder:normal-case placeholder:text-gray-400">
                    <button type="submit" class="btn-apply">APPLY</button>
                </form>
                ${couponError ? `<span class="text-xs text-red-600 font-bold -mt-3 mb-4 block"><i class="fas fa-circle-exclamation mr-1"></i>${couponError}</span>` : ''}
            `;
        }

        // Summary HTML
        const checkoutUrl = !Auth.isLoggedIn() ? BASE_URL + 'login?redirect=checkout' : BASE_URL + 'checkout';
        let summaryHtml = `
            <div class="cart-summary-wrapper">
                <div class="summary-card">
                    <h3 class="font-display text-xl font-black text-[#12090c] pb-4 border-b border-gray-200 mb-6 flex items-center justify-between">
                        Order Summary
                        <span class="text-sm font-bold text-gray-400 font-sans">${items.length} item${items.length > 1 ? 's' : ''}</span>
                    </h3>

                    ${couponHtml}

                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span class="font-bold text-[#12090c]">${Utils.formatCurrency(summary.subtotal)}</span>
                    </div>
                    ${summary.discount > 0 ? `
                    <div class="summary-row text-green-700">
                        <span>Discount</span>
                        <span>-${Utils.formatCurrency(summary.discount)}</span>
                    </div>` : ''}
                    <div class="summary-row">
                        <span>Shipping Charges</span>
                        <span class="font-bold text-gray-500 text-[10px] tracking-wide uppercase">
                            Calculated at checkout
                        </span>
                    </div>
                    <div class="summary-row">
                        <span>Tax</span>
                        <span class="font-bold text-[#12090c]">${Utils.formatCurrency(summary.tax)}</span>
                    </div>

                    <div class="summary-row total">
                        <span>Total</span>
                        <span class="font-display text-3xl font-black text-[#990024]">${Utils.formatCurrency(summary.total)}</span>
                    </div>

                    <div class="mt-8">
                        <a href="${checkoutUrl}" class="btn-primary-massive shadow-md hover:shadow-lg">
                            <span>PROCEED TO CHECKOUT</span>
                        </a>
                    </div>
                    
                    <!-- Trust Badges under CTA -->
                    <div class="trust-row">
                        <div class="trust-badge" title="Secure Encrypted Checkout"><i class="fas fa-lock"></i> Secure</div>
                        <div class="trust-badge" title="Guaranteed Quality"><i class="fas fa-shield-halved"></i> Guarantee</div>
                        <div class="trust-badge" title="Handcrafted in India"><i class="fas fa-leaf"></i> Handcrafted</div>
                    </div>
                </div>
            </div>
        `;

        container.innerHTML = `
            <div class="cart-split animate-fade-in-up">
                <div class="cart-items-wrapper">
                    ${itemsHtml}
                </div>
                ${summaryHtml}
            </div>
        `;

        // Update sticky bar
        this.updateStickyBar(summary.total, checkoutUrl);
    },

    /**
     * Show and update the mobile sticky checkout bar
     */
    updateStickyBar(total, checkoutUrl) {
        const bar = document.getElementById('cart-sticky-bar');
        const totalEl = document.getElementById('cart-sticky-total');
        const btn = document.getElementById('cart-sticky-checkout-btn');
        if (!bar) return;

        bar.classList.remove('hidden');
        document.body.classList.add('has-sticky-bar', 'has-sticky-bar-mobile-only');
        if (totalEl) totalEl.innerText = Utils.formatCurrency(total);
        if (btn && checkoutUrl) btn.href = checkoutUrl;
    },

    hideStickyBar() {
        const bar = document.getElementById('cart-sticky-bar');
        if (bar) bar.classList.add('hidden');
        document.body.classList.remove('has-sticky-bar', 'has-sticky-bar-mobile-only');
    },

    /**
     * Update item quantity
     */
    async updateQty(itemId, qty) {
        if (!Auth.isLoggedIn()) {
            let guestCart = JSON.parse(localStorage.getItem('guest_cart')) || [];
            const idx = guestCart.findIndex(i => String(i.id) === String(itemId));
            if (idx > -1) {
                if (qty <= 0) {
                    guestCart.splice(idx, 1);
                } else {
                    guestCart[idx].quantity = qty;
                }
                localStorage.setItem('guest_cart', JSON.stringify(guestCart));
                this.loadCart();
                if (window.CartModule) window.CartModule.updateCartBadge();
            }
            return;
        }

        try {
            await Api.put('/cart/update/' + itemId, { quantity: qty });
            this.loadCart();
            if (window.CartModule) window.CartModule.updateCartBadge();
        } catch (e) {
            Utils.showToast(e.message || "Failed to update quantity.", "error");
        }
    },

    /**
     * Remove item
     */
    async removeItem(itemId) {
        if (!Auth.isLoggedIn()) {
            let guestCart = JSON.parse(localStorage.getItem('guest_cart')) || [];
            guestCart = guestCart.filter(i => String(i.id) !== String(itemId));
            localStorage.setItem('guest_cart', JSON.stringify(guestCart));
            Utils.showToast("Item removed.", "success");
            this.loadCart();
            if (window.CartModule) window.CartModule.updateCartBadge();
            return;
        }

        try {
            await Api.delete('/cart/remove/' + itemId);
            Utils.showToast("Item removed.", "success");
            this.loadCart();
            if (window.CartModule) window.CartModule.updateCartBadge();
        } catch (e) {
            Utils.showToast(e.message || "Failed to remove item.", "error");
        }
    },

    /**
     * Apply coupon
     */
    async applyCoupon(e) {
        e.preventDefault();
        const codeInput = document.getElementById('coupon-code');
        if (!codeInput || !codeInput.value) return;

        try {
            await Api.post('/cart/apply-coupon', { code: codeInput.value.trim() });
            Utils.showToast("Coupon applied!", "success");
            this.loadCart();
        } catch (e) {
            Utils.showToast(e.message || "Invalid coupon code.", "error");
            this.loadCart();
        }
    },

    /**
     * Remove coupon
     */
    async removeCoupon() {
        try {
            await Api.delete('/cart/coupon');
            Utils.showToast("Coupon removed.", "info");
            this.loadCart();
        } catch (e) {
            Utils.showToast(e.message || "Failed to remove coupon.", "error");
        }
    }
};

window.CartPage = CartPage;
document.addEventListener('DOMContentLoaded', () => {
    CartPage.init();
});
