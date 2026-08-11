<?php
// admin/homepage-sections.php
include_once __DIR__ . '/includes/admin-header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Homepage Layout</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Reorder and customize section visibility on the live homepage.</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="AdminSections.save()" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors shadow-sm">
            <i class="ph ph-floppy-disk mr-2"></i> Save Layout
        </button>
    </div>
</div>

<div class="max-w-4xl space-y-6">
    <!-- Section Reordering List -->
    <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                <i class="ph ph-list-dashes mr-2 text-slate-400"></i> Active Sections
            </h3>
        </div>
        <div class="p-5">
            <div id="sections-container" class="space-y-3">
                <div class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-2"></div>
                    <span class="block text-sm text-slate-500 dark:text-slate-400">Loading layout configuration...</span>
                </div>
            </div>
            
            <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/50 rounded-lg flex gap-3">
                <i class="ph-fill ph-info text-blue-500 dark:text-blue-400 text-xl flex-shrink-0"></i>
                <p class="text-sm text-blue-800 dark:text-blue-300">Use the up and down arrows to change the order in which these sections appear on the homepage. Toggle the switch to show or hide a section entirely.</p>
            </div>
        </div>
    </div>
</div>

<script>
const AdminSections = {
    sections: [],

    async init() {
        await this.load();
    },

    async load() {
        const container = document.getElementById('sections-container');
        try {
            const res = await Api.get('/homepage/sections');
            if (res.success && res.data) {
                this.sections = res.data;
                this.render();
            }
        } catch(e) {
            container.innerHTML = `<div class="text-center py-8 text-red-500 text-sm font-medium">Failed to load sections config.</div>`;
        }
    },

    render() {
        const container = document.getElementById('sections-container');
        if (this.sections.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12 text-slate-500 dark:text-slate-400">
                    <i class="ph ph-layout text-4xl mb-3 opacity-50"></i>
                    <p class="text-sm font-medium">No sections configured.</p>
                </div>
            `;
            return;
        }

        let html = '';
        this.sections.forEach((sec, idx) => {
            const iconMap = {
                'categories': 'ph-squares-four text-primary-500',
                'trending': 'ph-fire text-amber-500',
                'occasions': 'ph-confetti text-pink-500',
                'must_buy': 'ph-sketch-logo text-purple-500',
                'reels': 'ph-video-camera text-rose-500',
                'mega_sale': 'ph-timer text-amber-500'
            };
            const icon = iconMap[sec.id] || 'ph-stack text-blue-500';

            const enabledClass = sec.enabled ? 'bg-primary-600' : 'bg-slate-200 dark:bg-slate-700';
            const knobClass = sec.enabled ? 'translate-x-5' : 'translate-x-0';
            
            html += `
                <div class="group bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between transition-all hover:shadow-md hover:border-slate-300 dark:hover:border-slate-600">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 flex items-center justify-center text-xl">
                            <i class="${icon}"></i>
                        </div>
                        <div>
                            <span class="font-semibold text-slate-900 dark:text-white text-sm block">${sec.name}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5 block">ID: ${sec.id}</span>
                        </div>
                    </div>

                    <div class="flex items-center space-x-6">
                        <!-- Custom Toggle Switch -->
                        <div class="flex items-center space-x-3">
                            <span class="text-xs font-medium ${sec.enabled ? 'text-primary-600 dark:text-primary-400' : 'text-slate-500 dark:text-slate-400'}">
                                ${sec.enabled ? 'Visible' : 'Hidden'}
                            </span>
                            <button type="button" onclick="AdminSections.toggle(${idx})" class="${enabledClass} relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2" role="switch" aria-checked="${sec.enabled}">
                                <span aria-hidden="true" class="${knobClass} pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                            </button>
                        </div>

                        <!-- Move controls -->
                        <div class="flex items-center space-x-1 border-l border-slate-200 dark:border-slate-700 pl-4">
                            <button onclick="AdminSections.move(${idx}, -1)" ${idx === 0 ? 'disabled' : ''} class="w-8 h-8 rounded text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-white disabled:opacity-30 disabled:hover:bg-transparent flex items-center justify-center transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500" title="Move Up">
                                <i class="ph ph-caret-up text-lg"></i>
                            </button>
                            <button onclick="AdminSections.move(${idx}, 1)" ${idx === this.sections.length - 1 ? 'disabled' : ''} class="w-8 h-8 rounded text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-white disabled:opacity-30 disabled:hover:bg-transparent flex items-center justify-center transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500" title="Move Down">
                                <i class="ph ph-caret-down text-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    },

    toggle(idx) {
        this.sections[idx].enabled = !this.sections[idx].enabled;
        this.render();
    },

    move(idx, direction) {
        const targetIdx = idx + direction;
        if (targetIdx < 0 || targetIdx >= this.sections.length) return;

        const temp = this.sections[idx];
        this.sections[idx] = this.sections[targetIdx];
        this.sections[targetIdx] = temp;

        // Recalculate order numbers
        this.sections.forEach((sec, i) => sec.order = i + 1);
        this.render();
    },

    async save() {
        this.sections.forEach((sec, i) => sec.order = i + 1);

        try {
            const res = await Api.put('/admin/homepage/sections', { sections: this.sections });
            if (res.success) {
                if(window.Utils && Utils.showToast) Utils.showToast(res.message || 'Homepage layout saved successfully!', 'success');
            }
        } catch(e) {
            if(window.Utils && Utils.showToast) Utils.showToast(e.message || 'Failed to save layout', 'error');
        }
    }
};

document.addEventListener('DOMContentLoaded', () => AdminSections.init());
</script>

<?php
include_once __DIR__ . '/includes/admin-footer.php';
?>
