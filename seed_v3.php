<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Database.php';

echo "<h1>Trisha Utsav - Seed V3</h1>\n";

try {
    $db = Database::getInstance();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Disable autocommit implicitly just in case
    $db->beginTransaction();
    
    $stmt = $db->query("SELECT id, name FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Categories found: " . count($categories) . "\n";

    $stmtProd = $db->prepare("INSERT INTO products (category_id, name, slug, sku, price, stock_quantity, status) VALUES (?, ?, ?, ?, ?, ?, 'published')");
    
    $total = 0;
    foreach ($categories as $cat) {
        for ($i = 1; $i <= 30; $i++) {
            $name = $cat['name'] . " Test " . rand(1000, 99999);
            $slug = "test-" . md5(uniqid(rand(), true));
            $sku = "SKU-" . md5(uniqid(rand(), true));
            
            $stmtProd->execute([
                $cat['id'], $name, $slug, substr($sku, 0, 50),
                rand(500, 2000), 50
            ]);
            $total++;
        }
    }
    
    $db->commit();
    echo "<p>Successfully seeded $total products!</p>\n";

} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "<p>Error: " . $e->getMessage() . "</p>\n";
}
?>
