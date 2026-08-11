<?php
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Access denied: Database seeder scripts can only be run via CLI.\n");
}
/**
 * Bulk Product Seeder Utility
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Database.php';

try {
    $db = Database::getInstance();

    echo "Disabling foreign key checks...\n";
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");

    echo "Truncating orders, carts, and product tables...\n";
    $db->exec("TRUNCATE TABLE refunds;");
    $db->exec("TRUNCATE TABLE payments;");
    $db->exec("TRUNCATE TABLE shipments;");
    $db->exec("TRUNCATE TABLE order_status_history;");
    $db->exec("TRUNCATE TABLE order_items;");
    $db->exec("TRUNCATE TABLE orders;");
    $db->exec("TRUNCATE TABLE cart_items;");
    $db->exec("TRUNCATE TABLE inventory_log;");
    $db->exec("TRUNCATE TABLE product_attributes;");
    $db->exec("TRUNCATE TABLE product_images;");
    $db->exec("TRUNCATE TABLE products;");

    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "Tables truncated successfully.\n";

    // Load brand names mapping
    $brandsRes = $db->query("SELECT id, name FROM brands")->fetchAll(PDO::FETCH_KEY_PAIR);

    $categories = [
        2 => [ // Smartphones
            'name' => 'Smartphone',
            'brands' => [1, 2, 3, 4],
            'models' => ["iPhone 16 Pro", "Galaxy S24", "Buds Pro Phone", "Redmi Note 13", "iPhone 15", "Galaxy S23", "Nord 4", "Mi 14", "Pixel 9 Pro", "Edge 50 Neo"],
            'images' => ['uploads/products/iphone_16_pro.png', 'uploads/products/samsung_s24.png']
        ],
        3 => [ // Laptops
            'name' => 'Laptop',
            'brands' => [1, 2, 4, 5],
            'models' => ["MacBook Air M3", "Galaxy Book4 Ultra", "Notebook Air", "Ultra SlimBook", "Developer Pro 15", "ZenBook Duo", "IdeaPad Slim", "Inspiron 14"],
            'images' => ['uploads/products/macbook_m3.png']
        ],
        4 => [ // Audio
            'name' => 'Audio',
            'brands' => [1, 2, 3, 5],
            'models' => ["Buds Pro Wireless", "AirPods Gold Edition", "Galaxy Buds Live", "Air Link Wireless", "Bass Boom Speaker", "Cheaper Cable", "HDMI 2.1 Wire", "SuperVOOC Charger"],
            'images' => ['uploads/products/oneplus_buds.png']
        ]
    ];

    $stmtProd = $db->prepare("INSERT INTO products (id, category_id, brand_id, name, slug, sku, short_description, description, price, mrp, tax_rate, stock_quantity, status, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtImg = $db->prepare("INSERT INTO product_images (product_id, image_url, alt_text, is_primary, sort_order) VALUES (?, ?, ?, 1, 0)");
    $stmtAttr = $db->prepare("INSERT INTO product_attributes (product_id, attribute_name, attribute_value, extra_price) VALUES (?, ?, ?, ?)");

    echo "Inserting core testing products (1-5)...\n";
    $coreProducts = [
        [1, 2, 1, 'iPhone 16 Pro', 'iphone-16-pro', 'IPH16PRO', 119900.00, 119900.00 * 1.1, 18.00, 50, 1, 'uploads/products/iphone_16_pro.png', 'Color', 'Titanium Grey'],
        [2, 2, 2, 'Samsung Galaxy S24', 'samsung-galaxy-s24', 'SAMS24', 99900.00, 99900.00 * 1.1, 12.00, 10, 1, 'uploads/products/samsung_s24.png', 'Color', 'Onyx Black'],
        [3, 4, 5, 'Cheaper Cable', 'cheaper-cable', 'CABLE01', 100.00, 120.00, 5.00, 100, 0, 'uploads/products/oneplus_buds.png', 'Color', 'Black'],
        [4, 3, 1, 'MacBook Air M3', 'macbook-air-m3', 'MBA13M3', 114900.00, 114900.00 * 1.1, 18.00, 15, 1, 'uploads/products/macbook_m3.png', 'Color', 'Space Grey'],
        [5, 4, 3, 'OnePlus Buds Pro', 'oneplus-buds-pro', 'OPBUDS', 9900.00, 9900.00 * 1.15, 18.00, 40, 1, 'uploads/products/oneplus_buds.png', 'Color', 'Matte Black']
    ];

    foreach ($coreProducts as $cp) {
        $stmtProd->execute([
            $cp[0], $cp[1], $cp[2], $cp[3], $cp[4], $cp[5],
            "Premium {$cp[3]} for testing.", "Detailed description of {$cp[3]}.",
            $cp[6], $cp[7], $cp[8], $cp[9], 'published', $cp[10]
        ]);
        $stmtImg->execute([$cp[0], $cp[11], $cp[3]]);
        $stmtAttr->execute([$cp[0], $cp[12], $cp[13], 0.00]);
    }
    
    // Seed extra variant attributes for testing product 1
    $stmtAttr->execute([1, 'Storage', '256GB', 10000.00]);

    echo "Inserting 200 sample products (6-205)...\n";

    for ($i = 6; $i <= 205; $i++) {
        // Pick category
        $catIds = array_keys($categories);
        $catId = $catIds[($i - 6) % count($catIds)];
        $catData = $categories[$catId];

        // Pick brand
        $brandId = $catData['brands'][($i - 6) % count($catData['brands'])];
        $brandName = isset($brandsRes[$brandId]) ? $brandsRes[$brandId] : 'Generic';

        // Pick model
        $model = $catData['models'][($i - 6) % count($catData['models'])];

        // Generate attributes
        $color = ["Silver", "Space Grey", "Onyx Black", "Rose Gold", "Titanium", "Forest Green"][($i - 6) % 6];
        $name = "{$brandName} {$model} - {$color} (Edition " . ($i - 5) . ")";
        
        // Clean slug
        $slug = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $name))) . '-' . $i;
        
        $sku = strtoupper(substr($catData['name'], 0, 3)) . str_pad($i, 5, '0', STR_PAD_LEFT);
        
        // Dynamic Pricing
        if ($catId === 2) { // Smartphones
            $price = 45000.00 + ($i * 200);
            $mrp = $price * 1.15;
            $tax = 12.00;
        } elseif ($catId === 3) { // Laptops
            $price = 75000.00 + ($i * 350);
            $mrp = $price * 1.20;
            $tax = 18.00;
        } else { // Audio / Cables
            $price = 499.00 + ($i * 45);
            $mrp = $price * 1.25;
            $tax = 18.00;
        }

        $shortDesc = "Experience high performance with the premium {$name}. Designed for quality and durability.";
        $desc = "<p>The {$name} offers cutting edge design coupled with high performance features. Ideal for tech enthusiasts who demand quality in every aspect of their digital lifestyle.</p><ul><li>Brilliant ergonomics and construction</li><li>High efficiency battery output</li><li>Full factory warranty covered</li></ul>";
        $stock = rand(10, 80);
        $featured = ($i % 12 === 0) ? 1 : 0;

        // Save product
        $stmtProd->execute([
            $i,
            $catId,
            $brandId,
            $name,
            $slug,
            $sku,
            $shortDesc,
            $desc,
            $price,
            $mrp,
            $tax,
            $stock,
            'published',
            $featured
        ]);

        // Save primary image
        $imgUrl = $catData['images'][($i - 6) % count($catData['images'])];
        $stmtImg->execute([
            $i,
            $imgUrl,
            $name
        ]);

        // Save sample attribute
        $stmtAttr->execute([
            $i,
            'Color',
            $color,
            0.00
        ]);
    }

    echo "Seeding 205 total products completed successfully.\n";

} catch (Exception $e) {
    echo "Error seeding: " . $e->getMessage() . "\n";
}
