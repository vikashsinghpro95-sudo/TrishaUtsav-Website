<?php
require_once __DIR__ . '/includes/config.php';
include_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 my-8 sm:my-12">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Contact Info Box (Left Column) -->
        <div class="md:col-span-1 space-y-6">
            <div class="bg-gradient-to-br from-[#990024] via-[#7a001c] to-[#4a0011] rounded-3xl p-8 text-[#fffdf7] shadow-xl border border-[#f59e0b]/30 flex flex-col justify-between min-h-[380px] relative overflow-hidden">
                <div class="z-10 space-y-2">
                    <span class="bg-[#f59e0b] text-[#12090c] font-black uppercase text-[9px] px-3 py-1 rounded-full tracking-widest inline-block shadow-md">
                        ROYAL ASSISTANCE
                    </span>
                    <h2 class="font-display text-3xl font-extrabold text-[#fffdf7]">Reach Out to Us</h2>
                    <p class="text-slate-200 text-xs leading-relaxed font-medium">Have questions about bulk festive orders, custom sweet boxes, or express shipping? Our team is at your service.</p>
                </div>
                
                <div class="space-y-4 my-6 text-xs font-semibold z-10">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full bg-[#f59e0b] text-[#12090c] flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-phone-alt text-xs"></i>
                        </div>
                        <span id="contact-phone-lbl" class="text-slate-200 font-bold">+91 8830524475</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full bg-[#f59e0b] text-[#12090c] flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-envelope text-xs"></i>
                        </div>
                        <span id="contact-email-lbl" class="text-slate-200 font-bold">Trishautsaventerprises@gmail.com</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full bg-[#f59e0b] text-[#12090c] flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-xs"></i>
                        </div>
                        <span class="text-slate-200 font-bold">Sai Sarovar Society, Jambhulwadi Road, Pune, Maha 411046 - India</span>
                    </div>
                </div>

                <div class="flex space-x-3 text-sm text-[#f59e0b] z-10 pt-2 border-t border-white/10">
                    <a href="https://www.instagram.com/trisha_utsav?igsh=MWxicXllOHV5NWk2Mw==" target="_blank" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-[#f59e0b] hover:text-[#12090c] transition"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.facebook.com/trishautsavofficial?rdid=zpY8M8ulu5g94iHB&share_url=https%3A%2F%2Fwww.facebook.com%2Fshare%2F1bUe7y66cN%2F%3Fref%3D1#" target="_blank" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-[#f59e0b] hover:text-[#12090c] transition"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://wa.me/918830524475" target="_blank" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-[#f59e0b] hover:text-[#12090c] transition"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>

        <!-- Contact Form (Right Column) -->
        <div class="md:col-span-2 bg-white rounded-3xl border border-[#f59e0b]/20 p-6 sm:p-10 shadow-sm">
            <h1 class="font-display text-2xl font-extrabold text-[#12090c] mb-6 flex items-center">
                <i class="fas fa-paper-plane text-[#990024] mr-3"></i> Send Us a Message
            </h1>

            <form id="frm-contact" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Your Name</label>
                        <input type="text" id="contact-name" required placeholder="Rahul Sharma" class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024] transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Email Address</label>
                        <input type="email" id="contact-email" required placeholder="rahul@example.com" class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024] transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Subject</label>
                    <input type="text" id="contact-subject" required placeholder="Festive Celebration Inquiry" class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024] transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Message</label>
                    <textarea id="contact-message" rows="5" required placeholder="Enter details of your inquiry or custom order..." class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024] transition resize-none"></textarea>
                </div>

                <button type="submit" class="bg-[#990024] hover:bg-[#7a001c] text-[#fffdf7] font-extrabold text-xs uppercase tracking-widest py-3.5 px-8 rounded-full shadow-lg transition duration-200 border border-[#f59e0b]/30 flex items-center space-x-2">
                    <i class="fas fa-paper-plane text-xs text-[#f59e0b]"></i>
                    <span>SEND MESSAGE</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        // Settings JS removed to allow hardcoded contact info to display

        const form = document.getElementById('frm-contact');
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = `<span>Sending...</span>`;

            setTimeout(() => {
                Utils.showToast("Message sent successfully! Our royal concierge team will contact you shortly.", "success");
                form.reset();
                btn.disabled = false;
                btn.innerHTML = `<i class="fas fa-paper-plane text-xs text-[#f59e0b]"></i> <span>SEND MESSAGE</span>`;
            }, 1000);
        });
    });
</script>

<?php
include_once __DIR__ . '/includes/footer.php';
?>