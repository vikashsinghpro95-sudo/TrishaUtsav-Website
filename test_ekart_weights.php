<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

require_once __DIR__ . '/includes/EkartService.php';

session_start();
unset($_SESSION['ekart_access_token']);

$service = new EkartService();
$weights = [0.1, 1.0, 2.0, 5.0, 10.0];

foreach ($weights as $w) {
    $result = $service->checkServiceabilityAndRate('110001', $w, 'Prepaid', 500.00);
    echo "Weight: {$w}kg | Charge: {$result['shipping_charge']}\n";
}
