<?php
require_once __DIR__ . '/includes/EkartService.php';

$service = new EkartService();

$reflection = new ReflectionClass($service);
$tokenMethod = $reflection->getMethod('getAccessToken');
$tokenMethod->setAccessible(true);
$token = $tokenMethod->invoke($service);

$baseUrlProp = $reflection->getProperty('baseUrl');
$baseUrlProp->setAccessible(true);
$baseUrl = $baseUrlProp->getValue($service);

$clientIdProp = $reflection->getProperty('clientId');
$clientIdProp->setAccessible(true);
$clientId = $clientIdProp->getValue($service);

$originPincodeProp = $reflection->getProperty('originPincode');
$originPincodeProp->setAccessible(true);
$originPincode = $originPincodeProp->getValue($service);

$destPincode = 401208;
$orderAmount = 199.00;

$ratePayload = [
    'pickupPincode' => $originPincode,
    'dropPincode' => (int)$destPincode,
    'invoiceAmount' => (float)$orderAmount,
    'weight' => 500,
    'length' => 10,
    'height' => 10,
    'width' => 10,
    'serviceType' => 'SURFACE',
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
$error = curl_error($chRate);
curl_close($chRate);

echo "HTTP Code: $rateHttpCode\n";
echo "Response: $rateResp\n";
echo "cURL Error: $error\n";
