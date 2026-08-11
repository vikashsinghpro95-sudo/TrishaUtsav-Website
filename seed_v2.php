<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Database.php';

echo "<h1>Trisha Utsav - Seed V2</h1>";

try {
    $db = Database::getInstance();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->query("SELECT id, name FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->query("SELECT id, name FROM occasions");
    $occasions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $images = [
        "https://images.unsplash.com/photo-1605556277322-6b943236e78f?q=80&w=800",
        "https://images.unsplash.com/photo-1599577180570-87a32bd057f9?q=80&w=800",
        "https://images.unsplash.com/photo-1603525281488-69315d0505b2?q=80&w=800"
    ];

    $total = 0;
    $db->beginTransaction();
    
    $stmtProd = $db->prepare("INSERT INTO products (category_id, occasion_id, name, slug, sku, short_description, description, price, mrp, tax_rate, stock_quantity, status, featured, is_trending) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'published', ?, ?)");
    
    foreach ($categories as $cat) {
        for ($i = 1; $i <= 30; $i++) {
            $name = $cat['name'] . " Product " . rand(1000, 99999);
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name)) . '-' . md5(uniqid(rand(), true));
            $sku = 'CAT-' . $cat['id'] . '-' . md5(uniqid(rand(), true));
            $is_trending = (rand(1, 10) > 7) ? 1 : 0;
            
            $stmtProd->execute([
                $cat['id'], null, $name, $slug, substr($sku, 0, 50),
                "Premium product.", "<p>High quality product.</p>",
                rand(500, 2000), rand(2100, 3000), 18.00, 50, 0, $is_trending
            ]);
            $total++;
        }
    }
    
    foreach ($occasions as $occ) {
        for ($i = 1; $i <= 30; $i++) {
            $name = $occ['name'] . " Product " . rand(1000, 99999);
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name)) . '-' . md5(uniqid(rand(), true));
            $sku = 'OCC-' . $occ['id'] . '-' . md5(uniqid(rand(), true));
            $is_trending = (rand(1, 10) > 7) ? 1 : 0;
            
            $stmtProd->execute([
                1, $occ['id'], $name, $slug, substr($sku, 0, 50),
                "Premium product.", "<p>High quality product.</p>",
                rand(500, 2000), rand(2100, 3000), 18.00, 50, 0, $is_trending
            ]);
            $total++;
        }
    }
    
    $db->commit();
    echo "<p>Successfully seeded $total products!</p>";

    // Now insert images in a separate transaction to avoid massive lock
    $db->beginTransaction();
    $stmtImg = $db->prepare("INSERT INTO product_images (product_id, image_url, alt_text, is_primary, sort_order) VALUES (?, ?, ?, 1, 0)");
    $stmtProducts = $db->query("SELECT id, name FROM products WHERE id > 10");
    while ($row = $stmtProducts->fetch(PDO::FETCH_ASSOC)) {
        $imgUrl = $images[array_rand($images)];
        $stmtImg->execute([$row['id'], $imgUrl, $row['name']]);
    }
    $db->commit();
    echo "<p>Successfully added images!</p>";

} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
