<?php
/**
 * PHP Built-in Server Router Script
 * Routes requests to api index, public static files, and clean HTML pages.
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 1. Route API requests to the backend front controller
if (strpos($uri, '/api/') === 0) {
    require __DIR__ . '/index.php';
    exit;
}

if ($uri === '/admin') {
    header("Location: /admin/");
    exit;
}

// 1.5. Route admin requests to the admin folder
if (strpos($uri, '/admin/') === 0) {
    $adminUri = preg_replace('/^\/admin/', '', $uri);
    $adminUri = '/' . ltrim($adminUri, '/');
    
    $adminFile = __DIR__ . '/admin' . $adminUri;
    if (is_file($adminFile)) {
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico)$/', $adminFile)) {
            $ext = pathinfo($adminFile, PATHINFO_EXTENSION);
            $mimes = [
                'css'  => 'text/css',
                'js'   => 'application/javascript',
                'png'  => 'image/png',
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif'  => 'image/gif',
                'svg'  => 'image/svg+xml',
                'ico'  => 'image/x-icon'
            ];
            
            $mime = 'text/plain';
            if (isset($mimes[$ext])) {
                $mime = $mimes[$ext];
            } elseif (class_exists('finfo')) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($adminFile);
            }
            header("Content-Type: $mime");
            readfile($adminFile);
            exit;
        }
        require $adminFile;
        exit;
    }
    
    $adminPath = trim($adminUri, '/');
    if (empty($adminPath)) {
        require __DIR__ . '/admin/index.php';
        exit;
    }
    
    $adminPageFile = __DIR__ . '/admin/' . $adminPath . '.php';
    if (is_file($adminPageFile)) {
        require $adminPageFile;
        exit;
    }
}

// 2. Serve product/category images from the parent uploads directory
if (strpos($uri, '/uploads/') === 0) {
    $uploadFile = __DIR__ . $uri;
    if (is_file($uploadFile)) {
        $mime = 'image/jpeg';
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($uploadFile);
        } else {
            $ext = pathinfo($uploadFile, PATHINFO_EXTENSION);
            $mimes = [
                'png'  => 'image/png',
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif'  => 'image/gif',
                'svg'  => 'image/svg+xml',
                'ico'  => 'image/x-icon'
            ];
            if (isset($mimes[$ext])) $mime = $mimes[$ext];
        }
        header("Content-Type: $mime");
        readfile($uploadFile);
        exit;
    }
}

// 3. Check if file exists inside the public folder and serve it directly
$publicFile = __DIR__ . '/public' . $uri;
if (is_file($publicFile)) {
    if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico)$/', $publicFile)) {
        $ext = pathinfo($publicFile, PATHINFO_EXTENSION);
        $mimes = [
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'svg'  => 'image/svg+xml',
            'ico'  => 'image/x-icon'
        ];
        
        $mime = 'text/plain';
        if (isset($mimes[$ext])) {
            $mime = $mimes[$ext];
        } elseif (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($publicFile);
        }
        header("Content-Type: $mime");
        readfile($publicFile);
        exit;
    }
    
    if (pathinfo($publicFile, PATHINFO_EXTENSION) === 'php') {
        require $publicFile;
        exit;
    }
}

// 4. Clean URLs Routing (e.g. /shop -> public/shop.php)
$path = trim($uri, '/');
if (empty($path)) {
    require __DIR__ . '/public/index.php';
    exit;
}

// Check for exact page match in public folder
$pageFile = __DIR__ . '/public/' . $path . '.php';
if (is_file($pageFile)) {
    require $pageFile;
    exit;
}

// 5. Fallback 404
require __DIR__ . '/public/404.php';
