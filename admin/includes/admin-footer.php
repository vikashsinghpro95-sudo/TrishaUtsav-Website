            </div> <!-- End max-w -->
        </main>
    </div> <!-- End Main Content Wrapper -->

    <!-- Global Toast Container (Accessible) -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 flex flex-col space-y-2 pointer-events-none" aria-live="polite"></div>

    <!-- UI Interaction Scripts -->
    <script>
        // Sidebar Toggling Logic
        const sidebar = document.getElementById('app-sidebar');
        const mobileOverlay = document.getElementById('mobile-overlay');
        let desktopSidebarCollapsed = false;

        function toggleSidebar() {
            // Mobile toggle
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                mobileOverlay.classList.remove('hidden');
                setTimeout(() => mobileOverlay.classList.remove('opacity-0'), 10);
                document.body.classList.add('overflow-hidden'); // Prevent background scroll
            } else {
                sidebar.classList.add('-translate-x-full');
                mobileOverlay.classList.add('opacity-0');
                setTimeout(() => mobileOverlay.classList.add('hidden'), 300);
                document.body.classList.remove('overflow-hidden');
            }
        }

        function toggleDesktopSidebar() {
            desktopSidebarCollapsed = !desktopSidebarCollapsed;
            if (desktopSidebarCollapsed) {
                sidebar.classList.replace('w-64', 'w-20');
                document.querySelectorAll('.sidebar-section-title').forEach(el => el.classList.add('opacity-0', 'h-0', 'py-0', 'pt-0', 'pb-0', 'overflow-hidden'));
                document.querySelectorAll('.admin-nav-link span').forEach(el => el.classList.add('opacity-0', 'w-0', 'hidden'));
                document.querySelectorAll('.sidebar-logo span').forEach(el => el.classList.add('opacity-0', 'w-0', 'hidden'));
                document.querySelector('.sidebar-user-info').classList.add('hidden');
                document.querySelector('.sidebar-user-actions').classList.replace('space-x-2', 'flex-col');
                document.querySelector('.sidebar-user-actions').classList.add('space-y-2');
            } else {
                sidebar.classList.replace('w-20', 'w-64');
                document.querySelectorAll('.sidebar-section-title').forEach(el => el.classList.remove('opacity-0', 'h-0', 'py-0', 'pt-0', 'pb-0', 'overflow-hidden'));
                document.querySelectorAll('.admin-nav-link span').forEach(el => el.classList.remove('opacity-0', 'w-0', 'hidden'));
                document.querySelectorAll('.sidebar-logo span').forEach(el => el.classList.remove('opacity-0', 'w-0', 'hidden'));
                document.querySelector('.sidebar-user-info').classList.remove('hidden');
                document.querySelector('.sidebar-user-actions').classList.replace('flex-col', 'space-x-2');
                document.querySelector('.sidebar-user-actions').classList.remove('space-y-2');
            }
        }

        // Dark Mode DISABLED — Admin panel is permanently in Light Mode (White & Red theme)
        function toggleDarkMode() {
            // Dark mode is permanently disabled. Always stay in light mode.
            document.documentElement.classList.remove('dark');
            localStorage.removeItem('admin_theme');
        }

        // Ensure light mode is always active on page load
        document.documentElement.classList.remove('dark');
        localStorage.removeItem('admin_theme');

        // Active Menu Routing State Logic
        document.addEventListener('DOMContentLoaded', () => {
            const path = window.location.pathname;
            const links = {
                'index.php': 'dashboard',
                'products.php': 'products',
                'product-edit.php': 'products',
                'categories.php': 'categories',
                'brands.php': 'brands',
                'orders.php': 'orders',
                'order-detail.php': 'orders',
                'customers.php': 'customers',
                'customer-detail.php': 'customers',
                'coupons.php': 'coupons',
                'banners.php': 'banners',
                'reels.php': 'reels',
                'occasions.php': 'occasions',
                'pages.php': 'pages',
                'settings.php': 'settings',
                'homepage-sections.php': 'homepage-sections',
                'audit-logs.php': 'audit-logs'
            };

            for (let file in links) {
                if (path.endsWith(file) || (file === 'index.php' && (path.endsWith('/admin/') || path.endsWith('/admin')))) {
                    const activeTab = links[file];
                    const btn = document.getElementById(`nav-link-${activeTab}`);
                    if (btn) {
                        btn.classList.add('bg-primary-50', 'text-primary-700', 'dark:bg-primary-900/20', 'dark:text-primary-400');
                        btn.classList.remove('text-slate-600', 'dark:text-slate-400');
                        const icon = btn.querySelector('i');
                        if (icon) {
                            icon.classList.remove('text-slate-400');
                            icon.classList.add('text-primary-600', 'dark:text-primary-500');
                        }
                    }
                    
                    const pageTitle = document.getElementById('admin-page-title');
                    if (pageTitle) {
                        let displayTitle = activeTab.replace(/-/g, ' ');
                        // Title casing
                        displayTitle = displayTitle.replace(
                            /\w\S*/g,
                            function(txt) {
                                return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
                            }
                        );
                        
                        // Overrides
                        if (file === 'product-edit.php') displayTitle = 'Edit Product';
                        if (file === 'order-detail.php') displayTitle = 'Order Details';
                        if (file === 'customer-detail.php') displayTitle = 'Customer Profile';
                        if (file === 'homepage-sections.php') displayTitle = 'Homepage Layout';
                        
                        pageTitle.innerText = displayTitle;
                    }
                    break;
                }
            }
        });
        
        // Override global Utils.showToast for SaaS design if available
        if (typeof Utils !== 'undefined' && Utils.showToast) {
            Utils.showToast = function(message, type = 'success') {
                const container = document.getElementById('toast-container');
                if (!container) return;

                const toast = document.createElement('div');
                toast.className = `flex items-center p-4 w-full max-w-xs text-sm rounded-lg shadow-lg border transition-all duration-300 transform translate-y-4 opacity-0 pointer-events-auto
                    ${type === 'success' ? 'bg-white dark:bg-slate-800 border-green-200 dark:border-green-900 text-green-800 dark:text-green-300' : ''}
                    ${type === 'error' ? 'bg-white dark:bg-slate-800 border-red-200 dark:border-red-900 text-red-800 dark:text-red-300' : ''}
                    ${type === 'warning' ? 'bg-white dark:bg-slate-800 border-amber-200 dark:border-amber-900 text-amber-800 dark:text-amber-300' : ''}
                    ${type === 'info' ? 'bg-white dark:bg-slate-800 border-blue-200 dark:border-blue-900 text-blue-800 dark:text-blue-300' : ''}
                `;

                let icon = '';
                if (type === 'success') icon = '<i class="ph ph-check-circle text-xl mr-3 text-green-500"></i>';
                if (type === 'error') icon = '<i class="ph ph-warning-circle text-xl mr-3 text-red-500"></i>';
                if (type === 'warning') icon = '<i class="ph ph-warning text-xl mr-3 text-amber-500"></i>';
                if (type === 'info') icon = '<i class="ph ph-info text-xl mr-3 text-blue-500"></i>';

                toast.innerHTML = `
                    ${icon}
                    <div class="flex-1 font-medium">${message}</div>
                    <button class="ml-auto -mx-1.5 -my-1.5 rounded-lg p-1.5 inline-flex h-8 w-8 text-slate-500 hover:text-slate-900 dark:hover:text-white focus:ring-2 focus:ring-slate-300" aria-label="Close" onclick="this.parentElement.remove()">
                        <i class="ph ph-x text-lg"></i>
                    </button>
                `;

                container.appendChild(toast);
                
                // Animate in
                requestAnimationFrame(() => {
                    toast.classList.remove('translate-y-4', 'opacity-0');
                    toast.classList.add('translate-y-0', 'opacity-100');
                });

                // Auto dismiss
                setTimeout(() => {
                    toast.classList.remove('translate-y-0', 'opacity-100');
                    toast.classList.add('opacity-0', 'translate-x-4');
                    setTimeout(() => toast.remove(), 300);
                }, 4000);
            };
        }
    </script>
</body>
</html>
