<?php
// admin/includes/admin-header.php
?>
<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Trisha Utsav - Admin Console</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Phosphor Icons (Crisp, Outlined) -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- FontAwesome (For Legacy Dashboard Layout) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            200: '#fecaca',
                            300: '#fca5a5',
                            400: '#f87171',
                            500: '#ef4444',
                            600: '#dc2626', // Primary Brand (Red)
                            700: '#b91c1c',
                            800: '#991b1b',
                            900: '#7f1d1d',
                            950: '#450a0a',
                        },
                        slate: {
                            850: '#151e2e',
                            900: '#0f172a',
                        },
                        success: '#10B981',
                        warning: '#F59E0B',
                        error: '#EF4444',
                        info: '#3B82F6',
                    }
                }
            }
        }
    </script>
    
    <!-- Chart.js for Dashboards -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="/admin/assets/css/admin.css">

    <!-- Pass Global Constants to Client JS -->
    <script>
        const BASE_URL = '/admin/';
        const API_BASE_URL = '/api';
        const FRONTEND_BASE_URL = '';
        
        // Force Light Mode — Dark mode permanently disabled
        localStorage.removeItem('admin_theme');
        document.documentElement.classList.remove('dark');
        document.documentElement.setAttribute('data-theme', 'light');
    </script>

    <!-- Base Libraries -->
    <script src="/admin/assets/js/utils.js"></script>
    <script src="/admin/assets/js/api.js"></script>
    <script src="/admin/assets/js/auth.js"></script>

    <script>
        // Synchronous auth check
        if (!localStorage.getItem('admin_token') && !window.location.pathname.endsWith('login.php') && !window.location.pathname.endsWith('login')) {
            window.location.href = '/admin/login';
        }
    </script>
