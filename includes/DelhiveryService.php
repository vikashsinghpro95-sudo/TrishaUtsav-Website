<?php
/**
 * Delhivery Express Shipping API Service
 * Handles pincode serviceability, live shipping charge calculation, and package tracking
 */

class DelhiveryService {
    private string $apiKey;
    private string $originPincode;
    private string $baseUrl;

    public function __construct() {
        $this->apiKey = $_ENV['DELHIVERY_API_KEY'] ?? '4e6c8e091637cb38f48cb88907b6dff4f306c44b';
        $this->originPincode = $_ENV['DELHIVERY_ORIGIN_PINCODE'] ?? '201301'; // Default origin warehouse pincode
        $this->baseUrl = 'https://track.delhivery.com';
    }

    /**
     * Check if a 6-digit pincode is serviceable by Delhivery
     *
     * @param string $pincode
     * @return array
     */
    public function checkPincodeServiceability(string $pincode): array {
        $pincode = trim($pincode);
        if (!preg_match('/^[1-9][0-9]{5}$/', $pincode)) {
            return [
                'serviceable' => false,
                'message' => 'Invalid Indian pincode. Please enter a valid 6-digit number.'
            ];
        }

        // Check short-term cache in session to reduce API hits
        $cacheKey = 'delhivery_pin_' . $pincode;
        if (isset($_SESSION[$cacheKey]) && (time() - $_SESSION[$cacheKey]['time']) < 1800) {
            return $_SESSION[$cacheKey]['data'];
        }

        $url = $this->baseUrl . '/c/api/pin-codes/json/?filter_codes=' . urlencode($pincode);
        $response = $this->makeCurlRequest($url, 'GET');

        if (!$response['success']) {
            $this->logError("Pincode Serviceability API Error for pincode {$pincode}: " . $response['error']);
            return [
                'serviceable' => false,
                'message' => 'Serviceability check unavailable. Using standard delivery.',
                'is_fallback' => true
            ];
        }

        $data = json_decode($response['body'], true);
        if (empty($data['delivery_codes']) || !is_array($data['delivery_codes'])) {
            $result = [
                'serviceable' => false,
                'message' => 'Pincode is not currently serviceable for delivery.',
                'pincode' => $pincode
            ];
            $_SESSION[$cacheKey] = ['time' => time(), 'data' => $result];
            return $result;
        }

        $pinData = null;
        foreach ($data['delivery_codes'] as $codeObj) {
            if (isset($codeObj['postal_code']) && (string)$codeObj['postal_code']['pin'] === (string)$pincode) {
                $pinData = $codeObj['postal_code'];
                break;
            }
        }

        if (!$pinData) {
            // Pick first code object if direct match array key differs
            $first = reset($data['delivery_codes']);
            $pinData = $first['postal_code'] ?? null;
        }

        if (!$pinData) {
            $result = [
                'serviceable' => false,
                'message' => 'Delivery unavailable to this pincode.',
                'pincode' => $pincode
            ];
            $_SESSION[$cacheKey] = ['time' => time(), 'data' => $result];
            return $result;
        }

        $isPrepaid = !empty($pinData['pre_paid']) && strtoupper((string)$pinData['pre_paid']) === 'Y';
        $isCod = !empty($pinData['cod']) && strtoupper((string)$pinData['cod']) === 'Y';
        $isPickup = !empty($pinData['pickup']) && strtoupper((string)$pinData['pickup']) === 'Y';
        $isServiceable = $isPrepaid || $isCod || $isPickup;

        $result = [
            'serviceable' => $isServiceable,
            'pincode' => $pincode,
            'city' => $pinData['district'] ?? ($pinData['city'] ?? ''),
            'state' => $pinData['state_code'] ?? ($pinData['state'] ?? ''),
            'cod_available' => $isCod,
            'prepaid_available' => $isPrepaid,
            'estimated_days' => '3-5 Business Days',
            'message' => $isServiceable ? 'Pincode is serviceable via Delhivery Express.' : 'Delivery currently unavailable to this area.'
        ];

        $_SESSION[$cacheKey] = ['time' => time(), 'data' => $result];
        return $result;
    }

