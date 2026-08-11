<?php
require_once __DIR__ . '/../models/Newsletter.php';
require_once __DIR__ . '/../includes/Helper.php';

class NewsletterController {
    private $newsletterModel;

    public function __construct() {
        $this->newsletterModel = new Newsletter();
    }

    public function subscribe() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data && !empty($_POST)) {
            $data = $_POST;
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Helper::jsonResponse(['success' => false, 'message' => 'Valid email address is required.']);
            return;
        }

        try {
            $result = $this->newsletterModel->subscribe($data['email']);
            Helper::jsonResponse(['success' => $result['success'], 'message' => $result['message']]);
        } catch (Exception $e) {
            error_log("Newsletter Subscribe Error: " . $e->getMessage());
            Helper::jsonResponse(['success' => false, 'message' => 'An error occurred while subscribing.']);
        }
    }

    public function adminIndex() {
        // Assume auth check is done in middleware or similar to other admin routes
        try {
            $subscribers = $this->newsletterModel->getAll();
            Helper::jsonResponse(['success' => true, 'message' => 'Newsletter subscribers fetched successfully.', 'data' => $subscribers]);
        } catch (Exception $e) {
            error_log("Newsletter Admin Index Error: " . $e->getMessage());
            Helper::jsonResponse(['success' => false, 'message' => 'An error occurred while fetching subscribers.']);
        }
    }
}
