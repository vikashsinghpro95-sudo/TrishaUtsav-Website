<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
require_once __DIR__ . '/includes/EkartService.php';
session_start();

$service = new EkartService();
$pincodes = [
    '411046' => 'Pune',
    '400001' => 'Mumbai',
    '110001' => 'Delhi',
    '793001' => 'Shillong'
];

foreach ($pincodes as $pin => $city) {
    unset($_SESSION['ekart_access_token']);
    $result = $service->checkServiceabilityAndRate($pin, 0.5, 'Prepaid', 500.00);
    // Since checkServiceabilityAndRate caches the result, wait I must clear the specific cache key too
    echo "$city ($pin): Charge {$result['shipping_charge']}\n";
}
