<?php
require_once __DIR__ . '/includes/EkartService.php';
$service = new EkartService();
$reflection = new ReflectionClass($service);

$baseUrlProp = $reflection->getProperty('baseUrl');
$baseUrlProp->setAccessible(true);
$baseUrl = $baseUrlProp->getValue($service);

$clientIdProp = $reflection->getProperty('clientId');
$clientIdProp->setAccessible(true);
$clientId = $clientIdProp->getValue($service);

$tokenUrl = $baseUrl . '/integrations/v2/auth/token/' . urlencode($clientId);
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $tokenUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'username' => 'trishautsaventerprises@gmail.com',
        'password' => 'Balaji@2026'
    ]),
    CURLOPT_TIMEOUT => 5,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json'
    ]
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
