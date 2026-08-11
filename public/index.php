<?php
$hide_standard_header = true;
include_once __DIR__ . '/includes/header.php';
?>

<!-- ─── SECTION 3: Royal Landing Hero Section (App-Like Mobile Experience) ─── -->
<div class="hidden md:block pt-3 sm:pt-6 pb-6 sm:pb-8 px-4 md:px-[50px] w-full">
    <div id="hero-card-container" class="relative w-full min-h-[460px] sm:h-[580px] lg:h-[620px] overflow-hidden bg-[#12090c] rounded-3xl sm:rounded-[2.5rem] shadow-2xl flex items-end md:items-center border border-[#f59e0b]/30">
        
        <!-- Autoplay looping muted background video -->
        <video id="hero-video" loop muted autoplay playsinline class="absolute inset-0 w-full h-full object-cover z-0 pointer-events-none opacity-70">
            <!-- Loaded dynamically -->
        </video>

        <!-- Background image element for image mode -->
        <img id="hero-image" class="absolute inset-0 w-full h-full object-cover z-0 hidden transition-opacity duration-700 pointer-events-none opacity-75" alt="Festive Background">

        <!-- Shimmer loading state -->
        <div id="hero-shimmer" class="absolute inset-0 bg-[#12090c] flex items-center justify-center transition-opacity duration-700 z-10">
            <div class="flex flex-col items-center space-y-4">
                <div class="w-10 h-10 border-4 border-[#f59e0b] border-t-transparent rounded-full animate-spin"></div>
                <span class="text-[#f59e0b] text-xs tracking-widest uppercase font-bold">Preparing Festive Light...</span>
            </div>
        </div>

        <!-- Dynamic Overlay Gradient Tint (Bottom-to-Top on Mobile for clear photo visibility, Left-to-Right on Desktop) -->
        <div id="hero-overlay" class="absolute inset-0 bg-gradient-to-t from-[#12090c] via-[#12090c]/80 to-black/30 md:bg-gradient-to-r md:from-[#12090c] md:via-[#12090c]/85 md:to-transparent z-10 pointer-events-none"></div>

        <!-- Slow-Spinning Dashed Rangoli SVG Ring (Bottom-Right Decorative Accent) -->
        <div class="absolute -right-20 -bottom-20 z-10 pointer-events-none opacity-25 text-[#f59e0b] hidden md:block">
            <svg class="w-[460px] h-[460px] animate-spin-slow" viewBox="0 0 200 200" fill="none" stroke="currentColor">
                <circle cx="100" cy="100" r="90" stroke-width="1.5" stroke-dasharray="6,6" />
                <circle cx="100" cy="100" r="75" stroke-width="1" stroke-dasharray="4,4" />
                <circle cx="100" cy="100" r="60" stroke-width="1.5" />
                <path d="M100 10 L100 190 M10 100 L190 100" stroke-width="0.75" stroke-dasharray="3,3" />
                <polygon points="100,20 120,60 160,60 130,85 145,125 100,100 55,125 70,85 40,60 80,60" stroke-width="0.75" />
            </svg>
        </div>

        <!-- Matter.js Interactive Canvas Container -->
        <div id="hangings-container" class="absolute inset-0 z-[15] pointer-events-none"></div>

        <!-- Hero Content Box -->
        <div class="relative w-full mx-auto px-4 sm:px-12 lg:px-16 z-20 text-[#fffdf7] flex flex-col justify-center h-full py-8 sm:py-12">
            <div class="max-w-2xl space-y-4 sm:space-y-5 text-center md:text-left flex flex-col items-center md:items-start">
                
                <!-- Gold Pill Badge -->
                <span class="animate-fade-in-up bg-[#990024]/40 backdrop-blur-md text-[#f59e0b] text-[10px] sm:text-xs font-bold px-3.5 sm:px-4 py-1 sm:py-1.5 rounded-full uppercase tracking-widest inline-flex items-center border border-[#f59e0b]/40 shadow-md">
                    <i class="fas fa-om mr-1.5 text-[#f59e0b]"></i> 🪔 HAPPY RAKSHABANDHAN 2026
                </span>
                
                <!-- Fraunces Display Headline -->
                <h1 id="hero-headline" class="font-display animate-fade-in-up delay-200 text-3xl sm:text-5xl lg:text-7xl font-bold tracking-tight leading-[1.1] text-[#fffdf7] drop-shadow-lg">
                    Light up every <span class="italic text-[#f59e0b] font-normal">celebration</span>
                </h1>

                <!-- Gold-to-Vermilion Rangoli Line Divider -->
                <div class="animate-fade-in-up delay-200 w-20 sm:w-28 h-[3px] bg-gradient-to-r from-[#f59e0b] via-[#ea580c] to-[#990024] rounded-full my-0.5 sm:my-1 shadow-md"></div>
                
                <!-- Description Paragraph -->
                <p id="hero-description" class="animate-fade-in-up delay-400 text-sm sm:text-base md:text-lg text-white/95 leading-relaxed font-medium max-w-lg drop-shadow-[0_2px_4px_rgba(0,0,0,0.8)]">
                    Handcrafted sweets, artisanal diyas, royal ethnic creations, and pure celebration attributes curated for your festive home across India.
                </p>

                <!-- CTA Buttons -->
                <div class="animate-fade-in-up delay-600 pt-1 sm:pt-2 flex flex-wrap items-center justify-center md:justify-start gap-3 sm:gap-4 w-full">
                    <a id="hero-cta-btn" href="shop.php" class="bg-[#fffdf7] hover:bg-[#fde047] text-[#990024] font-extrabold text-[11px] sm:text-xs uppercase tracking-widest py-3.5 sm:py-4 px-7 sm:px-9 rounded-full shadow-2xl hover:scale-105 transition duration-300 border border-[#f59e0b]/30 flex items-center justify-center space-x-2 w-full sm:w-auto">
                        <span>SHOP FESTIVITIES</span>
                        <i class="fas fa-arrow-right text-xs ml-1"></i>
                    </a>
                    <a href="#sec-reels" class="bg-white/10 hover:bg-white/20 text-[#fffdf7] border border-white/20 font-bold text-[11px] sm:text-xs uppercase tracking-widest py-3.5 sm:py-4 px-6 sm:px-7 rounded-full backdrop-blur-md transition duration-300 flex items-center justify-center space-x-2 w-full sm:w-auto">
                        <i class="fas fa-play text-[10px] text-[#f59e0b]"></i>
                        <span>WATCH REELS</span>
                    </a>
                </div>

                <!-- Stat Row -->
                <div class="pt-3 sm:pt-4 grid grid-cols-3 gap-2 sm:gap-6 text-center sm:text-left border-t border-white/10 w-full max-w-lg">
                    <div>
                        <span class="block font-display text-base sm:text-xl font-bold text-[#f59e0b]">2L+</span>
                        <span class="text-[9px] sm:text-[10px] text-slate-300 uppercase tracking-wider font-semibold">Happy Families</span>
                    </div>
                    <div>
                        <span class="block font-display text-base sm:text-xl font-bold text-[#f59e0b]">28</span>
                        <span class="text-[9px] sm:text-[10px] text-slate-300 uppercase tracking-wider font-semibold">States Served</span>
                    </div>
                    <div>
                        <span class="block font-display text-base sm:text-xl font-bold text-[#f59e0b]">4.9 ★</span>
                        <span class="text-[9px] sm:text-[10px] text-slate-300 uppercase tracking-wider font-semibold">Rating</span>
                    </div>
                </div>

            </div>
        </div>

        <!-- Floating Glass Bestseller Product Card (Bottom-Right Dynamic Feature) -->
        <a href="shop.php" id="hero-bestseller-card" class="hidden lg:flex absolute bottom-8 right-12 z-30 bg-[#12090c]/85 backdrop-blur-xl border border-[#f59e0b]/40 p-4 rounded-3xl shadow-2xl items-center space-x-4 max-w-xs hover:scale-105 transition duration-300 group cursor-pointer">
            <div class="w-16 h-16 rounded-2xl bg-[#990024] p-1 flex-shrink-0 relative overflow-hidden border border-[#f59e0b]/30">
                <img src="https://images.unsplash.com/photo-1605888967806-444427501ff2?w=200&auto=format&fit=crop&q=80" alt="Royal Brass Diya" loading="lazy" decoding="async" class="w-full h-full object-cover rounded-xl group-hover:scale-110 transition duration-500">
                <span class="absolute top-1 left-1 bg-[#f59e0b] text-[#12090c] text-[8px] font-black px-1.5 rounded-full shadow-sm">MUST BUY</span>
            </div>
            <div>
                <span class="text-[10px] font-bold text-[#f59e0b] uppercase tracking-wider block">Handcrafted Bestseller</span>
                <h4 class="text-xs font-extrabold text-white line-clamp-1 group-hover:text-[#f59e0b] transition">Royal Brass Lotus Diya</h4>
                <div class="flex items-center space-x-2 mt-1">
                    <span class="text-sm font-black text-white">₹1,299</span>
                    <span class="text-[10px] text-slate-400 line-through">₹2,499</span>
                </div>
            </div>
        </a>

        <!-- Play/Pause Discreet Control -->
        <button id="btn-video-control" onclick="Homepage.toggleVideo()" class="absolute bottom-6 right-6 z-30 w-9 h-9 rounded-full border border-white/20 bg-white/10 hover:bg-white/20 text-white flex items-center justify-center backdrop-blur text-xs focus:outline-none transition shadow-lg lg:hidden" title="Play/Pause Video">
            <i class="fas fa-pause"></i>
        </button>
    </div>
