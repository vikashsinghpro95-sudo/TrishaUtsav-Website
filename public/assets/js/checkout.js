/**
 * TrishaUtsav — Checkout Page Module (Mobile-First)
 */

const CheckoutPage = {
    cart: null,
    addresses: [],
    selectedAddressId: null,

    /**
     * Initializer
     */
    async init() {
        const urlParams = new URLSearchParams(window.location.search);
        this.directOrderToken = urlParams.get('direct_order');

        if (!Auth.isLoggedIn()) {
            const redirectTarget = 'checkout' + window.location.search;
            window.location.href = BASE_URL + 'login?redirect=' + encodeURIComponent(redirectTarget);
            return;
        }

        document.body.classList.add('has-sticky-bar', 'has-sticky-bar-mobile-only');

        try {
            if (this.directOrderToken) {
                const directRes = await Api.get('/checkout/direct-summary?direct_order=' + encodeURIComponent(this.directOrderToken));
                if (directRes.success && directRes.data) {
                    this.cart = directRes.data;
                } else {
                    Utils.showToast(directRes.message || "Direct order link is invalid or expired.", "warning");
                    setTimeout(() => {
                        window.location.href = BASE_URL + 'shop';
                    }, 1500);
                    return;
                }
            } else {
                const cartRes = await Api.get('/cart');
                if (cartRes.success && cartRes.data) {
                    this.cart = cartRes.data;
                    if (!this.cart.items || this.cart.items.length === 0) {
                        Utils.showToast("Your cart is empty.", "warning");
                        setTimeout(() => {
                            window.location.href = BASE_URL + 'cart';
                        }, 1000);
                        return;
                    }
                }
            }

            this.renderSummary();
            await this.loadAddresses();

            const addrForm = document.getElementById('frm-checkout-address');
            if (addrForm) {
                addrForm.addEventListener('submit', (e) => this.saveAddress(e));
            }
        } catch (e) {
            Utils.showToast("Failed to initialize checkout: " + e.message, "error");
        }
    },

    /**
     * Load and render saved addresses
     */
    async loadAddresses() {
        const container = document.getElementById('checkout-addresses-list');
        if (!container) return;

        container.innerHTML = `
            <div class="flex items-center space-x-2 py-4">
                <div class="loader-spinner-dark !w-4 !h-4"></div>
                <span class="text-xs text-gray-500">Loading addresses...</span>
            </div>
        `;

        try {
            const res = await Api.get('/addresses');
            if (res.success && res.data) {
                this.addresses = res.data;
                this.renderAddresses();
            }
        } catch (e) {
            container.innerHTML = `<span class="text-xs text-red-500 font-semibold">Failed to fetch addresses</span>`;
        }
    },

    renderAddresses() {
        const container = document.getElementById('checkout-addresses-list');
        if (!container) return;

        if (this.addresses.length === 0) {
            container.innerHTML = `
                <div class="text-center py-6 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                    <i class="fas fa-map-marker-alt text-gray-300 text-2xl mb-2"></i>
                    <span class="text-xs text-gray-500 font-medium block">No saved addresses. Add one below.</span>
                </div>
            `;
            this.selectedAddressId = null;

            // Auto-open address form
            const wrap = document.getElementById('new-addr-form-wrap');
            if (wrap) wrap.classList.remove('hidden');
            return;
        }

        let preselected = this.addresses.find(a => a.is_default == 1 && a.type === 'shipping');
        if (!preselected) {
            preselected = this.addresses.find(a => a.type === 'shipping') || this.addresses[0];
        }
        this.selectedAddressId = preselected ? preselected.id : null;
        let html = '<div class="space-y-3 stagger-children">';
        this.addresses.forEach(addr => {
            const isChecked = this.selectedAddressId == addr.id;
            html += `
                <label onclick="CheckoutPage.selectAddress(${addr.id}, this)" class="address-card ${isChecked ? 'active' : ''}">
                    <input type="radio" name="checkout_address" value="${addr.id}" ${isChecked ? 'checked' : ''} class="sr-only">
                    <div class="flex items-start justify-between gap-3 w-full">
                        <div class="flex items-start space-x-3 min-w-0 flex-1">
                            <div class="pm-radio mt-0.5" style="border-color: ${isChecked ? 'var(--crimson)' : '#d1d5db'};">
                                ${isChecked ? '<div style="width:10px;height:10px;background:var(--crimson);border-radius:50%;"></div>' : ''}
                            </div>
                            <div class="text-xs sm:text-sm leading-relaxed text-slate-700 min-w-0 flex-1 font-medium space-y-0.5">
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="font-extrabold text-[#12090c] text-sm sm:text-base">${addr.full_name}</span>
                                    ${addr.is_default == 1 ? `<span class="bg-emerald-100 text-emerald-800 text-[10px] font-black px-2.5 py-0.5 rounded-full border border-emerald-200">DEFAULT</span>` : ''}
                                    <span class="bg-gray-100 text-gray-700 text-[9px] font-black px-2.5 py-0.5 rounded-full uppercase border border-gray-200">${addr.type || 'SHIPPING'}</span>
                                </div>
                                <span class="block text-gray-600 font-bold">${addr.address_line1}</span>
                                ${addr.address_line2 ? `<span class="block text-gray-600">${addr.address_line2}</span>` : ''}
                                <span class="block font-bold text-gray-800">${addr.city}, ${addr.state} — ${addr.pincode}</span>
                                <span class="inline-flex items-center font-black mt-2 text-[#990024] text-xs bg-[#990024]/10 px-2.5 py-1 rounded-lg border border-[#990024]/20"><i class="fas fa-phone mr-1.5 text-[10px]"></i>${addr.phone}</span>
                            </div>
                        </div>
                    </div>
                </label>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    },

    selectAddress(id, el) {
        this.selectedAddressId = id;

        document.querySelectorAll('#checkout-addresses-list .address-card').forEach(card => {
            card.classList.remove('active');
            const radio = card.querySelector('input[type="radio"]');
            if (radio) radio.checked = false;
            const circle = card.querySelector('.pm-radio');
            if (circle) {
                circle.style.borderColor = '#d1d5db';
                circle.innerHTML = '';
            }
        });

        if (el) {
            el.classList.add('active');
            const radio = el.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
            const circle = el.querySelector('.pm-radio');
            if (circle) {
                circle.style.borderColor = 'var(--crimson)';
                circle.innerHTML = '<div style="width:10px;height:10px;background:var(--crimson);border-radius:50%;"></div>';
            }
        }

        const selected = this.addresses.find(a => a.id == id);
        if (selected && selected.pincode) {
            const input = document.getElementById('checkout-pincode-input');
            if (input) input.value = selected.pincode;
            this.checkPincode(selected.pincode);
        }
    },

    /**
     * Check pincode serviceability & rate via Delhivery Express
     */
    async checkPincode(pincodeToTest = null) {
        const pincode = pincodeToTest || document.getElementById('checkout-pincode-input')?.value;
        const statusEl = document.getElementById('pincode-status-badge');
        if (!pincode || !/^[1-9][0-9]{5}$/.test(String(pincode).trim())) {
            if (statusEl) {
                statusEl.innerHTML = `
                    <div class="mt-2 p-2.5 bg-amber-50 border border-amber-200/80 rounded-xl text-[11px] font-bold text-amber-800 flex items-center">
                        <i class="fas fa-exclamation-circle text-amber-600 text-xs mr-2"></i> Please enter a valid 6-digit Indian pincode.
                    </div>
                `;
            }
            return;
        }

        if (statusEl) {
            statusEl.innerHTML = `
                <div class="mt-2.5 p-3 bg-slate-50 border border-slate-200/80 rounded-xl flex items-center justify-between text-xs text-slate-600">
                    <span class="font-bold inline-flex items-center">
                        <i class="fas fa-circle-notch fa-spin mr-2 text-[#990024]"></i> Checking Delhivery Express serviceability...
                    </span>
                </div>
            `;
        }

        try {
            const subtotal = this.cart?.summary?.subtotal || 0;
            const res = await Api.get('/shipping/check-pincode?pincode=' + encodeURIComponent(String(pincode).trim()) + '&amount=' + subtotal);
            if (res.success && res.serviceable) {
                if (statusEl) {
                    statusEl.innerHTML = `
                        <div class="mt-2.5 p-3.5 bg-gradient-to-r from-emerald-50/90 via-emerald-50/50 to-teal-50/80 border border-emerald-200/90 rounded-2xl shadow-2xs transition-all duration-300">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-xs shrink-0 shadow-xs mt-0.5">
                                        <i class="fas fa-truck-fast"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="text-xs font-black text-emerald-950 tracking-tight">Express Delivery Available</span>
                                            <span class="text-[9px] font-extrabold uppercase bg-emerald-200/70 text-emerald-800 px-2 py-0.5 rounded-full border border-emerald-300/60">Delhivery</span>
                                        </div>
                                        <p class="text-[11px] font-bold text-emerald-800/90 mt-0.5 truncate">
                                            <i class="fas fa-location-dot text-emerald-600 mr-1 text-[10px]"></i>${res.city || 'Serviceable Area'}${res.state ? ', ' + res.state : ''}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0 bg-white/90 px-2.5 py-1.5 rounded-xl border border-emerald-200/80 shadow-2xs">
                                    <span class="block text-[9px] font-extrabold uppercase text-slate-400 tracking-wider">Est. Delivery</span>
                                    <span class="block text-xs font-black text-[#990024] mt-0.5">${res.estimated_days || '3-5 Days'}</span>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                // Dynamically update shipping charge in summary
                if (typeof res.shipping_charge !== 'undefined' && this.cart && this.cart.summary) {
                    this.cart.summary.shipping = res.shipping_charge;
                    this.cart.summary.total = Math.max(0, (this.cart.summary.subtotal + (this.cart.summary.tax || 0) - (this.cart.summary.discount || 0) + res.shipping_charge));
                    this.renderSummary();
                }
            } else {
                if (statusEl) {
                    statusEl.innerHTML = `
                        <div class="mt-2.5 p-3.5 bg-gradient-to-r from-red-50 via-rose-50/60 to-red-50 border border-red-200/90 rounded-2xl shadow-2xs text-xs">
                            <div class="flex items-start gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-red-500 text-white flex items-center justify-center text-xs shrink-0 shadow-xs mt-0.5">
                                    <i class="fas fa-triangle-exclamation"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-black text-red-950 block">Pincode Not Deliverable</span>
                                    <p class="text-[11px] font-semibold text-red-800 mt-0.5 leading-relaxed">${res.message || 'Delivery is currently unavailable to this pincode via Delhivery.'}</p>
                                </div>
                            </div>
                        </div>
                    `;
                }
            }
        } catch (e) {
            if (statusEl) {
                statusEl.innerHTML = `<span class="text-xs text-gray-500 font-bold">Standard delivery applies.</span>`;
            }
        }
    },

    /**
     * Toggle address form visibility
     */
    toggleAddressForm() {
        const wrap = document.getElementById('new-addr-form-wrap');
        if (!wrap) return;

        wrap.classList.toggle('hidden');
    },

    /**
     * Save new address
     */
    async saveAddress(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-save-address');
        btn.disabled = true;
        btn.innerHTML = `<span class="loader-spinner !w-4 !h-4"></span> Saving...`;

        const data = {
            type: document.getElementById('addr-type').value,
            full_name: document.getElementById('addr-name').value,
            phone: document.getElementById('addr-phone').value,
            address_line1: document.getElementById('addr-line1').value,
            address_line2: document.getElementById('addr-line2').value,
            city: document.getElementById('addr-city').value,
            state: document.getElementById('addr-state').value,
            pincode: document.getElementById('addr-pincode').value,
            is_default: document.getElementById('addr-default').checked ? 1 : 0
        };

        try {
            await Api.post('/addresses', data);
            Utils.showToast("Address saved!", "success");
            document.getElementById('frm-checkout-address').reset();

            // Collapse form
            const wrap = document.getElementById('new-addr-form-wrap');
            if (wrap) wrap.classList.add('hidden');

            await this.loadAddresses();
        } catch (err) {
            Utils.showToast(err.message || "Failed to save address.", "error");
        } finally {
            btn.disabled = false;
            btn.innerHTML = `Save Address`;
        }
    },

    /**
     * Select payment method card
     */
    selectPayment(el, method) {
        document.querySelectorAll('.pm-card').forEach(card => {
            card.classList.remove('active');
            const radio = card.querySelector('input[type="radio"]');
            if (radio) radio.checked = false;
        });

        el.classList.add('active');
        const radio = el.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
    },

    /**
     * Toggle mobile order summary
     */
    toggleMobileSummary() {
        const body = document.getElementById('order-summary-body');
        const toggle = document.getElementById('mobile-summary-toggle');
        if (!body || !toggle) return;

        body.classList.toggle('expanded');
        toggle.classList.toggle('open');
    },

    /**
     * Render order summary
     */
    renderSummary() {
        const container = document.getElementById('checkout-summary-items');
        const summary = this.cart.summary || {};
        if (!container) return;

        const placeholder = `${BASE_URL}assets/images/product_placeholder.jpg`;
        const fixFn = (window.Utils && typeof window.Utils.fixImageUrl === 'function')
            ? window.Utils.fixImageUrl
            : (p => ('/' + p.replace(/^\/+/, '')));

        let itemsHtml = '';
        this.cart.items.forEach(item => {
            const rawImg = item.product_image || item.image_url || item.image || item.primary_image || (item.images && item.images.length > 0 ? item.images[0].image_url : null);
            const imgUrl = rawImg ? fixFn(rawImg) : placeholder;

            itemsHtml += `
                <div class="summary-item">
                    <div class="summary-item-img">
                        <img src="${imgUrl}" alt="${item.product_name}" onerror="this.onerror=null;this.src='${placeholder}';" loading="lazy">
                    </div>
                    <div class="flex-1 min-w-0 space-y-0.5">
                        <h4 class="font-extrabold text-[#12090c] line-clamp-1 text-sm">${item.product_name}</h4>
                        <span class="text-xs text-gray-500 font-bold block">${item.quantity} × ${Utils.formatCurrency(item.price)}</span>
                    </div>
                    <span class="font-black text-[#12090c] text-sm flex-shrink-0">${Utils.formatCurrency(item.price * item.quantity)}</span>
                </div>
            `;
        });
        container.innerHTML = itemsHtml;

        // Totals
        document.getElementById('co-subtotal').innerText = Utils.formatCurrency(summary.subtotal);
        document.getElementById('co-discount').innerText = '-' + Utils.formatCurrency(summary.discount);
        document.getElementById('co-tax').innerText = Utils.formatCurrency(summary.tax);

        const ship = summary.shipping;
        document.getElementById('co-shipping').innerHTML = ship > 0 ? Utils.formatCurrency(ship) : '<span class="text-emerald-600 font-bold">Free</span>';

        document.getElementById('co-total').innerText = Utils.formatCurrency(summary.total);

        // Mobile inline total
        const inlineTotal = document.getElementById('co-total-inline');
        if (inlineTotal) inlineTotal.innerText = Utils.formatCurrency(summary.total);

        // Item count
        const countEl = document.getElementById('co-item-count');
        if (countEl) countEl.innerText = this.cart.items.length;

        // Sticky bar total
        const stickyTotal = document.getElementById('co-sticky-total');
        if (stickyTotal) stickyTotal.innerText = Utils.formatCurrency(summary.total);
    },

    /**
     * Place order
     */
    async placeOrder() {
        if (!this.selectedAddressId) {
            Utils.showToast("Please select a shipping address.", "warning");
            return;
        }

        const method = document.querySelector('input[name="payment_method"]:checked').value;
        const notes = document.getElementById('checkout-notes').value;

        // Disable both buttons
        const btns = [document.getElementById('btn-place-order'), document.getElementById('btn-place-order-desktop')];
        btns.forEach(btn => {
            if (btn) {
                btn.disabled = true;
                btn._origHtml = btn.innerHTML;
                btn.innerHTML = `<span class="loader-spinner !w-4 !h-4"></span> Placing Order...`;
            }
        });

        try {
            const payload = {
                shipping_address_id: this.selectedAddressId,
                payment_method: method,
                notes: notes,
                coupon_code: this.cart.summary.applied_coupon ? this.cart.summary.applied_coupon.code : null
            };

            if (this.directOrderToken) {
                payload.direct_order = this.directOrderToken;
            }

            const res = await Api.post('/checkout', payload);

            if (res.success) {
                if (method === 'razorpay' || method === 'upi') {
                    await this.initiatePayment(res.order_id, res.total, method);
                } else {
                    Utils.showToast("Order placed successfully!", "success");
                    setTimeout(() => {
                        window.location.href = BASE_URL + 'order-success?id=' + res.order_id;
                    }, 400);
                }
            }
        } catch (e) {
            Utils.showToast(e.message || "Checkout failed.", "error");
            btns.forEach(btn => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = btn._origHtml || 'Place Order';
                }
            });
        }
    },

    /**
     * Razorpay payment flow
     */
    async initiatePayment(orderId, amount, method = 'razorpay') {
        try {
            const initRes = await Api.post('/payments/initiate', { order_id: orderId });

            if (initRes.success) {
                var options = {
                    "key": initRes.key_id,
                    "amount": initRes.amount * 100,
                    "currency": initRes.currency,
                    "name": "Trisha Utsav",
                    "description": "Festive Order " + initRes.order_number,
                    "order_id": initRes.gateway_order_id,
                    "handler": async function (response) {
                        try {
                            const verifyRes = await Api.post('/payments/verify', {
                                order_id: orderId,
                                payment_id: response.razorpay_payment_id,
                                status: 'success',
                                gateway_data: {
                                    razorpay_order_id: response.razorpay_order_id,
                                    razorpay_signature: response.razorpay_signature
                                }
                            });

                            if (verifyRes.success) {
                                Utils.showToast("Payment successful! Order confirmed.", "success");
                                setTimeout(() => {
                                    window.location.href = BASE_URL + 'order-success?id=' + orderId;
                                }, 400);
                            } else {
                                throw new Error("Verification failed.");
                            }
                        } catch (err) {
                            Utils.showToast("Payment verification failed.", "error");
                            setTimeout(() => {
                                window.location.href = BASE_URL + 'order-detail?id=' + orderId;
                            }, 1500);
                        }
                    },
                    "modal": {
                        "ondismiss": async function() {
                            await Api.post('/payments/failed', { order_id: orderId, reason: 'user_dismissed' });
                            Utils.showToast("Payment cancelled. Order was not placed.", "warning");
                            const btns = [document.getElementById('btn-place-order'), document.getElementById('btn-place-order-desktop')];
                            btns.forEach(btn => {
                                if (btn) {
                                    btn.disabled = false;
                                    btn.innerHTML = btn._origHtml || 'Place Order';
                                }
                            });
                        }
                    },
                    "theme": {
                        "color": "#990024"
                    }
                };

                if (method === 'upi') {
                    options.config = {
                        display: {
                            blocks: {
                                upi: {
                                    name: "Pay via UPI",
                                    instruments: [{ method: "upi" }]
                                }
                            },
                            sequence: ["block.upi"],
                            preferences: { show_default_blocks: false }
                        }
                    };
                }

                var rzp1 = new Razorpay(options);
                rzp1.on('payment.failed', async function (response) {
                    await Api.post('/payments/failed', {
                        order_id: orderId,
                        reason: response.error ? response.error.description : 'payment_failed'
                    });
                    Utils.showToast("Payment failed. Order was not placed.", "error");
                    const btns = [document.getElementById('btn-place-order'), document.getElementById('btn-place-order-desktop')];
                    btns.forEach(btn => {
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = btn._origHtml || 'Place Order';
                        }
                    });
                });

                rzp1.open();
            } else {
                // If backend already says paid (e.g. duplicate click)
                Utils.showToast(initRes.message || "Payment initiation failed.", "warning");
                if (initRes.success === true) {
                     window.location.href = BASE_URL + 'order-detail?id=' + orderId;
                }
            }
        } catch (e) {
            Utils.showToast("Payment gateway error: " + e.message, "error");
            setTimeout(() => {
                window.location.href = BASE_URL + 'account';
            }, 1500);
        }
    }
};

window.CheckoutPage = CheckoutPage;
document.addEventListener('DOMContentLoaded', () => {
    CheckoutPage.init();
});
