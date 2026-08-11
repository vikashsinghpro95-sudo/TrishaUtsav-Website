<?php
/**
 * Trisha Utsav - Festive Occasions Collection Page
 */
$pageTitle = "Festive Occasions & Collections - Trisha Utsav";

// Load global header (defines BASE_URL, HTML head, assets, etc.)
require_once __DIR__ . '/includes/header.php';

// Safely pre-fetch active occasions for instant client rendering
$initialOccasions = [];
try {
    if (file_exists(__DIR__ . '/../config/database.php')) {
        require_once __DIR__ . '/../config/database.php';
    }
    if (file_exists(__DIR__ . '/../includes/Database.php')) {
        require_once __DIR__ . '/../includes/Database.php';
    }
    if (file_exists(__DIR__ . '/../models/Occasion.php')) {
        require_once __DIR__ . '/../models/Occasion.php';
        $occModel = new Occasion();
        $initialOccasions = $occModel->getActive();
        $emojiMap = [
            'diwali' => '🪔', 'weddings' => '💍', 'wedding' => '💍', 'rakhi' => '🧶',
            'birthdays' => '🎂', 'birthday' => '🎂', 'corporate' => '🎁', 'gudipadva' => '🌾',
            'makar' => '🪁', 'krishnajanmashtami' => '🪈', 'janmashtami' => '🪈', 'holi' => '🎨',
            'ganesh-chaturthi' => '🐘', 'navratri' => '🪘', 'karwa-chauth' => '🌙', 'bhai-dooj' => '✨',
            'dhanteras' => '🪙', 'onam-pongal' => '🌴', 'baby-shower' => '🍼', 'saraswati-puja' => '📜', 'chhath-puja' => '🌅'
        ];
        $iconMap = [
            'diwali' => 'fa-lightbulb', 'weddings' => 'fa-ring', 'wedding' => 'fa-ring', 'rakhi' => 'fa-heart-pulse',
            'birthdays' => 'fa-cake-candles', 'birthday' => 'fa-cake-candles', 'corporate' => 'fa-briefcase', 'gudipadva' => 'fa-sun',
            'makar' => 'fa-paper-plane', 'krishnajanmashtami' => 'fa-feather', 'janmashtami' => 'fa-feather', 'holi' => 'fa-palette',
            'ganesh-chaturthi' => 'fa-om', 'navratri' => 'fa-drum', 'karwa-chauth' => 'fa-moon', 'bhai-dooj' => 'fa-star',
            'dhanteras' => 'fa-coins', 'onam-pongal' => 'fa-seedling', 'baby-shower' => 'fa-baby', 'saraswati-puja' => 'fa-book-open', 'chhath-puja' => 'fa-sun'
        ];
        foreach ($initialOccasions as &$o) {
            $slug = strtolower(trim($o['slug'] ?? ''));
            $o['emoji'] = $emojiMap[$slug] ?? '🎉';
            $o['icon'] = $iconMap[$slug] ?? 'fa-glass-cheers';
        }
        unset($o);
    }
} catch (Throwable $e) {
    // Fallback handled cleanly by JS
}
?>

<script>
    window.INITIAL_OCCASIONS = <?php echo json_encode($initialOccasions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>

<style>
    /* Minimal & Hidden Scrollbars for Chip Bar */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    /* Touch momentum scrolling */
    .chip-scroll-container {
        -webkit-overflow-scrolling: touch;
        scroll-behavior: smooth;
    }
    /* Fade-in Animation for Product Loading */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.25s ease-out forwards;
    }
</style>

