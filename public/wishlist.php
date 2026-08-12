<?php
/**
 * Trisha Utsav - Customer Wishlist Page
 */
include_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header Hero Banner -->
<div class="bg-gradient-to-br from-[#fff8f0] via-[#fffdf7] to-[#fef2f2] border-b border-[#f59e0b]/20 py-8 sm:py-12 px-4 relative overflow-hidden">
    <div class="max-w-[1800px] mx-auto px-4 md:px-[50px] relative z-10">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="bg-[#990024]/10 text-[#990024] font-black text-[10px] sm:text-xs uppercase tracking-widest px-3 py-1 rounded-full mb-3 inline-block">
                    💖 Saved Curations
                </span>
                <h1 class="font-display font-black text-2xl sm:text-4xl text-[#12090c] tracking-tight">
                    My Wishlist
                </h1>
                <p class="text-xs sm:text-sm text-gray-600 mt-1">
                    Keep track of your favorite festive essentials & royal hampers.
                </p>
            </div>
            <div>
                <a href="<?php echo BASE_URL; ?>shop" class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-full bg-white border border-[#f59e0b]/40 text-[#12090c] hover:text-[#990024] font-bold text-xs shadow-sm hover:shadow transition duration-200">
                    <i class="fas fa-store text-[#f59e0b]"></i>
                    <span>Explore Full Catalog</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Main Wishlist Container -->
<div class="max-w-[1800px] mx-auto px-4 md:px-[50px] py-8 sm:py-12 flex-1">
    
    <!-- Guest User Login Reminder Banner -->
    <div id="wishlist-guest-banner" class="hidden mb-8 p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 border border-amber-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center space-x-3 text-center sm:text-left">
            <div class="w-10 h-10 rounded-full bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold text-lg flex-shrink-0">
                <i class="fas fa-user-lock"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm text-amber-950">Viewing Guest Wishlist</h4>
                <p class="text-xs text-amber-800 mt-0.5">Sign in to sync & save your favorite items across all your devices permanently.</p>
            </div>
        </div>
        <a href="<?php echo BASE_URL; ?>login" class="px-5 py-2 rounded-full bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs uppercase tracking-wider transition shadow-sm flex-shrink-0">
            Sign In Now
        </a>
    </div>

    <!-- Wishlist Loading State -->
    <div id="wishlist-loading" class="py-16 text-center">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-[#990024]/10 text-[#990024] animate-spin mb-4">
            <i class="fas fa-circle-notch text-xl"></i>
        </div>
        <p class="text-sm font-bold text-gray-700">Loading your wishlist items...</p>
    </div>

    <!-- Wishlist Items Grid -->
    <div id="wishlist-items-grid" class="hidden grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6 lg:gap-8">
        <!-- Rendered dynamically via JS -->
    </div>

    <!-- Empty Wishlist State -->
    <div id="wishlist-empty-state" class="hidden py-16 sm:py-24 text-center max-w-md mx-auto">
        <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto mb-6 rounded-full bg-red-50 text-[#990024] flex items-center justify-center text-3xl sm:text-4xl shadow-inner">
            <i class="far fa-heart"></i>
        </div>
        <h3 class="font-display font-bold text-xl sm:text-2xl text-gray-900 mb-2">Your Wishlist is Empty</h3>
        <p class="text-xs sm:text-sm text-gray-500 mb-6 leading-relaxed">
            You haven't saved any products yet. Browse our festival collections and tap the heart icon to save your favorites!
        </p>
        <a href="<?php echo BASE_URL; ?>shop" class="inline-flex items-center space-x-2 px-6 py-3 rounded-full bg-gradient-to-r from-[#990024] to-[#7a001c] text-white font-bold text-xs uppercase tracking-widest shadow-lg hover:shadow-xl hover:scale-105 transition duration-300">
            <i class="fas fa-shopping-bag"></i>
            <span>Browse Products</span>
        </a>
    </div>
</div>

<script>
const WishlistPage = {
    async loadItems() {
        const loadingEl = document.getElementById('wishlist-loading');
        const gridEl = document.getElementById('wishlist-items-grid');
        const emptyEl = document.getElementById('wishlist-empty-state');
        const guestBanner = document.getElementById('wishlist-guest-banner');

        if (loadingEl) loadingEl.classList.remove('hidden');
        if (gridEl) gridEl.classList.add('hidden');
        if (emptyEl) emptyEl.classList.add('hidden');

        // Check login state
        const isLoggedIn = (window.Auth && window.Auth.token);
        if (!isLoggedIn && guestBanner) {
            guestBanner.classList.remove('hidden');
        } else if (guestBanner) {
            guestBanner.classList.add('hidden');
        }

        try {
            const res = await Api.get('/wishlist');
            if (loadingEl) loadingEl.classList.add('hidden');

            if (res.success && res.data && res.data.length > 0) {
                this.renderGrid(res.data);
                if (gridEl) gridEl.classList.remove('hidden');
            } else {
                if (emptyEl) emptyEl.classList.remove('hidden');
            }
        } catch (e) {
            if (loadingEl) loadingEl.classList.add('hidden');
            if (emptyEl) emptyEl.classList.remove('hidden');
            if (window.Utils && Utils.showToast) {
                Utils.showToast("Failed to load wishlist: " + e.message, "error");
            }
        }
    },

    renderGrid(products) {
        const gridEl = document.getElementById('wishlist-items-grid');
        if (!gridEl) return;

        gridEl.innerHTML = products.map(prod => {
            const img = prod.primary_image || '/assets/images/placeholder.jpg';
            const price = parseFloat(prod.price).toFixed(2);
            const mrp = prod.mrp ? parseFloat(prod.mrp).toFixed(2) : null;
            const inStock = parseInt(prod.stock_quantity) > 0;
            
            // Calculate discount percentage
            let discountBadge = '';
            if (mrp && parseFloat(mrp) > parseFloat(price)) {
                const pct = Math.round(((parseFloat(mrp) - parseFloat(price)) / parseFloat(mrp)) * 100);
                if (pct > 0) {
                    discountBadge = `<span class="absolute top-2.5 left-2.5 z-10 bg-[#990024] text-white text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full shadow-sm">${pct}% OFF</span>`;
                }
            }

            return `
                <div class="group bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col overflow-hidden relative">
                    
                    ${discountBadge}

                    <!-- Remove Button -->
                    <button type="button" 
                        onclick="WishlistManager.toggle(${prod.id}, this, event)" 
                        aria-label="Remove item" 
                        title="Remove item" 
                        class="absolute top-2.5 right-2.5 z-20 w-8 h-8 rounded-full bg-white/90 backdrop-blur-md shadow-md flex items-center justify-center text-gray-500 hover:text-red-600 hover:scale-110 transition duration-200">
                        <i class="fas fa-times text-xs"></i>
                    </button>

                    <!-- Product Image -->
                    <a href="${BASE_URL}product?id=${prod.id}" class="block relative aspect-square overflow-hidden bg-gray-50">
                        <img src="${img}" alt="${prod.name}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </a>

                    <!-- Details -->
                    <div class="p-3.5 sm:p-5 flex-1 flex flex-col justify-between space-y-3">
                        <div>
                            ${prod.category_name ? `<span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">${prod.category_name}</span>` : ''}
                            <a href="${BASE_URL}product?id=${prod.id}" class="font-bold text-xs sm:text-sm text-gray-900 hover:text-[#990024] line-clamp-2 transition-colors">
                                ${prod.name}
                            </a>
                        </div>

                        <div>
                            <!-- Price -->
                            <div class="flex items-baseline space-x-2 mb-2">
                                <span class="font-display font-extrabold text-sm sm:text-base text-[#990024]">₹${price}</span>
                                ${mrp ? `<span class="text-xs text-gray-400 line-through">₹${mrp}</span>` : ''}
                            </div>

                            <!-- Stock status badge -->
                            <div class="mb-3">
                                ${inStock ? 
                                    `<span class="inline-flex items-center text-[10px] font-bold text-emerald-600"><i class="fas fa-check-circle mr-1 text-[9px]"></i> In Stock</span>` : 
                                    `<span class="inline-flex items-center text-[10px] font-bold text-red-500"><i class="fas fa-times-circle mr-1 text-[9px]"></i> Out of Stock</span>`}
                            </div>

                            <!-- Move to Cart Button -->
                            <button type="button" 
                                onclick="WishlistManager.moveToCart(${prod.id}, this)" 
                                ${!inStock ? 'disabled' : ''} 
                                class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-[#990024] to-[#7a001c] hover:from-[#7a001c] hover:to-[#5c0015] disabled:opacity-50 text-white font-bold text-[10px] sm:text-xs uppercase tracking-wider flex items-center justify-center space-x-1.5 shadow transition-all duration-200">
                                <i class="fas fa-shopping-bag text-xs"></i>
                                <span>Move to Cart</span>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    WishlistPage.loadItems();
});
</script>

<?php
include_once __DIR__ . '/includes/footer.php';
?>
