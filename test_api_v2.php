<?php
/**
 * E-Commerce Transaction Capabilities Integration Test Script
 * Verifies Cart, Address, Checkout, Coupons, Payments, Refunds, Shipments, and Admin Controls.
 */

function apiRequest(string $method, string $path, array $data = [], ?string $token = null): array {
    $ch = curl_init();
    $url = "http://localhost:8000" . $path;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    $headers = [];
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }

    if (!empty($data)) {
        $headers[] = "Content-Type: application/json";
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'body' => json_decode($response, true) ?: $response
    ];
}

function assertTest(string $name, array $response, int $expectedCode, ?callable $extraAssert = null): void {
    echo "Testing: $name... ";
    if ($response['code'] === $expectedCode) {
        if ($extraAssert === null || $extraAssert($response['body'])) {
            echo "\033[32mPASSED\033[0m (HTTP {$response['code']})\n";
        } else {
            echo "\033[31mFAILED\033[0m (Assertion Failed, HTTP {$response['code']})\n";
            print_r($response['body']);
        }
    } else {
        echo "\033[31mFAILED\033[0m (Expected HTTP $expectedCode, got {$response['code']})\n";
        print_r($response['body']);
    }
}

echo "Starting E-Commerce transactional API integration testing...\n";
$health = apiRequest('GET', '/api/products');
if ($health['code'] === 0) {
    echo "Error: Local PHP server is not running on http://localhost:8000.\n";
    exit(1);
}

// ----------------------------------------------------
// 1. Setup Accounts
// ----------------------------------------------------
$customerEmail = "buyer_" . time() . "@example.com";
$registerResponse = apiRequest('POST', '/api/auth/register', [
    'first_name' => 'John',
    'last_name' => 'Buyer',
    'email' => $customerEmail,
    'password' => 'BuyerPassword123',
    'phone' => '9876543210'
]);
$customerToken = $registerResponse['body']['token'] ?? null;
assertTest("Register Customer", $registerResponse, 201);

$adminLoginResponse = apiRequest('POST', '/api/auth/admin/login', [
    'email' => 'admin@example.com',
    'password' => 'Admin@123'
]);
$adminToken = $adminLoginResponse['body']['token'] ?? null;
assertTest("Login Admin User", $adminLoginResponse, 200);

// ----------------------------------------------------
// 2. Create Addresses (Customer)
// ----------------------------------------------------
$addressResponse = apiRequest('POST', '/api/addresses', [
    'type' => 'shipping',
    'full_name' => 'John Buyer',
    'phone' => '9876543210',
    'address_line1' => '123 E-Commerce Way',
    'city' => 'Bangalore',
    'state' => 'Karnataka',
    'pincode' => '560001',
    'is_default' => 1
], $customerToken);
$addressId = $addressResponse['body']['address_id'] ?? null;
assertTest("Create Customer Address", $addressResponse, 201, function($body) {
    return !empty($body['address_id']);
});

// ----------------------------------------------------
// 3. Add item to cart & test price calculation
// ----------------------------------------------------
// Add iPhone 16 Pro (base ₹1199.00) with storage 256GB (+₹100) -> item price ₹1299
$cartAddResponse = apiRequest('POST', '/api/cart/add', [
    'product_id' => 1,
    'quantity' => 1,
    'attributes' => ['Color' => 'Titanium Grey', 'Storage' => '256GB']
], $customerToken);

assertTest("Add Product with Attributes to Cart", $cartAddResponse, 200, function($body) {
    $item = $body['data']['items'][0] ?? null;
    return $item && (float)$item['price'] === 129900.00 && $item['attributes']['Storage'] === '256GB';
});

// ----------------------------------------------------
// 4. Test coupon validations
// ----------------------------------------------------
// A. Apply expired coupon (should fail)
$expiredCouponRes = apiRequest('POST', '/api/cart/apply-coupon', ['code' => 'EXPIRED20'], $customerToken);
assertTest("Apply Expired Coupon (Should Reject)", $expiredCouponRes, 422, function($body) {
    return strpos($body['message'] ?? '', 'expired') !== false;
});

// B. Apply WELCOME10 coupon (should succeed, 10% on ₹1299 is ₹129.90, but max discount cap is ₹50)
$couponRes = apiRequest('POST', '/api/cart/apply-coupon', ['code' => 'WELCOME10'], $customerToken);
assertTest("Apply Coupon WELCOME10 (Check Discount Cap)", $couponRes, 200, function($body) {
    $summary = $body['data']['summary'] ?? null;
    return $summary && (float)$summary['discount'] === 50.00;
});

