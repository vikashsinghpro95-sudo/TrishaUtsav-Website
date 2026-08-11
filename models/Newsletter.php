<?php
require_once __DIR__ . '/../includes/Database.php';

class Newsletter {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function subscribe($email) {
        $stmt = $this->db->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email is already subscribed.'];
        }

        $stmt = $this->db->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
        $stmt->execute([$email]);
        return ['success' => true, 'message' => 'Successfully subscribed!'];
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM newsletter_subscribers ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
