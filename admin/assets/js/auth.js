/**
 * Admin Panel Authentication Module
 */

const Auth = {
    /**
     * Authenticate admin credentials
     *
     * @param {string} email
     * @param {string} password
     */
    async adminLogin(email, password) {
        try {
            const res = await Api.post('/auth/admin/login', { email, password });
            
            if (res.success && res.token) {
                localStorage.setItem('admin_token', res.token);
                
                // Fetch profile to verify and store admin context
                const profile = await Api.get('/auth/me');
                
                if (!profile.user || parseInt(profile.user.role_id) !== 1) {
                    localStorage.removeItem('admin_token');
                    localStorage.removeItem('admin_user');
                    throw new Error("Access denied. Admin privileges required.");
                }

                localStorage.setItem('admin_user', JSON.stringify(profile.user));
                Utils.showToast("Authentication successful! Redirecting...", "success");
                
                setTimeout(() => {
                    window.location.replace('/admin/index.php');
                }, 400);
            }
        } catch (e) {
            localStorage.removeItem('admin_token');
            localStorage.removeItem('admin_user');
            Utils.showToast(e.message || "Invalid admin credentials.", "error");
        }
    },

    /**
     * Terminate admin session
     */
    async adminLogout() {
        try {
            await Api.post('/auth/logout');
        } catch (e) {
            // Proceed even if backend revocation fails
        }
        
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_user');
        Utils.showToast("Logged out successfully.", "success");
        
        setTimeout(() => {
            window.location.href = '/admin/login.php';
        }, 1000);
    },

    /**
     * Check if administrator token exists
     *
     * @return {boolean}
     */
    isLoggedIn() {
        return localStorage.getItem('admin_token') !== null;
    },

    /**
     * Get active admin user profile details
     *
     * @return {object|null}
     */
    getCurrentAdmin() {
        const user = localStorage.getItem('admin_user');
        return user ? JSON.parse(user) : null;
    },

    /**
     * Enforce authentication checks on every page load
     */
    async checkAuth() {
        if (!this.isLoggedIn()) {
            window.location.href = '/admin/login.php';
            return;
        }

        try {
            const res = await Api.get('/auth/me');
            if (!res.success || parseInt(res.user.role_id) !== 1) {
                throw new Error("Invalid admin profile role.");
            }
            
            // Set header profile details
            const nameSpan = document.getElementById('admin-profile-name');
            if (nameSpan) {
                nameSpan.innerText = res.user.first_name + ' ' + res.user.last_name;
            }
        } catch (e) {
            localStorage.removeItem('admin_token');
            localStorage.removeItem('admin_user');
            window.location.href = '/admin/login.php';
        }
    }
};

window.Auth = Auth;

// Form Submit Bindings
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('frm-admin-login');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const email = document.getElementById('admin-email').value;
            const pass = document.getElementById('admin-password').value;
            Auth.adminLogin(email, pass);
        });
    }
});
