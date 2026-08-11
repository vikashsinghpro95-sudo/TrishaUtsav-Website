<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trisha Utsav Admin - Login</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
    
    <script>
        const BASE_URL = '/admin/';
        const API_BASE_URL = '/api';
    </script>
    
    <!-- Base Scripts -->
    <script src="/admin/assets/js/utils.js"></script>
    <script src="/admin/assets/js/api.js"></script>
    <script src="/admin/assets/js/auth.js"></script>
</head>
<body class="bg-slate-900 font-sans min-h-screen flex items-center justify-center">

    <div class="max-w-md w-full mx-4 bg-slate-800 p-8 rounded-2xl border border-slate-700 shadow-2xl space-y-6">
        <!-- Logo -->
        <div class="text-center">
            <span class="text-amber-500 text-3xl font-black flex items-center justify-center space-x-2">
                <i class="fas fa-crown text-amber-500 animate-bounce"></i>
                <span class="text-white">TrishaUtsav</span>
            </span>
            <span class="text-xs text-slate-400 font-semibold uppercase tracking-widest mt-2 block">Management Console</span>
        </div>

        <!-- Login Form -->
        <form id="frm-admin-login" class="space-y-4">
            <div>
                <label for="admin-email" class="block text-xs font-semibold text-slate-300 uppercase mb-1">Admin Email</label>
                <input type="email" id="admin-email" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white text-sm focus:ring-1 focus:ring-primary-500 focus:border-primary-500 outline-none transition" placeholder="admin@example.com">
            </div>

            <div>
                <label for="admin-password" class="block text-xs font-semibold text-slate-300 uppercase mb-1">Password</label>
                <input type="password" id="admin-password" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white text-sm focus:ring-1 focus:ring-primary-500 focus:border-primary-500 outline-none transition" placeholder="••••••••">
            </div>

            <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-lg transition duration-200 focus:outline-none">
                Access Dashboard
            </button>
        </form>
    </div>

    <script>
        // Redirect already logged in admin users
        if (localStorage.getItem('admin_token')) {
            window.location.replace('/admin/index.php');
        }
    </script>
</body>
</html>
