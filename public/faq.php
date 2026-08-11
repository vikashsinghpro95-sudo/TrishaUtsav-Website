<?php
require_once __DIR__ . '/includes/config.php';
include_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 my-8 sm:my-12">
    <div class="bg-gradient-to-r from-[#990024] via-[#7a001c] to-[#4a0011] p-8 sm:p-12 rounded-t-3xl text-[#fffdf7] shadow-xl border-t border-x border-[#f59e0b]/30 text-center space-y-2">
        <span class="bg-[#f59e0b] text-[#12090c] font-black uppercase text-[10px] px-3.5 py-1 rounded-full tracking-widest inline-block shadow-md">
            ❓ FREQUENTLY ASKED
        </span>
        <h1 id="cms-page-title" class="font-display text-3xl sm:text-4xl font-extrabold text-[#fffdf7]">
            Festive <span class="italic text-[#f59e0b] font-normal">FAQ</span>
        </h1>
    </div>

    <div class="bg-white p-6 sm:p-10 rounded-b-3xl border-b border-x border-[#f59e0b]/20 shadow-sm">
        <div id="cms-page-content" class="text-xs sm:text-sm text-slate-700 leading-relaxed space-y-4 font-medium">
            <p class="font-bold text-[#12090c]">Find answers to common questions about ordering, packaging, and custom festive hampers.</p>
            <div class="space-y-4 pt-2">
                <div class="p-4 bg-slate-50 rounded-2xl border border-gray-100">
                    <h3 class="font-bold text-[#12090c] text-sm">How long does shipping take during festival sales?</h3>
                    <p class="text-xs text-slate-600 mt-1">Orders are dispatched within 24 hours. Express shipping takes 2-4 business days.</p>
                </div>
                <div class="p-4 bg-slate-50 rounded-2xl border border-gray-100">
                    <h3 class="font-bold text-[#12090c] text-sm">Are sweets freshly prepared?</h3>
                    <p class="text-xs text-slate-600 mt-1">Yes, all mithai and traditional treats are prepared fresh by master sweet makers and vacuum-sealed for maximum shelf life.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        try {
            const res = await Api.get('/pages/faq');
            if (res.success && res.data) {
                document.getElementById('cms-page-title').innerHTML = res.data.title;
                document.getElementById('cms-page-content').innerHTML = res.data.content;
            }
        } catch (e) {}
    });
</script>

<?php
include_once __DIR__ . '/includes/footer.php';
?>