/**
 * Admin Panel Common Utilities
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
     * @param {number} delay Timeout milliseconds
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
     * Ensure correct relative/absolute URL for uploaded image paths
     *
     * @param {string} path Image URL path
     * @return {string}
     */
    fixImageUrl(path) {
        if (!path) return '';
        if (path.startsWith('http://') || path.startsWith('https://')) return path;
        return '/' + path.replace(/^\/+/, '');
    }
};

window.Utils = Utils;
