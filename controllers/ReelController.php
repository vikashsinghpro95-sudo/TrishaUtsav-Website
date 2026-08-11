<?php
/**
 * Reel Controller
 */

class ReelController {
    private Reel $reelModel;

    public function __construct() {
        $this->reelModel = new Reel();
    }

    /**
     * GET /api/reels
     * Fetch active reels for public website
     */
    public function index(): void {
        try {
            $reels = $this->reelModel->getActive();
            Helper::jsonResponse([
                'success' => true,
                'data' => $reels
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch reels: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/admin/reels
     * Fetch all reels for admin panel
     */
    public function adminIndex(): void {
        AuthMiddleware::handle(true);

        try {
            $reels = $this->reelModel->all();
            Helper::jsonResponse([
                'success' => true,
                'data' => $reels
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch reels: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/admin/reels
     * Create a new reel (Admin only)
     */
    public function create(): void {
        AuthMiddleware::handle(true);

        $data = Helper::getRequestBody();

        $validator = new Validator($data);
        $errors = $validator->validate([
            'title'     => ['required', 'maxLength:255'],
            'video_url' => ['required']
        ]);

        if (!$validator->passes()) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors'  => $errors
            ], 422);
        }

        // BUG-005 Fix: Validate video_url to ensure it's an Instagram or YouTube Shorts link
        if (!preg_match('/(instagram\.com\/(?:reel|p)\/|youtube\.com\/shorts\/|youtu\.be\/)/i', $data['video_url'])) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors'  => ['video_url' => ['The video URL must be a valid Instagram Reel or YouTube Shorts link.']]
            ], 422);
        }

        try {
            $reelId = $this->reelModel->create($data);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Reel created successfully.',
                'reel_id' => $reelId
            ], 201);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to create reel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/admin/reels/{id}
     * Update an existing reel (Admin only)
     *
     * @param string $id
     */
    public function update(string $id): void {
        AuthMiddleware::handle(true);

        $reelId = (int)$id;
        $reel = $this->reelModel->find($reelId);

        if (!$reel) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Reel not found.'
            ], 404);
        }

        $data = Helper::getRequestBody();

        // BUG-005 Fix: Validate video_url if provided
        if (isset($data['video_url']) && !preg_match('/(instagram\.com\/(?:reel|p)\/|youtube\.com\/shorts\/|youtu\.be\/)/i', $data['video_url'])) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors'  => ['video_url' => ['The video URL must be a valid Instagram Reel or YouTube Shorts link.']]
            ], 422);
        }

        try {
            $this->reelModel->update($reelId, $data);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Reel updated successfully.'
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to update reel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/admin/reels/{id}
     * Delete a reel (Admin only)
     *
     * @param string $id
     */
    public function destroy(string $id): void {
        AuthMiddleware::handle(true);

        $reelId = (int)$id;
        $reel = $this->reelModel->find($reelId);

        if (!$reel) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Reel not found.'
            ], 404);
        }

        try {
            $this->reelModel->delete($reelId);
            Helper::jsonResponse([
                'success' => true,
                'message' => 'Reel deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            Helper::jsonResponse([
                'success' => false,
                'message' => 'Failed to delete reel: ' . $e->getMessage()
            ], 500);
        }
    }
}
