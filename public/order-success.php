<?php
require_once __DIR__ . '/includes/config.php';
include_once __DIR__ . '/includes/header.php';
?>

<div class="min-h-[75vh] py-12 px-4 sm:px-6 lg:px-8 flex flex-col items-center justify-center relative overflow-hidden">
    
    <!-- Confetti & Sparkles Canvas Overlay -->
    <canvas id="sparkles-canvas" class="fixed inset-0 pointer-events-none z-30"></canvas>

    <div class="max-w-2xl w-full space-y-8 text-center relative z-10">
        
        <!-- Animated Tick Icon Container -->
        <div class="flex justify-center mb-6">
            <div class="relative flex items-center justify-center">
                <!-- Glowing Outer Pulse Rings -->
                <div class="absolute w-32 h-32 rounded-full bg-emerald-500/20 animate-ping opacity-75"></div>
                <div class="absolute w-28 h-28 rounded-full bg-emerald-500/30 animate-pulse"></div>

                <!-- Animated SVG Checkmark -->
                <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-emerald-600 to-emerald-400 flex items-center justify-center shadow-xl shadow-emerald-500/30 relative z-10 transition-transform duration-500 scale-100 hover:scale-105">
                    <svg class="w-14 h-14 text-white tick-svg" viewBox="0 0 52 52">
                        <circle class="tick-circle" cx="26" cy="26" r="23" fill="none" stroke="currentColor" stroke-width="4"/>
                        <path class="tick-check" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" d="M14 27l7 7 16-16"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Confetti & Sparkle Badge Header -->
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-600 text-xs font-bold uppercase tracking-wider animate-bounce">
                <span>✨ Order Confirmed</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 font-display tracking-tight">
                Thank You for Your Order!
            </h1>
            <p class="text-sm sm:text-base text-slate-600 max-w-md mx-auto leading-relaxed">
                Your festive order has been placed successfully. We are preparing your items with love and care!
            </p>
        </div>

        <!-- Order Information Card (Populated via JS) -->
        <div id="order-success-card" class="bg-white rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/50 p-6 sm:p-8 text-left space-y-6">
            <div class="flex flex-col justify-center items-center py-12 space-y-3">
                <div class="loader-spinner-dark"></div>
                <span class="text-slate-500 text-xs font-medium">Fetching order confirmation details...</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
            <a id="btn-track-order" href="#" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 rounded-2xl bg-[#990024] hover:bg-[#7a001c] text-white font-bold text-sm shadow-lg shadow-red-900/20 hover:shadow-xl transition duration-200 group">
                <i class="fas fa-truck-fast mr-2 group-hover:translate-x-0.5 transition-transform"></i> Track Order Details
            </a>
            <a href="<?php echo BASE_URL; ?>index.php" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm transition duration-200">
                <i class="fas fa-bag-shopping mr-2"></i> Continue Shopping
            </a>
        </div>

    </div>
</div>

<!-- Inline CSS for Tick Drawing & Sparkle Animations -->
<style>
.tick-svg {
    transform: rotate(0deg);
}

.tick-circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.tick-check {
    transform-origin: 50% 50%;
    stroke-dasharray: 48;
    stroke-dashoffset: 48;
    animation: stroke 0.4s cubic-bezier(0.65, 0, 0.45, 1) 0.5s forwards;
}

@keyframes stroke {
    100% {
        stroke-dashoffset: 0;
    }
}
</style>

<script>
/**
 * Order Success Module — Celebration Sound Effects, Sparkles, & Order Summary
 */
const OrderSuccess = {
    orderId: null,

    async init() {
        if (!Auth.isLoggedIn()) {
            window.location.href = BASE_URL + 'login.php';
            return;
        }

        this.orderId = Utils.getQueryParam('id');
        if (!this.orderId) {
            window.location.href = BASE_URL + 'index.php';
            return;
        }

        document.getElementById('btn-track-order').href = BASE_URL + 'order-detail.php?id=' + this.orderId;

        // 1. Trigger Sound Effect
        this.playCelebrationSound();

        // 2. Launch Sparkles & Confetti Burst
        this.startSparklesAnimation();

        // 3. Load Order Summary Details
        await this.loadOrderDetails();
    },

    /**
     * Synthesize a victory chime sound using Web Audio API
     */
    playCelebrationSound() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;

            const ctx = new AudioContext();
            
            // Resume context if suspended by browser autoplay policy
            if (ctx.state === 'suspended') {
                const resume = () => {
                    ctx.resume();
                    window.removeEventListener('click', resume);
                    window.removeEventListener('touchstart', resume);
                };
                window.addEventListener('click', resume);
                window.addEventListener('touchstart', resume);
            }

            const notes = [
                { f: 523.25, time: 0.0,  duration: 0.15 }, // C5
                { f: 659.25, time: 0.12, duration: 0.15 }, // E5
                { f: 783.99, time: 0.24, duration: 0.20 }, // G5
                { f: 1046.5, time: 0.38, duration: 0.50 }  // C6
            ];

            notes.forEach(n => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.type = 'triangle';
                osc.frequency.setValueAtTime(n.f, ctx.currentTime + n.time);

                gain.gain.setValueAtTime(0.001, ctx.currentTime + n.time);
                gain.gain.exponentialRampToValueAtTime(0.25, ctx.currentTime + n.time + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + n.time + n.duration);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(ctx.currentTime + n.time);
                osc.stop(ctx.currentTime + n.time + n.duration + 0.05);
            });
        } catch (e) {
            console.log("Audio celebration initialized.");
        }
    },

    /**
     * Particle Confetti & Floating Festive Sparkles Canvas Engine
     */
    startSparklesAnimation() {
        const canvas = document.getElementById('sparkles-canvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        let width = canvas.width = window.innerWidth;
        let height = canvas.height = window.innerHeight;

        window.addEventListener('resize', () => {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        });

        const colors = ['#990024', '#D4AF37', '#10B981', '#F43F5E', '#3B82F6', '#F59E0B'];
        const particles = [];
        const particleCount = 75;

        for (let i = 0; i < particleCount; i++) {
            particles.push({
                x: width / 2,
                y: height / 3,
                vx: (Math.random() - 0.5) * 14,
                vy: (Math.random() - 0.7) * 12,
                size: Math.random() * 7 + 3,
                color: colors[Math.floor(Math.random() * colors.length)],
                rotation: Math.random() * Math.PI * 2,
                vRot: (Math.random() - 0.5) * 0.2,
                opacity: 1,
                decay: Math.random() * 0.015 + 0.005,
                shape: Math.random() > 0.5 ? 'star' : 'circle'
            });
        }

        function drawStar(cx, cy, spikes, outerRadius, innerRadius, color) {
            let rot = Math.PI / 2 * 3;
            let x = cx;
            let y = cy;
            let step = Math.PI / spikes;

            ctx.beginPath();
            ctx.moveTo(cx, cy - outerRadius);
            for (let i = 0; i < spikes; i++) {
                x = cx + Math.cos(rot) * outerRadius;
                y = cy + Math.sin(rot) * outerRadius;
                ctx.lineTo(x, y);
                rot += step;

                x = cx + Math.cos(rot) * innerRadius;
                y = cy + Math.sin(rot) * innerRadius;
                ctx.lineTo(x, y);
                rot += step;
            }
            ctx.lineTo(cx, cy - outerRadius);
            ctx.closePath();
            ctx.fillStyle = color;
            ctx.fill();
        }

        let animationFrame;
        function render() {
            ctx.clearRect(0, 0, width, height);

            let activeCount = 0;
            particles.forEach(p => {
                if (p.opacity > 0) {
                    activeCount++;
                    p.x += p.vx;
                    p.y += p.vy;
                    p.vy += 0.2; // gravity
                    p.vx *= 0.98;
                    p.opacity -= p.decay;
                    p.rotation += p.vRot;

                    ctx.save();
                    ctx.globalAlpha = Math.max(0, p.opacity);
                    ctx.translate(p.x, p.y);
                    ctx.rotate(p.rotation);

                    if (p.shape === 'star') {
                        drawStar(0, 0, 4, p.size, p.size / 2, p.color);
                    } else {
                        ctx.fillStyle = p.color;
                        ctx.beginPath();
                        ctx.arc(0, 0, p.size / 2, 0, Math.PI * 2);
                        ctx.fill();
                    }

                    ctx.restore();
                }
            });

            if (activeCount > 0) {
                animationFrame = requestAnimationFrame(render);
            } else {
                ctx.clearRect(0, 0, width, height);
            }
        }

        render();
    },

    /**
     * Load details of placed order
     */
    async loadOrderDetails() {
        const card = document.getElementById('order-success-card');
        try {
            const res = await Api.get('/orders/' + this.orderId);
            if (!res.success || !res.data) {
                throw new Error("Order details not found.");
            }

            const ord = res.data;
            const address = ord.shipping_address || {};

            // Estimated Delivery: 3 to 5 business days
            const createdDate = new Date(ord.created_at);
            const estMinDate = new Date(createdDate);
            estMinDate.setDate(estMinDate.getDate() + 3);
            const estMaxDate = new Date(createdDate);
            estMaxDate.setDate(estMaxDate.getDate() + 5);

            const options = { month: 'short', day: 'numeric' };
            const estString = `${estMinDate.toLocaleDateString('en-IN', options)} - ${estMaxDate.toLocaleDateString('en-IN', options)}`;

            let itemsHtml = '';
            ord.items.forEach(item => {
                const placeholder = `${BASE_URL}assets/images/product_placeholder.jpg`;
                const imgUrl = item.primary_image ? (BASE_URL + item.primary_image) : placeholder;

                itemsHtml += `
                    <div class="flex items-center gap-3 py-3 border-b border-slate-100 last:border-0 text-xs">
                        <img src="${imgUrl}" alt="${item.product_name}" class="w-10 h-10 rounded-lg object-cover bg-slate-50 flex-shrink-0" onerror="this.onerror=null;this.src='${placeholder}';">
                        <div class="flex-1 min-w-0">
                            <span class="font-bold text-slate-800 truncate block">${item.product_name}</span>
                            <span class="text-slate-400 font-medium">Qty: ${item.quantity}</span>
                        </div>
                        <span class="font-bold text-slate-900">${Utils.formatCurrency(item.price * item.quantity)}</span>
                    </div>
                `;
            });

            card.innerHTML = `
                <!-- Order Summary Header Pill -->
                <div class="flex flex-wrap items-center justify-between gap-4 pb-6 border-b border-slate-100">
                    <div>
                        <span class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider block">Order Reference</span>
                        <div class="flex items-center gap-2 mt-0.5">
                            <h3 class="text-lg font-black text-slate-900">${ord.order_number}</h3>
                            <button onclick="navigator.clipboard.writeText('${ord.order_number}'); Utils.showToast('Order number copied!', 'success');" class="text-slate-400 hover:text-slate-600 transition" title="Copy Order Number">
                                <i class="far fa-copy text-sm"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider block">Estimated Delivery</span>
                        <span class="text-sm font-bold text-emerald-600 flex items-center justify-end mt-0.5">
                            <i class="fas fa-calendar-check mr-1.5 text-xs"></i> ${estString}
                        </span>
                    </div>
                </div>

                <!-- Shipping & Payment Method Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50/80 p-4 rounded-2xl border border-slate-100 text-xs">
                    <div>
                        <span class="font-extrabold text-slate-400 uppercase tracking-wider text-[10px] block mb-1">Delivering To</span>
                        <span class="font-bold text-slate-800 block text-sm">${address.full_name || 'Valued Customer'}</span>
                        <span class="text-slate-600 block">${address.address_line1 || ''}</span>
                        <span class="text-slate-600 block">${address.city || ''}, ${address.state || ''} - ${address.pincode || ''}</span>
                    </div>
                    <div>
                        <span class="font-extrabold text-slate-400 uppercase tracking-wider text-[10px] block mb-1">Payment Method</span>
                        <span class="font-bold text-slate-800 block text-sm uppercase">${ord.payment_method}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase mt-1 ${ord.payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'}">
                            Payment: ${ord.payment_status}
                        </span>
                        ${ord.payment_method === 'cod' && ord.payment_status !== 'paid' ? `
                            <div class="mt-2.5">
                                <a href="${BASE_URL}order-detail.php?id=${ord.id}&pay=1" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#990024] hover:bg-[#7a001c] text-white font-bold text-xs transition shadow-sm">
                                    <i class="fas fa-credit-card mr-1.5"></i> Pay Online Now
                                </a>
                            </div>
                        ` : ''}
                    </div>
                </div>

                <!-- Items Preview Snapshot -->
                <div>
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-2">Order Items (${ord.items.length})</span>
                    <div class="bg-white rounded-2xl border border-slate-100 px-4">
                        ${itemsHtml}
                    </div>
                </div>

                <!-- Total Amount -->
                <div class="flex justify-between items-center pt-4 border-t border-slate-100 text-sm font-black text-slate-900">
                    <span>Total Amount Paid</span>
                    <span class="text-xl text-[#990024]">${Utils.formatCurrency(ord.total)}</span>
                </div>
            `;

        } catch (e) {
            card.innerHTML = `
                <div class="text-center py-8">
                    <span class="text-slate-500 font-medium text-sm">Order confirmed! Reference ID: #${this.orderId}</span>
                </div>
            `;
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    OrderSuccess.init();
});
</script>

<?php
include_once __DIR__ . '/includes/footer.php';
?>
