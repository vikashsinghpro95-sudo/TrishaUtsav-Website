<?php
// admin/banners.php
include_once __DIR__ . '/includes/admin-header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Promotional Banners</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage homepage banner carousels and promotional graphics.</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 lg:gap-8">
    
    <!-- Banners List (Left side) -->
    <div class="xl:col-span-2 bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                <i class="ph ph-image mr-2 text-slate-400"></i> Active Banners
            </h3>
        </div>

        <div class="overflow-x-auto min-h-[400px]">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-900/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-32">Preview</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Title & Link</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24">Actions</th>
                    </tr>
                </thead>
                <tbody id="banners-tbody" class="bg-white dark:bg-slate-850 divide-y divide-slate-200 dark:divide-slate-800">
                    <!-- Loaded dynamically -->
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-2"></div>
                            <span class="block text-sm text-slate-500 dark:text-slate-400">Loading banners...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Banner Form (Right side) -->
    <div class="xl:col-span-1">
        <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden sticky top-24">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
                <h3 id="banner-form-title" class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-plus-circle mr-2 text-slate-400"></i> Add Banner
                </h3>
                <button id="btn-cancel-edit-banner" onclick="Banners.resetForm()" class="hidden text-xs font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 px-2.5 py-1 rounded transition-colors focus:outline-none">
                    Cancel
                </button>
            </div>

            <form id="frm-banner-save" class="p-5 space-y-5">
                <div>
                    <label for="banner-title" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Banner Title</label>
                    <input type="text" id="banner-title" required placeholder="e.g. Festival Mega Launch 2026" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Banner Image</label>
                    <div class="flex items-center space-x-3 mb-2">
                        <input type="file" id="banner-image-file" accept="image/*" class="hidden" onchange="Banners.uploadImage(this)">
                        <button type="button" onclick="document.getElementById('banner-image-file').click()" class="inline-flex items-center px-3 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors whitespace-nowrap">
                            <i class="ph ph-upload-simple mr-2"></i> Upload Image
                        </button>
                        <input type="text" id="banner-image" readonly class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 placeholder-slate-400 sm:text-sm transition-colors" placeholder="No file uploaded">
                    </div>
                    <div class="flex items-start text-xs text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-lg border border-slate-100 dark:border-slate-800">
                        <i class="ph ph-info mr-2 text-primary-500 text-base flex-shrink-0"></i>
                        <span>Recommended size: <strong class="font-semibold text-slate-700 dark:text-slate-300">1920x600 px</strong> (high contrast widescreen). Max file size: 10MB.</span>
                    </div>
                </div>

                <div>
                    <label for="banner-link" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Redirect Link URL <span class="text-slate-400 font-normal">(Optional)</span></label>
                    <input type="text" id="banner-link" placeholder="e.g. shop.php?category=smartphones" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                </div>

                <div>
                    <label for="banner-status" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Visibility Status</label>
                    <select id="banner-status" required class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors cursor-pointer">
                        <option value="active">Active (Visible on homepage)</option>
                        <option value="inactive">Inactive (Hidden)</option>
                    </select>
                </div>

                <div class="pt-2">
                    <button type="submit" id="btn-save-banner" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                        Save Banner
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/admin/assets/js/banners.js?v=<?php echo time(); ?>"></script>

<?php
include_once __DIR__ . '/includes/admin-footer.php';
?>