</div>


<!-- ─── SECTION 4: Horizontal Marquee Ribbon ─── -->
<div class="bg-gradient-to-r from-[#990024] via-[#7a001c] to-[#4a0011] text-[#fffdf7] font-bold text-[10px] sm:text-xs uppercase tracking-widest py-2.5 sm:py-3 border-y border-[#f59e0b]/30 shadow-md relative overflow-hidden my-3 sm:my-4">
    <div class="flex whitespace-nowrap animate-marquee">
        <span class="mx-4 sm:mx-6 flex items-center"><i class="fas fa-om text-[#f59e0b] mr-1.5"></i> शुभ दीपावली · HAPPY RAKSHABANDHAN</span>
        <span class="mx-4 sm:mx-6 flex items-center"><i class="fas fa-crown text-[#f59e0b] mr-1.5"></i> RAKHI SPECIAL COLLECTIONS</span>
        <span class="mx-4 sm:mx-6 flex items-center"><i class="fas fa-crown text-[#f59e0b] mr-1.5"></i> ROYAL WEDDING SEASON</span>
        <span class="mx-4 sm:mx-6 flex items-center"><i class="fas fa-box-open mr-1.5 text-[#f59e0b]"></i> FREE ELEGANT PACKAGING</span>
        <span class="mx-4 sm:mx-6 flex items-center"><i class="fas fa-flag-usa mr-1.5 text-[#f59e0b]"></i> 100% HANDMADE IN INDIA</span>
        <span class="mx-4 sm:mx-6 flex items-center"><i class="fas fa-truck-fast text-[#f59e0b] mr-1.5"></i> PAN-INDIA EXPRESS DELIVERY</span>
        <!-- Duplicate for continuous scroll -->
        <span class="mx-4 sm:mx-6 flex items-center"><i class="fas fa-om text-[#f59e0b] mr-1.5"></i> शुभ दीपावली · HAPPY RAKSHABANDHAN</span>
        <span class="mx-4 sm:mx-6 flex items-center"><i class="fas fa-crown text-[#f59e0b] mr-1.5"></i> RAKHI SPECIAL COLLECTIONS</span>
        <span class="mx-4 sm:mx-6 flex items-center"><i class="fas fa-crown text-[#f59e0b] mr-1.5"></i> ROYAL WEDDING SEASON</span>
        <span class="mx-4 sm:mx-6 flex items-center"><i class="fas fa-box-open mr-1.5 text-[#f59e0b]"></i> FREE ELEGANT PACKAGING</span>
        <span class="mx-4 sm:mx-6 flex items-center"><i class="fas fa-flag-usa mr-1.5 text-[#f59e0b]"></i> 100% HANDMADE IN INDIA</span>
        <span class="mx-4 sm:mx-6 flex items-center"><i class="fas fa-truck-fast text-[#f59e0b] mr-1.5"></i> PAN-INDIA EXPRESS DELIVERY</span>
    </div>
</div>


<!-- Dynamic Homepage Main Content Wrapper -->
<div id="dynamic-sections-container" class="max-w-[1800px] mx-auto px-4 md:px-[50px] mt-8 sm:mt-12 pb-20 sm:pb-24 space-y-16 sm:space-y-24">
    <div class="text-center py-16">
        <div class="w-10 h-10 sm:w-12 sm:h-12 border-4 border-[#990024] border-t-transparent rounded-full animate-spin mx-auto"></div>
        <span class="text-xs font-bold text-gray-500 uppercase tracking-widest mt-4 block">Hydrating Royal Collections...</span>
    </div>
</div>


