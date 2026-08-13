<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
require_once __DIR__ . '/includes/EkartService.php';

class DebugEkartService extends EkartService {
    public function debugPayload() {
        $ratePayload = [
            'pickupPincode' => 411046,
            'dropPincode' => 110001,
            'invoiceAmount' => 500,
            'weight' => 500,
            'length' => 10,
            'height' => 10,
            'width' => 10,
            'serviceType' => 'SURFACE',
            'shippingDirection' => 'FORWARD',
            'codAmount' => 0,
            'packages' => []
        ];
        
        $token = $this->getAccessToken();
        
        $reflection = new ReflectionClass($this);
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
        echo $rateResp;
    }
}

$s = new DebugEkartService();
$s->debugPayload();
