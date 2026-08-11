<?php
/**
 * Address Controller
 */

class AddressController {
    private Address $addressModel;

    public function __construct() {
        $this->addressModel = new Address();
    }

    /**
     * GET /api/addresses
     * List all addresses for authenticated customer
     */
    public function index(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        try {
            $addresses = $this->addressModel->findByUser($userId);
            Helper::jsonResponse([
                'success' => true,
                'data' => $addresses
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve addresses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/addresses/{id}
     * Get a single address details (owner checked)
     *
     * @param string $id Address ID
     */
    public function show(string $id): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];
        $addressId = (int)$id;

        try {
            $address = $this->addressModel->find($addressId);
            if (!$address || (int)$address['user_id'] !== $userId) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Address not found or unauthorized.'
                ], 404);
            }

            Helper::jsonResponse([
                'success' => true,
                'data' => $address
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve address: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/addresses
     * Create a new address for customer
     */
    public function store(): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];

        $data = Helper::getRequestBody();
        $data['user_id'] = $userId;

        $validator = new Validator($data);
        $errors = $validator->validate([
            'type'          => ['required'], // shipping or billing
            'full_name'     => ['required', 'maxLength:255'],
            'phone'         => ['required', 'maxLength:20'],
            'address_line1' => ['required', 'maxLength:255'],
            'city'          => ['required', 'maxLength:100'],
            'state'         => ['required', 'maxLength:100'],
            'pincode'       => ['required', 'maxLength:10']
        ]);

        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        // Validate type enum
        if (!in_array($data['type'], ['shipping', 'billing'])) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => ['type' => ['Address type must be shipping or billing.']]
            ], 422);
        }

        try {
            $addressId = $this->addressModel->create($data);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Address created successfully.',
                'address_id' => $addressId
            ], 201);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to save address: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/addresses/{id}
     * Update an address for customer
     *
     * @param string $id Address ID
     */
    public function update(string $id): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];
        $addressId = (int)$id;

        $address = $this->addressModel->find($addressId);
        if (!$address || (int)$address['user_id'] !== $userId) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Address not found or unauthorized.'
            ], 404);
        }

        $data = Helper::getRequestBody();

        $validator = new Validator($data);
        $rules = [];
        if (array_key_exists('type', $data)) $rules['type'] = ['required'];
        if (array_key_exists('full_name', $data)) $rules['full_name'] = ['required', 'maxLength:255'];
        if (array_key_exists('phone', $data)) $rules['phone'] = ['required', 'maxLength:20'];
        if (array_key_exists('address_line1', $data)) $rules['address_line1'] = ['required', 'maxLength:255'];
        if (array_key_exists('city', $data)) $rules['city'] = ['required', 'maxLength:100'];
        if (array_key_exists('state', $data)) $rules['state'] = ['required', 'maxLength:100'];
        if (array_key_exists('pincode', $data)) $rules['pincode'] = ['required', 'maxLength:10'];

        $errors = $validator->validate($rules);
        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        if (isset($data['type']) && !in_array($data['type'], ['shipping', 'billing'])) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => ['type' => ['Address type must be shipping or billing.']]
            ], 422);
        }

        try {
            $updated = $this->addressModel->update($addressId, $userId, $data);
            if ($updated) {
                Helper::jsonResponse([
                    'success' => true,
                    'message' => 'Address updated successfully.'
                ], 200);
            } else {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'No changes made to the address.'
                ], 400);
            }
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to update address: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/addresses/{id}
     * Delete an address
     *
     * @param string $id Address ID
     */
    public function destroy(string $id): void {
        $user = AuthMiddleware::handle();
        $userId = (int)$user['id'];
        $addressId = (int)$id;

        try {
            $deleted = $this->addressModel->delete($addressId, $userId);
            if ($deleted) {
                Helper::jsonResponse([
                    'success' => true,
                    'message' => 'Address deleted successfully.'
                ], 200);
            } else {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Address not found or unauthorized.'
                ], 404);
            }
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to delete address: ' . $e->getMessage()
            ], 500);
        }
    }
}