<script>
    const Homepage = {
        videoPlaying: true,
        sectionsOrder: [],

        async init() {
            this.initMouseWheelScroll();
            await this.loadHeroConfig();
            await this.loadSectionsOrder();
        },

        initMouseWheelScroll() {
            document.addEventListener('wheel', (e) => {
                const target = e.target.closest('.scroll-horizontal-mouse');
                if (target && e.deltaY !== 0) {
                    e.preventDefault();
                    target.scrollLeft += e.deltaY * 1.5;
                }
            }, { passive: false });
        },

        async loadHeroConfig() {
            try {
                const res = await Api.get('/settings');
                if (res.success && res.data) {
                    const d = res.data;
                    this.siteSettings = d;
                    this.initHangings(d);
                    
                    const isMobile = window.innerWidth < 768;
                    const heroContainer = document.getElementById('hero-card-container');
                    const mobileBgType = d.hero_mobile_bg_type || 'desktop';

                    if (isMobile && mobileBgType === 'hidden') {
                        const heroWrapper = heroContainer ? heroContainer.parentElement : null;
                        if (heroWrapper) heroWrapper.classList.add('hidden');
                        return;
                    }

                    if (isMobile && heroContainer && d.hero_mobile_height) {
                        heroContainer.classList.remove('h-[540px]', 'sm:h-[580px]', 'lg:h-[620px]');
                        if (d.hero_mobile_height === 'compact') heroContainer.classList.add('min-h-[420px]');
                        else if (d.hero_mobile_height === 'full') heroContainer.classList.add('min-h-[82vh]');
                        else heroContainer.classList.add('min-h-[460px]');
                    }

                    let effectiveBgType = d.hero_bg_type || 'image';
                    let effectiveImageUrl = d.hero_image_url || 'https://images.unsplash.com/photo-1605888967806-444427501ff2?w=1600&auto=format&fit=crop&q=80';
                    let effectiveVideoUrl = d.hero_video_url || '';

                    if (isMobile && mobileBgType !== 'desktop') {
                        effectiveBgType = mobileBgType;
                        if (mobileBgType === 'image' && d.hero_mobile_image_url) effectiveImageUrl = d.hero_mobile_image_url;
                        if (mobileBgType === 'video' && d.hero_mobile_video_url) effectiveVideoUrl = d.hero_mobile_video_url;
                    }

                    const video = document.getElementById('hero-video');
                    const image = document.getElementById('hero-image');
                    const shimmer = document.getElementById('hero-shimmer');
                    const videoCtrl = document.getElementById('btn-video-control');

                    if (effectiveBgType === 'image' && effectiveImageUrl) {
                        if (video) video.classList.add('hidden');
                        if (videoCtrl) videoCtrl.classList.add('hidden');
                        if (image) {
                            image.src = effectiveImageUrl.startsWith('http') ? effectiveImageUrl : (BASE_URL + effectiveImageUrl);
                            image.classList.remove('hidden');
                            image.onload = () => {
                                shimmer.classList.add('opacity-0');
                                setTimeout(() => shimmer.classList.add('hidden'), 700);
                            };
                            if (image.complete) {
                                shimmer.classList.add('opacity-0');
                                setTimeout(() => shimmer.classList.add('hidden'), 700);
                            }
                        }
                    } else if (video && effectiveVideoUrl) {
                        if (image) image.classList.add('hidden');
                        if (videoCtrl) videoCtrl.classList.remove('hidden');
                        video.classList.remove('hidden');
                        video.src = effectiveVideoUrl.startsWith('http') ? effectiveVideoUrl : (BASE_URL + effectiveVideoUrl);
                        video.load();
                        video.onloadeddata = () => {
                            shimmer.classList.add('opacity-0');
                            setTimeout(() => shimmer.classList.add('hidden'), 700);
                        };
                    } else if (shimmer) {
                        shimmer.classList.add('opacity-0');
                        setTimeout(() => shimmer.classList.add('hidden'), 700);
                    }

                    if (d.hero_headline) {
                        document.getElementById('hero-headline').innerHTML = d.hero_headline.includes('<span') ? d.hero_headline : `${d.hero_headline.replace(/(\w+)$/, '<span class="italic text-[#f59e0b] font-normal">$1</span>')}`;
                    }
                    if (d.hero_description) {
                        document.getElementById('hero-description').innerText = d.hero_description;
                    }
                    
                    const cta = document.getElementById('hero-cta-btn');
                    if (cta) {
                        if (d.hero_cta_text) cta.innerHTML = `<span>${d.hero_cta_text.toUpperCase()}</span><i class="fas fa-arrow-right text-xs ml-1"></i>`;
                        if (d.hero_cta_link) cta.href = d.hero_cta_link;
                    }

                    // Hydrate dynamic Hero Floating Bestseller Card
                    await this.loadHeroBestseller(d.hero_bestseller_product_id || null);
                }
            } catch (e) {
                const shimmer = document.getElementById('hero-shimmer');
                if (shimmer) {
                    shimmer.classList.add('opacity-0');
                    setTimeout(() => shimmer.classList.add('hidden'), 700);
                }
                await this.loadHeroBestseller(null);
            }
        },

        async loadHeroBestseller(productId) {
            const card = document.getElementById('hero-bestseller-card');
            if (!card) return;

            try {
                let prod = null;
                if (productId) {
                    const res = await Api.get('/products/' + productId);
                    if (res.success && res.data) {
                        prod = res.data;
                    }
                }
                
                if (!prod) {
                    const resAll = await Api.get('/products?per_page=1&is_must_buy=1');
                    if (resAll.success && resAll.data && resAll.data.length > 0) {
                        prod = resAll.data[0];
                    } else {
                        const resFallback = await Api.get('/products?per_page=1');
                        if (resFallback.success && resFallback.data && resFallback.data.length > 0) {
                            prod = resFallback.data[0];
                        }
                    }
                }

                if (prod) {
                    const price = parseFloat(prod.price);
                    const mrp = prod.mrp ? parseFloat(prod.mrp) : (price * 1.3);
                    const rawImg = prod.primary_image || (prod.images && prod.images.length > 0 ? prod.images[0].image_url : null);
                    const fixFn = (window.Utils && typeof window.Utils.fixImageUrl === 'function') 
                        ? window.Utils.fixImageUrl 
                        : (p => (BASE_URL + p.replace(/^\/+/, '')));
                    const imgUrl = rawImg ? fixFn(rawImg) : `${BASE_URL}assets/images/product_placeholder.jpg`;

                    card.href = `${BASE_URL}product.php?slug=${prod.slug}`;
                    card.innerHTML = `
                        <div class="w-16 h-16 rounded-2xl bg-[#990024] p-1 flex-shrink-0 relative overflow-hidden border border-[#f59e0b]/30">
                            <img src="${imgUrl}" alt="${prod.name}" onerror="this.onerror=null;this.src='${BASE_URL}assets/images/product_placeholder.jpg';" loading="lazy" decoding="async" class="w-full h-full object-cover rounded-xl group-hover:scale-110 transition duration-500">
                            <span class="absolute top-1 left-1 bg-[#f59e0b] text-[#12090c] text-[8px] font-black px-1.5 rounded-full shadow-sm">MUST BUY</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-[#f59e0b] uppercase tracking-wider block">Handcrafted Bestseller</span>
                            <h4 class="text-xs font-extrabold text-white line-clamp-1 group-hover:text-[#f59e0b] transition">${prod.name}</h4>
                            <div class="flex items-center space-x-2 mt-1">
                                <span class="text-sm font-black text-white">${Utils.formatCurrency(price)}</span>
                                <span class="text-[10px] text-slate-400 line-through">${Utils.formatCurrency(mrp)}</span>
                            </div>
                        </div>
                    `;
                }
            } catch(e) {}
        },

        async loadSectionsOrder() {
            const container = document.getElementById('dynamic-sections-container');
            try {
                const res = await Api.get('/homepage/sections');
                if (res.success && res.data) {
                    this.sectionsOrder = res.data.filter(s => s.enabled !== false);
                    container.innerHTML = '';

                    for (const sec of this.sectionsOrder) {
                        const secNode = document.createElement('div');
                        secNode.id = `sec-${sec.id}`;
                        secNode.className = 'space-y-4 sm:space-y-6';
                        container.appendChild(secNode);

                        if (sec.id === 'occasions') await this.renderOccasions(secNode);
                        else if (sec.id === 'categories') await this.renderCategories(secNode);
                        else if (sec.id === 'trending') await this.renderTrending(secNode);
                        else if (sec.id === 'must_buy') await this.renderMustBuy(secNode);
                        else if (sec.id === 'mega_sale') await this.renderMegaSale(secNode);
                    }

                    if (!this.sectionsOrder.some(s => s.id === 'mega_sale')) {
                        await this.renderMegaSale(container);
                    }
                    this.renderUSPBar(container);
                    this.renderNewsletter(container);
                }
            } catch(e) {
                container.innerHTML = `<div class="text-center py-8 text-red-500 text-xs font-bold">Failed to load homepage layout.</div>`;
            }
        },

        // ─── SECTION 5: Shop By Occasion (3 Tall Cards) ───
        async renderOccasions(container) {
            container.innerHTML = `
                <div class="flex justify-between items-end border-b border-[#f59e0b]/20 pb-3 sm:pb-4">
                    <div>
                        <span class="text-[9px] sm:text-[10px] font-black text-[#990024] uppercase tracking-widest block mb-0.5 sm:mb-1">
                            <i class="fas fa-calendar-alt text-[#f59e0b] mr-1"></i> Festive Celebration Collections
                        </span>
                        <h2 class="font-display text-xl sm:text-3xl font-extrabold text-[#12090c] tracking-tight">
                            Shop by <span class="italic text-[#f59e0b] font-normal">occasion</span>
                        </h2>
                    </div>
                    <a href="${BASE_URL}occasions.php" class="text-[11px] sm:text-xs text-[#990024] font-extrabold hover:underline flex items-center uppercase tracking-wider">
                        View All <i class="fas fa-arrow-right ml-1 text-[9px]"></i>
                    </a>
                </div>

                <div id="occasions-cards-strip" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-6 pt-2">
                    <div class="col-span-full text-center py-8">
                        <div class="w-8 h-8 border-4 border-[#990024] border-t-transparent rounded-full animate-spin mx-auto"></div>
                    </div>
                </div>
            `;

            const strip = container.querySelector('#occasions-cards-strip');
            try {
                const res = await Api.get('/occasions');
                if (res.success && res.data && res.data.length > 0) {
                    let html = '';
                    res.data.slice(0, 5).forEach((occ, idx) => {
                        const bgImg = occ.image_url ? (BASE_URL + occ.image_url) : 'https://images.unsplash.com/photo-1605888967806-444427501ff2?w=800&auto=format&fit=crop&q=80';
                        const numStr = `#0${idx + 1}`;

                        html += `
                            <a href="${BASE_URL}occasions.php?slug=${occ.slug}" class="group relative rounded-3xl sm:rounded-[2rem] overflow-hidden shadow-xl border border-[#f59e0b]/30 h-[300px] sm:h-[380px] flex flex-col justify-between p-5 sm:p-6 transform hover:-translate-y-2 transition duration-300">
                                <img src="${bgImg}" alt="${occ.name}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition duration-700">
                                <div class="absolute inset-0 bg-gradient-to-t from-[#12090c] via-[#12090c]/40 to-transparent"></div>

                                <div class="z-10 relative flex justify-between items-center">
                                    <span class="bg-[#12090c]/80 backdrop-blur-md text-[#f59e0b] border border-[#f59e0b]/30 font-display text-xs font-black px-3 py-1 rounded-full">
                                        ${numStr}
                                    </span>
                                    <span class="bg-[#990024]/80 backdrop-blur-md text-[#fffdf7] text-[9px] font-extrabold px-2.5 py-0.5 sm:py-1 rounded-full uppercase tracking-wider border border-[#f59e0b]/30">
                                        Festive Special
                                    </span>
                                </div>

                                <div class="z-10 relative space-y-1.5 sm:space-y-2">
                                    <span class="text-[9px] sm:text-[10px] font-black text-[#f59e0b] uppercase tracking-widest block">
                                        Handcrafted Curation
                                    </span>
                                    <h3 class="font-display text-xl sm:text-2xl font-bold text-[#fffdf7] group-hover:text-[#fde047] transition">
                                        ${occ.name}
                                    </h3>
                                    <div class="flex justify-between items-center pt-1.5 sm:pt-2">
                                        <span class="text-[11px] sm:text-xs font-bold text-slate-300 uppercase tracking-wider">Explore Collection</span>
                                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-[#f59e0b] text-[#12090c] flex items-center justify-center font-black group-hover:scale-110 transition shadow-lg">
                                            <i class="fas fa-arrow-right text-xs"></i>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        `;
                    });
                    strip.innerHTML = html;
                }
            } catch(e) {
                strip.innerHTML = `<span class="text-xs text-red-500 py-4 col-span-full text-center">Failed to load occasions.</span>`;
            }
        },

        // ─── SECTION 6: Categories Grid ───
        async renderCategories(container) {
            container.innerHTML = `
                <div class="flex justify-between items-end border-b border-[#f59e0b]/20 pb-3 sm:pb-4">
                    <div>
                        <span class="text-[9px] sm:text-[10px] font-black text-[#990024] uppercase tracking-widest block mb-0.5 sm:mb-1">
                            <i class="fas fa-layer-group text-[#f59e0b] mr-1"></i> Traditional Collections
                        </span>
                        <h2 class="font-display text-xl sm:text-3xl font-extrabold text-[#12090c] tracking-tight">
                            Explore festive <span class="italic text-[#f59e0b] font-normal">categories</span>
                        </h2>
                    </div>
                    <a href="${BASE_URL}categories.php" class="text-[11px] sm:text-xs text-[#990024] font-extrabold hover:underline flex items-center uppercase tracking-wider">
                        Browse All <i class="fas fa-arrow-right ml-1 text-[9px]"></i>
                    </a>
                </div>

                <div id="categories-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6 pt-2">
                    <div class="col-span-full text-center py-8">
                        <div class="w-8 h-8 border-4 border-[#990024] border-t-transparent rounded-full animate-spin mx-auto"></div>
                    </div>
                </div>
            `;

            const grid = container.querySelector('#categories-grid');
            const fallbackImgs = [
                'https://images.unsplash.com/photo-1605888967806-444427501ff2?w=500&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1545232979-fbfd42e000b9?w=500&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=500&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1513151233558-d860c5398176?w=500&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?w=500&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1607344645866-009c320c5ab8?w=500&auto=format&fit=crop&q=80',
            ];

            try {
                const res = await Api.get('/categories');
                let html = '';

                if (res.success && res.data && res.data.length > 0) {
                    res.data.slice(0, 12).forEach((cat, idx) => {
                        const imagePath = cat.image || cat.image_url;
                        const fixFn = (window.Utils && typeof window.Utils.fixImageUrl === 'function') 
                            ? window.Utils.fixImageUrl 
                            : (p => (BASE_URL + p.replace(/^\/+/, '')));
                        const imgUrl = imagePath ? fixFn(imagePath) : fallbackImgs[idx % fallbackImgs.length];

                        html += `
                            <a href="${BASE_URL}categories.php?slug=${cat.slug}" class="relative rounded-3xl overflow-hidden shadow-xl h-[260px] sm:h-[320px] bg-[#12090c] border border-[#f59e0b]/30 group cursor-pointer transform hover:-translate-y-2 transition duration-300 flex flex-col justify-between p-5 sm:p-6">
                                <!-- Full Background Image -->
                                <img src="${imgUrl}" alt="${cat.name}" loading="lazy" decoding="async" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition duration-700">
                                
                                <!-- Dark Royal Gradient Overlay -->
                                <div class="absolute inset-0 bg-gradient-to-t from-[#12090c] via-[#12090c]/40 to-transparent"></div>

                                <!-- Category Badge -->
                                <div class="z-10 relative flex justify-between items-center">
                                    <span class="bg-[#12090c]/80 backdrop-blur-md text-[#f59e0b] border border-[#f59e0b]/30 font-display text-xs font-black px-3 py-1 rounded-full">
                                        #0${idx + 1}
                                    </span>
                                    <span class="bg-[#990024]/85 backdrop-blur-md text-white text-[9px] sm:text-[10px] font-black px-2.5 py-1 rounded-full border border-white/30 shadow-md">
                                        🪔 Collection
                                    </span>
                                </div>

                                <!-- Content Overlay at Bottom -->
                                <div class="relative z-10 space-y-1.5 sm:space-y-2">
                                    <h4 class="font-display text-lg sm:text-xl font-bold text-[#fffdf7] group-hover:text-[#fde047] transition line-clamp-1">
                                        ${cat.name}
                                    </h4>
                                    ${cat.description ? `
                                        <p class="text-[11px] sm:text-xs text-slate-300 line-clamp-2 font-medium">
                                            ${cat.description}
                                        </p>
                                    ` : ''}
                                    <div class="flex items-center justify-between pt-2 border-t border-[#f59e0b]/20">
                                        <span class="text-[11px] sm:text-xs font-extrabold text-[#f59e0b] uppercase tracking-wider flex items-center">
                                            Explore Collection
                                        </span>
                                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-[#f59e0b] text-[#12090c] flex items-center justify-center font-black group-hover:scale-110 transition shadow-lg">
                                            <i class="fas fa-arrow-right text-xs"></i>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        `;
                    });
                } else {
                    const defaultCats = [
                        { name: 'Diyas & Lights', img: fallbackImgs[0] },
                        { name: 'Sweets & Mithai', img: fallbackImgs[1] },
                        { name: 'Rakhi Hampers', img: fallbackImgs[2] },
                        { name: 'Puja Essentials', img: fallbackImgs[3] },
                        { name: 'Ethnic Wear', img: fallbackImgs[4] },
                        { name: 'Fine Jewellery', img: fallbackImgs[5] },
                    ];
                    defaultCats.forEach((cat) => {
                        html += `
                            <a href="${BASE_URL}shop.php" class="relative rounded-2xl sm:rounded-3xl overflow-hidden shadow-lg aspect-[4/5] bg-[#12090c] border border-[#f59e0b]/30 group cursor-pointer transform hover:-translate-y-2 transition duration-300 flex flex-col justify-end p-4 sm:p-5">
                                <img src="${cat.img}" alt="${cat.name}" loading="lazy" decoding="async" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition duration-700">
                                <div class="absolute inset-0 bg-gradient-to-t from-[#12090c] via-[#12090c]/50 to-transparent"></div>
                                <span class="absolute top-2.5 right-2.5 bg-[#990024]/85 backdrop-blur-md text-[#f59e0b] text-[9px] sm:text-[10px] font-black px-2.5 py-1 rounded-full border border-[#f59e0b]/30 shadow-md">
                                    🪔 Collection
                                </span>
                                <div class="relative z-10 space-y-1">
                                    <h4 class="font-display text-sm sm:text-base font-extrabold text-[#fffdf7] group-hover:text-[#f59e0b] transition line-clamp-1">
                                        ${cat.name}
                                    </h4>
                                    <div class="flex items-center justify-between pt-1 border-t border-[#f59e0b]/20">
                                        <span class="text-[10px] sm:text-[11px] font-black text-[#f59e0b] uppercase tracking-wider flex items-center">
                                            Explore <i class="fas fa-arrow-right ml-1 text-[9px] group-hover:translate-x-1 transition duration-200"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        `;
                    });
                }
                grid.innerHTML = html;
            } catch(e) {
                grid.innerHTML = `<span class="text-xs text-red-500 py-4 col-span-full text-center">Failed to load categories.</span>`;
            }
        },

        // ─── SECTION 7: Trending Picks (Side-by-side 2 Cols on Mobile) ───
        async renderTrending(container) {
            container.innerHTML = `
                <div class="flex justify-between items-end border-b border-[#f59e0b]/20 pb-3 sm:pb-4">
                    <div>
                        <span class="text-[9px] sm:text-[10px] font-black text-[#ea580c] uppercase tracking-widest block mb-0.5 sm:mb-1">
                            <i class="fas fa-fire text-[#ea580c] mr-1"></i> Curated Handpicked Specials
                        </span>
                        <h2 class="font-display text-xl sm:text-3xl font-extrabold text-[#12090c] tracking-tight">
                            Hot right <span class="italic text-[#f59e0b] font-normal">now</span>
                        </h2>
                    </div>
                    <a href="${BASE_URL}shop.php?is_trending=1" class="text-[11px] sm:text-xs text-[#990024] font-extrabold hover:underline flex items-center uppercase tracking-wider">
                        View All <i class="fas fa-arrow-right ml-1 text-[9px]"></i>
                    </a>
                </div>

                <div id="trending-products-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-6 pt-2">
                    <div class="col-span-full text-center py-8">
                        <div class="w-8 h-8 border-4 border-[#990024] border-t-transparent rounded-full animate-spin mx-auto"></div>
                    </div>
                </div>
            `;

            const grid = container.querySelector('#trending-products-grid');
            try {
                let res = await Api.get('/products?is_trending=1&per_page=6');
                if (!res.success || !res.data || res.data.length === 0) {
                    res = await Api.get('/products?per_page=6&sort=newest');
                }

                if (res.success && res.data) {
                    const fixFn = (window.Utils && typeof window.Utils.fixImageUrl === 'function') 
                        ? window.Utils.fixImageUrl 
                        : (p => (BASE_URL + p.replace(/^\/+/, '')));

                    let html = '';
                    res.data.forEach(prod => {
                        const price = parseFloat(prod.price);
                        const mrp = prod.mrp ? parseFloat(prod.mrp) : (price * 1.3);
                        const discount = Math.round(((mrp - price) / mrp) * 100);
                        const rawImg1 = prod.primary_image || (prod.images && prod.images.length > 0 ? prod.images[0].image_url : null);
                        let rawImg2 = null;
                        if (prod.images && prod.images.length > 1) {
                            const second = prod.images.find(i => i.image_url !== rawImg1);
                            if (second) rawImg2 = second.image_url;
                        }
                        const imgUrl1 = rawImg1 ? fixFn(rawImg1) : `${BASE_URL}assets/images/product_placeholder.jpg`;
                        const imgUrl2 = rawImg2 ? fixFn(rawImg2) : null;

                        html += `
                            <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl overflow-hidden flex flex-col group transition duration-300 relative">
                                <a href="${BASE_URL}product.php?slug=${prod.slug}" class="relative bg-gray-50 aspect-square overflow-hidden block">
                                    <img src="${imgUrl1}" alt="${prod.name}" onerror="this.onerror=null;this.src='${BASE_URL}assets/images/product_placeholder.jpg';" loading="lazy" decoding="async" class="w-full h-full object-cover transition-all duration-700 ease-out transform group-hover:scale-110 ${imgUrl2 ? 'group-hover:opacity-0' : ''}">
                                    ${imgUrl2 ? `
                                        <img src="${imgUrl2}" alt="${prod.name}" onerror="this.onerror=null;this.src='${BASE_URL}assets/images/product_placeholder.jpg';" loading="lazy" decoding="async" class="absolute inset-0 w-full h-full object-cover opacity-0 scale-100 group-hover:opacity-100 group-hover:scale-110 transition-all duration-700 ease-out">
                                    ` : ''}
                                    
                                    <span class="absolute top-2 sm:top-3 left-2 sm:left-3 bg-[#990024] text-[#fffdf7] text-[8px] sm:text-[9px] font-black px-2 py-0.5 sm:py-1 rounded-full uppercase tracking-wider shadow-md flex items-center z-10">
                                        <i class="fas fa-fire mr-1 text-white"></i> HOT
                                    </span>
                                    ${discount > 0 ? `
                                        <span class="absolute top-2 sm:top-3 right-2 sm:right-3 bg-[#f59e0b] text-[#12090c] text-[8px] sm:text-[9px] font-black px-1.5 sm:px-2 py-0.5 rounded-full shadow-sm z-10">
                                            -${discount}%
                                        </span>
                                    ` : ''}
                                </a>

                                <div class="p-3 sm:p-5 flex flex-col flex-grow">
                                    <div class="flex items-center space-x-0.5 text-[#f59e0b] text-[9px] sm:text-[10px] mb-1">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                        <span class="text-gray-400 font-bold ml-1">(4.9)</span>
                                    </div>

                                    <h3 class="font-display text-xs sm:text-sm font-bold text-[#12090c] line-clamp-2 hover:text-[#990024] transition flex-grow">
                                        <a href="${BASE_URL}product.php?slug=${prod.slug}">${prod.name}</a>
                                    </h3>

                                    <div class="mt-2.5 pt-2 sm:pt-2.5 border-t border-gray-100 space-y-2">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <span class="text-xs sm:text-base font-black text-[#990024]">${Utils.formatCurrency(price)}</span>
                                                <span class="text-[9px] sm:text-[10px] text-gray-400 line-through block sm:inline sm:ml-1">${Utils.formatCurrency(mrp)}</span>
                                            </div>
                                            <button onclick="Shop.quickAdd(${prod.id})" ${parseInt(prod.stock_quantity) <= 0 ? 'disabled' : ''} class="w-8 h-8 sm:w-9 sm:h-9 rounded-full border border-[#990024] text-[#990024] hover:bg-[#990024] hover:text-white disabled:bg-gray-100 disabled:border-gray-200 disabled:text-gray-400 transition duration-200 flex items-center justify-center cursor-pointer shrink-0" title="Add to Cart">
                                                <i class="fas fa-shopping-bag text-xs"></i>
                                            </button>
                                        </div>
                                        <button onclick="Utils.buyNow(${prod.id}, 1, null, this)" ${parseInt(prod.stock_quantity) <= 0 ? 'disabled' : ''} class="w-full py-1.5 sm:py-2 px-3 rounded-xl bg-[#990024] hover:bg-[#7a001c] disabled:bg-gray-200 disabled:text-gray-400 text-white font-extrabold text-[10px] sm:text-xs shadow-2xs hover:shadow-xs transition duration-200 flex items-center justify-center space-x-1 cursor-pointer" title="Buy Now (Direct Checkout)">
                                            <i class="fas fa-bolt text-[9px] text-amber-300"></i>
                                            <span>Buy Now</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    grid.innerHTML = html;
                }
            } catch(e) {
                grid.innerHTML = `<span class="text-xs text-red-500 py-4 col-span-full text-center">Failed to load trending items.</span>`;
            }
        },

        // ─── SECTION 7B: Must Buy Selection (Side-by-side 2 Cols on Mobile) ───
        async renderMustBuy(container) {
            container.innerHTML = `
                <div class="flex justify-between items-end border-b border-[#f59e0b]/20 pb-3 sm:pb-4">
                    <div>
                        <span class="text-[9px] sm:text-[10px] font-black text-[#990024] uppercase tracking-widest block mb-0.5 sm:mb-1">
                            <i class="fas fa-crown text-[#f59e0b] mr-1"></i> Editor's Choice
                        </span>
                        <h2 class="font-display text-xl sm:text-3xl font-extrabold text-[#12090c] tracking-tight">
                            Must buy <span class="italic text-[#f59e0b] font-normal">selection</span>
                        </h2>
                    </div>
                    <a href="${BASE_URL}shop.php" class="text-[11px] sm:text-xs text-[#990024] font-extrabold hover:underline flex items-center uppercase tracking-wider">
                        Explore All <i class="fas fa-arrow-right ml-1 text-[9px]"></i>
                    </a>
                </div>

                <div id="mustbuy-products-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-6 pt-2">
                    <div class="col-span-full text-center py-8">
                        <div class="w-8 h-8 border-4 border-[#990024] border-t-transparent rounded-full animate-spin mx-auto"></div>
                    </div>
                </div>
            `;

            const grid = container.querySelector('#mustbuy-products-grid');
            try {
                let res = await Api.get('/products?is_must_buy=1&per_page=6');
                if (!res.success || !res.data || res.data.length === 0) {
                    res = await Api.get('/products?per_page=6&sort=price_desc');
                }

                if (res.success && res.data) {
                    const fixFn = (window.Utils && typeof window.Utils.fixImageUrl === 'function') 
                        ? window.Utils.fixImageUrl 
                        : (p => (BASE_URL + p.replace(/^\/+/, '')));

                    let html = '';
                    res.data.forEach(prod => {
                        const price = parseFloat(prod.price);
                        const mrp = prod.mrp ? parseFloat(prod.mrp) : (price * 1.25);
                        const discount = Math.round(((mrp - price) / mrp) * 100);
                        const rawImg1 = prod.primary_image || (prod.images && prod.images.length > 0 ? prod.images[0].image_url : null);
                        let rawImg2 = null;
                        if (prod.images && prod.images.length > 1) {
                            const second = prod.images.find(i => i.image_url !== rawImg1);
                            if (second) rawImg2 = second.image_url;
                        }
                        const imgUrl1 = rawImg1 ? fixFn(rawImg1) : `${BASE_URL}assets/images/product_placeholder.jpg`;
                        const imgUrl2 = rawImg2 ? fixFn(rawImg2) : null;

                        html += `
                            <div class="bg-white rounded-2xl sm:rounded-3xl border border-[#f59e0b]/20 shadow-sm hover:shadow-xl overflow-hidden flex flex-col group transition duration-300 relative">
                                <a href="${BASE_URL}product.php?slug=${prod.slug}" class="relative bg-gray-50 aspect-square overflow-hidden block">
                                    <img src="${imgUrl1}" alt="${prod.name}" onerror="this.onerror=null;this.src='${BASE_URL}assets/images/product_placeholder.jpg';" loading="lazy" decoding="async" class="w-full h-full object-cover transition-all duration-700 ease-out transform group-hover:scale-110 ${imgUrl2 ? 'group-hover:opacity-0' : ''}">
                                    ${imgUrl2 ? `
                                        <img src="${imgUrl2}" alt="${prod.name}" onerror="this.onerror=null;this.src='${BASE_URL}assets/images/product_placeholder.jpg';" loading="lazy" decoding="async" class="absolute inset-0 w-full h-full object-cover opacity-0 scale-100 group-hover:opacity-100 group-hover:scale-110 transition-all duration-700 ease-out">
                                    ` : ''}
                                    <span class="absolute top-2 sm:top-3 left-2 sm:left-3 bg-[#f59e0b] text-[#12090c] text-[8px] sm:text-[9px] font-black px-2 py-0.5 sm:py-1 rounded-full uppercase tracking-wider shadow-md flex items-center z-10">
                                        <i class="fas fa-star mr-1 text-[#990024]"></i> MUST BUY
                                    </span>
                                    ${discount > 0 ? `
                                        <span class="absolute top-2 sm:top-3 right-2 sm:right-3 bg-[#990024] text-[#fffdf7] text-[8px] sm:text-[9px] font-black px-1.5 sm:px-2 py-0.5 rounded-full shadow-sm z-10">
                                            -${discount}%
                                        </span>
                                    ` : ''}
                                </a>
                                <div class="p-3 sm:p-5 flex flex-col flex-grow">
                                    <h3 class="font-display text-xs sm:text-sm font-bold text-[#12090c] line-clamp-2 hover:text-[#990024] transition flex-grow">
                                        <a href="${BASE_URL}product.php?slug=${prod.slug}">${prod.name}</a>
                                    </h3>
                                    <div class="mt-2.5 pt-2 sm:pt-2.5 border-t border-gray-100 space-y-2">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <span class="text-xs sm:text-base font-black text-[#990024]">${Utils.formatCurrency(price)}</span>
                                                <span class="text-[9px] sm:text-[10px] text-gray-400 line-through block sm:inline sm:ml-1">${Utils.formatCurrency(mrp)}</span>
                                            </div>
                                            <button onclick="Shop.quickAdd(${prod.id})" ${parseInt(prod.stock_quantity) <= 0 ? 'disabled' : ''} class="w-8 h-8 sm:w-9 sm:h-9 rounded-full border border-[#990024] text-[#990024] hover:bg-[#990024] hover:text-white disabled:bg-gray-100 disabled:border-gray-200 disabled:text-gray-400 transition duration-200 flex items-center justify-center cursor-pointer shrink-0" title="Add to Cart">
                                                <i class="fas fa-shopping-bag text-xs"></i>
                                            </button>
                                        </div>
                                        <button onclick="Utils.buyNow(${prod.id}, 1, null, this)" ${parseInt(prod.stock_quantity) <= 0 ? 'disabled' : ''} class="w-full py-1.5 sm:py-2 px-3 rounded-xl bg-[#990024] hover:bg-[#7a001c] disabled:bg-gray-200 disabled:text-gray-400 text-white font-extrabold text-[10px] sm:text-xs shadow-2xs hover:shadow-xs transition duration-200 flex items-center justify-center space-x-1 cursor-pointer" title="Buy Now (Direct Checkout)">
                                            <i class="fas fa-bolt text-[9px] text-amber-300"></i>
                                            <span>Buy Now</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    grid.innerHTML = html;
                }
            } catch(e) {
                grid.innerHTML = `<span class="text-xs text-red-500 py-4 col-span-full text-center">Failed to load Must Buy items.</span>`;
            }
        },

        // ─── SECTION 8: Mega Sale Banner (Fluid Mobile Layout) ───
        async renderMegaSale(container) {
            let s = this.siteSettings;
            if (!s) {
                try {
                    const resSettings = await Api.get('/settings');
                    if (resSettings.success && resSettings.data) {
                        s = resSettings.data;
                        this.siteSettings = s;
                    }
                } catch(e) {}
            }
            s = s || {};

            if (s.timer_section_enabled === 'false') {
                return;
            }

            const badgeText = s.timer_badge_text || '🪔 LIMITED TIME FESTIVE SALE';
            const headline = s.timer_headline || 'Up to 60% off festive <span class="italic text-[#f59e0b] font-normal">collection</span>';
            const description = s.timer_description || 'Elevate your home celebrations with authentic brass diyas, handcrafted sweets, pure silver pooja thalis, and royal celebration hampers.';
            const ctaText = s.timer_cta_text || 'CLAIM FESTIVE OFFERS';
            let ctaLink = s.timer_cta_link || 'shop.php';
            if (ctaLink && !ctaLink.startsWith('http') && !ctaLink.startsWith('/')) {
                ctaLink = BASE_URL + ctaLink;
            }

            const block = document.createElement('div');
            block.className = 'my-8 sm:my-12';
            block.innerHTML = `
                <div class="bg-gradient-to-r from-[#990024] via-[#7a001c] to-[#4a0011] rounded-3xl sm:rounded-[2.5rem] p-6 sm:p-12 lg:p-16 text-white relative overflow-hidden shadow-2xl border border-[#f59e0b]/40">
                    
                    <div class="absolute -right-16 -top-16 opacity-20 pointer-events-none text-[#f59e0b] hidden sm:block">
                        <svg class="w-96 h-96 animate-spin-slow" viewBox="0 0 100 100" fill="none" stroke="currentColor">
                            <circle cx="50" cy="50" r="45" stroke-width="1" stroke-dasharray="4,4"/>
                            <circle cx="50" cy="50" r="35" stroke-width="1.5"/>
                            <circle cx="50" cy="50" r="25" stroke-width="1" stroke-dasharray="2,2"/>
                        </svg>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8 items-center relative z-10">
                        <div class="space-y-4 sm:space-y-6 text-center lg:text-left">
                            <span class="bg-[#f59e0b] text-[#12090c] font-black uppercase text-[10px] sm:text-xs px-3.5 sm:px-4 py-1.5 rounded-full tracking-widest inline-block shadow-md">
                                ${badgeText}
                            </span>

                            <h2 class="font-display text-3xl sm:text-5xl lg:text-6xl font-bold tracking-tight leading-tight">
                                ${headline}
                            </h2>

                            <p class="text-xs sm:text-sm text-slate-200 max-w-md mx-auto lg:mx-0 leading-relaxed font-medium">
                                ${description}
                            </p>

                            <div class="flex items-center justify-center lg:justify-start space-x-2 sm:space-x-3 pt-1 sm:pt-2">
                                <div class="bg-black/40 border border-[#f59e0b]/30 p-2 sm:p-3 rounded-xl sm:rounded-2xl text-center min-w-[52px] sm:min-w-[64px]">
                                    <span id="festive-timer-days" class="block font-display text-base sm:text-xl font-bold text-[#f59e0b]">00</span>
                                    <span class="text-[8px] sm:text-[9px] uppercase tracking-wider text-slate-300">Days</span>
                                </div>
                                <span class="text-base sm:text-xl font-bold text-[#f59e0b]">:</span>
                                <div class="bg-black/40 border border-[#f59e0b]/30 p-2 sm:p-3 rounded-xl sm:rounded-2xl text-center min-w-[52px] sm:min-w-[64px]">
                                    <span id="festive-timer-hours" class="block font-display text-base sm:text-xl font-bold text-[#f59e0b]">00</span>
                                    <span class="text-[8px] sm:text-[9px] uppercase tracking-wider text-slate-300">Hours</span>
                                </div>
                                <span class="text-base sm:text-xl font-bold text-[#f59e0b]">:</span>
                                <div class="bg-black/40 border border-[#f59e0b]/30 p-2 sm:p-3 rounded-xl sm:rounded-2xl text-center min-w-[52px] sm:min-w-[64px]">
                                    <span id="festive-timer-mins" class="block font-display text-base sm:text-xl font-bold text-[#f59e0b]">00</span>
                                    <span class="text-[8px] sm:text-[9px] uppercase tracking-wider text-slate-300">Mins</span>
                                </div>
                                <span class="text-base sm:text-xl font-bold text-[#f59e0b]">:</span>
                                <div class="bg-black/40 border border-[#f59e0b]/30 p-2 sm:p-3 rounded-xl sm:rounded-2xl text-center min-w-[52px] sm:min-w-[64px]">
                                    <span id="festive-timer-secs" class="block font-display text-base sm:text-xl font-bold text-[#f59e0b]">00</span>
                                    <span class="text-[8px] sm:text-[9px] uppercase tracking-wider text-slate-300">Secs</span>
                                </div>
                            </div>

                            <div class="pt-2 sm:pt-4">
                                <a href="${ctaLink}" class="bg-[#fffdf7] hover:bg-[#fde047] text-[#990024] font-extrabold text-[11px] sm:text-xs uppercase tracking-widest py-3.5 sm:py-4 px-7 sm:px-9 rounded-full shadow-2xl hover:scale-105 transition duration-300 inline-flex items-center justify-center space-x-2 w-full sm:w-auto">
                                    <span>${ctaText}</span>
                                    <i class="fas fa-arrow-right text-xs"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Dynamic Popular Products Grid -->
                        <div id="mega-sale-popular-grid" class="grid grid-cols-2 gap-3 sm:gap-4 max-w-md mx-auto w-full pt-4 sm:pt-0">
                            <span class="text-[10px] text-gray-300 col-span-2 text-center">Loading popular items...</span>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(block);

            // Real-time ticking countdown logic
            let targetTime;
            if (s.timer_target_date) {
                targetTime = new Date(s.timer_target_date.replace(' ', 'T')).getTime();
            }
            if (!targetTime || isNaN(targetTime)) {
                targetTime = new Date().getTime() + (3 * 24 * 60 * 60 * 1000);
            }

            const updateCountdown = () => {
                const now = new Date().getTime();
                const distance = targetTime - now;

                const daysEl = block.querySelector('#festive-timer-days');
                const hoursEl = block.querySelector('#festive-timer-hours');
                const minsEl = block.querySelector('#festive-timer-mins');
                const secsEl = block.querySelector('#festive-timer-secs');

                if (!daysEl || !hoursEl || !minsEl || !secsEl) return;

                if (distance <= 0) {
                    daysEl.innerText = '00';
                    hoursEl.innerText = '00';
                    minsEl.innerText = '00';
                    secsEl.innerText = '00';
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                daysEl.innerText = String(days).padStart(2, '0');
                hoursEl.innerText = String(hours).padStart(2, '0');
                minsEl.innerText = String(minutes).padStart(2, '0');
                secsEl.innerText = String(seconds).padStart(2, '0');
            };

            updateCountdown();
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timerInterval = setInterval(updateCountdown, 1000);

            // Fetch popular products for the banner right side
            try {
                const res = await Api.get('/products?is_trending=1&limit=4');
                const grid = block.querySelector('#mega-sale-popular-grid');
                if (grid && res.success && res.data && res.data.length > 0) {
                    let html = '';
                    res.data.slice(0, 4).forEach(prod => {
                        const price = parseFloat(prod.price);
                        let imagePath = prod.primary_image || (prod.images && prod.images.length > 0 ? prod.images[0].image_url : null);
                        const pImg = imagePath 
                            ? (imagePath.startsWith('http') ? imagePath : BASE_URL + imagePath) 
                            : `${BASE_URL}assets/images/product_placeholder.jpg`;
                        
                        html += `
                            <a href="${BASE_URL}product.php?slug=${prod.slug}" class="bg-[#12090c]/80 border border-[#f59e0b]/30 rounded-2xl sm:rounded-3xl overflow-hidden shadow-xl hover:scale-105 transition duration-300 block group">
                                <div class="aspect-square w-full overflow-hidden relative">
                                    <img src="${pImg}" onerror="this.onerror=null;this.src='${BASE_URL}assets/images/product_placeholder.jpg';" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                                </div>
                                <div class="p-2 sm:p-3 text-center border-t border-[#f59e0b]/30 bg-[#12090c]">
                                    <span class="text-[9px] sm:text-[10px] font-bold text-slate-200 line-clamp-1 mb-1">${prod.name}</span>
                                    <span class="text-[10px] sm:text-xs font-black text-[#f59e0b]">${Utils.formatCurrency(price)}</span>
                                </div>
                            </a>
                        `;
                    });
                    grid.innerHTML = html;
                } else if (grid) {
                    grid.innerHTML = '<span class="text-[10px] text-gray-300 col-span-2 text-center">No popular items found.</span>';
                }
            } catch (e) {
                const grid = block.querySelector('#mega-sale-popular-grid');
                if (grid) grid.innerHTML = '';
            }
        },

        // ─── SECTION 9: Insta Reels ───
        async renderReels(container) {
            container.innerHTML = `
                <div class="flex justify-between items-end border-b border-[#f59e0b]/20 pb-3 sm:pb-4">
                    <div>
                        <span class="text-[9px] sm:text-[10px] font-black text-[#990024] uppercase tracking-widest block mb-0.5 sm:mb-1">
                            <i class="fab fa-instagram text-[#f59e0b] mr-1"></i> Social Spotlight
                        </span>
                        <h2 class="font-display text-xl sm:text-3xl font-extrabold text-[#12090c] tracking-tight">
                            Festive moments on <span class="italic text-[#f59e0b] font-normal">reels</span>
                        </h2>
                    </div>
                    <a href="https://instagram.com" target="_blank" class="text-[11px] sm:text-xs text-[#990024] font-extrabold hover:underline flex items-center uppercase tracking-wider">
                        Follow @festivetreat <i class="fas fa-arrow-right ml-1 text-[9px]"></i>
                    </a>
                </div>

                <div id="reels-strip-container" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-6 pt-2">
                    <div class="col-span-full text-center py-8">
                        <div class="w-8 h-8 border-4 border-[#990024] border-t-transparent rounded-full animate-spin mx-auto"></div>
                    </div>
                </div>
            `;

            const strip = container.querySelector('#reels-strip-container');
            try {
                const res = await Api.get('/reels');
                if (res.success && res.data && res.data.length > 0) {
                    let html = '';
                    res.data.slice(0, 4).forEach(r => {
                        const poster = `${BASE_URL}assets/images/product_placeholder.jpg`;
                        
                        html += `
                            <div onclick="Homepage.openReelModal(${r.id})" class="relative rounded-2xl sm:rounded-3xl overflow-hidden shadow-xl aspect-[9/16] bg-[#12090c] border border-[#f59e0b]/30 group cursor-pointer transform hover:-translate-y-2 transition duration-300">
                                <img src="${poster}" onerror="this.onerror=null;this.src='${BASE_URL}assets/images/product_placeholder.jpg';" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                                <div class="absolute inset-0 bg-gradient-to-t from-[#12090c] via-transparent to-transparent"></div>

                                <span class="absolute top-2.5 left-2.5 bg-red-600 text-white text-[8px] sm:text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider shadow-md flex items-center">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping mr-1"></span> LIVE
                                </span>

                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-[#f59e0b] text-[#12090c] flex items-center justify-center text-xs sm:text-sm shadow-2xl group-hover:scale-125 transition duration-300">
                                        <i class="fas fa-play ml-0.5"></i>
                                    </div>
                                </div>

                                <div class="absolute bottom-3 left-3 right-3 sm:bottom-4 sm:left-4 sm:right-4 z-10 text-white space-y-0.5">
                                    <span class="text-[9px] sm:text-[10px] font-bold text-[#f59e0b] uppercase tracking-wider flex items-center">
                                        <i class="fab fa-instagram mr-1"></i> @festivetreat
                                    </span>
                                    <h4 class="text-xs font-extrabold line-clamp-1">${r.title || 'Festive Unboxing'}</h4>
                                </div>
                            </div>
                        `;
                    });
                    strip.innerHTML = html;
                } else {
                    strip.innerHTML = `<span class="text-xs text-gray-400 py-4 col-span-full text-center font-semibold">No reels published yet.</span>`;
                }
            } catch(e) {
                strip.innerHTML = `<span class="text-xs text-red-500 py-4 col-span-full text-center">Failed to load reels.</span>`;
            }
        },

        // ─── SECTION 10: USP Bar (2 Cols on Mobile) ───
        renderUSPBar(container) {
            const bar = document.createElement('div');
            bar.className = 'my-8 sm:my-12';
            bar.innerHTML = `
                <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-8 border border-[#f59e0b]/20 shadow-sm">
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-8">
                        <div class="flex flex-col sm:flex-row items-center sm:items-start text-center sm:text-left space-y-2 sm:space-y-0 sm:space-x-4">
                            <div class="w-11 h-11 sm:w-14 sm:h-14 rounded-2xl bg-[#990024]/10 border border-[#990024]/30 text-[#990024] flex items-center justify-center text-lg sm:text-xl flex-shrink-0">
                                <i class="fas fa-truck-fast"></i>
                            </div>
                            <div>
                                <h4 class="font-display text-xs sm:text-sm font-bold text-[#12090c]">Pan-India Express</h4>
                                <p class="text-[10px] sm:text-xs text-gray-500">Free shipping on ₹499+</p>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center sm:items-start text-center sm:text-left space-y-2 sm:space-y-0 sm:space-x-4">
                            <div class="w-11 h-11 sm:w-14 sm:h-14 rounded-2xl bg-[#990024]/10 border border-[#990024]/30 text-[#990024] flex items-center justify-center text-lg sm:text-xl flex-shrink-0">
                                <i class="fas fa-shield-halved"></i>
                            </div>
                            <div>
                                <h4 class="font-display text-xs sm:text-sm font-bold text-[#12090c]">100% Secure Checkout</h4>
                                <p class="text-[10px] sm:text-xs text-gray-500">UPI, Cards & NetBanking</p>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center sm:items-start text-center sm:text-left space-y-2 sm:space-y-0 sm:space-x-4">
                            <div class="w-11 h-11 sm:w-14 sm:h-14 rounded-2xl bg-[#990024]/10 border border-[#990024]/30 text-[#990024] flex items-center justify-center text-lg sm:text-xl flex-shrink-0">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <div>
                                <h4 class="font-display text-xs sm:text-sm font-bold text-[#12090c]">Royal Packaging</h4>
                                <p class="text-[10px] sm:text-xs text-gray-500">Custom festive box & ribbon</p>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center sm:items-start text-center sm:text-left space-y-2 sm:space-y-0 sm:space-x-4">
                            <div class="w-11 h-11 sm:w-14 sm:h-14 rounded-2xl bg-[#990024]/10 border border-[#990024]/30 text-[#990024] flex items-center justify-center text-lg sm:text-xl flex-shrink-0">
                                <i class="fas fa-headset"></i>
                            </div>
                            <div>
                                <h4 class="font-display text-xs sm:text-sm font-bold text-[#12090c]">24/7 Support</h4>
                                <p class="text-[10px] sm:text-xs text-gray-500">Instant WhatsApp assistance</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(bar);
        },

        // ─── SECTION 11: Newsletter Card ───
        renderNewsletter(container) {
            const card = document.createElement('div');
            card.className = 'my-8 sm:my-12';
            card.innerHTML = `
                <div class="bg-gradient-to-r from-[#990024] via-[#7a001c] to-[#4a0011] rounded-3xl sm:rounded-[2.5rem] p-6 sm:p-12 text-[#fffdf7] text-center relative overflow-hidden shadow-2xl border border-[#f59e0b]/30">
                    <div class="max-w-2xl mx-auto space-y-3 sm:space-y-4 relative z-10">
                        <span class="bg-[#f59e0b] text-[#12090c] font-black uppercase text-[9px] sm:text-[10px] px-3.5 py-1 rounded-full tracking-widest inline-block shadow-md">
                            ROYAL CLUB INSIDER
                        </span>
                        
                        <h2 class="font-display text-2xl sm:text-4xl font-bold tracking-tight">
                            Get 10% off your first <span class="italic text-[#f59e0b] font-normal">order</span>
                        </h2>

                        <p class="text-xs sm:text-sm text-slate-200 font-medium leading-relaxed">
                            Subscribe to receive exclusive festive discount codes, new arrival drops, and traditional Indian celebration inspiration.
                        </p>

                        <form onsubmit="Homepage.subscribeNewsletter(event)" class="pt-3 sm:pt-4 flex flex-col sm:flex-row items-center justify-center gap-2.5 sm:gap-3 max-w-md mx-auto w-full">
                            <input type="email" id="newsletter-email" required placeholder="Enter your email address..." class="w-full px-5 py-3 sm:py-3.5 rounded-full text-xs text-gray-800 bg-white outline-none focus:ring-2 focus:ring-[#f59e0b] shadow-inner">
                            <button type="submit" id="newsletter-btn" class="w-full sm:w-auto bg-[#f59e0b] hover:bg-[#fde047] text-[#12090c] font-black text-xs uppercase tracking-widest py-3.5 px-8 rounded-full shadow-lg hover:scale-105 transition duration-300 whitespace-nowrap flex items-center justify-center">
                                <span>SUBSCRIBE</span>
                            </button>
                        </form>
                    </div>
                </div>
            `;
            container.appendChild(card);
        },

        async openReelModal(id) {
            const modal = document.getElementById('modal-reel-shop');
            const content = document.getElementById('reel-modal-content');
            modal.classList.remove('hidden');

            try {
                const res = await Api.get('/reels/' + id);
                if (res.success && res.data) {
                    const r = res.data;
                    content.innerHTML = `
                        <div class="aspect-[9/16] bg-black rounded-2xl overflow-hidden relative shadow-lg">
                            <video src="${BASE_URL + r.video_url}" controls autoplay class="w-full h-full object-cover"></video>
                        </div>
                        <div class="space-y-2">
                            <h3 class="font-display text-base font-bold text-gray-900">${r.title || 'Festive Reel'}</h3>
                            <p class="text-xs text-gray-500">${r.description || ''}</p>
                            <a href="${BASE_URL}shop.php" class="w-full block text-center bg-[#990024] text-white font-extrabold text-xs uppercase tracking-widest py-3 rounded-full shadow-md hover:bg-[#7a001c] transition">
                                Shop Featured Products
                            </a>
                        </div>
                    `;
                }
            } catch(e) {
                content.innerHTML = `<span class="text-xs text-red-500">Failed to load reel details.</span>`;
            }
        },

        closeReelModal() {
            document.getElementById('modal-reel-shop').classList.add('hidden');
        },

        toggleVideo() {
            const video = document.getElementById('hero-video');
            const btn = document.getElementById('btn-video-control');
            if (!video) return;

            if (this.videoPlaying) {
                video.pause();
                btn.innerHTML = `<i class="fas fa-play"></i>`;
                this.videoPlaying = false;
            } else {
                video.play();
                btn.innerHTML = `<i class="fas fa-pause"></i>`;
                this.videoPlaying = true;
            }
        },

        initHangings(config) {
            if (config.hero_hangings_enabled !== 'true') return;
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/matter-js/0.19.0/matter.min.js';
            script.onload = () => {
                this.setupMatterPhysics(config);
            };
            document.head.appendChild(script);
        },

        async subscribeNewsletter(e) {
            e.preventDefault();
            const emailInput = document.getElementById('newsletter-email');
            const btn = document.getElementById('newsletter-btn');
            const email = emailInput.value.trim();
            
            if (!email) return;
            
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>';
            btn.disabled = true;
            
            try {
                const res = await Api.post('/newsletter/subscribe', { email: email });
                if (res.success) {
                    Utils.showToast(res.message || 'Thank you for subscribing!', 'success');
                    emailInput.value = '';
                } else {
                    Utils.showToast(res.error || 'Failed to subscribe.', 'error');
                }
            } catch (error) {
                Utils.showToast('An error occurred. Please try again.', 'error');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        },

        setupMatterPhysics(config) {
            const container = document.getElementById('hangings-container');
            if (!container) return;

            const { Engine, World, Bodies, Constraint, Composite } = Matter;
            const engine = Engine.create();
            const gravityScale = parseFloat(config.hero_hangings_gravity);
            engine.gravity.y = isNaN(gravityScale) ? 1.0 : gravityScale;

            const canvas = document.createElement('canvas');
            canvas.style.width = '100%';
            canvas.style.height = '100%';
            container.appendChild(canvas);

            const ctx = canvas.getContext('2d');
            let width = container.clientWidth;
            let height = container.clientHeight;
            canvas.width = width;
            canvas.height = height;

            window.addEventListener('resize', () => {
                if (!container) return;
                width = container.clientWidth;
                height = container.clientHeight;
                canvas.width = width;
                canvas.height = height;
            });
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        Homepage.init();
    });
</script>

<script src="<?php echo BASE_URL; ?>assets/js/shop.js?v=<?php echo time(); ?>"></script>

<?php
include_once __DIR__ . '/includes/footer.php';
?>
