<?php
/**
 * Trisha Utsav - One-Click Hostinger Production Deployer
 */
header('Content-Type: text/html; charset=utf-8');
echo "UNIQUE_TEST_123_DEPLOY_PHP";

if (function_exists('opcache_reset')) {
    opcache_reset();
}

$dbHost = 'localhost';
$dbUser = 'u445085246_Vikash';
$dbPass = 'Vikash@01072005@99';
$dbName = 'u445085246_TrishaUtsav';

$extractedFiles = 0;
$dbImported = false;
$errorMsg = '';
$logs = [];

// Step 1: Extract Zip Package if present
$zipFile = __DIR__ . '/deploy_package.zip';
if (file_exists($zipFile)) {
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($zipFile) === TRUE) {
            $extractedFiles = $zip->numFiles;
            
            // Aggressive overwrite: delete key files before extract
            $keyFiles = ['index.php', 'config/database.php', 'includes/Database.php', 'includes/Helper.php'];
            foreach ($keyFiles as $kf) {
                $p = __DIR__ . '/' . $kf;
                if (file_exists($p)) {
                    @chmod($p, 0777);
                    @unlink($p);
                }
            }
            
            $zip->extractTo(__DIR__);
            $zip->close();
            $logs[] = "Successfully extracted $extractedFiles files from deploy_package.zip.";
        } else {
            $logs[] = "Failed to open deploy_package.zip.";
        }
    } else {
        $logs[] = "PHP ZipArchive extension not enabled on server.";
    }
} else {
    $logs[] = "deploy_package.zip not found in current directory. Using direct uploaded files.";
}

// Run schema update script automatically if exists
if (file_exists(__DIR__ . '/update_schema.php')) {
    ob_start();
    include __DIR__ . '/update_schema.php';
    $output = ob_get_clean();
    $logs[] = "Schema update: " . trim(strip_tags($output));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trisha Utsav - Hostinger Deployment</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4 font-sans">
    <div class="max-w-2xl w-full bg-slate-800 rounded-3xl p-8 border border-slate-700 shadow-2xl space-y-6">
        <div class="flex items-center space-x-4 border-b border-slate-700 pb-6">
            <div class="w-14 h-14 bg-gradient-to-br from-[#990024] to-[#4a0011] text-[#f59e0b] rounded-2xl font-black text-2xl flex items-center justify-center border border-[#f59e0b]/40 shadow-lg">
                त्रि
            </div>
            <div>
                <h1 class="text-2xl font-black tracking-tight text-white">Trisha Utsav Deployment</h1>
                <p class="text-xs text-slate-400 font-medium mt-0.5">Hostinger Production Server Setup</p>
            </div>
        </div>

        <div class="space-y-3">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Execution Logs</h2>
            <div class="bg-slate-950 rounded-2xl p-4 text-xs font-mono space-y-2 border border-slate-800 text-emerald-400">
                <?php foreach ($logs as $log): ?>
                    <div class="flex items-start space-x-2">
                        <span class="text-amber-400">></span>
                        <span><?php echo htmlspecialchars($log); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($dbImported): ?>
            <div class="bg-emerald-950/60 border border-emerald-500/40 p-4 rounded-2xl flex items-center space-x-3 text-emerald-200 text-xs">
                <i class="fas fa-check-circle text-emerald-400 text-xl flex-shrink-0"></i>
                <div>
                    <span class="font-bold block">Production Deployment Ready!</span>
                    <span>Database tables & settings initialized for Trisha Utsav.</span>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-amber-950/60 border border-amber-500/40 p-4 rounded-2xl flex items-center space-x-3 text-amber-200 text-xs">
                <i class="fas fa-exclamation-triangle text-amber-400 text-xl flex-shrink-0"></i>
                <div>
                    <span class="font-bold block">Database Action Needed</span>
                    <span>If tables are not imported automatically, import <code>u445085246_Trusha_Utsav.sql</code> into phpMyAdmin.</span>
                </div>
            </div>
        <?php endif; ?>

        <div class="pt-4 border-t border-slate-700 flex flex-col sm:flex-row justify-between gap-4">
            <a href="index.php" class="bg-[#990024] hover:bg-[#7a001c] text-white text-xs font-extrabold px-6 py-3.5 rounded-full shadow-lg text-center uppercase tracking-wider transition border border-[#f59e0b]/30">
                <i class="fas fa-store mr-1.5"></i> Open Trisha Utsav Storefront
            </a>
            <a href="admin/login.php" class="bg-slate-700 hover:bg-slate-600 text-slate-100 text-xs font-bold px-6 py-3.5 rounded-full text-center uppercase tracking-wider transition">
                <i class="fas fa-user-shield mr-1.5"></i> Admin Login Portal
            </a>
        </div>
    </div>
</body>
</html>
