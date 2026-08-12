/**
 * E-Commerce Client Utilities
 */

const Utils = {
    /**
     * Format a numeric amount to INR Currency
     *
     * @param {number|string} amount
     * @return {string}
     */
    formatCurrency(amount) {
        const value = parseFloat(amount) || 0.00;
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 2
        }).format(value);
    },

    /**
     * Show a float toast notification message
     *
     * @param {string} message
     * @param {string} type 'success', 'error', 'info', 'warning'
     */
    showToast(message, type = 'info') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        let iconClass = 'fa-info-circle';
        if (type === 'success') iconClass = 'fa-check-circle';
        if (type === 'error') iconClass = 'fa-times-circle';
        if (type === 'warning') iconClass = 'fa-exclamation-circle';

        toast.innerHTML = `
            <span class="mr-3 text-base"><i class="fas ${iconClass}"></i></span>
            <span class="flex-grow">${message}</span>
        `;
        
        container.appendChild(toast);
        
        // Trigger reflow to animate
        toast.offsetHeight;
        toast.classList.add('show');

        // Remove after delay
        setTimeout(() => {
            toast.classList.remove('show');
            toast.addEventListener('transitionend', () => {
                toast.remove();
            });
        }, 4000);
    },

    /**
     * Get a query parameter from the URL path
     *
     * @param {string} param Parameter name
     * @return {string|null}
     */
    getQueryParam(param) {
        const params = new URLSearchParams(window.location.search);
        return params.get(param);
    },

    /**
     * Standard input debounce helper
     *
     * @param {Function} func Callback
     * @param {number} delay Timeout milliseonds
     * @return {Function}
     */
    debounce(func, delay) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => func.apply(this, args), delay);
        };
    },

    /**
     * Get details of active user session from local storage
     *
     * @return {object|null}
     */
    getUserSession() {
        const token = localStorage.getItem('auth_token');
        const userJson = localStorage.getItem('auth_user');
        if (!token || !userJson) {
            return null;
        }
        try {
            return {
                token,
                user: JSON.parse(userJson)
            };
        } catch (e) {
            return null;
        }
    },

    /**
     * Ensure correct relative/absolute URL for uploaded image paths
     *
     * @param {string} path Image URL path
     * @return {string}
     */
    fixImageUrl(path) {
        if (!path) return '';
        if (path.startsWith('http://') || path.startsWith('https://')) return path;
        return '/' + path.replace(/^\/+/, '');
    },

    /**
     * Escape HTML characters to prevent XSS
     *
     * @param {string} text
     * @return {string}
     */
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.innerText = text;
        return div.innerHTML;
    },

    /**
     * Trigger direct "Buy Now" checkout using standard Cart flow
     *
     * @param {number|string} productId
     * @param {number} quantity
     * @param {number|string|null} variantId
     * @param {HTMLElement|null} btnElement
     * @param {object|null} attributes
     */
    async buyNow(productId, quantity = 1, variantId = null, btnElement = null, attributes = null) {
        if (btnElement) {
            btnElement.disabled = true;
            btnElement._origHtml = btnElement.innerHTML;
            btnElement.innerHTML = `<i class="fas fa-spinner fa-spin text-xs"></i><span class="ml-1 text-[10px] sm:text-xs">Processing...</span>`;
        }

        try {
            const pId = parseInt(productId);
            const qty = parseInt(quantity) || 1;

            if (typeof Auth !== 'undefined' && !Auth.isLoggedIn()) {
                // If not logged in, add to local guest cart and redirect to checkout
                let guestCart = JSON.parse(localStorage.getItem('guest_cart')) || [];
                
                try {
                    const res = await Api.get('/products/' + pId);
                    if (res.success && res.data) {
                        const prod = res.data;
                        let price = parseFloat(prod.price);
                        if (attributes && prod.attributes) {
                            for (let k in attributes) {
                                const val = attributes[k];
                                const match = prod.attributes.find(a => a.attribute_name === k && a.attribute_value === val);
                                if (match) price += parseFloat(match.extra_price);
                            }
                        }
                        const primaryImg = prod.primary_image || (prod.images && prod.images.length > 0 ? prod.images[0].image_url : '');
                        
                        const existingIndex = guestCart.findIndex(item => {
                            if (item.product_id !== pId) return false;
                            return JSON.stringify(item.attributes) === JSON.stringify(attributes);
                        });

                        if (existingIndex > -1) {
                            guestCart[existingIndex].quantity += qty;
                        } else {
                            guestCart.push({
                                id: Date.now() + Math.floor(Math.random() * 1000),
                                product_id: pId,
                                product_name: prod.name,
                                product_image: primaryImg,
                                sku: prod.sku,
                                price: price,
                                quantity: qty,
                                attributes: attributes
                            });
                        }

                        localStorage.setItem('guest_cart', JSON.stringify(guestCart));
                    }
                } catch (eG) {}

                const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/';
                window.location.href = baseUrl + 'login?redirect=checkout';
                return;
            }

            // Logged in user: Add item to backend Cart
            const payload = {
                product_id: pId,
                quantity: qty
            };
            if (attributes) payload.attributes = attributes;

            const res = await Api.post('/cart/add', payload);
            if (res.success) {
                const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/';
                window.location.href = baseUrl + 'checkout';
            } else {
                throw new Error(res.message || "Failed to add item to cart.");
            }
        } catch (err) {
            Utils.showToast(err.message || "Failed to initiate Buy Now.", "error");
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = btnElement._origHtml;
            }
        }
    },

    /**
     * Quick Add item to cart with 1 click
     */
    async quickAdd(productId, quantity = 1, btnElement = null) {
        if (btnElement) {
            btnElement.disabled = true;
            btnElement._origHtml = btnElement.innerHTML;
            btnElement.innerHTML = `<i class="fas fa-spinner fa-spin text-xs"></i>`;
        }

        try {
            const pId = parseInt(productId);
            const qty = parseInt(quantity) || 1;

            if (typeof Auth !== 'undefined' && !Auth.isLoggedIn()) {
                let guestCart = JSON.parse(localStorage.getItem('guest_cart')) || [];
                try {
                    const res = await Api.get('/products/' + pId);
                    if (res.success && res.data) {
                        const prod = res.data;
                        const price = parseFloat(prod.price);
                        const primaryImg = prod.primary_image || (prod.images && prod.images.length > 0 ? prod.images[0].image_url : '');
                        
                        const existingIndex = guestCart.findIndex(item => item.product_id === pId);
                        if (existingIndex > -1) {
                            guestCart[existingIndex].quantity += qty;
                        } else {
                            guestCart.push({
                                id: Date.now() + Math.floor(Math.random() * 1000),
                                product_id: pId,
                                product_name: prod.name,
                                product_image: primaryImg,
                                sku: prod.sku,
                                price: price,
                                quantity: qty
                            });
                        }
                        localStorage.setItem('guest_cart', JSON.stringify(guestCart));
                    }
                } catch (eG) {}
                Utils.showToast("Product added to cart!", "success");
                if (window.CartModule && typeof window.CartModule.updateCartBadge === 'function') {
                    window.CartModule.updateCartBadge();
                }
                return;
            }

            const res = await Api.post('/cart/add', { product_id: pId, quantity: qty });
            if (res.success) {
                Utils.showToast("Product added to cart!", "success");
                if (window.CartModule && typeof window.CartModule.updateCartBadge === 'function') {
                    window.CartModule.updateCartBadge();
                }
            } else {
                throw new Error(res.message || "Failed to add to cart.");
            }
        } catch (err) {
            Utils.showToast(err.message || "Could not add to cart.", "error");
        } finally {
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = btnElement._origHtml;
            }
        }
    }
};

