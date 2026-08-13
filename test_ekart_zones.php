<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
require_once __DIR__ . '/includes/EkartService.php';
session_start();
unset($_SESSION['ekart_access_token']);
$service = new EkartService();
$pincodes = ['411046', '400001', '110001', '793001', '190001', '744101'];
foreach ($pincodes as $pin) {
    $result = $service->checkServiceabilityAndRate($pin, 0.5, 'Prepaid', 500.00);
    echo "Pincode: $pin | Charge: {$result['shipping_charge']}\n";
}
