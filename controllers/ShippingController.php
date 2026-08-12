<?php
/**
 * Shipping Controller
 * API endpoint for checking pincode serviceability and live shipping charges via Delhivery
 */

class ShippingController {
    private DelhiveryService $delhivery;

    public function __construct() {
        $this->delhivery = new DelhiveryService();
    }

    /**
     * GET /api/shipping/check-pincode
     * Query Parameters: pincode (required), amount (optional), weight (optional)
     */
    public function checkPincode(): void {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $pincode = trim($_GET['pincode'] ?? '');
        $amount = (float)($_GET['amount'] ?? 0.0);
        $weight = (float)($_GET['weight'] ?? 0.5);

        if (empty($pincode)) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Pincode parameter is required.'
            ], 422);
        }

        if (!preg_match('/^[1-9][0-9]{5}$/', $pincode)) {
            Helper::jsonResponse([
                'success' => false,
                'serviceable' => false,
                'message' => 'Invalid 6-digit Indian pincode.'
            ], 400);
        }

        try {
            $rateInfo = $this->delhivery->calculateShippingCharge($pincode, $weight, 'Prepaid', $amount);

            Helper::jsonResponse([
                'success' => true,
                'serviceable' => $rateInfo['serviceable'],
                'pincode' => $pincode,
                'shipping_charge' => $rateInfo['shipping_charge'],
                'estimated_days' => $rateInfo['estimated_days'],
                'cod_available' => $rateInfo['cod_available'] ?? false,
                'city' => $rateInfo['city'] ?? '',
                'state' => $rateInfo['state'] ?? '',
                'is_fallback' => $rateInfo['is_fallback'] ?? false,
                'message' => $rateInfo['message'] ?? ($rateInfo['serviceable'] ? 'Pincode serviceable via Delhivery Express.' : 'Delivery currently unavailable to this pincode.')
            ], 200);

        } catch (Throwable $e) {
            Helper::jsonResponse([
                'success' => true,
                'serviceable' => true,
                'pincode' => $pincode,
                'shipping_charge' => ($amount >= 499.0) ? 0.0 : 49.0,
                'estimated_days' => '3-5 Business Days',
                'is_fallback' => true,
                'message' => 'Standard delivery applicable.'
            ], 200);
        }
    }
}
