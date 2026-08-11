const Newsletter = {
    init() {
        this.loadSubscribers();
    },

    async loadSubscribers() {
        try {
            const res = await Api.get('/admin/newsletter');
            const tbody = document.getElementById('newsletter-tbody');
            const countBadge = document.getElementById('subscriber-count');

            if (res.success && res.data) {
                const subs = res.data;
                countBadge.textContent = `${subs.length} Total`;
                
                if (subs.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center">
                                <i class="ph ph-envelope-open text-4xl text-slate-300 dark:text-slate-600 mb-2"></i>
                                <span class="block text-sm text-slate-500 dark:text-slate-400">No subscribers yet.</span>
                            </td>
                        </tr>
                    `;
                    return;
                }

                tbody.innerHTML = subs.map(sub => `
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-slate-100">#${sub.id}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 flex items-center justify-center flex-shrink-0 font-bold">
                                    ${sub.email.charAt(0).toUpperCase()}
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-slate-900 dark:text-white">${sub.email}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                            ${Utils.formatDate(sub.created_at)}
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-red-500 text-sm">Failed to load subscribers.</td>
                    </tr>
                `;
            }
        } catch (error) {
            console.error("Error loading subscribers:", error);
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    Newsletter.init();
});
