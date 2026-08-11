<?php
/**
 * Occasion Controller
 */

class OccasionController {
    private Occasion $occasionModel;

    public function __construct() {
        $this->occasionModel = new Occasion();
    }

    /**
     * GET /api/occasions
     * Public list of festive occasions
     */
    public function index(): void {
        try {
            $occasions = $this->occasionModel->getActive();
            foreach ($occasions as &$occ) {
                $occ['emoji'] = $this->getEmojiForOccasion($occ['slug'] ?? '');
                $occ['icon'] = $this->getIconForOccasion($occ['slug'] ?? '');
            }
            unset($occ);

            Helper::jsonResponse([
                'success' => true,
                'data' => $occasions
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch occasions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/occasions-products
     * Public endpoint to fetch products for an occasion with pagination, sort, and occasion metadata
     */
    public function products(): void {
        try {
            $occasionId = $_GET['occasion_id'] ?? null;
            $occasionSlug = $_GET['occasion'] ?? $_GET['slug'] ?? null;
            $sort = $_GET['sort'] ?? 'newest';
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = max(1, (int)($_GET['limit'] ?? $_GET['per_page'] ?? 12));

            $filters = [
                'page' => $page,
                'per_page' => $limit,
                'sort' => $sort,
                'status' => 'published'
            ];

            $activeOccasion = null;

            if (!empty($occasionId) && $occasionId !== 'all') {
                $filters['occasion_id'] = (int)$occasionId;
                $activeOccasion = $this->occasionModel->find((int)$occasionId);
            } elseif (!empty($occasionSlug) && $occasionSlug !== 'all') {
                if (is_numeric($occasionSlug)) {
                    $filters['occasion_id'] = (int)$occasionSlug;
                    $activeOccasion = $this->occasionModel->find((int)$occasionSlug);
                } else {
                    $filters['occasion'] = $occasionSlug;
                    $activeOccasion = $this->occasionModel->findBySlug($occasionSlug);
                }
            }

            $productModel = new Product();
            $result = $productModel->searchAndFilter($filters);

            if ($activeOccasion) {
                $activeOccasion['emoji'] = $this->getEmojiForOccasion($activeOccasion['slug'] ?? '');
                $activeOccasion['icon'] = $this->getIconForOccasion($activeOccasion['slug'] ?? '');
                if (!isset($activeOccasion['product_count'])) {
                    $activeOccasion['product_count'] = $result['pagination']['total_items'];
                }
            }

            Helper::jsonResponse([
                'success' => true,
                'data' => $result['data'],
                'total' => $result['pagination']['total_items'],
                'current_page' => $result['pagination']['current_page'],
                'last_page' => $result['pagination']['total_pages'],
                'per_page' => $result['pagination']['per_page'],
                'occasion' => $activeOccasion
            ], 200);

        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch occasion products: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getEmojiForOccasion(string $slug): string {
        $slug = strtolower(trim($slug));
        $map = [
            'diwali' => '🪔',
            'weddings' => '💍',
            'wedding' => '💍',
            'rakhi' => '🧶',
            'birthdays' => '🎂',
            'birthday' => '🎂',
            'corporate' => '🎁',
            'gudipadva' => '🌾',
            'makar' => '🪁',
            'krishnajanmashtami' => '🪈',
            'janmashtami' => '🪈',
            'holi' => '🎨',
            'ganesh-chaturthi' => '🐘',
            'navratri' => '🪘',
            'karwa-chauth' => '🌙',
            'bhai-dooj' => '✨',
            'dhanteras' => '🪙',
            'onam-pongal' => '🌴',
            'baby-shower' => '🍼',
            'saraswati-puja' => '📜',
            'chhath-puja' => '🌅'
        ];
        return $map[$slug] ?? '🎉';
    }

    private function getIconForOccasion(string $slug): string {
        $slug = strtolower(trim($slug));
        $map = [
            'diwali' => 'fa-lightbulb',
            'weddings' => 'fa-ring',
            'wedding' => 'fa-ring',
            'rakhi' => 'fa-heart-pulse',
            'birthdays' => 'fa-cake-candles',
            'birthday' => 'fa-cake-candles',
            'corporate' => 'fa-briefcase',
            'gudipadva' => 'fa-sun',
            'makar' => 'fa-paper-plane',
            'krishnajanmashtami' => 'fa-feather',
            'janmashtami' => 'fa-feather',
            'holi' => 'fa-palette',
            'ganesh-chaturthi' => 'fa-om',
            'navratri' => 'fa-drum',
            'karwa-chauth' => 'fa-moon',
            'bhai-dooj' => 'fa-star',
            'dhanteras' => 'fa-coins',
            'onam-pongal' => 'fa-seedling',
            'baby-shower' => 'fa-baby',
            'saraswati-puja' => 'fa-book-open',
            'chhath-puja' => 'fa-sun'
        ];
        return $map[$slug] ?? 'fa-glass-cheers';
    }

    /**
     * GET /api/occasions/{slug}
     * Get single occasion details
     */
    public function show(string $slug): void {
        try {
            $occasion = is_numeric($slug) ? $this->occasionModel->find((int)$slug) : $this->occasionModel->findBySlug($slug);
            if (!$occasion) {
                Helper::jsonResponse([
                    'success' => false,
                    'message' => 'Occasion not found.'
                ], 404);
            }
            Helper::jsonResponse([
                'success' => true,
                'data' => $occasion
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/admin/occasions
     * Admin list of occasions
     */
    public function adminIndex(): void {
        AuthMiddleware::handle(true);

        try {
            $occasions = $this->occasionModel->all();
            Helper::jsonResponse([
                'success' => true,
                'data' => $occasions
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch occasions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/admin/occasions
     * Create occasion (Admin only)
     */
    public function create(): void {
        AuthMiddleware::handle(true);

        $data = Helper::getRequestBody();
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Helper::slugify($data['name']);
        }

        $validator = new Validator($data);
        $errors = $validator->validate([
            'name' => ['required', 'maxLength:100'],
            'slug' => ['required', 'unique:occasions,slug', 'maxLength:100']
        ]);

        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors
            ], 422);
        }

        try {
            $id = $this->occasionModel->create($data);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Occasion created successfully.',
                'occasion_id' => $id
            ], 201);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to create occasion: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/admin/occasions/{id}
     * Update occasion (Admin only)
     *
     * @param string $id
     */
    public function update(string $id): void {
        AuthMiddleware::handle(true);

        $occId = (int)$id;
        $occasion = $this->occasionModel->find($occId);

        if (!$occasion) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Occasion not found.'
            ], 404);
        }

        $data = Helper::getRequestBody();
        if (!empty($data['slug'])) {
            $data['slug'] = Helper::slugify($data['slug']);
        }

        try {
            $this->occasionModel->update($occId, $data);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Occasion updated successfully.'
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to update occasion: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/admin/occasions/{id}
     * Delete occasion (Admin only)
     *
     * @param string $id
     */
    public function destroy(string $id): void {
        AuthMiddleware::handle(true);

        $occId = (int)$id;
        $occasion = $this->occasionModel->find($occId);

        if (!$occasion) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Occasion not found.'
            ], 404);
        }

        try {
            $this->occasionModel->delete($occId);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Occasion deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to delete occasion: ' . $e->getMessage()
            ], 500);
        }
    }
}
