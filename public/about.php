<?php
require_once __DIR__ . '/includes/config.php';
include_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 my-8 sm:my-14 space-y-8">
    <!-- Page Header Banner -->
    <div class="bg-gradient-to-r from-[#990024] via-[#7a001c] to-[#4a0011] p-8 sm:p-12 rounded-3xl text-[#fffdf7] shadow-xl border border-[#f59e0b]/30 text-center space-y-3 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'60\' height=\'60\' viewBox=\'0 0 60 60\'%3E%3Ccircle cx=\'30\' cy=\'30\' r=\'12\' fill=\'none\' stroke=\'%23f59e0b\' stroke-width=\'1\'/%3E%3C/svg%3E');"></div>
        <span class="bg-[#f59e0b] text-[#12090c] font-black uppercase text-[10px] px-3.5 py-1 rounded-full tracking-widest inline-block shadow-md">
            🪔 TRISHA UTSAV ENTERPRISES
        </span>
        <h1 id="cms-page-title" class="font-display text-3xl sm:text-4xl font-extrabold text-[#fffdf7]">
            About <span class="italic text-[#f59e0b] font-normal">Us</span>
        </h1>
        <p class="text-xs sm:text-sm text-slate-200 font-medium max-w-xl mx-auto">
            Bringing joy, warmth, and vibrant celebration to every home across India.
        </p>
    </div>

    <!-- Main Content Body -->
    <div class="bg-white p-6 sm:p-12 rounded-3xl border border-[#f59e0b]/20 shadow-sm space-y-8">
        <div id="cms-page-content" class="text-xs sm:text-sm text-slate-700 leading-relaxed space-y-6 font-medium">
            
            <div class="bg-amber-50/60 border-l-4 border-[#f59e0b] p-5 rounded-r-2xl">
                <h2 class="text-base sm:text-lg font-black text-[#12090c] mb-1">Welcome to Trisha Utsav Enterprises</h2>
                <p class="text-xs sm:text-sm text-slate-600">
                    We are an India-based online store dedicated to bringing joy and celebration to every home through a carefully selected range of quality products.
                </p>
            </div>

            <!-- Product Collection Showcase Grid -->
            <div class="space-y-4">
                <h3 class="text-sm font-black text-[#990024] uppercase tracking-wider flex items-center">
                    <i class="fas fa-award text-[#f59e0b] mr-2"></i> Our Curated Collection Includes:
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div class="bg-slate-50 border border-slate-100 p-3.5 rounded-xl flex items-center space-x-3 hover:border-[#f59e0b]/40 transition shadow-xs">
                        <div class="w-8 h-8 rounded-lg bg-[#990024]/10 text-[#990024] flex items-center justify-center flex-shrink-0 font-bold">
                            <i class="fas fa-robot text-xs"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-800">Kids Toys</span>
                    </div>

                    <div class="bg-slate-50 border border-slate-100 p-3.5 rounded-xl flex items-center space-x-3 hover:border-[#f59e0b]/40 transition shadow-xs">
                        <div class="w-8 h-8 rounded-lg bg-[#990024]/10 text-[#990024] flex items-center justify-center flex-shrink-0 font-bold">
                            <i class="fas fa-brain text-xs"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-800">Educational Toys</span>
                    </div>

                    <div class="bg-slate-50 border border-slate-100 p-3.5 rounded-xl flex items-center space-x-3 hover:border-[#f59e0b]/40 transition shadow-xs">
                        <div class="w-8 h-8 rounded-lg bg-[#990024]/10 text-[#990024] flex items-center justify-center flex-shrink-0 font-bold">
                            <i class="fas fa-diya text-xs">🪔</i>
                        </div>
                        <span class="text-xs font-bold text-slate-800">Festival Decoration Items</span>
                    </div>

                    <div class="bg-slate-50 border border-slate-100 p-3.5 rounded-xl flex items-center space-x-3 hover:border-[#f59e0b]/40 transition shadow-xs">
                        <div class="w-8 h-8 rounded-lg bg-[#990024]/10 text-[#990024] flex items-center justify-center flex-shrink-0 font-bold">
                            <i class="fas fa-birthday-cake text-xs"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-800">Birthday Decorations</span>
                    </div>

                    <div class="bg-slate-50 border border-slate-100 p-3.5 rounded-xl flex items-center space-x-3 hover:border-[#f59e0b]/40 transition shadow-xs">
                        <div class="w-8 h-8 rounded-lg bg-[#990024]/10 text-[#990024] flex items-center justify-center flex-shrink-0 font-bold">
                            <i class="fas fa-rings-wedding text-xs">💒</i>
                        </div>
                        <span class="text-xs font-bold text-slate-800">Wedding Decorations</span>
                    </div>

                    <div class="bg-slate-50 border border-slate-100 p-3.5 rounded-xl flex items-center space-x-3 hover:border-[#f59e0b]/40 transition shadow-xs">
                        <div class="w-8 h-8 rounded-lg bg-[#990024]/10 text-[#990024] flex items-center justify-center flex-shrink-0 font-bold">
                            <i class="fas fa-baby text-xs"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-800">Baby Shower Decorations</span>
                    </div>

                    <div class="bg-slate-50 border border-slate-100 p-3.5 rounded-xl flex items-center space-x-3 hover:border-[#f59e0b]/40 transition shadow-xs">
                        <div class="w-8 h-8 rounded-lg bg-[#990024]/10 text-[#990024] flex items-center justify-center flex-shrink-0 font-bold">
                            <i class="fas fa-holly-berry text-xs">🎉</i>
                        </div>
                        <span class="text-xs font-bold text-slate-800">Party Supplies</span>
                    </div>

                    <div class="bg-slate-50 border border-slate-100 p-3.5 rounded-xl flex items-center space-x-3 hover:border-[#f59e0b]/40 transition shadow-xs">
                        <div class="w-8 h-8 rounded-lg bg-[#990024]/10 text-[#990024] flex items-center justify-center flex-shrink-0 font-bold">
                            <i class="fas fa-[#f59e0b] fa-calendar-star text-xs">✨</i>
                        </div>
                        <span class="text-xs font-bold text-slate-800">Seasonal Celebration Products</span>
                    </div>

                    <div class="bg-slate-50 border border-slate-100 p-3.5 rounded-xl flex items-center space-x-3 hover:border-[#f59e0b]/40 transition shadow-xs">
                        <div class="w-8 h-8 rounded-lg bg-[#990024]/10 text-[#990024] flex items-center justify-center flex-shrink-0 font-bold">
                            <i class="fas fa-home text-xs"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-800">Home & Event Accessories</span>
                    </div>
                </div>
            </div>

            <!-- Mission Statement -->
            <div class="bg-gradient-to-r from-slate-900 to-[#12090c] p-6 rounded-2xl text-white space-y-2 border border-[#f59e0b]/20">
                <h3 class="text-xs font-black text-[#f59e0b] uppercase tracking-widest flex items-center">
                    <i class="fas fa-bullseye mr-2"></i> Our Mission
                </h3>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    Our mission is to make every festival, celebration, and special occasion more memorable by offering quality products at competitive prices with reliable service.
                </p>
            </div>

            <!-- Core Commitments -->
            <div class="space-y-3">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Our Core Commitments</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                        <i class="fas fa-smile-beam text-lg text-[#990024]"></i>
                        <h4 class="text-xs font-bold text-slate-800">Customer Satisfaction</h4>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                        <i class="fas fa-award text-lg text-[#990024]"></i>
                        <h4 class="text-xs font-bold text-slate-800">Product Quality</h4>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                        <i class="fas fa-lock text-lg text-[#990024]"></i>
                        <h4 class="text-xs font-bold text-slate-800">Secure Shopping</h4>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                        <i class="fas fa-shipping-fast text-lg text-[#990024]"></i>
                        <h4 class="text-xs font-bold text-slate-800">Timely Delivery</h4>
                    </div>
                </div>
            </div>

            <!-- Closing Note -->
            <div class="pt-4 border-t border-slate-100 text-center space-y-1">
                <p class="text-xs sm:text-sm font-bold text-[#12090c]">
                    Thank you for choosing Trisha Utsav Enterprises.
                </p>
                <p class="text-xs text-slate-500 font-medium">
                    We look forward to being a part of your celebrations.
                </p>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        try {
            const res = await Api.get('/pages/about');
            if (res.success && res.data && res.data.content) {
                document.getElementById('cms-page-title').innerHTML = res.data.title;
                document.getElementById('cms-page-content').innerHTML = res.data.content;
            }
        } catch (e) {}
    });
</script>

<?php
include_once __DIR__ . '/includes/footer.php';
?>