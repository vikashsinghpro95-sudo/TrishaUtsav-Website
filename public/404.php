<?php
require_once __DIR__ . '/includes/config.php';
include_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-md mx-auto text-center py-24 bg-white border border-gray-100 rounded-2xl shadow-sm my-12">
    <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6">
        <i class="fas fa-exclamation-triangle text-2xl"></i>
    </div>
    <h1 class="text-4xl font-black text-gray-800 mb-2">404</h1>
    <h2 class="text-lg font-bold text-gray-700 mb-2">Page Not Found</h2>
    <p class="text-gray-500 text-sm max-w-xs mx-auto mb-8">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
    <a href="<?php echo BASE_URL; ?>index.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-md transition duration-200">
        Go to Homepage
    </a>
</div>

<?php
include_once __DIR__ . '/includes/footer.php';
?>
