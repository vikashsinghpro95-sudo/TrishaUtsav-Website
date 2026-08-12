/**
 * Admin Settings Management Module
 */

const Settings = {
    async init() {
        await this.loadSettings();

        const form = document.getElementById('frm-settings-save');
        if (form) {
            form.addEventListener('submit', (e) => this.saveSettings(e));
        }
    },

    async loadSettings() {
        try {
            const res = await Api.get('/admin/settings');
            if (res.success && res.data) {
                const s = res.data;
                document.getElementById('set-store-name').value = s.store_name || '';
                document.getElementById('set-contact-email').value = s.contact_email || '';
                document.getElementById('set-contact-phone').value = s.contact_phone || '';
                document.getElementById('set-currency').value = s.currency || 'INR';
                document.getElementById('set-tax-rate').value = s.tax_rate || '18.00';
                document.getElementById('set-shipping-fee').value = s.shipping_fee || '50.00';
                
                document.getElementById('set-store-logo').value = s.store_logo || '';
                const bgType = s.hero_bg_type || 'video';
                document.getElementById('set-hero-bg-type').value = bgType;
                document.getElementById('set-hero-video-url').value = s.hero_video_url || '';
                document.getElementById('set-hero-image-url').value = s.hero_image_url || '';
                this.toggleBgType(bgType);

                document.getElementById('set-hero-headline').value = s.hero_headline || '';
                document.getElementById('set-hero-description').value = s.hero_description || '';
                document.getElementById('set-hero-cta-text').value = s.hero_cta_text || '';
                document.getElementById('set-hero-cta-link').value = s.hero_cta_link || '';
                document.getElementById('set-hero-overlay-opacity').value = s.hero_overlay_opacity || '0.35';
                document.getElementById('set-hero-overlay-color').value = s.hero_overlay_color || '#000000';

                // Populate Hero Bestseller Product Options
                const prodSelect = document.getElementById('set-hero-bestseller-product-id');
                if (prodSelect) {
                    try {
                        const prodRes = await Api.get('/products?per_page=100');
                        if (prodRes.success && prodRes.data) {
                            let options = '<option value="">-- Automatic (First Must-Buy Product) --</option>';
                            prodRes.data.forEach(p => {
                                const selected = (String(p.id) === String(s.hero_bestseller_product_id)) ? 'selected' : '';
                                options += `<option value="${p.id}" ${selected}>${p.name} - ₹${parseFloat(p.price).toLocaleString('en-IN')}</option>`;
                            });
                            prodSelect.innerHTML = options;
                        }
                    } catch (pe) {}
                    prodSelect.value = s.hero_bestseller_product_id || '';
                }

                // Mobile Hero Customizer
                const mobileBgType = s.hero_mobile_bg_type || 'desktop';
                document.getElementById('set-hero-mobile-bg-type').value = mobileBgType;
                document.getElementById('set-hero-mobile-height').value = s.hero_mobile_height || 'medium';
                document.getElementById('set-hero-mobile-video-url').value = s.hero_mobile_video_url || '';
                document.getElementById('set-hero-mobile-image-url').value = s.hero_mobile_image_url || '';
                this.toggleMobileBgType(mobileBgType);

                document.getElementById('set-hangings-enabled').value = s.hero_hangings_enabled || 'true';
                document.getElementById('set-hangings-type').value = s.hero_hangings_type || 'mixed';
                document.getElementById('set-hangings-count').value = s.hero_hangings_count || '6';
                document.getElementById('set-hangings-gravity').value = s.hero_hangings_gravity || '1.0';

                // Footer Customizations
                document.getElementById('set-footer-about-text').value = s.footer_about_text || '';
                document.getElementById('set-footer-address').value = s.footer_address || '';
                document.getElementById('set-footer-operating-hours').value = s.footer_operating_hours || '';
                document.getElementById('set-footer-social-instagram').value = s.footer_social_instagram || '';
                document.getElementById('set-footer-social-facebook').value = s.footer_social_facebook || '';
                document.getElementById('set-footer-social-whatsapp').value = s.footer_social_whatsapp || '';
                document.getElementById('set-footer-social-youtube').value = s.footer_social_youtube || '';
                document.getElementById('set-footer-decorations-enabled').value = s.footer_decorations_enabled || 'true';
                document.getElementById('set-footer-copyright-text').value = s.footer_copyright_text || '';

                // Festive Sale Countdown Timer Customizations
                document.getElementById('set-timer-section-enabled').value = s.timer_section_enabled || 'true';
                document.getElementById('set-timer-badge-text').value = s.timer_badge_text || '🪔 LIMITED TIME FESTIVE SALE';
                document.getElementById('set-timer-headline').value = s.timer_headline || 'Up to 60% off festive collection';
                document.getElementById('set-timer-description').value = s.timer_description || 'Elevate your home celebrations with authentic brass diyas, handcrafted sweets, pure silver pooja thalis, and royal celebration hampers.';
                document.getElementById('set-timer-cta-text').value = s.timer_cta_text || 'CLAIM FESTIVE OFFERS';
                document.getElementById('set-timer-cta-link').value = s.timer_cta_link || 'shop';

                let targetDateVal = s.timer_target_date || '';
                if (targetDateVal) {
                    targetDateVal = targetDateVal.replace(' ', 'T').slice(0, 16);
                } else {
                    const d = new Date();
                    d.setDate(d.getDate() + 3);
                    targetDateVal = d.toISOString().slice(0, 16);
                }
                document.getElementById('set-timer-target-date').value = targetDateVal;

                // Update summary card badges
                if (document.getElementById('summary-currency')) document.getElementById('summary-currency').innerText = (s.currency || 'INR');
                if (document.getElementById('summary-tax')) document.getElementById('summary-tax').innerText = (s.tax_rate || '18.00') + '%';
                if (document.getElementById('summary-shipping')) document.getElementById('summary-shipping').innerText = '₹' + (s.shipping_fee || '50.00');
                if (document.getElementById('summary-bg-type')) document.getElementById('summary-bg-type').innerText = bgType.toUpperCase();
            }
        } catch (e) {
            Utils.showToast("Failed to fetch store settings: " + e.message, "error");
        }
    },

    toggleBgType(type) {
        const boxVideo = document.getElementById('box-hero-video');
        const boxImage = document.getElementById('box-hero-image');
        if (type === 'image') {
            boxVideo.classList.add('hidden');
            boxImage.classList.remove('hidden');
        } else {
            boxVideo.classList.remove('hidden');
            boxImage.classList.add('hidden');
        }
        if (document.getElementById('summary-bg-type')) {
            document.getElementById('summary-bg-type').innerText = type.toUpperCase();
        }
    },

    toggleMobileBgType(type) {
        const boxVideo = document.getElementById('box-hero-mobile-video');
        const boxImage = document.getElementById('box-hero-mobile-image');
        if (!boxVideo || !boxImage) return;

        if (type === 'video') {
            boxVideo.classList.remove('hidden');
            boxImage.classList.add('hidden');
        } else if (type === 'image') {
            boxVideo.classList.add('hidden');
            boxImage.classList.remove('hidden');
        } else {
            boxVideo.classList.add('hidden');
            boxImage.classList.add('hidden');
        }
    },

    async uploadMobileMedia(input, type) {
        const targetInputId = type === 'video' ? 'set-hero-mobile-video-url' : 'set-hero-mobile-image-url';
        await this.uploadAsset(input, type, targetInputId);
    },

    async saveSettings(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-save-settings');
        btn.disabled = true;

        const payload = {
            store_name: document.getElementById('set-store-name').value.trim(),
            contact_email: document.getElementById('set-contact-email').value.trim(),
            contact_phone: document.getElementById('set-contact-phone').value.trim(),
            currency: document.getElementById('set-currency').value,
            tax_rate: parseFloat(document.getElementById('set-tax-rate').value) || 0.00,
            shipping_fee: parseFloat(document.getElementById('set-shipping-fee').value) || 0.00,
            
            store_logo: document.getElementById('set-store-logo').value.trim(),
            hero_bg_type: document.getElementById('set-hero-bg-type').value,
            hero_video_url: document.getElementById('set-hero-video-url').value.trim(),
            hero_image_url: document.getElementById('set-hero-image-url').value.trim(),
            hero_headline: document.getElementById('set-hero-headline').value.trim(),
            hero_description: document.getElementById('set-hero-description').value.trim(),
            hero_cta_text: document.getElementById('set-hero-cta-text').value.trim(),
            hero_cta_link: document.getElementById('set-hero-cta-link').value.trim(),
            hero_overlay_opacity: parseFloat(document.getElementById('set-hero-overlay-opacity').value) || 0.35,
            hero_overlay_color: document.getElementById('set-hero-overlay-color').value.trim(),
            hero_bestseller_product_id: document.getElementById('set-hero-bestseller-product-id') ? document.getElementById('set-hero-bestseller-product-id').value : '',

            hero_mobile_bg_type: document.getElementById('set-hero-mobile-bg-type').value,
            hero_mobile_height: document.getElementById('set-hero-mobile-height').value,
            hero_mobile_video_url: document.getElementById('set-hero-mobile-video-url').value.trim(),
            hero_mobile_image_url: document.getElementById('set-hero-mobile-image-url').value.trim(),

            hero_hangings_enabled: document.getElementById('set-hangings-enabled').value,
            hero_hangings_type: document.getElementById('set-hangings-type').value,
            hero_hangings_count: parseInt(document.getElementById('set-hangings-count').value) || 6,
            hero_hangings_gravity: parseFloat(document.getElementById('set-hangings-gravity').value) || 1.0,

            footer_about_text: document.getElementById('set-footer-about-text').value.trim(),
            footer_address: document.getElementById('set-footer-address').value.trim(),
            footer_operating_hours: document.getElementById('set-footer-operating-hours').value.trim(),
            footer_social_instagram: document.getElementById('set-footer-social-instagram').value.trim(),
            footer_social_facebook: document.getElementById('set-footer-social-facebook').value.trim(),
            footer_social_whatsapp: document.getElementById('set-footer-social-whatsapp').value.trim(),
            footer_social_youtube: document.getElementById('set-footer-social-youtube').value.trim(),
            footer_decorations_enabled: document.getElementById('set-footer-decorations-enabled').value,
            footer_copyright_text: document.getElementById('set-footer-copyright-text').value.trim(),

            // Festive Sale Countdown Timer
            timer_section_enabled: document.getElementById('set-timer-section-enabled').value,
            timer_target_date: document.getElementById('set-timer-target-date').value,
            timer_badge_text: document.getElementById('set-timer-badge-text').value.trim(),
            timer_headline: document.getElementById('set-timer-headline').value.trim(),
            timer_description: document.getElementById('set-timer-description').value.trim(),
            timer_cta_text: document.getElementById('set-timer-cta-text').value.trim(),
            timer_cta_link: document.getElementById('set-timer-cta-link').value.trim()
        };

        try {
            await Api.put('/admin/settings', payload);
            Utils.showToast("Store settings saved successfully!", "success");
            await this.loadSettings();
        } catch (err) {
            Utils.showToast(err.message || "Failed to save settings.", "error");
        } finally {
            btn.disabled = false;
        }
    },

    async uploadAsset(input, type, targetInputId) {
        if (!input.files || input.files.length === 0) return;
        const file = input.files[0];
        
        const textInput = document.getElementById(targetInputId);
        const originalPlaceholder = textInput.placeholder;
        textInput.placeholder = "Uploading file, please wait...";
        textInput.value = "";

        const formData = new FormData();
        formData.append('file', file);
        formData.append('type', type);

        try {
            const token = localStorage.getItem('admin_token');
            const resRaw = await fetch(API_BASE_URL + '/admin/media/upload', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                body: formData
            });
            const res = await resRaw.json();
            
            if (res.success && res.path) {
                textInput.value = res.path;
                Utils.showToast("File uploaded successfully!", "success");
            } else {
                throw new Error(res.message || "Upload failed.");
            }
        } catch (e) {
            textInput.placeholder = originalPlaceholder;
            Utils.showToast("Failed to upload: " + e.message, "error");
        } finally {
            input.value = "";
        }
    }
};

window.Settings = Settings;
document.addEventListener('DOMContentLoaded', () => {
    Settings.init();
});
