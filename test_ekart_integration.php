<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

require_once __DIR__ . '/includes/EkartService.php';

session_start();
unset($_SESSION['ekart_access_token']); // clear old tokens

$service = new EkartService();
$result = $service->checkServiceabilityAndRate('401208', 0.5, 'Prepaid', 199.00);

print_r($result);