<!-- Ultra Compact Mobile Hero Banner -->
<section class="bg-gradient-to-br from-[#990024] via-[#7a001c] to-[#4a0011] text-[#fffdf7] py-3 sm:py-10 px-3 sm:px-6 relative overflow-hidden border-b border-[#f59e0b]/30">
    <div class="max-w-[1800px] mx-auto px-1 sm:px-4 md:px-[50px] text-center space-y-1 sm:space-y-3 relative z-10">
        <span class="hidden sm:inline-flex items-center space-x-2 bg-[#f59e0b] text-[#12090c] font-black uppercase text-[10px] sm:text-xs px-3.5 py-1 rounded-full tracking-widest shadow-md">
            <span>🪔 ROYAL CELEBRATIONS & FESTIVITIES</span>
        </span>
        <h1 class="font-display text-lg sm:text-4xl md:text-5xl font-black tracking-tight leading-tight">Shop By <span class="text-[#f59e0b]">Occasion</span></h1>
        <p class="hidden sm:block text-xs sm:text-sm text-slate-200 max-w-2xl mx-auto font-medium leading-relaxed">Explore hand-curated festive products, traditional sweets, royal hampers, and handcrafted decor tailored for every special Indian festival and life celebration.</p>
    </div>

    <!-- Decorative Gold Accents -->
    <div class="absolute -top-12 -left-12 w-32 h-32 sm:w-48 sm:h-48 bg-[#f59e0b]/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-12 -right-12 w-32 h-32 sm:w-48 sm:h-48 bg-[#f59e0b]/10 rounded-full blur-3xl pointer-events-none"></div>
</section>

