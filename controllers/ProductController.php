<?php
/**
 * Product Controller
 */

class ProductController {
    private Product $productModel;

    public function __construct() {
        $this->productModel = new Product();
    }

    /**
     * GET /api/products
     * Public search, filter, and paginate products
     */
    public function index(): void {
        try {
            // Read query parameters
            $filters = [
                'page'         => $_GET['page'] ?? 1,
                'per_page'     => $_GET['per_page'] ?? 10,
                'search'       => $_GET['search'] ?? null,
                'category'     => $_GET['category'] ?? $_GET['category_id'] ?? null,
                'category_id'  => $_GET['category_id'] ?? $_GET['category'] ?? null,
                'occasion'     => $_GET['occasion'] ?? $_GET['occasion_id'] ?? null,
                'occasion_id'  => $_GET['occasion_id'] ?? $_GET['occasion'] ?? null,
                'brand'        => $_GET['brand'] ?? null,
                'sort'         => $_GET['sort'] ?? 'newest',
                'min_price'    => $_GET['min_price'] ?? null,
                'max_price'    => $_GET['max_price'] ?? null,
                'is_trending'  => $_GET['is_trending'] ?? null,
                'is_must_buy'  => $_GET['is_must_buy'] ?? null,
                'featured'     => $_GET['featured'] ?? null,
                'status'       => $_GET['status'] ?? 'published' // Public defaults to published
            ];

            // If a client calls public endpoint, force 'published' unless authenticated admin asks for drafts
            $headers = getallheaders();
            $isAdminRequest = false;
            
            if (isset($headers['Authorization']) || isset($headers['authorization'])) {
                try {
                    $user = AuthMiddleware::handle();
                    if ((int)$user['role_id'] === 1) {
                        $isAdminRequest = true;
                    }
                } catch (Exception $e) {
                    // Ignore, treat as guest
                }
            }

            if (!$isAdminRequest) {
                $filters['status'] = 'published';

                if (session_status() === PHP_SESSION_NONE) {
                    @session_start();
                }
                $cacheKey = 'prod_cache_' . md5(json_encode($filters));
                if (isset($_SESSION[$cacheKey]) && is_array($_SESSION[$cacheKey]) && (time() - ($_SESSION[$cacheKey]['time'] ?? 0)) < 30) {
                    Helper::jsonResponse($_SESSION[$cacheKey]['data'], 200);
                    return;
                }
            }

            $result = $this->productModel->searchAndFilter($filters);
            
            $responsePayload = [
                'success' => true,
                'data' => $result['data'],
                'pagination' => $result['pagination']
            ];

            if (!$isAdminRequest && isset($cacheKey)) {
                $_SESSION[$cacheKey] = [
                    'time' => time(),
                    'data' => $responsePayload
                ];
            }

            Helper::jsonResponse($responsePayload, 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/products/{slug}
     * Retrieve single product profile by slug
     *
     * @param string $slug
     */
    public function show(string $slug): void {
        try {
            $product = is_numeric($slug) ? $this->productModel->find((int)$slug) : $this->productModel->findBySlug($slug);

            if (!$product) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Product not found.'
                ], 404);
            }

            // Hide draft/archived products from non-admins
            if ($product['status'] !== 'published') {
                $headers = getallheaders();
                $isAdmin = false;
                if (isset($headers['Authorization']) || isset($headers['authorization'])) {
                    try {
                        $user = AuthMiddleware::handle();
                        if ((int)$user['role_id'] === 1) {
                            $isAdmin = true;
                        }
                    } catch (Exception $e) {}
                }

                if (!$isAdmin) {
                    Helper::jsonResponse([
                        'success' => false,
                        'message' => 'Product is not available.'
                    ], 404);
                }
            }

            Helper::jsonResponse([
                'success' => true,
                'data' => $product
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch product details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/admin/products
     * Create a new product (Admin only)
     */
    public function create(): void {
        // Enforce Admin Auth
        AuthMiddleware::handle(true);

        $data = Helper::getRequestBody();

        // Autocomplete slug if not specified
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Helper::slugify($data['name']);
        } elseif (!empty($data['slug'])) {
            $data['slug'] = Helper::slugify($data['slug']);
        }

        // Convert empty string for nullable fields to null
        $nullableFields = ['sku', 'brand_id', 'occasion_id', 'mrp', 'weight', 'dimensions', 'meta_title', 'meta_keywords', 'meta_description', 'short_description'];
        foreach ($nullableFields as $nField) {
            if (array_key_exists($nField, $data) && ($data[$nField] === '' || $data[$nField] === 'null')) {
                $data[$nField] = null;
            }
        }

        $validator = new Validator($data);
        $errors = $validator->validate([
            'category_id'    => ['required', 'numeric'],
            'name'           => ['required', 'maxLength:255'],
            'slug'           => ['required', 'unique:products,slug', 'maxLength:255'],
            'sku'            => ['unique:products,sku', 'maxLength:100'],
            'price'          => ['required', 'numeric'],
            'stock_quantity' => ['numeric']
        ]);

        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        try {
            $productId = $this->productModel->create($data);

            // Invalidate transient API response cache
            if (session_status() === PHP_SESSION_NONE) { @session_start(); }
            foreach ($_SESSION as $k => $v) {
                if (strpos($k, 'prod_cache_') === 0) { unset($_SESSION[$k]); }
            }

            Helper::jsonResponse([
                'success' => true,
                'message' => 'Product created successfully.',
                'product_id' => $productId
            ], 201);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/admin/products/{id}
     * Update an existing product (Admin only)
     *
     * @param string $id
     */
    public function update(string $id): void {
        // Enforce Admin Auth
        AuthMiddleware::handle(true);

        $productId = (int)$id;
        $product = $this->productModel->find($productId);

        if (!$product) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Product not found.'
            ], 404);
        }

        $data = Helper::getRequestBody();

        // Standardize or generate slug if sent
        if (!empty($data['slug'])) {
            $data['slug'] = Helper::slugify($data['slug']);
        } elseif (!empty($data['name'])) {
            $data['slug'] = Helper::slugify($data['name']);
        }

        // Convert empty string for nullable fields to null
        $nullableFields = ['sku', 'brand_id', 'occasion_id', 'mrp', 'weight', 'dimensions', 'meta_title', 'meta_keywords', 'meta_description', 'short_description'];
        foreach ($nullableFields as $nField) {
            if (array_key_exists($nField, $data) && ($data[$nField] === '' || $data[$nField] === 'null')) {
                $data[$nField] = null;
            }
        }

        $validator = new Validator($data);
        $rules = [];

        if (array_key_exists('category_id', $data)) {
            $rules['category_id'] = ['required', 'numeric'];
        }
        if (array_key_exists('name', $data)) {
            $rules['name'] = ['required', 'maxLength:255'];
        }
        if (array_key_exists('slug', $data) && !empty($data['slug'])) {
            $rules['slug'] = ['required', "unique:products,slug,$productId", 'maxLength:255'];
        }
        if (array_key_exists('sku', $data) && !empty($data['sku'])) {
            $rules['sku'] = ["unique:products,sku,$productId", 'maxLength:100'];
        }
        if (array_key_exists('price', $data)) {
            $rules['price'] = ['required', 'numeric'];
        }

        $errors = $validator->validate($rules);
        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        try {
            $this->productModel->update($productId, $data);

            // Invalidate transient API response cache
            if (session_status() === PHP_SESSION_NONE) { @session_start(); }
            foreach ($_SESSION as $k => $v) {
                if (strpos($k, 'prod_cache_') === 0) { unset($_SESSION[$k]); }
            }

            Helper::jsonResponse([
                'success' => true,
                'message' => 'Product updated successfully.'
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/admin/products/{id}
     * Archive/Delete a product (Admin only)
     *
     * @param string $id
     */
    public function delete(string $id): void {
        // Enforce Admin Auth
        AuthMiddleware::handle(true);

        $productId = (int)$id;
        $product = $this->productModel->find($productId);

        if (!$product) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Product not found.'
            ], 404);
        }

        try {
            $this->productModel->delete($productId);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Product successfully archived.'
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to archive product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/admin/products/{id}/images
     * Upload and attach additional image to product (Admin only)
     *
     * @param string $id
     */
    public function uploadImages(string $id): void {
        // Enforce Admin Auth
        AuthMiddleware::handle(true);

        $productId = (int)$id;
        $product = $this->productModel->find($productId);

        if (!$product) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Product not found.'
            ], 404);
        }

        $filesToUpload = [];

        if (isset($_FILES['image'])) {
            $file = $_FILES['image'];
            if (is_array($file['name'])) {
                for ($i = 0; $i < count($file['name']); $i++) {
                    if ($file['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
                    $filesToUpload[] = [
                        'name'     => $file['name'][$i],
                        'type'     => $file['type'][$i],
                        'tmp_name' => $file['tmp_name'][$i],
                        'error'    => $file['error'][$i],
                        'size'     => $file['size'][$i]
                    ];
                }
            } else {
                if ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                    $filesToUpload[] = $file;
                }
            }
        } elseif (isset($_FILES['images'])) {
            $file = $_FILES['images'];
            if (is_array($file['name'])) {
                for ($i = 0; $i < count($file['name']); $i++) {
                    if ($file['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
                    $filesToUpload[] = [
                        'name'     => $file['name'][$i],
                        'type'     => $file['type'][$i],
                        'tmp_name' => $file['tmp_name'][$i],
                        'error'    => $file['error'][$i],
                        'size'     => $file['size'][$i]
                    ];
                }
            } else {
                if ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                    $filesToUpload[] = $file;
                }
            }
        }

        if (empty($filesToUpload)) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation error. No valid image file provided.'
            ], 422);
        }

        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/jpg'  => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            'image/svg+xml' => 'svg'
        ];

        foreach ($filesToUpload as $f) {
            if ($f['error'] !== UPLOAD_ERR_OK) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'File upload error in one of the selected files.'
                ], 422);
            }
            if ($f['size'] > 5 * 1024 * 1024) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Payload Too Large. Image "' . $f['name'] . '" exceeds the 5MB limit.'
                ], 413);
            }
            $mime = '';
            if (class_exists('finfo')) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($f['tmp_name']);
            }
            if (empty($mime)) {
                $mime = $f['type'];
            }
            if (!array_key_exists($mime, $allowedMimes)) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Invalid image format for "' . $f['name'] . '". Allowed: JPG, PNG, WEBP, GIF, SVG.'
                ], 422);
            }
        }

        try {
            $uploaded = [];
            $existingImages = $this->productModel->getImages($productId);
            $nextSortOrder = count($existingImages);

            foreach ($filesToUpload as $f) {
                $relativeUrl = Helper::uploadImage($f, 'products');
                
                $isPrimary = 0;
                if ($nextSortOrder === 0) {
                    $isPrimary = 1;
                }

                $altText = $_POST['alt_text'] ?? null;
                $title = $_POST['title'] ?? null;

                $imageId = $this->productModel->addImage($productId, $relativeUrl, $altText, $isPrimary, $nextSortOrder);
                
                if (!empty($title)) {
                    $this->productModel->updateImageMetadata($productId, $imageId, ['title' => $title]);
                }

                $uploaded[] = [
                    'id'         => $imageId,
                    'image_url'  => $relativeUrl,
                    'is_primary' => $isPrimary,
                    'sort_order' => $nextSortOrder,
                    'alt_text'   => $altText,
                    'title'      => $title
                ];

                $nextSortOrder++;
            }

            Helper::jsonResponse([
                'success' => true,
                'message' => count($uploaded) . ' image(s) uploaded successfully.',
                'images'  => $uploaded
            ], 201);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to upload image(s): ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/admin/products/{id}/images/bulk-delete
     * Bulk delete images (Admin only)
     *
     * @param string $id Product ID
     */
    public function bulkDeleteImages(string $id): void {
        AuthMiddleware::handle(true);
        $productId = (int)$id;

        $product = $this->productModel->find($productId);
        if (!$product) {
            Helper::jsonResponse(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $data = Helper::getRequestBody();
        if (!isset($data['image_ids']) || !is_array($data['image_ids'])) {
            Helper::jsonResponse(['success' => false, 'message' => 'Invalid request. Expected array of image_ids.'], 422);
        }

        try {
            $this->productModel->deleteMultipleImages($productId, $data['image_ids']);
            Helper::jsonResponse(['success' => true, 'message' => 'Selected images deleted successfully.'], 200);
        } catch (Exception $e) {
            Helper::jsonResponse(['success' => false, 'message' => 'Failed to bulk delete images: ' . $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/admin/products/{id}/images/{imageId}
     * Update image metadata (Admin only)
     *
     * @param string $id Product ID
     * @param string $imageId Image ID
     */
    public function updateImageMetadata(string $id, string $imageId): void {
        AuthMiddleware::handle(true);
        $productId = (int)$id;
        $imgId = (int)$imageId;

        $product = $this->productModel->find($productId);
        if (!$product) {
            Helper::jsonResponse(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $data = Helper::getRequestBody();

        try {
            $this->productModel->updateImageMetadata($productId, $imgId, $data);
            Helper::jsonResponse(['success' => true, 'message' => 'Image metadata updated successfully.'], 200);
        } catch (Exception $e) {
            Helper::jsonResponse(['success' => false, 'message' => 'Failed to update metadata: ' . $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/admin/products/{id}/images
     * Update image sorting order and primary status (Admin only)
     *
     * @param string $id
     */
    public function updateImages(string $id): void {
        AuthMiddleware::handle(true);
        $productId = (int)$id;
        $product = $this->productModel->find($productId);

        if (!$product) {
            Helper::jsonResponse(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $data = Helper::getRequestBody();
        if (!isset($data['images']) || !is_array($data['images'])) {
            Helper::jsonResponse(['success' => false, 'message' => 'Invalid payload. Expected array of images.'], 422);
        }

        // Validate exactly one primary
        $primaryCount = 0;
        foreach ($data['images'] as $img) {
            if (isset($img['is_primary']) && (int)$img['is_primary'] === 1) {
                $primaryCount++;
            }
        }
        
        if ($primaryCount !== 1 && count($data['images']) > 0) {
             Helper::jsonResponse(['success' => false, 'message' => 'Exactly one image must be set as primary.'], 422);
        }

        try {
            $this->productModel->updateImageOrder($productId, $data['images']);
            Helper::jsonResponse(['success' => true, 'message' => 'Images updated successfully.'], 200);
        } catch (Exception $e) {
            Helper::jsonResponse(['success' => false, 'message' => 'Failed to update images: ' . $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/admin/products/{id}/images/{imageId}
     * Delete a product image (Admin only)
     *
     * @param string $id
     * @param string $imageId
     */
    public function deleteImage(string $id, string $imageId): void {
        AuthMiddleware::handle(true);
        $productId = (int)$id;
        $imgId = (int)$imageId;

        $product = $this->productModel->find($productId);
        if (!$product) {
            Helper::jsonResponse(['success' => false, 'message' => 'Product not found.'], 404);
        }

        try {
            $success = $this->productModel->deleteProductImage($productId, $imgId);
            if ($success) {
                Helper::jsonResponse(['success' => true, 'message' => 'Image deleted successfully.'], 200);
            } else {
                Helper::jsonResponse(['success' => false, 'message' => 'Image not found.'], 404);
            }
        } catch (Exception $e) {
            Helper::jsonResponse(['success' => false, 'message' => 'Failed to delete image: ' . $e->getMessage()], 500);
        }
    }

    /**
     * PATCH /api/admin/products/{id}/stock
     * Adjust stock level of product (Admin only)
     *
     * @param string $id
     */
    public function adjustStock(string $id): void {
        // Enforce Admin Auth
        $user = AuthMiddleware::handle(true);
        $userId = (int)$user['id'];

        $productId = (int)$id;
        $product = $this->productModel->find($productId);

        if (!$product) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Product not found.'
            ], 404);
        }

        $data = Helper::getRequestBody();

        $validator = new Validator($data);
        $errors = $validator->validate([
            'quantity_change' => ['required', 'numeric']
        ]);

        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        try {
            $change = (int)$data['quantity_change'];
            $reason = $data['reason'] ?? 'Manual inventory adjustment';

            $this->productModel->adjustStock($productId, $userId, $change, $reason);

            Helper::jsonResponse([
                'success' => true,
                'message' => 'Product stock adjusted successfully.'
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to adjust inventory: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/admin/products/{id}
     * Retrieve single product profile by ID (Admin only)
     *
     * @param string $id
     */
    public function showAdmin(string $id): void {
        AuthMiddleware::handle(true);
        $productId = (int)$id;

        try {
            $product = $this->productModel->find($productId);

            if (!$product) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Product not found.'
                ], 404);
            }

            Helper::jsonResponse([
                'success' => true,
                'data' => $product
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch product: ' . $e->getMessage()
            ], 500);
        }
    }
}
