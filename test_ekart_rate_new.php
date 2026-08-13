<?php
require_once __DIR__ . '/includes/EkartService.php';

$service = new EkartService();
$reflection = new ReflectionClass(EkartService::class);
$usernameProp = $reflection->getProperty('username');
$usernameProp->setAccessible(true);
$usernameProp->setValue($service, 'trishautsaventerprises@gmail.com');

$passwordProp = $reflection->getProperty('password');
$passwordProp->setAccessible(true);
$passwordProp->setValue($service, 'Balaji@2026');

$tokenMethod = $reflection->getMethod('getAccessToken');
$tokenMethod->setAccessible(true);
$token = $tokenMethod->invoke($service);

$baseUrlProp = $reflection->getProperty('baseUrl');
$baseUrlProp->setAccessible(true);
$baseUrl = $baseUrlProp->getValue($service);

$clientIdProp = $reflection->getProperty('clientId');
$clientIdProp->setAccessible(true);
$clientId = $clientIdProp->getValue($service);

$ratePayload = [
    'pickupPincode' => 411046,
    'dropPincode' => 401208,
    'invoiceAmount' => 199.00,
    'weight' => 500,
    'length' => 10,
    'height' => 10,
    'width' => 10,
    'serviceType' => 'SURFACE',
    'shippingDirection' => 'FORWARD',
    'codAmount' => 0,
    'packages' => []
];

$rateUrl = $baseUrl . '/data/pricing/estimate';
$chRate = curl_init();
curl_setopt_array($chRate, [
    CURLOPT_URL => $rateUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($ratePayload),
    CURLOPT_TIMEOUT => 5,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'Client-Id: ' . $clientId,
        'Content-Type: application/json',
        'Accept: application/json'
    ]
]);
$rateResp = curl_exec($chRate);
$rateHttpCode = curl_getinfo($chRate, CURLINFO_HTTP_CODE);
curl_close($chRate);

echo "HTTP Code: $rateHttpCode\n";
echo "Response: $rateResp\n";