</head>
<body class="bg-white text-slate-800 min-h-screen flex overflow-hidden">

    <!-- Verify Admin Auth Role dynamically -->
    <script>
        if (!window.location.pathname.endsWith('login.php') && !window.location.pathname.endsWith('login')) {
            Auth.checkAuth();
        }
    </script>

    <!-- Mobile Overlay -->
    <div id="mobile-overlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity opacity-0" onclick="toggleSidebar()"></div>

    <!-- 1. Sidebar Navigation (Collapsible & Off-canvas) -->
    <aside id="app-sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-red-100 flex flex-col transition-all duration-300 transform -translate-x-full lg:translate-x-0 lg:static lg:flex-shrink-0">
        
        <!-- Logo Area -->
        <div class="h-16 px-5 border-b border-red-100 flex items-center justify-between flex-shrink-0">
            <a href="/admin" class="flex items-center space-x-2.5 overflow-hidden sidebar-logo">
                <div class="w-8 h-8 rounded-lg bg-primary-600 flex items-center justify-center flex-shrink-0 shadow-sm">
                    <i class="ph ph-package text-white text-lg"></i>
                </div>
                <span class="font-bold text-slate-900 tracking-tight whitespace-nowrap transition-opacity duration-300">
                    Trisha Utsav
                </span>
            </a>
            <button class="lg:hidden text-slate-500 hover:text-slate-700 focus:outline-none rounded-md focus-visible:ring-2 focus-visible:ring-primary-500" onclick="toggleSidebar()">
                <i class="ph ph-x text-xl"></i>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto overflow-x-hidden p-3 space-y-1 custom-scrollbar">
            
            <div class="sidebar-section-title px-3 pt-3 pb-1 text-[11px] font-semibold text-red-400 uppercase tracking-wider whitespace-nowrap overflow-hidden transition-opacity duration-300">Dashboard</div>
            <a href="/admin" id="nav-link-dashboard" class="admin-nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-600 hover:bg-red-50 hover:text-red-700 transition-colors group">
                <i class="ph ph-squares-four text-lg text-slate-400 group-hover:text-red-600 mr-3 flex-shrink-0"></i>
                <span class="whitespace-nowrap flex-1 truncate transition-opacity duration-300">Overview</span>
            </a>

            <div class="sidebar-section-title px-3 pt-5 pb-1 text-[11px] font-semibold text-red-400 uppercase tracking-wider whitespace-nowrap overflow-hidden transition-opacity duration-300">Catalog</div>
            <a href="/admin/products" id="nav-link-products" class="admin-nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-600 hover:bg-red-50 hover:text-red-700 transition-colors group">
                <i class="ph ph-tag text-lg text-slate-400 group-hover:text-red-600 mr-3 flex-shrink-0"></i>
                <span class="whitespace-nowrap flex-1 truncate transition-opacity duration-300">Products</span>
            </a>
            <a href="/admin/categories" id="nav-link-categories" class="admin-nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-600 hover:bg-red-50 hover:text-red-700 transition-colors group">
                <i class="ph ph-folders text-lg text-slate-400 group-hover:text-red-600 mr-3 flex-shrink-0"></i>
                <span class="whitespace-nowrap flex-1 truncate transition-opacity duration-300">Categories</span>
            </a>
            <a href="/admin/brands" id="nav-link-brands" class="admin-nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-600 hover:bg-red-50 hover:text-red-700 transition-colors group">
                <i class="ph ph-star text-lg text-slate-400 group-hover:text-red-600 mr-3 flex-shrink-0"></i>
                <span class="whitespace-nowrap flex-1 truncate transition-opacity duration-300">Brands</span>
            </a>

            <div class="sidebar-section-title px-3 pt-5 pb-1 text-[11px] font-semibold text-red-400 uppercase tracking-wider whitespace-nowrap overflow-hidden transition-opacity duration-300">Sales</div>
            <a href="/admin/orders" id="nav-link-orders" class="admin-nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-600 hover:bg-red-50 hover:text-red-700 transition-colors group">
                <i class="ph ph-shopping-cart text-lg text-slate-400 group-hover:text-red-600 mr-3 flex-shrink-0"></i>
                <span class="whitespace-nowrap flex-1 truncate transition-opacity duration-300">Orders</span>
            </a>
            <a href="/admin/customers" id="nav-link-customers" class="admin-nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-600 hover:bg-red-50 hover:text-red-700 transition-colors group">
                <i class="ph ph-users text-lg text-slate-400 group-hover:text-red-600 mr-3 flex-shrink-0"></i>
                <span class="whitespace-nowrap flex-1 truncate transition-opacity duration-300">Customers</span>
            </a>

            <div class="sidebar-section-title px-3 pt-5 pb-1 text-[11px] font-semibold text-red-400 uppercase tracking-wider whitespace-nowrap overflow-hidden transition-opacity duration-300">Marketing & CMS</div>
            <a href="/admin/coupons" id="nav-link-coupons" class="admin-nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-600 hover:bg-red-50 hover:text-red-700 transition-colors group">
                <i class="ph ph-ticket text-lg text-slate-400 group-hover:text-red-600 mr-3 flex-shrink-0"></i>
                <span class="whitespace-nowrap flex-1 truncate transition-opacity duration-300">Coupons</span>
            </a>
            <a href="/admin/newsletter" id="nav-link-newsletter" class="admin-nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-600 hover:bg-red-50 hover:text-red-700 transition-colors group">
                <i class="ph ph-envelope text-lg text-slate-400 group-hover:text-red-600 mr-3 flex-shrink-0"></i>
                <span class="whitespace-nowrap flex-1 truncate transition-opacity duration-300">Newsletter</span>
            </a>
            <a href="/admin/banners" id="nav-link-banners" class="admin-nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-600 hover:bg-red-50 hover:text-red-700 transition-colors group">
                <i class="ph ph-image text-lg text-slate-400 group-hover:text-red-600 mr-3 flex-shrink-0"></i>
                <span class="whitespace-nowrap flex-1 truncate transition-opacity duration-300">Banners</span>
            </a>
            <a href="/admin/reels" id="nav-link-reels" class="admin-nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-600 hover:bg-red-50 hover:text-red-700 transition-colors group">
                <i class="ph ph-video text-lg text-slate-400 group-hover:text-red-600 mr-3 flex-shrink-0"></i>
                <span class="whitespace-nowrap flex-1 truncate transition-opacity duration-300">Reels</span>
            </a>
            <a href="/admin/occasions" id="nav-link-occasions" class="admin-nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-600 hover:bg-red-50 hover:text-red-700 transition-colors group">
                <i class="ph ph-sparkle text-lg text-slate-400 group-hover:text-red-600 mr-3 flex-shrink-0"></i>
                <span class="whitespace-nowrap flex-1 truncate transition-opacity duration-300">Occasions</span>
            </a>
            <a href="/admin/pages" id="nav-link-pages" class="admin-nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-600 hover:bg-red-50 hover:text-red-700 transition-colors group">
                <i class="ph ph-file-text text-lg text-slate-400 group-hover:text-red-600 mr-3 flex-shrink-0"></i>
                <span class="whitespace-nowrap flex-1 truncate transition-opacity duration-300">Static Pages</span>
            </a>

            <div class="sidebar-section-title px-3 pt-5 pb-1 text-[11px] font-semibold text-red-400 uppercase tracking-wider whitespace-nowrap overflow-hidden transition-opacity duration-300">System</div>
            <a href="/admin/homepage-sections" id="nav-link-homepage-sections" class="admin-nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-600 hover:bg-red-50 hover:text-red-700 transition-colors group">
                <i class="ph ph-layout text-lg text-slate-400 group-hover:text-red-600 mr-3 flex-shrink-0"></i>
                <span class="whitespace-nowrap flex-1 truncate transition-opacity duration-300">Homepage Layout</span>
            </a>
            <a href="/admin/settings" id="nav-link-settings" class="admin-nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-600 hover:bg-red-50 hover:text-red-700 transition-colors group">
                <i class="ph ph-gear text-lg text-slate-400 group-hover:text-red-600 mr-3 flex-shrink-0"></i>
                <span class="whitespace-nowrap flex-1 truncate transition-opacity duration-300">Settings</span>
            </a>
            <a href="/admin/audit-logs" id="nav-link-audit-logs" class="admin-nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-600 hover:bg-red-50 hover:text-red-700 transition-colors group">
                <i class="ph ph-shield-check text-lg text-slate-400 group-hover:text-red-600 mr-3 flex-shrink-0"></i>
                <span class="whitespace-nowrap flex-1 truncate transition-opacity duration-300">Audit Logs</span>
            </a>

        </nav>

        <!-- User / Bottom Profile -->
        <div class="p-4 border-t border-red-100 flex-shrink-0">
            <div class="flex items-center space-x-3 mb-3">
                <div class="w-9 h-9 rounded-full bg-red-100 flex items-center justify-center text-red-600 font-bold flex-shrink-0">
                    A
                </div>
                <div class="flex-1 min-w-0 transition-opacity duration-300 sidebar-user-info">
                    <p id="admin-profile-name" class="text-sm font-semibold text-slate-900 truncate">Admin</p>
                    <p class="text-xs text-slate-500 truncate">Super Admin</p>
                </div>
            </div>
            <div class="flex items-center space-x-2 transition-opacity duration-300 sidebar-user-actions">
                <button onclick="" class="flex-1 flex items-center justify-center py-1.5 px-2 bg-red-50 hover:bg-red-100 text-red-400 rounded-md transition-colors focus:outline-none" title="Light Mode Active" aria-label="Light Mode Active" disabled>
                    <i id="theme-icon" class="ph ph-sun text-lg text-red-400"></i>
                </button>
                <button onclick="Auth.adminLogout()" class="flex-1 flex items-center justify-center py-1.5 px-2 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 text-red-600 dark:text-red-400 rounded-md transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500" title="Sign Out" aria-label="Sign out">
                    <i class="ph ph-sign-out text-lg"></i>
                </button>
            </div>
        </div>
    </aside>

    <!-- 2. Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden bg-gray-50">
        
        <!-- Sticky Top Header -->
        <header class="h-16 bg-white border-b border-red-100 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-30 flex-shrink-0">
            <div class="flex items-center flex-1">
                <button class="mr-4 lg:hidden text-slate-500 hover:text-red-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 rounded-md" onclick="toggleSidebar()" aria-label="Open sidebar">
                    <i class="ph ph-list text-2xl"></i>
                </button>
                <button class="hidden lg:block mr-4 text-slate-400 hover:text-red-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 rounded-md" onclick="toggleDesktopSidebar()" aria-label="Collapse sidebar">
                    <i class="ph ph-list text-xl"></i>
                </button>
                
                <h1 id="admin-page-title" class="text-lg font-semibold text-slate-900 truncate">
                    <!-- Injected via JS or PHP -->
                </h1>
            </div>
            
            <div class="flex items-center space-x-3 sm:space-x-4">
                <a href="/" target="_blank" class="hidden sm:flex items-center space-x-1.5 px-3 py-1.5 text-sm font-medium text-slate-600 hover:text-red-600 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 rounded-md">
                    <i class="ph ph-arrow-square-out text-lg"></i>
                    <span>Storefront</span>
                </a>
            </div>
        </header>

        <!-- Main Body Scrollable Area -->
        <main class="flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6 lg:p-8 custom-scrollbar">
            <div class="max-w-[1400px] mx-auto">
