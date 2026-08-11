<?php
/**
 * Helper Utility Class
 */

class Helper {
    /**
     * Send JSON Response and terminate execution
     *
     * @param mixed $data
     * @param int $statusCode
     * @return void
     */
    public static function jsonResponse($data, $statusCode = 200): void {
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Mitigate verbose SQL error disclosure globally
        if (isset($data['message']) && is_string($data['message'])) {
            $badWords = ['SQLSTATE', 'PDOException', 'syntax error', 'MySQL', 'database', 'column', 'table'];
            foreach ($badWords as $word) {
                if (stripos($data['message'], $word) !== false) {
                    $data['message'] = 'A server error occurred. Please try again later.';
                    break;
                }
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Get JSON request body parsed as associative array
     *
     * @return array
     */
    public static function getRequestBody(): array {
        $input = file_get_contents('php://input');
        if (empty($input)) {
            return [];
        }
        $decoded = json_decode($input, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Validate and upload an image file
     *
     * @param array $file The $_FILES element
     * @param string $targetSubDir e.g. 'products', 'categories'
     * @return string Relative path to the uploaded file from server root
     * @throws Exception
     */
    public static function uploadFile(array $file, string $targetSubDir = '', array $allowedMimes = [], int $maxSize = 100000000): string {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception("Invalid file upload parameters.");
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_NO_FILE:
                    throw new Exception("No file was uploaded.");
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $maxIni = ini_get('upload_max_filesize') ?: '30M';
                    throw new Exception("Uploaded file exceeds maximum server size limit ({$maxIni}). Please select a smaller file or compress your image.");
                default:
                    throw new Exception("File upload failed with error code: " . $file['error']);
            }
        }

        if ($file['size'] > $maxSize) {
            $mb = round($maxSize / 1000000);
            throw new Exception("File size exceeds {$mb}MB limit.");
        }

        $mime = '';
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
        }
        if (empty($mime)) {
            $mime = $file['type'];
        }

        if (!array_key_exists($mime, $allowedMimes)) {
            throw new Exception("Invalid file format. Allowed: " . implode(', ', array_keys($allowedMimes)));
        }

        $ext = $allowedMimes[$mime];
        $fileName = bin2hex(random_bytes(16)) . '.' . $ext;

        $baseUploadDir = __DIR__ . '/../uploads';
        $targetDir = $baseUploadDir;
        if (!empty($targetSubDir)) {
            $targetDir .= '/' . trim($targetSubDir, '/');
        }

        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                throw new Exception("Failed to create upload directories.");
            }
        }

        $destPath = $targetDir . '/' . $fileName;
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new Exception("Failed to save uploaded file.");
        }

        $relativePrefix = 'uploads';
        if (!empty($targetSubDir)) {
            $relativePrefix .= '/' . trim($targetSubDir, '/');
        }
        return $relativePrefix . '/' . $fileName;
    }

    /**
     * Upload an image to a specific directory
     *
     * @param array $file The $_FILES element
     * @param string $targetSubDir e.g. 'products', 'categories'
     * @return string Relative path to the uploaded file from server root
     * @throws Exception
     */
    public static function uploadImage(array $file, string $targetSubDir = ''): string {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception("Invalid file upload parameters.");
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new Exception("No file was uploaded.");
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception("Uploaded file exceeds server size limit. Maximum allowed size is 30MB.");
            default:
                throw new Exception("File upload failed with error code: " . $file['error']);
        }

        // Limit file size to 30MB
        if ($file['size'] > 30000000) {
            throw new Exception("File size exceeds 30MB limit.");
        }

        // Check MIME type securely using finfo
        $mime = '';
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
        }
        if (empty($mime)) {
            $mime = $file['type'];
        }
        
        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/jpg'  => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            'image/svg+xml' => 'svg'
        ];

        if (!array_key_exists($mime, $allowedMimes)) {
            throw new Exception("Invalid image format. Allowed formats: JPG, PNG, WEBP, GIF, SVG.");
        }

        $ext = $allowedMimes[$mime];
        
        // Generate random unique filename
        $fileName = bin2hex(random_bytes(16)) . '.' . $ext;

        $baseUploadDir = __DIR__ . '/../uploads';
        $targetDir = $baseUploadDir;
        if (!empty($targetSubDir)) {
            $targetDir .= '/' . trim($targetSubDir, '/');
        }

        // Create directory recursively with read/write permissions
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                throw new Exception("Failed to create upload directories.");
            }
        }

        $destPath = $targetDir . '/' . $fileName;
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new Exception("Failed to save uploaded image.");
        }

        // Return path relative to the root (e.g. uploads/products/filename.jpg)
        $relativePrefix = 'uploads';
        if (!empty($targetSubDir)) {
            $relativePrefix .= '/' . trim($targetSubDir, '/');
        }
        return $relativePrefix . '/' . $fileName;
    }

    /**
     * Generate URL-friendly slug from string
     *
     * @param string $text
     * @return string
     */
    public static function slugify(string $text): string {
        // replace non-letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        // trim
        $text = trim($text, '-');
        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);
        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }

    /**
     * Log an admin action to the audit_logs table
     *
     * @param int $userId
     * @param string $action
     * @param string|null $entityType
     * @param int|null $entityId
     * @param array|null $oldValue
     * @param array|null $newValue
     * @return void
     */
    public static function logAction(
        int $userId, 
        string $action, 
        ?string $entityType = null, 
        ?int $entityId = null, 
        ?array $oldValue = null, 
        ?array $newValue = null
    ): void {
        try {
            $db = Database::getInstance();
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            
            $oldJson = $oldValue !== null ? json_encode($oldValue, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
            $newJson = $newValue !== null ? json_encode($newValue, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
            
            $stmt = $db->prepare("
                INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_value, new_value, ip_address)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $action,
                $entityType,
                $entityId,
                $oldJson,
                $newJson,
                $ipAddress
            ]);
        } catch (Exception $e) {
            error_log("Failed to write audit log: " . $e->getMessage());
        }
    }
}
