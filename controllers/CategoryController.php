<?php
/**
 * Category Controller
 */

class CategoryController {
    private Category $categoryModel;

    public function __construct() {
        $this->categoryModel = new Category();
    }

    /**
     * GET /api/categories
     * Retrieve categories (tree and flat list with counts)
     */
    public function index(): void {
        try {
            $tree = $this->categoryModel->getTree();
            $flat = $this->categoryModel->getActiveWithCount();
            foreach ($flat as &$cat) {
                $cat['emoji'] = $this->getEmojiForCategory($cat['slug'] ?? '');
                $cat['icon'] = $this->getIconForCategory($cat['slug'] ?? '');
            }
            unset($cat);

            Helper::jsonResponse([
                'success' => true,
                'data' => $tree,
                'categories' => $flat
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to retrieve categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/categories-products
     * Public endpoint to fetch products for a category with pagination, sort, and category metadata
     */
    public function products(): void {
        try {
            $categoryId = $_GET['category_id'] ?? null;
            $categorySlug = $_GET['category'] ?? $_GET['slug'] ?? null;
            $sort = $_GET['sort'] ?? 'newest';
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = max(1, (int)($_GET['limit'] ?? $_GET['per_page'] ?? 12));

            $filters = [
                'page' => $page,
                'per_page' => $limit,
                'sort' => $sort,
                'status' => 'published'
            ];

            $activeCategory = null;

            if (!empty($categoryId) && $categoryId !== 'all') {
                $filters['category_id'] = (int)$categoryId;
                $activeCategory = $this->categoryModel->find((int)$categoryId);
            } elseif (!empty($categorySlug) && $categorySlug !== 'all') {
                if (is_numeric($categorySlug)) {
                    $filters['category_id'] = (int)$categorySlug;
                    $activeCategory = $this->categoryModel->find((int)$categorySlug);
                } else {
                    $filters['category'] = $categorySlug;
                    $activeCategory = $this->categoryModel->findBySlug($categorySlug);
                }
            }

            $productModel = new Product();
            $result = $productModel->searchAndFilter($filters);

            if ($activeCategory) {
                $activeCategory['emoji'] = $this->getEmojiForCategory($activeCategory['slug'] ?? '');
                $activeCategory['icon'] = $this->getIconForCategory($activeCategory['slug'] ?? '');
                if (!isset($activeCategory['product_count'])) {
                    $activeCategory['product_count'] = $result['pagination']['total_items'];
                }
            }

            Helper::jsonResponse([
                'success' => true,
                'data' => $result['data'],
                'total' => $result['pagination']['total_items'],
                'current_page' => $result['pagination']['current_page'],
                'last_page' => $result['pagination']['total_pages'],
                'per_page' => $result['pagination']['per_page'],
                'category' => $activeCategory
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch category products: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getEmojiForCategory(string $slug): string {
        $slug = strtolower(trim($slug));
        $map = [
            'smartphones' => '📱',
            'laptops' => '💻',
            'audio' => '🎧',
            'rakhi' => '🧶',
            'gudipadva' => '🌾',
            'makar' => '🪁',
            'krishnajanmashtami' => '🪈',
            'diwali' => '🪔',
            'holi' => '🎨',
            'ganesh-chaturthi' => '🐘',
            'navratri' => '🪘',
            'karwa-chauth' => '🌙',
            'bhai-dooj' => '✨',
            'dhanteras' => '🪙',
            'onam-pongal' => '🌴',
            'wedding-celebrations' => '💍',
            'baby-shower' => '🍼',
            'saraswati-puja' => '📜',
            'chhath-puja' => '🌅'
        ];
        return $map[$slug] ?? '🏷️';
    }

    private function getIconForCategory(string $slug): string {
        $slug = strtolower(trim($slug));
        $map = [
            'smartphones' => 'fa-mobile-screen-button',
            'laptops' => 'fa-laptop',
            'audio' => 'fa-headphones',
            'rakhi' => 'fa-heart-pulse',
            'gudipadva' => 'fa-sun',
            'makar' => 'fa-paper-plane',
            'krishnajanmashtami' => 'fa-feather',
            'diwali' => 'fa-lightbulb',
            'holi' => 'fa-palette',
            'ganesh-chaturthi' => 'fa-om',
            'navratri' => 'fa-drum',
            'karwa-chauth' => 'fa-moon',
            'bhai-dooj' => 'fa-star',
            'dhanteras' => 'fa-coins',
            'onam-pongal' => 'fa-seedling',
            'wedding-celebrations' => 'fa-ring',
            'baby-shower' => 'fa-baby',
            'saraswati-puja' => 'fa-book-open',
            'chhath-puja' => 'fa-sun'
        ];
        return $map[$slug] ?? 'fa-tag';
    }

    /**
     * GET /api/categories/{slug}
     * Get category details by slug or ID
     */
    public function show(string $slug): void {
        try {
            $category = is_numeric($slug) ? $this->categoryModel->find((int)$slug) : $this->categoryModel->findBySlug($slug);
            if (!$category) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Category not found.'
                ], 404);
            }
            Helper::jsonResponse([
                'success' => true,
                'data' => $category
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/admin/categories
     * Create a new category (Admin only)
     */
    public function create(): void {
        // Enforce Admin Authentication
        AuthMiddleware::handle(true);

        $data = Helper::getRequestBody();

        // Autogenerate slug if not sent
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Helper::slugify($data['name']);
        } elseif (!empty($data['slug'])) {
            $data['slug'] = Helper::slugify($data['slug']);
        }

        $validator = new Validator($data);
        $rules = [
            'name' => ['required', 'maxLength:191'],
            'slug' => ['required', 'unique:categories,slug', 'maxLength:191']
        ];

        // If parent_id is sent, validate it exists in database
        if (!empty($data['parent_id'])) {
            $rules['parent_id'] = ['numeric'];
            // Manual validation check to see if parent category actually exists
            $parent = $this->categoryModel->find((int)$data['parent_id']);
            if (!$parent) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Validation errors occurred.',
                    'errors' => ['parent_id' => ['The specified parent category does not exist.']]
                ], 422);
            }
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
            $categoryId = $this->categoryModel->create($data);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Category created successfully.',
                'category_id' => $categoryId
            ], 201);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to create category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/admin/categories/{id}
     * Update an existing category (Admin only)
     *
     * @param string $id
     */
    public function update(string $id): void {
        // Enforce Admin Authentication
        AuthMiddleware::handle(true);

        $categoryId = (int)$id;
        $category = $this->categoryModel->find($categoryId);

        if (!$category) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Category not found.'
            ], 404);
        }

        $data = Helper::getRequestBody();

        // Autogenerate/format slug if sent
        if (!empty($data['slug'])) {
            $data['slug'] = Helper::slugify($data['slug']);
        } elseif (isset($data['name']) && !empty($data['name']) && !isset($data['slug'])) {
            // Optional: autogenerate if name changes and slug is omitted
            $data['slug'] = Helper::slugify($data['name']);
        }

        $validator = new Validator($data);
        $rules = [];

        if (array_key_exists('name', $data)) {
            $rules['name'] = ['required', 'maxLength:191'];
        }
        if (array_key_exists('slug', $data)) {
            $rules['slug'] = ['required', "unique:categories,slug,$categoryId", 'maxLength:191'];
        }

        // Prevent setting parent_id to itself
        if (!empty($data['parent_id'])) {
            if ((int)$data['parent_id'] === $categoryId) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Validation errors occurred.',
                    'errors' => ['parent_id' => ['A category cannot be its own parent.']]
                ], 422);
            }
            
            $parent = $this->categoryModel->find((int)$data['parent_id']);
            if (!$parent) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Validation errors occurred.',
                    'errors' => ['parent_id' => ['The specified parent category does not exist.']]
                ], 422);
            }
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
            $this->categoryModel->update($categoryId, $data);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Category updated successfully.'
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to update category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/admin/categories/{id}
     * Soft delete/deactivate a category (Admin only)
     *
     * @param string $id
     */
    public function destroy(string $id): void {
        // Enforce Admin Authentication
        AuthMiddleware::handle(true);

        $categoryId = (int)$id;
        $category = $this->categoryModel->find($categoryId);

        if (!$category) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Category not found.'
            ], 404);
        }

        // BUG-003 Fix: Prevent deleting categories with active products
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND status != 'archived'");
        $stmt->execute([$categoryId]);
        $activeProductsCount = (int)$stmt->fetchColumn();

        if ($activeProductsCount > 0) {
            Helper::jsonResponse([
                'success' => false,
                'message' => "Cannot delete category. It is currently being used by $activeProductsCount active product(s)."
            ], 409);
        }

        try {
            $this->categoryModel->delete($categoryId);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Category successfully deactivated.'
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to deactivate category: ' . $e->getMessage()
            ], 500);
        }
    }
}
