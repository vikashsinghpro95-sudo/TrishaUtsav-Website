/**
 * Admin Audit Logs Viewer Module
 */

const AuditLogs = {
    filters: {
        page: 1,
        per_page: 15,
        action: '',
        user_id: ''
    },

    async init() {
        this.filters.page = parseInt(Utils.getQueryParam('page')) || 1;
        this.filters.action = Utils.getQueryParam('action') || '';
        this.filters.user_id = Utils.getQueryParam('user_id') || '';

        const actionSelect = document.getElementById('log-action-filter');
        if (actionSelect) actionSelect.value = this.filters.action;

        await this.loadAuditLogsTable();
    },

    async loadAuditLogsTable() {
        const tbody = document.getElementById('audit-logs-tbody');
        const pagination = document.getElementById('audit-logs-pagination');
        if (!tbody) return;

        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-12 text-center">
                    <div class="loader-spinner-dark mx-auto"></div>
                </td>
            </tr>
        `;

        try {
            const q = new URLSearchParams();
            for (let key in this.filters) {
                if (this.filters[key] !== '') q.append(key, this.filters[key]);
            }

            const res = await Api.get('/admin/audit-logs?' + q.toString());
            if (res.success && res.data) {
        if (this.list.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3 text-slate-400 dark:text-slate-500">
                            <i class="ph ph-list-magnifying-glass text-2xl"></i>
                        </div>
                        <p class="text-sm font-medium text-slate-900 dark:text-white">No audit logs found</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Try adjusting your filters.</p>
                    </td>
                </tr>
            `;
            if (pagination) pagination.innerHTML = '';
            return;
        }

        let html = '';
        this.list.forEach(log => {
            const date = new Date(log.created_at).toLocaleDateString('en-IN', {
                year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
            });

            // Format changes details
            let changesHtml = '';
            if (log.old_value || log.new_value) {
                const oldObj = log.old_value || {};
                const newObj = log.new_value || {};
                
                // Pick modified properties
                const allKeys = Array.from(new Set([...Object.keys(oldObj), ...Object.keys(newObj)]));
                const items = [];
                allKeys.forEach(k => {
                    // Skip password differences
                    if (k.includes('password')) return;
                    
                    const oldVal = typeof oldObj[k] === 'object' ? JSON.stringify(oldObj[k]) : oldObj[k];
                    const newVal = typeof newObj[k] === 'object' ? JSON.stringify(newObj[k]) : newObj[k];
                    
                    if (oldVal !== newVal) {
                        items.push(`<div class="flex items-center gap-1.5"><span class="font-medium text-slate-700 dark:text-slate-300">${k}:</span> <span class="line-through text-red-500/80">${oldVal ?? 'N/A'}</span> <i class="ph ph-arrow-right text-slate-400"></i> <span class="text-emerald-600 dark:text-emerald-400">${newVal ?? 'N/A'}</span></div>`);
                    }
                });
                
                if (items.length > 0) {
                    changesHtml = `<div class="space-y-1 text-xs text-slate-600 dark:text-slate-400 font-mono bg-slate-50 dark:bg-slate-900/50 p-2 rounded border border-slate-100 dark:border-slate-800">${items.join('')}</div>`;
                }
            }

            // Map actions to icons and colors
            let actionIcon = 'ph-info';
            let actionColor = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
            
            if (log.action.includes('create')) {
                actionIcon = 'ph-plus-circle';
                actionColor = 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20';
            } else if (log.action.includes('update')) {
                actionIcon = 'ph-pencil-simple';
                actionColor = 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/20';
            } else if (log.action.includes('delete')) {
                actionIcon = 'ph-trash';
                actionColor = 'bg-red-50 text-red-700 ring-1 ring-red-600/20 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/20';
            } else if (log.action.includes('change')) {
                actionIcon = 'ph-arrows-left-right';
                actionColor = 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20';
            }

            html += `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-8 w-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 mr-3 flex-shrink-0">
                                <i class="ph ph-user text-lg"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-slate-900 dark:text-white">${log.first_name || ''} ${log.last_name || 'System'}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">${log.email || 'N/A'}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ${actionColor}">
                            <i class="ph ${actionIcon} mr-1.5"></i>
                            ${log.action.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                        <div class="text-sm text-slate-900 dark:text-white font-medium">${log.entity_type ? (log.entity_type.charAt(0).toUpperCase() + log.entity_type.slice(1)) : 'N/A'}</div>
                        ${log.entity_id ? `<div class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5">ID: ${log.entity_id}</div>` : ''}
                    </td>
                    <td class="px-6 py-4 hidden md:table-cell max-w-xs">
                        ${changesHtml || '<span class="text-xs text-slate-400 dark:text-slate-500 italic">No detailed field changes recorded</span>'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                        <span class="text-xs font-mono text-slate-500 dark:text-slate-400">${log.ip_address || 'N/A'}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-slate-500 dark:text-slate-400">
                        ${date}
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
        this.renderPagination(res.pagination, pagination);
    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-red-500 text-sm font-medium">${e.message}</td></tr>`;
    }
    },

    renderPagination(pag, container) {
        if (!container) return;
        if (pag.total_pages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<div class="flex items-center space-x-1.5">';
        if (pag.current_page > 1) {
            html += `<button onclick="AuditLogs.goToPage(${pag.current_page - 1})" class="p-1.5 border border-gray-300 rounded hover:bg-gray-50 text-gray-600"><i class="fas fa-chevron-left text-[10px]"></i></button>`;
        }

        for (let i = 1; i <= pag.total_pages; i++) {
            const isCurrent = i === pag.current_page;
            const btnClass = isCurrent ? 'bg-primary-600 text-white font-bold' : 'border border-gray-300 hover:bg-gray-50 text-gray-600';
            html += `<button onclick="AuditLogs.goToPage(${i})" class="px-2.5 py-1 rounded text-xs transition ${btnClass}">${i}</button>`;
        }

        if (pag.current_page < pag.total_pages) {
            html += `<button onclick="AuditLogs.goToPage(${pag.current_page + 1})" class="p-1.5 border border-gray-300 rounded hover:bg-gray-50 text-gray-600"><i class="fas fa-chevron-right text-[10px]"></i></button>`;
        }
        html += '</div>';
        container.innerHTML = html;
    },

    goToPage(p) {
        const url = new URL(window.location.href);
        url.searchParams.set('page', p);
        window.location.href = url.toString();
    },

    applyFilters() {
        const action = document.getElementById('log-action-filter').value;
        const url = new URL(window.location.href);
        url.searchParams.set('page', 1);
        if (action) url.searchParams.set('action', action); else url.searchParams.delete('action');
        window.location.href = url.toString();
    }
};

window.AuditLogs = AuditLogs;
document.addEventListener('DOMContentLoaded', () => {
    AuditLogs.init();
});
