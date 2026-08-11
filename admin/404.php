<?php
include_once __DIR__ . '/includes/admin-header.php';
?>

<div class="flex flex-col items-center justify-center py-20 space-y-4">
    <span class="text-primary-600 text-6xl font-black">404</span>
    <h2 class="text-xl font-bold text-gray-800 uppercase tracking-wide">Resource Not Found</h2>
    <p class="text-xs text-gray-500 font-medium">The administration resource you are trying to access does not exist or has been moved.</p>
    <a href="/admin/index.php" class="bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs py-2.5 px-6 rounded-lg shadow transition">
        Return to Dashboard
    </a>
</div>

<?php
include_once __DIR__ . '/includes/admin-footer.php';
?>
