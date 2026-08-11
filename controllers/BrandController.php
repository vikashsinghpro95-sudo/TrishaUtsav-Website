<?php
/**
 * Brand Controller
 */

class BrandController {
    private Brand $brandModel;

    public function __construct() {
        $this->brandModel = new Brand();
    }

    /**
     * GET /api/brands
     * List all active brands
     */
    public function index(): void {
        try {
            $brands = $this->brandModel->all();
            Helper::jsonResponse([
                'success' => true,
                'data' => $brands
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve brands: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/admin/brands
     * Create brand (Admin only)
     */
    public function store(): void {
        AuthMiddleware::handle(true);

        $data = Helper::getRequestBody();
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Helper::slugify($data['name']);
        }

        $validator = new Validator($data);
        $errors = $validator->validate([
            'name' => ['required', 'unique:brands,name', 'maxLength:191'],
            'slug' => ['required', 'unique:brands,slug', 'maxLength:191']
        ]);

        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        try {
            $brandId = $this->brandModel->create($data);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Brand created successfully.',
                'brand_id' => $brandId
            ], 201);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to create brand: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/admin/brands/{id}
     * Update brand details (Admin only)
     *
     * @param string $id Brand ID
     */
    public function update(string $id): void {
        AuthMiddleware::handle(true);
        $brandId = (int)$id;

        $brand = $this->brandModel->find($brandId);
        if (!$brand) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Brand not found.'
            ], 404);
        }

        $data = Helper::getRequestBody();
        if (!empty($data['slug'])) {
            $data['slug'] = Helper::slugify($data['slug']);
        }

        $validator = new Validator($data);
        $rules = [];
        if (array_key_exists('name', $data)) $rules['name'] = ['required', "unique:brands,name,$brandId", 'maxLength:191'];
        if (array_key_exists('slug', $data)) $rules['slug'] = ['required', "unique:brands,slug,$brandId", 'maxLength:191'];

        $errors = $validator->validate($rules);
        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        try {
            $this->brandModel->update($brandId, $data);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Brand updated successfully.'
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to update brand: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/admin/brands/{id}
     * Delete a brand (Admin only)
     *
     * @param string $id Brand ID
     */
    public function destroy(string $id): void {
        AuthMiddleware::handle(true);
        $brandId = (int)$id;

        $brand = $this->brandModel->find($brandId);
        if (!$brand) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Brand not found.'
            ], 404);
        }

        try {
            $this->brandModel->delete($brandId);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Brand deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to delete brand: ' . $e->getMessage()
            ], 500);
        }
    }
}
