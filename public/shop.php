<?php
require_once __DIR__ . '/includes/config.php';
include_once __DIR__ . '/includes/header.php';
?>

<!-- Shop Page Festive Header Banner -->
<div class="bg-gradient-to-r from-[#990024] via-[#7a001c] to-[#4a0011] text-[#fffdf7] py-10 sm:py-14 px-4 sm:px-8 border-b border-[#f59e0b]/30 relative overflow-hidden shadow-lg">
    <div class="max-w-[1800px] mx-auto px-4 md:px-[50px] flex flex-col items-center text-center space-y-3 relative z-10">
        <span class="bg-[#f59e0b] text-[#12090c] font-black uppercase text-[10px] sm:text-xs px-3.5 py-1 rounded-full tracking-widest shadow-md">
            🪔 ROYAL INDIAN COLLECTIONS
        </span>
        <h1 class="font-display text-3xl sm:text-5xl font-extrabold text-[#fffdf7] tracking-tight">
            Explore festive <span class="italic text-[#f59e0b] font-normal">catalog</span>
        </h1>
        <p class="text-xs sm:text-sm text-slate-200 max-w-xl font-medium">
            Handcrafted sweets, pure silver attributes, brass diyas, and authentic festive hampers shipped directly across India.
        </p>
    </div>
</div>

<div class="max-w-[1800px] mx-auto px-4 md:px-[50px] my-8 sm:my-12">
    <div class="grid grid-cols-1 lg:grid-cols-4 xl:grid-cols-5 gap-8">
        
        <!-- Sidebar Filters -->
        <div id="filter-sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-white p-5 sm:p-6 shadow-2xl transform -translate-x-full transition-transform duration-300 overflow-y-auto lg:relative lg:w-auto lg:translate-x-0 lg:shadow-sm lg:col-span-1 lg:rounded-3xl lg:border lg:border-[#f59e0b]/20 lg:h-fit lg:block">
            <!-- Mobile Close Button & Overlay -->
            <button onclick="Shop.toggleMobileFilters()" class="lg:hidden absolute top-4 right-4 text-gray-500 hover:text-gray-800 focus:outline-none">
                <i class="fas fa-times text-xl"></i>
            </button>
            <div class="flex justify-between items-center pb-4 border-b border-gray-100">
                <h2 class="font-display text-base font-extrabold text-[#12090c] flex items-center">
                    <i class="fas fa-filter text-[#990024] mr-2 text-sm"></i> Refine Selection
                </h2>
                <button onclick="Shop.clearAllFilters()" class="text-xs text-[#990024] hover:underline font-extrabold uppercase tracking-wider">Reset All</button>
            </div>

            <!-- 1. Categories Filter -->
            <div>
                <h3 class="text-xs font-black text-[#12090c] mb-3 uppercase tracking-wider flex items-center">
                    <i class="fas fa-th text-[#f59e0b] mr-2 text-xs"></i> Categories
                </h3>
                <div id="sidebar-categories" class="max-h-60 overflow-y-auto pr-1 space-y-1">
                    <div class="flex items-center space-x-2 py-2">
                        <div class="w-4 h-4 border-2 border-[#990024] border-t-transparent rounded-full animate-spin"></div>
                        <span class="text-xs text-gray-500">Loading categories...</span>
                    </div>
                </div>
            </div>

            <!-- 2. Brands Filter -->
            <div class="pt-4 border-t border-gray-100">
                <h3 class="text-xs font-black text-[#12090c] mb-3 uppercase tracking-wider flex items-center">
                    <i class="fas fa-tag text-[#f59e0b] mr-2 text-xs"></i> Brands & Artisans
                </h3>
                <div id="sidebar-brands" class="max-h-48 overflow-y-auto pr-1 space-y-1">
                    <div class="flex items-center space-x-2 py-2">
                        <div class="w-4 h-4 border-2 border-[#990024] border-t-transparent rounded-full animate-spin"></div>
                        <span class="text-xs text-gray-500">Loading brands...</span>
                    </div>
                </div>
            </div>

            <!-- 3. Price Filter -->
            <div class="pt-4 border-t border-gray-100">
                <h3 class="text-xs font-black text-[#12090c] mb-3 uppercase tracking-wider flex items-center">
                    <i class="fas fa-rupee-sign text-[#f59e0b] mr-2 text-xs"></i> Price Range
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center space-x-2">
                        <input type="number" id="filter-min-price" placeholder="Min ₹" class="w-1/2 px-3 py-2 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024] font-medium">
                        <span class="text-gray-400 text-xs font-bold">to</span>
                        <input type="number" id="filter-max-price" placeholder="Max ₹" class="w-1/2 px-3 py-2 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024] font-medium">
                    </div>
                    <button id="btn-apply-price" class="w-full bg-[#990024] hover:bg-[#7a001c] text-white font-extrabold py-2 text-xs uppercase tracking-wider rounded-xl transition duration-200 shadow-md">
                        Apply Price Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Catalog Items Section -->
        <div class="lg:col-span-3 xl:col-span-4 space-y-6">
            
            <!-- Toolbar Header -->
            <div class="flex flex-col sm:flex-row justify-between sm:items-center bg-white p-4 sm:p-5 rounded-3xl border border-[#f59e0b]/20 shadow-sm gap-4">
                <div class="flex justify-between items-center w-full sm:w-auto">
                    <span id="results-count" class="text-xs sm:text-sm text-gray-700 font-extrabold uppercase tracking-wider">0 products found</span>
                    <button onclick="Shop.toggleMobileFilters()" class="lg:hidden flex items-center justify-center space-x-2 bg-gray-100 px-3 py-1.5 rounded-lg text-xs font-bold text-gray-700 hover:bg-gray-200 transition">
                        <i class="fas fa-filter text-[#990024]"></i>
                        <span>Filters</span>
                    </button>
                </div>
                <div class="flex items-center space-x-3 w-full sm:w-auto justify-end">
                    <label for="shop-sort" class="text-xs text-gray-500 font-bold uppercase tracking-wider">Sort By:</label>
                    <select id="shop-sort" class="px-4 py-2 border border-gray-200 rounded-xl text-xs outline-none bg-white font-bold text-gray-800 focus:border-[#990024]">
                        <option value="newest">Newest Arrivals</option>
                        <option value="price_asc">Price: Low to High</option>
                        <option value="price_desc">Price: High to Low</option>
                        <option value="name_asc">Name: A to Z</option>
                        <option value="name_desc">Name: Z to A</option>
                    </select>
                </div>
            </div>

            <!-- Product Grid -->
            <div id="product-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3 sm:gap-6">
                <!-- Loaded dynamically -->
            </div>

            <!-- Pagination Container -->
            <div id="shop-pagination" class="flex justify-center pt-8 pb-4">
                <button id="btn-load-more" onclick="Shop.loadMore()" class="hidden bg-white border-2 border-[#990024] text-[#990024] hover:bg-[#990024] hover:text-white font-black text-xs sm:text-sm uppercase tracking-widest py-3 px-10 rounded-full transition duration-300 shadow-sm">
                    Load More
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Filter Backdrop -->
<div id="mobile-filter-backdrop" onclick="Shop.toggleMobileFilters()" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden transition-opacity duration-300 opacity-0"></div>

<script src="<?php echo BASE_URL; ?>assets/js/shop.js?v=<?php echo time(); ?>"></script>

<?php
include_once __DIR__ . '/includes/footer.php';
?>
