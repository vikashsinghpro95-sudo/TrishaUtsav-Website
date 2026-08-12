<?php
require_once __DIR__ . '/includes/config.php';
include_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-[1800px] mx-auto px-4 md:px-[50px] my-6 sm:my-10">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar Navigation -->
        <div class="lg:col-span-1 bg-white p-5 rounded-3xl border border-[#f59e0b]/20 shadow-sm h-fit space-y-1">
            <!-- Sidebar User Profile Card -->
            <div class="px-2 pt-2 pb-5 text-center border-b border-gray-100 mb-3 space-y-3">
                <div class="relative w-20 h-20 mx-auto">
                    <img id="user-avatar-img" src="" alt="User Avatar border" class="w-20 h-20 rounded-full border-2 border-[#f59e0b] shadow-lg object-cover hidden mx-auto">
                    <div id="user-avatar-fallback" class="w-20 h-20 rounded-full bg-gradient-to-br from-[#990024] to-[#4a0011] text-[#f59e0b] font-display text-2xl font-black flex items-center justify-center border-2 border-[#f59e0b]/40 shadow-md mx-auto">
                        <span id="avatar-initials">U</span>
                    </div>
                    <span id="user-verified-badge" title="Verified Account" class="absolute bottom-0 right-0 w-6 h-6 bg-emerald-500 text-white rounded-full flex items-center justify-center text-[10px] border-2 border-white shadow-md">
                        <i class="fas fa-check"></i>
                    </span>
                </div>
                
                <div>
                    <span id="account-nav-name" class="font-display font-extrabold text-[#12090c] text-lg block">Customer</span>
                    <span id="account-nav-email" class="text-xs text-gray-500 font-medium block truncate max-w-[200px] mx-auto">customer@example.com</span>
                </div>
            </div>

            <button id="tab-btn-dashboard" class="w-full flex items-center space-x-3 px-4 py-3 bg-[#990024] text-[#fffdf7] font-extrabold text-xs uppercase tracking-wider rounded-2xl transition duration-150 shadow-md">
                <i class="fas fa-th-large w-5 text-center text-[#f59e0b]"></i>
                <span>Dashboard</span>
            </button>

            <button id="tab-btn-orders" class="w-full flex items-center space-x-3 px-4 py-3 text-slate-600 hover:bg-[#990024]/10 hover:text-[#990024] text-xs font-bold uppercase tracking-wider rounded-2xl transition duration-150">
                <i class="fas fa-box w-5 text-center text-[#f59e0b]"></i>
                <span>Orders History</span>
            </button>

            <button id="tab-btn-addresses" class="w-full flex items-center space-x-3 px-4 py-3 text-slate-600 hover:bg-[#990024]/10 hover:text-[#990024] text-xs font-bold uppercase tracking-wider rounded-2xl transition duration-150">
                <i class="fas fa-map-marker-alt w-5 text-center text-[#f59e0b]"></i>
                <span>Manage Addresses</span>
            </button>

            <button id="tab-btn-profile" class="w-full flex items-center space-x-3 px-4 py-3 text-slate-600 hover:bg-[#990024]/10 hover:text-[#990024] text-xs font-bold uppercase tracking-wider rounded-2xl transition duration-150">
                <i class="fas fa-user-cog w-5 text-center text-[#f59e0b]"></i>
                <span>Profile Settings</span>
            </button>

            <button id="tab-btn-password" class="w-full flex items-center space-x-3 px-4 py-3 text-slate-600 hover:bg-[#990024]/10 hover:text-[#990024] text-xs font-bold uppercase tracking-wider rounded-2xl transition duration-150">
                <i class="fas fa-key w-5 text-center text-[#f59e0b]"></i>
                <span>Change Password</span>
            </button>

            <button id="tab-btn-logout" class="w-full flex items-center space-x-3 px-4 py-3 text-red-500 hover:bg-red-50 text-xs font-extrabold uppercase tracking-wider rounded-2xl transition duration-150 pt-4 border-t border-gray-100">
                <i class="fas fa-sign-out-alt w-5 text-center"></i>
                <span>Sign Out</span>
            </button>
        </div>

        <!-- Content Sections -->
        <div class="lg:col-span-3">
            <!-- 1. Dashboard Tab -->
            <div id="tab-content-dashboard" class="space-y-6">
                <!-- Welcome Banner -->
                <div class="bg-gradient-to-r from-[#990024] via-[#7a001c] to-[#4a0011] p-6 sm:p-8 rounded-3xl text-[#fffdf7] shadow-xl border border-[#f59e0b]/30 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="space-y-1 text-center md:text-left">
                        <span class="bg-[#f59e0b] text-[#12090c] font-black uppercase text-[10px] px-3.5 py-1 rounded-full tracking-widest inline-block shadow-md">
                            WELCOME BACK
                        </span>
                        <h1 class="font-display text-2xl sm:text-3xl font-extrabold">Hello, <span id="dash-welcome-name">Customer</span>!</h1>
                    </div>

                    <div class="flex items-center space-x-4 bg-white/10 p-4 rounded-2xl border border-white/20 backdrop-blur-md">
                        <img id="dash-banner-avatar-img" src="" alt="Google Avatar" class="w-16 h-16 rounded-full border-2 border-[#f59e0b] object-cover hidden">
                        <div id="dash-banner-avatar-fallback" class="w-16 h-16 rounded-full bg-[#12090c] text-[#f59e0b] font-display text-xl font-black flex items-center justify-center border-2 border-[#f59e0b]/40">
                            <span id="dash-avatar-initials">U</span>
                        </div>
                        <div class="text-left text-xs">
                            <span class="font-bold text-[#f59e0b] block" id="dash-banner-phone">Phone Verified</span>
                            <span class="text-slate-300 block text-[11px]" id="dash-banner-email">email@example.com</span>
                            <span class="text-emerald-400 font-bold text-[10px] uppercase tracking-wider block mt-0.5"><i class="fas fa-[#shield-check]"></i> Active Member</span>
                        </div>
                    </div>
                </div>

                <div id="dash-metrics-grid" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Loaded dynamically -->
                </div>
            </div>

            <!-- 2. Profile Tab -->
            <div id="tab-content-profile" class="hidden bg-white p-6 sm:p-8 rounded-3xl border border-[#f59e0b]/20 shadow-sm space-y-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-4 border-b border-gray-100 gap-4">
                    <div>
                        <h2 class="font-display text-xl font-bold text-[#12090c] flex items-center">
                            <i class="fas fa-user-edit text-[#990024] mr-2.5"></i> Profile Information
                        </h2>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">Manage your personal details, email, and mobile phone number.</p>
                    </div>

                    <!-- Google Connection Badge -->
                    <div id="google-connected-badge" class="hidden bg-slate-50 border border-gray-200 px-3.5 py-1.5 rounded-full flex items-center space-x-2 shadow-xs">
                        <svg class="w-4 h-4" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                        </svg>
                        <span class="text-[11px] font-extrabold text-gray-700">Google Verified Profile</span>
                    </div>
                </div>

                <form id="frm-account-profile" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="profile-first-name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1 flex items-center">
                                <i class="fas fa-user text-gray-400 mr-1.5"></i> First Name
                            </label>
                            <input type="text" id="profile-first-name" required class="w-full px-4 py-3 border border-gray-200 rounded-xl text-xs font-bold outline-none focus:border-[#990024] transition text-gray-800">
                        </div>
                        <div>
                            <label for="profile-last-name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1 flex items-center">
                                <i class="fas fa-user text-gray-400 mr-1.5"></i> Last Name
                            </label>
                            <input type="text" id="profile-last-name" required class="w-full px-4 py-3 border border-gray-200 rounded-xl text-xs font-bold outline-none focus:border-[#990024] transition text-gray-800">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="profile-email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1 flex items-center">
                                <i class="fas fa-envelope text-gray-400 mr-1.5"></i> Email Address
                            </label>
                            <input type="email" id="profile-email" required class="w-full px-4 py-3 border border-gray-200 rounded-xl text-xs font-bold outline-none focus:border-[#990024] transition text-gray-800">
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label for="profile-phone" class="block text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center">
                                    <i class="fas fa-phone text-gray-400 mr-1.5"></i> Phone Number
                                </label>
                                <span id="profile-phone-status-badge" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 flex items-center">
                                    <i class="fas fa-check-circle mr-1"></i> Verified
                                </span>
                            </div>
                            <input type="tel" id="profile-phone" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-xs font-bold outline-none focus:border-[#990024] transition text-gray-800">
                        </div>
                    </div>

                    <div class="pt-2 flex justify-start">
                        <button type="submit" class="bg-[#990024] hover:bg-[#7a001c] text-[#fffdf7] font-extrabold text-xs uppercase tracking-widest py-3.5 px-8 rounded-full shadow-lg transition duration-200 border border-[#f59e0b]/30 flex items-center space-x-2">
                            <i class="fas fa-save"></i>
                            <span>Update Profile Information</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- 3. Addresses Tab -->
            <div id="tab-content-addresses" class="hidden space-y-6">
                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#f59e0b]/20 shadow-sm space-y-4">
                    <h2 class="font-display text-lg font-bold text-[#12090c] pb-3 border-b border-gray-100 flex items-center">
                        <i class="fas fa-map-marked-alt text-[#990024] mr-2"></i> Saved Delivery Addresses
                    </h2>
                    <div id="account-addresses-list">
                        <!-- Loaded dynamically -->
                    </div>
                </div>

                <div id="account-address-form-sec" class="bg-white p-6 sm:p-8 rounded-3xl border border-[#f59e0b]/20 shadow-sm space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                        <h2 id="addr-form-title" class="font-display text-base font-bold text-[#12090c]">Add New Address</h2>
                        <button id="btn-cancel-edit-addr" onclick="Account.resetAddressForm()" class="hidden text-xs text-red-500 font-bold hover:underline">Cancel Edit</button>
                    </div>
                    
                    <form id="frm-account-address" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="addr-type" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Address Type</label>
                                <select id="addr-type" required class="w-full px-4 py-2 border border-gray-200 rounded-xl text-xs outline-none bg-white font-medium text-gray-700">
                                    <option value="shipping">Shipping Address</option>
                                    <option value="billing">Billing Address</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label for="addr-name" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Receiver Name</label>
                                <input type="text" id="addr-name" required placeholder="e.g. Rahul Sharma" class="w-full px-4 py-2 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024]">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="addr-phone" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Contact Phone</label>
                                <input type="tel" id="addr-phone" required placeholder="e.g. 9876543210" class="w-full px-4 py-2 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024]">
                            </div>
                            <div>
                                <label for="addr-line1" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Address Line 1</label>
                                <input type="text" id="addr-line1" required placeholder="Flat / House No, Building" class="w-full px-4 py-2 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024]">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-2">
                                <label for="addr-line2" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Address Line 2 (Optional)</label>
                                <input type="text" id="addr-line2" placeholder="Street name, landmark" class="w-full px-4 py-2 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024]">
                            </div>
                            <div>
                                <label for="addr-city" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">City</label>
                                <input type="text" id="addr-city" required placeholder="e.g. Bengaluru" class="w-full px-4 py-2 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024]">
                            </div>
                            <div>
                                <label for="addr-state" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">State</label>
                                <input type="text" id="addr-state" required placeholder="e.g. Karnataka" class="w-full px-4 py-2 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024]">
                            </div>
                        </div>

                        <div class="flex items-center justify-between flex-wrap gap-4 pt-2">
                            <div class="w-full md:w-1/3">
                                <label for="addr-pincode" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Pincode</label>
                                <input type="text" id="addr-pincode" required placeholder="e.g. 560001" class="w-full px-4 py-2 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024]">
                            </div>
                            <label class="flex items-center text-xs text-gray-600 cursor-pointer">
                                <input type="checkbox" id="addr-default" class="h-4 w-4 text-[#990024] border-gray-300 rounded mr-2">
                                <span class="font-bold">Set as default delivery address</span>
                            </label>
                            <button type="submit" class="bg-[#990024] hover:bg-[#7a001c] text-white font-extrabold text-xs uppercase tracking-wider px-6 py-2.5 rounded-full shadow-md transition duration-200 border border-[#f59e0b]/30">
                                Save Address
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 4. Orders History Tab -->
            <div id="tab-content-orders" class="hidden bg-white p-6 sm:p-8 rounded-3xl border border-[#f59e0b]/20 shadow-sm space-y-6">
                <h2 class="font-display text-lg font-bold text-[#12090c] pb-3 border-b border-gray-100 flex items-center">
                    <i class="fas fa-history text-[#990024] mr-2"></i> Orders History
                </h2>
                <div id="account-orders-list">
                    <!-- Loaded dynamically -->
                </div>
            </div>

            <!-- 5. Change Password Tab -->
            <div id="tab-content-password" class="hidden bg-white p-6 sm:p-8 rounded-3xl border border-[#f59e0b]/20 shadow-sm space-y-6">
                <h2 class="font-display text-lg font-bold text-[#12090c] pb-3 border-b border-gray-100 flex items-center">
                    <i class="fas fa-lock text-[#990024] mr-2"></i> Change Password
                </h2>
                <form id="frm-account-password" class="space-y-4">
                    <div>
                        <label for="pass-current" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Current Password</label>
                        <div class="relative">
                            <input type="password" id="pass-current" required placeholder="••••••••" class="w-full px-4 py-2 pr-10 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024]">
                            <button type="button" onclick="togglePasswordVisibility('pass-current', this)" class="password-toggle-btn absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-[#990024] transition-colors focus:outline-none cursor-pointer" aria-label="Show password" title="Show password">
                                <i class="far fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="pass-new" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">New Password</label>
                        <div class="relative">
                            <input type="password" id="pass-new" required placeholder="Min. 8 characters" class="w-full px-4 py-2 pr-10 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024]">
                            <button type="button" onclick="togglePasswordVisibility('pass-new', this)" class="password-toggle-btn absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-[#990024] transition-colors focus:outline-none cursor-pointer" aria-label="Show password" title="Show password">
                                <i class="far fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="pass-confirm" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Confirm New Password</label>
                        <div class="relative">
                            <input type="password" id="pass-confirm" required placeholder="Repeat new password" class="w-full px-4 py-2 pr-10 border border-gray-200 rounded-xl text-xs outline-none focus:border-[#990024]">
                            <button type="button" onclick="togglePasswordVisibility('pass-confirm', this)" class="password-toggle-btn absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-[#990024] transition-colors focus:outline-none cursor-pointer" aria-label="Show password" title="Show password">
                                <i class="far fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="bg-[#990024] hover:bg-[#7a001c] text-white font-extrabold text-xs uppercase tracking-wider py-3 px-6 rounded-full shadow-md transition duration-200 border border-[#f59e0b]/30">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/account.js"></script>

<?php
include_once __DIR__ . '/includes/footer.php';
?>
