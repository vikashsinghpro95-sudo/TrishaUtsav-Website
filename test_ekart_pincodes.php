<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

require_once __DIR__ . '/includes/EkartService.php';

session_start();
unset($_SESSION['ekart_access_token']);

$service = new EkartService();
$pincodes = ['400001', '110001', '700001', '600001', '560001', '380001', '411046', '411001'];

foreach ($pincodes as $pin) {
    // We pass 1.0kg weight to see if it changes
    $result = $service->checkServiceabilityAndRate($pin, 1.0, 'Prepaid', 500.00);
    echo "Pincode: $pin | Serviceable: {$result['serviceable']} | Charge: {$result['shipping_charge']} | City: {$result['city']}\n";
}
