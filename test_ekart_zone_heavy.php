<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
require_once __DIR__ . '/includes/EkartService.php';

class DebugEkartService extends EkartService {
    public function debugPayload($dest) {
        $ratePayload = [
            'pickupPincode' => 411046,
            'dropPincode' => (int)$dest,
            'invoiceAmount' => 1500,
            'weight' => 2500,
            'length' => 20,
            'height' => 20,
            'width' => 20,
            'serviceType' => 'SURFACE',
            'shippingDirection' => 'FORWARD',
            'codAmount' => 0,
            'packages' => []
        ];
        
        $reflection = new ReflectionClass(EkartService::class);
        $tokenMethod = $reflection->getMethod('getAccessToken');
        $tokenMethod->setAccessible(true);
        $token = $tokenMethod->invoke($this);
        $baseUrlProp = $reflection->getProperty('baseUrl');
        $baseUrlProp->setAccessible(true);
        $baseUrl = $baseUrlProp->getValue($this);
        $clientIdProp = $reflection->getProperty('clientId');
        $clientIdProp->setAccessible(true);
        $clientId = $clientIdProp->getValue($this);

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
        curl_close($chRate);
        echo "PIN: $dest -> $rateResp\n";
    }
}

$s = new DebugEkartService();
$s->debugPayload('411046'); // Pune
$s->debugPayload('400001'); // Mumbai
$s->debugPayload('110001'); // Delhi
$s->debugPayload('793001'); // Shillong