<!-- Main Container with Minimal Spacing on Mobile -->
<main class="max-w-[1800px] mx-auto px-3 sm:px-6 md:px-[50px] py-2.5 sm:py-8 space-y-2.5 sm:space-y-6">

    <!-- 1. Occasion Selector Chips Bar -->
    <div class="space-y-1 sm:space-y-2">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-1.5">
                <i class="fas fa-sliders text-[#990024] text-xs sm:text-sm"></i>
                <h2 class="font-display text-xs sm:text-xl font-extrabold text-[#12090c] uppercase tracking-wider sm:normal-case">
                    Filter By Occasion
                </h2>
            </div>
            <button onclick="OccasionPage.selectOccasion('all')" id="btn-all-occasions" class="text-[11px] sm:text-xs font-bold text-[#990024] hover:underline flex items-center space-x-1">
                <span>Reset</span>
                <i class="fas fa-rotate-left text-[9px]"></i>
            </button>
        </div>

        <!-- Chips Carousel Wrapper with Desktop Nav Arrows -->
        <div class="relative group">
            <!-- Scroll Left Button (Desktop) -->
            <button id="chip-scroll-left" onclick="OccasionPage.scrollChips(-220)" aria-label="Scroll left" class="hidden md:flex absolute -left-3 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/95 text-gray-700 border border-gray-200 shadow-md items-center justify-center hover:bg-[#990024] hover:text-white hover:border-[#990024] transition duration-200 opacity-90 hover:opacity-100 focus:outline-none">
                <i class="fas fa-chevron-left text-xs"></i>
            </button>

            <!-- Horizontally Scrollable Chip Bar -->
            <div id="occasions-cards-container" class="chip-scroll-container no-scrollbar flex items-center gap-1.5 sm:gap-2.5 overflow-x-auto py-1 px-0.5 scroll-smooth w-full select-none">
                <div class="py-2 text-center w-full">
                    <div class="loader-spinner-dark mx-auto"></div>
                </div>
            </div>

            <!-- Scroll Right Button (Desktop) -->
            <button id="chip-scroll-right" onclick="OccasionPage.scrollChips(220)" aria-label="Scroll right" class="hidden md:flex absolute -right-3 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/95 text-gray-700 border border-gray-200 shadow-md items-center justify-center hover:bg-[#990024] hover:text-white hover:border-[#990024] transition duration-200 opacity-90 hover:opacity-100 focus:outline-none">
                <i class="fas fa-chevron-right text-xs"></i>
            </button>
        </div>
    </div>

    <!-- 2. Active Occasion Showcase Banner (Compact Mobile Strip) -->
    <div id="active-occasion-banner" class="hidden bg-gradient-to-r from-amber-500/10 via-[#990024]/10 to-amber-500/10 border border-[#f59e0b]/30 p-2 sm:p-6 rounded-xl sm:rounded-3xl flex flex-row items-center justify-between gap-2 shadow-2xs transition-all duration-300">
        <div class="space-y-0.5 min-w-0 flex-1 text-left">
            <span id="active-occ-badge" class="hidden sm:inline-flex bg-[#990024] text-white font-extrabold text-[9px] px-2.5 py-0.5 rounded-full uppercase tracking-wider items-center mb-1">
                <i class="fas fa-sparkles text-amber-300 text-[9px] mr-1"></i>
                <span id="active-occ-badge-text">ACTIVE SELECTION</span>
            </span>
            <h2 id="active-occ-title" class="font-display text-xs sm:text-2xl font-black text-[#12090c] truncate leading-tight">
                Festive Collection
            </h2>
            <p id="active-occ-desc" class="hidden sm:block text-xs sm:text-sm text-gray-600 max-w-2xl font-medium truncate">
                Handcrafted products curated specifically for this celebration.
            </p>
        </div>

        <div class="flex items-center space-x-1.5 sm:space-x-3 shrink-0">
            <span id="active-occ-count" class="bg-white border border-gray-200 font-extrabold text-[10px] sm:text-xs px-2 sm:px-3.5 py-1 sm:py-2 rounded-lg sm:rounded-xl shadow-2xs text-gray-800 whitespace-nowrap">
                0 Items
            </span>
            <button onclick="OccasionPage.selectOccasion('all')" class="text-[10px] sm:text-xs font-bold text-gray-600 hover:text-red-600 bg-white border border-gray-200 px-2 py-1 sm:px-3.5 sm:py-2 rounded-lg sm:rounded-xl hover:border-red-300 transition shadow-2xs whitespace-nowrap flex items-center" title="Clear Filter">
                <i class="fas fa-times text-[10px]"></i>
                <span class="hidden sm:inline ml-1">Clear</span>
            </button>
        </div>
    </div>

    <!-- 3. Header Controls & Sorting -->
    <div class="flex flex-row justify-between items-center gap-2 border-b border-gray-200 pb-2">
        <div class="min-w-0 flex-1">
            <h3 class="font-display text-xs sm:text-lg font-bold text-[#12090c] truncate" id="products-grid-heading">
                All Festive Products
            </h3>
            <span class="text-[10px] sm:text-xs text-gray-500 font-medium block truncate" id="products-count-label">Loading products...</span>
        </div>

        <div class="flex items-center space-x-1.5 text-xs font-bold shrink-0">
            <span class="text-gray-500 uppercase tracking-wider text-[9px] hidden sm:inline">Sort:</span>
            <select id="sort-select" onchange="OccasionPage.handleSortChange(this.value)" class="bg-white border border-gray-200 rounded-lg sm:rounded-xl px-2 py-1 sm:px-3 sm:py-2 outline-none focus:border-[#990024] text-[11px] sm:text-xs font-bold text-gray-700 cursor-pointer shadow-2xs">
                <option value="newest">Newest</option>
                <option value="price_asc">Price: Low to High</option>
                <option value="price_desc">Price: High to Low</option>
                <option value="popular">Popular</option>
            </select>
        </div>
    </div>

    <!-- 4. Products Grid -->
    <div id="occasion-products-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-2 sm:gap-6 min-h-[250px]">
        <div class="col-span-full py-8 text-center">
            <div class="loader-spinner-dark mx-auto"></div>
        </div>
    </div>

    <!-- 5. Load More & Pagination Container -->
    <div class="space-y-3 pt-2 text-center">
        <!-- Load More Button -->
        <div id="load-more-container" class="hidden">
            <button id="btn-load-more" onclick="OccasionPage.loadMoreProducts()" class="inline-flex items-center justify-center space-x-2 bg-white hover:bg-gray-50 text-[#990024] font-black text-xs px-6 py-2.5 sm:px-8 sm:py-3.5 rounded-xl sm:rounded-2xl border-2 border-[#990024]/30 hover:border-[#990024] shadow-xs hover:shadow-md transition-all duration-200 active:scale-95">
                <span id="load-more-text">Load More Products</span>
                <i id="load-more-icon" class="fas fa-chevron-down text-xs ml-1"></i>
            </button>
        </div>

        <!-- Page Numbers Navigation -->
        <div id="occasion-pagination-container" class="flex flex-wrap justify-center items-center gap-1 pt-1"></div>
    </div>

</main>

<script src="<?php echo BASE_URL; ?>assets/js/occasions.js?v=<?php echo time(); ?>"></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
