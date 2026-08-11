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
     * Trigger direct "Buy Now" checkout bypassing normal cart
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
            const payload = {
                product_id: parseInt(productId),
                quantity: parseInt(quantity) || 1
            };
            if (variantId) payload.variant_id = variantId;
            if (attributes) payload.attributes = attributes;

            const res = await Api.post('/buy-now', payload);

            if (res.success && res.redirect_url) {
                window.location.href = res.redirect_url;
            } else {
                throw new Error(res.message || "Failed to initiate direct checkout.");
            }
        } catch (err) {
            Utils.showToast(err.message || "Failed to initiate Buy Now.", "error");
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = btnElement._origHtml;
            }
        }
    }
};

window.Utils = Utils;
