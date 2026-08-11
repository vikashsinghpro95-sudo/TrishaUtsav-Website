<?php
require_once __DIR__ . '/includes/config.php';
include_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-md mx-auto my-12 bg-white rounded-3xl shadow-xl border border-[#f59e0b]/30 overflow-hidden">
    <!-- Header Logo Banner -->
    <div class="bg-gradient-to-r from-[#990024] via-[#7a001c] to-[#4a0011] p-6 text-center text-[#fffdf7] border-b border-[#f59e0b]/30">
        <div class="w-12 h-12 rounded-full bg-[#12090c] text-[#f59e0b] font-display text-2xl font-black flex items-center justify-center mx-auto border border-[#f59e0b]/40 mb-2">
            त्रि
        </div>
        <h1 class="font-display text-2xl font-bold text-[#fffdf7]">Trisha<span class="text-[#f59e0b]">Utsav</span></h1>
        <p class="text-[10px] text-slate-300 uppercase tracking-widest font-semibold mt-0.5">Royal Club Portal</p>
    </div>

    <!-- Tabs Header -->
    <div class="flex border-b border-gray-100 bg-slate-50">
        <button id="tab-login-btn" class="w-1/2 py-3.5 text-center font-extrabold text-xs uppercase tracking-wider border-b-2 border-[#990024] text-[#990024] focus:outline-none transition">
            Sign In
        </button>
        <button id="tab-register-btn" class="w-1/2 py-3.5 text-center font-extrabold text-xs uppercase tracking-wider border-b border-gray-200 text-gray-500 hover:text-[#990024] focus:outline-none transition">
            Create Account
        </button>
    </div>

    <!-- Forms Body -->
    <div class="p-6 sm:p-8">
        <!-- Google Social Login Option -->
        <div class="mb-6 space-y-4">
            <button type="button" onclick="Auth.googleAuthPopup()" class="w-full bg-white hover:bg-gray-50 text-gray-700 font-bold text-xs uppercase tracking-wider py-3 px-4 rounded-full border border-gray-200 shadow-sm flex items-center justify-center space-x-3 transition active:scale-98">
                <svg class="w-4 h-4" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                <span>Continue with Google</span>
            </button>

            <div class="relative flex py-1 items-center">
                <div class="flex-grow border-t border-gray-200"></div>
                <span class="flex-shrink mx-3 text-[10px] text-gray-400 uppercase font-extrabold tracking-widest">or email authentication</span>
                <div class="flex-grow border-t border-gray-200"></div>
            </div>
        </div>

        <!-- 1. Sign In Container -->
        <div id="login-form-container">
            <h2 class="font-display text-lg font-extrabold text-[#12090c] mb-4 text-center">Sign In to Your Account</h2>
            <form id="frm-login" class="space-y-4">
                <div>
                    <label for="login-email" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Email Address</label>
                    <input type="email" id="login-email" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024] transition" placeholder="you@example.com">
                </div>
                
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label for="login-password" class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Password</label>
                        <a href="#" class="text-[10px] text-[#990024] font-bold hover:underline">Forgot password?</a>
                    </div>
                    <input type="password" id="login-password" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024] transition" placeholder="••••••••">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="login-remember" class="h-4 w-4 text-[#990024] border-gray-300 rounded">
                    <label for="login-remember" class="ml-2 text-xs text-gray-600 font-medium">Remember me on this device</label>
                </div>

                <button type="submit" class="w-full bg-[#990024] hover:bg-[#7a001c] text-[#fffdf7] font-extrabold text-xs uppercase tracking-widest py-3.5 px-4 rounded-full shadow-lg transition duration-200 border border-[#f59e0b]/30">
                    SIGN IN TO ACCOUNT
                </button>
            </form>
        </div>

        <!-- 2. Create Account Container -->
        <div id="register-form-container" class="hidden">
            <h2 class="font-display text-lg font-extrabold text-[#12090c] mb-4 text-center">Create Your Royal Account</h2>
            <form id="frm-register" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="reg-first-name" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">First Name</label>
                        <input type="text" id="reg-first-name" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024] transition" placeholder="Rahul">
                    </div>
                    <div>
                        <label for="reg-last-name" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Last Name</label>
                        <input type="text" id="reg-last-name" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024] transition" placeholder="Sharma">
                    </div>
                </div>

                <div>
                    <label for="reg-email" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Email Address</label>
                    <input type="email" id="reg-email" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024] transition" placeholder="rahul@example.com">
                </div>

                <div>
                    <label for="reg-phone" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Phone Number (Optional)</label>
                    <input type="tel" id="reg-phone" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024] transition" placeholder="9876543210">
                </div>

                <div>
                    <label for="reg-password" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Password</label>
                    <input type="password" id="reg-password" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024] transition" placeholder="Min. 8 characters">
                </div>

                <div>
                    <label for="reg-confirm-password" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Confirm Password</label>
                    <input type="password" id="reg-confirm-password" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024] transition" placeholder="Repeat password">
                </div>

                <button type="submit" class="w-full bg-[#990024] hover:bg-[#7a001c] text-[#fffdf7] font-extrabold text-xs uppercase tracking-widest py-3.5 px-4 rounded-full shadow-lg transition duration-200 border border-[#f59e0b]/30">
                    CREATE ROYAL ACCOUNT
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Firebase Web SDK Compat scripts for Google Sign-In -->
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-auth-compat.js"></script>

<script>
    const firebaseConfig = {
      apiKey: "AIzaSyA-ufMQcBtunQWXWKZ6wK3w_aHuheh3BYc",
      authDomain: "trisha-utsav.firebaseapp.com",
      projectId: "trisha-utsav",
      storageBucket: "trisha-utsav.firebasestorage.app",
      messagingSenderId: "861085977741",
      appId: "1:861085977741:web:6f82acb1471183b64a66c4",
      measurementId: "G-RJZW1688SW"
    };
    if (typeof firebase !== 'undefined' && !firebase.apps.length) {
        firebase.initializeApp(firebaseConfig);
    }

    if (localStorage.getItem('auth_token')) {
        const params = new URLSearchParams(window.location.search);
        let redirect = params.get('redirect');
        
        // Prevent Open Redirect (V5) by blocking external URLs
        if (redirect) {
            redirect = decodeURIComponent(redirect);
            if (redirect.includes('://') || redirect.startsWith('//')) {
                redirect = null;
            }
        }
        
        // Strip leading slash if BASE_URL already handles it
        window.location.href = redirect ? BASE_URL + redirect.replace(/^\/+/, '') : BASE_URL + 'account.php';
    }
</script>

<script src="<?php echo BASE_URL; ?>assets/js/auth.js?v=<?php echo time(); ?>"></script>

<?php
include_once __DIR__ . '/includes/footer.php';
?>
