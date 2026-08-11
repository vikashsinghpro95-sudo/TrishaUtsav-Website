/**
 * E-Commerce Customer Authentication Module
 */

const Auth = {
    /**
     * Authenticate customer credentials
     *
     * @param {string} email
     * @param {string} password
     */
    async login(email, password) {
        try {
            const res = await Api.post('/auth/login', { email, password });
            
            if (res.success && res.token) {
                localStorage.setItem('auth_token', res.token);
                
                // Fetch profile to store customer context details
                const profile = await Api.get('/auth/me');
                localStorage.setItem('auth_user', JSON.stringify(profile.user));
                
                await this.syncGuestCart();

                Utils.showToast("Login successful!", "success");
                
                if (!profile.user.phone || parseInt(profile.user.is_phone_verified) === 0) {
                    setTimeout(() => {
                        this.showPhoneOtpModal();
                    }, 500);
                } else {
                    setTimeout(() => {
                        const params = new URLSearchParams(window.location.search);
                        const redirect = params.get('redirect');
                        window.location.href = redirect ? BASE_URL + redirect : BASE_URL + 'account.php';
                    }, 1000);
                }
            }
        } catch (e) {
            Utils.showToast(e.message || "Invalid credentials.", "error");
        }
    },

    async register(data) {
        try {
            const res = await Api.post('/auth/register', data);
            
            if (res.success && res.token) {
                localStorage.setItem('auth_token', res.token);
                
                // Fetch profile context
                const profile = await Api.get('/auth/me');
                localStorage.setItem('auth_user', JSON.stringify(profile.user));
                
                await this.syncGuestCart();

                Utils.showToast("Registration completed! Welcome.", "success");
                
                if (!profile.user.phone || parseInt(profile.user.is_phone_verified) === 0) {
                    setTimeout(() => {
                        this.showPhoneOtpModal();
                    }, 500);
                } else {
                    setTimeout(() => {
                        const params = new URLSearchParams(window.location.search);
                        const redirect = params.get('redirect');
                        window.location.href = redirect ? BASE_URL + redirect : BASE_URL + 'account.php';
                    }, 1000);
                }
            }
        } catch (e) {
            if (e.errors) {
                const errMsgs = Object.values(e.errors).flat();
                Utils.showToast(errMsgs[0] || "Registration failed.", "error");
            } else {
                Utils.showToast(e.message || "Registration failed.", "error");
            }
        }
    },

    /**
     * Trigger Google Auth Popup via Firebase or Interactive Fallback
     */
    async googleAuthPopup() {
        try {
            if (typeof firebase !== 'undefined' && firebase.auth) {
                if (!firebase.apps.length) {
                    firebase.initializeApp({
                      apiKey: "AIzaSyA-ufMQcBtunQWXWKZ6wK3w_aHuheh3BYc",
                      authDomain: "trisha-utsav.firebaseapp.com",
                      projectId: "trisha-utsav",
                      storageBucket: "trisha-utsav.firebasestorage.app",
                      messagingSenderId: "861085977741",
                      appId: "1:861085977741:web:6f82acb1471183b64a66c4",
                      measurementId: "G-RJZW1688SW"
                    });
                }
                const provider = new firebase.auth.GoogleAuthProvider();
                const result = await firebase.auth().signInWithPopup(provider);
                const gUser = result.user;
                
                const payload = {
                    email: gUser.email,
                    name: gUser.displayName,
                    avatar: gUser.photoURL,
                    google_uid: gUser.uid
                };
                await this.submitGoogleAuth(payload);
            } else {
                const email = prompt("Enter your Google Account email address:");
                if (!email) return;
                const name = prompt("Enter your Full Name:", "Google Member") || "Google Member";
                
                const payload = {
                    email: email,
                    name: name,
                    avatar: "https://lh3.googleusercontent.com/a/default-user=s96-c"
                };
                await this.submitGoogleAuth(payload);
            }
        } catch (e) {
            console.error("Google auth error:", e);
            Utils.showToast(e.message || "Google Sign-In cancelled.", "error");
        }
    },

    /**
     * Submit Google User details to backend /api/auth/google
     */
    async submitGoogleAuth(payload) {
        try {
            const res = await Api.post('/auth/google', payload);
            if (res.success && res.token) {
                localStorage.setItem('auth_token', res.token);
                localStorage.setItem('auth_user', JSON.stringify(res.user));
                
                await this.syncGuestCart();

                Utils.showToast("Authenticated via Google successfully!", "success");

                if (res.requires_phone || !res.user.phone || parseInt(res.user.is_phone_verified) === 0) {
                    setTimeout(() => {
                        this.showPhoneOtpModal();
                    }, 500);
                } else {
                    setTimeout(() => {
                        const params = new URLSearchParams(window.location.search);
                        const redirect = params.get('redirect');
                        window.location.href = redirect ? BASE_URL + redirect : BASE_URL + 'account.php';
                    }, 1000);
                }
            }
        } catch (e) {
            Utils.showToast(e.message || "Google Sign-In failed.", "error");
        }
    },

    /**
     * Show Phone & OTP Modal
     */
    showPhoneOtpModal() {
        const modal = document.getElementById('modal-phone-otp');
        if (!modal) return;
        modal.classList.remove('hidden');
        
        const phoneInput = document.getElementById('otp-phone-input');
        const currentUser = this.getCurrentUser();
        if (currentUser && currentUser.phone && phoneInput) {
            // Strip any existing country code if it starts with 91, etc (simplified for UI)
            let p = currentUser.phone.replace(/[^\d]/g, '');
            if (p.length > 10 && p.startsWith('91')) p = p.substring(2);
            phoneInput.value = p;
        }
    },

    /**
     * Close Phone & OTP Modal
     */
    closeOtpModal() {
        const modal = document.getElementById('modal-phone-otp');
        if (modal) modal.classList.add('hidden');
    },

    /**
     * Reset OTP modal back to phone step
     */
    resetOtpStep() {
        document.getElementById('otp-step-code').classList.add('hidden');
        document.getElementById('otp-step-phone').classList.remove('hidden');
        const input = document.getElementById('otp-phone-input');
        if (input) input.focus();
    },

    /**
     * Send OTP code to user's phone via MSG91 Javascript Widget
     */
    async sendPhoneOtp() {
        const ccode = document.getElementById('otp-country-code').value;
        const input = document.getElementById('otp-phone-input');
        const rawPhone = (input ? input.value : '').replace(/[^\d]/g, '');

        if (rawPhone.length < 5) {
            Utils.showToast("Please enter a valid mobile number.", "warning");
            return;
        }

        const fullPhone = ccode + rawPhone;
        const btn = document.getElementById('btn-send-otp');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        }

        if (typeof window.sendOtp !== 'function') {
            Utils.showToast("OTP Service is still loading, please try again.", "warning");
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Get Security Code <i class="fas fa-arrow-right ml-2 opacity-80"></i>';
            }
            return;
        }

        window.sendOtp(
            fullPhone,
            (data) => {
                Utils.showToast("OTP Sent Successfully!", "info");
                
                document.getElementById('otp-target-code').innerText = ccode;
                document.getElementById('otp-target-phone').innerText = " " + rawPhone;
                
                document.getElementById('otp-step-phone').classList.add('hidden');
                document.getElementById('otp-step-code').classList.remove('hidden');
                
                for (let i = 1; i <= 6; i++) {
                    const el = document.getElementById('otp-digit-' + i);
                    if (el) el.value = '';
                }
                
                const d1 = document.getElementById('otp-digit-1');
                if (d1) d1.focus();

                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = 'Get Security Code <i class="fas fa-arrow-right ml-2 opacity-80"></i>';
                }
            },
            (error) => {
                Utils.showToast(error.message || "Failed to send OTP.", "error");
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = 'Get Security Code <i class="fas fa-arrow-right ml-2 opacity-80"></i>';
                }
            }
        );
    },

    /**
     * Focus helper for 6-digit OTP box inputs
     */
    focusNextOtpInput(currentIdx) {
        const curr = document.getElementById('otp-digit-' + currentIdx);
        if (curr && curr.value.length === 1 && currentIdx < 6) {
            const next = document.getElementById('otp-digit-' + (currentIdx + 1));
            if (next) next.focus();
        }
    },

    /**
     * Verify OTP code via MSG91 Javascript Widget
     */
    async verifyPhoneOtp() {
        const ccode = document.getElementById('otp-country-code').value;
        const input = document.getElementById('otp-phone-input');
        const rawPhone = (input ? input.value : '').replace(/[^\d]/g, '');
        const fullPhone = ccode + rawPhone;
        
        let otp = '';
        for (let i = 1; i <= 6; i++) {
            const el = document.getElementById('otp-digit-' + i);
            if (el) otp += el.value;
        }

        if (otp.length < 4) {
            Utils.showToast("Please enter the complete OTP code.", "warning");
            return;
        }

        const btn = document.getElementById('btn-verify-otp');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        }

        if (typeof window.verifyOtp !== 'function') {
            Utils.showToast("OTP Service error, please reload page.", "error");
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Verify & Continue <i class="fas fa-check ml-2 opacity-80"></i>';
            }
            return;
        }

        window.verifyOtp(
            otp,
            async (data) => {
                // Success! Pass JWT token and phone to backend to finalize verification
                try {
                    const res = await Api.post('/auth/verify-msg91-token', { 
                        token: data.message || data.token || "verified",
                        phone: fullPhone 
                    });
                    if (res.success) {
                        Utils.showToast("Phone number verified successfully!", "success");
                        if (res.user) {
                            localStorage.setItem('auth_user', JSON.stringify(res.user));
                        }
                        await Auth.syncGuestCart();
                        Auth.closeOtpModal();
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        Utils.showToast(res.message || "Verification failed on server.", "error");
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = 'Verify & Continue <i class="fas fa-check ml-2 opacity-80"></i>';
                        }
                    }
                } catch (e) {
                    Utils.showToast(e.message || "Invalid OTP or server error.", "error");
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = 'Verify & Continue <i class="fas fa-check ml-2 opacity-80"></i>';
                    }
                }
            },
            (error) => {
                Utils.showToast(error.message || "Invalid OTP entered.", "error");
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = 'Verify & Continue <i class="fas fa-check ml-2 opacity-80"></i>';
                }
            }
        );
    },

    /**
     * Terminate customer session
     */
    async logout() {
        try {
            await Api.post('/auth/logout');
        } catch (e) {
        }
        
        localStorage.removeItem('auth_token');
        localStorage.removeItem('auth_user');
        Utils.showToast("Logged out successfully.", "success");
        
        setTimeout(() => {
            window.location.href = BASE_URL + 'index.php';
        }, 1000);
    },

    /**
     * Check if a token session exists
     *
     * @return {boolean}
     */
    isLoggedIn() {
        return localStorage.getItem('auth_token') !== null;
    },

    /**
     * Retrieve active customer info
     *
     * @return {object|null}
     */
    getCurrentUser() {
        const user = localStorage.getItem('auth_user');
        return user ? JSON.parse(user) : null;
    },

    /**
     * Sync local guest cart items to the database cart
     */
    async syncGuestCart() {
        try {
            const guestCart = JSON.parse(localStorage.getItem('guest_cart')) || [];
            if (guestCart.length === 0) return;

            // Push each item sequentially or in parallel
            // Note: API expects product_id, quantity, attributes
            const promises = guestCart.map(item => {
                return Api.post('/cart/add', {
                    product_id: item.product_id,
                    quantity: item.quantity,
                    attributes: item.attributes || {}
                }).catch(e => console.warn('Failed to sync item:', e));
            });

            await Promise.all(promises);
            
            // Clear local cart
            localStorage.removeItem('guest_cart');
            if (window.CartModule) {
                window.CartModule.updateCartBadge();
            }
        } catch (e) {
            console.error('Failed to sync guest cart', e);
        }
    }
};

