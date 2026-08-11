<?php
// admin/settings.php
include_once __DIR__ . '/includes/admin-header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">System Settings</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Configure general store identity, hero visuals, and physics animations.</p>
    </div>
    <div class="flex items-center gap-3">
        <button type="button" onclick="document.getElementById('frm-settings-save').dispatchEvent(new Event('submit', {cancelable: true, bubbles: true}))" id="btn-header-save" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors shadow-sm">
            <i class="ph ph-floppy-disk mr-2"></i> Save Settings
        </button>
    </div>
</div>

<div class="pb-12">
    <!-- Main Settings Form Grid (2 Columns: 2 cols main, 1 col info/preview) -->
    <form id="frm-settings-save" class="grid grid-cols-1 xl:grid-cols-3 gap-6 lg:gap-8">
        
        <!-- Left 2 Columns: Config Panels -->
        <div class="xl:col-span-2 space-y-6">
            
            <!-- 1. Store Identity & Regional Settings -->
            <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                        <i class="ph ph-storefront mr-2 text-slate-400"></i> Store Identity & Regional
                    </h3>
                </div>
                
                <div class="p-5 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="set-store-name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Store Name</label>
                            <input type="text" id="set-store-name" required placeholder="e.g. Trisha Utsav" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                        <div>
                            <label for="set-currency" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Currency Code</label>
                            <select id="set-currency" required class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors cursor-pointer">
                                <option value="INR">Indian Rupee (INR ₹)</option>
                                <option value="USD">US Dollar (USD $)</option>
                                <option value="EUR">Euro (EUR €)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="set-contact-email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Support Email</label>
                            <input type="email" id="set-contact-email" required placeholder="support@domain.com" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                        <div>
                            <label for="set-contact-phone" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Contact Phone</label>
                            <input type="text" id="set-contact-phone" required placeholder="+91 9876543210" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="set-tax-rate" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Default GST Rate (%)</label>
                            <input type="number" step="0.01" id="set-tax-rate" required placeholder="18.00" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                        <div>
                            <label for="set-shipping-fee" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Default Shipping Fee (₹)</label>
                            <input type="number" step="0.01" id="set-shipping-fee" required placeholder="50.00" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Store Logo Asset</label>
                        <div class="flex items-center space-x-3">
                            <input type="file" id="set-store-logo-file" accept="image/*" class="hidden" onchange="Settings.uploadAsset(this, 'logo', 'set-store-logo')">
                            <button type="button" onclick="document.getElementById('set-store-logo-file').click()" class="inline-flex items-center px-3 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors whitespace-nowrap">
                                <i class="ph ph-upload-simple mr-2"></i> Upload Logo
                            </button>
                            <input type="text" id="set-store-logo" readonly class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 placeholder-slate-400 sm:text-sm transition-colors" placeholder="No file uploaded">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Hero Section Customizer -->
            <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                        <i class="ph ph-image mr-2 text-slate-400"></i> Hero Section
                    </h3>
                </div>

                <div class="p-5 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="set-hero-bg-type" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Background Media Type</label>
                            <select id="set-hero-bg-type" onchange="Settings.toggleBgType(this.value)" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors cursor-pointer">
                                <option value="video">🎥 Background Video (MP4 / WebM)</option>
                                <option value="image">🖼️ Background Image (Banner)</option>
                            </select>
                        </div>

                        <div id="box-hero-video">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Background Video File</label>
                            <div class="flex items-center space-x-3">
                                <input type="file" id="set-hero-video-file" accept="video/mp4,video/webm" class="hidden" onchange="Settings.uploadAsset(this, 'video', 'set-hero-video-url')">
                                <button type="button" onclick="document.getElementById('set-hero-video-file').click()" class="inline-flex items-center px-3 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors whitespace-nowrap">
                                    <i class="ph ph-video-camera mr-2"></i> Upload Video
                                </button>
                                <input type="text" id="set-hero-video-url" readonly class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 placeholder-slate-400 sm:text-sm transition-colors" placeholder="No file uploaded">
                            </div>
                        </div>

                        <div id="box-hero-image" class="hidden">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Background Image File</label>
                            <div class="flex items-center space-x-3">
                                <input type="file" id="set-hero-image-file" accept="image/*" class="hidden" onchange="Settings.uploadAsset(this, 'banner', 'set-hero-image-url')">
                                <button type="button" onclick="document.getElementById('set-hero-image-file').click()" class="inline-flex items-center px-3 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors whitespace-nowrap">
                                    <i class="ph ph-image mr-2"></i> Upload Image
                                </button>
                                <input type="text" id="set-hero-image-url" readonly class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 placeholder-slate-400 sm:text-sm transition-colors" placeholder="No file uploaded">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5">
                        <div>
                            <label for="set-hero-headline" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Hero Headline Text</label>
                            <input type="text" id="set-hero-headline" placeholder="Celebrate the Colors of Joy" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                        <div>
                            <label for="set-hero-description" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Hero Subtitle</label>
                            <textarea id="set-hero-description" rows="2" placeholder="Enter text description..." class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors resize-none"></textarea>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="set-hero-cta-text" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">CTA Button Text</label>
                            <input type="text" id="set-hero-cta-text" placeholder="Explore Festivities" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                        <div>
                            <label for="set-hero-cta-link" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">CTA Destination Link</label>
                            <input type="text" id="set-hero-cta-link" placeholder="/shop.php" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="set-hero-overlay-opacity" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Overlay Tint Opacity (0-1)</label>
                            <input type="number" step="0.05" min="0" max="1" id="set-hero-overlay-opacity" placeholder="0.35" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                        <div>
                            <label for="set-hero-overlay-color" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Overlay Hex Color</label>
                            <input type="text" id="set-hero-overlay-color" placeholder="#000000" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Mobile Hero Settings -->
            <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                        <i class="ph ph-device-mobile mr-2 text-slate-400"></i> Mobile Hero View
                    </h3>
                </div>
                
                <div class="p-5 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="set-hero-mobile-bg-type" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mobile Background Media</label>
                            <select id="set-hero-mobile-bg-type" onchange="Settings.toggleMobileBgType(this.value)" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors cursor-pointer">
                                <option value="desktop">Use Desktop Media (Inherit)</option>
                                <option value="image">Custom Mobile Image (Portrait)</option>
                                <option value="video">Custom Mobile Video</option>
                                <option value="hidden">Hide Hero Section on Mobile</option>
                            </select>
                        </div>

                        <div>
                            <label for="set-hero-mobile-height" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mobile Height Profile</label>
                            <select id="set-hero-mobile-height" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors cursor-pointer">
                                <option value="medium">Medium Height (460px)</option>
                                <option value="compact">Compact Height (380px)</option>
                                <option value="full">Full Screen Height (80vh)</option>
                            </select>
                        </div>
                    </div>

                    <div id="box-hero-mobile-video" class="hidden">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mobile Video File</label>
                        <div class="flex items-center space-x-3">
                            <input type="file" id="file-hero-mobile-video" accept="video/mp4,video/webm" class="hidden" onchange="Settings.uploadMobileMedia(this, 'video')">
                            <button type="button" onclick="document.getElementById('file-hero-mobile-video').click()" class="inline-flex items-center px-3 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors whitespace-nowrap">
                                <i class="ph ph-upload-simple mr-2"></i> Upload Video
                            </button>
                            <input type="text" id="set-hero-mobile-video-url" readonly class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 placeholder-slate-400 sm:text-sm transition-colors">
                        </div>
                    </div>

                    <div id="box-hero-mobile-image" class="hidden">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mobile Image File</label>
                        <div class="flex items-center space-x-3">
                            <input type="file" id="file-hero-mobile-image" accept="image/*" class="hidden" onchange="Settings.uploadMobileMedia(this, 'image')">
                            <button type="button" onclick="document.getElementById('file-hero-mobile-image').click()" class="inline-flex items-center px-3 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors whitespace-nowrap">
                                <i class="ph ph-upload-simple mr-2"></i> Upload Image
                            </button>
                            <input type="text" id="set-hero-mobile-image-url" readonly class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 placeholder-slate-400 sm:text-sm transition-colors">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Matter.js Physics Hangings Settings -->
            <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                        <i class="ph ph-atom mr-2 text-slate-400"></i> Interactive Garlands (Matter.js)
                    </h3>
                </div>

                <div class="p-5 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="set-hangings-enabled" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Enable Physics Animations</label>
                            <select id="set-hangings-enabled" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors cursor-pointer">
                                <option value="true">Enabled</option>
                                <option value="false">Disabled</option>
                            </select>
                        </div>
                        <div>
                            <label for="set-hangings-type" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Garland Theme</label>
                            <select id="set-hangings-type" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors cursor-pointer">
                                <option value="mixed">Mixed (Flowers, Decor, Lanterns)</option>
                                <option value="flowers">Marigold Flowers</option>
                                <option value="gifts">Festive Boxes</option>
                                <option value="decoratives">Diyas & Lanterns</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="set-hangings-count" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Garland Count (2-15)</label>
                            <input type="number" min="2" max="15" id="set-hangings-count" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                        <div>
                            <label for="set-hangings-gravity" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Gravity Scale (0.0-3.0)</label>
                            <input type="number" step="0.1" min="0" max="3" id="set-hangings-gravity" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 5. Footer Content & Festive Motifs -->
            <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                        <i class="ph ph-layout mr-2 text-slate-400"></i> Footer & Social Links
                    </h3>
                </div>

                <div class="p-5 space-y-6">
                    <div>
                        <label for="set-footer-about-text" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Footer About Text</label>
                        <textarea id="set-footer-about-text" rows="2" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="set-footer-address" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Store Address</label>
                            <input type="text" id="set-footer-address" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                        <div>
                            <label for="set-footer-operating-hours" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Operating Hours</label>
                            <input type="text" id="set-footer-operating-hours" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="set-footer-social-instagram" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><i class="ph ph-instagram-logo mr-1"></i> Instagram</label>
                            <input type="text" id="set-footer-social-instagram" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                        <div>
                            <label for="set-footer-social-facebook" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><i class="ph ph-facebook-logo mr-1"></i> Facebook</label>
                            <input type="text" id="set-footer-social-facebook" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="set-footer-social-whatsapp" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><i class="ph ph-whatsapp-logo mr-1"></i> WhatsApp</label>
                            <input type="text" id="set-footer-social-whatsapp" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                        <div>
                            <label for="set-footer-social-youtube" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><i class="ph ph-youtube-logo mr-1"></i> YouTube</label>
                            <input type="text" id="set-footer-social-youtube" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="set-footer-decorations-enabled" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Footer Mandala Pattern</label>
                            <select id="set-footer-decorations-enabled" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors cursor-pointer">
                                <option value="true">Enabled</option>
                                <option value="false">Disabled</option>
                            </select>
                        </div>
                        <div>
                            <label for="set-footer-copyright-text" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Copyright Text</label>
                            <input type="text" id="set-footer-copyright-text" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Festive Sale & Countdown Timer Section Customizer -->
            <div class="bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white flex items-center">
                        <i class="ph ph-timer mr-2 text-amber-500"></i> Festive Sale & Countdown Timer
                    </h3>
                </div>

                <div class="p-5 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="set-timer-section-enabled" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Section Visibility</label>
                            <select id="set-timer-section-enabled" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors cursor-pointer">
                                <option value="true">Enabled (Visible)</option>
                                <option value="false">Disabled (Hidden)</option>
                            </select>
                        </div>
                        <div>
                            <label for="set-timer-target-date" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Countdown End Date & Time</label>
                            <input type="datetime-local" id="set-timer-target-date" required class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="set-timer-badge-text" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Badge Text</label>
                            <input type="text" id="set-timer-badge-text" placeholder="🪔 LIMITED TIME FESTIVE SALE" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                        <div>
                            <label for="set-timer-headline" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Sale Headline</label>
                            <input type="text" id="set-timer-headline" placeholder="Up to 60% off festive collection" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                    </div>

                    <div>
                        <label for="set-timer-description" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Section Subtitle / Description</label>
                        <textarea id="set-timer-description" rows="2" placeholder="Elevate your home celebrations with authentic brass diyas..." class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="set-timer-cta-text" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">CTA Button Text</label>
                            <input type="text" id="set-timer-cta-text" placeholder="CLAIM FESTIVE OFFERS" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                        <div>
                            <label for="set-timer-cta-link" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">CTA Button Link</label>
                            <input type="text" id="set-timer-cta-link" placeholder="shop.php" class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-colors">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Global Hidden fields mapped from backend that exist but are rarely altered here directly -->
            <input type="hidden" id="set-hero-bestseller-product-id">
        </div>

        <!-- Right 1 Column: Summary & Quick Actions -->
        <div class="xl:col-span-1 space-y-6">
            
            <!-- Quick Summary Card -->
            <div class="bg-primary-900 dark:bg-slate-800 text-white p-6 rounded-xl shadow-md overflow-hidden relative">
                <div class="absolute -right-4 -top-4 text-primary-800/50 dark:text-slate-700/50">
                    <i class="ph-fill ph-gear text-9xl"></i>
                </div>
                <div class="relative z-10 space-y-5">
                    <div class="flex justify-between items-center border-b border-primary-800 dark:border-slate-700 pb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-primary-200">System Status</span>
                        <span class="flex h-3 w-3 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                        </span>
                    </div>
                    
                    <div class="space-y-4 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-primary-200">Currency</span>
                            <span id="summary-currency" class="font-medium">INR (₹)</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-primary-200">GST Rate</span>
                            <span id="summary-tax" class="font-medium text-emerald-400">18.00%</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-primary-200">Shipping</span>
                            <span id="summary-shipping" class="font-medium text-amber-400">₹50.00</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-primary-200">Hero Media</span>
                            <span id="summary-bg-type" class="font-medium uppercase">Video</span>
                        </div>
                    </div>

                    <div class="pt-5 mt-5 border-t border-primary-800 dark:border-slate-700">
                        <a href="/" target="_blank" class="flex items-center justify-center w-full px-4 py-2 border border-primary-700 dark:border-slate-600 rounded-lg text-sm font-medium text-white hover:bg-primary-800 dark:hover:bg-slate-700 transition-colors">
                            <i class="ph ph-arrow-square-out mr-2"></i> Preview Storefront
                        </a>
                    </div>
                </div>
            </div>

            <!-- Helpful System Notice Card -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/50 p-5 rounded-xl">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="ph-fill ph-info text-blue-500 dark:text-blue-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Design Note</h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-400/90 leading-relaxed">
                            <p>For optimal mobile performance, compress background videos (MP4) to under 10MB or use optimized WebP images.</p>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </form>
</div>

<script src="/admin/assets/js/settings.js?v=<?php echo time(); ?>"></script>

<?php
include_once __DIR__ . '/includes/admin-footer.php';
?>
