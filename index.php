<?php
/**
 * Application Front Controller & Bootstrapper
 */

// Set Default Timezone to IST (Indian Standard Time)
date_default_timezone_set('Asia/Kolkata');

// Error & Upload configuration for REST API (suppress display, log errors)
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Increase PHP upload & post size limits dynamically
@ini_set('upload_max_filesize', '100M');
@ini_set('post_max_size', '100M');
@ini_set('memory_limit', '256M');

// Require Composer Autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Environment variables setup via Dotenv
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

// Fallback autoloader for legacy or un-namespaced classes
spl_autoload_register(function (string $class) {
    // Strip namespace prefix if passed
    $className = basename(str_replace('\\', '/', $class));

    $directories = [
        __DIR__ . '/includes/',
        __DIR__ . '/models/',
        __DIR__ . '/controllers/',
        __DIR__ . '/middleware/',
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Load database configurations
require_once __DIR__ . '/config/database.php';

// Apply Restricted CORS Headers for API accessibility
$allowedOrigins = array_filter(array_map('trim', explode(',', $_ENV['ALLOWED_ORIGINS'] ?? 'http://localhost:3000,http://127.0.0.1:8000,http://localhost:8000')));
$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($requestOrigin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: " . $requestOrigin);
} elseif (!empty($requestOrigin) && ($_ENV['APP_ENV'] ?? 'development') === 'development') {
    header("Access-Control-Allow-Origin: " . $requestOrigin);
} else {
    header("Access-Control-Allow-Origin: " . ($allowedOrigins[0] ?? '*'));
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 86400");

// Immediately terminate OPTIONS preflight requests successfully
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Clean and parse the request URI
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normalize path relative to the script location (handles subdirectory installations)
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptName !== '/' && strpos($requestUri, $scriptName) === 0) {
    $requestUri = substr($requestUri, strlen($scriptName));
}

// Ensure the URI has a leading slash
$requestUri = '/' . trim($requestUri, '/');
$requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);

// Load routing table
$routes = require_once __DIR__ . '/routes.php';
$matched = false;

if (isset($routes[$requestMethod])) {
    foreach ($routes[$requestMethod] as $pattern => $handler) {
        if (preg_match($pattern, $requestUri, $matches)) {
            // Remove the overall match string, leaving only captured regex parameter groups
            array_shift($matches);

            // Separate controller class and method name
            list($controllerClass, $method) = explode('@', $handler);

            try {
                if (!class_exists($controllerClass)) {
                    Helper::jsonResponse([
                        'success' => false,
                        'message' => "Internal server configuration error: Controller '$controllerClass' is missing."
                    ], 500);
                }

                $controllerInstance = new $controllerClass();
                if (!method_exists($controllerInstance, $method)) {
                    Helper::jsonResponse([
                        'success' => false,
                        'message' => "Internal server configuration error: Action '$method' is not defined on '$controllerClass'."
                    ], 500);
                }

                // Execute controller action, spreading regex captures as arguments
                call_user_func_array([$controllerInstance, $method], $matches);
                $matched = true;
                break;

            } catch (Throwable $e) {
                error_log("Unhandled Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
                
                $response = [
                    'success' => false,
                    'message' => 'An unexpected server error occurred.'
                ];
                if (!$isProduction) {
                    $response['error'] = $e->getMessage();
                }

                Helper::jsonResponse($response, 500);
            }
        }
    }
}

// Fallback handling if no route matched
if (!$matched) {
    // Determine if the URI is registered under a different HTTP method (405 vs 404 check)
    $isMethodInvalid = false;
    foreach ($routes as $method => $methodRoutes) {
        if ($method !== $requestMethod) {
            foreach ($methodRoutes as $pattern => $handler) {
                if (preg_match($pattern, $requestUri)) {
                    $isMethodInvalid = true;
                    break 2;
                }
            }
        }
    }

    if ($isMethodInvalid) {
        Helper::jsonResponse([
            'success' => false,
            'message' => '405 Method Not Allowed'
        ], 405);
    } else {
        Helper::jsonResponse([
            'success' => false,
            'message' => '404 Route Not Found'
        ], 404);
    }
}
