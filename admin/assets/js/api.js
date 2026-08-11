/**
 * Admin Panel API Fetch Client Wrapper
 */

const Api = {
    /**
     * Perform HTTP request to the backend API
     *
     * @param {string} method GET, POST, PUT, DELETE, PATCH
     * @param {string} path API Endpoint path (e.g. /admin/products)
     * @param {object|FormData|null} data Payload
     * @return {Promise<object>}
     */
    async request(method, path, data = null) {
        const token = localStorage.getItem('admin_token');
        const url = API_BASE_URL + path;

        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        const config = {
            method,
            headers
        };

        if (data) {
            if (data instanceof FormData) {
                config.body = data;
                // Note: Fetch will generate multipart boundary automatically
            } else {
                headers['Content-Type'] = 'application/json';
                config.body = JSON.stringify(data);
            }
        }

        try {
            const response = await fetch(url, config);
            const body = await response.json();

            // Intercept Unauthorized status (admin token expired or invalid)
            if (response.status === 401) {
                localStorage.removeItem('admin_token');
                localStorage.removeItem('admin_user');
                
                // Avoid loop redirect on login page
                if (!window.location.pathname.endsWith('login.php') && !window.location.pathname.endsWith('login')) {
                    Utils.showToast("Your admin session has expired. Please login again.", "warning");
                    setTimeout(() => {
                        window.location.href = '/admin/login.php';
                    }, 1500);
                }
                throw { status: 401, message: "Admin session expired" };
            }

            if (!response.ok) {
                const message = body.message || 'An error occurred processing request.';
                throw { status: response.status, message, errors: body.errors || null };
            }

            return body;

        } catch (error) {
            if (error.name === 'TypeError') {
                Utils.showToast("Cannot connect to server. Check your connection.", "error");
                throw { message: "Server connection failed" };
            }
            throw error;
        }
    },

    get(path) { return this.request('GET', path); },
    post(path, data) { return this.request('POST', path, data); },
    put(path, data) { return this.request('PUT', path, data); },
    delete(path) { return this.request('DELETE', path); },
    patch(path, data) { return this.request('PATCH', path, data); },
    uploadFile(path, formData) { return this.request('POST', path, formData); }
};

window.Api = Api;
