<?php
// admin/pages.php
include_once __DIR__ . '/includes/admin-header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">CMS Static Pages</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage content for static pages like Terms, Privacy Policy, and About Us.</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 lg:gap-8">
    <!-- Pages List (Left Side) -->
    <div class="xl:col-span-2 bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                <i class="ph ph-files mr-2 text-slate-400"></i> Published Pages
            </h3>
        </div>

        <div class="overflow-x-auto min-h-[400px]">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-900/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Page Title & Slug</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">Created Date</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24">Actions</th>
                    </tr>
                </thead>
                <tbody id="pages-tbody" class="bg-white dark:bg-slate-850 divide-y divide-slate-200 dark:divide-slate-800">
                    <!-- Loaded dynamically -->
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-2"></div>
                            <span class="block text-sm text-slate-500 dark:text-slate-400">Loading pages...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Page Form (Right Side) -->
    <div class="xl:col-span-1">
        <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden sticky top-24">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
                <h3 id="page-form-title" class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                    <i class="ph ph-file-plus mr-2 text-slate-400"></i> Create Page
                </h3>
                <button id="btn-cancel-edit-page" onclick="Pages.resetForm()" class="hidden text-xs font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 px-2.5 py-1 rounded transition-colors focus:outline-none">
                    Cancel
                </button>
            </div>

            <form id="frm-page-save" class="p-5 space-y-5">
                <div>
                    <label for="page-title" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Page Title</label>
                    <input type="text" id="page-title" required placeholder="e.g. Terms and Conditions" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                </div>

                <div>
                    <label for="page-slug" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Slug (URL Keyword)</label>
                    <div class="flex rounded-md shadow-sm">
                        <span class="inline-flex items-center rounded-l-md border border-r-0 border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 text-slate-500 dark:text-slate-400 sm:text-sm">/page/</span>
                        <input type="text" id="page-slug" placeholder="e.g. terms-conditions" class="block w-full min-w-0 flex-1 rounded-none rounded-r-md px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors font-mono">
                    </div>
                </div>

                <div>
                    <label for="page-content" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1 flex justify-between items-center">
                        Page Content
                        <span class="text-[10px] uppercase font-bold text-blue-600 bg-blue-50 dark:text-blue-400 dark:bg-blue-900/30 px-1.5 py-0.5 rounded">HTML</span>
                    </label>
                    <textarea id="page-content" rows="10" required placeholder="<h1>Terms</h1>\n<p>Enter details here...</p>" class="block w-full p-3 border border-slate-300 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors resize-y font-mono"></textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" id="btn-save-page" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                        Save CMS Page
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/admin/assets/js/pages.js?v=<?php echo time(); ?>"></script>

<?php
include_once __DIR__ . '/includes/admin-footer.php';
?>
