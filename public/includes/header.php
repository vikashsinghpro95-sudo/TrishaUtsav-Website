<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/config.php';
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../models/Wishlist.php';
require_once __DIR__ . '/../../includes/WishlistHelper.php';

$wishlistIds = WishlistHelper::getWishlistProductIds();
?>
<!DOCTYPE html>
<html lang="en" class="overflow-x-hidden">
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Trisha Utsav | Premium Indian Festival & Celebration Store'; ?></title>

<?php if (isset($ogProduct) && !empty($ogProduct)): ?>
    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="product" />
    <meta property="og:title" content="<?php echo $ogTitle; ?>" />
    <meta property="og:description" content="<?php echo $ogDesc; ?>" />
    <meta property="og:url" content="<?php echo $ogUrl; ?>" />
    <meta property="og:image" content="<?php echo $ogImage; ?>" />
    <meta property="og:site_name" content="Trisha Utsav" />
    <meta property="product:price:amount" content="<?php echo $ogPrice; ?>" />
    <meta property="product:price:currency" content="INR" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo $ogTitle; ?>" />
    <meta name="twitter:description" content="<?php echo $ogDesc; ?>" />
    <meta name="twitter:image" content="<?php echo $ogImage; ?>" />
<?php else: ?>
    <!-- Fallback Default Social Meta Tags -->
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?php echo isset($pageTitle) ? $pageTitle : 'Trisha Utsav – Premium Indian Festival & Celebration Store'; ?>" />
    <meta property="og:description" content="Explore handcrafted sweets, artisanal diyas, royal ethnic creations, and pure celebration attributes at Trisha Utsav." />
    <meta property="og:image" content="https://trishautsav.in/favicon.png" />
    <meta property="og:url" content="https://trishautsav.in/" />
    <meta property="og:site_name" content="Trisha Utsav" />
    <meta name="twitter:card" content="summary_large_image" />