// ----------------------------------------------------
// 5. Checkout (COD)
// ----------------------------------------------------
$checkoutRes = apiRequest('POST', '/api/checkout', [
    'shipping_address_id' => $addressId,
    'payment_method' => 'cod',
    'notes' => 'Deliver after 5 PM'
], $customerToken);
$orderId = $checkoutRes['body']['order_id'] ?? null;
$orderNumber = $checkoutRes['body']['order_number'] ?? null;

assertTest("Perform COD Checkout (Places Order)", $checkoutRes, 201, function($body) {
    return !empty($body['order_id']) && !empty($body['order_number']);
});

// Verify cart is now cleared
$cartClearedRes = apiRequest('GET', '/api/cart', [], $customerToken);
assertTest("Verify Cart is Cleared", $cartClearedRes, 200, function($body) {
    return empty($body['data']['items']);
});

// ----------------------------------------------------
// 6. Admin updates order status & shipping
// ----------------------------------------------------
// Admin adds shipment
$shipmentRes = apiRequest('POST', "/api/admin/orders/$orderId/shipment", [
    'courier_name' => 'FedEx Express',
    'tracking_number' => 'TRK-FX-987654321'
], $adminToken);
assertTest("Admin Dispatches Shipment", $shipmentRes, 200);

// Admin updates status to delivered
$deliveredRes = apiRequest('PATCH', "/api/admin/orders/$orderId/status", [
    'status' => 'delivered',
    'comment' => 'Handed over to customer directly'
], $adminToken);
assertTest("Admin Sets Order Delivered", $deliveredRes, 200);

// ----------------------------------------------------
// 7. Customer initiates return
// ----------------------------------------------------
$returnRes = apiRequest('POST', "/api/orders/$orderId/return", [
    'comment' => 'Wrong storage size received'
], $customerToken);
assertTest("Customer Initiates Return Request", $returnRes, 200);

// ----------------------------------------------------
// 8. Place a second order with online payment (Check Shipping Fees)
// ----------------------------------------------------
// Add Cheaper Cable (₹10.00), total under ₹500, shipping should be ₹50
$cartAdd2Res = apiRequest('POST', '/api/cart/add', [
    'product_id' => 3,
    'quantity' => 1
], $customerToken);
assertTest("Add Low-Value Product to Cart", $cartAdd2Res, 200, function($body) {
    $summary = $body['data']['summary'] ?? null;
    return $summary && (float)$summary['shipping'] === 50.00;
});

// Checkout online
$checkoutRes2 = apiRequest('POST', '/api/checkout', [
    'shipping_address_id' => $addressId,
    'payment_method' => 'online'
], $customerToken);
$orderId2 = $checkoutRes2['body']['order_id'] ?? null;
assertTest("Perform Online Checkout", $checkoutRes2, 201);

// ----------------------------------------------------
// 9. Payment verification simulation
// ----------------------------------------------------
$initiateRes = apiRequest('POST', '/api/payments/initiate', ['order_id' => $orderId2], $customerToken);
assertTest("Initiate Gateway Payment", $initiateRes, 200, function($body) {
    return !empty($body['gateway_order_id']);
});

$verifyRes = apiRequest('POST', '/api/payments/verify', [
    'order_id' => $orderId2,
    'payment_id' => 'TXN-ONLINE-999',
    'status' => 'success'
], $customerToken);
assertTest("Verify Success Payment", $verifyRes, 200);

// Check order status changed to confirmed
$orderCheckRes = apiRequest('GET', "/api/orders/$orderId2", [], $customerToken);
assertTest("Verify Order Status Confirmed & Paid", $orderCheckRes, 200, function($body) {
    return $body['data']['order_status'] === 'confirmed' && $body['data']['payment_status'] === 'paid';
});

// ----------------------------------------------------
// 10. Customer cancels the paid order (verify refund request & restock)
// ----------------------------------------------------
$cancelRes = apiRequest('POST', "/api/orders/$orderId2/cancel", [
    'comment' => 'Accidental purchase'
], $customerToken);
assertTest("Customer Cancels Paid Order (Triggers Restock & Auto-Refund)", $cancelRes, 200);

// Verify order is marked refunded
$orderCheckRes2 = apiRequest('GET', "/api/orders/$orderId2", [], $customerToken);
assertTest("Verify Cancelled Order Status Refunded", $orderCheckRes2, 200, function($body) {
    return $body['data']['order_status'] === 'cancelled' && $body['data']['payment_status'] === 'refunded';
});

echo "\nAll transaction capabilities integration tests executed successfully.\n";