window.Utils = Utils;

/**
 * Global Password Eye Toggle Helper
 */
function togglePasswordVisibility(inputId, btnElement = null) {
    const input = typeof inputId === 'string' ? document.getElementById(inputId) : inputId;
    if (!input) return;

    const btn = btnElement || (input.parentElement ? input.parentElement.querySelector('.password-toggle-btn') : null);
    const isPassword = input.type === 'password';

    input.type = isPassword ? 'text' : 'password';

    if (btn) {
        const icon = btn.querySelector('i');
        if (icon) {
            if (isPassword) {
                icon.className = 'far fa-eye-slash text-sm text-[#990024]';
                btn.setAttribute('aria-label', 'Hide password');
                btn.setAttribute('title', 'Hide password');
            } else {
                icon.className = 'far fa-eye text-sm text-gray-400 hover:text-gray-600';
                btn.setAttribute('aria-label', 'Show password');
                btn.setAttribute('title', 'Show password');
            }
        }
    }
}

function initPasswordToggleButtons() {
    const pwdInputs = document.querySelectorAll('input[type="password"]');
    pwdInputs.forEach(input => {
        if (input.dataset.hasPasswordToggle === 'true') return;
        
        const parent = input.parentElement;
        if (!parent) return;

        if (getComputedStyle(parent).position === 'static') {
            parent.style.position = 'relative';
        }

        input.classList.add('pr-10');
        input.dataset.hasPasswordToggle = 'true';

        let btn = parent.querySelector('.password-toggle-btn');
        if (!btn) {
            btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'password-toggle-btn absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-[#990024] transition-colors focus:outline-none cursor-pointer z-10';
            btn.setAttribute('aria-label', 'Show password');
            btn.setAttribute('title', 'Show password');
            btn.innerHTML = '<i class="far fa-eye text-sm"></i>';
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                togglePasswordVisibility(input, btn);
            });
            parent.appendChild(btn);
        }
    });
}

window.togglePasswordVisibility = togglePasswordVisibility;
window.initPasswordToggleButtons = initPasswordToggleButtons;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPasswordToggleButtons);
} else {
    initPasswordToggleButtons();
}
