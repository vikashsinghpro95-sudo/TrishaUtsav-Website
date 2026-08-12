/**
 * Trisha Utsav - Wishlist Feature Client Manager
 * Handles heart icon toggles, instant state sync, animations, and cart transfers
 */

if (typeof window.wishlistIds === 'undefined') {
    window.wishlistIds = [];
}

const WishlistManager = {

    /**
     * Check if a product ID is currently in wishlist
     *
     * @param {number|string} productId
     * @return {boolean}
     */
    isWishlisted(productId) {
        const id = parseInt(productId);
        return window.wishlistIds.map(n => parseInt(n)).includes(id);
    },

    /**
     * Toggle product wishlist status (Add / Remove)
     *
     * @param {number|string} productId
     * @param {HTMLElement|null} btnElement
     * @param {Event|null} event
     */
    async toggle(productId, btnElement = null, event = null) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        const pId = parseInt(productId);
        if (!pId || isNaN(pId)) return;

        // Temporarily disable click to prevent rapid double clicks
        if (btnElement) {
            btnElement.style.pointerEvents = 'none';
        }

        try {
            const res = await Api.post('/wishlist/toggle', { product_id: pId });
            if (res.success) {
                if (res.action === 'added') {
                    if (!window.wishlistIds.includes(pId)) {
                        window.wishlistIds.push(pId);
                    }
                    if (window.Utils && Utils.showToast) {
                        Utils.showToast("Added to your wishlist! 💖", "success");
                    }
                } else {
                    window.wishlistIds = window.wishlistIds.filter(id => parseInt(id) !== pId);
                    if (window.Utils && Utils.showToast) {
                        Utils.showToast("Removed from your wishlist.", "info");
                    }
                }

                // Update UI state across page
                this.updateHeartIcons();
                this.updateHeaderCount(res.count);

                // If on Wishlist page, reload or update grid
                if (window.location.pathname.endsWith('/wishlist') || window.location.pathname.endsWith('/wishlist.php')) {
                    if (typeof WishlistPage !== 'undefined' && typeof WishlistPage.loadItems === 'function') {
                        WishlistPage.loadItems();
                    }
                }
            } else {
                throw new Error(res.message || "Failed to update wishlist.");
            }
        } catch (err) {
            if (window.Utils && Utils.showToast) {
                Utils.showToast(err.message || "Could not update wishlist.", "error");
            }
        } finally {
            if (btnElement) {
                btnElement.style.pointerEvents = 'auto';
            }
        }
    },

    /**
     * Move an item directly from Wishlist to Cart
     *
     * @param {number|string} productId
     * @param {HTMLElement|null} btnElement
     */
    async moveToCart(productId, btnElement = null) {
        const pId = parseInt(productId);
        if (!pId || isNaN(pId)) return;

        let origHtml = '';
        if (btnElement) {
            btnElement.disabled = true;
            origHtml = btnElement.innerHTML;
            btnElement.innerHTML = `<i class="fas fa-spinner fa-spin text-xs"></i><span class="ml-1 text-[10px] sm:text-xs">Moving...</span>`;
        }

        try {
            const res = await Api.post('/wishlist/move-to-cart', { product_id: pId });
            if (res.success) {
                window.wishlistIds = window.wishlistIds.filter(id => parseInt(id) !== pId);
                
                if (window.Utils && Utils.showToast) {
                    Utils.showToast("Moved to cart! 🛍️", "success");
                }

                this.updateHeartIcons();
                
                // Fetch updated wishlist & cart counts
                this.fetchWishlistCount();
                if (window.CartController && typeof window.CartController.fetchCount === 'function') {
                    window.CartController.fetchCount();
                }

                // If on Wishlist page, refresh grid
                if (window.location.pathname.endsWith('/wishlist') || window.location.pathname.endsWith('/wishlist.php')) {
                    if (typeof WishlistPage !== 'undefined' && typeof WishlistPage.loadItems === 'function') {
                        WishlistPage.loadItems();
                    }
                }
            } else {
                throw new Error(res.message || "Failed to move item to cart.");
            }
        } catch (err) {
            if (window.Utils && Utils.showToast) {
                Utils.showToast(err.message || "Could not move to cart.", "error");
            }
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = origHtml;
            }
        }
    },

    /**
     * Synchronize all heart icons on current DOM with window.wishlistIds
     */
    updateHeartIcons() {
        const targets = document.querySelectorAll('[data-wishlist-id]');
        targets.forEach(el => {
            const pId = parseInt(el.getAttribute('data-wishlist-id'));
            const isListed = this.isWishlisted(pId);
            
            const icon = el.querySelector('i') || el;
            if (isListed) {
                icon.className = 'fas fa-heart text-[#990024] transform scale-110 transition-transform duration-200';
                el.setAttribute('aria-label', 'Remove from wishlist');
                el.setAttribute('title', 'Remove from wishlist');
            } else {
                icon.className = 'far fa-heart text-gray-700 hover:text-[#990024] transition-colors duration-200';
                el.setAttribute('aria-label', 'Add to wishlist');
                el.setAttribute('title', 'Add to wishlist');
            }
        });
    },

    /**
     * Dynamically update the header badge count
     *
     * @param {number} count
     */
    updateHeaderCount(count) {
        const badge = document.getElementById('header-wishlist-count');
        if (badge) {
            badge.textContent = count;
            if (count > 0) {
                badge.classList.remove('hidden');
                badge.classList.add('animate-bounce');
                setTimeout(() => badge.classList.remove('animate-bounce'), 1000);
            } else {
                badge.textContent = '0';
            }
        }
    },

    /**
     * Fetch active wishlist count from server endpoint
     */
    async fetchWishlistCount() {
        try {
            const res = await Api.get('/wishlist/count');
            if (res.success && typeof res.count !== 'undefined') {
                this.updateHeaderCount(res.count);
            }
        } catch (e) {}
    },

    /**
     * Helper to render heart icon overlay HTML for any product card
     *
     * @param {number|string} productId
     * @return {string} HTML snippet
     */
    renderHeartButton(productId) {
        const pId = parseInt(productId);
        const isListed = this.isWishlisted(pId);
        const iconClass = isListed ? 'fas fa-heart text-[#990024]' : 'far fa-heart text-gray-600 hover:text-[#990024]';
        const ariaText = isListed ? 'Remove from wishlist' : 'Add to wishlist';

        return `
            <button type="button" 
                onclick="WishlistManager.toggle(${pId}, this, event)" 
                data-wishlist-id="${pId}" 
                aria-label="${ariaText}" 
                title="${ariaText}" 
                class="absolute top-2.5 right-2.5 z-20 w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/90 backdrop-blur-md shadow-md flex items-center justify-center text-xs sm:text-sm hover:scale-110 transition duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#990024]/50">
                <i class="${iconClass}"></i>
            </button>
        `;
    }
};

window.WishlistManager = WishlistManager;

document.addEventListener('DOMContentLoaded', () => {
    WishlistManager.updateHeartIcons();
    WishlistManager.fetchWishlistCount();
});