window.Auth = Auth;

// DOM Load Bindings
document.addEventListener('DOMContentLoaded', () => {
    const loginTabBtn = document.getElementById('tab-login-btn');
    const registerTabBtn = document.getElementById('tab-register-btn');
    const loginForm = document.getElementById('login-form-container');
    const registerForm = document.getElementById('register-form-container');

    if (loginTabBtn && registerTabBtn) {
        loginTabBtn.addEventListener('click', () => {
            loginTabBtn.className = "w-1/2 py-3.5 text-center font-extrabold text-xs uppercase tracking-wider border-b-2 border-[#990024] text-[#990024] focus:outline-none transition";
            registerTabBtn.className = "w-1/2 py-3.5 text-center font-extrabold text-xs uppercase tracking-wider border-b border-gray-200 text-gray-500 hover:text-[#990024] focus:outline-none transition";
            loginForm.classList.remove('hidden');
            registerForm.classList.add('hidden');
        });

        registerTabBtn.addEventListener('click', () => {
            registerTabBtn.className = "w-1/2 py-3.5 text-center font-extrabold text-xs uppercase tracking-wider border-b-2 border-[#990024] text-[#990024] focus:outline-none transition";
            loginTabBtn.className = "w-1/2 py-3.5 text-center font-extrabold text-xs uppercase tracking-wider border-b border-gray-200 text-gray-500 hover:text-[#990024] focus:outline-none transition";
            registerForm.classList.remove('hidden');
            loginForm.classList.add('hidden');
        });
    }

    const loginFormEl = document.getElementById('frm-login');
    if (loginFormEl) {
        loginFormEl.addEventListener('submit', (e) => {
            e.preventDefault();
            const email = document.getElementById('login-email').value;
            const pass = document.getElementById('login-password').value;
            Auth.login(email, pass);
        });
    }

    const registerFormEl = document.getElementById('frm-register');
    if (registerFormEl) {
        registerFormEl.addEventListener('submit', (e) => {
            e.preventDefault();
            const pass = document.getElementById('reg-password').value;
            const confirmPass = document.getElementById('reg-confirm-password').value;

            if (pass !== confirmPass) {
                Utils.showToast("Passwords do not match.", "error");
                return;
            }

            const data = {
                first_name: document.getElementById('reg-first-name').value,
                last_name: document.getElementById('reg-last-name').value,
                email: document.getElementById('reg-email').value,
                phone: document.getElementById('reg-phone').value,
                password: pass
            };

            Auth.register(data);
        });
    }
});

// Initialize MSG91 Exposed Methods
(function initMsg91() {
    window.msg91Configuration = {
        widgetId: "3668666a356c373537343937",
        tokenAuth: "557819TzYfWmNwzC6a7468cfP1",
        exposeMethods: true,
        success: (data) => {
            console.log('MSG91 Widget success:', data);
        },
        failure: (error) => {
            console.error('MSG91 Widget failure:', error);
        }
    };

    const s = document.createElement('script');
    s.src = "https://verify.msg91.com/otp-provider.js";
    s.async = true;
    s.onload = () => {
        if (typeof window.initSendOTP === 'function') {
            window.initSendOTP(window.msg91Configuration);
        }
    };
    document.head.appendChild(s);
})();
