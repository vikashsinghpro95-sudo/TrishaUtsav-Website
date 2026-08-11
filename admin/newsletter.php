<?php
// admin/newsletter.php
include_once __DIR__ . '/includes/admin-header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Newsletter Subscribers</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage the users who have subscribed to the Royal Club Insider newsletter.</p>
    </div>
</div>

<div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col overflow-hidden max-w-5xl">
    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
        <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
            <i class="ph ph-envelope mr-2 text-slate-400"></i> Subscriber List
        </h3>
        <span id="subscriber-count" class="bg-primary-100 text-primary-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-primary-900/30 dark:text-primary-300">0 Total</span>
    </div>

    <div class="overflow-x-auto min-h-[400px]">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
            <thead class="bg-slate-50 dark:bg-slate-900/50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-16">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email Address</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Subscribed On</th>
                </tr>
            </thead>
            <tbody id="newsletter-tbody" class="bg-white dark:bg-slate-850 divide-y divide-slate-200 dark:divide-slate-800">
                <!-- Loaded dynamically -->
                <tr>
                    <td colspan="3" class="px-6 py-12 text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-2"></div>
                        <span class="block text-sm text-slate-500 dark:text-slate-400">Loading subscribers...</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="/admin/assets/js/newsletter.js?v=<?php echo time(); ?>"></script>

<?php
include_once __DIR__ . '/includes/admin-footer.php';
?>