    /**
     * Calculate live Delhivery shipping charges based on destination pincode, weight, and order total
     *
     * @param string $destPincode
     * @param float $weightKg
     * @param string $paymentMode 'Prepaid' or 'COD'
     * @param float $orderTotal
     * @return array
     */
    public function calculateShippingCharge(string $destPincode, float $weightKg = 0.5, string $paymentMode = 'Prepaid', float $amount = 0.0): array {
        $serviceability = $this->checkPincodeServiceability($destPincode);
        if (!$serviceability['serviceable']) {
            return [
                'serviceable' => false,
                'shipping_charge' => 0.0,
                'estimated_days' => 'N/A',
                'message' => $serviceability['message'] ?? 'Pincode not serviceable.'
            ];
        }

        // Cache rate check for 30 minutes
        $cacheKey = 'delhivery_rate_' . $destPincode . '_' . round($weightKg, 2) . '_' . strtolower($paymentMode);
        if (isset($_SESSION[$cacheKey]) && (time() - $_SESSION[$cacheKey]['time']) < 1800) {
            return $_SESSION[$cacheKey]['data'];
        }

        $weightGrams = max(500, (int)($weightKg * 1000));
        $queryParams = http_build_query([
            'md' => 'S',               // Mode: Express Surface
            'ss' => 'DTO',             // Service type
            'd_pin' => $destPincode,   // Destination pincode
            'o_pin' => $this->originPincode, // Origin warehouse pincode
            'cgm' => $weightGrams,     // Weight in grams
            'pt' => $paymentMode === 'COD' ? 'COD' : 'Prepaid'
        ]);

        $url = $this->baseUrl . '/api/kinko/v1/invoice/charges/.json?' . $queryParams;
        $response = $this->makeCurlRequest($url, 'GET');

        if ($response['success']) {
            $data = json_decode($response['body'], true);
            if (is_array($data)) {
                // Delhivery returns invoice breakdown array
                $chargeItem = is_array($data) && isset($data[0]) ? $data[0] : $data;
                $freightCharge = (float)($chargeItem['total_amount'] ?? ($chargeItem['freight_charge'] ?? 0.0));
                
                if ($freightCharge > 0) {
                    $result = [
                        'serviceable' => true,
                        'shipping_charge' => round($freightCharge, 2),
                        'estimated_days' => '3-5 Business Days',
                        'cod_available' => $serviceability['cod_available'] ?? false,
                        'city' => $serviceability['city'] ?? '',
                        'state' => $serviceability['state'] ?? '',
                        'is_fallback' => false
                    ];
                    $_SESSION[$cacheKey] = ['time' => time(), 'data' => $result];
                    return $result;
                }
            }
        }

        // Fallback: If Delhivery rate API is unreachable or returns 0, fallback to standard rule (Free above ₹499, else ₹49)
        $fallbackCharge = ($amount >= 499.00) ? 0.00 : 49.00;
        $result = [
            'serviceable' => true,
            'shipping_charge' => $fallbackCharge,
            'estimated_days' => '3-5 Business Days',
            'cod_available' => $serviceability['cod_available'] ?? false,
            'city' => $serviceability['city'] ?? '',
            'state' => $serviceability['state'] ?? '',
            'is_fallback' => true
        ];

        $_SESSION[$cacheKey] = ['time' => time(), 'data' => $result];
        return $result;
    }

    /**
     * Get live tracking information for a waybill / tracking ID
     *
     * @param string $trackingId
     * @return array
     */
    public function trackShipment(string $trackingId): array {
        $trackingId = trim($trackingId);
        if (empty($trackingId)) {
            return [
                'tracked' => false,
                'message' => 'No tracking ID provided.'
            ];
        }

        // Cache tracking response for 15 minutes
        $cacheKey = 'delhivery_track_' . $trackingId;
        if (isset($_SESSION[$cacheKey]) && (time() - $_SESSION[$cacheKey]['time']) < 900) {
            return $_SESSION[$cacheKey]['data'];
        }

        $url = $this->baseUrl . '/api/v1/packages/json/?waybill=' . urlencode($trackingId) . '&token=' . urlencode($this->apiKey);
        $response = $this->makeCurlRequest($url, 'GET');

        $fallbackUrl = 'https://www.delhivery.com/track/package/' . urlencode($trackingId);

        if (!$response['success']) {
            return [
                'tracked' => false,
                'tracking_id' => $trackingId,
                'message' => 'Tracking information is temporarily unavailable.',
                'fallback_url' => $fallbackUrl
            ];
        }

        $data = json_decode($response['body'], true);
        $shipmentData = $data['ShipmentData'][0]['Shipment'] ?? null;

        if (!$shipmentData) {
            return [
                'tracked' => true,
                'tracking_id' => $trackingId,
                'status' => 'In Transit',
                'status_code' => 'IN_TRANSIT',
                'location' => 'In Transit to Hub',
                'estimated_delivery' => 'Expected soon',
                'scans' => [],
                'fallback_url' => $fallbackUrl
            ];
        }

        $scans = [];
        if (!empty($shipmentData['Scans']) && is_array($shipmentData['Scans'])) {
            foreach ($shipmentData['Scans'] as $scanItem) {
                $scan = $scanItem['ScanDetail'] ?? $scanItem;
                $scans[] = [
                    'timestamp' => $scan['ScanDateTime'] ?? ($scan['ScanDate'] ?? ''),
                    'status' => $scan['Instructions'] ?? ($scan['Scan'] ?? 'Status updated'),
                    'location' => $scan['ScannedLocation'] ?? ($scan['Location'] ?? ''),
                    'comment' => $scan['Comment'] ?? ''
                ];
            }
        }

        $latestStatus = $shipmentData['Status']['Status'] ?? 'In Transit';
        $location = $shipmentData['Status']['StatusLocation'] ?? ($scans[0]['location'] ?? 'Delhivery Network');
        $expectedDate = $shipmentData['ExpectedDeliveryDate'] ?? '';

        $result = [
            'tracked' => true,
            'tracking_id' => $trackingId,
            'status' => $latestStatus,
            'status_code' => strtoupper(str_replace(' ', '_', $latestStatus)),
            'location' => $location,
            'estimated_delivery' => $expectedDate,
            'scans' => $scans,
            'fallback_url' => $fallbackUrl
        ];

        $_SESSION[$cacheKey] = ['time' => time(), 'data' => $result];
        return $result;
    }

    /**
     * Helper to perform authenticated cURL requests to Delhivery API
     *
     * @param string $url
     * @param string $method
     * @param array|null $payload
     * @return array
     */
    private function makeCurlRequest(string $url, string $method = 'GET', ?array $payload = null): array {
        $ch = curl_init();
        
        $headers = [
            'Authorization: Token ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($payload) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            }
        }

        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode >= 400) {
            return [
                'success' => false,
                'http_code' => $httpCode,
                'error' => $error ?: ("HTTP " . $httpCode),
                'body' => $body
            ];
        }

        return [
            'success' => true,
            'http_code' => $httpCode,
            'body' => $body
        ];
    }

    /**
     * Internal error logger for Delhivery failures
     *
     * @param string $message
     */
    private function logError(string $message): void {
        error_log("[Delhivery API Error] " . date('Y-m-d H:i:s') . " - " . $message);
    }
}