<?php endif; ?>
    
    <!-- Google Fonts: Fraunces (Display Serif) & Plus Jakarta Sans (Body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;0,9..144,700;0,9..144,800;0,9..144,900;1,9..144,400;1,9..144,600;1,9..144,700&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        crimson: {
                            DEFAULT: '#990024',
                            50: '#fdf2f4',
                            100: '#fbe5e8',
                            200: '#f7ced5',
                            300: '#f0a8b5',
                            400: '#e47489',
                            500: '#d34360',
                            600: '#b92444',
                            700: '#990024',
                            800: '#800220',
                            900: '#6c061e',
                            950: '#3e010e',
                        },
                        ivory: {
                            DEFAULT: '#fffdf7',
                            dark: '#f6f1e5',
                        },
                        gold: {
                            DEFAULT: '#f59e0b',
                            light: '#fde047',
                            dark: '#d97706',
                        },
                        ink: {
                            DEFAULT: '#12090c',
                            light: '#1c0d12',
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        display: ['Fraunces', 'serif'],
                    },
                    animation: {
                        'spin-slow': 'spin 25s linear infinite',
                        'marquee': 'marquee 28s linear infinite',
                        'pulse-glow': 'pulseGlow 2.5s infinite',
                    },
                    keyframes: {
                        marquee: {
                            '0%': { transform: 'translateX(0%)' },
                            '100%': { transform: 'translateX(-50%)' },
                        },
                        pulseGlow: {
                            '0%, 100%': { opacity: '1', transform: 'scale(1)' },
                            '50%': { opacity: '0.6', transform: 'scale(1.08)' },
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    
    <style>
        :root {
            --crimson: #990024;
            --ivory: #fffdf7;
            --gold: #f59e0b;
            --ink: #12090c;
        }
        body {
            background-color: var(--ivory);
            color: #1f2937;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
        }
        .font-display {
            font-family: 'Fraunces', serif;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #12090c;
        }
        ::-webkit-scrollbar-thumb {
            background: #990024;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #f59e0b;
        }

        /* ---- GLOBAL OVERFLOW FIXES FOR 320px VIEWPORTS ---- */
        html, body {
            max-width: 100vw;
            overflow-x: hidden;
        }
        *, *::before, *::after {
            box-sizing: border-box;
        }
        img, video, iframe, embed {
            max-width: 100%;
            height: auto;
        }
        /* Force word break on long strings (emails, urls) */
        h1, h2, h3, h4, p, span, a {
            overflow-wrap: break-word;
        }
        /* Ensure inputs do not overflow */
        input, textarea, select {
            max-width: 100%;
        }
        /* Universal flex wrap for known overflowing rows */
        .trust-row, .promo-form {
            flex-wrap: wrap;
        }
        .promo-input {
            min-width: 0; /* allows shrinking below content width */
        }
    </style>

    <!-- Pass Global Constants to Client JS -->
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const API_BASE_URL = '<?php echo API_BASE_URL; ?>';
        window.wishlistIds = <?php echo json_encode(array_map('intval', $wishlistIds)); ?>;
    </script>

    <!-- Base Libraries -->
    <script src="<?php echo BASE_URL; ?>assets/js/utils.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/api.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/auth.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/wishlist.js?v=<?php echo time(); ?>"></script>
</head>
<body class="bg-[#fffdf7] text-gray-800 font-sans min-h-screen flex flex-col antialiased selection:bg-[#990024] selection:text-white overflow-x-hidden w-full max-w-full">

    <!-- 🪔 Royal Festive Page Loader & Route Transition Overlay -->
    <div id="website-page-loader" class="fixed inset-0 bg-[#fffdf7] z-[9999] flex flex-col items-center justify-center transition-opacity duration-300 pointer-events-auto">
        <div class="relative flex flex-col items-center justify-center space-y-5">
            <!-- Pulsing Emblem -->
            <div class="relative">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-br from-[#990024] via-[#7a001c] to-[#4a0011] text-white font-display text-3xl sm:text-4xl font-black flex items-center justify-center shadow-2xl border-2 border-[#f59e0b]/50 animate-pulse">
                    त्रि
                </div>
                <!-- Spinning Gold Rangoli Ring -->
                <div class="absolute -inset-3 rounded-full border-2 border-dashed border-[#990024]/40 animate-spin-slow pointer-events-none"></div>
            </div>
            
            <div class="text-center space-y-1">
                <span class="font-display text-lg sm:text-xl font-extrabold text-[#12090c] tracking-tight block">
                    Trisha<span class="text-[#990024]">Utsav</span>
                </span>
                <span class="text-[10px] font-extrabold text-[#990024] uppercase tracking-widest block opacity-90 animate-pulse">
                    One Place For Every Occasion.
                </span>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const loader = document.getElementById('website-page-loader');
            if (!loader) return;

            function hideLoader() {
                loader.style.opacity = '0';
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 300);
            }

            function showLoader() {
                loader.style.display = 'flex';
                loader.offsetHeight;
                loader.style.opacity = '1';
            }

            // Hide loader on window load or DOMReady
            if (document.readyState === 'complete') {
                hideLoader();
            } else {
                window.addEventListener('load', hideLoader);
                setTimeout(hideLoader, 2000); // Safety fallback
            }

            // Trigger smooth loader overlay when switching between pages
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (link && link.href) {
                    try {
                        const url = new URL(link.href, window.location.href);
                        if (url.origin === window.location.origin && 
                            !url.hash && 
                            !link.getAttribute('target') && 
                            !link.href.startsWith('javascript:') &&
                            !link.getAttribute('onclick')) {
                            showLoader();
                        }
                    } catch(err) {}
                }
            });

            // Restore loader on back/forward cache navigation
            window.addEventListener('pageshow', function(e) {
                if (e.persisted) hideLoader();
            });
        })();
    </script>

    <!-- Global Cart Badge Handler -->
    <script>
        const CartModule = {
            async updateCartBadge() {
                const badges = document.querySelectorAll('.cart-badge-count-target');
                if (!badges || badges.length === 0) return;
                
                if (!localStorage.getItem('auth_token')) {
                    const guestCart = JSON.parse(localStorage.getItem('guest_cart')) || [];
                    let count = 0;
                    guestCart.forEach(item => count += parseInt(item.quantity || 1));
                    
                    badges.forEach(b => {
                        b.innerText = count.toString();
                        if (count > 0) {
                            b.classList.remove('hidden');
                        } else {
                            b.classList.add('hidden');
                        }
                    });
                    return;
                }

                try {
                    const res = await Api.get('/cart/count');
                    if (res.success) {
                        const count = res.count || 0;
                        badges.forEach(b => {
                            b.innerText = count.toString();
                            if (count > 0) {
                                b.classList.remove('hidden');
                            } else {
                                b.classList.add('hidden');
                            }
                        });
                    }
                } catch (e) {
                    badges.forEach(b => b.classList.add('hidden'));
                }
            }
        };
        window.CartModule = CartModule;

        document.addEventListener('DOMContentLoaded', () => {
            CartModule.updateCartBadge();

            // Populate header user section based on authentication
            const userNavSecs = document.querySelectorAll('.header-user-section-target');
            const mobileUserSecs = document.querySelectorAll('.mobile-drawer-user-target');
            if (user) {
                const u = JSON.parse(user);
                userNavSecs.forEach(sec => {
                    sec.innerHTML = `
                        <div class="relative group">
                            <button class="text-xs font-bold text-gray-800 hover:text-[#990024] focus:outline-none transition flex items-center space-x-1.5 py-1.5 px-3 rounded-full bg-white/80 border border-[#f59e0b]/30">
                                <i class="far fa-user text-xs text-[#990024]"></i>
                                <span class="font-bold">Hi, ${u.first_name}</span>
                                <i class="fas fa-chevron-down text-[9px] text-gray-400"></i>
                            </button>
                            <div class="absolute right-0 w-48 bg-white border border-[#f59e0b]/20 rounded-2xl shadow-xl py-2 mt-2 hidden group-hover:block z-50">
                                <a href="${BASE_URL}account" class="block px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-[#990024]/5 hover:text-[#990024] transition">
                                    <i class="fas fa-th-large mr-2 text-[#f59e0b]"></i> Dashboard
                                </a>
                                <a href="${BASE_URL}account" onclick="setTimeout(() => { if(window.Account) window.Account.switchTab('orders'); }, 100);" class="block px-4 py-2.5 text-xs font-semibold text-gray-700 hover:bg-[#990024]/5 hover:text-[#990024] transition">
                                    <i class="fas fa-box-open mr-2 text-[#f59e0b]"></i> My Orders
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <button onclick="Auth.logout()" class="w-full text-left block px-4 py-2 text-xs font-semibold text-red-500 hover:bg-red-50 transition">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </button>
                            </div>
                        </div>
                    `;
                });
                
                mobileUserSecs.forEach(sec => {
                    sec.innerHTML = `
                        <div class="flex items-center space-x-3 mb-4 p-2 bg-gray-50 rounded-2xl">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#990024] to-[#4a0011] text-white flex items-center justify-center font-bold text-lg shadow-inner">
                                ${u.first_name.charAt(0)}
                            </div>
                            <div class="flex-1 overflow-hidden">
                                <div class="font-bold text-gray-900 truncate">Hi, ${u.first_name}</div>
                                <div class="text-[10px] text-gray-500 truncate">${u.email}</div>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <a href="${BASE_URL}account" onclick="toggleMobileMenu(false)" class="block p-3 rounded-2xl hover:bg-gray-50 text-gray-800 flex items-center space-x-3 font-bold text-sm">
                                <i class="fas fa-th-large text-[#f59e0b] w-5 text-center"></i><span>My Account</span>
                            </a>
                            <a href="${BASE_URL}account" onclick="setTimeout(() => { if(window.Account) window.Account.switchTab('orders'); }, 100); toggleMobileMenu(false);" class="block p-3 rounded-2xl hover:bg-gray-50 text-gray-800 flex items-center space-x-3 font-bold text-sm">
                                <i class="fas fa-box-open text-[#f59e0b] w-5 text-center"></i><span>Your Orders</span>
                            </a>
                            <button onclick="Auth.logout()" class="w-full p-3 rounded-2xl bg-red-50 hover:bg-red-100 text-red-600 flex items-center space-x-3 font-bold text-sm transition">
                                <i class="fas fa-sign-out-alt w-5 text-center"></i><span>Logout</span>
                            </button>
                        </div>
                    `;
                });
            }
        });
    </script>

    <!-- SECTION 1: Crimson Top Announcement Bar -->
    <div class="bg-gradient-to-r from-[#990024] via-[#7a001c] to-[#4a0011] text-[#fffdf7] py-1.5 sm:py-2 px-3 sm:px-4 text-[10px] sm:text-xs font-semibold tracking-wider relative z-50 border-b border-[#f59e0b]/30">
        <div class="max-w-[1800px] mx-auto px-4 md:px-[50px] flex justify-between items-center">
            <div class="flex items-center space-x-2 text-[10px] sm:text-xs truncate mx-auto sm:mx-0">
                <span class="bg-[#f59e0b] text-[#12090c] font-black uppercase text-[8px] sm:text-[9px] px-2 py-0.5 rounded-full tracking-widest shadow-sm flex-shrink-0 animate-pulse">Offer</span>
                <span class="truncate">✨ Free Shipping ₹499+</span>
            </div>
            <div class="hidden sm:flex items-center space-x-6 text-[11px] text-[#fffdf7]/80">
                <a href="<?php echo BASE_URL; ?>contact" class="hover:text-[#f59e0b] transition"><i class="fas fa-headset mr-1 text-[#f59e0b]"></i> 24/7 Support</a>
                <span>|</span>
                <span class="text-[#fde047] font-bold"><i class="fas fa-shield-halved mr-1"></i> Handcrafted</span>
            </div>
        </div>
    </div>

    <!-- SECTION 2: Sticky Glass Navigation Bar -->
    <header class="sticky top-0 z-50 bg-[#fffdf7]/95 backdrop-blur-xl border-b border-[#f59e0b]/20 shadow-sm transition duration-300">
        <div class="max-w-[1800px] mx-auto px-4 md:px-[50px]">
            <div class="flex justify-between items-center h-16 sm:h-20">
                
                <!-- Left: Mobile Menu Trigger + Brand Logo -->
                <div class="flex items-center space-x-2 sm:space-x-3 mr-2 sm:mr-6 lg:mr-10 xl:mr-14 min-w-0">
                    <!-- Mobile Hamburger Drawer Button -->
                    <button type="button" onclick="toggleMobileMenu(true)" class="lg:hidden w-9 h-9 rounded-full bg-white border border-[#f59e0b]/30 text-[#12090c] flex flex-shrink-0 items-center justify-center shadow-sm active:scale-95 transition" title="Open Menu">
                        <i class="fas fa-bars text-xs text-[#990024]"></i>
                    </button>

                    <a href="<?php echo BASE_URL; ?>" class="flex items-center space-x-2 sm:space-x-3 group min-w-0">
                        <div class="relative flex-shrink-0">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-[#990024] via-[#7a001c] to-[#4a0011] text-white rounded-2xl flex items-center justify-center font-display text-xl sm:text-2xl font-black shadow-lg shadow-[#990024]/20 border border-[#f59e0b]/30 group-hover:scale-105 transition duration-300">
                                त्रि
                            </div>
                            <span class="absolute -top-0.5 -right-0.5 w-3 h-3 sm:w-3.5 sm:h-3.5 rounded-full bg-[#f59e0b] animate-ping opacity-75"></span>
                            <span class="absolute -top-0.5 -right-0.5 w-3 h-3 sm:w-3.5 sm:h-3.5 rounded-full bg-[#f59e0b] border-2 border-white shadow-sm"></span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="font-display font-black text-xl sm:text-2xl tracking-tight text-[#12090c] leading-none group-hover:text-[#990024] transition duration-200 truncate">
                                Trisha<span class="text-[#990024]">Utsav</span>
                            </span>
                            <span class="text-[9px] sm:text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1 truncate hidden min-[380px]:block">
                                One Place For Every Occasion.
                            </span>
                        </div>
                    </a>
                </div>

                <?php
                $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
                $currentClean = trim(str_replace(['/public', '.php'], '', $requestUri), '/');
                if (empty($currentClean)) $currentClean = 'index';
                $currentQuery = $_SERVER['QUERY_STRING'] ?? '';

                $isOccasionsActive = ($currentClean === 'occasions');
                $isCategoriesActive = ($currentClean === 'categories');
                $isTrendingActive = ($currentClean === 'shop' && str_contains($currentQuery, 'search=trending'));
                $isReelsActive = str_contains($currentQuery, 'reels');
                $isShopActive = ($currentClean === 'shop' && !$isTrendingActive);
                $isHomeActive = (($currentClean === 'index' || $currentClean === '') && !$isReelsActive);
                ?>

                <!-- Navigation Links (Desktop) -->
                <nav class="hidden lg:flex items-center space-x-4 lg:space-x-6 xl:space-x-10 text-[10px] xl:text-xs font-bold uppercase tracking-widest mx-2 lg:mx-4 xl:mx-8">
                    <a href="<?php echo BASE_URL; ?>" class="<?php echo $isHomeActive ? 'text-[#990024] font-extrabold' : 'text-gray-700 hover:text-[#990024]'; ?> transition relative py-1 group">
                        <span>Home</span>
                        <span class="absolute bottom-0 left-0 <?php echo $isHomeActive ? 'w-full' : 'w-0 group-hover:w-full'; ?> h-0.5 bg-[#990024] rounded-full transition-all duration-300"></span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>occasions" class="<?php echo $isOccasionsActive ? 'text-[#990024] font-extrabold' : 'text-gray-700 hover:text-[#990024]'; ?> transition relative py-1 group">
                        <span>Occasions</span>
                        <span class="absolute bottom-0 left-0 <?php echo $isOccasionsActive ? 'w-full' : 'w-0 group-hover:w-full'; ?> h-0.5 bg-[#990024] rounded-full transition-all duration-300"></span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>categories" class="<?php echo $isCategoriesActive ? 'text-[#990024] font-extrabold' : 'text-gray-700 hover:text-[#990024]'; ?> transition relative py-1 group">
                        <span>Categories</span>
                        <span class="absolute bottom-0 left-0 <?php echo $isCategoriesActive ? 'w-full' : 'w-0 group-hover:w-full'; ?> h-0.5 bg-[#990024] rounded-full transition-all duration-300"></span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>shop" class="<?php echo $isShopActive ? 'text-[#990024] font-extrabold' : 'text-gray-700 hover:text-[#990024]'; ?> transition relative py-1 group">
                        <span>Shop</span>
                        <span class="absolute bottom-0 left-0 <?php echo $isShopActive ? 'w-full' : 'w-0 group-hover:w-full'; ?> h-0.5 bg-[#990024] rounded-full transition-all duration-300"></span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>shop?search=trending" class="<?php echo $isTrendingActive ? 'text-[#990024] font-extrabold' : 'text-gray-700 hover:text-[#990024]'; ?> transition relative py-1 group">
                        <span>Trending</span>
                        <span class="absolute bottom-0 left-0 <?php echo $isTrendingActive ? 'w-full' : 'w-0 group-hover:w-full'; ?> h-0.5 bg-[#990024] rounded-full transition-all duration-300"></span>
                    </a>
                </nav>

                <script>
                    (function highlightActiveNav() {
                        function updateNav() {
                            const path = window.location.pathname.toLowerCase();
                            const search = window.location.search.toLowerCase();
                            const hash = window.location.hash.toLowerCase();

                            const links = document.querySelectorAll('header nav a');
                            links.forEach(link => {
                                const href = (link.getAttribute('href') || '').toLowerCase();
                                let isActive = false;

                                if (href.includes('sort=newest') && search.includes('sort=newest')) {
                                    isActive = true;
                                } else if (href.includes('search=trending') && search.includes('search=trending')) {
                                    isActive = true;
                                } else if (href.includes('reels') && (search.includes('reels') || hash.includes('reels'))) {
                                    isActive = true;
                                } else if (href.endsWith('shop.php') && path.endsWith('shop.php') && !search.includes('sort=newest') && !search.includes('search=trending')) {
                                    isActive = true;
                                } else if ((href.endsWith('index.php') || href === BASE_URL.toLowerCase()) && 
                                           (path.endsWith('index.php') || path.endsWith('/') || path.endsWith('/public/')) && 
                                           !search.includes('reels') && !hash.includes('reels')) {
                                    isActive = true;
                                }

                                const underline = link.querySelector('span:last-child');
                                if (isActive) {
                                    link.classList.remove('text-gray-700');
                                    link.classList.add('text-[#990024]', 'font-extrabold');
                                    if (underline) {
                                        underline.classList.remove('w-0');
                                        underline.classList.add('w-full');
                                    }
                                } else {
                                    link.classList.remove('text-[#990024]', 'font-extrabold');
                                    link.classList.add('text-gray-700');
                                    if (underline) {
                                        underline.classList.remove('w-full');
                                        underline.classList.add('w-0');
                                    }
                                }
                            });
                        }

                        if (document.readyState === 'loading') {
                            document.addEventListener('DOMContentLoaded', updateNav);
                        } else {
                            updateNav();
                        }
                    })();
                </script>

                <!-- Controls (Far Most Right) -->
                <div class="hidden lg:flex items-center space-x-2 sm:space-x-3 ml-auto">
                    <!-- Wishlist Button -->
                    <a href="<?php echo BASE_URL; ?>wishlist" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full border border-[#f59e0b]/30 bg-white hover:bg-[#990024]/5 text-[#12090c] hover:text-[#990024] flex items-center justify-center transition shadow-sm relative group" title="View Wishlist">
                        <i class="far fa-heart text-xs sm:text-sm"></i>
                        <span id="header-wishlist-count" class="absolute -top-1 -right-1 bg-[#f59e0b] text-[#12090c] text-[9px] font-black rounded-full h-4 w-4 sm:h-4.5 sm:w-4.5 flex items-center justify-center border border-white shadow-sm font-sans">
                            <?php echo count($wishlistIds); ?>
                        </span>
                    </a>

                    <!-- User Profile Button -->
                    <div class="header-user-section-target">
                        <a href="<?php echo BASE_URL; ?>login" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full border border-[#f59e0b]/30 bg-white hover:bg-[#990024]/5 text-[#12090c] flex items-center justify-center transition shadow-sm" title="Sign In">
                            <i class="far fa-user text-xs sm:text-sm"></i>
                        </a>
                    </div>

                    <!-- Primary Cart Button with Badge -->
                    <a href="<?php echo BASE_URL; ?>cart" class="bg-gradient-to-r from-[#990024] via-[#7a001c] to-[#5c0015] text-[#fffdf7] font-extrabold text-[10px] xl:text-xs uppercase tracking-widest px-3 lg:px-4 xl:px-5 py-2 lg:py-2.5 rounded-full shadow-md flex items-center space-x-1.5 transition border border-[#f59e0b]/40 relative flex-shrink-0">
                        <i class="fas fa-shopping-bag text-xs sm:text-sm text-white"></i>
                        <span class="hidden sm:inline">Cart</span>
                        <span class="cart-badge-count-target bg-white text-[#990024] text-[9px] font-black h-4 w-4 sm:h-5 sm:w-5 rounded-full flex items-center justify-center shadow-md hidden ml-0.5 sm:ml-1">0</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Navigation Drawer -->
    <div id="mobile-menu-overlay" class="fixed inset-0 bg-[#12090c]/80 backdrop-blur-md z-[60] transform -translate-x-full transition-transform duration-500 flex flex-col p-4 sm:p-6 lg:hidden">
        <div class="bg-[#fffdf7] rounded-3xl p-5 shadow-2xl flex flex-col h-full w-full max-w-[320px] overflow-y-auto">
            <div class="flex justify-between items-center pb-4 border-b border-gray-100 shrink-0">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#990024] to-[#4a0011] text-[#f59e0b] font-display text-lg font-black flex items-center justify-center shadow-sm">
                        त्रि
                    </div>
                    <span class="font-display font-bold text-lg text-[#12090c]">TrishaUtsav</span>
                </div>
                <button onclick="toggleMobileMenu(false)" class="w-8 h-8 rounded-full bg-gray-100 text-gray-700 flex items-center justify-center hover:bg-gray-200 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="py-4 shrink-0">
                <form action="<?php echo BASE_URL; ?>shop" method="GET" class="relative">
                    <input type="text" name="search" placeholder="Search products..." class="w-full bg-white border border-gray-200 rounded-2xl py-3 pl-11 pr-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#990024]/20 focus:border-[#990024]/40 transition shadow-sm">
                    <i class="fas fa-search absolute left-4 top-3.5 text-gray-400"></i>
                </form>
            </div>

            <nav class="flex flex-col space-y-1.5 font-bold text-sm flex-1">
                <a href="<?php echo BASE_URL; ?>" onclick="toggleMobileMenu(false)" class="p-3 rounded-2xl <?php echo $isHomeActive ? 'bg-[#990024]/10 text-[#990024]' : 'hover:bg-gray-50 text-gray-800'; ?> flex items-center space-x-3 transition">
                    <i class="fas fa-house <?php echo $isHomeActive ? 'text-[#990024]' : 'text-gray-400'; ?> w-5 text-center"></i><span>Home</span>
                </a>
                <a href="<?php echo BASE_URL; ?>occasions" onclick="toggleMobileMenu(false)" class="p-3 rounded-2xl <?php echo $isOccasionsActive ? 'bg-[#990024]/10 text-[#990024]' : 'hover:bg-gray-50 text-gray-800'; ?> flex items-center space-x-3 transition">
                    <i class="fas fa-glass-cheers <?php echo $isOccasionsActive ? 'text-[#990024]' : 'text-[#f59e0b]'; ?> w-5 text-center"></i><span>Occasions</span>
                </a>
                <a href="<?php echo BASE_URL; ?>categories" onclick="toggleMobileMenu(false)" class="p-3 rounded-2xl <?php echo $isCategoriesActive ? 'bg-[#990024]/10 text-[#990024]' : 'hover:bg-gray-50 text-gray-800'; ?> flex items-center space-x-3 transition">
                    <i class="fas fa-th-large <?php echo $isCategoriesActive ? 'text-[#990024]' : 'text-[#f59e0b]'; ?> w-5 text-center"></i><span>Categories</span>
                </a>
                <a href="<?php echo BASE_URL; ?>shop" onclick="toggleMobileMenu(false)" class="p-3 rounded-2xl <?php echo $isShopActive ? 'bg-[#990024]/10 text-[#990024]' : 'hover:bg-gray-50 text-gray-800'; ?> flex items-center space-x-3 transition">
                    <i class="fas fa-store <?php echo $isShopActive ? 'text-[#990024]' : 'text-[#f59e0b]'; ?> w-5 text-center"></i><span>Shop Catalog</span>
                </a>
                <a href="<?php echo BASE_URL; ?>cart" onclick="toggleMobileMenu(false)" class="p-3 rounded-2xl hover:bg-gray-50 text-gray-800 flex items-center space-x-3 transition">
                    <i class="fas fa-shopping-bag text-[#f59e0b] w-5 text-center"></i><span>My Cart</span>
                    <span class="cart-badge-count-target ml-auto bg-[#990024] text-white text-[10px] px-2 py-0.5 rounded-full hidden">0</span>
                </a>
            </nav>
            
            <div class="mt-4 pt-4 border-t border-gray-100 shrink-0">
                <div class="mobile-drawer-user-target">
                    <!-- Default Logged Out State -->
                    <a href="<?php echo BASE_URL; ?>login" class="block w-full py-3.5 bg-gradient-to-r from-[#990024] to-[#7a001c] text-white text-center rounded-2xl text-sm font-extrabold tracking-wide shadow-md hover:shadow-lg transition">
                        Login / Sign Up
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleMobileMenu(open) {
            const overlay = document.getElementById('mobile-menu-overlay');
            if (!overlay) return;
            if (open) overlay.classList.remove('-translate-x-full');
            else overlay.classList.add('-translate-x-full');
        }
    </script>
    <div id="modal-phone-otp" class="fixed inset-0 bg-[#12090c]/80 backdrop-blur-md z-[9999] flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-[2rem] p-6 sm:p-8 max-w-md w-full shadow-2xl border border-gray-100 space-y-6 text-center relative animate-fade-in overflow-hidden">
            <!-- Decorative background blob -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-[#990024]/10 rounded-full blur-3xl"></div>
            
            <!-- Close Button -->
            <button type="button" onclick="Auth.closeOtpModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 bg-gray-50 hover:bg-gray-100 rounded-full w-8 h-8 flex items-center justify-center transition z-20">
                <i class="fas fa-times"></i>
            </button>

            <div class="w-16 h-16 bg-gradient-to-br from-[#990024] to-[#c70030] text-white rounded-2xl flex items-center justify-center mx-auto text-2xl shadow-xl shadow-[#990024]/30 transform -rotate-3 relative z-10">
                <i class="fas fa-shield-halved"></i>
            </div>
            
            <div class="relative z-10">
                <h3 class="font-display text-2xl font-extrabold text-gray-900">Secure Your Account</h3>
                <p class="text-sm text-gray-500 font-medium mt-2">Verify your mobile number to receive order updates and royal rewards.</p>
            </div>

            <!-- Step 1: Input Phone Form -->
            <div id="otp-step-phone" class="space-y-5 relative z-10">
                <div class="text-left space-y-1.5">
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest pl-1">Mobile Number</label>
                    <div class="flex space-x-2">
                        <!-- Country Code Dropdown -->
                        <div class="relative w-[105px] shrink-0">
                            <select id="otp-country-code" class="w-full appearance-none bg-gray-50 border-2 border-gray-100 text-gray-800 text-sm font-bold rounded-xl py-3.5 pl-3 pr-7 focus:border-[#990024] focus:ring-4 focus:ring-[#990024]/10 outline-none transition cursor-pointer shadow-inner">
                                <option value="91">🇮🇳 +91</option>
                                <option value="1">🇺🇸 +1</option>
                                <option value="44">🇬🇧 +44</option>
                                <option value="971">🇦🇪 +971</option>
                                <option value="61">🇦🇺 +61</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-[10px] pointer-events-none"></i>
                        </div>
                        
                        <!-- Phone Input -->
                        <div class="relative flex-grow">
                            <input type="tel" id="otp-phone-input" maxlength="15" placeholder="Mobile number" class="w-full bg-gray-50 border-2 border-gray-100 text-gray-800 text-base font-bold rounded-xl py-3.5 px-4 focus:border-[#990024] focus:ring-4 focus:ring-[#990024]/10 outline-none transition shadow-inner placeholder-gray-400">
                        </div>
                    </div>
                </div>

                <button type="button" onclick="Auth.sendPhoneOtp()" id="btn-send-otp" class="w-full bg-gradient-to-r from-[#990024] to-[#c70030] hover:from-[#7a001c] hover:to-[#990024] text-white font-black text-sm uppercase tracking-widest py-4 rounded-xl shadow-lg shadow-[#990024]/30 transition transform hover:-translate-y-0.5">
                    Get Security Code <i class="fas fa-arrow-right ml-2 opacity-80"></i>
                </button>
            </div>

            <!-- Step 2: Input 6-Digit OTP Form -->
            <div id="otp-step-code" class="space-y-6 hidden relative z-10">
                <div class="bg-emerald-50 text-emerald-700 text-xs font-bold py-2.5 px-4 rounded-lg flex items-center justify-center border border-emerald-100">
                    <i class="fas fa-check-circle mr-2 text-emerald-500"></i> Code sent to +<span id="otp-target-code" class="mr-1"></span><span id="otp-target-phone"></span>
                </div>
                
                <div class="space-y-2">
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest">Enter 6-digit Code</label>
                    <div class="flex justify-between gap-1 sm:gap-2 max-w-[320px] mx-auto">
                        <input type="text" id="otp-digit-1" maxlength="1" oninput="Auth.focusNextOtpInput(1)" class="w-10 h-12 sm:w-11 sm:h-14 bg-gray-50 border-2 border-gray-200 rounded-xl text-center text-xl sm:text-2xl font-black text-gray-800 outline-none focus:border-[#990024] focus:bg-white focus:ring-4 focus:ring-[#990024]/10 transition shadow-inner">
                        <input type="text" id="otp-digit-2" maxlength="1" oninput="Auth.focusNextOtpInput(2)" class="w-10 h-12 sm:w-11 sm:h-14 bg-gray-50 border-2 border-gray-200 rounded-xl text-center text-xl sm:text-2xl font-black text-gray-800 outline-none focus:border-[#990024] focus:bg-white focus:ring-4 focus:ring-[#990024]/10 transition shadow-inner">
                        <input type="text" id="otp-digit-3" maxlength="1" oninput="Auth.focusNextOtpInput(3)" class="w-10 h-12 sm:w-11 sm:h-14 bg-gray-50 border-2 border-gray-200 rounded-xl text-center text-xl sm:text-2xl font-black text-gray-800 outline-none focus:border-[#990024] focus:bg-white focus:ring-4 focus:ring-[#990024]/10 transition shadow-inner">
                        <input type="text" id="otp-digit-4" maxlength="1" oninput="Auth.focusNextOtpInput(4)" class="w-10 h-12 sm:w-11 sm:h-14 bg-gray-50 border-2 border-gray-200 rounded-xl text-center text-xl sm:text-2xl font-black text-gray-800 outline-none focus:border-[#990024] focus:bg-white focus:ring-4 focus:ring-[#990024]/10 transition shadow-inner">
                        <input type="text" id="otp-digit-5" maxlength="1" oninput="Auth.focusNextOtpInput(5)" class="w-10 h-12 sm:w-11 sm:h-14 bg-gray-50 border-2 border-gray-200 rounded-xl text-center text-xl sm:text-2xl font-black text-gray-800 outline-none focus:border-[#990024] focus:bg-white focus:ring-4 focus:ring-[#990024]/10 transition shadow-inner">
                        <input type="text" id="otp-digit-6" maxlength="1" oninput="Auth.focusNextOtpInput(6)" class="w-10 h-12 sm:w-11 sm:h-14 bg-gray-50 border-2 border-gray-200 rounded-xl text-center text-xl sm:text-2xl font-black text-gray-800 outline-none focus:border-[#990024] focus:bg-white focus:ring-4 focus:ring-[#990024]/10 transition shadow-inner">
                    </div>
                </div>

                <button type="button" onclick="Auth.verifyPhoneOtp()" id="btn-verify-otp" class="w-full bg-gradient-to-r from-[#990024] to-[#c70030] hover:from-[#7a001c] hover:to-[#990024] text-white font-black text-sm uppercase tracking-widest py-4 rounded-xl shadow-lg shadow-[#990024]/30 transition transform hover:-translate-y-0.5">
                    Verify & Continue <i class="fas fa-check ml-2 opacity-80"></i>
                </button>

                <button type="button" onclick="Auth.resetOtpStep()" class="text-xs text-gray-400 hover:text-[#990024] font-bold underline decoration-2 underline-offset-4 transition block mx-auto pt-2">
                    Wrong phone number?
                </button>
            </div>
        </div>
    </div>


    <!-- Main Container -->
    <main class="w-full flex-grow">
