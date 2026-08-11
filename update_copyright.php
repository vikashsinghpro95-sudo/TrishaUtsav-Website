<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Database.php';

try {
    $db = Database::getInstance();
    $db->exec("UPDATE settings SET setting_value = '© 2026 Trisha Utsav. All rights reserved. Crafted for joyful celebrations.' WHERE setting_key = 'footer_copyright_text'");
    echo "Successfully updated copyright text.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
