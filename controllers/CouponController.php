<?php
/**
 * Coupon Controller (Admin only)
 */

class CouponController {
    private Coupon $couponModel;

    public function __construct() {
        $this->couponModel = new Coupon();
    }

    /**
     * GET /api/admin/coupons
     * List all coupons
     */
    public function index(): void {
        AuthMiddleware::handle(true); // Admin auth

        try {
            $coupons = $this->couponModel->all();
            Helper::jsonResponse([
                'success' => true,
                'data' => $coupons
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve coupons: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/admin/coupons
     * Create a new coupon promo code
     */
    public function store(): void {
        $user = AuthMiddleware::handle(true); // Admin auth
        $adminId = (int)$user['id'];

        $data = Helper::getRequestBody();

        // format code to uppercase
        if (isset($data['code'])) {
            $data['code'] = strtoupper(trim($data['code']));
        }

        $validator = new Validator($data);
        $errors = $validator->validate([
            'code'           => ['required', 'unique:coupons,code', 'maxLength:50'],
            'type'           => ['required'], // percentage, fixed, free_shipping
            'value'          => ['required', 'numeric'],
            'min_cart_value' => ['numeric'],
            'max_discount'   => ['numeric'],
            'usage_limit'    => ['numeric']
        ]);

        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        // Validate type enum
        if (!in_array($data['type'], ['percentage', 'fixed', 'free_shipping'])) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => ['type' => ['Coupon type must be percentage, fixed, or free_shipping.']]
            ], 422);
        }

        try {
            $couponId = $this->couponModel->create($data);
            
            // Log admin audit action
            Helper::logAction($adminId, 'create_coupon', 'coupons', $couponId, null, $data);

            Helper::jsonResponse([
                'success' => true,
                'message' => 'Coupon created successfully.',
                'coupon_id' => $couponId
            ], 201);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to create coupon: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/admin/coupons/{id}
     * Update an existing coupon
     *
     * @param string $id Coupon ID
     */
    public function update(string $id): void {
        $user = AuthMiddleware::handle(true); // Admin auth
        $adminId = (int)$user['id'];
        $couponId = (int)$id;

        $coupon = $this->couponModel->find($couponId);
        if (!$coupon) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Coupon not found.'
            ], 404);
        }

        $data = Helper::getRequestBody();

        if (isset($data['code'])) {
            $data['code'] = strtoupper(trim($data['code']));
        }

        $validator = new Validator($data);
        $rules = [];
        if (array_key_exists('code', $data)) $rules['code'] = ['required', "unique:coupons,code,$couponId", 'maxLength:50'];
        if (array_key_exists('type', $data)) $rules['type'] = ['required'];
        if (array_key_exists('value', $data)) $rules['value'] = ['required', 'numeric'];
        if (array_key_exists('min_cart_value', $data)) $rules['min_cart_value'] = ['numeric'];
        if (array_key_exists('max_discount', $data)) $rules['max_discount'] = ['numeric'];
        if (array_key_exists('usage_limit', $data)) $rules['usage_limit'] = ['numeric'];

        $errors = $validator->validate($rules);
        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        if (isset($data['type']) && !in_array($data['type'], ['percentage', 'fixed', 'free_shipping'])) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => ['type' => ['Coupon type must be percentage, fixed, or free_shipping.']]
            ], 422);
        }

        try {
            $this->couponModel->update($couponId, $data);
            
            // Log admin audit action
            Helper::logAction($adminId, 'update_coupon', 'coupons', $couponId, $coupon, $data);

            Helper::jsonResponse([
                'success' => true,
                'message' => 'Coupon updated successfully.'
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to update coupon: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/admin/coupons/{id}
     * Delete a coupon
     *
     * @param string $id Coupon ID
     */
    public function destroy(string $id): void {
        $user = AuthMiddleware::handle(true); // Admin auth
        $adminId = (int)$user['id'];
        $couponId = (int)$id;

        $coupon = $this->couponModel->find($couponId);
        if (!$coupon) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Coupon not found.'
            ], 404);
        }

        try {
            $this->couponModel->delete($couponId);
            
            // Log admin audit action
            Helper::logAction($adminId, 'delete_coupon', 'coupons', $couponId, $coupon, null);

            Helper::jsonResponse([
                'success' => true,
                'message' => 'Coupon deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to delete coupon: ' . $e->getMessage()
            ], 500);
        }
    }
}
